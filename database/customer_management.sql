-- ============================================================================
-- FILE DATABASE SQL: Customer Management System
-- Tugas Kelompok : Perbandingan Layered vs Non-Layered Web Architecture
-- Cara Import    : Buka phpMyAdmin -> Buat/Pilih DB -> Menu Import -> Pilih file ini
-- ============================================================================

CREATE DATABASE IF NOT EXISTS customer_management;
USE customer_management;

-- Membuat tabel pelanggan (sesuai studi kasus)
DROP TABLE IF EXISTS pelanggan;

CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telepon VARCHAR(25) NOT NULL,
    alamat TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Memasukkan 5 data contoh pelanggan awal
INSERT INTO pelanggan (nama, email, telepon, alamat) VALUES
('Andi Pratama', 'andi@nusantara.co.id', '0812-3456-7890', 'Jl. Setiabudhi No. 229, Kota Bandung'),
('Budi Santoso', 'budi@teknologimaju.id', '0813-9876-5432', 'Jl. Asia Afrika No. 88, Kota Bandung'),
('Citra Lestari', 'citra@kreasidigital.com', '0821-5566-7788', 'Jl. Ir. H. Juanda (Dago) No. 145, Kota Bandung'),
('Dewi Anggraini', 'dewi@sinergiglobal.co.id', '0852-1122-3344', 'Jl. Soekarno Hatta No. 512, Kota Bandung'),
('Eko Prasetyo', 'eko@mitraniaga.id', '0878-4455-6677', 'Jl. Pasteur No. 32, Kota Bandung');
