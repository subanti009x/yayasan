<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/components.php';
require_once __DIR__ . '/includes/articles.php';

$data = site_data();
$latestArticles = array_slice(load_articles(true), 0, 3);
$title = 'Yayasan Cendekia - Pendidikan Terpadu Indonesia';
$description = $data['foundation']['description'];

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="relative isolate overflow-hidden bg-slate-950">
        <img src="<?= e($data['foundation']['hero_image']); ?>" alt="Kegiatan belajar Yayasan Cendekia" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-40">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950 via-slate-950/84 to-primary-950/50"></div>
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-28">
            <div>
                <span class="inline-flex rounded-full bg-white/12 px-4 py-2 text-sm font-semibold text-secondary-100 ring-1 ring-white/20">Pendidikan terpadu Yayasan Cendekia</span>
                <h1 class="mt-6 max-w-4xl text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl"><?= e($data['foundation']['tagline']); ?></h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-100"><?= e($data['foundation']['description']); ?></p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#unit-sekolah" class="inline-flex items-center justify-center rounded-md bg-primary-600 px-5 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-secondary-300">Lihat Unit Sekolah</a>
                    <a href="<?= url('kontak.php'); ?>" class="inline-flex items-center justify-center rounded-md bg-white px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-slate-100">Kontak Yayasan</a>
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
                    <p class="mt-1 text-sm text-slate-200">Pendaftaran mudah secara online untuk tiap sekolah</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Tentang Pendaftaran</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Setiap jenjang punya kebutuhan yang berbeda.</h2>
                </div>
                <p class="text-base leading-8 text-slate-600">Karena itu, informasi sekolah kami pisahkan dengan jelas. Cendekia Cirebon memuat jenjang TK, SD, SMP, dan SMK, sementara Cabang Losari memiliki halaman sendiri untuk TK dan SD agar orang tua lebih mudah melihat pilihan yang sesuai.</p>
            </div>
        </div>
    </section>

    <section id="unit-sekolah" class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Cendekia Cirebon</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Pilihan jenjang lengkap di Cirebon.</h2>
                <p class="mt-4 text-base leading-8 text-slate-600">Mulai dari TK sampai SMK, setiap halaman berisi gambaran singkat sekolah, kontak admin, pendaftaran, dan lokasi.</p>
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
                <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Cabang Losari</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">TK dan SD IT Cendekia 2 Losari.</h2>
                <p class="mt-4 text-base leading-8 text-slate-600">Orang tua bisa langsung memilih halaman yang sesuai. TKIT Cendekia 2 Losari dibuat untuk masa awal anak bersekolah, sementara SD IT Cendekia 2 Losari membantu anak memperkuat dasar belajar dan kebiasaan baik.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <?php foreach (schools_by_campus($data, 'losari') as $key => $school): ?>
                    <?php render_school_card($key, $school); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <style>
                @keyframes gallery-pan {
                    from { transform: translateX(0); }
                    to { transform: translateX(-34rem); }
                }

                .gallery-track {
                    animation: gallery-pan 18s ease-in-out infinite alternate;
                    width: max-content;
                }

                .gallery-window:hover .gallery-track {
                    animation-play-state: paused;
                }

                @media (max-width: 640px) {
                    @keyframes gallery-pan {
                        from { transform: translateX(0); }
                        to { transform: translateX(-52rem); }
                    }
                }
            </style>
            <div class="mb-10 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Kegiatan Sekolah</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Sekilas suasana belajar dan kegiatan siswa.</h2>
                <p class="mt-4 text-base leading-8 text-slate-600">Gambar bergerak ini memberi gambaran kegiatan anak di sekolah. Arahkan kursor ke gambar untuk membaca keterangan kegiatannya.</p>
            </div>
            <div class="gallery-window overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                <div class="gallery-track flex gap-4 p-4">
                    <article class="group relative h-72 w-80 flex-none overflow-hidden rounded-lg bg-slate-200 shadow-sm sm:w-96">
                        <img src="https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=1200&q=80" alt="Suasana belajar siswa" class="h-full w-full object-cover" loading="lazy">
                        <div class="absolute inset-0 flex items-end bg-slate-950/70 p-5 text-white opacity-0 transition duration-300 group-hover:opacity-100">
                            <div>
                                <h3 class="text-lg font-bold">Suasana belajar di kelas</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-100">Siswa belajar bersama guru dengan suasana kelas yang tertib, hangat, dan mudah diikuti.</p>
                            </div>
                        </div>
                    </article>
                    <article class="group relative h-72 w-80 flex-none overflow-hidden rounded-lg bg-slate-200 shadow-sm sm:w-96">
                        <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=80" alt="Kegiatan anak usia dini" class="h-full w-full object-cover" loading="lazy">
                        <div class="absolute inset-0 flex items-end bg-slate-950/70 p-5 text-white opacity-0 transition duration-300 group-hover:opacity-100">
                            <div>
                                <h3 class="text-lg font-bold">Belajar sambil bermain</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-100">Anak usia dini diajak mengenal sekolah melalui kegiatan bermain, bernyanyi, dan bercerita.</p>
                            </div>
                        </div>
                    </article>
                    <article class="group relative h-72 w-80 flex-none overflow-hidden rounded-lg bg-slate-200 shadow-sm sm:w-96">
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=900&q=80" alt="Kegiatan belajar di kelas" class="h-full w-full object-cover" loading="lazy">
                        <div class="absolute inset-0 flex items-end bg-slate-950/70 p-5 text-white opacity-0 transition duration-300 group-hover:opacity-100">
                            <div>
                                <h3 class="text-lg font-bold">Pendampingan belajar</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-100">Guru membantu siswa memahami pelajaran dasar dan membangun keberanian untuk bertanya.</p>
                            </div>
                        </div>
                    </article>
                    <article class="group relative h-72 w-80 flex-none overflow-hidden rounded-lg bg-slate-200 shadow-sm sm:w-96">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=900&q=80" alt="Kegiatan siswa bersama teman" class="h-full w-full object-cover" loading="lazy">
                        <div class="absolute inset-0 flex items-end bg-slate-950/70 p-5 text-white opacity-0 transition duration-300 group-hover:opacity-100">
                            <div>
                                <h3 class="text-lg font-bold">Kegiatan bersama teman</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-100">Siswa belajar bekerja sama, menghargai teman, dan membangun rasa percaya diri.</p>
                            </div>
                        </div>
                    </article>
                    <article class="group relative h-72 w-80 flex-none overflow-hidden rounded-lg bg-slate-200 shadow-sm sm:w-96">
                        <img src="https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=900&q=80" alt="Kegiatan siswa jenjang kejuruan" class="h-full w-full object-cover" loading="lazy">
                        <div class="absolute inset-0 flex items-end bg-slate-950/70 p-5 text-white opacity-0 transition duration-300 group-hover:opacity-100">
                            <div>
                                <h3 class="text-lg font-bold">Praktik dan keterampilan</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-100">Siswa jenjang atas mulai mengenal keterampilan, tanggung jawab, dan kebiasaan kerja.</p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Alur Pendaftaran</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Langkah Pendaftaran</h2>
                <p class="mt-4 text-base leading-8 text-slate-600">Orang tua bisa mulai dari website, lalu admin sekolah akan membantu melanjutkan prosesnya.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-bold text-primary-700">01</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-950">Pilih jenjang</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Buka halaman sekolah yang sesuai dengan usia dan kebutuhan anak.</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-bold text-primary-700">02</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-950">Isi data awal</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Masukkan nama calon siswa, kontak orang tua, dan alamat domisili.</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-bold text-primary-700">03</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-950">Admin menghubungi</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Admin sekolah akan membantu menjawab pertanyaan dan memberi arahan berikutnya.</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-bold text-primary-700">04</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-950">Lanjut pendaftaran</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Orang tua dapat melengkapi berkas dan informasi lain sesuai arahan sekolah.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Artikel Terbaru</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Cerita dan informasi dari sekolah.</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">Artikel terbaru akan otomatis tampil di sini setelah dipublikasikan melalui dashboard admin.</p>
                </div>
                <a href="<?= url('articles.php'); ?>" class="inline-flex rounded-md border border-secondary-200 bg-white px-5 py-3 text-sm font-bold text-primary-700 transition hover:border-primary-300 hover:bg-secondary-50">Lihat Semua Artikel</a>
            </div>
            <?php if ($latestArticles === []): ?>
                <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Belum Ada Artikel</p>
                    <h3 class="mt-3 text-2xl font-bold text-slate-950">Artikel sekolah akan segera hadir.</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Admin dapat menambahkan artikel melalui dashboard.</p>
                </div>
            <?php else: ?>
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($latestArticles as $index => $article): ?>
                        <?php render_article_card($article, $index === 0); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
            <div class="rounded-lg bg-slate-950 p-8 text-white shadow-soft">
                <p class="text-sm font-bold uppercase tracking-wide text-secondary-200">Pendaftaran</p>
                <h2 class="mt-3 text-3xl font-bold">Ingin bertanya atau mendaftar?</h2>
                <p class="mt-4 text-sm leading-7 text-slate-200">Pilih jenjang yang dituju, isi data awal, lalu admin sekolah akan membantu proses berikutnya.</p>
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
