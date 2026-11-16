# Dokumentasi API Kuliner Khas Medan (v1)

API baca-saja (GET) untuk data restoran dan menu kuliner khas Kota Medan.

## Dasar

- Alamat dasar: `http://HOST/api/v1` (ganti HOST dengan alamat server; produksi wajib HTTPS)
- Format: JSON
- Semua endpoint wajib header `X-API-KEY`. Kunci diminta ke pemilik sistem dan **hanya boleh dipakai dari sisi server**, jangan ditaruh di JavaScript browser atau di GitHub.

## Format respons

Sukses:

~~~json
{ "status": "success", "data": "...", "meta": { } }
~~~

Gagal:

~~~json
{ "status": "error", "message": "penjelasan", "errors": { "lat": "..." } }
~~~

| Kode | Arti |
|---|---|
| 200 | Berhasil |
| 401 | API key tidak ada atau tidak valid |
| 404 | Restoran tidak ditemukan |
| 422 | Parameter tidak valid |
| 503 | API belum dikonfigurasi di server |

## Endpoint

### GET /kategori

Daftar kategori beserta jumlah restoran.

### GET /restoran

Daftar restoran, urut rating tertinggi.

| Parameter | Keterangan |
|---|---|
| `kategori` | Filter nama kategori persis (ambil dari `/kategori`) |
| `q` | Cari sebagian nama restoran |
| `page` | Halaman, mulai 1 (bawaan 1) |
| `per_page` | Jumlah per halaman, 1 sampai 50 (bawaan 20) |

`meta` berisi `page`, `per_page`, `total`, dan `total_pages`.

### GET /restoran/terdekat

Restoran terdekat dari satu titik, urut dari yang paling dekat.

| Parameter | Keterangan |
|---|---|
| `lat` | Wajib, -90 sampai 90 |
| `lng` | Wajib, -180 sampai 180 |
| `radius` | Kilometer, 0.1 sampai 20 (bawaan 2) |
| `limit` | Jumlah maksimal, 1 sampai 50 (bawaan 10) |

Setiap item punya `jarak_km`. Jarak adalah **garis lurus**, bukan jarak tempuh lewat jalan.

### GET /restoran/{slug}

Detail satu restoran, termasuk `menu`, `deskripsi`, `no_telepon`, dan `link_maps`.

## Contoh

~~~bash
curl -H "X-API-KEY: KUNCI_ANDA" \
  "http://HOST/api/v1/restoran/terdekat?lat=3.575278&lng=98.683889&radius=1&limit=3"
~~~

## Catatan data

- Harga masih **estimasi**, bukan harga asli.
- `info_halal` banyak yang "Belum terverifikasi"; jangan ditampilkan seolah sudah halal.
- `foto` masih kosong (`null`) untuk sebagian besar restoran.
- Penilaian "khas Medan" masih perlu disetujui tim.
