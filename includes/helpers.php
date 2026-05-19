<?php

function site_data(): array
{
    static $data = null;

    if ($data === null) {
        $data = require __DIR__ . '/../data/sekolah.php';
    }

    return $data;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function current_page(): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
}

function is_active(string $page): bool
{
    return current_page() === $page;
}

function whatsapp_url(string $phone, string $message): string
{
    return 'https://wa.me/' . preg_replace('/\D+/', '', $phone) . '?text=' . rawurlencode($message);
}

function nav_items(array $data): array
{
    $items = [
        ['label' => 'Yayasan', 'url' => 'index.php'],
    ];

    foreach (schools_by_campus($data, 'cirebon') as $school) {
        $items[] = ['label' => $school['short_name'], 'url' => $school['page']];
    }

    $items[] = ['label' => 'Cabang Losari', 'url' => 'index.php#cabang-losari'];
    $items[] = ['label' => 'FAQ', 'url' => 'faq.php'];
    $items[] = ['label' => 'Kontak', 'url' => 'kontak.php'];

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
    $map = [
        'emerald' => ['badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'button' => 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-300', 'text' => 'text-emerald-700'],
        'sky' => ['badge' => 'bg-sky-50 text-sky-700 ring-sky-200', 'button' => 'bg-sky-600 hover:bg-sky-700 focus:ring-sky-300', 'text' => 'text-sky-700'],
        'indigo' => ['badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200', 'button' => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-300', 'text' => 'text-indigo-700'],
        'amber' => ['badge' => 'bg-amber-50 text-amber-800 ring-amber-200', 'button' => 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-300', 'text' => 'text-amber-700'],
        'rose' => ['badge' => 'bg-rose-50 text-rose-700 ring-rose-200', 'button' => 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-300', 'text' => 'text-rose-700'],
    ];

    return $map[$accent] ?? $map['sky'];
}
