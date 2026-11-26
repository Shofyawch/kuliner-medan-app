<?php

namespace App\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->has('pengguna_id')) {
            return redirect()->to(session()->get('role') === 'admin' ? '/admin' : '/');
        }

        return view('auth/login', [
            'judul' => 'Masuk - Kuliner Khas Medan',
        ]);
    }

    /**
     * Username dan password diperiksa oleh POST /api/v1/auth/login (dipanggil dari
     * halaman login). Di sini hanya token dari endpoint itu yang diterima untuk
     * membuat sesi web, jadi semua aplikasi memakai endpoint login yang sama.
     */
    public function prosesLogin()
    {
        $token   = (string) $this->request->getPost('token');
        $rahasia = (string) env('JWT_SECRET', '');

        try {
            if ($token === '' || $rahasia === '') {
                throw new \RuntimeException('Token atau JWT_SECRET kosong.');
            }
            $isi = JWT::decode($token, new Key($rahasia, 'HS256'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Login tidak berhasil. Silakan coba lagi.');
        }

        try {
            $user = db_connect()->table('pengguna')
                ->where('id', (int) ($isi->sub ?? 0))
                ->where('aktif', true)
                ->get()->getRowArray();
        } catch (\Throwable $e) {
            log_message('error', 'Login DB error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem database.');
        }

        if ($user === null) {
            return redirect()->back()->withInput()->with('error', 'Akun tidak ditemukan atau tidak aktif.');
        }

        session()->regenerate(true);
        session()->set([
            'pengguna_id' => (int) $user['id'],
            'username'    => $user['username'],
            'role'        => $user['role'],
            'isLoggedIn'  => true,
            'api_token'   => $token,
        ]);

        if ($user['role'] === 'admin') {
            return redirect()->to('/admin');
        }

        return redirect()->to('/')->with('success', 'Selamat datang kembali, ' . $user['username'] . '!');
    }

    public function daftar()
    {
        if (session()->has('pengguna_id')) {
            return redirect()->to('/');
        }

        return view('auth/daftar', [
            'judul' => 'Daftar Akun - Kuliner Khas Medan',
        ]);
    }

    public function prosesDaftar()
    {
        $username   = trim((string) $this->request->getPost('username'));
        $password   = (string) $this->request->getPost('password');
        $konfirmasi = (string) $this->request->getPost('konfirmasi');

        if ($username === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi.');
        }

        if (strlen($username) < 3 || strlen($username) > 30 || !preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
            return redirect()->back()->withInput()->with('error', 'Username harus 3-30 karakter (hanya huruf, angka, minus, atau garis bawah).');
        }

        if (strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', 'Password minimal 8 karakter.');
        }

        if ($password !== $konfirmasi) {
            return redirect()->back()->withInput()->with('error', 'Konfirmasi password tidak cocok.');
        }

        $db = db_connect();
        try {
            $exists = $db->table('pengguna')->where('username', $username)->countAllResults();
            if ($exists > 0) {
                return redirect()->back()->withInput()->with('error', 'Username sudah digunakan, silakan pilih yang lain.');
            }

            $db->table('pengguna')->insert([
                'username'      => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role'          => 'user',
                'aktif'         => true,
            ]);

            $idBaru = $db->insertID();

            session()->set([
                'pengguna_id' => (int) $idBaru,
                'username'    => $username,
                'role'        => 'user',
                'isLoggedIn'  => true,
            ]);

            return redirect()->to('/')->with('success', 'Pendaftaran berhasil! Selamat datang, ' . $username . '.');
        } catch (\Throwable $e) {
            log_message('error', 'Daftar DB error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mendaftar. Silakan coba lagi.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('success', 'Anda telah berhasil keluar.');
    }
}

