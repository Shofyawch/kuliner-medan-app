<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pembantu format response dan validasi, dipakai bersama oleh controller API.
 */
trait ResponsApi
{
    protected function sukses($data, array $meta = [], int $kode = 200): ResponseInterface
    {
        $body = ['status' => 'success', 'data' => $data];
        if ($meta !== []) {
            $body['meta'] = $meta;
        }

        return $this->response->setStatusCode($kode)->setJSON($body);
    }

    protected function gagal(string $pesan, int $kode, array $detail = []): ResponseInterface
    {
        $body = ['status' => 'error', 'message' => $pesan];
        if ($detail !== []) {
            $body['errors'] = $detail;
        }

        return $this->response->setStatusCode($kode)->setJSON($body);
    }

    /** Baca body JSON. Mengembalikan null kalau kosong atau bukan JSON objek. */
    protected function bodyJson(): ?array
    {
        try {
            $data = $this->request->getJSON(true);
        } catch (\Throwable $e) {
            return null;
        }

        return is_array($data) ? $data : null;
    }

    /**
     * Validasi data. $sebagian = true hanya memeriksa kolom yang dikirim
     * (untuk update). Mengembalikan null kalau valid, atau daftar error.
     */
    protected function validasi(array $data, array $aturan, bool $sebagian = false): ?array
    {
        if ($sebagian) {
            $aturan = array_intersect_key($aturan, $data);
        }
        if ($aturan === []) {
            return null;
        }

        service('language')->setLocale('id');
        $v = service('validation');
        $v->reset();
        $v->setRules($aturan);

        return $v->run($data) ? null : $v->getErrors();
    }

    /** Ubah kesalahan database menjadi response yang aman (tanpa detail server). */
    protected function galatDb(\Throwable $e): ResponseInterface
    {
        $pesan = strtolower($e->getMessage());
        log_message('error', 'Galat API: ' . $e->getMessage());

        if (str_contains($pesan, 'duplicate key') || str_contains($pesan, 'unique')) {
            return $this->gagal('Data bentrok dengan data yang sudah ada. Seluruh perubahan dibatalkan.', 409);
        }
        if (str_contains($pesan, 'check constraint') || str_contains($pesan, 'violates')) {
            return $this->gagal('Data melanggar aturan database. Seluruh perubahan dibatalkan.', 422);
        }
        if (str_contains($pesan, 'permission denied')) {
            return $this->gagal('Akun database aplikasi tidak berhak melakukan aksi ini.', 403);
        }

        return $this->gagal('Terjadi kesalahan pada server.', 500);
    }

    protected function ringkasRestoran(array $r): array
    {
        return [
            'id'            => (int) $r['id'],
            'nama'          => $r['nama'],
            'slug'          => $r['slug'],
            'alamat'        => $r['alamat'],
            'kecamatan'     => $r['kecamatan'],
            'lokasi'        => ['lat' => (float) $r['latitude'], 'lng' => (float) $r['longitude']],
            'kategori'      => $r['kategori_khas'],
            'jenis_masakan' => $r['jenis_masakan'],
            'harga_min'     => isset($r['harga_min']) ? (int) $r['harga_min'] : null,
            'harga_max'     => isset($r['harga_max']) ? (int) $r['harga_max'] : null,
            'info_halal'    => $r['info_halal'] ?? null,
            'foto'          => $r['link_foto'] ?? null,
            'link_maps'     => $r['link_maps'] ?? null,
            'deskripsi'     => $r['deskripsi'] ?? null,
            'no_telepon'    => $r['no_telepon'] ?? null,
            'rating'        => (float) $r['rating'],
            'jumlah_ulasan' => (int) $r['jumlah_ulasan'],
            'versi'         => (int) $r['versi'],
        ];
    }

    protected function ringkasMenu(array $m): array
    {
        return [
            'id'           => (int) $m['id'],
            'restoran_id'  => (int) $m['restoran_id'],
            'nama_menu'    => $m['nama_menu'],
            'harga'        => (int) $m['harga'],
            'kategori'     => $m['kategori'],
            'status_harga' => $m['status_harga'] ?? null,
            'deskripsi'    => $m['deskripsi'] ?? null,
            'versi'        => (int) $m['versi'],
        ];
    }
}
