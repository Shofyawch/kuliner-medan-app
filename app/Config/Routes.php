<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Kuliner::index');
$routes->get('restoran/(:segment)', 'Kuliner::detail/$1');
$routes->get('terdekat', 'Terdekat::index');

$routes->group('api/v1', ['filter' => 'apikey', 'namespace' => 'App\Controllers\Api\V1'], static function ($routes) {
    $routes->get('kategori', 'Restoran::kategori');
    $routes->get('restoran', 'Restoran::index');
    $routes->get('restoran/terdekat', 'Restoran::terdekat');
    $routes->get('restoran/(:segment)', 'Restoran::detail/$1');
});
