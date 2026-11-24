<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Models\RestoranModel;
use CodeIgniter\HTTP\ResponseInterface;

class Restoran extends BaseController
{
    private function sukses($data, array $meta = [], int $kode = 200): ResponseInterface
    {
        $body = ['status' => 'success', 'data' => $data];
        if ($meta !== []) {
            $body['meta'] = $meta;
        }
        return $this->response->setStatusCode($kode)->setJSON($body);
    }

    private function gagal(string $pesan, int $kode, array $detail = []): ResponseInterface
    {
        $body = ['status' => 'error', 'message' => $pesan];
        if ($detail !== []) {
            $body['errors'] = $detail;
        }
        return $this->response->setStatusCode($kode)->setJSON($body);
    }

    private function format(array $r): array
    {
        $hasil = [
            'id'            => (int) $r['id'],
            'nama'          => $r['nama'],
            'slug'          => $r['slug'],
            'alamat'        => $r['alamat'],
            'kecamatan'     => $r['kecamatan'],
            'lokasi'        => ['lat' => (float) $r['latitude'], 'lng' => (float) $r['longitude']],
            'kategori'      => $r['kategori_khas'],
            'jenis_masakan' => $r['jenis_masakan'],
            'harga'         => [
                'min'     => isset($r['harga_min']) ? (int) $r['harga_min'] : null,
                'max'     => isset($r['harga_max']) ? (int) $r['harga_max'] : null,
                'catatan' => 'estimasi',
            ],
            'rating'        => (float) $r['rating'],
            'jumlah_ulasan' => (int) $r['jumlah_ulasan'],
            'versi'         => isset($r['versi']) ? (int) $r['versi'] : null,
            'info_halal'    => $r['info_halal'] ?? null,
            'foto'          => $r['link_foto'] ?? null,
        ];
        if (isset($r['jarak_km'])) {
            $hasil['jarak_km'] = (float) $r['jarak_km'];
        }
        return $hasil;
    }

    public function kategori()
    {
        $rows = db_connect()->table('restoran')
            ->select('kategori_khas AS nama, COUNT(*) AS jumlah')
            ->groupBy('kategori_khas')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();

        foreach ($rows as &$k) {
            $k['jumlah'] = (int) $k['jumlah'];
        }
        return $this->sukses($rows);
    }

    public function index()
    {
        $perPage  = min(max((int) ($this->request->getGet('per_page') ?: 20), 1), 50);
        $page     = max((int) ($this->request->getGet('page') ?: 1), 1);
        $kategori = $this->request->getGet('kategori');
        $cari     = $this->request->getGet('q');

        $model = new RestoranModel();
        if ($kategori) {
            $model->where('kategori_khas', $kategori);
        }
        if ($cari) {
            $model->like('nama', $cari, 'both', null, true);
        }

        $rows = $model->orderBy('rating', 'DESC')->paginate($perPage, 'default', $page);

        return $this->sukses(array_map([$this, 'format'], $rows), [
            'page'        => $model->pager->getCurrentPage(),
            'per_page'    => $perPage,
            'total'       => $model->pager->getTotal(),
            'total_pages' => $model->pager->getPageCount(),
        ]);
    }

    public function terdekat()
    {
        $lat = $this->request->getGet('lat');
        $lng = $this->request->getGet('lng');

        $err = [];
        if (! is_numeric($lat) || abs((float) $lat) > 90) {
            $err['lat'] = 'Wajib angka antara -90 dan 90.';
        }
        if (! is_numeric($lng) || abs((float) $lng) > 180) {
            $err['lng'] = 'Wajib angka antara -180 dan 180.';
        }
        if ($err !== []) {
            return $this->gagal('Parameter tidak valid.', 422, $err);
        }

        $radius = min(max((float) ($this->request->getGet('radius') ?: 2), 0.1), 20);
        $limit  = min(max((int) ($this->request->getGet('limit') ?: 10), 1), 50);

        $rows = db_connect()
            ->query('SELECT * FROM restoran_terdekat(?, ?, ?, ?)', [(float) $lat, (float) $lng, $radius, $limit])
            ->getResultArray();

        return $this->sukses(array_map([$this, 'format'], $rows), [
            'pusat'     => ['lat' => (float) $lat, 'lng' => (float) $lng],
            'radius_km' => $radius,
            'jumlah'    => count($rows),
            'catatan'   => 'Jarak garis lurus, bukan jarak tempuh.',
        ]);
    }

    public function detail(string $slug)
    {
        $r = (new RestoranModel())->where('slug', $slug)->first();
        if ($r === null) {
            return $this->gagal('Restoran tidak ditemukan.', 404);
        }

        $menu = (new MenuModel())
            ->where('restoran_id', $r['id'])
            ->orderBy('kategori')
            ->orderBy('nama_menu')
            ->findAll();

        $data = $this->format($r) + [
            'deskripsi'     => $r['deskripsi'] ?? null,
            'no_telepon'    => $r['no_telepon'] ?? null,
            'link_maps'     => $r['link_maps'] ?? null,
            'menu'          => array_map(static fn ($m) => [
                'id'        => (int) $m['id'],
                'versi'     => (int) ($m['versi'] ?? 1),
                'nama'      => $m['nama_menu'],
                'harga'     => (int) $m['harga'],
                'kategori'  => $m['kategori'],
                'deskripsi' => $m['deskripsi'] ?? null,
            ], $menu),
            'catatan_harga' => 'Harga masih estimasi.',
        ];

        return $this->sukses($data);
    }
}
