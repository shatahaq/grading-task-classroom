<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Env;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file = APP_PATH . DIRECTORY_SEPARATOR . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'functions.php';

Env::load(BASE_PATH . DIRECTORY_SEPARATOR . '.env');

$timezone = (string) Env::get('APP_TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set($timezone);

$sessionDomain = trim((string) Env::get('SESSION_DOMAIN', ''));

Config::set([
    'app' => [
        'name' => (string) Env::get('APP_NAME', 'AutoGrade AI'),
        'env' => (string) Env::get('APP_ENV', 'production'),
        'debug' => Env::bool('APP_DEBUG', false),
        'key' => (string) Env::get('APP_KEY', ''),
        'url' => rtrim((string) Env::get('APP_URL', 'http://127.0.0.1:8000'), '/'),
        'timezone' => $timezone,
    ],
    'database' => [
        'connection' => (string) Env::get('DB_CONNECTION', 'mysql'),
        'host' => (string) Env::get('DB_HOST', '127.0.0.1'),
        'port' => (string) Env::get('DB_PORT', '3306'),
        'database' => (string) Env::get('DB_DATABASE', ''),
        'username' => (string) Env::get('DB_USERNAME', 'root'),
        'password' => (string) Env::get('DB_PASSWORD', ''),
    ],
    'session' => [
        'lifetime' => (int) Env::get('SESSION_LIFETIME', 120),
        'path' => (string) Env::get('SESSION_PATH', '/'),
        'domain' => in_array(strtolower($sessionDomain), ['', 'null'], true) ? null : $sessionDomain,
        'secure' => is_https(),
    ],
    'services' => [
        'google' => [
            'client_id' => (string) Env::get('GOOGLE_CLIENT_ID', ''),
            'client_secret' => (string) Env::get('GOOGLE_CLIENT_SECRET', ''),
            'redirect' => (string) Env::get(
                'GOOGLE_REDIRECT_URI',
                rtrim((string) Env::get('APP_URL', 'http://127.0.0.1:8000'), '/') . '/auth/google/callback'
            ),
            'scopes' => [
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/classroom.courses.readonly',
                'https://www.googleapis.com/auth/classroom.coursework.students',
                'https://www.googleapis.com/auth/classroom.student-submissions.students.readonly',
                'https://www.googleapis.com/auth/classroom.rosters.readonly',
                'https://www.googleapis.com/auth/classroom.profile.emails',
                'https://www.googleapis.com/auth/drive.file',
                'https://www.googleapis.com/auth/drive.readonly',
                'https://www.googleapis.com/auth/gmail.send',
            ],
        ],
        'n8n' => [
            'webhook_url' => (string) Env::get('N8N_WEBHOOK_URL', ''),
            'api_key' => (string) Env::get('N8N_API_KEY', ''),
        ],
    ],
]);

if (Config::get('app.debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

ensure_directory(STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs');
ensure_directory(STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads');
ensure_directory(STORAGE_PATH . DIRECTORY_SEPARATOR . 'cache');

ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

session_name('autograde_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => Config::get('session.path', '/'),
    'domain' => Config::get('session.domain'),
    'secure' => (bool) Config::get('session.secure', false),
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
