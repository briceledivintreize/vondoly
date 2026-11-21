<?php
/**
 * Contrôleur frontal : toutes les requêtes passent par ici (URLs propres).
 * Serveur de développement : php -S localhost:8080 -t public public/index.php
 */
declare(strict_types=1);

// Fichiers statiques servis directement par le serveur intégré de PHP
if (PHP_SAPI === 'cli-server') {
    $f = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($f !== __DIR__ . '/' && is_file($f)) return false;
}

require dirname(__DIR__) . '/app/bootstrap.php';

$path = '/' . trim(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)), '/');
$bp = base_path();
if ($bp && str_starts_with($path, $bp)) $path = '/' . trim(substr($path, strlen($bp)), '/');

// Première installation : assistant de création d'un site d'auteur
if (!db_installed() || $path === '/install') {
    require APP . '/install.php';
    exit;
}

// Administration
if ($path === '/admin' || str_starts_with($path, '/admin/')) {
    require APP . '/admin/router.php';
    exit;
}

require APP . '/pages.php';
route_public($path);
