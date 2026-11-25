# 📖 Dokumentasi Integrasi REST API — Kuliner Khas Medan (Kelompok Kuliner)

Dokumentasi ini ditujukan untuk **Kelompok Penginapan (Singgah Medan)** agar dapat mengintegrasikan data restoran kuliner khas Medan, mencari kuliner/tempat makan terdekat dari lokasi hotel, serta menampilkan detail restoran dan daftar menu langsung di aplikasi web penginapan Anda.

---

## 🔑 Kredensial & Konfigurasi API

REST API kami dibangun menggunakan **CodeIgniter 4 (PHP)** dengan database **PostgreSQL** dan telah mendukung geolokasi GPS serta autentikasi API Key.

* **Base URL API:** `https://<URL_HOST_ANDA>/api/v1`  
  *(Ganti `<URL_HOST_ANDA>` dengan URL tunnel Ngrok atau Pinggy yang sedang aktif dari server Kuliner)*
* **Format Response:** JSON (Standard RESTful)
* **Header Wajib:**
  ```http
  X-API-KEY: 2fd919b37898dc69fef56b2f540317e87b88820aa082b97e6a38bc3ace94df91
  Accept: application/json
  ```
* **CORS:** Diaktifkan (`Access-Control-Allow-Origin: *`), dapat dipanggil langsung dari JavaScript browser (Fetch / Axios) maupun backend server PHP (cURL / file_get_contents).
* **Kunci Penghubung Antar Kelompok:**
  1. **Koordinat GPS** (`lat` dan `lng` / `latitude` dan `longitude`): Untuk mencari restoran kuliner terdekat dari hotel Anda secara presisi (otomatis dihitung jarak garis lurus `jarak_km`).
  2. **`kecamatan`** (contoh: `"Medan Barat"`, `"Medan Petisah"`, `"Medan Kota"`, dll.): Untuk sinkronisasi dan filter wilayah.

---

## 🍽️ 1. Mencari Kuliner Terdekat dari Hotel (FITUR UTAMA)

Endpoint ini dirancang khusus agar web penginapan Anda dapat menampilkan rekomendasi **"Tempat Makan / Kuliner Terdekat di Sekitar Hotel Ini"** cukup dengan mengirimkan titik koordinat GPS (`latitude` & `longitude`) dari hotel Anda.

* **Method:** `GET`
* **URL:** `https://<URL_HOST_ANDA>/api/v1/restoran/terdekat`
* **Query Parameters:**
  * `lat` *(wajib)*: Latitude GPS hotel (angka antara -90 s/d 90, contoh: `3.592594`)
  * `lng` *(wajib)*: Longitude GPS hotel (angka antara -180 s/d 180, contoh: `98.676002`)
  * `radius` *(opsional)*: Jarak radius maksimal dalam kilometer (default: `2`, maks: `20`, disarankan: `3`)
  * `limit` *(opsional)*: Jumlah maksimal tempat kuliner yang ingin ditampilkan (default: `10`, disarankan: `3` sampai `5`)

### Contoh URL Panggilan:
```http
GET https://<URL_HOST_ANDA>/api/v1/restoran/terdekat?lat=3.592594&lng=98.676002&radius=3&limit=5
```

### Contoh Response JSON:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nama": "Soto Kesawan Enak",
      "slug": "soto-kesawan-enak",
      "alamat": "Jl. Ahmad Yani No. 116, Kesawan",
      "kecamatan": "Medan Barat",
      "lokasi": {
        "lat": 3.593120,
        "lng": 98.675430
      },
      "kategori": "Soto",
      "jenis_masakan": "Tradisional Medan",
      "harga": {
        "min": 25000,
        "max": 40000,
        "catatan": "estimasi"
      },
      "rating": 4.8,
      "jumlah_ulasan": 342,
      "jarak_km": 0.18,
      "info_halal": "Halal",
      "foto": "/img/restoran/soto-kesawan.jpg"
    },
    {
      "id": 4,
      "nama": "Restoran Tip Top",
      "slug": "restoran-tip-top",
      "alamat": "Jl. Jenderal Ahmad Yani No. 92, Kesawan",
      "kecamatan": "Medan Barat",
      "lokasi": {
        "lat": 3.588820,
        "lng": 98.681150
      },
      "kategori": "Kue & Bakery",
      "jenis_masakan": "Kolonial & Khas",
      "harga": {
        "min": 30000,
        "max": 75000,
        "catatan": "estimasi"
      },
      "rating": 4.6,
      "jumlah_ulasan": 520,
      "jarak_km": 0.45,
      "info_halal": "Halal",
      "foto": "/img/restoran/tip-top.jpg"
    }
  ],
  "meta": {
    "total": 2
  }
}
```

---

## 📋 2. Mengambil Daftar Semua Restoran & Filter (READ)

### A. Ambil Semua Restoran (dengan Pagination & Pencarian)
* **Method:** `GET`
* **URL:** `https://<URL_HOST_ANDA>/api/v1/restoran`
* **Query Parameter Opsional:**
  * `page` (default: 1)
  * `per_page` (default: 20, maks: 50)
  * `q` (kata kunci pencarian nama restoran, misal: `soto`)
  * `kategori` (filter nama kategori, misal: `Bihun Bebek`, `Lontong Medan`, `Durian`, dll.)

### B. Ambil Daftar Kategori Kuliner Khas
* **Method:** `GET`
* **URL:** `https://<URL_HOST_ANDA>/api/v1/kategori`
* **Contoh Response JSON:**
```json
{
  "status": "success",
  "data": [
    { "nama": "Soto Medan", "jumlah": 18 },
    { "nama": "Bihun Bebek", "jumlah": 14 },
    { "nama": "Lontong Medan", "jumlah": 12 },
    { "nama": "Durian & Olahan", "jumlah": 9 }
  ]
}
```

### C. Ambil Detail Restoran Tertentu & Daftar Menu Lengkap
* **Method:** `GET`
* **URL:** `https://<URL_HOST_ANDA>/api/v1/restoran/{id}`  *(atau gunakan `{slug}`)*
* **Contoh:**
  `https://<URL_HOST_ANDA>/api/v1/restoran/1`
* **Response JSON:** Berisi informasi alamat lengkap, nomor telepon, link Google Maps, deskripsi, serta seluruh daftar menu makanan dan minuman beserta harganya.

---

## 💬 3. Membaca Ulasan Pengunjung Restoran

Untuk menampilkan ulasan pengunjung pada restoran kuliner tertentu di situs web penginapan Anda:

* **Method:** `GET`
* **URL:** `https://<URL_HOST_ANDA>/api/v1/restoran/{id}/ulasan`
* **Contoh Response JSON:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 10,
      "penulis": "Rizky Pratama",
      "rating": 5,
      "komentar": "Rasanya otentik khas Medan dan sangat dekat dengan hotel tempat menginap!",
      "created_at": "2026-11-24 14:10:00"
    }
  ]
}
```

---

## 💻 Contoh Implementasi Pemanggilan Kode

### Contoh Menggunakan PHP (CodeIgniter 4 / Native cURL):
*Cocok dipasang di Controller Penginapan Anda untuk menampilkan rekomendasi kuliner di bawah detail hotel:*

```php
<?php
// Contoh memanggil kuliner terdekat dari hotel (di Controller Penginapan Anda)
$hotelLat = 3.592594;
$hotelLng = 98.676002;

$apiUrl = 'https://<URL_HOST_ANDA>/api/v1/restoran/terdekat?' . http_build_query([
    'lat'    => $hotelLat,
    'lng'    => $hotelLng,
    'radius' => 3, // radius 3 km
    'limit'  => 4, // tampilkan 4 rekomendasi
]);

$options = [
    'http' => [
        'method' => 'GET',
        'header' => "X-API-KEY: 2fd919b37898dc69fef56b2f540317e87b88820aa082b97e6a38bc3ace94df91\r\n" .
                    "ngrok-skip-browser-warning: 1\r\n" .
                    "Accept: application/json\r\n",
        'timeout' => 8,
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
];

$context = stream_context_create($options);
$response = @file_get_contents($apiUrl, false, $context);

if ($response !== false) {
    $result = json_decode($response, true);
    if (($result['status'] ?? '') === 'success') {
        $kulinerTerdekat = $result['data'];
        // Tampilkan daftar kuliner terdekat di view hotel Anda!
        print_r($kulinerTerdekat);
    }
}
```

### Contoh Menggunakan JavaScript (Fetch):
*Cocok jika ingin dipanggil secara langsung di frontend browser:*

```javascript
const BASE_KULINER_URL = 'https://<URL_HOST_ANDA>/api/v1';
const API_KEY = '2fd919b37898dc69fef56b2f540317e87b88820aa082b97e6a38bc3ace94df91';

// Mengambil kuliner terdekat dari titik GPS hotel
async function getKulinerTerdekat(lat, lng) {
  try {
    const res = await fetch(`${BASE_KULINER_URL}/restoran/terdekat?lat=${lat}&lng=${lng}&radius=3&limit=4`, {
      method: 'GET',
      headers: {
        'X-API-KEY': API_KEY,
        'ngrok-skip-browser-warning': '1',
        'Accept': 'application/json'
      }
    });
    const result = await res.json();
    if (result.status === 'success') {
      console.log('Rekomendasi Kuliner Terdekat:', result.data);
      return result.data;
    }
  } catch (error) {
    console.error('Gagal mengambil data kuliner:', error);
  }
}
```

---

## 📞 Kontak Integrasi
Jika ada kendala koneksi API, kebutuhan format data tambahan, atau pertanyaan integrasi, silakan hubungi tim **Kuliner Khas Medan**.

