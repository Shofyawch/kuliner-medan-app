<?php

namespace App\Controllers;

use App\Models\MenuModel;
use App\Models\RestoranModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Kuliner extends BaseController
{
    public function index()
    {
        $ambil = function (string $nama): ?string {
            $v = $this->request->getGet($nama);
            return is_string($v) && $v !== '' ? $v : null;
        };

        $kategori  = $ambil('kategori');
        $cari      = $ambil('q');
        $kecamatan = $ambil('kecamatan');
        $harga     = $ambil('harga');
        $urut      = $ambil('urut');

        if (!in_array($harga, ['murah', 'sedang', 'mahal'], true)) {
            $harga = null;
        }
        if (!in_array($urut, ['rating', 'murah'], true)) {
            $urut = null;
        }

        $db = db_connect();
        $daftarKategori = $db->table('restoran')
            ->select('kategori_khas, COUNT(*) AS jumlah')
            ->groupBy('kategori_khas')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();

        $daftarKecamatan = $db->table('restoran')
            ->select('kecamatan, COUNT(*) AS jumlah')
            ->where('kecamatan IS NOT NULL', null, false)
            ->groupBy('kecamatan')
            ->orderBy('kecamatan')
            ->get()->getResultArray();

        $model = new RestoranModel();
        if ($kategori) {
            $model->where('kategori_khas', $kategori);
        }
        if ($cari) {
            $model->like('nama', $cari, 'both', null, true);
        }
        if ($kecamatan) {
            $model->where('kecamatan', $kecamatan);
        }
        if ($harga === 'murah') {
            $model->where('harga_max <=', 25000);
        } elseif ($harga === 'sedang') {
            $model->where('harga_max >', 25000)->where('harga_max <=', 60000);
        } elseif ($harga === 'mahal') {
            $model->where('harga_max >', 60000);
        }

        if ($urut === 'rating') {
            $model->orderBy('COALESCE(rating,0)', 'DESC', false)
                  ->orderBy('COALESCE(jumlah_ulasan,0)', 'DESC', false);
        } elseif ($urut === 'murah') {
            $model->orderBy('COALESCE(harga_min,999999999)', 'ASC', false)
                  ->orderBy('nama', 'ASC');
        } else {
            $model->orderBy('(COALESCE(rating,0)*COALESCE(jumlah_ulasan,0)+43)/(COALESCE(jumlah_ulasan,0)+10)', 'DESC', false);
        }

        $restoran = $model->paginate(20);
        $model->pager->only(['kategori', 'q', 'kecamatan', 'harga', 'urut']);

        return view('kuliner/index', [
            'restoran'        => $restoran,
            'pager'           => $model->pager,
            'daftarKategori'  => $daftarKategori,
            'daftarKecamatan' => $daftarKecamatan,
            'kategori'        => $kategori,
            'cari'            => $cari,
            'kecamatan'       => $kecamatan,
            'harga'           => $harga,
            'urut'            => $urut,
        ]);
    }

    public function detail(string $slug)
    {
        $restoran = (new RestoranModel())->where('slug', $slug)->first();
        if ($restoran === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $menu = (new MenuModel())
            ->where('restoran_id', $restoran['id'])
            ->orderBy('kategori')
            ->orderBy('nama_menu')
            ->findAll();

        $sekitar = [];
        if (is_numeric($restoran['latitude']) && is_numeric($restoran['longitude'])) {
            $semua = db_connect()
                ->query('SELECT * FROM restoran_terdekat(?, ?, ?, ?)', [
                    (float) $restoran['latitude'], (float) $restoran['longitude'], 2.0, 8,
                ])->getResultArray();
            $semua   = array_filter($semua, static fn ($x) => $x['slug'] !== $slug);
            $sekitar = array_slice(array_values($semua), 0, 5);
        }

        return view('kuliner/detail', [
            'r'       => $restoran,
            'menu'    => $menu,
            'sekitar' => $sekitar,
            'judul'   => $restoran['nama'] . ' - Kuliner Khas Medan',
        ]);
    }
}
