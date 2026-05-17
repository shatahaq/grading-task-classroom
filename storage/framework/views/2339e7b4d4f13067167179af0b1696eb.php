<?php $__env->startSection('content'); ?>
    <?php
        $hour = now('Asia/Jakarta')->hour;
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        $statusLabel = fn (?string $status) => match ($status) {
            'completed' => 'Selesai',
            'grading' => 'Sedang dinilai',
            'failed' => 'Gagal',
            'ready' => 'Siap',
            default => ucfirst($status ?? 'Draft'),
        };
        $statusMeta = fn (?string $status) => match ($status) {
            'completed' => ['badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300', 'icon' => 'circle-check', 'bar' => 'border-l-emerald-400'],
            'grading' => ['badge' => 'bg-lush-lime/15 text-[#4B6500] dark:text-lush-lime', 'icon' => 'brain', 'bar' => 'border-l-lush-lime'],
            'failed' => ['badge' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300', 'icon' => 'circle-x', 'bar' => 'border-l-red-400'],
            'ready' => ['badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300', 'icon' => 'clock', 'bar' => 'border-l-amber-400'],
            default => ['badge' => 'bg-white text-[#666666] dark:bg-white/5 dark:text-[#A8A8A8]', 'icon' => 'file-text', 'bar' => 'border-l-lush-border dark:border-l-white/10'],
        };
        $courseName = fn (?string $courseId) => $courseId ?: 'Google Classroom';
    ?>

    <section class="grid gap-6">
        <article class="paper-sheet p-8 sm:p-10 mb-2">
            <div class="relative z-10">
                <span class="inline-flex items-center rounded-full border-2 border-paper-marker bg-white px-4 py-1.5 text-xs font-bold uppercase tracking-[0.1em] text-paper-ink shadow-sm rotate-[-1deg]">
                    Human-in-the-loop
                </span>
                <h1 class="mt-6 max-w-3xl font-serif text-3xl font-bold tracking-tight text-paper-ink sm:text-4xl">
                    <?php echo e($greeting); ?>, <?php echo e(auth()->user()?->name ?? '061_Shata Diyaul Haq'); ?>.
                </h1>
                <p class="mt-4 max-w-2xl text-sm font-medium leading-relaxed text-paper-ink/80">
                    Pantau tugas, review hasil AI, dan kirim feedback dari satu dashboard yang fokus pada keputusan penting.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?php echo e(route('assignments.create')); ?>" class="group relative inline-flex items-center justify-center bg-transparent px-6 py-2.5 text-sm font-bold text-paper-ink">
                        <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:rotate-1 group-hover:scale-105"></span>
                        <span class="relative">Tugas Baru</span>
                    </a>
                    <a href="<?php echo e(route('courses.index')); ?>" class="group relative inline-flex items-center justify-center bg-transparent px-6 py-2.5 text-sm font-bold text-paper-ink">
                        <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:-rotate-1 group-hover:scale-105"></span>
                        <span class="relative">Lihat Kelas</span>
                    </a>
                </div>
            </div>
        </article>

        <aside class="grid gap-4 sm:gap-6 md:grid-cols-3">
            <article class="paper-sheet p-6 relative">
                <div class="paper-clip"></div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-paper-ink/70">Perlu review</p>
                <p class="mt-2 text-5xl font-extrabold tracking-tight text-paper-ink"><?php echo e($stats['review'] ?? 0); ?></p>
                <p class="mt-4 text-xs font-medium text-paper-ink/80">hasil menunggu keputusan dosen</p>
            </article>
            <article class="paper-sheet p-6 relative">
                <div class="paper-clip"></div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-paper-ink/70">Kelas</p>
                <p class="mt-2 text-5xl font-extrabold tracking-tight text-paper-ink"><?php echo e($stats['courses'] ?? 0); ?></p>
            </article>
            <article class="paper-sheet p-6 relative">
                <div class="paper-clip"></div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-paper-ink/70">Disetujui</p>
                <p class="mt-2 text-5xl font-extrabold tracking-tight text-paper-ink"><?php echo e($stats['approved'] ?? 0); ?></p>
            </article>
        </aside>
    </section>

    <section class="mt-8 mb-10">
        <section class="paper-sheet rounded-[1rem]">
            <header class="flex flex-col gap-4 border-b border-paper-line/50 p-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-paper-ink/70">Assignment stream</p>
                </div>
                <a href="<?php echo e(route('assignments.create')); ?>" class="group relative inline-flex items-center justify-center bg-transparent px-5 py-2 text-sm font-bold text-paper-ink">
                    <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:-rotate-1 group-hover:scale-105"></span>
                    <span class="relative">Buat Tugas</span>
                </a>
            </header>

            <?php if($recentAssignments->isNotEmpty()): ?>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-paper-ink/80 border-b border-paper-line/50">
                            <tr>
                                <th class="py-4 px-6 font-bold font-serif text-[15px]">Daftar Tugas</th>
                                <th class="py-4 px-6 font-bold font-serif text-[15px]">Date</th>
                                <th class="py-4 px-6 font-bold font-serif text-[15px]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-paper-line/30">
                            <?php $__currentLoopData = $recentAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-paper-blue/10 transition-colors">
                                    <td class="py-4 px-6 font-medium text-paper-ink">
                                        <a href="<?php echo e(route('assignments.grading', $assignment)); ?>" class="hover:underline"><?php echo e($assignment->title); ?></a>
                                    </td>
                                    <td class="py-4 px-6 font-medium text-paper-ink/80"><?php echo e($assignment->created_at?->format('d M Y') ?? 'N/A'); ?></td>
                                    <td class="py-4 px-6 font-bold text-paper-ink">
                                        <span class="marker-highlight px-2 py-0.5 inline-block"><?php echo e($statusLabel($assignment->status)); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <section class="flex min-h-[16rem] flex-col items-center justify-center p-8 text-center text-paper-ink">
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-3">
                        <i data-lucide="clipboard-list" class="h-8 w-8"></i>
                    </span>
                    <h3 class="mt-6 font-serif text-2xl font-bold">Belum ada tugas</h3>
                    <p class="mt-2 max-w-md text-sm font-medium leading-relaxed opacity-80">Mulai dari rubrik dan kunci jawaban, lalu biarkan workflow menyiapkan review awal.</p>
                    <a href="<?php echo e(route('assignments.create')); ?>" class="group relative mt-6 inline-flex items-center justify-center bg-transparent px-6 py-3 text-sm font-bold text-paper-ink">
                        <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:-rotate-2 group-hover:scale-105"></span>
                        <span class="relative inline-flex items-center gap-2"><i data-lucide="file-plus-2" class="h-4 w-4"></i> Buat tugas pertama</span>
                    </a>
                </section>
            <?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', [
    'title' => 'Dashboard - AutoGrade AI',
    'pageTitle' => 'Dashboard',
    'pageCaption' => 'Ruang kontrol grading otomatis.',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\PW-2\Projek_AI\resources\views/dashboard.blade.php ENDPATH**/ ?>