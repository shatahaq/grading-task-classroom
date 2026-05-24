<?php
$user = current_user();
$displayName = $user['name'] ?? 'User';
$displayEmail = $user['email'] ?? null;
$tokenStatusText = $tokenStatus ?? 'Tersambung';
$navItems = [
    ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => ['dashboard']],
    ['label' => 'Kelas', 'route' => 'courses.index', 'match' => ['courses.index', 'courses.show', 'courses.coursework']],
    ['label' => 'Tugas Baru', 'route' => 'assignments.create', 'match' => ['assignments.create']],
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title ?? 'AutoGrade AI') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= e(asset('autograde-icon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="<?= e(route('dashboard')) ?>">
                <span class="brand-mark" aria-hidden="true">
                    <img src="<?= e(asset('autograde-icon.svg')) ?>" alt="">
                </span>
                <span class="brand-text">AUTOGRADE AI</span>
            </a>

            <nav class="main-nav" aria-label="Navigasi utama">
                <?php foreach ($navItems as $item): ?>
                    <?php $active = in_array(current_route(), $item['match'], true); ?>
                    <a class="nav-button <?= $active ? 'active' : '' ?>" href="<?= e(route($item['route'])) ?>" <?= $active ? 'aria-current="page"' : '' ?>>
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="account-actions" aria-label="Akun pengguna">
                <div class="account-chip">
                    <span><?= e($displayName) ?></span>
                    <?php if ($displayEmail): ?><small><?= e($displayEmail) ?></small><?php endif; ?>
                </div>
                <form method="post" action="<?= e(route('logout')) ?>">
                    <?= csrf_field() ?>
                    <button class="header-action" type="submit">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="page-wrap">
        <?php if ($message = flash_get('status')): ?>
            <div class="alert success" role="status"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($message = flash_get('error')): ?>
            <div class="alert danger" role="alert"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($googleError = error_for('google')): ?>
            <div class="alert danger" role="alert"><?= e($googleError) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <script src="<?= e(asset('app.js')) ?>" defer></script>
</body>
</html>
<?php unset($_SESSION['_flash']['errors']); clear_old(); ?>
