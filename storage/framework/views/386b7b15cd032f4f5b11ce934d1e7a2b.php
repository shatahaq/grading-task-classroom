<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoGrade AI</title>
    <script>
        try {
            if ((localStorage.getItem('theme') || 'dark') !== 'light') {
                document.documentElement.classList.add('dark');
            }
        } catch (error) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-lush-canvas text-lush-ink antialiased dark:bg-lush-dark dark:text-lush-fog">
    <main class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-lush-dark via-lush-dark-surface to-lush-dark dark:opacity-100"></div>
        <div class="absolute -left-32 top-20 h-80 w-80 rounded-full bg-lush-lime/18 blur-3xl animate-mesh-shift"></div>
        <div class="absolute right-0 top-0 h-[34rem] w-[34rem] rounded-full bg-white/6 blur-3xl animate-mesh-shift"></div>
        <div class="absolute inset-x-0 bottom-0 h-32 bg-lush-canvas clip-slant dark:bg-lush-dark-surface/80"></div>

        <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
            <a href="<?php echo e(route('home')); ?>" class="group flex items-center gap-3 rounded-full transition-all duration-300 hover:scale-[1.02] focus:outline-none focus:ring-4 focus:ring-lush-lime/30">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-lush-lime text-black shadow-lime transition-transform duration-300 group-hover:rotate-3">
                    <i data-lucide="brain-circuit" class="h-6 w-6"></i>
                </span>
                <span>
                    <span class="block text-lg font-extrabold tracking-tight text-white">AutoGrade AI</span>
                    <span class="block text-xs font-bold uppercase tracking-wide text-white/40">Assessment workspace</span>
                </span>
            </a>

            <button type="button" data-theme-toggle class="flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-2 text-sm font-bold text-white backdrop-blur-xl transition-all duration-300 hover:scale-[1.02] hover:border-lush-lime/40 hover:shadow-lime focus:outline-none focus:ring-4 focus:ring-lush-lime/30" aria-label="Toggle tema" aria-pressed="true">
                <span class="relative h-5 w-9 rounded-full bg-white/20">
                    <span class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-lush-lime transition-transform duration-300 dark:translate-x-4"></span>
                </span>
                <span data-theme-label class="hidden sm:inline">Dark</span>
            </button>
        </header>

        <section class="relative z-10 mx-auto grid min-h-[calc(100vh-5.5rem)] max-w-7xl gap-10 px-5 pb-16 pt-8 sm:px-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
            <div class="max-w-3xl animate-fade-in">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white/65 backdrop-blur-xl">
                    <i data-lucide="sparkles" class="h-4 w-4 text-lush-lime"></i>
                    Built for Google Classroom grading
                </p>

                <h1 class="mt-8 max-w-4xl text-5xl font-extrabold leading-[0.95] tracking-tight text-white sm:text-6xl lg:text-7xl">AutoGrade AI</h1>
                <p class="mt-8 max-w-2xl text-lg font-medium leading-8 text-white/58">
                    Ruang kerja penilaian yang menghubungkan Classroom, rubrik, dan review dosen dalam alur yang rapi, cepat, dan tetap bisa dikontrol manusia.
                </p>

                <?php if(session('error')): ?>
                    <section class="mt-6 rounded-2xl border border-red-400/40 bg-red-500/10 p-4 text-sm font-bold text-red-200" role="alert">
                        <?php echo e(session('error')); ?>

                    </section>
                <?php endif; ?>

                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <a href="<?php echo e(route('auth.google.redirect')); ?>" class="group inline-flex items-center justify-center gap-3 rounded-full bg-lush-lime px-6 py-4 text-sm font-extrabold text-black shadow-lime transition-all duration-300 hover:scale-[1.02] hover:bg-lush-lime/85 hover:shadow-[0_0_36px_rgba(193,255,0,0.38)] focus:outline-none focus:ring-4 focus:ring-lush-lime/30">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06L5.84 9.9C6.71 7.3 9.14 5.38 12 5.38z"/>
                        </svg>
                        <span>
                            <span class="block leading-none">Sign in dengan Google Classroom</span>
                            <span class="mt-1 block text-left text-[11px] font-bold uppercase tracking-wide text-black/55">OAuth aman, tanpa password lokal</span>
                        </span>
                    </a>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-3 text-xs font-bold text-white/65 backdrop-blur-xl">
                        <i data-lucide="shield-check" class="h-4 w-4 text-lush-lime"></i>
                        Used by 120+ educators
                    </span>
                </div>
            </div>

            <aside class="relative animate-slide-in-left">
                <div class="absolute -left-6 top-8 h-24 w-24 rounded-full border border-lush-lime/30"></div>
                <section class="lush-noise relative rounded-2xl border border-white/10 bg-white/[0.07] p-4 shadow-dark-glow backdrop-blur-2xl ring-1 ring-white/5 transition-all duration-300 hover:scale-[1.01] hover:border-lush-lime/35 sm:p-6" aria-label="Preview antrian grading">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-white/40">Live queue</p>
                            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-white">Antrian grading</h2>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-lush-lime px-3 py-2 text-xs font-extrabold text-black">
                            <i data-lucide="activity" class="h-4 w-4"></i>
                            Live
                        </span>
                    </div>

                    <div class="mt-8 grid gap-3">
                        <?php $__currentLoopData = [
                            ['task' => 'Tugas tersinkron', 'state' => 'Selesai', 'icon' => 'circle-check', 'tone' => 'text-emerald-300', 'score' => '78.5'],
                            ['task' => 'Submission perlu review', 'state' => 'Review', 'icon' => 'alert-triangle', 'tone' => 'text-amber-300', 'score' => '64.0'],
                            ['task' => 'Workflow siap jalan', 'state' => 'Siap', 'icon' => 'clock', 'tone' => 'text-white/55', 'score' => 'Ready'],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="group rounded-2xl border border-white/10 bg-black/18 p-5 ring-1 ring-white/5 transition-all duration-300 hover:translate-x-1 hover:border-lush-lime/35 hover:bg-black/28">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-extrabold text-white"><?php echo e($item['task']); ?></h3>
                                        <p class="mt-1 text-xs font-bold uppercase tracking-wide text-white/35">Google Classroom</p>
                                    </div>
                                    <i data-lucide="<?php echo e($item['icon']); ?>" class="h-5 w-5 <?php echo e($item['tone']); ?>"></i>
                                </div>
                                <div class="mt-5 flex items-end justify-between">
                                    <span class="rounded-full border border-white/10 px-3 py-1.5 text-xs font-bold text-white/60"><?php echo e($item['state']); ?></span>
                                    <span class="font-mono text-2xl font-extrabold text-lush-lime"><?php echo e($item['score']); ?></span>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            </aside>
        </section>
    </main>
</body>
</html>
<?php /**PATH C:\laragon\www\PW-2\Projek_AI\resources\views/welcome.blade.php ENDPATH**/ ?>