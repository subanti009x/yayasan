<?php
require_once __DIR__ . '/database.php';

function load_articles(bool $publishedOnly = false, string $search = '', ?string $status = null): array
{
    try {
        $sql = 'SELECT id, title, slug, category, author, excerpt, content, image, views, status, created_at, updated_at
                FROM cms_articles';
        $conditions = [];
        $params = [];

        if ($publishedOnly) {
            $conditions[] = "status = 'published'";
        } elseif (in_array($status, ['draft', 'published'], true)) {
            $conditions[] = 'status = :status';
            $params['status'] = $status;
        }

        $search = trim($search);
        if ($search !== '') {
            $conditions[] = '(title LIKE :search_title OR category LIKE :search_category OR author LIKE :search_author OR excerpt LIKE :search_excerpt)';
            $searchTerm = '%' . mb_substr($search, 0, 100) . '%';
            $params['search_title'] = $searchTerm;
            $params['search_category'] = $searchTerm;
            $params['search_author'] = $searchTerm;
            $params['search_excerpt'] = $searchTerm;
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';
        $statement = cms_require_database()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    } catch (Throwable $exception) {
        cms_last_error('Artikel tidak dapat dibaca dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return [];
    }
}

function save_article(array $article): bool
{
    try {
        $statement = cms_require_database()->prepare(
            'INSERT INTO cms_articles (id, title, slug, category, author, excerpt, content, image, views, status, created_at, updated_at)
             VALUES (:id, :title, :slug, :category, :author, :excerpt, :content, :image, :views, :status, :created_at, :updated_at)
             ON DUPLICATE KEY UPDATE title = VALUES(title), slug = VALUES(slug), category = VALUES(category),
                 author = VALUES(author), excerpt = VALUES(excerpt), content = VALUES(content), image = VALUES(image),
                 views = VALUES(views), status = VALUES(status), updated_at = VALUES(updated_at)'
        );
        $statement->execute([
            'id' => $article['id'],
            'title' => $article['title'],
            'slug' => $article['slug'],
            'category' => $article['category'],
            'author' => $article['author'],
            'excerpt' => $article['excerpt'],
            'content' => $article['content'],
            'image' => $article['image'],
            'views' => $article['views'],
            'status' => $article['status'],
            'created_at' => $article['created_at'],
            'updated_at' => $article['updated_at'],
        ]);
        cms_last_error('');
        return true;
    } catch (Throwable $exception) {
        cms_last_error('Artikel tidak dapat disimpan ke MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return false;
    }
}

function delete_article(string $id): bool
{
    try {
        $statement = cms_require_database()->prepare('DELETE FROM cms_articles WHERE id = :id');
        $statement->execute(['id' => $id]);
        cms_last_error('');
        return $statement->rowCount() === 1;
    } catch (Throwable $exception) {
        cms_last_error('Artikel tidak dapat dihapus dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return false;
    }
}

function article_slug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'artikel-' . date('YmdHis');
}

function unique_article_slug(string $title, ?string $currentId = null): string
{
    $base = article_slug($title);
    $slug = $base;
    $counter = 2;

    while (article_slug_exists($slug, $currentId)) {
        $slug = $base . '-' . $counter;
        $counter++;
    }

    return $slug;
}

function article_slug_exists(string $slug, ?string $currentId = null): bool
{
    try {
        $sql = 'SELECT 1 FROM cms_articles WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($currentId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $currentId;
        }

        $sql .= ' LIMIT 1';
        $statement = cms_require_database()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchColumn() !== false;
    } catch (Throwable $exception) {
        cms_last_error('Slug artikel tidak dapat diperiksa.');
        error_log('[cms-db] ' . $exception->getMessage());
        return true;
    }
}

function find_article_by_slug(string $slug): ?array
{
    try {
        $statement = cms_require_database()->prepare(
            "SELECT id, title, slug, category, author, excerpt, content, image, views, status, created_at, updated_at
             FROM cms_articles WHERE slug = :slug AND status = 'published' LIMIT 1"
        );
        $statement->execute(['slug' => $slug]);
        $article = $statement->fetch();
        return is_array($article) ? $article : null;
    } catch (Throwable $exception) {
        cms_last_error('Artikel tidak dapat dibaca dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return null;
    }
}

function find_article_by_id(string $id): ?array
{
    try {
        $statement = cms_require_database()->prepare(
            'SELECT id, title, slug, category, author, excerpt, content, image, views, status, created_at, updated_at
             FROM cms_articles WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $article = $statement->fetch();
        return is_array($article) ? $article : null;
    } catch (Throwable $exception) {
        cms_last_error('Artikel tidak dapat dibaca dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return null;
    }
}

function increment_article_views(string $id): ?array
{
    try {
        $pdo = cms_require_database();
        $statement = $pdo->prepare('UPDATE cms_articles SET views = views + 1 WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() === 1 ? find_article_by_id($id) : null;
    } catch (Throwable $exception) {
        cms_last_error('Jumlah pembaca artikel tidak dapat diperbarui.');
        error_log('[cms-db] ' . $exception->getMessage());
        return null;
    }
}

function article_date(array $article): string
{
    $timestamp = strtotime($article['created_at'] ?? '');
    return $timestamp === false ? '' : date('d M Y', $timestamp);
}

function article_url(array $article): string
{
    if (getenv('VERCEL') === '1') {
        return '/artikel-' . rawurlencode($article['slug'] ?? '');
    }
    return 'artikel.php?slug=' . rawurlencode($article['slug'] ?? '');
}

function article_static_url(array $article): string
{
    return 'artikel-' . ($article['slug'] ?? '') . '.html';
}

function article_plain_excerpt(array $article, int $length = 150): string
{
    $excerpt = trim($article['excerpt'] ?? '');
    if ($excerpt !== '') {
        return $excerpt;
    }

    $content = trim(preg_replace('/\s+/', ' ', strip_tags($article['content'] ?? '')) ?? '');
    return strlen($content) <= $length ? $content : substr($content, 0, $length - 3) . '...';
}

function article_reading_time(array $article): string
{
    $words = str_word_count(strip_tags($article['content'] ?? ''));
    return max(1, (int) ceil($words / 180)) . ' menit baca';
}

function article_views(array $article): int
{
    return max(0, (int) ($article['views'] ?? 0));
}

function article_views_label(array $article): string
{
    return 'Dilihat ' . article_views($article) . ' kali';
}

function render_article_views(array $article, string $class = ''): void
{
    $id = $article['id'] ?? '';
    $views = article_views($article);
    ?>
    <span class="inline-flex items-center gap-1.5 <?= e($class); ?>" data-article-views-id="<?= e($id); ?>" data-article-views-count="<?= $views; ?>">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" /></svg>
        <span class="views-count-text"><?= e(article_views_label($article)); ?></span>
    </span>
    <?php
}

function render_article_card(array $article, bool $featured = false): void
{
    $image = trim($article['image'] ?? '');
    $url = article_url($article);
    $imageClass = $featured ? 'aspect-[16/10]' : 'aspect-[16/11]';
    ?>
    <article class="group overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-soft">
        <a href="<?= e($url); ?>" class="block <?= e($imageClass); ?> overflow-hidden bg-secondary-50">
            <?php if ($image !== ''): ?><img src="<?= e($image); ?>" alt="<?= e($article['title'] ?? 'Artikel Yayasan Cendekia'); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy"><?php else: ?><div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-100 via-secondary-50 to-yellow-100 text-sm font-bold text-primary-700">Yayasan Cendekia</div><?php endif; ?>
        </a>
        <div class="p-6"><div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-wide text-primary-700"><span><?= e($article['category'] ?? 'Artikel'); ?></span><span class="text-slate-300">/</span><span class="text-slate-500"><?= e(article_date($article)); ?></span></div><h3 class="mt-3 text-xl font-bold leading-tight text-slate-950"><a href="<?= e($url); ?>" class="transition hover:text-primary-700"><?= e($article['title'] ?? 'Artikel Yayasan Cendekia'); ?></a></h3><p class="mt-3 text-sm leading-7 text-slate-600"><?= e(article_plain_excerpt($article)); ?></p><div class="mt-5 flex flex-wrap items-center justify-between gap-3"><?php render_article_views($article, 'rounded-full bg-secondary-50 px-3 py-1.5 text-xs font-bold text-primary-800 ring-1 ring-secondary-200'); ?><a href="<?= e($url); ?>" class="inline-flex rounded-md border border-secondary-200 px-4 py-2 text-sm font-bold text-primary-700 transition hover:border-primary-300 hover:bg-secondary-50">Baca Artikel</a></div></div>
    </article>
    <?php
}

function render_article_body(string $content): void
{
    $paragraphs = preg_split("/\R{2,}/", trim($content));
    if ($paragraphs === false) return;
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if ($paragraph !== '') echo '<p class="mt-6 text-base leading-8 text-slate-700">' . nl2br(e($paragraph)) . '</p>';
    }
}
