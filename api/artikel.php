<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/articles.php';

$slug = (string) ($_GET['slug'] ?? '');
$article = find_article_by_slug($slug);

if ($article === null) {
    http_response_code(404);
    $title = 'Artikel Tidak Ditemukan - Yayasan Cendekia';
    $description = 'Artikel yang anda cari tidak tersedia.';
    require __DIR__ . '/includes/header.php';
    ?>
    <main>
        <section class="bg-white py-20 sm:py-24">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <p class="text-sm font-bold uppercase tracking-wide text-orange-700">Artikel</p>
                <h1 class="mt-4 text-4xl font-bold tracking-tight text-slate-950">Artikel tidak ditemukan.</h1>
                <p class="mt-4 text-base leading-8 text-slate-600">Konten yang anda buka mungkin sudah dipindahkan atau belum dipublikasikan.</p>
                <a href="<?= url('articles.php'); ?>" class="mt-8 inline-flex rounded-md bg-orange-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-700">Lihat Artikel Lain</a>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php
    exit;
}

if (!defined('BUILD_STATIC')) {
    $viewedArticle = increment_article_views((string) ($article['id'] ?? ''));

    if ($viewedArticle !== null) {
        $article = $viewedArticle;
    }
}

$title = ($article['title'] ?? 'Artikel') . ' - Yayasan Cendekia';
$description = article_plain_excerpt($article, 160);

require __DIR__ . '/includes/header.php';
?>
<main>
    <article>
        <section class="relative isolate overflow-hidden bg-slate-950 text-white">
            <?php if (!empty($article['image'])): ?>
                <img src="<?= e($article['image']); ?>" alt="<?= e($article['title']); ?>" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-35">
            <?php endif; ?>
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950 via-slate-950/86 to-orange-950/45"></div>
            <div class="mx-auto max-w-4xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                <div class="flex flex-wrap items-center gap-2 text-sm font-bold uppercase tracking-wide text-amber-100">
                    <span><?= e($article['category'] ?? 'Artikel'); ?></span>
                    <span class="text-white/35">/</span>
                    <span><?= e(article_date($article)); ?></span>
                    <span class="text-white/35">/</span>
                    <span><?= e(article_reading_time($article)); ?></span>
                    <span class="text-white/35">/</span>
                    <?php render_article_views($article); ?>
                </div>
                <h1 class="mt-5 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl"><?= e($article['title']); ?></h1>
                <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-100"><?= e(article_plain_excerpt($article)); ?></p>
            </div>
        </section>

        <section class="bg-white py-14 sm:py-16">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.72fr_0.28fr] lg:px-8">
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <?php render_article_body($article['content'] ?? ''); ?>
                </div>
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-6">
                        <p class="text-sm font-bold uppercase tracking-wide text-orange-700">Info Artikel</p>
                        <dl class="mt-5 grid gap-4 text-sm">
                            <div>
                                <dt class="font-bold text-slate-950">Penulis</dt>
                                <dd class="mt-1 text-slate-600"><?= e($article['author'] ?? 'Admin Yayasan'); ?></dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-950">Kategori</dt>
                                <dd class="mt-1 text-slate-600"><?= e($article['category'] ?? 'Artikel'); ?></dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-950">Diterbitkan</dt>
                                <dd class="mt-1 text-slate-600"><?= e(article_date($article)); ?></dd>
                            </div>
                            <div>
                                <dt class="font-bold text-slate-950">Pengunjung</dt>
                                <dd class="mt-1 text-slate-600"><?php render_article_views($article); ?></dd>
                            </div>
                        </dl>
                        <a href="<?= url('articles.php'); ?>" class="mt-6 inline-flex w-full justify-center rounded-md bg-orange-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-orange-700">Semua Artikel</a>
                    </div>
                </aside>
            </div>
        </section>
    </article>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
