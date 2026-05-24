<?php

declare(strict_types=1);

namespace App\Core;

use App\Security\Auth;

final class Request
{
    private ?array $json = null;

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofed = strtoupper((string) $_POST['_method']);

            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoofed;
            }
        }

        return $method;
    }

    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $normalized = trim($path, '/');

        return $normalized === '' ? '/' : '/' . $normalized;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }

        $json = $this->json();

        if (array_key_exists($key, $json)) {
            return $json[$key];
        }

        return $_GET[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return array_merge($this->json(), $_POST, $_GET);
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        if (! str_contains($contentType, 'application/json')) {
            return $this->json = [];
        }

        $body = file_get_contents('php://input') ?: '';
        $decoded = json_decode($body, true);

        return $this->json = is_array($decoded) ? $decoded : [];
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return isset($_FILES[$key]) && is_array($_FILES[$key]) ? $_FILES[$key] : null;
    }

    public function header(string $key): ?string
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

        if (isset($_SERVER[$serverKey])) {
            return (string) $_SERVER[$serverKey];
        }

        if (strtolower($key) === 'content-type' && isset($_SERVER['CONTENT_TYPE'])) {
            return (string) $_SERVER['CONTENT_TYPE'];
        }

        return null;
    }

    public function user(): ?array
    {
        return Auth::user();
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }
}
