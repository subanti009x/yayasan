<?php
require_once __DIR__ . '/articles.php';

function load_site_data(): array
{
    $path = __DIR__ . '/../data/site.json';
    if (!is_file($path)) return [];
    $json = file_get_contents($path);
    $data = json_decode($json ?: '[]', true);
    return is_array($data) ? $data : [];
}

function save_site_data(array $data): bool
{
    $path = __DIR__ . '/../data/site.json';
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) return false;
    
    if (getenv('VERCEL') === '1') {
        $githubToken = getenv('GITHUB_TOKEN');
        if ($githubToken) {
            return commit_to_github('api/data/site.json', $json . PHP_EOL, 'update site configuration', $githubToken);
        }
        return false;
    }
    return file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false;
}

function load_faq_data(): array
{
    $path = __DIR__ . '/../data/faq.json';
    if (!is_file($path)) return [];
    $json = file_get_contents($path);
    $data = json_decode($json ?: '[]', true);
    return is_array($data) ? $data : [];
}

function save_faq_data(array $data): bool
{
    $path = __DIR__ . '/../data/faq.json';
    $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) return false;
    
    if (getenv('VERCEL') === '1') {
        $githubToken = getenv('GITHUB_TOKEN');
        if ($githubToken) {
            return commit_to_github('api/data/faq.json', $json . PHP_EOL, 'update FAQ data', $githubToken);
        }
        return false;
    }
    return file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false;
}
