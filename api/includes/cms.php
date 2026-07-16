<?php
require_once __DIR__ . '/database.php';

function load_site_data(): array
{
    return cms_load_document('site');
}

function save_site_data(array $data): bool
{
    return cms_save_document('site', $data);
}

function load_faq_data(string $search = ''): array
{
    try {
        $search = trim($search);
        $sql = 'SELECT id, question, answer FROM cms_faqs';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE question LIKE :search_question OR answer LIKE :search_answer';
            $searchTerm = '%' . mb_substr($search, 0, 100) . '%';
            $params['search_question'] = $searchTerm;
            $params['search_answer'] = $searchTerm;
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        $statement = cms_require_database()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    } catch (Throwable $exception) {
        cms_last_error('FAQ tidak dapat dibaca dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return [];
    }
}

function find_faq_by_id(int $id): ?array
{
    try {
        $statement = cms_require_database()->prepare(
            'SELECT id, question, answer FROM cms_faqs WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $faq = $statement->fetch();
        return is_array($faq) ? $faq : null;
    } catch (Throwable $exception) {
        cms_last_error('FAQ tidak dapat dibaca dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return null;
    }
}

function save_faq(?int $id, string $question, string $answer): bool
{
    try {
        $pdo = cms_require_database();

        if ($id !== null) {
            $statement = $pdo->prepare(
                'UPDATE cms_faqs SET question = :question, answer = :answer, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $statement->execute(['id' => $id, 'question' => $question, 'answer' => $answer]);
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO cms_faqs (question, answer, sort_order) VALUES (:question, :answer, COALESCE((SELECT MAX(sort_order) + 1 FROM (SELECT sort_order FROM cms_faqs) AS faq_order), 1))'
            );
            $statement->execute(['question' => $question, 'answer' => $answer]);
        }

        cms_last_error('');
        return true;
    } catch (Throwable $exception) {
        cms_last_error('FAQ tidak dapat disimpan ke MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return false;
    }
}

function delete_faq(int $id): bool
{
    try {
        $statement = cms_require_database()->prepare('DELETE FROM cms_faqs WHERE id = :id');
        $statement->execute(['id' => $id]);
        cms_last_error('');
        return $statement->rowCount() === 1;
    } catch (Throwable $exception) {
        cms_last_error('FAQ tidak dapat dihapus dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return false;
    }
}
