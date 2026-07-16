<?php

/**
 * MySQL persistence layer.
 *
 * Credentials are supplied only through environment variables; no production
 * credential is stored in this repository.  See .env.example for the names.
 */

function cms_last_error(?string $message = null): string
{
    static $lastError = '';

    if ($message !== null) {
        $lastError = $message;
    }

    return $lastError;
}

function cms_env(string $name, string $default = ''): string
{
    static $envLoaded = false;
    if (!$envLoaded) {
        $envPath = dirname(dirname(__DIR__)) . '/.env';
        if (is_file($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    list($key, $val) = explode('=', $line, 2);
                    $key = trim($key);
                    $val = trim($val);
                    if (preg_match('/^"(.*)"$/', $val, $matches) || preg_match("/^'(.*)'$/", $val, $matches)) {
                        $val = $matches[1];
                    }
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }
        $envLoaded = true;
    }

    $value = getenv($name);

    if ($value === false && isset($_ENV[$name])) {
        $value = $_ENV[$name];
    }

    return $value === false || $value === null ? $default : trim((string) $value);
}

function cms_database_configured(): bool
{
    return cms_env('DB_NAME') !== '' && cms_env('DB_USER') !== '';
}

function cms_database_pdo(): ?PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!cms_database_configured()) {
        cms_last_error('Konfigurasi MySQL belum lengkap. Atur DB_NAME dan DB_USER di environment server.');
        return null;
    }

    if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
        cms_last_error('Driver PDO MySQL tidak tersedia pada server PHP ini.');
        return null;
    }

    $host = cms_env('DB_HOST', '127.0.0.1');
    $port = cms_env('DB_PORT', '3306');
    $name = cms_env('DB_NAME');
    $user = cms_env('DB_USER');
    $password = cms_env('DB_PASSWORD');

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );
    } catch (Throwable $exception) {
        cms_last_error('Koneksi ke MySQL gagal. Periksa konfigurasi database dan hak akses user.');
        error_log('[cms-db] ' . $exception->getMessage());
        return null;
    }

    return $pdo;
}

function cms_require_database(): PDO
{
    $pdo = cms_database_pdo();

    if (!$pdo instanceof PDO) {
        throw new RuntimeException(cms_last_error() ?: 'Database MySQL tidak tersedia.');
    }

    return $pdo;
}

function cms_encode_document(array $data): string
{
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    return $json;
}

function cms_decode_document(string $json, array $default = []): array
{
    try {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return is_array($data) ? $data : $default;
    } catch (JsonException $exception) {
        cms_last_error('Data database tidak valid.');
        error_log('[cms-db] ' . $exception->getMessage());
        return $default;
    }
}

function cms_load_document(string $key, array $default = []): array
{
    try {
        $statement = cms_require_database()->prepare(
            'SELECT data FROM cms_documents WHERE document_key = :document_key LIMIT 1'
        );
        $statement->execute(['document_key' => $key]);
        $row = $statement->fetch();

        return is_array($row) ? cms_decode_document((string) $row['data'], $default) : $default;
    } catch (Throwable $exception) {
        cms_last_error('Data CMS tidak dapat dibaca dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return $default;
    }
}

function cms_save_document(string $key, array $data): bool
{
    try {
        $statement = cms_require_database()->prepare(
            'INSERT INTO cms_documents (document_key, data, updated_at)
             VALUES (:document_key, :data, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = CURRENT_TIMESTAMP'
        );
        $statement->execute([
            'document_key' => $key,
            'data' => cms_encode_document($data),
        ]);
        cms_last_error('');
        return true;
    } catch (Throwable $exception) {
        cms_last_error('Data CMS tidak dapat disimpan ke MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return false;
    }
}

function cms_save_upload(string $path, string $content, string $contentType): bool
{
    try {
        $statement = cms_require_database()->prepare(
            'INSERT INTO cms_uploads (upload_path, content_type, content, size_bytes, created_at)
             VALUES (:upload_path, :content_type, :content, :size_bytes, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE content_type = VALUES(content_type), content = VALUES(content),
                 size_bytes = VALUES(size_bytes), created_at = CURRENT_TIMESTAMP'
        );
        $statement->bindValue(':upload_path', $path, PDO::PARAM_STR);
        $statement->bindValue(':content_type', $contentType, PDO::PARAM_STR);
        $statement->bindValue(':content', $content, PDO::PARAM_LOB);
        $statement->bindValue(':size_bytes', strlen($content), PDO::PARAM_INT);
        $statement->execute();
        cms_last_error('');
        return true;
    } catch (Throwable $exception) {
        cms_last_error('Gambar tidak dapat disimpan ke MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return false;
    }
}

function cms_load_upload(string $path): ?array
{
    try {
        $statement = cms_require_database()->prepare(
            'SELECT upload_path, content_type, content, size_bytes FROM cms_uploads WHERE upload_path = :upload_path LIMIT 1'
        );
        $statement->execute(['upload_path' => $path]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        return [
            'path' => (string) $row['upload_path'],
            'content_type' => (string) $row['content_type'],
            'content' => (string) $row['content'],
            'size_bytes' => (int) $row['size_bytes'],
        ];
    } catch (Throwable $exception) {
        cms_last_error('Gambar tidak dapat dibaca dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return null;
    }
}

function cms_delete_upload(string $path): bool
{
    try {
        $statement = cms_require_database()->prepare('DELETE FROM cms_uploads WHERE upload_path = :upload_path');
        $statement->execute(['upload_path' => $path]);
        cms_last_error('');
        return true;
    } catch (Throwable $exception) {
        cms_last_error('Gambar tidak dapat dihapus dari MySQL.');
        error_log('[cms-db] ' . $exception->getMessage());
        return false;
    }
}
