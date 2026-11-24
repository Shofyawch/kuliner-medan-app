<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;

/**
 * Ulasan restoran.
 *  GET    /api/v1/restoran/{id}/ulasan   daftar ulasan satu restoran (token atau API key)
 *  POST   /api/v1/restoran/{id}/ulasan   tulis ulasan (login; satu pengguna satu ulasan per restoran)
 *  PUT    /api/v1/ulasan/{id}            ubah ulasan sendiri (admin boleh semua), wajib kirim "versi"
 *  DELETE /api/v1/ulasan/{id}            hapus ulasan sendiri (admin boleh semua)
 *  GET    /api/v1/ulasan                 semua ulasan untuk moderasi (admin)
 */
class Ulasan extends BaseController
{
    use ResponsApi;

    private const ATURAN = [
        'rating'   => 'required|is_natural_no_zero|less_than_equal_to[5]',
        'komentar' => 'permit_empty|max_length[500]',
    ];

    public function daftar($restoranId = 0)
    {
        $restoranId = (int) $restoranId;
        $db         = db_connect();

        if ($db->table('restoran')->where('id', $restoranId)->countAllResults() === 0) {
            return $this->gagal('Restoran tidak ditemukan.', 404);
        }

        [$perPage, $page] = $this->halaman();
        $total = $db->table('ulasan')->where('restoran_id', $restoranId)->countAllResults();
        $rows  = $db->table('v_ulasan')
            ->where('restoran_id', $restoranId)
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();
        $rata = $db->table('ulasan')->select('AVG(rating) AS rata', false)
            ->where('restoran_id', $restoranId)->get()->getRowArray();

        return $this->sukses(array_map([$this, 'ringkas'], $rows), [
            'page'             => $page,
            'per_page'         => $perPage,
            'total'            => $total,
            'total_pages'      => (int) ceil($total / $perPage),
            'rata_rata_rating' => $total > 0 ? round((float) $rata['rata'], 1) : null,
        ]);
    }

    public function semua()
    {
        $db = db_connect();
        [$perPage, $page] = $this->halaman();

        $total = $db->table('ulasan')->countAllResults();
        $rows  = $db->table('v_ulasan')
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        return $this->sukses(array_map([$this, 'ringkas'], $rows), [
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    public function create($restoranId = 0)
    {
        $restoranId = (int) $restoranId;
        $uid        = $this->idPengguna();
        if ($uid < 1) {
            return $this->gagal('Login diperlukan.', 401);
        }

        $data = $this->bodyJson();
        if ($data === null) {
            return $this->gagal('Body harus berupa JSON.', 400);
        }

        $db = db_connect();
        if ($db->table('restoran')->where('id', $restoranId)->countAllResults() === 0) {
            return $this->gagal('Restoran tidak ditemukan.', 404);
        }

        $err = $this->validasi($data, self::ATURAN);
        if ($err !== null) {
            return $this->gagal('Data tidak valid.', 422, $err);
        }

        $row = [
            'restoran_id' => $restoranId,
            'pengguna_id' => $uid,
            'rating'      => (int) $data['rating'],
            'komentar'    => $this->teks($data['komentar'] ?? null),
        ];

        try {
            $db->table('ulasan')->insert($row);
            $baru = $db->table('v_ulasan')
                ->where('restoran_id', $restoranId)
                ->where('pengguna_id', $uid)
                ->get()->getRowArray();
        } catch (\Throwable $e) {
            if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
                return $this->gagal('Kamu sudah menulis ulasan untuk restoran ini. Ubah ulasan yang sudah ada.', 409);
            }

            return $this->galatDb($e);
        }

        return $this->sukses($this->ringkas($baru), [], 201);
    }

    public function update($id = 0)
    {
        $id   = (int) $id;
        $data = $this->bodyJson();
        if ($data === null) {
            return $this->gagal('Body harus berupa JSON.', 400);
        }

        if (! isset($data['versi']) || ! is_numeric($data['versi']) || (int) $data['versi'] < 1) {
            return $this->gagal('Data tidak valid.', 422, [
                'versi' => 'Wajib diisi dengan nomor versi terakhir yang kamu terima.',
            ]);
        }
        $versi = (int) $data['versi'];

        $err = $this->validasi($data, self::ATURAN, true);
        if ($err !== null) {
            return $this->gagal('Data tidak valid.', 422, $err);
        }

        $set = array_intersect_key($data, ['rating' => 1, 'komentar' => 1]);
        if ($set === []) {
            return $this->gagal('Tidak ada kolom yang diubah.', 422);
        }
        if (array_key_exists('rating', $set)) {
            $set['rating'] = (int) $set['rating'];
        }
        if (array_key_exists('komentar', $set)) {
            $set['komentar'] = $this->teks($set['komentar']);
        }

        $db   = db_connect();
        $lama = $db->table('ulasan')->where('id', $id)->get()->getRowArray();
        if ($lama === null) {
            return $this->gagal('Ulasan tidak ditemukan.', 404);
        }
        if (! $this->boleh((int) $lama['pengguna_id'])) {
            return $this->gagal('Kamu hanya boleh mengubah ulasanmu sendiri.', 403);
        }

        try {
            $db->table('ulasan')->where('id', $id)->where('versi', $versi)->update($set);
            $berubah = $db->affectedRows();
            $baru    = $db->table('v_ulasan')->where('id', $id)->get()->getRowArray();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        if ($berubah === 0) {
            return $this->gagal(
                'Konflik: ulasan sudah diubah oleh pihak lain. Ambil data terbaru lalu ulangi perubahanmu.',
                409,
                ['versi_dikirim' => $versi, 'versi_sekarang' => (int) $baru['versi']]
            );
        }

        return $this->sukses($this->ringkas($baru));
    }

    public function delete($id = 0)
    {
        $id = (int) $id;
        $db = db_connect();

        $lama = $db->table('ulasan')->where('id', $id)->get()->getRowArray();
        if ($lama === null) {
            return $this->gagal('Ulasan tidak ditemukan.', 404);
        }
        if (! $this->boleh((int) $lama['pengguna_id'])) {
            return $this->gagal('Kamu hanya boleh menghapus ulasanmu sendiri.', 403);
        }

        try {
            $db->table('ulasan')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        return $this->sukses(['id' => $id, 'restoran_id' => (int) $lama['restoran_id']]);
    }

    private function idPengguna(): int
    {
        return (int) $this->request->getHeaderLine('X-Auth-Id');
    }

    /** Pemilik ulasan atau admin. */
    private function boleh(int $pemilik): bool
    {
        return $this->request->getHeaderLine('X-Auth-Role') === 'admin'
            || ($this->idPengguna() > 0 && $this->idPengguna() === $pemilik);
    }

    private function teks($v): ?string
    {
        $v = trim((string) ($v ?? ''));

        return $v === '' ? null : $v;
    }

    private function halaman(): array
    {
        $perPage = min(max((int) ($this->request->getGet('per_page') ?: 20), 1), 50);
        $page    = max((int) ($this->request->getGet('page') ?: 1), 1);

        return [$perPage, $page];
    }

    private function ringkas(array $u): array
    {
        return [
            'id'          => (int) $u['id'],
            'restoran_id' => (int) $u['restoran_id'],
            'restoran'    => $u['restoran_nama'],
            'pengguna'    => $u['username'],
            'rating'      => (int) $u['rating'],
            'komentar'    => $u['komentar'],
            'versi'       => (int) $u['versi'],
            'dibuat'      => $u['created_at'],
            'diubah'      => $u['updated_at'],
        ];
    }
}
