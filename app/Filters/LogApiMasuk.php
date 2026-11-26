<?php

namespace App\Filters;

use App\Libraries\LogIntegrasi;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Mencatat setiap permintaan masuk ke API (/api/*) ke tabel log_integrasi (arah = 'masuk').
 */
class LogApiMasuk implements FilterInterface
{
    private static float $mulai = 0.0;

    public function before(RequestInterface $request, $arguments = null)
    {
        self::$mulai = microtime(true);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $metode = strtoupper($request->getMethod());
        if ($metode === 'OPTIONS') {
            return;
        }

        $durasiMs = self::$mulai > 0 ? (int) round((microtime(true) - self::$mulai) * 1000) : null;
        $path     = '/' . ltrim(method_exists($request, 'getPath') ? $request->getPath() : $request->getUri()->getPath(), '/');
        $query    = $request->getUri()->getQuery();
        $status   = $response->getStatusCode();

        $jumlahData = null;
        $keterangan = null;
        $body       = $response->getBody();
        if (is_string($body) && $body !== '') {
            $json = json_decode($body, true);
            if (is_array($json)) {
                if (array_key_exists('data', $json)) {
                    $d = $json['data'];
                    $jumlahData = is_array($d) ? (array_is_list($d) ? count($d) : 1) : ($d !== null ? 1 : 0);
                }
                if ($status >= 400 && isset($json['message']) && is_string($json['message'])) {
                    $keterangan = $json['message'];
                }
            }
        }

        LogIntegrasi::catat(
            'masuk',
            $path,
            $metode,
            $query !== '' ? $query : null,
            $status,
            $jumlahData,
            $durasiMs,
            $keterangan
        );
    }
}
