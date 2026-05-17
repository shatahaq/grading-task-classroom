<?php $__env->startSection('content'); ?>
    <?php
        $labelClass = 'mb-1 block text-sm font-bold text-paper-ink/80';
        $inputClass = 'block w-full rounded-md border border-paper-line/50 bg-white px-4 py-2.5 text-sm text-paper-ink shadow-[inset_0_1px_3px_rgba(0,0,0,0.02)] transition-all focus:border-paper-marker focus:outline-none focus:ring-4 focus:ring-paper-marker/30';
        $textareaClass = 'block w-full rounded-md border border-paper-line/50 bg-white px-4 py-2.5 text-sm text-paper-ink shadow-[inset_0_1px_3px_rgba(0,0,0,0.02)] transition-all focus:border-paper-marker focus:outline-none focus:ring-4 focus:ring-paper-marker/30 resize-y';
        $panelClass = 'paper-sheet p-6 sm:p-8 rounded-[1rem] relative mb-6';
        $steps = [
            ['title' => 'Informasi', 'icon' => 'file-text'],
            ['title' => 'Penilaian', 'icon' => 'settings-2'],
            ['title' => 'Berkas', 'icon' => 'cloud-upload'],
            ['title' => 'Otomasi', 'icon' => 'bot'],
        ];
    ?>

    <?php if($notice): ?>
        <section role="status">
            <?php echo e($notice); ?>

        </section>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('assignments.store')); ?>" enctype="multipart/form-data" data-completion-form class="grid lg:grid-cols-[240px_1fr] xl:grid-cols-[260px_1fr] gap-8">
        <?php echo csrf_field(); ?>

        <aside class="hidden lg:block">
            <div class="sticky top-28 paper-sheet p-6 rounded-[1rem]">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-paper-ink/70">Setup flow</p>
                <ol class="mt-6 grid gap-4">
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center gap-4 text-paper-ink/70">
                            <span class="grid h-10 w-10 place-items-center rounded-full border border-paper-line bg-white shadow-sm">
                                <i data-lucide="<?php echo e($step['icon']); ?>" class="h-5 w-5"></i>
                            </span>
                            <span class="text-sm font-bold"><?php echo e($step['title']); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            </div>
        </aside>

        <div class="grid gap-6 lg:col-span-2 xl:col-span-3">
            <section class="paper-sheet p-8 sm:p-10 mb-2">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-paper-ink/70">Google Classroom assignment</p>
                <h1 class="mt-4 max-w-2xl font-serif text-3xl font-bold tracking-tight text-paper-ink sm:text-4xl">Buat tugas yang siap dinilai sejak pertama dipublikasi.</h1>
                <p class="mt-4 max-w-xl text-sm font-medium leading-relaxed text-paper-ink/80">Isi instruksi, batas penilaian, dan tiga file konteks agar workflow grading punya pegangan yang jelas.</p>
            </section>

            <section class="<?php echo e($panelClass); ?>" aria-labelledby="assignment-info-title">
                <header class="mb-6 flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-[-2deg]"><i data-lucide="file-text"></i></span>
                    <div>
                        <h2 id="assignment-info-title" class="font-serif text-xl font-bold text-paper-ink">Informasi Tugas</h2>
                        <p class="text-xs font-medium text-paper-ink/70">Kelas dan instruksi utama.</p>
                    </div>
                </header>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="course_id" class="<?php echo e($labelClass); ?>">Kelas</label>
                        <select id="course_id" name="course_id" class="<?php echo e($inputClass); ?>" required>
                            <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <option value="<?php echo e($course['id']); ?>" <?php if(old('course_id', $selectedCourse) === $course['id']): echo 'selected'; endif; ?>><?php echo e($course['name']); ?> - <?php echo e($course['section'] ?? '-'); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <option value="" disabled selected>Belum ada kelas tersedia</option>
                            <?php endif; ?>
                        </select>
                        <?php $__errorArgs = ['course_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs font-bold text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="title" class="<?php echo e($labelClass); ?>">Judul Tugas</label>
                        <input id="title" name="title" type="text" value="<?php echo e(old('title')); ?>" placeholder="Contoh: Tugas PHP Pertemuan 5" class="<?php echo e($inputClass); ?>" required>
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs font-bold text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="<?php echo e($labelClass); ?>">Deskripsi</label>
                        <textarea id="description" name="description" rows="5" placeholder="Instruksi, konteks tugas, dan ekspektasi jawaban mahasiswa..." class="<?php echo e($textareaClass); ?>"><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs font-bold text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </section>

            <section class="<?php echo e($panelClass); ?>" aria-labelledby="grading-settings-title">
                <header class="mb-6 flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-2"><i data-lucide="settings-2"></i></span>
                    <div>
                        <h2 id="grading-settings-title" class="font-serif text-xl font-bold text-paper-ink">Pengaturan Penilaian</h2>
                        <p class="text-xs font-medium text-paper-ink/70">Skor, mode, batas minimum, dan tenggat.</p>
                    </div>
                </header>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="max_score" class="<?php echo e($labelClass); ?>">Skor Maksimum</label>
                        <input id="max_score" name="max_score" type="number" min="0" max="999.99" step="0.01" value="<?php echo e(old('max_score', 100)); ?>" class="<?php echo e($inputClass); ?>" required>
                        <?php $__errorArgs = ['max_score'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs font-bold text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="grade_mode" class="<?php echo e($labelClass); ?>">Grade Mode</label>
                        <select id="grade_mode" name="grade_mode" class="<?php echo e($inputClass); ?>">
                            <option value="draft" <?php if(old('grade_mode', 'draft') === 'draft'): echo 'selected'; endif; ?>>Draft</option>
                            <option value="final" <?php if(old('grade_mode') === 'final'): echo 'selected'; endif; ?>>Final</option>
                            <option value="none" <?php if(old('grade_mode') === 'none'): echo 'selected'; endif; ?>>Tanpa Nilai</option>
                        </select>
                        <?php $__errorArgs = ['grade_mode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs font-bold text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="min_answer_length" class="<?php echo e($labelClass); ?>">Minimum Panjang Jawaban</label>
                        <input id="min_answer_length" name="min_answer_length" type="number" min="0" max="10000" step="1" value="<?php echo e(old('min_answer_length', 120)); ?>" class="<?php echo e($inputClass); ?>" required>
                        <?php $__errorArgs = ['min_answer_length'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs font-bold text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="due_date" class="<?php echo e($labelClass); ?>">Tenggat Waktu</label>
                        <input id="due_date" name="due_date" type="datetime-local" value="<?php echo e(old('due_date')); ?>" class="<?php echo e($inputClass); ?>">
                        <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs font-bold text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <label class="mt-6 flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-paper-line bg-paper-bg/50 p-4 transition-all hover:bg-paper-blue/20">
                    <span class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white text-paper-ink shadow-sm"><i data-lucide="calendar-clock" class="h-5 w-5"></i></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold text-paper-ink">Tutup Saat Tenggat Habis</span>
                            <span class="block text-xs font-medium text-paper-ink/70">Pengumpulan otomatis ditutup setelah deadline.</span>
                        </span>
                    </span>
                    <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full bg-paper-line transition-colors peer-checked:bg-paper-marker">
                        <input type="checkbox" name="close_on_due" value="1" <?php if(old('close_on_due')): echo 'checked'; endif; ?> class="peer sr-only">
                        <span class="inline-block h-4 w-4 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-6"></span>
                    </span>
                </label>
            </section>

            <section class="<?php echo e($panelClass); ?>" aria-labelledby="upload-title">
                <header class="mb-6 flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-[-3deg]"><i data-lucide="cloud-upload"></i></span>
                    <div>
                        <h2 id="upload-title" class="font-serif text-xl font-bold text-paper-ink">Unggah Berkas</h2>
                        <p class="text-xs font-medium text-paper-ink/70">Soal, rubrik, dan kunci jawaban.</p>
                    </div>
                </header>

                <div class="grid gap-5 md:grid-cols-3">
                    <?php $__currentLoopData = [
                        ['field' => 'question_file', 'label' => 'File Soal', 'icon' => 'help-circle'],
                        ['field' => 'rubric_file', 'label' => 'File Rubrik', 'icon' => 'list-checks'],
                        ['field' => 'answer_key_file', 'label' => 'File Kunci Jawaban', 'icon' => 'key-round'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <label for="<?php echo e($file['field']); ?>" class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-paper-line bg-paper-bg/30 p-6 text-center transition-all hover:border-paper-marker hover:bg-paper-blue/10">
                                <input id="<?php echo e($file['field']); ?>" name="<?php echo e($file['field']); ?>" type="file" data-file-input required class="sr-only">
                                <span class="relative mb-3 grid h-12 w-12 place-items-center rounded-full bg-white text-paper-ink shadow-sm transition-transform group-hover:scale-110 group-hover:rotate-6">
                                    <i data-lucide="<?php echo e($file['icon']); ?>" class="h-6 w-6"></i>
                                    <span class="absolute -bottom-1 -right-1 grid h-5 w-5 place-items-center rounded-full bg-paper-marker text-paper-ink shadow-sm"><i data-lucide="plus" class="h-3 w-3"></i></span>
                                </span>
                                <span class="block text-sm font-bold text-paper-ink"><?php echo e($file['label']); ?></span>
                                <span class="mt-1 block text-[11px] font-medium text-paper-ink/60" data-file-name="<?php echo e($file['field']); ?>" data-default-text="Seret file atau klik">Seret file atau klik</span>
                                <span class="mt-2 block rounded bg-paper-line/50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-paper-ink/60">PDF, DOCX, TXT</span>
                            </label>
                            <?php $__errorArgs = [$file['field']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-center text-xs font-bold text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            <section class="<?php echo e($panelClass); ?>" aria-labelledby="automation-title">
                <header class="mb-6 flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-2"><i data-lucide="bot"></i></span>
                    <div>
                        <h2 id="automation-title" class="font-serif text-xl font-bold text-paper-ink">Opsi Otomatis</h2>
                        <p class="text-xs font-medium text-paper-ink/70">Tentukan aksi setelah AI selesai.</p>
                    </div>
                </header>

                <div class="grid gap-3">
                    <?php $__currentLoopData = [
                        ['name' => 'auto_approval', 'title' => 'Auto Approval', 'desc' => 'Nilai disetujui otomatis jika confidence HIGH.', 'icon' => 'circle-check'],
                        ['name' => 'auto_email', 'title' => 'Auto Email Feedback', 'desc' => 'Feedback penilaian dikirim ke email mahasiswa.', 'icon' => 'send'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $toggle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-paper-line bg-paper-bg/50 p-4 transition-all hover:bg-paper-blue/20">
                            <span class="flex items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white text-paper-ink shadow-sm"><i data-lucide="<?php echo e($toggle['icon']); ?>" class="h-5 w-5"></i></span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-bold text-paper-ink"><?php echo e($toggle['title']); ?></span>
                                    <span class="block text-xs font-medium text-paper-ink/70"><?php echo e($toggle['desc']); ?></span>
                                </span>
                            </span>
                            <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full bg-paper-line transition-colors peer-checked:bg-paper-marker">
                                <input type="checkbox" name="<?php echo e($toggle['name']); ?>" value="1" <?php if(old($toggle['name'])): echo 'checked'; endif; ?> class="peer sr-only">
                                <span class="inline-block h-4 w-4 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-6"></span>
                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            <footer class="sticky bottom-0 z-20 mt-8 flex items-center justify-between gap-4 border-t border-paper-line bg-paper-bg/80 px-4 py-4 backdrop-blur-md sm:px-8 mx-[-1.25rem] sm:mx-[-2rem] mb-[-2.5rem]">
                <div class="flex items-center gap-4">
                    <div class="h-2 w-24 overflow-hidden rounded-full bg-paper-line/50">
                        <span data-completion-meter class="block h-full bg-paper-marker transition-all duration-300" style="width: 0%"></span>
                    </div>
                    <span data-completion-label class="text-[11px] font-bold uppercase tracking-widest text-paper-ink/70">0% siap</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="<?php echo e(route('dashboard')); ?>" class="group relative inline-flex items-center justify-center bg-transparent px-5 py-2.5 text-sm font-bold text-paper-ink">
                        <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:-rotate-1 group-hover:scale-105"></span>
                        <span class="relative inline-flex items-center gap-2"><i data-lucide="arrow-left" class="h-4 w-4"></i> Batal</span>
                    </a>
                    <button type="submit" class="group relative inline-flex items-center justify-center bg-transparent px-6 py-2.5 text-sm font-bold text-paper-ink">
                        <span class="absolute inset-0 rounded-sm bg-paper-marker shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:rotate-1 group-hover:scale-105"></span>
                        <span class="relative inline-flex items-center gap-2"><i data-lucide="sparkles" class="h-4 w-4"></i> Simpan & Publikasikan</span>
                    </button>
                </div>
            </footer>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', [
    'title' => 'Buat Tugas - AutoGrade AI',
    'pageTitle' => 'Buat Tugas',
    'pageCaption' => 'Publikasikan tugas dan siapkan konteks AI.',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\PW-2\Projek_AI\resources\views/assignments/create.blade.php ENDPATH**/ ?>