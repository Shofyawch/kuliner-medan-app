<?php

namespace App\Libraries;

use Throwable;

/**
 * Rating dari pengunjung web, dihitung dari tabel ulasan.
 * Kolom restoran.rating dan restoran.jumlah_ulasan tetap berisi rating Google.
 */
class RatingWeb
{
    /** Menambahkan "rating_web" (null kalau belum ada ulasan) dan "jumlah_ulasan_web" ke tiap baris. */
    public static function tempel(array $restoran): array
    {
        if ($restoran === []) {
            return $restoran;
        }

        $nilai = [];
        try {
            $baris = db_connect()->table('ulasan')
                ->select('restoran_id, AVG(rating) AS rata, COUNT(*) AS jumlah', false)
                ->whereIn('restoran_id', array_column($restoran, 'id'))
                ->groupBy('restoran_id')
                ->get()->getResultArray();
            foreach ($baris as $b) {
                $nilai[(int) $b['restoran_id']] = $b;
            }
        } catch (Throwable $e) {
            log_message('error', 'Gagal menghitung rating web: ' . $e->getMessage());
        }

        foreach ($restoran as &$r) {
            $n = $nilai[(int) $r['id']] ?? null;
            $r['rating_web']        = $n ? round((float) $n['rata'], 1) : null;
            $r['jumlah_ulasan_web'] = $n ? (int) $n['jumlah'] : 0;
        }
        unset($r);

        return $restoran;
    }
}
