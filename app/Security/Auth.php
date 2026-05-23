<?php

declare(strict_types=1);

namespace App\Security;

use App\Repositories\UserRepository;

final class Auth
{
    private static ?array $user = null;
    private static bool $resolved = false;

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$user;
        }

        self::$resolved = true;
        $userId = $_SESSION['user_id'] ?? null;

        if (! is_numeric($userId)) {
            return null;
        }

        self::$user = (new UserRepository())->findById((int) $userId);

        if (self::$user === null) {
            unset($_SESSION['user_id']);
        }

        return self::$user;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        self::$user = $user;
        self::$resolved = true;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        session_start();
        session_regenerate_id(true);
        self::$user = null;
        self::$resolved = true;
    }
}
