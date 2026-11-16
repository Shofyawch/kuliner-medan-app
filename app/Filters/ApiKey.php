<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKey implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $valid = array_filter(array_map('trim', explode(',', (string) env('API_KEYS', ''))));
        if ($valid === []) {
            return $this->tolak(503, 'API belum dikonfigurasi.');
        }

        $kunci = (string) $request->getHeaderLine('X-API-KEY');
        foreach ($valid as $k) {
            if ($kunci !== '' && hash_equals($k, $kunci)) {
                return;
            }
        }

        return $this->tolak(401, 'API key tidak ada atau tidak valid.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function tolak(int $kode, string $pesan): ResponseInterface
    {
        return service('response')->setStatusCode($kode)->setJSON(['status' => 'error', 'message' => $pesan]);
    }
}
