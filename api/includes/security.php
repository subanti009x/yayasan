<?php

function apply_security_headers(): void
{
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; img-src 'self' https: data:; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; frame-src https://docs.google.com https://www.google.com; form-action 'self' https://docs.google.com; connect-src 'self'");
}

function security_client_key(string $secret): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return hash_hmac('sha256', $ip, $secret);
}

function login_is_rate_limited(string $key): bool
{
    try {
        $statement = cms_require_database()->prepare('SELECT locked_until FROM cms_login_attempts WHERE client_key = :client_key LIMIT 1');
        $statement->execute(['client_key' => $key]);
        $row = $statement->fetch();
        return is_array($row) && !empty($row['locked_until']) && strtotime((string) $row['locked_until']) > time();
    } catch (Throwable $exception) {
        error_log('[security] ' . $exception->getMessage());
        return false;
    }
}

function record_login_failure(string $key): void
{
    try {
        $pdo = cms_require_database();
        $statement = $pdo->prepare('SELECT attempts, window_started FROM cms_login_attempts WHERE client_key = :client_key LIMIT 1');
        $statement->execute(['client_key' => $key]);
        $row = $statement->fetch();
        $windowExpired = !is_array($row) || strtotime((string) $row['window_started']) < time() - 900;
        $attempts = $windowExpired ? 1 : min(5, (int) $row['attempts'] + 1);
        $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
        $save = $pdo->prepare('INSERT INTO cms_login_attempts (client_key, attempts, window_started, locked_until) VALUES (:client_key, :attempts, CURRENT_TIMESTAMP, :locked_until) ON DUPLICATE KEY UPDATE attempts = VALUES(attempts), window_started = IF(:reset_window = 1, CURRENT_TIMESTAMP, window_started), locked_until = VALUES(locked_until)');
        $save->execute(['client_key' => $key, 'attempts' => $attempts, 'locked_until' => $lockedUntil, 'reset_window' => $windowExpired ? 1 : 0]);
    } catch (Throwable $exception) { error_log('[security] ' . $exception->getMessage()); }
}

function clear_login_failures(string $key): void
{
    try { $statement = cms_require_database()->prepare('DELETE FROM cms_login_attempts WHERE client_key = :client_key'); $statement->execute(['client_key' => $key]); } catch (Throwable $exception) { error_log('[security] ' . $exception->getMessage()); }
}
