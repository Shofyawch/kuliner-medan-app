<?php

namespace App\Controllers;

use App\Libraries\HotelService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Hotel extends BaseController
{
    private HotelService $hotelService;

    public function __construct()
    {
        $this->hotelService = new HotelService();
    }

    public function index()
    {
        $ambil = function (string $nama): ?string {
            $v = $this->request->getGet($nama);
            return is_string($v) && trim($v) !== '' ? trim($v) : null;
        };

        $cari       = $ambil('q');
        $kecamatan  = $ambil('kecamatan');
        $sarapan    = $ambil('sarapan');
        $ratingMin  = $ambil('rating_min');
        $page       = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $filter = [
            'page'  => $page,
            'limit' => 20,
        ];

        if ($cari) {
            $filter['q'] = $cari;
        }
        if ($kecamatan) {
            $filter['kecamatan'] = $kecamatan;
        }
        if ($sarapan === 'ya') {
            $filter['sarapan'] = 'ya';
        }
        if ($ratingMin && is_numeric($ratingMin)) {
            $filter['rating_min'] = (float) $ratingMin;
        }

        $resHotel       = $this->hotelService->getHotels($filter);
        $hotels         = $resHotel['data'] ?? [];
        $meta           = $resHotel['meta'] ?? ['page' => 1, 'limit' => 20, 'total' => count($hotels), 'total_pages' => 1];
        $daftarKecamatan = $this->hotelService->getKecamatan();

        return view('hotel/index', [
            'judul'           => 'Daftar Hotel & Penginapan di Medan',
            'hotels'          => $hotels,
            'meta'            => $meta,
            'daftarKecamatan' => $daftarKecamatan,
            'cari'            => $cari,
            'kecamatan'       => $kecamatan,
            'sarapan'         => $sarapan,
            'ratingMin'       => $ratingMin,
        ]);
    }

    public function detail(int $id)
    {
        $hotel = $this->hotelService->getHotelById($id);
        if ($hotel === null) {
            throw PageNotFoundException::forPageNotFound('Hotel tidak ditemukan di API Singgah Medan.');
        }

        $ulasan = $this->hotelService->getReviews($id);

        // Cari restoran kuliner terdekat dari hotel ini menggunakan koordinat GPS hotel
        $kulinerSekitar = [];
        if (!empty($hotel['latitude']) && !empty($hotel['longitude']) && is_numeric($hotel['latitude']) && is_numeric($hotel['longitude'])) {
            try {
                $db = db_connect();
                $semua = $db->query('SELECT * FROM restoran_terdekat(?, ?, ?, ?)', [
                    (float) $hotel['latitude'],
                    (float) $hotel['longitude'],
                    3.0, // radius 3 km
                    6,   // maks 6 restoran
                ])->getResultArray();
                $kulinerSekitar = $semua;
            } catch (\Throwable $e) {
                log_message('error', 'Gagal memuat restoran sekitar hotel: ' . $e->getMessage());
            }
        }

        return view('hotel/detail', [
            'judul'          => $hotel['nama'] . ' - Penginapan Medan',
            'h'              => $hotel,
            'ulasan'         => $ulasan,
            'kulinerSekitar' => $kulinerSekitar,
        ]);
    }

    public function kirimUlasan(int $id)
    {
        $rating = (int) $this->request->getPost('rating');
        $judul  = trim((string) $this->request->getPost('judul'));
        $isi    = trim((string) $this->request->getPost('isi'));

        if ($rating < 1 || $rating > 5 || $judul === '' || $isi === '') {
            return redirect()->to('/hotel/' . $id)
                ->with('error', 'Semua kolom ulasan wajib diisi (rating 1-5).');
        }

        $hasil = $this->hotelService->createReview($id, $rating, $judul, $isi);

        if ($hasil && ($hasil['status'] ?? '') === 'ok') {
            return redirect()->to('/hotel/' . $id)
                ->with('success', 'Ulasan Anda berhasil dikirim ke server Singgah Medan!');
        }

        return redirect()->to('/hotel/' . $id)
            ->with('error', 'Gagal mengirim ulasan ke server penginapan. Coba lagi nanti.');
    }

    public function hapusUlasan(int $hotelId, int $reviewId)
    {
        $hasil = $this->hotelService->deleteReview($reviewId, $hotelId);
        if ($hasil && ($hasil['status'] ?? '') === 'ok') {
            return redirect()->to('/hotel/' . $hotelId)
                ->with('success', 'Ulasan berhasil dihapus dari server penginapan.');
        }

        return redirect()->to('/hotel/' . $hotelId)
            ->with('error', 'Gagal menghapus ulasan dari server penginapan.');
    }
}

