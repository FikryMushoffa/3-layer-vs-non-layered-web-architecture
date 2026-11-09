/**
 * ============================================================================
 * LAYER 1: PRESENTATION LAYER — assets/js/app.js
 * Tugas : Menghubungkan tampilan HTML dengan Backend (Business Logic Layer)
 *         menggunakan REST API (fetch) dan format data JSON.
 * ============================================================================
 */

const URL_API = '../business-logic/api/customers.php';

// 1. Fungsi untuk mengambil & menampilkan daftar pelanggan (GET)
async function muatDataPelanggan(kataKunci = '') {
    let url = URL_API;
    if (kataKunci !== '') {
        url += '?q=' + encodeURIComponent(kataKunci);
    }

    const response = await fetch(url);
    const hasil = await response.json();

    // Tampilkan log JSON di panel bawah untuk bukti komunikasi REST API
    tampilkanLogJson('GET ' + url + ' (HTTP ' + response.status + ')', hasil);

    if (!hasil.success) {
        tampilkanError(hasil.errors || [hasil.message]);
    }

    const tbody = document.getElementById('isi-tabel');
    document.getElementById('jumlah-data').textContent = hasil.data ? hasil.data.length : 0;

    if (!hasil.data || hasil.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px;">Data pelanggan tidak ditemukan.</td></tr>';
        return;
    }

    tbody.innerHTML = hasil.data.map(row => `
        <tr>
            <td><strong>#${row.id}</strong></td>
            <td>
                <strong>${ row.nama }</strong><br>
                <small style="color:#1d4ed8;">${ row.email }</small>
            </td>
            <td>${ row.telepon }</td>
            <td>${ row.alamat }</td>
            <td>
                <button type="button" class="btn btn-edit" onclick="isiFormEdit(${row.id})">Edit</button>
                <button type="button" class="btn btn-hapus" onclick="hapusPelanggan(${row.id}, '${row.nama}')">Hapus</button>
            </td>
        </tr>
    `).join('');
}

// 2. Fungsi saat form Tambah / Edit disubmit (POST atau PUT dengan JSON)
document.getElementById('form-pelanggan').addEventListener('submit', async function (e) {
    e.preventDefault();
    sembunyikanPesan();

    const idEdit = document.getElementById('id_edit').value;
    const dataKirim = {
        nama: document.getElementById('nama').value,
        email: document.getElementById('email').value,
        telepon: document.getElementById('telepon').value,
        alamat: document.getElementById('alamat').value
    };

    // Jika id_edit terisi gunakan method PUT (Edit), jika kosong gunakan POST (Tambah)
    const method = idEdit !== '' ? 'PUT' : 'POST';
    const url = idEdit !== '' ? `${URL_API}?id=${idEdit}` : URL_API;

    const response = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dataKirim)
    });

    const hasil = await response.json();
    tampilkanLogJson(`${method} ${url} (HTTP ${response.status})`, hasil);

    if (hasil.success) {
        tampilkanSukses(hasil.message);
        resetForm();
        muatDataPelanggan(document.getElementById('input-cari').value);
    } else {
        tampilkanError(hasil.errors || [hasil.message]);
    }
});

// 3. Fungsi mengambil 1 data pelanggan ke dalam form untuk diedit (GET ?id=...)
async function isiFormEdit(id) {
    sembunyikanPesan();
    const url = `${URL_API}?id=${id}`;
    const response = await fetch(url);
    const hasil = await response.json();

    tampilkanLogJson(`GET ${url} (HTTP ${response.status})`, hasil);

    if (hasil.success && hasil.data) {
        document.getElementById('id_edit').value = hasil.data.id;
        document.getElementById('nama').value = hasil.data.nama;
        document.getElementById('email').value = hasil.data.email;
        document.getElementById('telepon').value = hasil.data.telepon;
        document.getElementById('alamat').value = hasil.data.alamat;

        document.getElementById('judul-form').textContent = 'Edit Pelanggan #' + hasil.data.id;
        document.getElementById('tombol-simpan').textContent = 'Simpan Perubahan';
        document.getElementById('tombol-batal').style.display = 'inline-block';
    }
}

// 4. Fungsi menghapus pelanggan berdasarkan ID (DELETE ?id=...)
async function hapusPelanggan(id, nama) {
    if (!confirm(`Hapus pelanggan "${nama}"?`)) return;
    sembunyikanPesan();

    const url = `${URL_API}?id=${id}`;
    const response = await fetch(url, { method: 'DELETE' });
    const hasil = await response.json();

    tampilkanLogJson(`DELETE ${url} (HTTP ${response.status})`, hasil);

    if (hasil.success) {
        tampilkanSukses(hasil.message);
        resetForm();
        muatDataPelanggan(document.getElementById('input-cari').value);
    } else {
        tampilkanError(hasil.errors || [hasil.message]);
    }
}

// 5. Fungsi pencarian berdasarkan nama atau email
document.getElementById('form-cari').addEventListener('submit', function (e) {
    e.preventDefault();
    const kataKunci = document.getElementById('input-cari').value.trim();
    muatDataPelanggan(kataKunci);
});

function resetPencarian() {
    document.getElementById('input-cari').value = '';
    muatDataPelanggan('');
}

// Fungsi bantu mereset form kembali ke mode Tambah
function resetForm() {
    document.getElementById('form-pelanggan').reset();
    document.getElementById('id_edit').value = '';
    document.getElementById('judul-form').textContent = 'Tambah Pelanggan Baru';
    document.getElementById('tombol-simpan').textContent = '+ Tambah Pelanggan';
    document.getElementById('tombol-batal').style.display = 'none';
}

// Fungsi bantu menampilkan pesan sukses / error
function tampilkanSukses(pesan) {
    const box = document.getElementById('box-sukses');
    box.textContent = '✔ ' + pesan;
    box.style.display = 'block';
}

function tampilkanError(daftarError) {
    const box = document.getElementById('box-error');
    const ul = document.getElementById('list-error');
    ul.innerHTML = daftarError.map(err => `<li>${err}</li>`).join('');
    box.style.display = 'block';
}

function sembunyikanPesan() {
    document.getElementById('box-sukses').style.display = 'none';
    document.getElementById('box-error').style.display = 'none';
}

function tampilkanLogJson(infoMethod, dataJson) {
    document.getElementById('info-endpoint').textContent = infoMethod;
    document.getElementById('isi-json').textContent = JSON.stringify(dataJson, null, 2);
}

// Panggil otomatis saat halaman pertama kali dibuka
muatDataPelanggan();
