<?php
require_once __DIR__ . '/includes/database.php';

$path = rawurldecode((string) ($_GET['path'] ?? ''));
$path = ltrim(str_replace('\\', '/', $path), '/');

if (!preg_match('#^uploads/(articles|schools)/[A-Za-z0-9._-]+\.(jpe?g|png|webp|gif)$#i', $path)) {
    http_response_code(404);
    exit('Not found');
}

$upload = cms_load_upload($path);

if ($upload === null) {
    http_response_code(404);
    exit('Not found');
}

$contentType = $upload['content_type'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($contentType, $allowedMimeTypes, true)) {
    http_response_code(404);
    exit('Not found');
}

$content = $upload['content'];
$etag = '"' . sha1($content) . '"';

if (trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
    http_response_code(304);
    exit;
}

header('Content-Type: ' . $contentType);
header('Content-Length: ' . strlen($content));
header('Cache-Control: public, max-age=31536000, immutable');
header('ETag: ' . $etag);
header('X-Content-Type-Options: nosniff');

echo $content;
