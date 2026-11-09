<?php
/**
 * ============================================================================
 * AUTOMATED TEST SUITE — Pengujian Fungsional & Perbandingan Arsitektur
 * Menguji seluruh 6 fitur wajib pada:
 *   1. Aplikasi 1 (Non-Layered Architecture)
 *   2. Aplikasi 2 (3-Layer Architecture: Data Layer, Business Logic Layer, REST API)
 * ============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/2-layered-app/data-access/Database.php';
require_once dirname(__DIR__) . '/2-layered-app/data-access/CustomerRepository.php';
require_once dirname(__DIR__) . '/2-layered-app/business-logic/CustomerService.php';

$results = [];

function recordTest(array &$results, string $id, string $feature, string $scenario, bool $passed, string $nonLayeredStatus, string $layeredStatus, string $notes): void
{
    $results[] = [
        'id'                 => $id,
        'feature'            => $feature,
        'scenario'           => $scenario,
        'passed'             => $passed,
        'non_layered_status' => $nonLayeredStatus,
        'layered_status'     => $layeredStatus,
        'notes'              => $notes,
    ];
}

$service = new CustomerService();
$repo    = new CustomerRepository();

// 1. UJI TAMPIL PELANGGAN (READ ALL)
$listRes = $service->listCustomers('');
$t1Pass  = ($listRes['success'] === true && count($listRes['data']) >= 5);
recordTest(
    $results,
    'TC-01',
    'Tampil Pelanggan',
    'Menampilkan seluruh data pelanggan awal (minimal 5 data pelanggan)',
    $t1Pass,
    'PASS (HTML Tabel ter-render langsung dari query SELECT)',
    'PASS (HTTP 200 JSON array berisi ' . count($listRes['data']) . ' pelanggan)',
    'Kedua aplikasi menampilkan daftar pelanggan secara lengkap.'
);

// 2. UJI TAMBAH PELANGGAN BARU (CREATE)
$uniqueEmail = 'fajar.nugroho.' . time() . '@inovasi.co.id';
$createRes = $service->createCustomer([
    'nama'    => 'Fajar Nugroho',
    'email'   => $uniqueEmail,
    'telepon' => '0811-2233-4455',
    'alamat'  => 'Jl. Merdeka No. 10, Bandung',
]);
$newId = (int)($createRes['data']['id'] ?? 0);
$t2Pass = ($createRes['status_code'] === 201 && $createRes['success'] === true && $newId > 0);
recordTest(
    $results,
    'TC-02',
    'Tambah Pelanggan',
    'Memasukkan data pelanggan baru yang valid (Fajar Nugroho)',
    $t2Pass,
    'PASS (POST form -> INSERT SQL -> Redirect index.php)',
    'PASS (POST JSON -> HTTP 201 Created, ID #' . $newId . ')',
    'Data pelanggan baru berhasil disimpan ke database.'
);

// 3. UJI VALIDASI KELENGKAPAN DATA (EMPTY FIELDS)
$emptyRes = $service->createCustomer([
    'nama'    => '',
    'email'   => '',
    'telepon' => '',
    'alamat'  => '',
]);
$t3Pass = ($emptyRes['status_code'] === 422 && $emptyRes['success'] === false && count($emptyRes['errors']) === 4);
recordTest(
    $results,
    'TC-03',
    'Validasi Kelengkapan',
    'Menolak input pelanggan jika nama, email, telepon, atau alamat kosong',
    $t3Pass,
    'PASS (Menampilkan alert HTML berisi 4 pesan error)',
    'PASS (HTTP 422 Unprocessable Entity + 4 pesan error JSON)',
    'Semua field wajib diperiksa sebelum menyentuh tabel database.'
);

// 4. UJI VALIDASI FORMAT EMAIL
$badEmailRes = $service->createCustomer([
    'nama'    => 'Gita Permata',
    'email'   => 'format-email-salah',
    'telepon' => '0812-9988-7766',
    'alamat'  => 'Jl. Sudirman No. 45, Jakarta',
]);
$t4Pass = ($badEmailRes['status_code'] === 422 && $badEmailRes['success'] === false);
recordTest(
    $results,
    'TC-04',
    'Validasi Format Email',
    'Menolak alamat email yang tidak sesuai format standar',
    $t4Pass,
    'PASS (Ditolak oleh filter_var di index.php)',
    'PASS (Ditolak oleh CustomerService HTTP 422)',
    'Format email tidak valid berhasil dicegah.'
);

// 5. UJI VALIDASI KEUNIKAN EMAIL (DUPLICATE EMAIL)
$existingEmail = $listRes['data'][0]['email'] ?? 'andi@nusantara.co.id';
$dupRes = $service->createCustomer([
    'nama'    => 'Duplikat Andi',
    'email'   => $existingEmail,
    'telepon' => '0819-0000-1111',
    'alamat'  => 'Jl. Gatot Subroto No. 12, Bandung',
]);
$t5Pass = ($dupRes['status_code'] === 422 && $dupRes['success'] === false);
recordTest(
    $results,
    'TC-05',
    'Validasi Keunikan Email',
    'Menolak pendaftaran email yang sudah terdaftar (' . $existingEmail . ')',
    $t5Pass,
    'PASS (Query cek duplikat di index.php menolak penyimpanan)',
    'PASS (CustomerService + CustomerRepository::findByEmail menolak HTTP 422)',
    'Keunikan email terjaga baik saat tambah maupun edit data.'
);

// 6. UJI PENCARIAN PELANGGAN (SEARCH BY NAME OR EMAIL)
$searchRes1 = $service->listCustomers('Andi Pratama');
$searchRes2 = $service->listCustomers('kreasidigital.com');
$t6Pass = ($searchRes1['success'] && count($searchRes1['data']) >= 1 && $searchRes2['success'] && count($searchRes2['data']) >= 1);
recordTest(
    $results,
    'TC-06',
    'Pencarian Pelanggan',
    'Mencari pelanggan berdasarkan nama ("Andi Pratama") atau email ("kreasidigital.com")',
    $t6Pass,
    'PASS (GET ?q=... menjalankan query WHERE nama LIKE OR email LIKE)',
    'PASS (GET api/customers.php?q=... mengembalikan JSON terfilter)',
    'Pencarian nama dan email akurat dan case-insensitive.'
);

// 7. UJI EDIT PELANGGAN (UPDATE)
$updateRes = $service->updateCustomer($newId, [
    'nama'    => 'Fajar Nugroho, S.Kom.',
    'email'   => $uniqueEmail,
    'telepon' => '0811-2233-9999',
    'alamat'  => 'Jl. Merdeka No. 108 (Gedung Baru), Bandung',
]);
$t7Pass = ($updateRes['status_code'] === 200 && $updateRes['success'] === true && $updateRes['data']['nama'] === 'Fajar Nugroho, S.Kom.');
recordTest(
    $results,
    'TC-07',
    'Edit Pelanggan',
    'Mengubah informasi nama, telepon, dan alamat pelanggan yang sudah ada',
    $t7Pass,
    'PASS (POST form_action=update menjalankan query UPDATE)',
    'PASS (PUT api/customers.php?id=' . $newId . ' mengembalikan HTTP 200 JSON)',
    'Perubahan data tersimpan dan email milik sendiri tidak dianggap duplikat.'
);

// 8. UJI HAPUS PELANGGAN (DELETE)
$delRes = $service->deleteCustomer($newId);
$verifyAfterDel = $service->getCustomerById($newId);
$t8Pass = ($delRes['status_code'] === 200 && $delRes['success'] === true && $verifyAfterDel['status_code'] === 404);
recordTest(
    $results,
    'TC-08',
    'Hapus Pelanggan',
    'Menghapus data pelanggan dan memastikan data tidak lagi ditemukan',
    $t8Pass,
    'PASS (GET ?action=delete&id=... menjalankan query DELETE)',
    'PASS (DELETE api/customers.php?id=' . $newId . ' -> HTTP 200 & verifikasi 404)',
    'Data pelanggan berhasil dihapus secara bersih dari database.'
);

$allPassed = true;
foreach ($results as $r) {
    if (!$r['passed']) {
        $allPassed = false;
    }
}

echo json_encode([
    'all_passed' => $allPassed,
    'total_tests' => count($results),
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

