<?php

namespace App\Controllers;

use App\Models\MenuModel;
use App\Models\RestoranModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Kuliner extends BaseController
{
    public function index()
    {
        $model    = new RestoranModel();
        $kategori = $this->request->getGet('kategori');
        $cari     = $this->request->getGet('q');

        $daftarKategori = db_connect()->table('restoran')
            ->select('kategori_khas, COUNT(*) AS jumlah')
            ->groupBy('kategori_khas')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();

        if ($kategori) {
            $model->where('kategori_khas', $kategori);
        }
        if ($cari) {
            $model->like('nama', $cari, 'both', null, true);
        }

        $restoran = $model
            ->orderBy('(COALESCE(rating,0)*COALESCE(jumlah_ulasan,0)+43)/(COALESCE(jumlah_ulasan,0)+10)', 'DESC', false)
            ->paginate(20);
        $model->pager->only(['kategori', 'q']);

        return view('kuliner/index', [
            'restoran'       => $restoran,
            'pager'          => $model->pager,
            'daftarKategori' => $daftarKategori,
            'kategori'       => $kategori,
            'cari'           => $cari,
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
