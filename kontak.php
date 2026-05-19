<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/components.php';

$data = site_data();
$title = 'Kontak - Yayasan Cendekia';
$description = 'Hubungi Yayasan Cendekia dan unit sekolah TK, SD, SMP, SMK, PAUD, serta Cabang Rosari.';

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="bg-slate-950 py-20 text-white sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-wide text-teal-200">Kontak</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">Temukan admin sekolah dan lokasi Yayasan Cendekia.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">Hubungi yayasan atau pilih WhatsApp unit sekolah sesuai kebutuhan pendaftaran dan informasi jenjang.</p>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Yayasan</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Informasi utama</h2>
                <div class="mt-6 grid gap-4 text-sm text-slate-600">
                    <p class="rounded-lg border border-slate-200 bg-slate-50 p-4"><?= e($data['foundation']['address']); ?></p>
                    <a class="rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:border-teal-300 hover:text-teal-700" href="mailto:<?= e($data['foundation']['email']); ?>"><?= e($data['foundation']['email']); ?></a>
                    <a class="rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:border-teal-300 hover:text-teal-700" href="<?= e(whatsapp_url($data['foundation']['phone'], 'Halo Yayasan Cendekia, saya ingin bertanya.')); ?>" target="_blank" rel="noopener">WhatsApp Yayasan</a>
                </div>
            </div>
            <?php render_map($data['foundation']['maps_embed'], 'Lokasi Yayasan Cendekia'); ?>
        </div>
    </section>

    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-2xl">
                <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Admin Sekolah</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Kontak WhatsApp tiap unit.</h2>
            </div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($data['schools'] as $school): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-bold text-teal-700"><?= e($school['level']); ?></p>
                        <h3 class="mt-2 text-xl font-bold text-slate-950"><?= e($school['name']); ?></h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600"><?= e($school['description']); ?></p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="<?= e(whatsapp_url($school['phone'], 'Halo ' . $school['name'] . ', saya ingin bertanya.')); ?>" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-teal-800" target="_blank" rel="noopener">WhatsApp</a>
                            <a href="<?= e($school['page']); ?>" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">Detail</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
