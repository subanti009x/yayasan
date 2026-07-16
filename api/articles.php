<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/articles.php';

$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$articles = load_articles(true, $search);
$title = 'Artikel - Yayasan Cendekia';
$description = 'Artikel, berita, dan informasi terbaru dari Yayasan Cendekia.';

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="bg-slate-950 py-20 text-white sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-wide text-secondary-200">Artikel Yayasan</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">Cerita, kegiatan, dan informasi sekolah.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">Temukan kabar terbaru, tips pendidikan, dan aktivitas siswa yang ditulis langsung oleh admin sekolah.</p>
        </div>
    </section>

    <section class="bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="get" class="mb-8 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:flex-row">
                <label class="sr-only" for="article-search">Cari artikel</label>
                <input id="article-search" name="q" type="search" maxlength="100" value="<?= e($search); ?>" placeholder="Cari judul, kategori, penulis, atau ringkasan" class="min-w-0 flex-1 rounded-md border border-slate-200 px-3 py-3 text-sm outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                <button type="submit" class="rounded-md bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700">Cari Artikel</button>
                <?php if ($search !== ''): ?><a href="articles.php" class="rounded-md border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-700">Reset</a><?php endif; ?>
            </form>
            <?php if ($articles === []): ?>
                <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <p class="text-sm font-bold uppercase tracking-wide text-primary-700">Belum Ada Artikel</p>
                    <h2 class="mt-3 text-3xl font-bold text-slate-950"><?= $search !== '' ? 'Artikel tidak ditemukan.' : 'Artikel akan segera hadir.'; ?></h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600"><?= $search !== '' ? 'Coba gunakan kata kunci lain.' : 'Admin sekolah dapat menambahkan artikel melalui dashboard admin.'; ?></p>
                </div>
            <?php else: ?>
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($articles as $index => $article): ?>
                        <?php render_article_card($article, $index === 0); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
