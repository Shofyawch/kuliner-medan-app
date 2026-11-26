<?php

namespace App\Controllers;

use App\Libraries\HotelService;
use App\Libraries\RatingWeb;
use App\Models\MenuModel;
use App\Models\RestoranModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Kuliner extends BaseController
{
    private const FILTER_HARGA = ['murah', 'sedang', 'mahal'];
    private const FILTER_HALAL = ['halal', 'belum', 'nonhalal'];
    private const OPSI_URUT    = ['rating', 'harga'];

    public function index()
    {
        // ---------- baca parameter ----------
        $kategori  = $this->ambilTeks('kategori');
        $cari      = $this->ambilTeks('q');
        $kecamatan = $this->ambilTeks('kecamatan');
        $harga     = $this->ambilTeks('harga');
        $halal     = $this->ambilTeks('halal');

        if (!in_array($harga, self::FILTER_HARGA, true)) {
            $harga = null;
        }
        if (!in_array($halal, self::FILTER_HALAL, true)) {
            $halal = null;
        }

        // urut bisa lebih dari satu: ?urut[]=rating&urut[]=harga
        $urut = array_values(array_intersect(
            (array) $this->request->getGet('urut'),
            self::OPSI_URUT
        ));

        // ---------- data pendukung filter ----------
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

        // ---------- query restoran ----------
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

        $this->terapkanFilterHarga($model, $harga);
        $this->terapkanFilterHalal($model, $halal);
        $this->terapkanUrutan($model, $urut);

        $restoran = RatingWeb::tempel($model->paginate(20));
        $model->pager->only(['kategori', 'q', 'kecamatan', 'harga', 'halal', 'urut']);

        $totalTampil = $model->pager->getTotal();
        $metaDeskripsi = $kategori
            ? "Jelajahi {$totalTampil} tempat makan {$kategori} di Medan lengkap dengan menu, harga, status halal, dan peta lokasi."
            : "Temukan {$totalTampil} restoran kuliner khas Medan lengkap dengan daftar menu, estimasi harga, status halal, peta lokasi, dan ulasan pengunjung.";

        return view('kuliner/index', [
            'restoran'        => $restoran,
            'pager'           => $model->pager,
            'daftarKategori'  => $daftarKategori,
            'daftarKecamatan' => $daftarKecamatan,
            'kategori'        => $kategori,
            'cari'            => $cari,
            'kecamatan'       => $kecamatan,
            'harga'           => $harga,
            'halal'           => $halal,
            'urut'            => $urut,
            'metaDeskripsi'   => $metaDeskripsi,
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

        $db           = db_connect();
        $sekitar      = [];
        $hotelSekitar = [];

        if (is_numeric($restoran['latitude']) && is_numeric($restoran['longitude'])) {
            $lat = (float) $restoran['latitude'];
            $lng = (float) $restoran['longitude'];

            // restoran lain di sekitar (radius 2 km), tanpa restoran ini sendiri
            $semua = $db
                ->query('SELECT * FROM restoran_terdekat(?, ?, ?, ?)', [$lat, $lng, 2.0, 8])
                ->getResultArray();
            $semua   = array_filter($semua, static fn ($x) => $x['slug'] !== $slug);
            $sekitar = array_slice(array_values($semua), 0, 5);

            // hotel terdekat dari API Singgah Medan
            $hotelSekitar = (new HotelService())->getHotelsNear(
                $lat,
                $lng,
                $restoran['kecamatan'] ?? null,
                5.0,
                4
            );
        }

        // semua ulasan restoran ini
        $ulasan = [];
        try {
            $ulasan = $db->table('v_ulasan')
                ->where('restoran_id', $restoran['id'])
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Gagal memuat ulasan: ' . $e->getMessage());
        }

        // ulasan milik pengguna yang sedang login
        $ulasanSaya = null;
        $penggunaId = session()->get('pengguna_id');
        if ($penggunaId) {
            try {
                $ulasanSaya = $db->table('ulasan')
                    ->where('restoran_id', $restoran['id'])
                    ->where('pengguna_id', $penggunaId)
                    ->get()->getRowArray();
            } catch (\Throwable $e) {
                log_message('error', 'Gagal memuat ulasan pengguna: ' . $e->getMessage());
            }
        }

        $lokasi = !empty($restoran['kecamatan']) ? ' di ' . $restoran['kecamatan'] . ', Medan' : ' di Medan';
        $metaDeskripsi = $restoran['nama'] . ' (' . ($restoran['kategori_khas'] ?? 'Kuliner Medan') . ')' . $lokasi . ' — lihat daftar menu, estimasi harga, lokasi peta, dan ulasan pengunjung.';

        return view('kuliner/detail', [
            'r'             => $restoran,
            'menu'          => $menu,
            'sekitar'       => $sekitar,
            'hotelSekitar'  => $hotelSekitar,
            'ulasan'        => $ulasan,
            'ulasanSaya'    => $ulasanSaya,
            'judul'         => $restoran['nama'] . ' - Kuliner Khas Medan',
            'metaDeskripsi' => $metaDeskripsi,
        ]);
    }

    public function simpanUlasan(string $slug)
    {
        $penggunaId = session()->get('pengguna_id');
        if (!$penggunaId) {
            return redirect()->to('/login')->with('error', 'Silakan masuk terlebih dahulu untuk menulis ulasan.');
        }

        $restoran = (new RestoranModel())->where('slug', $slug)->first();
        if ($restoran === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $balik    = '/restoran/' . $slug;
        $rating   = (int) $this->request->getPost('rating');
        $komentar = trim((string) $this->request->getPost('komentar'));

        if ($rating < 1 || $rating > 5) {
            return redirect()->to($balik)->with('error', 'Rating wajib antara 1 sampai 5.');
        }
        if (mb_strlen($komentar) > 500) {
            return redirect()->to($balik)->with('error', 'Komentar maksimal 500 karakter.');
        }

        $komentar = $komentar !== '' ? $komentar : null;
        $db       = db_connect();

        try {
            $sudahAda = $db->table('ulasan')
                ->where('restoran_id', $restoran['id'])
                ->where('pengguna_id', $penggunaId)
                ->get()->getRowArray();

            if ($sudahAda) {
                $db->table('ulasan')
                    ->where('id', $sudahAda['id'])
                    ->update([
                        'rating'     => $rating,
                        'komentar'   => $komentar,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                $pesan = 'Ulasan Anda berhasil diperbarui!';
            } else {
                $db->table('ulasan')->insert([
                    'restoran_id' => $restoran['id'],
                    'pengguna_id' => $penggunaId,
                    'rating'      => $rating,
                    'komentar'    => $komentar,
                ]);
                $pesan = 'Terima kasih! Ulasan Anda berhasil ditambahkan.';
            }

            // restoran.rating dan jumlah_ulasan berisi data Google, jadi tidak diubah di sini.
            // Rating pengunjung web dihitung dari tabel ulasan (lihat App\Libraries\RatingWeb).

            return redirect()->to($balik)->with('success', $pesan);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal simpan ulasan: ' . $e->getMessage());
            return redirect()->to($balik)->with('error', 'Gagal menyimpan ulasan.');
        }
    }

    public function hapusUlasan(string $slug)
    {
        $penggunaId = session()->get('pengguna_id');
        if (!$penggunaId) {
            return redirect()->to('/login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $restoran = (new RestoranModel())->where('slug', $slug)->first();
        if ($restoran === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $balik = '/restoran/' . $slug . '#ulasan-pengguna';
        $db    = db_connect();

        try {
            $db->table('ulasan')
                ->where('restoran_id', $restoran['id'])
                ->where('pengguna_id', (int) $penggunaId)
                ->delete();

            if ($db->affectedRows() === 0) {
                return redirect()->to($balik)->with('error', 'Ulasan tidak ditemukan atau bukan milik Anda.');
            }

            return redirect()->to($balik)->with('success', 'Ulasan Anda berhasil dihapus.');
        } catch (\Throwable $e) {
            log_message('error', 'Gagal hapus ulasan: ' . $e->getMessage());
            return redirect()->to($balik)->with('error', 'Gagal menghapus ulasan.');
        }
    }

    // ------------------------------------------------------------------
    //  Helper privat
    // ------------------------------------------------------------------

    /** Ambil parameter GET berupa teks; kosong atau bukan string menjadi null. */
    private function ambilTeks(string $nama): ?string
    {
        $v = $this->request->getGet($nama);

        return is_string($v) && $v !== '' ? $v : null;
    }

    /** Filter status halal berdasarkan info_halal. */
    private function terapkanFilterHalal(RestoranModel $model, ?string $halal): void
    {
        match ($halal) {
            'halal'    => $model->like('info_halal', 'Halal', 'after', null, true),
            'nonhalal' => $model->like('info_halal', 'Non', 'after', null, true),
            'belum'    => $model->like('info_halal', 'belum', 'both', null, true),
            default    => null,
        };
    }

    /** Filter rentang harga berdasarkan harga_max. */
    private function terapkanFilterHarga(RestoranModel $model, ?string $harga): void
    {
        match ($harga) {
            'murah'  => $model->where('harga_max <=', 25000),
            'sedang' => $model->where('harga_max >', 25000)->where('harga_max <=', 60000),
            'mahal'  => $model->where('harga_max >', 60000),
            default  => null,
        };
    }

    /**
     * Urutan hasil. Kalau rating dan harga sama-sama dipilih,
     * rating jadi prioritas pertama, harga pembeda berikutnya.
     * Kosong = "Paling cocok" (skor rating tertimbang).
     */
    private function terapkanUrutan(RestoranModel $model, array $urut): void
    {
        if ($urut === []) {
            $model->orderBy(
                '(COALESCE(rating,0)*COALESCE(jumlah_ulasan,0)+43)/(COALESCE(jumlah_ulasan,0)+10)',
                'DESC',
                false
            );
            return;
        }

        if (in_array('rating', $urut, true)) {
            $model->orderBy('COALESCE(rating,0)', 'DESC', false)
                ->orderBy('COALESCE(jumlah_ulasan,0)', 'DESC', false);
        }
        if (in_array('harga', $urut, true)) {
            $model->orderBy('COALESCE(harga_min,999999999)', 'ASC', false);
        }

        // pembeda terakhir supaya urutan stabil antar halaman
        $model->orderBy('nama', 'ASC');
    }
}