<?php
/**
 * ============================================================================
 * LAYER 2: BUSINESS LOGIC LAYER — CustomerService.php
 * Tugas : Menangani aturan bisnis & validasi aplikasi Customer Management:
 *         1. Memeriksa kelengkapan input (nama, email, telepon, alamat)
 *         2. Memeriksa format email yang benar
 *         3. Memeriksa keunikan email (meminta bantuan Data Layer)
 *         4. Mengatur proses Tampil, Tambah, Edit, Hapus, dan Pencarian
 *
 * Catatan : Layer ini TIDAK menulis query SQL dan TIDAK membuat kode HTML.
 * ============================================================================
 */

require_once dirname(__DIR__) . '/data-access/CustomerRepository.php';

class CustomerService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new CustomerRepository();
    }

    // 1. Ambil daftar pelanggan (Semua atau hasil Pencarian Nama/Email)
    public function listCustomers($keyword = '')
    {
        $keyword = trim((string) $keyword);
        $data = ($keyword !== '')
            ? $this->repo->search($keyword)
            : $this->repo->findAll();

        return [
            'status_code' => 200,
            'success'     => true,
            'message'     => 'Data pelanggan berhasil diambil.',
            'meta'        => [
                'total_all'      => $this->repo->countAll(),
                'total_filtered' => count($data),
                'keyword'        => $keyword,
            ],
            'data'        => $data,
        ];
    }

    // 2. Ambil 1 data pelanggan berdasarkan ID
    public function getCustomerById($id)
    {
        $pelanggan = $this->repo->findById((int) $id);
        if (!$pelanggan) {
            return [
                'status_code' => 404,
                'success'     => false,
                'message'     => "Pelanggan dengan ID #$id tidak ditemukan.",
                'errors'      => ["Pelanggan dengan ID #$id tidak ditemukan."],
            ];
        }

        return [
            'status_code' => 200,
            'success'     => true,
            'message'     => 'Detail pelanggan ditemukan.',
            'data'        => $pelanggan,
        ];
    }

    // 3. Tambah pelanggan baru (dengan Validasi Kelengkapan & Keunikan Email)
    public function createCustomer($input)
    {
        $data   = $this->rapikanInput($input);
        $errors = $this->validasiData($data, null);

        // Jika validasi gagal, tolak penyimpanan & kirim daftar error
        if (!empty($errors)) {
            return [
                'status_code' => 422,
                'success'     => false,
                'message'     => 'Validasi data pelanggan gagal.',
                'errors'      => $errors,
            ];
        }

        // Jika validasi lolos, minta Data Layer menyimpan ke database
        $pelangganBaru = $this->repo->create($data);

        return [
            'status_code' => 201,
            'success'     => true,
            'message'     => "Pelanggan baru '{$data['nama']}' berhasil ditambahkan!",
            'data'        => $pelangganBaru,
        ];
    }

    // 4. Edit pelanggan (dengan Validasi Kelengkapan & Keunikan Email)
    public function updateCustomer($id, $input)
    {
        $id = (int) $id;
        $lama = $this->repo->findById($id);
        if (!$lama) {
            return [
                'status_code' => 404,
                'success'     => false,
                'message'     => "Pelanggan dengan ID #$id tidak ditemukan.",
                'errors'      => ["Data pelanggan tidak ditemukan."],
            ];
        }

        $data   = $this->rapikanInput($input);
        $errors = $this->validasiData($data, $id);

        if (!empty($errors)) {
            return [
                'status_code' => 422,
                'success'     => false,
                'message'     => 'Validasi perubahan data gagal.',
                'errors'      => $errors,
            ];
        }

        $pelangganUpdate = $this->repo->update($id, $data);

        return [
            'status_code' => 200,
            'success'     => true,
            'message'     => "Data pelanggan '{$data['nama']}' berhasil diperbarui!",
            'data'        => $pelangganUpdate,
        ];
    }

    // 5. Hapus pelanggan berdasarkan ID
    public function deleteCustomer($id)
    {
        $id = (int) $id;
        $lama = $this->repo->findById($id);
        if (!$lama) {
            return [
                'status_code' => 404,
                'success'     => false,
                'message'     => "Pelanggan dengan ID #$id tidak ditemukan.",
                'errors'      => ["Data pelanggan tidak ditemukan."],
            ];
        }

        $this->repo->delete($id);

        return [
            'status_code' => 200,
            'success'     => true,
            'message'     => "Data pelanggan '{$lama['nama']}' berhasil dihapus.",
            'data'        => ['deleted_id' => $id],
        ];
    }

    // Fungsi bantu: merapikan spasi dan huruf kecil pada email
    private function rapikanInput($input)
    {
        return [
            'nama'    => trim((string) ($input['nama'] ?? '')),
            'email'   => strtolower(trim((string) ($input['email'] ?? ''))),
            'telepon' => trim((string) ($input['telepon'] ?? '')),
            'alamat'  => trim((string) ($input['alamat'] ?? '')),
        ];
    }

    // Fungsi utama aturan bisnis: Validasi Kelengkapan & Keunikan Email
    private function validasiData($data, $excludeId = null)
    {
        $errors = [];

        // A. Cek kelengkapan seluruh kolom wajib
        if ($data['nama'] === '') {
            $errors[] = 'Nama pelanggan wajib diisi.';
        }
        if ($data['email'] === '') {
            $errors[] = 'Alamat email wajib diisi.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format alamat email tidak valid.';
        }
        if ($data['telepon'] === '') {
            $errors[] = 'Nomor telepon wajib diisi.';
        }
        if ($data['alamat'] === '') {
            $errors[] = 'Alamat pelanggan wajib diisi.';
        }

        // B. Cek keunikan email melalui Data Layer
        if ($data['email'] !== '' && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $duplikat = $this->repo->findByEmail($data['email'], $excludeId);
            if ($duplikat) {
                $errors[] = "Email '{$data['email']}' sudah digunakan oleh pelanggan '{$duplikat['nama']}'. Gunakan email yang unik!";
            }
        }

        return $errors;
    }
}
