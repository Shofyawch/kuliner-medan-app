<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Filter autentikasi API.
 *  - Authorization: Bearer <token>  -> login pengguna (role admin atau user)
 *  - X-API-KEY                      -> layanan lain, hanya boleh membaca
 * Pemakaian di route:
 *  'apiauth'        semua yang terautentikasi (token atau API key)
 *  'apiauth:login'  wajib login (token), API key ditolak
 *  'apiauth:admin'  wajib login sebagai admin
 */
class ApiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Buang identitas kiriman klien supaya tidak bisa dipalsukan
        $request->removeHeader('X-Auth-Id');
        $request->removeHeader('X-Auth-Role');

        $arg        = is_array($arguments) ? $arguments : [];
        $perluAdmin = in_array('admin', $arg, true);
        $perluLogin = $perluAdmin || in_array('login', $arg, true);

        $auth = trim($request->getHeaderLine('Authorization'));
        if (preg_match('/^Bearer\s+(\S+)$/i', $auth, $m)) {
            $rahasia = (string) env('JWT_SECRET', '');
            if ($rahasia === '') {
                return $this->tolak(503, 'API belum dikonfigurasi.');
            }

            try {
                $isi = JWT::decode($m[1], new Key($rahasia, 'HS256'));
            } catch (\Throwable $e) {
                return $this->tolak(401, 'Token tidak valid atau sudah kedaluwarsa.');
            }

            if ($perluAdmin && ($isi->role ?? '') !== 'admin') {
                return $this->tolak(403, 'Akses ditolak: aksi ini hanya untuk admin.');
            }

            $request->setHeader('X-Auth-Id', (string) ($isi->sub ?? 0));
            $request->setHeader('X-Auth-Role', (string) ($isi->role ?? ''));

            return;
        }

        $kunci = trim($request->getHeaderLine('X-API-KEY'));
        if ($kunci !== '') {
            $valid = array_filter(array_map('trim', explode(',', (string) env('API_KEYS', ''))));
            if ($valid === []) {
                return $this->tolak(503, 'API belum dikonfigurasi.');
            }

            foreach ($valid as $k) {
                if (hash_equals($k, $kunci)) {
                    if ($perluLogin) {
                        return $this->tolak(403, 'API key hanya boleh membaca data. Gunakan login pengguna.');
                    }

                    return;
                }
            }

            return $this->tolak(401, 'API key tidak valid.');
        }

        return $this->tolak(401, 'Autentikasi diperlukan: kirim Bearer token atau X-API-KEY.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function tolak(int $kode, string $pesan): ResponseInterface
    {
        return service('response')->setStatusCode($kode)->setJSON(['status' => 'error', 'message' => $pesan]);
    }
}
