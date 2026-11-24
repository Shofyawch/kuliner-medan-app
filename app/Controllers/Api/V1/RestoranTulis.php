<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;

/**
 * Operasi tulis restoran (khusus admin):
 *  POST   /api/v1/restoran           tambah restoran
 *  POST   /api/v1/restoran/lengkap   tambah restoran + menu dalam satu transaksi
 *  PUT    /api/v1/restoran/{id}      ubah (wajib kirim "versi" terakhir)
 *  DELETE /api/v1/restoran/{id}      hapus (menu ikut terhapus)
 */
class RestoranTulis extends BaseController
{
    use ResponsApi;

    private const KOLOM = [
        'nama', 'alamat', 'kecamatan', 'latitude', 'longitude', 'kategori_khas',
        'jenis_masakan', 'harga_min', 'harga_max', 'info_halal', 'link_foto',
        'link_maps', 'deskripsi', 'no_telepon',
    ];

    private const WAJIB_TEKS = ['nama', 'alamat', 'kecamatan', 'kategori_khas', 'jenis_masakan'];

    public const ATURAN = [
        'nama'          => 'required|min_length[3]|max_length[150]',
        'alamat'        => 'required|min_length[5]|max_length[300]',
        'kecamatan'     => 'required|max_length[80]',
        'latitude'      => 'required|decimal|greater_than_equal_to[-90]|less_than_equal_to[90]',
        'longitude'     => 'required|decimal|greater_than_equal_to[-180]|less_than_equal_to[180]',
        'kategori_khas' => 'required|max_length[100]',
        'jenis_masakan' => 'required|max_length[100]',
        'harga_min'     => 'permit_empty|is_natural',
        'harga_max'     => 'permit_empty|is_natural',
        'info_halal'    => 'permit_empty|max_length[100]',
        'link_foto'     => 'permit_empty|max_length[255]',
        'link_maps'     => 'permit_empty|valid_url_strict[http,https]|max_length[600]',
        'deskripsi'     => 'permit_empty|max_length[1000]',
        'no_telepon'    => 'permit_empty|max_length[30]',
    ];

    public function create()
    {
        $data = $this->bodyJson();
        if ($data === null) {
            return $this->gagal('Body harus berupa JSON.', 400);
        }

        $err = $this->validasi($data, self::ATURAN);
        if ($err !== null) {
            return $this->gagal('Data tidak valid.', 422, $err);
        }

        $row = $this->bersihkan($data);
        if (($e = $this->cekHarga($row['harga_min'] ?? null, $row['harga_max'] ?? null)) !== null) {
            return $this->gagal('Data tidak valid.', 422, $e);
        }

        $row['slug']       = $this->buatSlug($row['nama']);
        $row['info_halal'] = $row['info_halal'] ?? 'Status halal belum dicek';

        try {
            $db = db_connect();
            $db->table('restoran')->insert($row);
            $baru = $db->table('restoran')->where('slug', $row['slug'])->get()->getRowArray();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        return $this->sukses($this->ringkasRestoran($baru), [], 201);
    }

    public function createLengkap()
    {
        $data = $this->bodyJson();
        if ($data === null) {
            return $this->gagal('Body harus berupa JSON.', 400);
        }

        $r    = $data['restoran'] ?? null;
        $menu = $data['menu'] ?? [];
        if (! is_array($r) || ! is_array($menu)) {
            return $this->gagal('Format harus {"restoran": {...}, "menu": [...]}.', 422);
        }

        $err = [];
        if (($e = $this->validasi($r, self::ATURAN)) !== null) {
            $err['restoran'] = $e;
        }

        $menuBersih = [];
        foreach (array_values($menu) as $i => $m) {
            if (! is_array($m)) {
                $err["menu.$i"] = 'Harus berupa objek.';
                continue;
            }
            if (($e = $this->validasi($m, MenuTulis::ATURAN)) !== null) {
                $err["menu.$i"] = $e;
                continue;
            }
            $menuBersih[] = [
                'nama_menu' => trim((string) $m['nama_menu']),
                'harga'     => (int) $m['harga'],
                'kategori'  => trim((string) $m['kategori']),
                'deskripsi' => isset($m['deskripsi']) && trim((string) $m['deskripsi']) !== '' ? trim((string) $m['deskripsi']) : null,
            ];
        }
        if ($err !== []) {
            return $this->gagal('Data tidak valid.', 422, $err);
        }

        $row = $this->bersihkan($r);
        if (($e = $this->cekHarga($row['harga_min'] ?? null, $row['harga_max'] ?? null)) !== null) {
            return $this->gagal('Data tidak valid.', 422, ['restoran' => $e]);
        }
        $row['slug']       = $this->buatSlug($row['nama']);
        $row['info_halal'] = $row['info_halal'] ?? 'Status halal belum dicek';

        try {
            $q = db_connect()->query(
                'SELECT tambah_restoran_dengan_menu(?::jsonb, ?::jsonb) AS id',
                [
                    json_encode($row, JSON_UNESCAPED_UNICODE),
                    json_encode($menuBersih, JSON_UNESCAPED_UNICODE),
                ]
            );
            if ($q === false) {
                return $this->gagal('Penyimpanan gagal. Seluruh perubahan dibatalkan.', 422);
            }
            $id = (int) $q->getRowArray()['id'];
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        return $this->sukses([
            'id'          => $id,
            'slug'        => $row['slug'],
            'jumlah_menu' => count($menuBersih),
            'catatan'     => 'Restoran dan semua menu disimpan dalam satu transaksi.',
        ], [], 201);
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

        $db   = db_connect();
        $lama = $db->table('restoran')->where('id', $id)->get()->getRowArray();
        if ($lama === null) {
            return $this->gagal('Restoran tidak ditemukan.', 404);
        }

        $hMin = array_key_exists('harga_min', $set) ? $set['harga_min'] : $lama['harga_min'];
        $hMax = array_key_exists('harga_max', $set) ? $set['harga_max'] : $lama['harga_max'];
        if (($e = $this->cekHarga($hMin, $hMax)) !== null) {
            return $this->gagal('Data tidak valid.', 422, $e);
        }

        try {
            $db->table('restoran')->where('id', $id)->where('versi', $versi)->update($set);
            $berubah = $db->affectedRows();
            $baru    = $db->table('restoran')->where('id', $id)->get()->getRowArray();
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

        return $this->sukses($this->ringkasRestoran($baru));
    }

    public function delete($id = 0)
    {
        $id = (int) $id;
        $db = db_connect();

        $r = $db->table('restoran')->select('id, nama')->where('id', $id)->get()->getRowArray();
        if ($r === null) {
            return $this->gagal('Restoran tidak ditemukan.', 404);
        }

        try {
            $jumlahMenu = $db->table('menu')->where('restoran_id', $id)->countAllResults();
            $db->table('restoran')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        return $this->sukses([
            'id'                 => $id,
            'nama'               => $r['nama'],
            'menu_ikut_terhapus' => $jumlahMenu,
        ]);
    }

    private function bersihkan(array $data): array
    {
        $row = array_intersect_key($data, array_flip(self::KOLOM));
        foreach ($row as $k => $v) {
            if (is_string($v)) {
                $v = trim($v);
                $row[$k] = ($v === '' && ! in_array($k, self::WAJIB_TEKS, true)) ? null : $v;
            }
        }

        return $row;
    }

    private function cekHarga($min, $max): ?array
    {
        if ($min !== null && $max !== null && $min !== '' && $max !== '' && (int) $min > (int) $max) {
            return ['harga_min' => 'Tidak boleh lebih besar dari harga_max.'];
        }

        return null;
    }

    private function buatSlug(string $nama): string
    {
        helper('url');
        $dasar = url_title($nama, '-', true);
        if ($dasar === '') {
            $dasar = 'restoran';
        }

        $db   = db_connect();
        $slug = $dasar;
        for ($i = 2; $i < 50 && $db->table('restoran')->where('slug', $slug)->countAllResults() > 0; $i++) {
            $slug = $dasar . '-' . $i;
        }

        return $slug;
    }
}
