# Panduan Instalasi - Kuliner Khas Medan

## Prasyarat
PHP 8.2 atau lebih baru (ekstensi intl, mbstring, pgsql), Composer, klien `psql`, dan database PostgreSQL (kami memakai Supabase).

## Langkah
1. `git clone` repo ini, lalu `composer install`.
2. `cp .env.example .env`, lalu isi nilainya. Buat JWT_SECRET dengan `openssl rand -hex 32`.
3. Isi database, SEKALI SAJA dan berurutan (schema.sql menghapus tabel lama):
   `database/schema.sql`, `seed.sql`, `foto.sql`, `halal.sql`, `deskripsi.sql`,
   `migrasi_01.sql` (beri `-v pw_api=... -v pw_ro=...`), lalu `migrasi_02.sql`.
   Migrasi dijalankan sebagai pemilik database; aplikasi memakai user `kuliner_api`.
4. Buat akun admin: simpan hash dengan `php -r 'echo password_hash("PASSWORD", PASSWORD_DEFAULT);'`
   lalu `INSERT INTO pengguna (username, password_hash, role) VALUES ('admin', '<hash>', 'admin');`
5. Jalankan: `php spark serve`

## Alamat
- Situs utama: http://localhost:8080/
- Aplikasi 1 (Web Admin): http://localhost:8080/web-admin.html
- Aplikasi 2 (Web Pengguna): http://localhost:8080/web-publik.html
- API: http://localhost:8080/api/v1

## Pengujian
`bash docs/uji-api.sh`, `bash docs/uji-ulasan.sh`, `bash docs/uji-penguatan.sh`, atau impor
`docs/kuliner-medan-api.postman_collection.json` ke Postman. ERD: `docs/erd-kuliner-medan.dbml` (dbdiagram.io).
