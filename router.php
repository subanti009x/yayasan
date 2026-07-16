<?php

/**
 * Development router for PHP's built-in server.
 * Run: php -S 127.0.0.1:8000 router.php
 */

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? trim($requestPath, '/') : '';

// Let the built-in server serve public assets directly from the repository root.
if ($requestPath !== '' && is_file(__DIR__ . DIRECTORY_SEPARATOR . $requestPath)) {
    return false;
}

$routes = [
    '' => 'index.php',
    'index.php' => 'index.php',
    'admin' => 'admin.php',
    'admin.php' => 'admin.php',
    'articles' => 'articles.php',
    'articles.php' => 'articles.php',
    'artikel.php' => 'artikel.php',
    'faq' => 'faq.php',
    'faq.php' => 'faq.php',
    'kontak' => 'kontak.php',
    'kontak.php' => 'kontak.php',
    'sd' => 'sd.php',
    'sd.php' => 'sd.php',
    'smp' => 'smp.php',
    'smp.php' => 'smp.php',
    'smk' => 'smk.php',
    'smk.php' => 'smk.php',
    'tk-paud' => 'tk-paud.php',
    'tk-paud.php' => 'tk-paud.php',
    'losari-tk' => 'losari-tk.php',
    'losari-tk.php' => 'losari-tk.php',
    'losari-sd' => 'losari-sd.php',
    'losari-sd.php' => 'losari-sd.php',
    'rosari' => 'rosari.php',
    'rosari.php' => 'rosari.php',
    'uploads.php' => 'uploads.php',
];

if (preg_match('#^artikel-([a-z0-9-]+)$#i', $requestPath, $matches)) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/api/artikel.php';
    return true;
}

if (isset($routes[$requestPath])) {
    require __DIR__ . '/api/' . $routes[$requestPath];
    return true;
}

http_response_code(404);
require __DIR__ . '/api/includes/helpers.php';
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>404 - Halaman Tidak Ditemukan</title></head>
<body><main><h1>404 - Halaman Tidak Ditemukan</h1><p>Alamat yang diminta tidak tersedia.</p><p><a href="/">Kembali ke beranda</a></p></main></body></html>
