<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/components.php';

$key = preg_replace('/[^a-z0-9-]/i', '', (string) ($_GET['id'] ?? ''));
$school = site_data()['schools'][$key] ?? null;
if (!is_array($school)) {
    http_response_code(404);
    exit('Unit sekolah tidak ditemukan.');
}
$title = $school['name'] . ' - Yayasan Cendekia';
$description = $school['description'];
require __DIR__ . '/includes/header.php';
render_school_page($key);
require __DIR__ . '/includes/footer.php';
