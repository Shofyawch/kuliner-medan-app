<?php

namespace App\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/** Dashboard admin. Memakai token dari POST /api/v1/auth/login untuk API CRUD. */
class Admin extends BaseController
{
    public function index()
    {
        if (! session()->has('pengguna_id')) {
            return redirect()->to('/login');
        }

        // Cek ulang ke database: role atau status akun bisa saja sudah berubah
        $u = db_connect()->table('pengguna')
            ->where('id', (int) session()->get('pengguna_id'))
            ->where('aktif', true)
            ->get()->getRowArray();

        if ($u === null || $u['role'] !== 'admin') {
            return redirect()->to('/')->with('error', 'Halaman ini hanya untuk admin.');
        }

        $rahasia = (string) env('JWT_SECRET', '');
        if ($rahasia === '') {
            return $this->response->setStatusCode(503)->setBody('JWT_SECRET belum diisi di file .env');
        }

        // Token yang diterima saat login. Kalau tidak ada atau kedaluwarsa, login ulang.
        $token = (string) session()->get('api_token');
        try {
            $isi = JWT::decode($token, new Key($rahasia, 'HS256'));
            if ((int) ($isi->sub ?? 0) !== (int) $u['id']) {
                throw new \RuntimeException('Token bukan milik pengguna ini.');
            }
        } catch (\Throwable $e) {
            session()->remove(['pengguna_id', 'username', 'role', 'isLoggedIn', 'api_token']);

            return redirect()->to('/login')->with('error', 'Sesi sudah habis. Silakan masuk lagi.');
        }

        $this->response->setHeader('Cache-Control', 'no-store');

        return view('admin/index', ['token' => $token, 'username' => $u['username']]);
    }

    public function keluar()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
