# Kuliner Khas Medan

Aplikasi web untuk menjelajahi kuliner khas **Kota Medan**: daftar restoran, detail dan menu, serta restoran terdekat dengan peta. Dibuat dengan CodeIgniter 4 dan PostgreSQL.

> Catatan data: harga menu masih **estimasi**, bukan harga asli. Status halal banyak yang belum terverifikasi. Penilaian "khas Medan" masih perlu disetujui tim.

## Fitur

- **Daftar restoran**: filter kategori, pencarian nama, urut rating, 20 per halaman.
- **Detail restoran**: info, tautan Google Maps, daftar menu, dan peta lokasi.
- **Restoran terdekat**: pilih titik patokan atau pakai lokasi browser, radius 1 sampai 5 km, hasil di peta dan daftar. Jarak dihitung garis lurus, bukan jarak tempuh.

## Kebutuhan

- PHP 8.2 atau lebih baru (PHP 8.5 butuh CodeIgniter 4.7.0 ke atas), dengan ekstensi `intl`, `mbstring`, `pgsql`, `curl`, `xml`
- Composer
- PostgreSQL
- Git

## Cara memasang

~~~bash
git clone https://github.com/Shofyawch/kuliner-medan-app.git
cd kuliner-medan-app
composer install
~~~

### 1. Buat database

~~~bash
sudo -u postgres createdb kuliner_medan
sudo -u postgres psql -d kuliner_medan < database/schema.sql
sudo -u postgres psql -d kuliner_medan < database/seed.sql
~~~

Cek hasilnya, harus 83 dan 305:

~~~bash
sudo -u postgres psql -d kuliner_medan -c "SELECT COUNT(*) FROM restoran;"
sudo -u postgres psql -d kuliner_medan -c "SELECT COUNT(*) FROM menu;"
~~~

Atur password pengguna postgres milikmu sendiri:

~~~bash
sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'isi_password_sendiri';"
~~~

### 2. Buat file `.env`

~~~bash
cp env .env
~~~

Lalu tambahkan di bagian bawah `.env` (ganti password dengan milikmu):

~~~
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = kuliner_medan
database.default.username = postgres
database.default.password = isi_password_sendiri
database.default.DBDriver = Postgre
database.default.port = 5432
database.default.charset = utf8
~~~

Baris `charset = utf8` wajib. Tanpa itu muncul error `invalid value for parameter "client_encoding"`.

File `.env` **jangan** diunggah ke GitHub dan password **jangan** dikirim lewat chat.

### 3. Jalankan

~~~bash
php spark serve
~~~

Buka http://localhost:8080.

## Struktur penting

| Berkas | Fungsi |
|---|---|
| `app/Controllers/Kuliner.php` | Daftar dan detail restoran |
| `app/Controllers/Terdekat.php` | Restoran terdekat |
| `app/Models/RestoranModel.php`, `MenuModel.php` | Model tabel |
| `app/Views/kuliner/` | Tampilan (index, detail, terdekat) |
| `app/Config/Routes.php` | Rute |
| `database/schema.sql`, `seed.sql` | Struktur dan isi database |

Rute: `/` (daftar), `/restoran/{slug}` (detail), `/terdekat` (terdekat).

## Database

- Tabel `restoran` dan `menu` (relasi lewat `restoran_id`).
- Fungsi `restoran_terdekat(lat, lng, radius_km, limit)` mengembalikan restoran terdekat dalam radius tertentu.
- Kolom `id` belum auto-increment; perlu diubah sebelum ada fitur tambah data dari aplikasi.

## Bekerja bareng

- Sebelum mulai: `git pull`.
- Setelah selesai: `git add .`, `git commit -m "pesan singkat"`, `git push`.
- Bagi tugas per bagian supaya tidak mengedit berkas yang sama.
- Perubahan data hanya ada di database masing-masing. Kalau data diubah, perbarui `database/seed.sql` lalu commit, atau sepakati satu orang pemegang data utama.

## Yang masih direncanakan

Perapian tampilan (UI/UX), filter lebih lengkap, halaman admin, foto restoran, perapian data bersama tim, dan deploy.

## Lisensi

MIT (lihat berkas `LICENSE`).
