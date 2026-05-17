<?php $__env->startSection('content'); ?>
    <?php
        $studentCount = $course['students'] ?? null;
        $assignmentCount = is_array($courseWorks ?? null) ? count($courseWorks) : $assignments->count();
        $localByCoursework = $assignments->filter(fn ($assignment) => filled($assignment->coursework_id))->keyBy(fn ($assignment) => (string) $assignment->coursework_id);
        $statusMeta = function ($assignment) {
            $isOverdue = $assignment->due_date && $assignment->due_date->isPast() && $assignment->status !== 'completed';
            if ($isOverdue) return ['label' => 'Tenggat Lewat', 'badge' => '', 'icon' => 'circle-x', 'bar' => '', 'active' => false];
            return match ($assignment->status) {
                'completed' => ['label' => 'Selesai', 'badge' => '', 'icon' => 'circle-check', 'bar' => '', 'active' => false],
                'grading' => ['label' => 'Sedang Dinilai', 'badge' => '', 'icon' => 'brain', 'bar' => '', 'active' => true],
                'failed' => ['label' => 'Gagal', 'badge' => '', 'icon' => 'circle-x', 'bar' => '', 'active' => false],
                'ready' => ['label' => 'Siap', 'badge' => '', 'icon' => 'clock', 'bar' => '', 'active' => false],
                default => ['label' => ucfirst($assignment->status), 'badge' => '', 'icon' => 'clock', 'bar' => '', 'active' => false],
            };
        };
        $classroomStatusMeta = fn (?string $status) => match ($status) {
            'PUBLISHED' => ['label' => 'Published', 'badge' => '', 'icon' => 'circle-check'],
            'DRAFT' => ['label' => 'Draft', 'badge' => '', 'icon' => 'file-pen-line'],
            'DELETED' => ['label' => 'Deleted', 'badge' => '', 'icon' => 'trash-2'],
            default => ['label' => ucfirst(strtolower($status ?: 'unknown')), 'badge' => '', 'icon' => 'clock'],
        };
    ?>

    <?php if($notice): ?>
        <section role="status">
            <?php echo e($notice); ?>

        </section>
    <?php endif; ?>

    <section class="paper-sheet p-6 sm:p-8 rounded-[1rem] mb-6 flex flex-col md:flex-row md:items-start justify-between gap-6">
        <div>
            <a href="<?php echo e(route('courses.index')); ?>" class="inline-flex items-center gap-2 text-sm font-bold text-paper-ink/60 transition-colors hover:text-paper-ink mb-4">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Kembali
            </a>
            <div>
                <h1 class="font-serif text-3xl font-bold tracking-tight text-paper-ink"><?php echo e($course['name']); ?></h1>
                <p class="mt-1 text-sm font-medium text-paper-ink/70"><?php echo e($course['section'] ?? 'Kelas Google'); ?> · Semester Genap 2026</p>
            </div>
        </div>

        <div class="flex gap-4">
            <dl class="flex gap-4">
                <?php $__currentLoopData = [
                    ['label' => 'Mahasiswa', 'value' => $studentCount ?? '-', 'icon' => 'users'],
                    ['label' => is_array($courseWorks ?? null) ? 'Tugas Classroom' : 'Tugas Lokal', 'value' => $assignmentCount, 'icon' => 'clipboard-list'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-xl border border-paper-line bg-paper-bg/30 p-4 min-w-[120px]">
                        <dt class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-paper-ink/60">
                            <i data-lucide="<?php echo e($stat['icon']); ?>" class="h-4 w-4"></i>
                            <?php echo e($stat['label']); ?>

                        </dt>
                        <dd class="mt-2 text-3xl font-extrabold tracking-tight text-paper-ink"><?php echo e($stat['value']); ?></dd>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </dl>
        </div>
    </section>

    <section class="paper-sheet rounded-[1rem] overflow-hidden">
        <header class="border-b border-paper-line/50 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <label class="relative block w-full sm:max-w-xs">
                <span class="sr-only">Cari tugas kelas</span>
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-paper-ink/50"></i>
                <input type="search" placeholder="Cari tugas kelas..." data-search-target="[data-coursework-row]" data-search-empty="#coursework-empty-search" class="w-full rounded-full border border-paper-line/50 bg-paper-bg/30 py-2 pl-9 pr-4 text-sm text-paper-ink placeholder:text-paper-ink/40 focus:border-paper-marker focus:outline-none focus:ring-2 focus:ring-paper-marker/30">
            </label>
            <a href="<?php echo e(route('assignments.create', ['course_id' => $course['id']])); ?>" class="group relative inline-flex items-center justify-center bg-transparent px-5 py-2 text-sm font-bold text-paper-ink">
                <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:-rotate-1 group-hover:scale-105"></span>
                <span class="relative inline-flex items-center gap-2"><i data-lucide="plus" class="h-4 w-4"></i> Buat Tugas Baru</span>
            </a>
        </header>

        <div id="coursework-empty-search" class="hidden p-8 text-center text-sm font-medium text-paper-ink/60">Tidak ada tugas yang cocok.</div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-paper-ink/80 border-b border-paper-line/50 bg-paper-bg/30">
                    <tr>
                        <th class="py-4 px-6 font-bold font-serif text-[15px]">Tugas</th>
                        <th class="py-4 px-6 font-bold font-serif text-[15px] text-right">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-paper-line/30">
                <?php if(is_array($courseWorks)): ?>
                    <?php $__empty_1 = true; $__currentLoopData = $courseWorks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $work): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $localAssignment = $localByCoursework->get((string) ($work['id'] ?? ''));
                            $meta = $localAssignment ? $statusMeta($localAssignment) : $classroomStatusMeta($work['status'] ?? null);
                            $progress = $localAssignment && $studentCount ? min(100, round(($localAssignment->grading_results_count / $studentCount) * 100)) : 0;
                            $submittedText = $localAssignment ? ($studentCount ? "{$localAssignment->grading_results_count}/{$studentCount} dinilai" : "{$localAssignment->grading_results_count} dinilai") : 'Belum di-AutoGrade';
                            $active = $localAssignment && ($meta['active'] ?? false);
                        ?>
                        <tr data-coursework-row data-search-text="<?php echo e($work['title']); ?> <?php echo e($submittedText); ?> <?php echo e($meta['label']); ?>" class="hover:bg-paper-blue/10 transition-colors">
                            <td class="py-4 px-6">
                                <div>
                                    <h2 class="font-bold text-paper-ink text-base"><?php echo e($work['title']); ?></h2>
                                    <p class="mt-1 flex items-center gap-2 text-xs font-medium text-paper-ink/60">
                                        <i data-lucide="calendar-clock" class="h-3.5 w-3.5"></i>
                                        <?php echo e(isset($work['creationTime']) ? \Carbon\Carbon::parse($work['creationTime'])->translatedFormat('d M Y') : '-'); ?>

                                        <span class="opacity-50">·</span>
                                        <?php echo e(($work['due'] ?? '-') !== '-' ? \Carbon\Carbon::parse($work['due'])->translatedFormat('d M Y') : 'tanpa tenggat'); ?>

                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="marker-highlight inline-flex px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-paper-ink">Classroom</span>
                                        <span class="rounded bg-paper-line/50 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-paper-ink/60">Maks <?php echo e(isset($work['max_points']) ? number_format((float) $work['max_points'], 0) : '-'); ?></span>
                                        <?php if($localAssignment): ?>
                                            <span class="rounded border border-paper-line px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-paper-ink/80 bg-white">AutoGrade</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex flex-col items-end gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-paper-ink">
                                        <i data-lucide="<?php echo e($meta['icon']); ?>" class="h-4 w-4"></i>
                                        <?php echo e($meta['label']); ?>

                                    </span>
                                    <div class="flex flex-col items-end w-32">
                                        <p class="text-[10px] font-medium text-paper-ink/70 mb-1"><?php echo e($submittedText); ?></p>
                                        <div class="w-full h-1.5 rounded-full bg-paper-line/50 overflow-hidden">
                                            <span class="block h-full bg-paper-marker rounded-full" style="width: <?php echo e($progress); ?>%"></span>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <?php if($localAssignment): ?>
                                            <a href="<?php echo e(route('assignments.grading', $localAssignment)); ?>" class="inline-flex items-center gap-1.5 rounded border border-paper-line bg-white px-2.5 py-1 text-[11px] font-bold text-paper-ink hover:bg-paper-blue/10 transition-colors">
                                                <i data-lucide="eye" class="h-3 w-3"></i>
                                                Grading
                                            </a>
                                        <?php endif; ?>
                                        <?php if($work['alternateLink'] ?? null): ?>
                                            <a href="<?php echo e($work['alternateLink']); ?>" target="_blank" rel="noreferrer" class="inline-flex items-center gap-1.5 rounded border border-paper-line bg-paper-bg/50 px-2.5 py-1 text-[11px] font-bold text-paper-ink/70 hover:text-paper-ink transition-colors">
                                                <i data-lucide="external-link" class="h-3 w-3"></i>
                                                Classroom
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="2">
                                <div class="py-12 text-center text-paper-ink">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-paper-blue/20 text-paper-ink/50"><i data-lucide="clipboard-list" class="h-6 w-6"></i></span>
                                    <h2 class="mt-4 font-serif text-lg font-bold">Belum ada tugas di Classroom</h2>
                                    <p class="mt-1 text-sm text-paper-ink/70">Coursework belum tersedia. Buat tugas pertama dari AutoGrade.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php else: ?>
                    <?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $meta = $statusMeta($assignment);
                            $submittedText = "{$assignment->grading_results_count} dinilai";
                            $isOverdue = $assignment->due_date && $assignment->due_date->isPast() && $assignment->status !== 'completed';
                        ?>
                        <tr data-coursework-row data-search-text="<?php echo e($assignment->title); ?> <?php echo e($submittedText); ?> <?php echo e($meta['label']); ?>" class="hover:bg-paper-blue/10 transition-colors">
                            <td class="py-4 px-6">
                                <div>
                                    <h2 class="font-bold text-paper-ink text-base"><?php echo e($assignment->title); ?></h2>
                                    <p class="mt-1 flex items-center gap-2 text-xs font-medium text-paper-ink/60">
                                        <i data-lucide="calendar-clock" class="h-3.5 w-3.5"></i>
                                        <?php echo e($assignment->created_at?->translatedFormat('d M Y') ?? '-'); ?>

                                        <span class="opacity-50">·</span>
                                        <?php echo e($assignment->due_date?->translatedFormat('d M Y') ?? 'tanpa tenggat'); ?>

                                    </p>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex flex-col items-end gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-paper-ink">
                                        <i data-lucide="<?php echo e($meta['icon']); ?>" class="h-4 w-4"></i>
                                        <?php echo e($meta['label']); ?>

                                    </span>
                                    <p class="text-[10px] font-medium text-paper-ink/70"><?php echo e($submittedText); ?></p>
                                    <a href="<?php echo e(route('assignments.grading', $assignment)); ?>" class="mt-2 inline-flex items-center gap-1.5 rounded border border-paper-line bg-white px-2.5 py-1 text-[11px] font-bold text-paper-ink hover:bg-paper-blue/10 transition-colors">
                                        <i data-lucide="eye" class="h-3 w-3"></i>
                                        Grading
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="2">
                                <div class="py-12 text-center text-paper-ink">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-paper-blue/20 text-paper-ink/50"><i data-lucide="clipboard-list" class="h-6 w-6"></i></span>
                                    <h2 class="mt-4 font-serif text-lg font-bold">Data tugas belum tersedia</h2>
                                    <p class="mt-1 text-sm text-paper-ink/70">Sinkronisasi Classroom belum mengembalikan tugas untuk kelas ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', [
    'title' => ($course['name'] ?? 'Detail Kelas').' - AutoGrade AI',
    'pageTitle' => 'Detail Kelas',
    'pageCaption' => 'Stream tugas, progres penilaian, dan coursework.',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\PW-2\Projek_AI\resources\views/courses/show.blade.php ENDPATH**/ ?>