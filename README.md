# Kuliner Khas Medan

Aplikasi untuk menjelajahi kuliner khas **Kota Medan**, terdiri dari satu REST API dan beberapa tampilan web di atas satu database PostgreSQL. Dibuat dengan CodeIgniter 4.

> Catatan data: harga menu masih **estimasi**. Status halal banyak yang belum terverifikasi.

## Fitur

- Daftar restoran dengan pencarian, filter kategori, kecamatan, harga, dan urutan.
- Detail restoran: menu, peta, ulasan, dan penginapan terdekat.
- Restoran terdekat dari titik patokan atau lokasi browser (jarak garis lurus).
- Daftar penginapan dari API Singgah Medan.
- Daftar akun, login, dan ulasan pengunjung.
- Dashboard admin: kelola restoran dan menu, moderasi ulasan restoran dan penginapan.

## Aplikasi dan alamat

| Aplikasi | Alamat | Cara mengambil data |
|---|---|---|
| Situs utama | `/` | Sebagian besar langsung dari database (sisi server) |
| Dashboard admin | `/admin` | REST API (setelah login admin) |
| Web Admin | `/web-admin.html` | REST API |
| Web Pengguna | `/web-publik.html` | REST API |
| REST API | `/api/v1` | lihat [docs/API.md](docs/API.md) |

## Kebutuhan

- PHP 8.2 atau lebih baru dengan ekstensi `intl`, `mbstring`, `pgsql`, `curl`, `xml`
- Composer
- PostgreSQL (tim memakai Supabase) dan klien `psql`
- Git

## Cara memasang

Langkah lengkap, termasuk membuat database dan akun admin, ada di [docs/PANDUAN-INSTALASI.md](docs/PANDUAN-INSTALASI.md).

~~~bash
git clone https://github.com/Shofyawch/kuliner-medan-app.git
cd kuliner-medan-app
composer install
cp .env.example .env    # lalu isi nilainya
php spark serve
~~~

Tambahkan juga baris `database.default.charset = utf8` di `.env`. Tanpa itu bisa muncul error `invalid value for parameter "client_encoding"`.

Buka http://localhost:8080. Kalau port 8080 tidak bisa dipakai, jalankan `php spark serve --host 127.0.0.1 --port 8081` dan ubah `app.baseURL` di `.env` mengikuti alamat itu.

File `.env` **jangan** diunggah ke GitHub, dan password atau kunci **jangan** dikirim lewat chat.

## Database

- Tabel: `restoran`, `menu`, `pengguna`, `ulasan`, `log_integrasi`.
- View: `v_restoran_ringkas`, `v_ulasan`.
- Function: `restoran_terdekat`, `tambah_restoran_dengan_menu` (satu transaksi).
- Trigger `trg_naikkan_versi`: menaikkan `versi` untuk mencegah perubahan saling menimpa.
- User database: `kuliner_api` (dipakai aplikasi) dan `kuliner_readonly` (hanya baca).

`database/schema.sql` **menghapus tabel lama**. Jangan dijalankan di database yang sudah berisi data.

## Dokumentasi

| Berkas | Isi |
|---|---|
| [docs/API.md](docs/API.md) | Semua endpoint |
| [docs/PANDUAN-INSTALASI.md](docs/PANDUAN-INSTALASI.md) | Cara memasang |
| [docs/DOKUMENTASI_INTEGRASI_API.md](docs/DOKUMENTASI_INTEGRASI_API.md) | Panduan untuk kelompok lain |
| [docs/erd-kuliner-medan.dbml](docs/erd-kuliner-medan.dbml) | ERD (impor ke dbdiagram.io) |
| [docs/kuliner-medan-api.postman_collection.json](docs/kuliner-medan-api.postman_collection.json) | Koleksi Postman |
| `docs/uji-*.sh`, `docs/bukti-rollback.sql` | Skrip uji |

## Bekerja bareng

- Sebelum mulai: `git pull`.
- Setelah selesai: `git add <berkas>`, `git commit -m "pesan singkat"`, `git pull`, `git push`.
- Bagi tugas per bagian supaya tidak mengedit berkas yang sama.

## Lisensi

MIT (lihat berkas `LICENSE`).
