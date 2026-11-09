<?php
/**
 * ============================================================================
 * APLIKASI 1: NON-LAYERED ARCHITECTURE (ARSITEKTUR MONOLITIK 1 FILE)
 * Studi Kasus : Customer Management System
 * Sesuai PDF  : Halaman 14, 15, 19, dan 20
 *
 * Ciri Utama  :
 * 1. Koneksi database MySQL dan query SQL ditulis langsung di dalam file ini.
 * 2. Proses bisnis (validasi kelengkapan & keunikan email) ada di file ini.
 * 3. Tampilan HTML digabungkan langsung dengan kode PHP di file ini.
 * 4. Tidak menggunakan REST API (menggunakan submit form biasa).
 * ============================================================================
 */

// ============================================================================
// BAGIAN 1: KONEKSI LANGSUNG KE DATABASE MYSQL
// ============================================================================
$host     = "localhost";
$dbname   = "customer_management";
$username = "root";
$password = "";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $koneksi = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // Jika database customer_management belum di-import di MySQL, otomatis jalankan file database/customer_management.sql
    $sqlFile = dirname(__DIR__) . "/database/customer_management.sql";
    if (strpos($e->getMessage(), "1049") !== false && file_exists($sqlFile)) {
        $pdoSetup = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, $options);
        $pdoSetup->exec(file_get_contents($sqlFile));
        $koneksi = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    } else {
        die("Koneksi MySQL gagal. Pastikan MySQL di XAMPP/Laragon sudah dinyalakan. Detail: " . $e->getMessage());
    }
}

// ============================================================================
// BAGIAN 2: PROSES BISNIS (VALIDASI) & QUERY CRUD KE DATABASE MYSQL
// ============================================================================
$pesan_error  = [];
$pesan_sukses = "";

// Variabel penampung nilai form (untuk Tambah maupun Edit)
$id_edit = "";
$nama    = "";
$email   = "";
$telepon = "";
$alamat  = "";

// --- FITUR 4: HAPUS PELANGGAN (Saat tombol Hapus diklik) ---
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    $stmt = $koneksi->prepare("DELETE FROM pelanggan WHERE id = ?");
    $stmt->execute([$id_hapus]);
    $pesan_sukses = "Data pelanggan berhasil dihapus dari database.";
}

// --- PERSIAPAN FITUR 3: AMBIL DATA DARI DATABASE UNTUK FORM EDIT ---
if (isset($_GET['edit'])) {
    $id_ambil = (int) $_GET['edit'];
    $stmt = $koneksi->prepare("SELECT * FROM pelanggan WHERE id = ?");
    $stmt->execute([$id_ambil]);
    $data_lama = $stmt->fetch();

    if ($data_lama) {
        $id_edit = $data_lama['id'];
        $nama    = $data_lama['nama'];
        $email   = $data_lama['email'];
        $telepon = $data_lama['telepon'];
        $alamat  = $data_lama['alamat'];
    }
}

// --- FITUR 1, 3, & 6: SIMPAN / UPDATE PELANGGAN & VALIDASI DATA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_edit = trim($_POST['id_edit'] ?? '');
    $nama    = trim($_POST['nama'] ?? '');
    $email   = strtolower(trim($_POST['email'] ?? ''));
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    // A. Validasi Kelengkapan Data (Semua kolom wajib diisi)
    if ($nama === '') {
        $pesan_error[] = "Nama pelanggan wajib diisi.";
    }
    if ($email === '') {
        $pesan_error[] = "Email pelanggan wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error[] = "Format alamat email tidak valid.";
    }
    if ($telepon === '') {
        $pesan_error[] = "Nomor telepon wajib diisi.";
    }
    if ($alamat === '') {
        $pesan_error[] = "Alamat pelanggan wajib diisi.";
    }

    // B. Validasi Keunikan Email (Cek langsung ke tabel pelanggan di MySQL)
    if (empty($pesan_error)) {
        if ($id_edit !== '') {
            // Saat Edit: cek apakah email dipakai oleh pelanggan selain dirinya sendiri
            $stmtCek = $koneksi->prepare("SELECT id FROM pelanggan WHERE LOWER(email) = ? AND id != ?");
            $stmtCek->execute([$email, (int) $id_edit]);
        } else {
            // Saat Tambah Baru: cek apakah email sudah ada di tabel pelanggan
            $stmtCek = $koneksi->prepare("SELECT id FROM pelanggan WHERE LOWER(email) = ?");
            $stmtCek->execute([$email]);
        }

        if ($stmtCek->fetch()) {
            $pesan_error[] = "Email '$email' sudah terdaftar di database. Gunakan email yang unik!";
        }
    }

    // C. Jika Validasi Lolos -> Jalankan Query INSERT atau UPDATE ke MySQL
    if (empty($pesan_error)) {
        if ($id_edit !== '') {
            // FITUR 3: EDIT PELANGGAN (UPDATE)
            $stmt = $koneksi->prepare("UPDATE pelanggan SET nama = ?, email = ?, telepon = ?, alamat = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $telepon, $alamat, (int) $id_edit]);
            $pesan_sukses = "Data pelanggan '$nama' berhasil diperbarui!";
        } else {
            // FITUR 1: TAMBAH PELANGGAN BARU (INSERT)
            $stmt = $koneksi->prepare("INSERT INTO pelanggan (nama, email, telepon, alamat) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $telepon, $alamat]);
            $pesan_sukses = "Pelanggan baru '$nama' berhasil ditambahkan ke database!";
        }

        // Kosongkan form kembali setelah berhasil disimpan
        $id_edit = $nama = $email = $telepon = $alamat = "";
    }
}

// --- FITUR 2 & 5: TAMPIL PELANGGAN & PENCARIAN DARI DATABASE MYSQL ---
$kata_kunci = trim($_GET['cari'] ?? '');
if ($kata_kunci !== '') {
    $stmt = $koneksi->prepare("SELECT * FROM pelanggan WHERE nama LIKE ? OR email LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$kata_kunci%", "%$kata_kunci%"]);
    $daftar_pelanggan = $stmt->fetchAll();
} else {
    $stmt = $koneksi->query("SELECT * FROM pelanggan ORDER BY id DESC");
    $daftar_pelanggan = $stmt->fetchAll();
}
?>
<!-- ==========================================================================
     BAGIAN 3: TAMPILAN HTML & CSS (Menyatu langsung dengan kode PHP di atas)
     ========================================================================== -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Customer Management System - Non-Layered Architecture</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background: #f4f6f9; margin: 0; padding: 0; color: #1e293b; }
        .header { background: #b45309; color: white; padding: 18px 30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; font-size: 14px; opacity: 0.9; }
        .container { max-width: 1150px; margin: 20px auto; padding: 0 20px; }
        .info-box { background: #fffbeb; border-left: 5px solid #d97706; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-size: 14px; }
        .alert-error { background: #fef2f2; border-left: 5px solid #dc2626; color: #991b1b; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; }
        .alert-success { background: #f0fdf4; border-left: 5px solid #16a34a; color: #166534; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-weight: bold; }
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; align-items: start; }
        @media (max-width: 850px) { .grid { grid-template-columns: 1fr; } }
        .card { background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card h2 { margin-top: 0; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 5px; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 14px; }
        .btn { display: inline-block; padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; }
        .btn-utama { background: #d97706; color: white; }
        .btn-utama:hover { background: #b45309; }
        .btn-batal { background: #e2e8f0; color: #334155; }
        .btn-edit { background: #dbeafe; color: #1d4ed8; padding: 5px 10px; }
        .btn-hapus { background: #fee2e2; color: #dc2626; padding: 5px 10px; }
        .search-form { display: flex; gap: 8px; margin-bottom: 14px; }
        .search-form input { flex: 1; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 10px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 12px; text-transform: uppercase; color: #475569; }
    </style>
</head>
<body>

<div class="header">
    <h1>Customer Management System — Aplikasi 1: Non-Layered Architecture</h1>
    <p>Pendekatan Monolitik Sederhana: HTML, Validasi Proses Bisnis, dan Query Database Menyatu dalam 1 File (index.php)</p>
</div>

<div class="container">
    <div class="info-box">
        <strong>Info Arsitektur:</strong> Pada aplikasi ini, seluruh proses mulai dari menampilkan form, menerima input, memvalidasi data, mengakses database MySQL, hingga menampilkan tabel pelanggan berada di dalam satu file <code>index.php</code>.
    </div>

    <!-- Menampilkan Pesan Error Validasi (Jika Ada) -->
    <?php if (!empty($pesan_error)): ?>
        <div class="alert-error">
            <strong>Validasi Gagal:</strong>
            <ul style="margin: 6px 0 0; padding-left: 20px;">
                <?php foreach ($pesan_error as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Menampilkan Pesan Sukses (Jika Ada) -->
    <?php if ($pesan_sukses !== ""): ?>
        <div class="alert-success">
            &#10004; <?= htmlspecialchars($pesan_sukses) ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <!-- KOLOM KIRI: FORM TAMBAH / EDIT PELANGGAN -->
        <div class="card">
            <h2><?= $id_edit !== '' ? "Edit Pelanggan #$id_edit" : "Tambah Pelanggan Baru" ?></h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="id_edit" value="<?= htmlspecialchars((string)$id_edit) ?>">

                <div class="form-group">
                    <label>Nama Pelanggan *</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($nama) ?>" placeholder="Contoh: Andi Pratama">
                </div>

                <div class="form-group">
                    <label>Email Pelanggan (Unik) *</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Contoh: andi@email.com">
                </div>

                <div class="form-group">
                    <label>Nomor Telepon *</label>
                    <input type="text" name="telepon" value="<?= htmlspecialchars($telepon) ?>" placeholder="Contoh: 0812-3456-7890">
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap *</label>
                    <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap..."><?= htmlspecialchars($alamat) ?></textarea>
                </div>

                <button type="submit" class="btn btn-utama">
                    <?= $id_edit !== '' ? "Simpan Perubahan" : "+ Tambah Pelanggan" ?>
                </button>
                <?php if ($id_edit !== ''): ?>
                    <a href="index.php" class="btn btn-batal">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- KOLOM KANAN: PENCARIAN & TABEL DATA PELANGGAN -->
        <div class="card">
            <h2>Daftar Pelanggan (Total: <?= count($daftar_pelanggan) ?>)</h2>

            <!-- Form Pencarian Berdasarkan Nama atau Email -->
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="cari" value="<?= htmlspecialchars($kata_kunci) ?>" placeholder="Cari berdasarkan nama atau email...">
                <button type="submit" class="btn btn-utama">Cari</button>
                <?php if ($kata_kunci !== ''): ?>
                    <a href="index.php" class="btn btn-batal">Reset</a>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama &amp; Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_pelanggan)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #64748b;">
                                Data pelanggan tidak ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_pelanggan as $row): ?>
                            <tr>
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
                                    <small style="color: #0369a1;"><?= htmlspecialchars($row['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['telepon']) ?></td>
                                <td><?= htmlspecialchars($row['alamat']) ?></td>
                                <td>
                                    <a href="index.php?edit=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                                    <a href="index.php?hapus=<?= $row['id'] ?>" class="btn btn-hapus" onclick="return confirm('Hapus pelanggan ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
