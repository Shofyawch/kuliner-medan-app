<?php

namespace App\Libraries;

/**
 * Pencatat aktivitas pertukaran data antar sistem ke tabel log_integrasi.
 */
class LogIntegrasi
{
    public static function catat(
        string $arah,
        string $endpoint,
        string $metode = 'GET',
        ?string $parameter = null,
        ?int $statusCode = null,
        ?int $jumlahData = null,
        ?int $durasiMs = null,
        ?string $keterangan = null
    ): void {
        try {
            db_connect()->table('log_integrasi')->insert([
                'arah'        => $arah === 'keluar' ? 'keluar' : 'masuk',
                'endpoint'    => substr($endpoint, 0, 500),
                'metode'      => strtoupper(substr($metode, 0, 16)),
                'parameter'   => $parameter !== null && $parameter !== '' ? substr($parameter, 0, 1000) : null,
                'status_code' => $statusCode,
                'jumlah_data' => $jumlahData,
                'durasi_ms'   => $durasiMs,
                'keterangan'  => $keterangan !== null && $keterangan !== '' ? substr($keterangan, 0, 1000) : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal mencatat log_integrasi: ' . $e->getMessage());
        }
    }
}
