<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'AutoGrade AI'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>

<body class="min-h-screen paper-bg text-paper-ink antialiased">
    <?php
        $user = auth()->user();
        $displayName = $user?->name ?? 'User';
        $displayEmail = $user?->email;
        $initials = collect(explode(' ', trim($displayName)))
            ->filter()
            ->take(2)
            ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'U';
        $tokenStatusText = $tokenStatus ?? ($user?->oauthToken && !$user?->oauthToken?->revoked ? 'Tersambung' : 'Belum tersambung');
        $connected = $tokenStatusText === 'Tersambung';
        $resolvedPageTitle = $pageTitle ?? match (true) {
            request()->routeIs('dashboard') => 'Dashboard',
            request()->routeIs('assignments.create') => 'Buat Tugas',
            request()->routeIs('assignments.grading') => 'Hasil Grading',
            request()->routeIs('courses.show') => 'Detail Kelas',
            request()->routeIs('courses.*') => 'Kelas Saya',
            default => 'AutoGrade AI',
        };
        $resolvedPageCaption = $pageCaption ?? 'Platform penilaian otomatis untuk dosen.';
        $activeNav = 'bg-paper-blue text-paper-ink shadow-[inset_0_-8px_0_rgba(255,255,255,0.45)] rotate-[-1deg]';
        $idleNav = 'text-paper-muted hover:bg-paper-blue/45 hover:text-paper-ink hover:rotate-[-1deg]';
        $navItems = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard', 'active' => request()->routeIs('dashboard')],
            ['label' => 'Kelas', 'route' => 'courses.index', 'icon' => 'graduation-cap', 'active' => request()->routeIs('courses.*')],
            ['label' => 'Tugas Baru', 'route' => 'assignments.create', 'icon' => 'file-plus-2', 'active' => request()->routeIs('assignments.create')],
        ];
    ?>

    <div class="min-h-screen">
        <div data-sidebar-backdrop class="fixed inset-0 z-40 hidden bg-paper-ink/25 backdrop-blur-sm md:hidden"></div>

        <aside data-sidebar
            class="paper-sheet fixed inset-y-5 left-5 z-50 flex w-[18.5rem] -translate-x-[calc(100%+2rem)] flex-col rounded-[1.35rem] px-5 py-6 transition-transform duration-300 md:translate-x-0 lg:w-[19.5rem]">
            <header class="flex items-center justify-between gap-3">
                <a href="<?php echo e(route('dashboard')); ?>"
                    class="group flex min-w-0 items-center gap-3 rounded-2xl transition-all duration-300 hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-paper-blue/60">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-paper-blue font-serif text-xl font-bold text-paper-ink shadow-paper transition-transform duration-300 group-hover:rotate-[-3deg]">
                        AI
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-lg font-extrabold tracking-tight text-paper-ink">AutoGrade
                            AI</span>
                        <span
                            class="block truncate text-[11px] font-bold uppercase tracking-[0.22em] text-paper-muted">notebook
                            workspace</span>
                    </span>
                </a>

                <button type="button" data-sidebar-close
                    class="grid h-10 w-10 place-items-center rounded-full border border-paper-line bg-white/80 text-paper-ink transition-all duration-300 hover:rotate-6 hover:bg-paper-blue md:hidden"
                    aria-label="Tutup sidebar">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </header>

            <nav class="mt-10 grid gap-2" aria-label="Navigasi utama">
                <?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route($item['route'])); ?>"
                        class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-extrabold transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-paper-blue/60 <?php echo e($item['active'] ? $activeNav : $idleNav); ?>"
                        <?php if($item['active']): ?> aria-current="page" <?php endif; ?>>
                        <i data-lucide="<?php echo e($item['icon']); ?>" class="h-5 w-5 shrink-0"></i>
                        <span><?php echo e($item['label']); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            <section
                class="mt-auto rounded-2xl border border-paper-line bg-white/70 p-4 shadow-[inset_0_0_0_1px_rgba(255,255,255,0.7)]"
                aria-label="Akun pengguna">
                <div class="flex items-center gap-3">
                    <span
                        class="sketch-avatar flex h-14 w-14 shrink-0 items-center justify-center rounded-full font-serif text-sm font-bold text-paper-ink"><?php echo e($initials); ?></span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-extrabold text-paper-ink"><?php echo e($displayName); ?></span>
                        <?php if($displayEmail): ?>
                            <span class="block truncate text-xs font-medium text-paper-muted"><?php echo e($displayEmail); ?></span>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="mt-4 grid gap-2">
                    <form method="POST" action="<?php echo e(route('auth.google.disconnect')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="flex w-full items-center gap-2 rounded-xl border border-paper-line bg-white px-3 py-2 text-xs font-bold text-paper-muted transition-all duration-300 hover:bg-paper-blue/50 hover:text-paper-ink">
                            <i data-lucide="unplug" class="h-4 w-4"></i>
                            <span>Putuskan Google</span>
                        </button>
                    </form>

                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="flex w-full items-center gap-2 rounded-xl border border-paper-line bg-white px-3 py-2 text-xs font-bold text-paper-muted transition-all duration-300 hover:bg-paper-blue/50 hover:text-paper-ink">
                            <i data-lucide="door-open" class="h-4 w-4"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </section>
        </aside>

        <div class="min-h-screen md:pl-[22rem]">
            <header class="sticky top-0 z-30 bg-paper-bg/75 px-5 py-5 backdrop-blur-md sm:px-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" data-sidebar-open
                            class="grid h-11 w-11 place-items-center rounded-full border border-paper-line bg-white text-paper-ink shadow-paper md:hidden"
                            aria-label="Buka sidebar">
                            <i data-lucide="menu" class="h-5 w-5"></i>
                        </button>
                        <div class="min-w-0">
                            <p class="truncate font-serif text-2xl font-bold tracking-tight text-paper-ink">
                                <?php echo e($resolvedPageTitle); ?></p>
                            <p class="mt-1 truncate text-xs font-bold uppercase tracking-[0.22em] text-paper-muted">
                                <?php echo e($resolvedPageCaption); ?></p>
                        </div>
                    </div>

                    <span
                        class="hidden items-center gap-2 rounded-full bg-paper-blue px-4 py-2 text-xs font-extrabold text-paper-ink shadow-[inset_0_-6px_0_rgba(255,255,255,0.38)] sm:inline-flex">
                        <i data-lucide="<?php echo e($connected ? 'plug-zap' : 'circle-alert'); ?>" class="h-4 w-4"></i>
                        Google <?php echo e($connected ? 'Tersambung' : 'Belum'); ?>

                    </span>
                </div>
            </header>

            <main class="px-5 pb-10 pt-4 sm:px-8">
                <?php if(session('status')): ?>
                    <section
                        class="mb-6 rounded-2xl border border-sky-200 bg-paper-blue/70 p-4 text-sm font-bold text-paper-ink shadow-paper"
                        role="status">
                        <?php echo e(session('status')); ?>

                    </section>
                <?php endif; ?>

                <?php if($errors->has('google')): ?>
                    <section
                        class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700 shadow-paper"
                        role="alert">
                        <?php echo e($errors->first('google')); ?>

                    </section>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html><?php /**PATH C:\laragon\www\PW-2\Projek_AI\resources\views/layouts/app.blade.php ENDPATH**/ ?>