<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Router;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'native.php';

$router = new Router();

require BASE_PATH . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';

try {
    $router->dispatch(new Request());
} catch (Throwable $exception) {
    error_log($exception);

    $status = $exception instanceof PDOException ? 503 : 500;
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');

    $message = config('app.debug')
        ? $exception->getMessage()
        : 'Terjadi kesalahan pada aplikasi. Silakan coba lagi.';
    ?>
    <!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Terjadi Kesalahan</title>
        <link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
    </head>
    <body class="auth-screen">
        <main class="error-page">
            <p class="eyebrow"><?= e((string) $status) ?></p>
            <h1>Aplikasi belum bisa memproses request.</h1>
            <p><?= e($message) ?></p>
            <a class="button secondary" href="/">Kembali</a>
        </main>
    </body>
    </html>
    <?php
}
