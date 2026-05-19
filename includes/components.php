<?php

function render_school_card(string $key, array $school): void
{
    $accent = accent_classes($school['accent']);
    ?>
    <article class="group overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-soft">
        <div class="aspect-[16/10] overflow-hidden bg-slate-200">
            <img src="<?= e($school['hero_image']); ?>" alt="<?= e($school['name']); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        </div>
        <div class="p-6">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 <?= e($accent['badge']); ?>"><?= e($school['level']); ?></span>
            <h3 class="mt-4 text-xl font-bold text-slate-950"><?= e($school['name']); ?></h3>
            <p class="mt-3 text-sm leading-7 text-slate-600"><?= e($school['description']); ?></p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= e($school['page']); ?>" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Lihat Sekolah</a>
                <a href="<?= e($school['page']); ?>#pendaftaran" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-teal-300 hover:text-teal-700">Daftar</a>
            </div>
        </div>
    </article>
    <?php
}

function render_school_hero(array $school): void
{
    $accent = accent_classes($school['accent']);
    ?>
    <section class="relative isolate overflow-hidden bg-slate-950">
        <img src="<?= e($school['hero_image']); ?>" alt="<?= e($school['name']); ?>" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-40">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950 via-slate-950/82 to-slate-900/30"></div>
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <span class="inline-flex rounded-full bg-white/12 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/20"><?= e($school['level']); ?></span>
            <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl"><?= e($school['name']); ?></h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-100"><?= e($school['description']); ?></p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#pendaftaran" class="inline-flex items-center justify-center rounded-md px-5 py-3 text-sm font-bold text-white shadow-soft transition focus:outline-none focus:ring-4 <?= e($accent['button']); ?>">Daftar Sekarang</a>
                <a href="<?= e(whatsapp_url($school['phone'], 'Halo ' . $school['name'] . ', saya ingin bertanya tentang pendaftaran.')); ?>" class="inline-flex items-center justify-center rounded-md bg-white px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-slate-100" target="_blank" rel="noopener">Chat WhatsApp</a>
            </div>
        </div>
    </section>
    <?php
}

function render_registration_preview(array $school): void
{
    $entries = $school['form_entries'];
    $iframeName = 'hidden_google_form_' . preg_replace('/[^a-z0-9]+/', '_', strtolower($school['short_name']));
    ?>
    <form class="grid gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm" action="<?= e($school['form_url']); ?>" method="post" target="<?= e($iframeName); ?>" data-google-form>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Nama lengkap
                <input type="text" name="<?= e($entries['nama']); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100" placeholder="Nama calon siswa" required>
            </label>
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Jenis kelamin
                <select name="<?= e($entries['jenis_kelamin']); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100" required>
                    <option>Laki-laki</option>
                    <option>Perempuan</option>
                </select>
            </label>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Umur / tanggal lahir
                <input type="text" name="<?= e($entries['tanggal_lahir']); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100" placeholder="Contoh: 7 tahun / 12-05-2019" required>
            </label>
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Nomor HP
                <input type="tel" name="<?= e($entries['nomor_hp']); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100" placeholder="08xxxxxxxxxx" required>
            </label>
        </div>
        <label class="grid gap-2 text-sm font-semibold text-slate-700">
            Alamat
            <textarea name="<?= e($entries['alamat']); ?>" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100" placeholder="Alamat domisili" required></textarea>
        </label>
        <label class="grid gap-2 text-sm font-semibold text-slate-700">
            Pilihan sekolah
            <input type="text" name="<?= e($entries['pilihan_sekolah']); ?>" value="<?= e($school['name']); ?>" readonly class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3 font-normal text-slate-600">
        </label>
        <button type="submit" class="rounded-md bg-teal-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-200">
            Kirim Pendaftaran
        </button>
        <p class="hidden rounded-md bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200" data-form-success>Terima kasih. Data pendaftaran sedang dikirim ke Google Form.</p>
        <p class="text-xs leading-6 text-slate-500">Setelah dikirim, admin sekolah akan menindaklanjuti data pendaftaran ini.</p>
        <iframe name="<?= e($iframeName); ?>" class="hidden" title="Google Form submission target" data-google-form-frame></iframe>
    </form>
    <?php
}

function render_map(string $embedUrl, string $title): void
{
    ?>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-100 shadow-sm">
        <iframe title="<?= e($title); ?>" src="<?= e($embedUrl); ?>" class="h-80 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    <?php
}

function render_school_page(string $key): void
{
    $data = site_data();
    $school = $data['schools'][$key];
    ?>
    <main>
        <?php render_school_hero($school); ?>

        <section class="bg-white py-16 sm:py-20">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_0.85fr] lg:px-8">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Profil Sekolah</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Lingkungan belajar yang dekat dengan anak.</h2>
                    <p class="mt-5 text-base leading-8 text-slate-600"><?= e($school['description']); ?></p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                            <p class="text-2xl font-bold text-slate-950">Aktif</p>
                            <p class="mt-1 text-sm text-slate-600">Pembinaan karakter</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                            <p class="text-2xl font-bold text-slate-950">Rapi</p>
                            <p class="mt-1 text-sm text-slate-600">Komunikasi orang tua</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                            <p class="text-2xl font-bold text-slate-950">Siap</p>
                            <p class="mt-1 text-sm text-slate-600">Pendaftaran online</p>
                        </div>
                    </div>
                </div>
                <aside class="rounded-lg bg-slate-950 p-6 text-white shadow-soft">
                    <p class="text-sm font-semibold text-teal-200">Pendaftaran</p>
                    <h2 class="mt-3 text-2xl font-bold">Hubungi admin <?= e($school['short_name']); ?></h2>
                    <p class="mt-4 text-sm leading-7 text-slate-200">Admin akan membantu menjawab pertanyaan seputar biaya, jadwal, berkas, dan langkah pendaftaran.</p>
                    <div class="mt-6 grid gap-3">
                        <a href="<?= e(whatsapp_url($school['phone'], 'Halo ' . $school['name'] . ', saya ingin bertanya tentang pendaftaran.')); ?>" class="rounded-md bg-white px-4 py-3 text-center text-sm font-bold text-slate-950 transition hover:bg-slate-100" target="_blank" rel="noopener">WhatsApp <?= e($school['short_name']); ?></a>
                        <a href="#pendaftaran" class="rounded-md border border-white/20 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-white/10">Isi Form Pendaftaran</a>
                    </div>
                </aside>
            </div>
        </section>

        <section id="pendaftaran" class="bg-slate-50 py-16 sm:py-20">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Pendaftaran</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Isi data calon siswa.</h2>
                    <p class="mt-5 text-base leading-8 text-slate-600">Data ini menjadi langkah awal agar admin sekolah dapat menghubungi orang tua dan membantu proses berikutnya.</p>
                </div>
                <?php render_registration_preview($school); ?>
            </div>
        </section>

        <section class="bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 max-w-2xl">
                    <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Lokasi</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Google Maps <?= e($school['name']); ?></h2>
                </div>
                <?php render_map($school['maps_embed'], 'Lokasi ' . $school['name']); ?>
            </div>
        </section>
    </main>
    <?php
}
