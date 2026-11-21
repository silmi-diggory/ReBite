# ReBite

**Rescue food, share happiness.**

ReBite adalah aplikasi web untuk membagikan makanan **sisa** yang masih layak konsumsi, misalnya kelebihan masak, sisa produksi, atau stok yang mendekati batas jual. Penyedia memposting makanan, penerima mengklaimnya sebelum batas waktu ambil, dan aplikasi menghitung berapa banyak makanan yang berhasil diselamatkan.

Proyek ini dibuat sebagai Responsi 2 (Proyek Akhir) Praktikum Pemrograman Web I, Jurusan Informatika, Fakultas Teknik, Universitas Jenderal Soedirman.

## Latar belakang dan tujuan

Warung, katering, kantin, dan rumah tangga sering memiliki makanan sisa yang masih layak tetapi akhirnya terbuang, sementara ada mahasiswa dan warga yang membutuhkan makanan. ReBite mempertemukan keduanya dalam satu tempat.

Tujuan:
- Mengurangi makanan layak yang terbuang.
- Membantu pemenuhan kebutuhan pangan.
- Mencatat dampak dalam jumlah porsi makanan yang diselamatkan.

Kaitan dengan SDGs:
- **SDG 12** Konsumsi dan Produksi yang Bertanggung Jawab (utama)
- **SDG 2** Tanpa Kelaparan (pendukung)

## Peran pengguna

| Peran | Yang bisa dilakukan |
| --- | --- |
| Admin | Memverifikasi penyedia, mengelola kategori dan pengguna, melihat dashboard |
| Penyedia | Memposting, mengubah, dan menghapus makanan, melihat riwayat makanan yang dibagikan |
| Penerima | Melihat katalog, mengklaim dan membatalkan klaim, melihat riwayat klaim |

## Daftar fitur dan modul

| # | Modul | Fitur utama |
| --- | --- | --- |
| 1 | Registrasi dan login | Daftar sebagai penyedia atau penerima, login, logout, session |
| 2 | Hak akses | Pembatasan halaman per peran |
| 3 | Profil pengguna | Lihat dan ubah data diri |
| 4 | CRUD postingan makanan | Penyedia menambah, melihat, mengubah, dan menghapus makanan |
| 5 | Katalog makanan | Daftar makanan tersedia |
| 6 | Klaim makanan | Penerima mengklaim, porsi berkurang, pembatalan klaim |
| 7 | Riwayat | Riwayat klaim penerima dan riwayat makanan penyedia |
| 8 | Dashboard dampak | Statistik makanan yang diselamatkan |
| 9 | Manajemen admin | Kategori, verifikasi penyedia, kelola pengguna |

## Teknologi

- PHP (native) dan MySQL
- HTML, CSS, dan JavaScript
- XAMPP (Apache dan MySQL) sebagai server lokal
- Git dan GitHub untuk kolaborasi

## Struktur folder

```
rebite/
├── index.php
├── dashboard.php
├── katalog.php
├── profil.php
├── profil_edit.php
├── auth/          register, login, logout
├── penyedia/      kelola makanan dan riwayat berbagi
├── penerima/      klaim, pembatalan, riwayat klaim
├── admin/         kategori, verifikasi penyedia, kelola pengguna
├── includes/      koneksi, header, menu, footer
├── assets/        css dan gambar
└── database/      database.sql
```

## Instalasi dan konfigurasi

Yang dibutuhkan:
- XAMPP (Apache dan MySQL)
- Git
- Browser seperti Google Chrome
- Visual Studio Code (opsional)

Langkah:

1. **Clone repository** ke folder `htdocs` milik XAMPP.

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/USERNAME/rebite.git
   ```

   Ganti `USERNAME` dengan nama akun GitHub pemilik repo. Pastikan nama foldernya `rebite`.

2. **Jalankan XAMPP.** Buka XAMPP Control Panel, lalu klik **Start** pada **Apache** dan **MySQL**.

3. **Buat database.** Buka `http://localhost/phpmyadmin`, klik **New**, lalu buat database bernama `rebite` dengan collation `utf8mb4_general_ci`.

4. **Impor struktur dan data contoh.** Pilih database `rebite`, buka tab **Import**, pilih file `database/database.sql`, lalu klik **Go**.

5. **Cek konfigurasi koneksi.** Buka `includes/koneksi.php` dan pastikan isinya sesuai pengaturan XAMPP kamu. Pengaturan bawaan XAMPP adalah:

   | Pengaturan | Nilai |
   | --- | --- |
   | Server | `localhost` |
   | Username | `root` |
   | Password | (kosong) |
   | Nama database | `rebite` |

## Cara menjalankan

1. Pastikan Apache dan MySQL berstatus berjalan di XAMPP.
2. Buka browser dan akses:

   ```
   http://localhost/rebite/
   ```

3. Daftar akun baru atau login memakai akun contoh di bawah, lalu mulai gunakan aplikasi sesuai peran.

### Akun contoh

Semua akun contoh memakai password `rebite123`.

| Peran | Nama | Email |
| --- | --- | --- |
| Admin | Kakashi Hatake | `admin@rebite.test` |
| Penyedia | Warung Ichiraku | `ichiraku@rebite.test` |
| Penyedia | Katering Totoro | `totoro@rebite.test` |
| Penyedia | Kantin Fakultas Teknik | `kantin.ft@rebite.test` |
| Penyedia (menunggu verifikasi admin) | Bakery Kiki | `kiki@rebite.test` |
| Penerima | Naruto Uzumaki | `naruto@rebite.test` |
| Penerima | Mikasa Ackerman | `mikasa@rebite.test` |
| Penerima | Tanjiro Kamado | `tanjiro@rebite.test` |
| Penerima | Hinata Hyuga | `hinata@rebite.test` |
| Penerima | Zoro Roronoa | `zoro@rebite.test` |
| Penerima | Nezuko Kamado | `nezuko@rebite.test` |

### Alur singkat untuk mencoba

1. Daftar sebagai penyedia, lalu minta admin memverifikasi akun tersebut.
2. Penyedia memposting makanan sisa beserta jumlah porsi, lokasi, dan batas waktu ambil.
3. Penerima membuka katalog dan mengklaim makanan. Jumlah porsi berkurang otomatis.
4. Penyedia menandai makanan sudah diambil.
5. Dashboard menampilkan total makanan yang diselamatkan.

## Pemecahan masalah

| Masalah | Penyebab dan solusi |
| --- | --- |
| Apache tidak bisa start | Port 80 dipakai aplikasi lain. Tutup aplikasi tersebut atau ubah port di XAMPP |
| MySQL tidak bisa start | Port 3306 dipakai layanan MySQL lain. Hentikan layanan itu terlebih dahulu |
| Muncul tulisan "Gagal koneksi" | Periksa `includes/koneksi.php`, pastikan MySQL berjalan dan database `rebite` sudah dibuat |
| Halaman tidak ditemukan (404) | Pastikan nama folder di `htdocs` sama dengan alamat yang dibuka |
| Halaman kosong | Aktifkan tampilan error PHP atau lihat log di `C:\xampp\apache\logs\error.log` |

## Tim pengembang

| Nama | Peran |
| --- | --- |
| (Hanifah Silmi Fajri) | Akun, pengguna, session, hak akses, database |
| (Mutiara Gita Ramadhanti) | Makanan, katalog, admin, tampilan bersama |
| (Muhammad Doli Muda Siregar) | Klaim, riwayat, dashboard |

Dibuat untuk Responsi 2 Praktikum Pemrograman Web I, Informatika Universitas Jenderal Soedirman, 2026.