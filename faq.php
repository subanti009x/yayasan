<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/components.php';

$title = 'FAQ Pendaftaran - Yayasan Cendekia';
$description = 'Pertanyaan yang sering diajukan seputar pendaftaran Yayasan Cendekia, Cendekia Cirebon, TKIT Cendekia 2 Losari, dan SD IT Cendekia 2 Losari.';

$faqs = [
    [
        'question' => 'Bagaimana cara mulai mendaftar?',
        'answer' => 'Orang tua dapat memilih halaman jenjang yang dituju, mengisi data awal calon siswa, lalu menunggu admin sekolah menghubungi untuk langkah berikutnya.',
    ],
    [
        'question' => 'Apakah bisa bertanya dulu sebelum mengisi form?',
        'answer' => 'Bisa. Setiap halaman sekolah memiliki tombol WhatsApp admin. Orang tua dapat bertanya tentang biaya, jadwal, syarat berkas, atau informasi sekolah terlebih dahulu.',
    ],
    [
        'question' => 'Apa perbedaan Cendekia Cirebon dan Cabang Losari?',
        'answer' => 'Cendekia Cirebon menampilkan jenjang TK, SD, SMP, dan SMK. Cabang Losari saat ini disiapkan dengan halaman khusus TKIT Cendekia 2 Losari dan SD IT Cendekia 2 Losari.',
    ],
    [
        'question' => 'Apakah TKIT Cendekia 2 Losari dan SD IT Cendekia 2 Losari memiliki form berbeda?',
        'answer' => 'Ya. TKIT Cendekia 2 Losari dan SD IT Cendekia 2 Losari memiliki halaman masing-masing sehingga pilihan sekolah pada form pendaftaran sesuai dengan jenjang yang dipilih.',
    ],
    [
        'question' => 'Apakah bisa survei atau datang langsung ke sekolah?',
        'answer' => 'Silakan hubungi admin terlebih dahulu melalui WhatsApp agar jadwal kunjungan dapat diarahkan dengan lebih nyaman.',
    ],
    [
        'question' => 'Data apa saja yang perlu disiapkan?',
        'answer' => 'Untuk tahap awal, orang tua cukup menyiapkan nama calon siswa, jenis kelamin, tanggal lahir atau umur, alamat, dan nomor HP yang aktif.',
    ],
];

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="bg-slate-950 py-20 text-white sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-wide text-teal-200">FAQ Pendaftaran</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">Pertanyaan yang sering ditanyakan orang tua.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">Beberapa jawaban singkat sebelum menghubungi admin atau mengisi form pendaftaran.</p>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4">
                <?php foreach ($faqs as $faq): ?>
                    <details class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                        <summary class="cursor-pointer text-base font-bold text-slate-950"><?= e($faq['question']); ?></summary>
                        <p class="mt-3 text-sm leading-7 text-slate-600"><?= e($faq['answer']); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
            <div class="mt-10 rounded-lg bg-slate-950 p-6 text-white">
                <h2 class="text-2xl font-bold">Masih ingin bertanya?</h2>
                <p class="mt-3 text-sm leading-7 text-slate-200">Admin yayasan siap membantu menjelaskan informasi pendaftaran dan mengarahkan ke jenjang yang sesuai.</p>
                <a href="<?= e(whatsapp_url(site_data()['foundation']['phone'], 'Halo Yayasan Cendekia, saya ingin bertanya tentang pendaftaran.')); ?>" class="mt-5 inline-flex rounded-md bg-white px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-slate-100" target="_blank" rel="noopener">Tanya Admin</a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
