<?php
require_once __DIR__ . '/registration.php';

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
                <a href="<?= e(url($school['page'])); ?>" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Lihat Sekolah</a>
                <a href="<?= e(url($school['page'])); ?>#pendaftaran" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Daftar</a>
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
    render_managed_registration_form($school);
    return;

    $campusLabel = ($school['campus'] ?? 'cirebon') === 'losari' ? 'Cabang Losari' : 'Cabang Kota Cirebon';
    $formAction = preg_replace('/\/viewform(\?.*)?$/', '/formResponse', $school['form_url']) ?? $school['form_url'];
    $iframeName = 'hidden_google_form_' . preg_replace('/[^a-z0-9]+/', '_', strtolower($school['short_name']));
    
    $isActualFormAvailable = in_array($school['name'], [
        'TKIT Cendekia',
        'SDIT SABILUL QURAN',
        'SMPIT TAHFIDZUL QURAN'
    ]);
    ?>
    <form class="grid gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm" action="<?= e($formAction); ?>" method="post" target="<?= e($iframeName); ?>" data-google-form>
        <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
            <label>Jangan isi kolom ini<input type="text" name="website" tabindex="-1" autocomplete="off" data-form-honeypot></label>
        </div>
        <div>
            <span class="inline-flex rounded-full bg-secondary-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-primary-800 ring-1 ring-secondary-200"><?= e($campusLabel); ?></span>
            <p class="mt-3 text-sm leading-7 text-slate-600">Data pendaftaran Anda akan langsung diterima oleh admin resmi <?= e($campusLabel); ?>.</p>
        </div>

        <?php if (!$isActualFormAvailable): ?>
            <div class="rounded-md bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 font-semibold mb-2">
                ⚠️ Pendaftaran online untuk <?= e($school['name']); ?> belum dibuka. Silakan hubungi nomor WhatsApp sekolah untuk mendaftar langsung atau mendapatkan informasi lebih lanjut.
            </div>
        <?php endif; ?>

        <?php if ($school['name'] === 'TKIT Cendekia'): ?>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama lengkap
                    <input type="text" name="entry.794168599" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama calon siswa" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama panggilan
                    <input type="text" name="entry.627662662" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama panggilan anak" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    No. WA
                    <input type="tel" name="entry.1052167666" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="08xxxxxxxxxx" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Asal TK
                    <input type="text" name="entry.1697485376" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Asal sekolah/TK sebelumnya" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Ayah
                    <input type="text" name="entry.150230774" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama lengkap Ayah" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pekerjaan Ayah
                    <input type="text" name="entry.1280888089" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pekerjaan Ayah" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Ibu
                    <input type="text" name="entry.1615103016" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama lengkap Ibu" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pekerjaan Ibu
                    <input type="text" name="entry.1310556616" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pekerjaan Ibu" required>
                </label>
            </div>
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Alamat Rumah
                <textarea name="entry.277342110" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Alamat domisili lengkap" required></textarea>
            </label>

        <?php elseif ($school['name'] === 'SDIT SABILUL QURAN'): ?>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Lengkap
                    <input type="text" name="entry.1267371777" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama calon siswa" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Panggilan
                    <input type="text" name="entry.567078244" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama panggilan anak" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Jenis Kelamin
                    <select name="entry.484836580" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
                        <option>Laki-laki</option>
                        <option>Perempuan</option>
                    </select>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Tempat tanggal lahir
                    <input type="text" name="entry.149872710" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Contoh: Cirebon, 12 Mei 2019" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Agama
                    <input type="text" name="entry.1744331187" value="Islam" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Kewarganegaraan
                    <input type="text" name="entry.539583406" value="WNI" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Asal Sekolah
                    <input type="text" name="entry.1590909408" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Asal TK/sekolah sebelumnya" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    No. WA Aktif
                    <input type="tel" name="entry.676149694" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="08xxxxxxxxxx" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Ayah
                    <input type="text" name="entry.935546412" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama lengkap Ayah" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pendidikan Ayah
                    <input type="text" name="entry.1717883185" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pendidikan terakhir Ayah" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pekerjaan Ayah
                    <input type="text" name="entry.2090349951" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pekerjaan Ayah" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Ibu
                    <input type="text" name="entry.837404399" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama lengkap Ibu" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pendidikan Ibu
                    <input type="text" name="entry.1098953204" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pendidikan terakhir Ibu" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pekerjaan Ibu
                    <input type="text" name="entry.216663890" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pekerjaan Ibu" required>
                </label>
            </div>
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Alamat tempat tinggal
                <textarea name="entry.1035227650" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Alamat lengkap domisili" required></textarea>
            </label>

        <?php elseif ($school['name'] === 'SMPIT TAHFIDZUL QURAN'): ?>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Lengkap
                    <input type="text" name="entry.2055510208" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama calon siswa" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Panggilan
                    <input type="text" name="entry.558526172" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama panggilan anak" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Jenis Kelamin
                    <select name="entry.324562681" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
                        <option>Laki-laki</option>
                        <option>Perempuan</option>
                    </select>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Tempat, Tanggal Lahir
                    <input type="text" name="entry.56417918" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Contoh: Cirebon, 12 Mei 2013" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Agama
                    <input type="text" name="entry.810990337" value="Islam" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Kewarganegaraan
                    <input type="text" name="entry.2002042407" value="WNI" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Asal Sekolah
                    <input type="text" name="entry.181234805" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Asal sekolah/SD sebelumnya" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    No. WA
                    <input type="tel" name="entry.408552828" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="08xxxxxxxxxx" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Ayah
                    <input type="text" name="entry.1001224564" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama lengkap Ayah" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pendidikan Ayah
                    <input type="text" name="entry.51377260" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pendidikan terakhir Ayah" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pekerjaan Ayah
                    <input type="text" name="entry.698003970" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pekerjaan Ayah" required>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Nama Ibu
                    <input type="text" name="entry.108118824" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Nama lengkap Ibu" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pendidikan Ibu
                    <input type="text" name="entry.137652037" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pendidikan terakhir Ibu" required>
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    Pekerjaan Ibu
                    <input type="text" name="entry.1959004689" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Pekerjaan Ibu" required>
                </label>
            </div>
            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                Alamat Tempat Tinggal
                <textarea name="entry.488607707" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Alamat lengkap tempat tinggal" required></textarea>
            </label>

        <?php else: ?>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                    Nama lengkap
                    <input type="text" disabled class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3 font-normal cursor-not-allowed text-slate-400 outline-none" placeholder="Nama calon siswa">
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                    Jenis kelamin
                    <select disabled class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3 font-normal cursor-not-allowed text-slate-400 outline-none">
                        <option>Laki-laki</option>
                        <option>Perempuan</option>
                    </select>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                    Umur / tanggal lahir
                    <input type="text" disabled class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3 font-normal cursor-not-allowed text-slate-400 outline-none" placeholder="Contoh: 7 tahun / 12-05-2019">
                </label>
                <label class="grid gap-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                    Nomor HP
                    <input type="tel" disabled class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3 font-normal cursor-not-allowed text-slate-400 outline-none" placeholder="08xxxxxxxxxx">
                </label>
            </div>
            <label class="grid gap-2 text-sm font-semibold text-slate-400 cursor-not-allowed">
                Alamat
                <textarea disabled rows="3" class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3 font-normal cursor-not-allowed text-slate-400 outline-none" placeholder="Alamat domisili"></textarea>
            </label>
        <?php endif; ?>

        <label class="grid gap-2 text-sm font-semibold text-slate-700">
            Pilihan sekolah
            <input type="text" value="<?= e($school['name']); ?>" readonly class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3 font-normal text-slate-600">
        </label>
        
        <?php if ($isActualFormAvailable): ?>
            <label class="flex items-start gap-3 text-xs leading-6 text-slate-600">
                <input type="checkbox" required class="mt-1 h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                <span>Saya menyetujui pengiriman data pendaftaran kepada admin sekolah untuk proses penerimaan siswa.</span>
            </label>
            <button type="submit" class="rounded-md bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-secondary-200">
                Kirim Pendaftaran
            </button>
            <p class="hidden rounded-md bg-secondary-50 px-4 py-3 text-sm font-semibold text-primary-800 ring-1 ring-secondary-200" data-form-success>Terima kasih. Data pendaftaran anda sudah terkirim.</p>
            <p class="text-xs leading-6 text-slate-500">Setelah dikirim, admin sekolah akan menindaklanjuti data pendaftaran ini.</p>
        <?php else: ?>
            <button type="button" disabled class="rounded-md bg-slate-400 px-5 py-3 text-sm font-bold text-white cursor-not-allowed hover:bg-slate-400 focus:outline-none">
                Pendaftaran Belum Dibuka
            </button>
            <p class="text-xs leading-6 text-slate-400">Pendaftaran online belum dibuka. Silakan hubungi WhatsApp sekolah untuk mendaftar langsung.</p>
        <?php endif; ?>
        
        <iframe name="<?= e($iframeName); ?>" class="hidden" title="Google Form submission target" data-google-form-frame></iframe>
    </form>
    <?php
}

function render_map(string $embedUrl, string $title, ?string $openUrl = null): void
{
    $mapsUrl = $openUrl ?: str_replace('&output=embed', '', $embedUrl);
    ?>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-100 shadow-sm">
        <iframe title="<?= e($title); ?>" src="<?= e($embedUrl); ?>" class="h-80 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm leading-6 text-slate-600">Lokasi tersedia melalui Google Maps.</p>
            <a href="<?= e($mapsUrl); ?>" class="inline-flex justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800" target="_blank" rel="noopener">Buka di Google Maps</a>
        </div>
    </div>
    <?php
}

function school_detail_card_item(string|array $item, string $type): array
{
    if (is_array($item)) {
        $defaults = detail_card_defaults($type, (string) ($item['title'] ?? ''));
        return [
            'title' => $item['title'] ?? '',
            'description' => $item['description'] ?? $defaults['description'],
            'image' => ($item['image'] ?? '') ?: $defaults['image'],
        ];
    }

    $details = detail_card_defaults($type, $item);

    return [
        'title' => $item,
        'description' => $details['description'],
        'image' => $details['image'],
    ];
}

function detail_card_defaults(string $type, string $title): array
{
    $fallbacks = [
        'facility' => [
            'description' => 'Fasilitas ini membantu siswa belajar, beraktivitas, dan mendapatkan pendampingan yang nyaman selama berada di sekolah.',
            'image' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=900&q=80',
        ],
        'activity' => [
            'description' => 'Kegiatan ini membantu siswa membangun kebiasaan baik, keberanian, kerja sama, dan rasa percaya diri dalam keseharian sekolah.',
            'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=900&q=80',
        ],
    ];

    $items = [
        'Ruang kelas anak' => ['description' => 'Ruang kelas dibuat ramah untuk anak usia dini agar mereka nyaman belajar, bermain, dan mengikuti arahan guru.', 'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=80'],
        'Ruang kelas TK' => ['description' => 'Ruang belajar TK mendukung kegiatan awal sekolah seperti bercerita, bernyanyi, bermain, dan latihan kemandirian.', 'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=80'],
        'Ruang kelas SD' => ['description' => 'Ruang kelas SD menjadi tempat siswa memperkuat dasar membaca, menulis, berhitung, dan belajar tertib bersama teman.', 'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=900&q=80'],
        'Ruang kelas' => ['description' => 'Ruang kelas disiapkan untuk pembelajaran harian yang tertib, mudah diikuti, dan dekat dengan pendampingan guru.', 'image' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=900&q=80'],
        'Area bermain' => ['description' => 'Area bermain memberi ruang bagi anak untuk bergerak, bersosialisasi, dan belajar melalui aktivitas yang menyenangkan.', 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=900&q=80'],
        'Media belajar edukatif' => ['description' => 'Media belajar membantu anak mengenal warna, angka, huruf, bentuk, dan konsep sederhana dengan cara visual.', 'image' => 'https://images.unsplash.com/photo-1604881991720-f91add269bed?auto=format&fit=crop&w=900&q=80'],
        'Media belajar anak' => ['description' => 'Media belajar anak mendukung kegiatan bermain terarah, eksplorasi, dan latihan fokus sesuai tahap perkembangan.', 'image' => 'https://images.unsplash.com/photo-1604881991720-f91add269bed?auto=format&fit=crop&w=900&q=80'],
        'Pendampingan guru kelas' => ['description' => 'Guru kelas mendampingi anak saat belajar, bermain, dan membangun kebiasaan baik secara bertahap.', 'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=900&q=80'],
        'Pendampingan guru' => ['description' => 'Pendampingan guru membantu anak merasa aman, memahami kegiatan, dan berani mencoba hal baru.', 'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=900&q=80'],
        'Perpustakaan sederhana' => ['description' => 'Perpustakaan sederhana menumbuhkan kebiasaan membaca dan memberi siswa akses bahan bacaan yang dekat dengan usia mereka.', 'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=900&q=80'],
        'Area olahraga' => ['description' => 'Area olahraga mendukung aktivitas fisik, kerja sama, sportivitas, dan kebugaran siswa.', 'image' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=80'],
        'Pendampingan wali kelas' => ['description' => 'Wali kelas membantu memantau perkembangan belajar, kedisiplinan, dan komunikasi dengan orang tua.', 'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=80'],
        'Bimbingan wali kelas' => ['description' => 'Wali kelas membimbing siswa dalam kebiasaan belajar, kedisiplinan, dan komunikasi harian dengan sekolah.', 'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=80'],
        'Ruang kegiatan siswa' => ['description' => 'Ruang kegiatan memberi tempat bagi siswa untuk berdiskusi, berorganisasi, dan mengembangkan minat bersama teman.', 'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=900&q=80'],
        'Ruang praktik' => ['description' => 'Ruang praktik membantu siswa melatih keterampilan secara langsung dan mengenal kebiasaan kerja yang lebih nyata.', 'image' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=900&q=80'],
        'Akses perangkat belajar' => ['description' => 'Perangkat belajar mendukung latihan, proyek, dan literasi digital agar siswa lebih siap menghadapi kebutuhan masa depan.', 'image' => 'https://images.unsplash.com/photo-1581090464777-f3220bbe1b8b?auto=format&fit=crop&w=900&q=80'],
        'Pendampingan guru produktif' => ['description' => 'Guru produktif membimbing siswa mengenal keterampilan, disiplin kerja, dan proses praktik sesuai bidangnya.', 'image' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=900&q=80'],
        'Area belajar' => ['description' => 'Area belajar membantu siswa menjalani kegiatan kelas, tugas, dan pendampingan akademik dengan lebih terarah.', 'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=900&q=80'],
        'Bernyanyi dan bercerita' => ['description' => 'Anak belajar bahasa, keberanian, dan imajinasi melalui lagu, cerita, serta interaksi hangat bersama guru.', 'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=80'],
        'Mewarnai dan membuat karya' => ['description' => 'Kegiatan karya melatih motorik halus, kreativitas, kesabaran, dan rasa bangga terhadap hasil buatan sendiri.', 'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=900&q=80'],
        'Senam dan permainan motorik' => ['description' => 'Gerak dan permainan membantu anak melatih koordinasi tubuh, keseimbangan, dan kebiasaan hidup aktif.', 'image' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=80'],
        'Pembiasaan doa dan sopan santun' => ['description' => 'Rutinitas harian menanamkan kebiasaan berdoa, antre, menyapa, dan menghargai teman serta guru.', 'image' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=900&q=80'],
        'Literasi pagi' => ['description' => 'Siswa diajak membaca dan memahami bacaan sederhana untuk membangun minat baca sejak awal hari.', 'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=900&q=80'],
        'Latihan berhitung' => ['description' => 'Latihan numerasi membantu siswa memahami angka, pola, dan pemecahan masalah secara bertahap.', 'image' => 'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?auto=format&fit=crop&w=900&q=80'],
        'Kegiatan seni dan prakarya' => ['description' => 'Seni dan prakarya memberi ruang bagi siswa untuk berekspresi, berkarya, dan bekerja dengan tekun.', 'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=900&q=80'],
        'Olahraga bersama' => ['description' => 'Olahraga bersama melatih kebugaran, kerja sama, sportivitas, dan semangat mengikuti kegiatan sekolah.', 'image' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=80'],
        'Diskusi kelompok' => ['description' => 'Siswa belajar menyampaikan pendapat, mendengar teman, dan menyelesaikan tugas bersama secara bertanggung jawab.', 'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=80'],
        'Presentasi siswa' => ['description' => 'Presentasi melatih keberanian berbicara, menyusun gagasan, dan tampil percaya diri di depan kelas.', 'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=900&q=80'],
        'Kegiatan organisasi' => ['description' => 'Kegiatan organisasi membantu siswa belajar kepemimpinan, tanggung jawab, dan kerja sama lintas teman.', 'image' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=900&q=80'],
        'Olahraga dan seni' => ['description' => 'Kegiatan ini menyeimbangkan kemampuan fisik, kreativitas, ekspresi diri, dan kebersamaan siswa.', 'image' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=900&q=80'],
        'Praktik keterampilan' => ['description' => 'Siswa melatih keterampilan secara langsung agar lebih paham proses, alat, dan standar kerja sederhana.', 'image' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=900&q=80'],
        'Proyek siswa' => ['description' => 'Proyek memberi pengalaman merencanakan, mencoba, memperbaiki, dan menyelesaikan pekerjaan secara mandiri.', 'image' => 'https://images.unsplash.com/photo-1581090464777-f3220bbe1b8b?auto=format&fit=crop&w=900&q=80'],
        'Simulasi kerja' => ['description' => 'Simulasi kerja mengenalkan siswa pada disiplin, komunikasi, tanggung jawab, dan kebiasaan profesional.', 'image' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=900&q=80'],
        'Kegiatan kewirausahaan' => ['description' => 'Siswa belajar melihat peluang, membuat rencana sederhana, bekerja sama, dan memahami nilai usaha.', 'image' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80'],
        'Bercerita' => ['description' => 'Bercerita membantu anak memperkaya kosakata, memahami alur, dan berani merespons pertanyaan guru.', 'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=80'],
        'Mewarnai' => ['description' => 'Mewarnai melatih fokus, koordinasi tangan, pilihan warna, dan ketekunan menyelesaikan kegiatan.', 'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=900&q=80'],
        'Bernyanyi' => ['description' => 'Bernyanyi membuat anak lebih aktif, ceria, dan mudah mengingat kata, irama, serta kebiasaan kelas.', 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=900&q=80'],
        'Permainan kelompok' => ['description' => 'Permainan kelompok melatih anak berbagi giliran, mengikuti aturan, dan bekerja sama dengan teman.', 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=900&q=80'],
        'Literasi kelas' => ['description' => 'Literasi kelas menguatkan kebiasaan membaca, memahami instruksi, dan menyampaikan kembali isi bacaan.', 'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=900&q=80'],
        'Latihan numerasi' => ['description' => 'Numerasi membantu siswa memakai logika angka dalam latihan harian dan persoalan sederhana.', 'image' => 'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?auto=format&fit=crop&w=900&q=80'],
        'Prakarya' => ['description' => 'Prakarya melatih kreativitas, ketelitian, dan kemampuan mengikuti langkah kerja sampai selesai.', 'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=900&q=80'],
    ];

    return $items[$title] ?? $fallbacks[$type];
}

function render_school_detail_sections(array $school): void
{
    ?>
    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Program Unggulan</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Kegiatan belajar yang disesuaikan dengan jenjang.</h2>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <?php foreach ($school['programs'] ?? [] as $program): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-950"><?= e($program['title']); ?></h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600"><?= e($program['description']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Fasilitas</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Ruang belajar dan kegiatan siswa.</h2>
                <p class="mt-5 text-base leading-8 text-slate-600">Fasilitas disiapkan untuk mendukung kegiatan belajar harian, pendampingan guru, dan aktivitas siswa di sekolah.</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <?php foreach ($school['facilities'] ?? [] as $facility): ?>
                    <?php $facilityItem = school_detail_card_item($facility, 'facility'); ?>
                    <article class="group overflow-hidden rounded-lg border border-slate-200 bg-slate-50 shadow-sm">
                        <div class="aspect-[16/10] overflow-hidden bg-slate-200">
                            <img src="<?= e($facilityItem['image']); ?>" alt="<?= e($facilityItem['title']); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-950"><?= e($facilityItem['title']); ?></h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600"><?= e($facilityItem['description']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Kegiatan Siswa</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Anak belajar lewat kebiasaan sehari-hari.</h2>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <?php foreach ($school['activities'] ?? [] as $activity): ?>
                    <?php $activityItem = school_detail_card_item($activity, 'activity'); ?>
                    <article class="group overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div class="aspect-[16/11] overflow-hidden bg-slate-200">
                            <img src="<?= e($activityItem['image']); ?>" alt="<?= e($activityItem['title']); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-950"><?= e($activityItem['title']); ?></h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600"><?= e($activityItem['description']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
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
                    <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Profil Sekolah</p>
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
                    <p class="text-sm font-semibold text-secondary-200">Pendaftaran</p>
                    <h2 class="mt-3 text-2xl font-bold">Hubungi admin <?= e($school['short_name']); ?></h2>
                    <p class="mt-4 text-sm leading-7 text-slate-200">Admin akan membantu menjawab pertanyaan seputar biaya, jadwal, berkas, dan langkah pendaftaran.</p>
                    <div class="mt-6 grid gap-3">
                        <a href="<?= e(whatsapp_url($school['phone'], 'Halo ' . $school['name'] . ', saya ingin bertanya tentang pendaftaran.')); ?>" class="rounded-md bg-white px-4 py-3 text-center text-sm font-bold text-slate-950 transition hover:bg-slate-100" target="_blank" rel="noopener">WhatsApp <?= e($school['short_name']); ?></a>
                        <a href="#pendaftaran" class="rounded-md border border-white/20 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-white/10">Isi Form Pendaftaran</a>
                    </div>
                </aside>
            </div>
        </section>

        <?php render_school_detail_sections($school); ?>

        <section id="pendaftaran" class="bg-slate-50 py-16 sm:py-20">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Pendaftaran</p>
                    <?php if ($school['name'] === 'SMPIT TAHFIDZUL QURAN'): ?>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Formulir Pendaftaran</h2>
                        <div class="mt-5 text-sm leading-7 text-slate-600 space-y-4">
                            <p class="font-semibold text-slate-900">Assalamu’alaikum warahmatullahi wabarakatuh.</p>
                            <p>Terima kasih atas minat Ayah/Bunda untuk mendaftarkan Ananda di SMPIT Tahfidzul Qur’an Cendekia Cirebon.</p>
                            <p>Melalui formulir ini, Ayah/Bunda dapat mengisi data awal pendaftaran calon peserta didik baru. Data yang diisi akan digunakan oleh pihak sekolah sebagai data administrasi awal dalam proses Penerimaan Peserta Didik Baru (PPDB).</p>
                            <p>Mohon agar Ayah/Bunda mengisi setiap kolom dengan data yang benar dan lengkap agar proses verifikasi dapat berjalan dengan baik.</p>
                            <p>Setelah mengisi formulir ini, mohon berkenan untuk menghubungi Admin Sekolah melalui WhatsApp guna proses verifikasi data dan informasi tahapan pendaftaran selanjutnya.</p>
                            <div class="my-4 rounded-md border border-secondary-200 bg-secondary-50 p-4">
                                <span class="block font-bold text-primary-950">📞 Admin (WhatsApp):</span>
                                <a href="https://wa.me/6281573888807" target="_blank" rel="noopener" class="text-primary-700 font-bold hover:underline">0815-7388-8807</a>
                            </div>
                            <p>Apabila terdapat pertanyaan atau membutuhkan informasi lebih lanjut terkait proses pendaftaran, Ayah/Bunda juga dapat menghubungi nomor tersebut.</p>
                            <p class="font-semibold text-slate-900">Semoga Allah memudahkan setiap langkah pendidikan Ananda.</p>
                            <p class="font-semibold text-slate-900">Wassalamu’alaikum warahmatullahi wabarakatuh.</p>
                        </div>
                    <?php else: ?>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Isi data calon siswa.</h2>
                        <p class="mt-5 text-base leading-8 text-slate-600">Formulir di website ini akan mengirimkan data pendaftaran secara langsung ke cabang sekolah yang dipilih.</p>
                    <?php endif; ?>
                </div>
                <?php render_registration_preview($school); ?>
            </div>
        </section>

        <section class="bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 max-w-2xl">
                    <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Lokasi</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Google Maps <?= e($school['name']); ?></h2>
                </div>
                <?php render_map($school['maps_embed'], 'Lokasi ' . $school['name'], $school['maps_url'] ?? null); ?>
            </div>
        </section>
    </main>
    <?php
}
