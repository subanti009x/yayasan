<?php
/** Read/insert/update/delete test. It always rolls back its transaction. */
require_once __DIR__ . '/../api/includes/database.php';
require_once __DIR__ . '/../api/includes/articles.php';
require_once __DIR__ . '/../api/includes/cms.php';

try {
    $pdo = cms_require_database();
    $pdo->beginTransaction();
    $suffix = bin2hex(random_bytes(5));
    $articleId = 'test_' . $suffix;
    $slug = 'uji-mysql-' . $suffix;
    if (!save_article(['id' => $articleId, 'title' => 'Uji MySQL', 'slug' => $slug, 'category' => 'Pengujian', 'author' => 'Automated test', 'excerpt' => 'Uji CRUD yang akan dibatalkan.', 'content' => 'Konten uji.', 'image' => '', 'views' => 0, 'status' => 'draft', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')])) throw new RuntimeException(cms_last_error());
    $article = find_article_by_id($articleId);
    if (!is_array($article) || $article['slug'] !== $slug) throw new RuntimeException('Uji baca artikel gagal.');
    if (find_article_by_id("' OR 1=1 -- ") !== null) throw new RuntimeException('Uji proteksi SQL injection gagal.');
    if (!increment_article_views($articleId) || article_views(find_article_by_id($articleId) ?? []) !== 1) throw new RuntimeException('Uji pembaruan artikel gagal.');
    if (!save_faq(null, 'FAQ uji ' . $suffix, 'Jawaban uji')) throw new RuntimeException(cms_last_error());
    $faqId = (int) $pdo->lastInsertId();
    if ($faqId < 1 || !delete_faq($faqId) || !delete_article($articleId)) throw new RuntimeException('Uji hapus data gagal.');

    $uploadPath = 'uploads/articles/test-' . $suffix . '.png';
    if (!cms_save_upload($uploadPath, 'test-image', 'image/png')) throw new RuntimeException(cms_last_error());
    $upload = cms_load_upload($uploadPath);
    if (!is_array($upload) || $upload['content'] !== 'test-image') throw new RuntimeException('Uji penyimpanan gambar gagal.');
    $statement = $pdo->prepare('DELETE FROM cms_uploads WHERE upload_path = :path');
    $statement->execute(['path' => $uploadPath]);
    $pdo->rollBack();
    echo "Pengujian MySQL CMS lulus; semua data pengujian telah di-rollback.\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Pengujian gagal: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
