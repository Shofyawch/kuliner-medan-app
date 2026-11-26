<?php

namespace App\Controllers;

use Firebase\JWT\JWT;

/** Dashboard admin. Memakai sesi login web, lalu memberi token untuk API CRUD. */
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

        // Isi token sama dengan POST /api/v1/auth/login
        $now   = time();
        $token = JWT::encode([
            'iss'  => 'kuliner-medan-api',
            'iat'  => $now,
            'exp'  => $now + max(60, (int) env('JWT_TTL', 7200)),
            'sub'  => (int) $u['id'],
            'usr'  => $u['username'],
            'role' => $u['role'],
        ], $rahasia, 'HS256');

        $this->response->setHeader('Cache-Control', 'no-store');

        return view('admin/index', ['token' => $token, 'username' => $u['username']]);
    }

    public function keluar()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
