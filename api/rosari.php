<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/components.php';

$data = site_data();
$title = 'Cabang Losari - Yayasan Cendekia';
$description = 'Pilih pendaftaran TKIT Cendekia 2 Losari atau SD IT Cendekia 2 Losari sesuai kebutuhan anak.';

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="relative isolate overflow-hidden bg-slate-950">
        <img src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=1600&q=80" alt="Cabang Losari" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-40">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950 via-slate-950/82 to-slate-900/30"></div>
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <span class="inline-flex rounded-full bg-white/12 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/20">Cabang Losari</span>
            <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">Pendaftaran Cabang Losari.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-100">Silakan pilih TKIT Cendekia 2 Losari atau SD IT Cendekia 2 Losari. Setiap halaman berisi informasi singkat, kontak admin, dan form pendaftaran awal.</p>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Cabang Losari</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Pilih sesuai jenjang anak.</h2>
                <p class="mt-4 text-base leading-8 text-slate-600">Untuk anak usia dini, orang tua bisa melihat TKIT Cendekia 2 Losari. Untuk jenjang dasar, silakan masuk ke halaman SD IT Cendekia 2 Losari.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <?php foreach (schools_by_campus($data, 'losari') as $key => $school): ?>
                    <?php render_school_card($key, $school); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
