<?php

function articles_path(): string
{
    return __DIR__ . '/../data/articles.json';
}

function commit_to_github(string $path, string $content, string $commitMessage, string $token): bool
{
    $repo = 'subanti009x/yayasan';
    $branch = 'main';

    // 1. Get current SHA if the file exists
    $ch = curl_init("https://api.github.com/repos/{$repo}/contents/{$path}?ref={$branch}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: Vercel-PHP",
        "Authorization: Bearer {$token}"
    ]);
    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $sha = null;
    if ($status === 200) {
        $data = json_decode($res, true);
        $sha = $data['sha'] ?? null;
    }

    // 2. Commit new content
    $payload = [
        'message' => $commitMessage,
        'content' => base64_encode($content),
        'branch' => $branch
    ];
    if ($sha) {
        $payload['sha'] = $sha;
    }

    $ch = curl_init("https://api.github.com/repos/{$repo}/contents/{$path}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: Vercel-PHP",
        "Authorization: Bearer {$token}",
        "Content-Type: application/json"
    ]);
    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $status === 200 || $status === 201;
}

function load_articles(bool $publishedOnly = false): array
{
    $path = articles_path();

    if (!is_file($path)) {
        return [];
    }

    $json = file_get_contents($path);
    $data = json_decode($json ?: '[]', true);

    if (!is_array($data)) {
        return [];
    }

    if (isset($data['articles']) && is_array($data['articles'])) {
        $articles = $data['articles'];
    } else {
        $articles = $data;
    }

    $articles = array_values(array_filter($articles, 'is_array'));

    if ($publishedOnly) {
        $articles = array_values(array_filter($articles, fn (array $article): bool => ($article['status'] ?? 'draft') === 'published'));
    }

    usort($articles, function (array $left, array $right): int {
        return strcmp($right['created_at'] ?? '', $left['created_at'] ?? '');
    });

    return $articles;
}

function save_articles(array $articles): bool
{
    $path = articles_path();
    $json = json_encode(array_values($articles), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        return false;
    }

    if (getenv('VERCEL') === '1') {
        $githubToken = getenv('GITHUB_TOKEN');
        if ($githubToken) {
            return commit_to_github('api/data/articles.json', $json . PHP_EOL, 'update articles database', $githubToken);
        }
        return false;
    }

    $directory = dirname($path);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    return file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false;
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
    $usedSlugs = [];

    foreach (load_articles(false) as $article) {
        if (($article['id'] ?? '') === $currentId) {
            continue;
        }

        if (!empty($article['slug'])) {
            $usedSlugs[$article['slug']] = true;
        }
    }

    $slug = $base;
    $counter = 2;

    while (isset($usedSlugs[$slug])) {
        $slug = $base . '-' . $counter;
        $counter++;
    }

    return $slug;
}

function find_article_by_slug(string $slug): ?array
{
    foreach (load_articles(true) as $article) {
        if (($article['slug'] ?? '') === $slug) {
            return $article;
        }
    }

    return null;
}

function find_article_by_id(string $id): ?array
{
    foreach (load_articles(false) as $article) {
        if (($article['id'] ?? '') === $id) {
            return $article;
        }
    }

    return null;
}

function increment_article_views(string $id): ?array
{
    if (getenv('VERCEL') === '1') {
        return null;
    }

    $path = articles_path();

    if (!is_file($path)) {
        return null;
    }

    $handle = fopen($path, 'c+');

    if ($handle === false) {
        return null;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return null;
        }

        rewind($handle);
        $json = stream_get_contents($handle);
        $articles = json_decode($json ?: '[]', true);

        if (!is_array($articles)) {
            return null;
        }

        $updatedArticle = null;

        foreach ($articles as $index => $article) {
            if (!is_array($article) || ($article['id'] ?? '') !== $id) {
                continue;
            }

            $article['views'] = max(0, (int) ($article['views'] ?? 0)) + 1;
            $articles[$index] = $article;
            $updatedArticle = $article;
            break;
        }

        if ($updatedArticle === null) {
            return null;
        }

        $encoded = json_encode(array_values($articles), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            return null;
        }

        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, $encoded . PHP_EOL);
        fflush($handle);

        return $updatedArticle;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function article_date(array $article): string
{
    $timestamp = strtotime($article['created_at'] ?? '');

    if ($timestamp === false) {
        return '';
    }

    return date('d M Y', $timestamp);
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

    if (strlen($content) <= $length) {
        return $content;
    }

    return substr($content, 0, $length - 3) . '...';
}

function article_reading_time(array $article): string
{
    $words = str_word_count(strip_tags($article['content'] ?? ''));
    $minutes = max(1, (int) ceil($words / 180));

    return $minutes . ' menit baca';
}

function article_views(array $article): int
{
    return max(0, (int) ($article['views'] ?? 0));
}

function article_views_label(array $article): string
{
    $views = article_views($article);

    return 'Dilihat ' . $views . ' kali';
}

function render_article_views(array $article, string $class = ''): void
{
    $id = $article['id'] ?? '';
    $views = article_views($article);
    ?>
    <span class="inline-flex items-center gap-1.5 <?= e($class); ?>" data-article-views-id="<?= e($id); ?>" data-article-views-count="<?= $views; ?>">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="3" />
        </svg>
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
            <?php if ($image !== ''): ?>
                <img src="<?= e($image); ?>" alt="<?= e($article['title'] ?? 'Artikel Yayasan Cendekia'); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
            <?php else: ?>
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-100 via-secondary-50 to-yellow-100 text-sm font-bold text-primary-700">Yayasan Cendekia</div>
            <?php endif; ?>
        </a>
        <div class="p-6">
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-wide text-primary-700">
                <span><?= e($article['category'] ?? 'Artikel'); ?></span>
                <span class="text-slate-300">/</span>
                <span class="text-slate-500"><?= e(article_date($article)); ?></span>
            </div>
            <h3 class="mt-3 text-xl font-bold leading-tight text-slate-950">
                <a href="<?= e($url); ?>" class="transition hover:text-primary-700"><?= e($article['title'] ?? 'Artikel Yayasan Cendekia'); ?></a>
            </h3>
            <p class="mt-3 text-sm leading-7 text-slate-600"><?= e(article_plain_excerpt($article)); ?></p>
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                <?php render_article_views($article, 'rounded-full bg-secondary-50 px-3 py-1.5 text-xs font-bold text-primary-800 ring-1 ring-secondary-200'); ?>
                <a href="<?= e($url); ?>" class="inline-flex rounded-md border border-secondary-200 px-4 py-2 text-sm font-bold text-primary-700 transition hover:border-primary-300 hover:bg-secondary-50">Baca Artikel</a>
            </div>
        </div>
    </article>
    <?php
}

function render_article_body(string $content): void
{
    $paragraphs = preg_split("/\R{2,}/", trim($content));

    if ($paragraphs === false) {
        return;
    }

    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);

        if ($paragraph === '') {
            continue;
        }

        echo '<p class="mt-6 text-base leading-8 text-slate-700">' . nl2br(e($paragraph)) . '</p>';
    }
}
