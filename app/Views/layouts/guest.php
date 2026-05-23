<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'AutoGrade AI') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
</head>
<body>
    <main class="auth-screen">
        <section class="auth-card glass-panel">
            <?php if ($message = flash_get('status')): ?>
                <div class="alert success" role="status"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($message = flash_get('error')): ?>
                <div class="alert danger" role="alert"><?= e($message) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </section>
    </main>
</body>
</html>
<?php unset($_SESSION['_flash']['errors']); clear_old(); ?>
