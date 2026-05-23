<?php

declare(strict_types=1);

namespace App\Security;

use App\Core\Request;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['_csrf_token'];
    }

    public static function validate(Request $request): void
    {
        $token = (string) ($request->input('_token', '') ?: $request->header('X-CSRF-TOKEN') ?: '');

        if ($token === '' || ! hash_equals(self::token(), $token)) {
            json_response(['message' => 'CSRF token tidak valid. Muat ulang halaman lalu coba lagi.'], 419);
        }
    }
}
