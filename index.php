<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/components.php';

$data = site_data();
$title = 'Yayasan Cendekia - Pendidikan Terpadu Indonesia';
$description = $data['foundation']['description'];

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="relative isolate overflow-hidden bg-slate-950">
        <img src="<?= e($data['foundation']['hero_image']); ?>" alt="Kegiatan belajar Yayasan Cendekia" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-40">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950 via-slate-950/86 to-teal-950/45"></div>
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-28">
            <div>
                <span class="inline-flex rounded-full bg-white/12 px-4 py-2 text-sm font-semibold text-teal-100 ring-1 ring-white/20">Pendidikan terpadu Yayasan Cendekia</span>
                <h1 class="mt-6 max-w-4xl text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl"><?= e($data['foundation']['tagline']); ?></h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-100"><?= e($data['foundation']['description']); ?></p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#unit-sekolah" class="inline-flex items-center justify-center rounded-md bg-teal-600 px-5 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-300">Lihat Unit Sekolah</a>
                    <a href="kontak.php" class="inline-flex items-center justify-center rounded-md bg-white px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-slate-100">Kontak Yayasan</a>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-white/15 bg-white/10 p-5 text-white backdrop-blur">
                    <p class="text-3xl font-bold">6</p>
                    <p class="mt-1 text-sm text-slate-200">Halaman jenjang</p>
                </div>
                <div class="rounded-lg border border-white/15 bg-white/10 p-5 text-white backdrop-blur">
                    <p class="text-3xl font-bold">2</p>
                    <p class="mt-1 text-sm text-slate-200">Lokasi layanan</p>
                </div>
                <div class="rounded-lg border border-white/15 bg-white/10 p-5 text-white backdrop-blur sm:col-span-2">
                    <p class="text-3xl font-bold">Online</p>
                    <p class="mt-1 text-sm text-slate-200">Pendaftaran melalui Google Form tiap sekolah</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Konsep Pendaftaran</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Konten dibuat sesuai jenjang agar calon orang tua lebih mudah memilih.</h2>
                </div>
                <p class="text-base leading-8 text-slate-600">Cendekia Cirebon tampil sebagai paket pendidikan lengkap TK, SD, SMP, dan SMK. Cabang Losari disiapkan dengan halaman TK dan SD terpisah agar pesan pendaftaran, suasana belajar, dan ajakan daftarnya lebih relevan untuk usia anak.</p>
            </div>
        </div>
    </section>

    <section id="unit-sekolah" class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Cendekia Cirebon</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Satu paket website untuk TK, SD, SMP, dan SMK.</h2>
                <p class="mt-4 text-base leading-8 text-slate-600">Setiap jenjang memiliki konten khusus, halaman pendaftaran, WhatsApp admin, dan lokasi sehingga informasi lebih lengkap untuk calon siswa.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach (schools_by_campus($data, 'cirebon') as $key => $school): ?>
                    <?php render_school_card($key, $school); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="cabang-losari" class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Cabang Losari</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Halaman pendaftaran terpisah untuk TK dan SD Losari.</h2>
                <p class="mt-4 text-base leading-8 text-slate-600">TK Losari menonjolkan suasana belajar yang aman dan menyenangkan, sedangkan SD Losari menonjolkan fondasi akademik, karakter, dan kedisiplinan.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <?php foreach (schools_by_campus($data, 'losari') as $key => $school): ?>
                    <?php render_school_card($key, $school); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
            <div class="rounded-lg bg-slate-950 p-8 text-white shadow-soft">
                <p class="text-sm font-bold uppercase tracking-wide text-teal-200">Pendaftaran</p>
                <h2 class="mt-3 text-3xl font-bold">Mulai dari jenjang yang tepat.</h2>
                <p class="mt-4 text-sm leading-7 text-slate-200">Pilih halaman sekolah, isi data awal, lalu lanjut ke Google Form unit terkait. Admin sekolah akan membantu tahapan berikutnya.</p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="#unit-sekolah" class="inline-flex justify-center rounded-md bg-white px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-slate-100">Cendekia Cirebon</a>
                    <a href="#cabang-losari" class="inline-flex justify-center rounded-md border border-white/20 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">Cabang Losari</a>
                </div>
            </div>
            <?php render_map($data['foundation']['maps_embed'], 'Lokasi Yayasan Cendekia'); ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
