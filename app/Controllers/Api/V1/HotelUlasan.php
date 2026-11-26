<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Libraries\HotelService;

/**
 * Moderasi ulasan penginapan (terintegrasi dengan API Singgah Medan) khusus admin.
 *  GET    /api/v1/hotel               daftar ringkas hotel untuk filter
 *  GET    /api/v1/hotel/ulasan        daftar ulasan hotel (opsional ?hotel_id=)
 *  DELETE /api/v1/hotel/ulasan/{id}   hapus ulasan hotel
 */
class HotelUlasan extends BaseController
{
    use ResponsApi;

    private HotelService $hotelService;

    public function __construct()
    {
        $this->hotelService = new HotelService();
    }

    public function daftarHotel()
    {
        $res = $this->hotelService->getHotels(['limit' => 100]);
        $data = array_map(static fn (array $h) => [
            'id'   => (int) ($h['id'] ?? 0),
            'nama' => (string) ($h['nama'] ?? 'Hotel #' . ($h['id'] ?? '')),
        ], $res['data'] ?? []);

        return $this->sukses($data);
    }

    public function index()
    {
        $hotelId = (int) ($this->request->getGet('hotel_id') ?? 0);
        $perPage = min(max((int) ($this->request->getGet('per_page') ?: 15), 1), 50);
        $page    = max((int) ($this->request->getGet('page') ?: 1), 1);

        $res = $this->hotelService->getAllReviews($hotelId > 0 ? $hotelId : null, $page, $perPage);
        if (($res['status'] ?? '') === 'error') {
            return $this->gagal('Gagal mengambil ulasan penginapan dari server Singgah Medan.', 502);
        }

        // Petakan nama hotel agar tetap muncul meskipun API reviews hanya mengirim hotel_id
        $hotelsRes = $this->hotelService->getHotels(['limit' => 100]);
        $namaHotel = [];
        foreach ($hotelsRes['data'] ?? [] as $h) {
            if (isset($h['id'])) {
                $namaHotel[(int) $h['id']] = (string) ($h['nama'] ?? '');
            }
        }

        $rows = array_map(static function (array $u) use ($namaHotel): array {
            $hid = (int) ($u['hotel_id'] ?? 0);
            return [
                'id'       => (int) ($u['id'] ?? 0),
                'hotel_id' => $hid,
                'hotel'    => (string) ($u['hotel_nama'] ?? $u['hotel'] ?? ($namaHotel[$hid] ?? ('Hotel #' . $hid))),
                'penulis'  => (string) ($u['penulis'] ?? $u['username'] ?? 'Pengunjung Web Kuliner'),
                'rating'   => (int) ($u['rating'] ?? 5),
                'judul'    => (string) ($u['judul'] ?? ''),
                'isi'      => (string) ($u['isi'] ?? $u['komentar'] ?? ''),
                'dibuat'   => (string) ($u['created_at'] ?? ''),
            ];
        }, $res['data'] ?? []);

        $meta = $res['meta'] ?? [];
        $total = (int) ($meta['total'] ?? count($rows));

        return $this->sukses($rows, [
            'page'        => (int) ($meta['page'] ?? $page),
            'per_page'    => (int) ($meta['limit'] ?? $perPage),
            'total'       => $total,
            'total_pages' => max(1, (int) ($meta['total_pages'] ?? ceil($total / $perPage))),
        ]);
    }

    public function delete($id = 0)
    {
        $id = (int) $id;
        if ($id < 1) {
            return $this->gagal('ID ulasan tidak valid.', 400);
        }

        $hotelId = (int) ($this->request->getGet('hotel_id') ?? 0);
        $hasil = $this->hotelService->deleteReview($id, $hotelId);

        if ($hasil && ($hasil['status'] ?? '') === 'ok') {
            return $this->sukses(['id' => $id, 'hotel_id' => $hotelId]);
        }

        return $this->gagal('Gagal menghapus ulasan penginapan dari server Singgah Medan.', 502);
    }
}
