<?php
$data = site_data();
$title = $title ?? $data['foundation']['name'];
$description = $description ?? $data['foundation']['description'];
$items = nav_items($data);
?>
<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title); ?></title>
    <meta name="description" content="<?= e($description); ?>">
    <meta name="theme-color" content="#0f766e">
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://images.unsplash.com">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Arial', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 18px 45px rgba(15, 23, 42, 0.10)'
                    }
                }
            }
        };
    </script>
    <style>
        .glass-nav { backdrop-filter: blur(18px); }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 glass-nav">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8" aria-label="Navigasi utama">
            <a href="index.php" class="flex items-center gap-3" aria-label="Beranda Yayasan Cendekia">
                <span class="grid h-11 w-11 place-items-center rounded-lg bg-teal-700 text-base font-bold text-white shadow-soft">YC</span>
                <span>
                    <span class="block text-sm font-bold tracking-wide text-slate-950">Yayasan Cendekia</span>
                    <span class="block text-xs text-slate-500">Sekolah Indonesia</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 lg:flex">
                <?php foreach ($items as $item): ?>
                    <a href="<?= e($item['url']); ?>" class="rounded-md px-3 py-2 text-sm font-semibold transition <?= is_active($item['url']) ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'; ?>">
                        <?= e($item['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <a href="<?= e(whatsapp_url($data['foundation']['phone'], 'Halo Yayasan Cendekia, saya ingin bertanya tentang pendaftaran.')); ?>" class="hidden rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-200 lg:inline-flex" target="_blank" rel="noopener">
                Hubungi Kami
            </a>

            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden" data-mobile-toggle aria-controls="mobile-menu" aria-expanded="false">
                <span class="sr-only">Buka menu</span>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                </svg>
            </button>
        </nav>

        <div id="mobile-menu" class="hidden border-t border-slate-200 bg-white px-4 py-4 lg:hidden" data-mobile-menu>
            <div class="mx-auto grid max-w-7xl gap-1">
                <?php foreach ($items as $item): ?>
                    <a href="<?= e($item['url']); ?>" class="rounded-md px-3 py-3 text-sm font-semibold <?= is_active($item['url']) ? 'bg-teal-50 text-teal-800' : 'text-slate-700 hover:bg-slate-100'; ?>">
                        <?= e($item['label']); ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?= e(whatsapp_url($data['foundation']['phone'], 'Halo Yayasan Cendekia, saya ingin bertanya tentang pendaftaran.')); ?>" class="mt-2 rounded-md bg-teal-700 px-3 py-3 text-center text-sm font-semibold text-white" target="_blank" rel="noopener">
                    Hubungi WhatsApp
                </a>
            </div>
        </div>
    </header>
