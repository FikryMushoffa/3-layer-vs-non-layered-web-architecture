# Tabel Hasil Pengujian dan Perbandingan Akhir
## Studi Kasus: Customer Management System (Non-Layered vs 3-Layer Web Application Architecture)

Dokumen ini memuat **Satu Tabel Utama Hasil Pengujian & Perbandingan** sesuai arahan dosen pengampu, yang merangkum:
1. **Unsur-unsur apa saja yang diuji coba** (struktur file/folder, kecepatan pembuatan awal, pengerjaan individu vs tim, kemudahan penelusuran kesalahan/bug, kecepatan respons UI, keamanan edit desain, kemudahan penambahan fitur, dan reusabilitas API).
2. **Bagaimana hasilnya pada aplikasi Tanpa Layer (Non-Layered)** vs **Menggunakan 3-Layer**.
3. **Siapa yang lebih unggul** pada masing-masing unsur pengujian.
4. **Alasan mengapa** arsitektur tersebut lebih unggul.

---

## 1. Tabel Hasil Pengujian & Perbandingan Arsitektur

| No | Unsur yang Diuji Coba | Tanpa Layer (Non-Layered) | Menggunakan 3-Layer | Siapa yang Lebih Unggul? | Alasannya Kenapa? |
| :-: | :--- | :--- | :--- | :---: | :--- |
| **1** | **Struktur Pemisahan File & Folder** | Seluruh kode (HTML, CSS, validasi PHP, koneksi PDO, & query SQL) menyatu di dalam **1 file** (`1-non-layered-app/index.php`). | Terpisah tegas ke dalam **3 folder**: `presentation/` (UI), `business-logic/` (Service & API), dan `data-access/` (SQL). | **3-Layer Architecture** | Struktur kode sangat rapi dan setiap file/folder memiliki tanggung jawab yang jelas (*Separation of Concerns*). |
| **2** | **Kecepatan Pembuatan Awal (Aplikasi Kecil)** | **Sangat cepat** dibuat karena programmer cukup menulis satu file `index.php` tanpa perlu membuat endpoint API JSON. | Membutuhkan waktu awal lebih lama karena harus menyiapkan 3 folder, REST API (`customers.php`), dan `fetch()` JS. | **Non-Layered Architecture** | Untuk program kecil atau tugas sederhana, membuat 1 file langsung jalan tanpa perlu konfigurasi antar-layer. |
| **3** | **Pengerjaan Individu vs Kerja Tim** | Hanya cocok dikerjakan **1 orang (individu)**. Jika dikerjakan tim secara bersamaan pada 1 file `index.php`, kode akan saling bentrok (*conflict*). | Sangat cocok untuk **kerja tim**. Anggota 1 mengerjakan folder `presentation/`, Anggota 2 di `business-logic/`, Anggota 3 di `data-access/`. | **3-Layer Architecture** | Setiap anggota tim bekerja di folder/file yang berbeda sehingga tidak saling mengganggu pekerjaan anggota lain. |
| **4** | **Kemudahan Penelusuran Kesalahan (Debugging)** | **Sulit ditelusuri** karena ketika muncul error, kita harus mencari di antara ratusan baris HTML, PHP, dan SQL yang bercampur di 1 file. | **Sangat mudah dilacak** karena lokasi error langsung terisolasi sesuai foldernya (apakah di tampilan JS, validasi Service, atau query SQL). | **3-Layer Architecture** | Jika salah tampilan cek `presentation/`, jika salah aturan validasi cek `CustomerService.php`, jika salah query cek `CustomerRepository.php`. |
| **5** | **Kecepatan Respons Antarmuka (UI)** | Lebih lambat karena setiap tambah/edit/hapus/cari pelanggan harus **memuat ulang seluruh halaman** (*Full Page Reload*). | **Lebih cepat dan mulus** karena browser hanya mengirim & menerima data teks ringan berformat **JSON** via `fetch()` tanpa *reload* halaman. | **3-Layer Architecture** | Pertukaran data menggunakan REST API & JSON hanya memperbarui isi tabel HTML secara dinamis tanpa me-refresh seluruh halaman. |
| **6** | **Keamanan Saat Mengubah Desain Tampilan (HTML/CSS)** | **Rawan merusak program**, karena saat mengedit tag HTML/CSS di `index.php`, kita bisa tidak sengaja menghapus variabel PHP atau query SQL di atasnya. | **Sangat aman**, karena desainer web cukup mengedit `index.html` dan `style.css` yang murni berisi kode tampilan (0% kode PHP/SQL). | **3-Layer Architecture** | Kode tampilan di folder `presentation/` terpisah 100% dari logika bisnis PHP maupun query database MySQL. |
| **7** | **Kemudahan Penambahan Fitur Baru (Skalabilitas)** | Semakin banyak fitur baru ditambahkan, file `index.php` menjadi semakin panjang, rumit, dan sulit dipelihara (*spaghetti code*). | Mudah dikembangkan cukup dengan menambah fungsi baru di `CustomerRepository.php` dan `CustomerService.php` tanpa merusak kode lama. | **3-Layer Architecture** | Penambahan fitur baru terorganisir per layer sehingga aplikasi mudah dikembangkan untuk jangka panjang. |
| **8** | **Kemampuan Dipakai Ulang oleh Aplikasi Lain (Reusabilitas)** | **Tidak bisa** dipakai oleh aplikasi Mobile (Android/iOS) karena output dari `index.php` berupa halaman HTML utuh, bukan data JSON. | **Bisa langsung dipakai ulang** oleh aplikasi Android, iOS, atau sistem lain karena `api/customers.php` menghasilkan **JSON standar**. | **3-Layer Architecture** | Business Logic Layer menyediakan REST API berformat JSON yang dapat diakses lintas platform (Web & Mobile). |

---

## 2. Bukti Visual Konsep Kode & Tampilan Web

### A. Screenshot Code (Mewakili Konsep Non-Layered vs 3-Layer)
1. **Screenshot Code Non-Layered (`1-non-layered-app/index.php`):** [`screenshots/0_screenshot_code_non_layered.png`](../screenshots/0_screenshot_code_non_layered.png)
   - Menunjukkan koneksi PDO MySQL, validasi PHP, query SQL (`SELECT`, `INSERT`, `UPDATE`, `DELETE`), CSS, dan HTML yang menyatu di dalam **1 file**.
2. **Screenshot Code 3-Layer (`presentation/`, `business-logic/`, `data-access/`):** [`screenshots/0_screenshot_code_3_layer.png`](../screenshots/0_screenshot_code_3_layer.png)
   - Menunjukkan pemisahan kode ke dalam 3 folder berbeda: `app.js` (Presentation Layer), `CustomerService.php` (Business Logic Layer), dan `CustomerRepository.php` (Data Layer).

### B. Diagram Arsitektur (Pemisahan File/Folder & Hubungan HTML, CSS, JS, PHP, SQL)
1. **Diagram Arsitektur Non-Layered:** [`diagrams/1_diagram_arsitektur_non_layered.png`](../diagrams/1_diagram_arsitektur_non_layered.png)
2. **Diagram Arsitektur 3-Layer:** [`diagrams/2_diagram_arsitektur_3_layer.png`](../diagrams/2_diagram_arsitektur_3_layer.png)
3. **Diagram Perbandingan Alur Request & Response:** [`diagrams/3_diagram_perbandingan_alur_request.png`](../diagrams/3_diagram_perbandingan_alur_request.png)

---

## 3. Kesimpulan Akhir Perbandingan

- **Non-Layered Architecture Lebih Unggul dalam hal:** **Kecepatan pembuatan awal** untuk aplikasi yang sangat kecil, sederhana, dan dikerjakan oleh **1 orang (individu)**.
- **3-Layer Architecture Lebih Unggul dalam hal:** **Struktur pemisahan file/folder**, **kerja tim paralel**, **kemudahan penelusuran kesalahan (debugging)**, **kecepatan respons UI (JSON tanpa reload)**, **keamanan saat mengubah desain HTML/CSS**, **kemudahan penambahan fitur baru**, serta **reusabilitas REST API** untuk platform lain.
