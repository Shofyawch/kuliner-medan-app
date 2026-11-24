<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use Firebase\JWT\JWT;

class Auth extends BaseController
{
    use ResponsApi;

    /** POST /api/v1/auth/login  {"username": "...", "password": "..."} */
    public function login()
    {
        $in = $this->bodyJson();
        if ($in === null) {
            return $this->gagal('Body harus berupa JSON.', 400);
        }

        $username = trim((string) ($in['username'] ?? ''));
        $password = (string) ($in['password'] ?? '');

        $err = [];
        if ($username === '') {
            $err['username'] = 'Wajib diisi.';
        }
        if ($password === '') {
            $err['password'] = 'Wajib diisi.';
        }
        if ($err !== []) {
            return $this->gagal('Data login tidak lengkap.', 422, $err);
        }

        $rahasia = (string) env('JWT_SECRET', '');
        if ($rahasia === '') {
            return $this->gagal('API belum dikonfigurasi.', 503);
        }

        try {
            $u = db_connect()->table('pengguna')
                ->where('username', $username)
                ->where('aktif', true)
                ->get()->getRowArray();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        if ($u === null || ! password_verify($password, $u['password_hash'])) {
            return $this->gagal('Username atau password salah.', 401);
        }

        $ttl = max(60, (int) env('JWT_TTL', 7200));
        $now = time();
        $token = JWT::encode([
            'iss'  => 'kuliner-medan-api',
            'iat'  => $now,
            'exp'  => $now + $ttl,
            'sub'  => (int) $u['id'],
            'usr'  => $u['username'],
            'role' => $u['role'],
        ], $rahasia, 'HS256');

        return $this->sukses([
            'token'         => $token,
            'tipe'          => 'Bearer',
            'berlaku_detik' => $ttl,
            'username'      => $u['username'],
            'role'          => $u['role'],
        ]);
    }

    /** POST /api/v1/auth/daftar  {"username": "...", "password": "..."}  (role selalu "user") */
    public function daftar()
    {
        $in = $this->bodyJson();
        if ($in === null) {
            return $this->gagal('Body harus berupa JSON.', 400);
        }

        $err = $this->validasi($in, [
            'username' => 'required|min_length[3]|max_length[30]|alpha_dash',
            'password' => 'required|min_length[8]|max_length[72]',
        ]);
        if ($err !== null) {
            return $this->gagal('Data tidak valid.', 422, $err);
        }

        $username = trim((string) $in['username']);
        $db       = db_connect();

        try {
            if ($db->table('pengguna')->where('username', $username)->countAllResults() > 0) {
                return $this->gagal('Username sudah dipakai.', 409);
            }

            $db->table('pengguna')->insert([
                'username'      => $username,
                'password_hash' => password_hash((string) $in['password'], PASSWORD_DEFAULT),
                'role'          => 'user',
            ]);
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        return $this->sukses(['username' => $username, 'role' => 'user'], [], 201);
    }
}
