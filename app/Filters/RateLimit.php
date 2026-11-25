<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pembatas jumlah permintaan per alamat IP (token bucket bawaan CodeIgniter).
 *  - /api/v1/auth/*  : ketat, mencegah tebak-tebakan password (RATE_AUTH, bawaan 20 per menit)
 *  - /api/v1/*       : umum (RATE_UMUM, bawaan 240 per menit)
 * Nilainya bisa diubah lewat .env tanpa mengubah kode.
 */
class RateLimit implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return; // preflight CORS tidak dihitung
        }

        $path  = trim(method_exists($request, 'getPath') ? $request->getPath() : $request->getUri()->getPath(), '/');
        $ketat = str_starts_with($path, 'api/v1/auth/');
        $batas = max(1, (int) env($ketat ? 'RATE_AUTH' : 'RATE_UMUM', $ketat ? 20 : 240));
        $kunci = 'rl_' . ($ketat ? 'auth_' : 'umum_') . md5((string) $request->getIPAddress());

        $pembatas = service('throttler');
        if ($pembatas->check($kunci, $batas, 60) === false) {
            $tunggu = max(1, (int) $pembatas->getTokenTime());

            return service('response')
                ->setStatusCode(429)
                ->setHeader('Retry-After', (string) $tunggu)
                ->setJSON([
                    'status'  => 'error',
                    'message' => "Terlalu banyak permintaan. Coba lagi dalam {$tunggu} detik.",
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
