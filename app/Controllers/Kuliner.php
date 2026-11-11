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

        $restoran = $model->orderBy('rating', 'DESC')->paginate(20);
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

        return view('kuliner/detail', ['r' => $restoran, 'menu' => $menu]);
    }
}
