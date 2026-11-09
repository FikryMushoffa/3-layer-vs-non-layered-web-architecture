# Tugas Kelompok: Analisis & Implementasi Perbandingan Layered vs Non-Layered Web Application Architecture

**Studi Kasus:** Customer Management System (Sistem Pengelolaan Data Pelanggan Perusahaan)  
**Acuan Materi:** [`3-Layer Web Application Architecture.pdf`](./3-Layer%20Web%20Application%20Architecture.pdf) (Halaman 1–22)  
**Teknologi:** PHP Native (Tanpa Framework), MySQL (PDO), HTML, CSS, JavaScript Fetch API (REST & JSON)

---

## 1. Peta Seluruh Luaran Wajib (Sesuai Halaman 22 PDF)

Seluruh **8 luaran wajib** yang diminta pada slide halaman 22 telah selesai dibuat secara lengkap:

| No. | Luaran yang Harus Dikumpulkan & Dipresentasikan | Lokasi File / Folder |
| :-: | :--- | :--- |
| **1** | **Source Code Aplikasi Non-Layered** | [`1-non-layered-app/index.php`](./1-non-layered-app/index.php) |
| **2** | **Source Code Aplikasi 3-Layer Architecture** | [`2-layered-app/`](./2-layered-app/) (`presentation/`, `business-logic/`, `data-access/`) |
| **3** | **File Database SQL** | [`database/customer_management.sql`](./database/customer_management.sql) |
| **4** | **Diagram Arsitektur Kedua Aplikasi** | [`diagrams/`](./diagrams/) (3 file diagram PNG resolusi tinggi) |
| **5** | **Screenshot Tampilan Kedua Aplikasi** | [`screenshots/`](./screenshots/) (6 screenshot fitur CRUD, Cari, & Validasi) |
| **6** | **Tabel Hasil Pengujian & Perbandingan** | [`pengujian/Tabel_Hasil_Pengujian_dan_Perbandingan.md`](./pengujian/Tabel_Hasil_Pengujian_dan_Perbandingan.md) |
| **7** | **Laporan Analisis (PDF)** | [`laporan/Laporan_Analisis_Layered_vs_Non_Layered_Architecture.pdf`](./laporan/Laporan_Analisis_Layered_vs_Non_Layered_Architecture.pdf) |
| **8** | **Slide Presentasi Kelompok (PPTX & PDF)** | [`presentasi/Slide_Presentasi_Kelompok_3Layer_vs_NonLayered.pptx`](./presentasi/Slide_Presentasi_Kelompok_3Layer_vs_NonLayered.pptx) & [`.pdf`](./presentasi/Slide_Presentasi_Kelompok_3Layer_vs_NonLayered.pdf) |

---

## 2. Ringkasan Konsep yang Mudah Dipahami (Untuk Bahan Presentasi)

### A. Apa Bedanya Non-Layered vs 3-Layer Architecture?
Bayangkan kita membuat aplikasi **Customer Management System** yang memiliki **6 fitur wajib** (Halaman 19 PDF):
1. **Tambah pelanggan** (memasukkan nama, email, telepon, alamat)
2. **Tampil pelanggan** (menampilkan seluruh daftar pelanggan)
3. **Edit pelanggan** (mengubah data pelanggan)
4. **Hapus pelanggan** (menghapus data pelanggan)
5. **Pencarian** (mencari pelanggan berdasarkan **nama** atau **email**)
6. **Validasi** (memeriksa **kelengkapan seluruh kolom** dan **keunikan email** agar tidak ada 2 pelanggan dengan email yang sama)

Kedua aplikasi memiliki tampilan dan fitur yang **sama persis**, tetapi cara menyusun kodenya berbeda:

#### 1) Aplikasi Pertama: Non-Layered Architecture ([`1-non-layered-app/index.php`](./1-non-layered-app/index.php))
- **Konsep (Halaman 14–15 & 20 PDF):** Disebut juga arsitektur **monolitik satu file**.
- **Cara Kerja:** Seluruh kode ditempatkan di dalam **1 file PHP saja (`index.php`)**:
  - **Bagian 1 (Baris atas):** Koneksi langsung ke database MySQL (`new PDO(...)`).
  - **Bagian 2 (Baris tengah):** Proses bisnis (cek apakah input kosong, cek apakah email sudah dipakai) sekaligus langsung menjalankan query SQL (`INSERT`, `UPDATE`, `DELETE`, `SELECT`).
  - **Bagian 3 (Baris bawah):** Kode tampilan HTML dan CSS untuk menampilkan form dan tabel pelanggan.
- **Komunikasi:** Tidak memakai REST API. Setiap tombol Simpan/Cari/Hapus diklik, browser me-*reload* ulang seluruh halaman `index.php`.

#### 2) Aplikasi Kedua: 3-Layer Architecture ([`2-layered-app/`](./2-layered-app/))
- **Konsep (Halaman 2–13 & 21 PDF):** Membagi aplikasi menjadi **3 lapisan (layer)** dengan tugas dan tanggung jawab yang terpisah tegas, serta berkomunikasi menggunakan **REST API dan JSON**:
  1. **Layer 1 — Presentation Layer (Frontend):**
     - Lokasi: [`2-layered-app/presentation/index.html`](./2-layered-app/presentation/index.html), [`style.css`](./2-layered-app/presentation/assets/css/style.css), dan [`app.js`](./2-layered-app/presentation/assets/js/app.js).
     - Tugas: Hanya mengurus tampilan yang dilihat pengguna. Tidak ada kode PHP dan tidak ada query SQL sama sekali. Mengirim dan mengambil data ke Backend menggunakan `fetch()` berformat **JSON**.
  2. **Layer 2 — Business Logic Layer (Backend & REST API):**
     - Lokasi: [`CustomerService.php`](./2-layered-app/business-logic/CustomerService.php) dan [`api/customers.php`](./2-layered-app/business-logic/api/customers.php).
     - Tugas: Menerima permintaan JSON dari Frontend (`GET`, `POST`, `PUT`, `DELETE`), memeriksa aturan bisnis (apakah semua kolom sudah diisi? apakah format email benar? apakah email sudah dipakai pelanggan lain?), lalu mengembalikan hasil dalam bentuk **JSON**.
  3. **Layer 3 — Data Layer (Database Access):**
     - Lokasi: [`Database.php`](./2-layered-app/data-access/Database.php) dan [`CustomerRepository.php`](./2-layered-app/data-access/CustomerRepository.php).
     - Tugas: Satu-satunya bagian yang berhubungan dengan database MySQL. Berisi fungsi-fungsi query SQL (`findAll`, `search`, `findById`, `findByEmail`, `create`, `update`, `delete`).

---

## 3. Jawaban 5 Pertanyaan Diskusi (Halaman 17 PDF)

Berikut jawaban lengkap dan mudah dipahami untuk menjawab pertanyaan dosen saat presentasi:

1. **Mana yang lebih mudah dibuat?**
   - **Aplikasi Non-Layered lebih mudah dan cepat dibuat di awal** untuk aplikasi kecil, karena kita cukup menulis koneksi database, validasi, query SQL, dan tampilan HTML di dalam satu file `index.php` tanpa perlu membuat REST API atau membagi banyak file.
2. **Mana yang lebih mudah diperbaiki ketika terjadi kesalahan (bug)?**
   - **Aplikasi 3-Layer jauh lebih mudah diperbaiki.** Karena tugas tiap layer sudah dipisah:
     - Kalau tampilan tombol/tabel berantakan $\rightarrow$ cukup periksa folder `presentation/`.
     - Kalau aturan validasi email salah $\rightarrow$ cukup periksa `CustomerService.php` di `business-logic/`.
     - Kalau query SQL salah $\rightarrow$ cukup periksa `CustomerRepository.php` di `data-access/`.
3. **Mana yang lebih mudah dikembangkan ketika fitur bertambah?**
   - **Aplikasi 3-Layer jauh lebih mudah dikembangkan.** Ketika fitur bertambah (misalnya ingin membuat aplikasi Android/iOS atau menambah fitur baru), kita cukup memakai ulang endpoint REST API JSON (`api/customers.php`) tanpa perlu membongkar kode yang sudah ada.
4. **Apa risiko jika seluruh kode ditempatkan dalam satu file?**
   - Kode menjadi sangat panjang dan bercampur aduk (*spaghetti code*).
   - Mengubah desain HTML berisiko tidak sengaja menghapus/merusak kode validasi PHP atau query SQL.
   - Sulit dikerjakan bersama-sama dalam satu tim (rawan *conflict* saat *merge* kode).
   - Tidak bisa langsung dipakai oleh aplikasi Mobile karena outputnya berupa HTML, bukan JSON.
5. **Apakah aplikasi tanpa 3-layer selalu lebih buruk?**
   - **Tidak selalu.** Untuk aplikasi yang sangat kecil, skrip sederhana satu halaman, atau prototipe cepat (*MVP*), pendekatan Non-Layered justru praktis dan tidak berbelit-belit. Namun, untuk aplikasi perusahaan yang terus berkembang dan dikelola jangka panjang, **3-Layer Architecture jauh lebih baik**.

---

## 4. Cara Menjalankan Kedua Aplikasi

1. **Nyalakan Apache & MySQL** di XAMPP atau Laragon.
2. **Import Database SQL** ke MySQL / phpMyAdmin:
   - Buka `http://localhost/phpmyadmin`
   - Klik menu **Import** $\rightarrow$ pilih file [`database/customer_management.sql`](./database/customer_management.sql) $\rightarrow$ klik **Import / Go**.
   - *(Atau lewat terminal MySQL: `mysql -u root < database/customer_management.sql`)*.
3. **Jalankan Aplikasi:**
   - Jika memakai folder `htdocs` XAMPP / `www` Laragon, langsung buka dari browser.
   - Atau jalankan langsung dari folder ini dengan perintah:
     ```powershell
     php -S localhost:8000
     ```
4. **Buka di Browser:**
   - **Aplikasi 1 (Non-Layered):** `http://localhost:8000/1-non-layered-app/index.php`
   - **Aplikasi 2 (3-Layer Architecture):** `http://localhost:8000/2-layered-app/presentation/index.html`
   - **Cek Output JSON REST API:** `http://localhost:8000/2-layered-app/business-logic/api/customers.php`
