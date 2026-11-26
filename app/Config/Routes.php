<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Kuliner::index');
$routes->get('restoran/(:segment)', 'Kuliner::detail/$1');
$routes->get('terdekat', 'Terdekat::index');
$routes->get('hotel', 'Hotel::index');
$routes->get('hotel/(:num)', 'Hotel::detail/$1');
$routes->post('hotel/(:num)/ulasan', 'Hotel::kirimUlasan/$1');
$routes->post('hotel/(:num)/ulasan/(:num)/hapus', 'Hotel::hapusUlasan/$1/$2');

// ---- Halaman Informasi & Bantuan ----
$routes->get('faq', 'Info::faq');
$routes->get('kontak', 'Info::kontak');
$routes->get('tentang', 'Info::tentang');
$routes->get('ketentuan', 'Info::ketentuan');

// ---- Autentikasi Pengguna Web ----
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::prosesLogin');
$routes->get('daftar', 'Auth::daftar');
$routes->post('daftar', 'Auth::prosesDaftar');
$routes->post('logout', 'Auth::logout');

// ---- Kirim Ulasan Restoran Web ----
$routes->post('restoran/(:segment)/ulasan', 'Kuliner::simpanUlasan/$1');


// ---- API v1 ----
// Publik: login
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
    $routes->post('auth/login', 'Auth::login');
});

// Baca data: Bearer token (admin/user) atau X-API-KEY
$routes->group('api/v1', ['filter' => 'apiauth', 'namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
    $routes->get('kategori', 'Restoran::kategori');
    $routes->get('restoran', 'Restoran::index');
    $routes->get('restoran/terdekat', 'Restoran::terdekat');
    $routes->get('restoran/(:segment)', 'Restoran::detail/$1');
});

// Tulis data: hanya admin (Bearer token)
$routes->group('api/v1', ['filter' => 'apiauth:admin', 'namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
    $routes->post('restoran/lengkap', 'RestoranTulis::createLengkap');
    $routes->post('restoran', 'RestoranTulis::create');
    $routes->match(['put', 'patch'], 'restoran/(:num)', 'RestoranTulis::update/$1');
    $routes->delete('restoran/(:num)', 'RestoranTulis::delete/$1');
    $routes->post('restoran/(:num)/menu', 'MenuTulis::create/$1');
    $routes->match(['put', 'patch'], 'menu/(:num)', 'MenuTulis::update/$1');
    $routes->delete('menu/(:num)', 'MenuTulis::delete/$1');
});

// ---- API v1: pendaftaran, ulasan ----
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
    $routes->post('auth/daftar', 'Auth::daftar');
    $routes->get('restoran/(:num)/ulasan', 'Ulasan::daftar/$1');
});

$routes->group('api/v1', ['filter' => 'apiauth:login', 'namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
    $routes->post('restoran/(:num)/ulasan', 'Ulasan::create/$1');
    $routes->match(['put', 'patch'], 'ulasan/(:num)', 'Ulasan::update/$1');
    $routes->delete('ulasan/(:num)', 'Ulasan::delete/$1');
});

$routes->group('api/v1', ['filter' => 'apiauth:admin', 'namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
    $routes->get('ulasan', 'Ulasan::semua');
});

// ---- API v1: preflight CORS dan alamat yang tidak dikenal (selalu paling bawah) ----
$routes->options('api/v1/(:any)', static function () {
    return service('response')->setStatusCode(204);
});
$routes->match(['get', 'post', 'put', 'patch', 'delete'], 'api/v1/(:any)', static function () {
    return service('response')->setStatusCode(404)->setJSON([
        'status'  => 'error',
        'message' => 'Alamat API tidak ditemukan.',
    ]);
});

// ---- Dashboard Admin ----
$routes->get('admin', 'Admin::index');
$routes->post('admin/keluar', 'Admin::keluar');
