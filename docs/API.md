# Dokumentasi API Kuliner Khas Medan (v1)

REST API untuk data restoran, menu, akun, dan ulasan kuliner Kota Medan. API juga dipakai untuk moderasi ulasan penginapan dari API Singgah Medan.

Koleksi uji siap pakai: [`kuliner-medan-api.postman_collection.json`](kuliner-medan-api.postman_collection.json).

## Dasar

- Alamat dasar: `http://HOST/api/v1` (di komputer sendiri: `http://127.0.0.1:8080/api/v1`, sesuaikan port). Di server produksi gunakan HTTPS.
- Format kirim dan terima: JSON (`Content-Type: application/json`).

## Autentikasi

| Cara | Header | Boleh untuk |
|---|---|---|
| Token login (JWT) | `Authorization: Bearer <token>` | Sesuai role: `admin` atau `user` |
| API key | `X-API-KEY: <kunci>` | Hanya membaca data restoran |

Token didapat dari `POST /auth/login` dan berlaku selama `JWT_TTL` detik (bawaan 7200). API key diberikan oleh pemilik sistem. Jangan taruh API key asli di GitHub atau di JavaScript browser.

Kolom **Akses** di bawah:

- **Publik**: tanpa autentikasi.
- **Baca**: token atau API key.
- **Login**: token (`user` atau `admin`).
- **Admin**: token dengan role `admin`.

## Daftar endpoint

| Method | Alamat | Akses | Fungsi |
|---|---|---|---|
| POST | `/auth/login` | Publik | Masuk, mendapat token |
| POST | `/auth/daftar` | Publik | Daftar akun (role `user`) |
| GET | `/kategori` | Baca | Daftar kategori |
| GET | `/restoran` | Baca | Daftar restoran |
| GET | `/restoran/terdekat` | Baca | Restoran terdekat |
| GET | `/restoran/{slug}` | Baca | Detail restoran dan menu |
| POST | `/restoran` | Admin | Tambah restoran |
| POST | `/restoran/lengkap` | Admin | Tambah restoran dan menu dalam satu transaksi |
| PUT, PATCH | `/restoran/{id}` | Admin | Ubah restoran |
| DELETE | `/restoran/{id}` | Admin | Hapus restoran, menu dan ulasannya ikut terhapus |
| POST | `/restoran/{id}/menu` | Admin | Tambah menu |
| PUT, PATCH | `/menu/{id}` | Admin | Ubah menu |
| DELETE | `/menu/{id}` | Admin | Hapus menu |
| GET | `/restoran/{id}/ulasan` | Publik | Ulasan satu restoran |
| POST | `/restoran/{id}/ulasan` | Login | Tulis ulasan |
| PUT, PATCH | `/ulasan/{id}` | Login | Ubah ulasan sendiri (admin boleh semua) |
| DELETE | `/ulasan/{id}` | Login | Hapus ulasan sendiri (admin boleh semua) |
| GET | `/ulasan` | Admin | Semua ulasan, untuk moderasi |
| GET | `/hotel` | Admin | Daftar ringkas penginapan |
| GET | `/hotel/ulasan` | Admin | Ulasan penginapan |
| DELETE | `/hotel/ulasan/{id}` | Admin | Hapus ulasan penginapan |

## Format respons

Berhasil (`meta` hanya ada pada sebagian endpoint):

~~~json
{ "status": "success", "data": {}, "meta": {} }
~~~

Gagal (`errors` hanya ada kalau ada rincian per kolom):

~~~json
{ "status": "error", "message": "Data tidak valid.", "errors": { "nama": "..." } }
~~~

| Kode | Arti |
|---|---|
| 200 | Berhasil |
| 201 | Data baru dibuat |
| 400 | Body bukan JSON, atau ID tidak valid |
| 401 | Belum login, token salah atau kedaluwarsa, API key salah, password salah |
| 403 | Tidak punya hak untuk aksi ini |
| 404 | Data atau alamat tidak ditemukan |
| 409 | Bentrok: versi lama, data ganda, atau username sudah dipakai |
| 422 | Isian tidak valid |
| 429 | Terlalu banyak permintaan (lihat header `Retry-After`) |
| 500 | Kesalahan di server |
| 502 | API Singgah Medan gagal dihubungi |
| 503 | API belum dikonfigurasi di server |

**Batas permintaan per alamat IP:** `/auth/*` 20 per menit, endpoint lain 240 per menit. Bisa diubah lewat `RATE_AUTH` dan `RATE_UMUM`.

## Konflik update (penguncian optimistis)

Restoran, menu, dan ulasan punya kolom `versi` yang naik otomatis setiap kali diubah. Saat `PUT` atau `PATCH`, kirim `versi` terakhir yang diterima. Kalau data sudah diubah pihak lain, jawabannya `409`:

~~~json
{
  "status": "error",
  "message": "Konflik: data sudah diubah oleh pihak lain. Ambil data terbaru lalu ulangi perubahanmu.",
  "errors": { "versi_dikirim": 1, "versi_sekarang": 2 }
}
~~~

## Akun

### POST /auth/login

~~~json
{ "username": "namaku", "password": "passwordku" }
~~~

Jawaban `data`: `token`, `tipe` (`Bearer`), `berlaku_detik`, `username`, `role`.

### POST /auth/daftar

Body sama dengan login. `username` 3 sampai 30 karakter (huruf, angka, `-`, `_`), `password` 8 sampai 72 karakter. Jawaban `201`, atau `409` kalau username sudah dipakai.

## Membaca data restoran

### GET /kategori

Setiap item: `nama`, `jumlah`.

### GET /restoran

Urut rating tertinggi.

| Parameter | Keterangan |
|---|---|
| `kategori` | Nama kategori persis (ambil dari `/kategori`) |
| `q` | Sebagian nama restoran |
| `page` | Mulai 1 (bawaan 1) |
| `per_page` | 1 sampai 50 (bawaan 20) |

Setiap item: `id`, `nama`, `slug`, `alamat`, `kecamatan`, `lokasi` (`lat`, `lng`), `kategori`, `jenis_masakan`, `harga` (`min`, `max`, `catatan`), `rating`, `jumlah_ulasan`, `versi`, `info_halal`, `foto`.

`meta`: `page`, `per_page`, `total`, `total_pages`.

### GET /restoran/terdekat

| Parameter | Keterangan |
|---|---|
| `lat` | Wajib, -90 sampai 90 |
| `lng` | Wajib, -180 sampai 180 |
| `radius` | Kilometer, 0.1 sampai 20 (bawaan 2) |
| `limit` | 1 sampai 50 (bawaan 10) |

Setiap item punya `jarak_km` (garis lurus, bukan jarak tempuh).

### GET /restoran/{slug}

Isi seperti daftar, ditambah `deskripsi`, `no_telepon`, `link_maps`, `catatan_harga`, dan `menu`. Setiap menu: `id`, `versi`, `nama`, `harga`, `kategori`, `deskripsi`.

## Mengelola restoran (admin)

### POST /restoran

| Kolom | Wajib | Aturan |
|---|---|---|
| `nama` | ya | 3 sampai 150 karakter |
| `alamat` | ya | 5 sampai 300 karakter |
| `kecamatan` | ya | maksimal 80 |
| `latitude` | ya | -90 sampai 90 |
| `longitude` | ya | -180 sampai 180 |
| `kategori_khas` | ya | maksimal 100 |
| `jenis_masakan` | ya | maksimal 100 |
| `harga_min`, `harga_max` | tidak | bilangan bulat, min tidak boleh melebihi max |
| `info_halal` | tidak | maksimal 100 |
| `link_foto` | tidak | maksimal 255 |
| `link_maps` | tidak | URL http atau https, maksimal 600 |
| `deskripsi` | tidak | maksimal 1000 |
| `no_telepon` | tidak | maksimal 30 |

`slug` dibuat otomatis. Jawaban tambah dan ubah memakai nama kolom `harga_min` dan `harga_max` (tidak digabung dalam `harga`).

### POST /restoran/lengkap

Restoran dan semua menu disimpan dalam satu transaksi (fungsi database `tambah_restoran_dengan_menu`). Kalau satu bagian gagal, semuanya dibatalkan.

~~~json
{
  "restoran": {
    "nama": "Warung Contoh", "alamat": "Jl. Contoh No. 1", "kecamatan": "Medan Kota",
    "latitude": 3.59, "longitude": 98.67,
    "kategori_khas": "Mie Khas Medan", "jenis_masakan": "Mie"
  },
  "menu": [ { "nama_menu": "Mie Goreng", "harga": 20000, "kategori": "Makanan" } ]
}
~~~

Jawaban `201`: `id`, `slug`, `jumlah_menu`, `catatan`.

### PUT /restoran/{id}

Kirim `versi` dan kolom yang diubah saja.

~~~json
{ "versi": 1, "nama": "Nama Baru" }
~~~

### DELETE /restoran/{id}

Jawaban `data`: `id`, `nama`, `menu_ikut_terhapus`.

## Mengelola menu (admin)

| Kolom | Wajib | Aturan |
|---|---|---|
| `nama_menu` | ya | 2 sampai 150 karakter, tidak boleh ganda dalam satu restoran |
| `harga` | ya | bilangan bulat, 0 atau lebih |
| `kategori` | ya | maksimal 80 |
| `status_harga` | tidak | maksimal 80 |
| `deskripsi` | tidak | maksimal 500 |

`POST /restoran/{id}/menu` menambah menu. `PUT /menu/{id}` wajib menyertakan `versi`. `DELETE /menu/{id}` menghapus.

## Ulasan restoran

Isi: `rating` (wajib, 1 sampai 5) dan `komentar` (opsional, maksimal 500 karakter). Satu pengguna hanya boleh satu ulasan per restoran; kiriman kedua dibalas `409`.

~~~json
{ "rating": 5, "komentar": "Enak dan porsinya pas." }
~~~

Setiap ulasan: `id`, `restoran_id`, `restoran`, `pengguna`, `rating`, `komentar`, `versi`, `dibuat`, `diubah`.

Daftar memakai `page` dan `per_page` (1 sampai 50, bawaan 20). `meta` pada `GET /restoran/{id}/ulasan` juga berisi `rata_rata_rating`.

## Ulasan penginapan (admin)

Data berasal dari API Singgah Medan (`HOTEL_API_URL`). Penghapusan berlaku di server mereka.

- `GET /hotel`: `id` dan `nama` penginapan.
- `GET /hotel/ulasan?hotel_id=&page=&per_page=`: `hotel_id` opsional, `per_page` bawaan 15. Setiap item: `id`, `hotel_id`, `hotel`, `penulis`, `rating`, `judul`, `isi`, `dibuat`.
- `DELETE /hotel/ulasan/{id}?hotel_id=`

## Contoh dengan curl

~~~bash
# 1. Login, lalu salin nilai data.token
curl -X POST http://127.0.0.1:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"USERNAME","password":"PASSWORD"}'

# 2. Pakai token
curl http://127.0.0.1:8080/api/v1/restoran?per_page=5 \
  -H "Authorization: Bearer TOKEN"

# 3. Baca dengan API key
curl "http://127.0.0.1:8080/api/v1/restoran/terdekat?lat=3.575278&lng=98.683889&radius=1" \
  -H "X-API-KEY: KUNCI_ANDA"
~~~

## Catatan data

- Harga masih **estimasi**, bukan harga asli.
- Banyak `info_halal` yang belum terverifikasi; jangan ditampilkan seolah sudah halal.
