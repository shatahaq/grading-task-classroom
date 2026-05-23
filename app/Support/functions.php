<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Router;
use App\Security\Auth;
use App\Security\Csrf;

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    return (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function ensure_directory(string $path): void
{
    if (! is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function route(string $name, array|string|int $params = []): string
{
    return Router::url($name, $params);
}

function current_route(): ?string
{
    return Router::currentName();
}

function url(string $path = ''): string
{
    return rtrim((string) config('app.url', ''), '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function redirect(string $to, int $status = 302): never
{
    header('Location: ' . $to, true, $status);
    exit;
}

function redirect_back(string $fallback = '/'): never
{
    redirect((string) ($_SERVER['HTTP_REFERER'] ?? $fallback));
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function abort_response(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>' . $status . '</title>';
    echo '<body style="font-family:system-ui;padding:2rem;background:#f7f9fc;color:#172554">';
    echo '<h1>' . $status . '</h1><p>' . e($message) . '</p><p><a href="/">Kembali</a></p></body></html>';
    exit;
}

function flash(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function flash_get(string $key, mixed $default = null): mixed
{
    $value = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function flash_peek(string $key, mixed $default = null): mixed
{
    return $_SESSION['_flash'][$key] ?? $default;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash_old(array $input): void
{
    unset($input['_token'], $input['_method']);
    $_SESSION['_old'] = $input;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function errors(): array
{
    return $_SESSION['_flash']['errors'] ?? [];
}

function error_for(string $key): ?string
{
    $errors = errors();

    return isset($errors[$key]) ? (string) $errors[$key] : null;
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
}

function current_user(): ?array
{
    return Auth::user();
}

function now_string(): string
{
    return date('Y-m-d H:i:s');
}

function str_limit(string $value, int $limit = 120): string
{
    return mb_strlen($value) <= $limit ? $value : mb_substr($value, 0, $limit - 3) . '...';
}

function uuid_v4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function bool_input(mixed $value): bool
{
    return in_array($value, ['1', 1, true, 'true', 'on', 'yes'], true);
}
