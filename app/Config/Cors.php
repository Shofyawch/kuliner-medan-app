<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * CORS API. Hanya origin yang terdaftar yang boleh memanggil API dari browser.
 * Dua aplikasi client berasal dari origin yang sama dengan API, jadi tidak
 * membutuhkan CORS. Aplikasi lain (misalnya sistem kelompok lain) diizinkan
 * lewat .env:  CORS_ORIGINS = http://localhost:8080,https://contoh.ac.id
 */
class Cors extends BaseConfig
{
    public array $default = [
        'allowedOrigins'         => [],
        'allowedOriginsPatterns' => [],
        'supportsCredentials'    => false,
        'allowedHeaders'         => ['Content-Type', 'Authorization', 'X-API-KEY'],
        'exposedHeaders'         => ['Retry-After'],
        'allowedMethods'         => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'maxAge'                 => 7200,
    ];

    public function __construct()
    {
        parent::__construct();

        $daftar = array_filter(array_map('trim', explode(',', (string) env('CORS_ORIGINS', 'http://localhost:8080'))));
        $this->default['allowedOrigins'] = array_values($daftar);
    }
}
