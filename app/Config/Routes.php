<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Kuliner::index');
$routes->get('restoran/(:segment)', 'Kuliner::detail/$1');
$routes->get('terdekat', 'Terdekat::index');


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
});

$routes->group('api/v1', ['filter' => 'apiauth', 'namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
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
