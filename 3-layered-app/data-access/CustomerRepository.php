<?php
/**
 * ============================================================================
 * LAYER 3: DATA LAYER — CustomerRepository.php
 * Tugas : Menjalankan query SQL (SELECT, INSERT, UPDATE, DELETE, Pencarian)
 *         ke tabel `pelanggan`.
 *         Dipanggil oleh Business Logic Layer (CustomerService.php).
 * ============================================================================
 */

require_once __DIR__ . '/Database.php';

class CustomerRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // 1. Mengambil seluruh data pelanggan (Fitur Tampil Pelanggan)
    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM pelanggan ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    // 2. Mencari pelanggan berdasarkan Nama atau Email (Fitur Pencarian)
    public function search($keyword)
    {
        $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE nama LIKE ? OR email LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        return $stmt->fetchAll();
    }

    // 3. Mengambil 1 pelanggan berdasarkan ID (Untuk Edit / Cek Hapus)
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE id = ?");
        $stmt->execute([(int) $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // 4. Mencari pelanggan berdasarkan Email (Untuk Validasi Keunikan Email)
    public function findByEmail($email, $excludeId = null)
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE LOWER(email) = LOWER(?) AND id != ?");
            $stmt->execute([$email, (int) $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE LOWER(email) = LOWER(?)");
            $stmt->execute([$email]);
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // 5. Menyimpan pelanggan baru ke database (Fitur Tambah Pelanggan)
    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO pelanggan (nama, email, telepon, alamat) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['nama'],
            $data['email'],
            $data['telepon'],
            $data['alamat'],
        ]);
        $idBaru = (int) $this->db->lastInsertId();
        return $this->findById($idBaru);
    }

    // 6. Mengubah data pelanggan di database (Fitur Edit Pelanggan)
    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE pelanggan SET nama = ?, email = ?, telepon = ?, alamat = ? WHERE id = ?");
        $stmt->execute([
            $data['nama'],
            $data['email'],
            $data['telepon'],
            $data['alamat'],
            (int) $id,
        ]);
        return $this->findById((int) $id);
    }

    // 7. Menghapus data pelanggan dari database (Fitur Hapus Pelanggan)
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM pelanggan WHERE id = ?");
        $stmt->execute([(int) $id]);
        return $stmt->rowCount() > 0;
    }

    // Menghitung total seluruh pelanggan
    public function countAll()
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
    }
}
