<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Kuliner::index');
$routes->get('restoran/(:segment)', 'Kuliner::detail/$1');
$routes->get('terdekat', 'Terdekat::index');
