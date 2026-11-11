<?php

namespace App\Controllers;

class Terdekat extends BaseController
{
    // Titik patokan untuk tes tanpa GPS (koordinat Istana Maimun dari data kita, sisanya perkiraan)
    private array $patokan = [
        'istana-maimun'  => ['Istana Maimun', 3.575278, 98.683889],
        'masjid-raya'    => ['Masjid Raya Al Mashun', 3.575000, 98.687000],
        'lapangan-merdeka' => ['Lapangan Merdeka', 3.588000, 98.677000],
        'polmed'         => ['Politeknik Negeri Medan', 3.563600, 98.656200],
    ];

    public function index()
    {
        $lat      = $this->request->getGet('lat');
        $lng      = $this->request->getGet('lng');
        $pilihan  = $this->request->getGet('patokan');
        $radius   = (float) ($this->request->getGet('radius') ?: 2);
        $radius   = min(max($radius, 0.5), 10);

        $asal  = null;
        $hasil = null;

        if ($pilihan && isset($this->patokan[$pilihan])) {
            [$asal, $lat, $lng] = $this->patokan[$pilihan];
        } elseif (is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180) {
            $lat  = (float) $lat;
            $lng  = (float) $lng;
            $asal = 'Lokasimu saat ini';
        } else {
            $lat = $lng = null;
        }

        if ($lat !== null) {
            $hasil = db_connect()
                ->query('SELECT * FROM restoran_terdekat(?, ?, ?, ?)', [$lat, $lng, $radius, 30])
                ->getResultArray();
        }

        return view('kuliner/terdekat', [
            'patokan' => $this->patokan,
            'pilihan' => $pilihan,
            'radius'  => $radius,
            'asal'    => $asal,
            'hasil'   => $hasil,
        ]);
    }
}
