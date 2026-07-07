<?php

function site_data(): array
{
    static $data = null;

    if ($data === null) {
        require_once __DIR__ . '/cms.php';
        $data = load_site_data();
    }

    return $data;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function url(string $path): string
{
    if (getenv('VERCEL') === '1') {
        if ($path === 'index.php') {
            return '/';
        }
        return '/' . preg_replace('/\.php$/', '', $path);
    }
    return $path;
}

function current_page(): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
}

function is_active(string $page): bool
{
    $normalizedPage = preg_replace('/^\//', '', preg_replace('/\.php$/', '', $page));
    $normalizedCurrent = preg_replace('/^\//', '', preg_replace('/\.php$/', '', current_page()));
    
    if ($normalizedCurrent === '') {
        $normalizedCurrent = 'index';
    }
    if ($normalizedPage === '') {
        $normalizedPage = 'index';
    }
    
    // Handle anchor links or query strings if any (e.g. index#cabang-losari)
    $normalizedPage = explode('#', $normalizedPage)[0];
    $normalizedPage = explode('?', $normalizedPage)[0];
    
    return $normalizedCurrent === $normalizedPage;
}

function whatsapp_url(string $phone, string $message): string
{
    return 'https://wa.me/' . preg_replace('/\D+/', '', $phone) . '?text=' . rawurlencode($message);
}

function nav_items(array $data): array
{
    $items = [
        ['label' => 'Yayasan', 'url' => url('index.php')],
    ];

    foreach (schools_by_campus($data, 'cirebon') as $school) {
        $items[] = ['label' => $school['short_name'], 'url' => url($school['page'])];
    }

    $items[] = ['label' => 'Cabang Losari', 'url' => url('index.php') . '#cabang-losari'];
    $items[] = ['label' => 'Artikel', 'url' => url('articles.php')];
    $items[] = ['label' => 'FAQ', 'url' => url('faq.php')];
    $items[] = ['label' => 'Kontak', 'url' => url('kontak.php')];

    return $items;
}

function schools_by_campus(array $data, string $campus): array
{
    $schools = [];

    foreach ($data['schools'] as $key => $school) {
        if (($school['campus'] ?? 'cirebon') !== $campus) {
            continue;
        }

        $schools[$key] = $school;
    }

    return $schools;
}

function accent_classes(string $accent): array
{
    $allowed = ['orange', 'yellow', 'amber', 'emerald', 'sky', 'indigo', 'rose', 'blue', 'teal', 'pink', 'violet', 'fuchsia', 'primary', 'secondary'];
    if (!in_array($accent, $allowed, true)) {
        $accent = 'amber';
    }
    
    return [
        'badge' => "bg-{$accent}-50 text-{$accent}-800 ring-{$accent}-200",
        'button' => "bg-{$accent}-600 hover:bg-{$accent}-700 focus:ring-{$accent}-300",
        'text' => "text-{$accent}-700"
    ];
}
