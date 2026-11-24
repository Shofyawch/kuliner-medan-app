<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;

/**
 * Operasi tulis menu (khusus admin):
 *  POST   /api/v1/restoran/{id}/menu   tambah menu ke restoran
 *  PUT    /api/v1/menu/{id}            ubah (wajib kirim "versi" terakhir)
 *  DELETE /api/v1/menu/{id}            hapus
 */
class MenuTulis extends BaseController
{
    use ResponsApi;

    private const KOLOM = ['nama_menu', 'harga', 'kategori', 'status_harga', 'deskripsi'];

    public const ATURAN = [
        'nama_menu'    => 'required|min_length[2]|max_length[150]',
        'harga'        => 'required|is_natural',
        'kategori'     => 'required|max_length[80]',
        'status_harga' => 'permit_empty|max_length[80]',
        'deskripsi'    => 'permit_empty|max_length[500]',
    ];

    public function create($restoranId = 0)
    {
        $restoranId = (int) $restoranId;
        $data       = $this->bodyJson();
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

        $row = $this->bersihkan($data);
        $row['restoran_id'] = $restoranId;

        try {
            $db->table('menu')->insert($row);
            $baru = $db->table('menu')
                ->where('restoran_id', $restoranId)
                ->where('nama_menu', $row['nama_menu'])
                ->get()->getRowArray();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        return $this->sukses($this->ringkasMenu($baru), [], 201);
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

        $set = $this->bersihkan($data);
        if ($set === []) {
            return $this->gagal('Tidak ada kolom yang diubah.', 422);
        }

        $db = db_connect();
        if ($db->table('menu')->where('id', $id)->countAllResults() === 0) {
            return $this->gagal('Menu tidak ditemukan.', 404);
        }

        try {
            $db->table('menu')->where('id', $id)->where('versi', $versi)->update($set);
            $berubah = $db->affectedRows();
            $baru    = $db->table('menu')->where('id', $id)->get()->getRowArray();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        if ($berubah === 0) {
            return $this->gagal(
                'Konflik: data sudah diubah oleh pihak lain. Ambil data terbaru lalu ulangi perubahanmu.',
                409,
                ['versi_dikirim' => $versi, 'versi_sekarang' => (int) $baru['versi']]
            );
        }

        return $this->sukses($this->ringkasMenu($baru));
    }

    public function delete($id = 0)
    {
        $id = (int) $id;
        $db = db_connect();

        $m = $db->table('menu')->select('id, nama_menu')->where('id', $id)->get()->getRowArray();
        if ($m === null) {
            return $this->gagal('Menu tidak ditemukan.', 404);
        }

        try {
            $db->table('menu')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        return $this->sukses(['id' => $id, 'nama_menu' => $m['nama_menu']]);
    }

    private function bersihkan(array $data): array
    {
        $row = array_intersect_key($data, array_flip(self::KOLOM));
        foreach ($row as $k => $v) {
            if (is_string($v)) {
                $v = trim($v);
                $row[$k] = ($v === '' && ! in_array($k, ['nama_menu', 'kategori'], true)) ? null : $v;
            }
        }

        return $row;
    }
}
