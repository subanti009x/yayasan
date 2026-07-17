<?php

$root = dirname(__DIR__);
$public = $root . '/public';
define('BUILD_STATIC', true);
require_once $root . '/api/includes/helpers.php';
require_once $root . '/api/includes/cms.php';
$pages = [
    'index.php' => 'index.html',
    'articles.php' => 'articles.html',
    'sd.php' => 'sd.html',
    'smp.php' => 'smp.html',
    'smk.php' => 'smk.html',
    'tk-paud.php' => 'tk-paud.html',
    'losari-tk.php' => 'losari-tk.html',
    'losari-sd.php' => 'losari-sd.html',
    'rosari.php' => 'rosari.html',
    'faq.php' => 'faq.html',
    'kontak.php' => 'kontak.html',
];
$schoolSourceKeys = ['sd.php' => 'sd', 'smp.php' => 'smp', 'smk.php' => 'smk', 'tk-paud.php' => 'tk-paud', 'losari-tk.php' => 'losari-tk', 'losari-sd.php' => 'losari-sd', 'rosari.php' => 'rosari'];

if (!is_dir($public)) {
    mkdir($public, 0777, true);
}

foreach ($pages as $source => $target) {
    if (isset($schoolSourceKeys[$source]) && !isset(load_site_data()['schools'][$schoolSourceKeys[$source]])) {
        continue;
    }
    $_SERVER['SCRIPT_NAME'] = '/' . $source;

    ob_start();
    require $root . '/api/' . $source;
    $html = ob_get_clean();

    $html = str_replace(
        ['href="' . pathinfo($source, PATHINFO_FILENAME) . '.php', 'href="index.php', 'src="assets/'],
        ['href="' . pathinfo($source, PATHINFO_FILENAME) . '.html', 'href="index.html', 'src="assets/'],
        $html
    );

    $html = str_replace(
        ['href="sd.php', 'href="smp.php', 'href="smk.php', 'href="tk-paud.php', 'href="losari-tk.php', 'href="losari-sd.php', 'href="rosari.php', 'href="articles.php', 'href="faq.php', 'href="kontak.php'],
        ['href="sd.html', 'href="smp.html', 'href="smk.html', 'href="tk-paud.html', 'href="losari-tk.html', 'href="losari-sd.html', 'href="rosari.html', 'href="articles.html', 'href="faq.html', 'href="kontak.html'],
        $html
    );

    $html = rewrite_article_links($html);

    file_put_contents($public . '/' . $target, $html);
}

foreach (load_site_data()['schools'] ?? [] as $key => $school) {
    if (!str_starts_with((string) ($school['page'] ?? ''), 'sekolah-')) continue;
    $_SERVER['SCRIPT_NAME'] = '/sekolah.php';
    $_GET['id'] = $key;
    ob_start(); require $root . '/api/sekolah.php'; $html = ob_get_clean();
    $html = str_replace(['href="index.php', 'href="articles.php', 'href="faq.php', 'href="kontak.php'], ['href="index.html', 'href="articles.html', 'href="faq.html', 'href="kontak.html'], $html);
    $html = preg_replace('/href="sekolah-([a-z0-9-]+)"/i', 'href="sekolah-$1.html"', $html) ?? $html;
    file_put_contents($public . '/sekolah-' . $key . '.html', $html);
}

require_once $root . '/api/includes/helpers.php';
require_once $root . '/api/includes/articles.php';

foreach (load_articles(true) as $article) {
    $_SERVER['SCRIPT_NAME'] = '/artikel.php';
    $_GET['slug'] = $article['slug'];

    ob_start();
    require $root . '/api/artikel.php';
    $html = ob_get_clean();

    $html = str_replace(
        ['href="index.php', 'href="articles.php', 'href="sd.php', 'href="smp.php', 'href="smk.php', 'href="tk-paud.php', 'href="losari-tk.php', 'href="losari-sd.php', 'href="rosari.php', 'href="faq.php', 'href="kontak.php'],
        ['href="index.html', 'href="articles.html', 'href="sd.html', 'href="smp.html', 'href="smk.html', 'href="tk-paud.html', 'href="losari-tk.html', 'href="losari-sd.html', 'href="rosari.html', 'href="faq.html', 'href="kontak.html'],
        $html
    );

    $html = rewrite_article_links($html);

    file_put_contents($public . '/' . article_static_url($article), $html);
}

copy_dir($root . '/assets', $public . '/assets');

function rewrite_article_links(string $html): string
{
    return preg_replace_callback('/href="artikel\.php\?slug=([^"]+)"/', function (array $matches): string {
        return 'href="artikel-' . rawurldecode($matches[1]) . '.html"';
    }, $html) ?? $html;
}

function copy_dir(string $source, string $target): void
{
    if (!is_dir($target)) {
        mkdir($target, 0777, true);
    }

    $items = scandir($source);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $sourcePath = $source . '/' . $item;
        $targetPath = $target . '/' . $item;

        if (is_dir($sourcePath)) {
            copy_dir($sourcePath, $targetPath);
            continue;
        }

        copy($sourcePath, $targetPath);
    }
}
