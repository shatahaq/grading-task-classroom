<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'AutoGrade AI') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= e(asset('autograde-icon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
</head>
<body>
    <main class="landing-page">
        <?php if ($message = flash_get('status')): ?>
            <div class="landing-alert"><div class="alert success" role="status"><?= e($message) ?></div></div>
        <?php endif; ?>
        <?php if ($message = flash_get('error')): ?>
            <div class="landing-alert"><div class="alert danger" role="alert"><?= e($message) ?></div></div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</body>
</html>
<?php unset($_SESSION['_flash']['errors']); clear_old(); ?>
