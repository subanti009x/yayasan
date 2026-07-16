<?php
/** One-time import for the former JSON CMS data. */
require_once __DIR__ . '/../api/includes/database.php';
require_once __DIR__ . '/../api/includes/articles.php';
require_once __DIR__ . '/../api/includes/cms.php';

function import_json_array(string $file): array
{
    $json = file_get_contents($file);
    if ($json === false) throw new RuntimeException("Seed tidak dapat dibaca: {$file}");
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) throw new RuntimeException("Seed harus berupa array/object: {$file}");
    return $data;
}

try {
    $pdo = cms_require_database();
    $seedPath = __DIR__ . '/../api/data';
    $site = import_json_array($seedPath . '/site.json');
    $faqs = import_json_array($seedPath . '/faq.json');
    $articles = import_json_array($seedPath . '/articles.json');
    $pdo->beginTransaction();

    if (!cms_save_document('site', $site)) throw new RuntimeException(cms_last_error());
    $pdo->exec('DELETE FROM cms_faqs');
    $faqStatement = $pdo->prepare('INSERT INTO cms_faqs (question, answer, sort_order) VALUES (:question, :answer, :sort_order)');
    foreach ($faqs as $index => $faq) {
        $faqStatement->execute(['question' => trim((string) ($faq['question'] ?? '')), 'answer' => trim((string) ($faq['answer'] ?? '')), 'sort_order' => $index + 1]);
    }

    foreach ($articles as $article) {
        if (!is_array($article) || empty($article['id']) || empty($article['title']) || empty($article['slug'])) throw new RuntimeException('Data artikel seed tidak lengkap.');
        if (!save_article([
            'id' => (string) $article['id'], 'title' => trim((string) $article['title']), 'slug' => trim((string) $article['slug']),
            'category' => trim((string) ($article['category'] ?? 'Artikel')), 'author' => trim((string) ($article['author'] ?? 'Admin Yayasan')),
            'excerpt' => trim((string) ($article['excerpt'] ?? '')), 'content' => trim((string) ($article['content'] ?? '')),
            'image' => trim((string) ($article['image'] ?? '')), 'views' => max(0, (int) ($article['views'] ?? 0)),
            'status' => ($article['status'] ?? '') === 'published' ? 'published' : 'draft',
            'created_at' => (string) ($article['created_at'] ?? date('Y-m-d H:i:s')), 'updated_at' => (string) ($article['updated_at'] ?? date('Y-m-d H:i:s')),
        ])) throw new RuntimeException(cms_last_error());
    }

    $pdo->commit();
    echo 'Migrasi selesai: ' . count($articles) . ' artikel dan ' . count($faqs) . " FAQ diimpor ke MySQL.\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Migrasi gagal: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
