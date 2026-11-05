<?php
/**
 * FCDF Pro - Front Controller
 * All requests are routed through this file (see .htaccess).
 * This file lives at the project root, which is also the web server's
 * Document Root (flat deployment layout — see README.md).
 */

require __DIR__ . '/src/Config/config.php';

/** @var \App\Core\Router $router */
$router = require __DIR__ . '/src/routes.php';
$router->dispatch();
