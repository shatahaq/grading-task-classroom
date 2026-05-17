<?php $__env->startSection('content'); ?>
    <?php
        $scores = $results->whereNotNull('score_ai')->map(fn ($result) => (float) $result->score_ai);
        $highest = $scores->count() ? round($scores->max(), 1) : 0;
        $lowest = $scores->count() ? round($scores->min(), 1) : 0;
        $reviewCount = $results->where('status', 'REVIEW')->count();
        $statusMeta = match ($assignment->status) {
            'completed' => ['label' => 'Selesai', 'class' => '', 'icon' => 'circle-check'],
            'grading' => ['label' => 'Sedang Dinilai', 'class' => '', 'icon' => 'brain'],
            'failed' => ['label' => 'Gagal', 'class' => '', 'icon' => 'circle-x'],
            'ready' => ['label' => 'Siap', 'class' => '', 'icon' => 'clock'],
            default => ['label' => ucfirst($assignment->status), 'class' => '', 'icon' => 'clock'],
        };
        $gradingRows = $results->map(fn ($result) => [
            'id' => $result->id,
            'student_id' => $result->student_id,
            'student_email' => $result->student_email,
            'score_ai' => $result->score_ai,
            'confidence' => $result->confidence,
            'extraction_status' => $result->extraction_status,
            'needs_review' => $result->needs_review,
            'status' => $result->status,
            'reason' => $result->reason,
            'feedback_email' => $result->feedback_email,
            'rubric_breakdown' => $result->rubric_breakdown,
            'email_sent' => $result->email_sent,
        ])->values();
    ?>

    <section class="paper-sheet p-6 sm:p-8 rounded-[1rem] mb-6 flex flex-col md:flex-row md:items-start md:justify-between gap-6">
        <div>
            <a href="<?php echo e(route('courses.show', ['courseId' => $assignment->course_id])); ?>" class="inline-flex items-center gap-2 text-sm font-bold text-paper-ink/60 transition-colors hover:text-paper-ink mb-4">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Kembali
            </a>
            <div class="flex flex-wrap items-center gap-4">
                <h1 class="font-serif text-3xl font-bold tracking-tight text-paper-ink"><?php echo e($assignment->title); ?></h1>
                <span id="assignment-status-badge" class="marker-highlight inline-flex items-center gap-2 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-paper-ink">
                    <i data-lucide="<?php echo e($statusMeta['icon']); ?>" class="h-4 w-4"></i>
                    <span id="assignment-status"><?php echo e($statusMeta['label']); ?></span>
                </span>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium text-paper-ink/80">
                <span class="flex items-center gap-2"><i data-lucide="graduation-cap" class="h-4 w-4 opacity-70"></i><?php echo e($assignment->course_id); ?></span>
                <span class="flex items-center gap-2"><i data-lucide="award" class="h-4 w-4 opacity-70"></i>Maks <?php echo e(number_format((float) $assignment->max_score, 0)); ?></span>
                <span class="flex items-center gap-2"><i data-lucide="users" class="h-4 w-4 opacity-70"></i><?php echo e($results->count()); ?> mahasiswa</span>
                <span class="flex items-center gap-2"><i data-lucide="calendar-clock" class="h-4 w-4 opacity-70"></i><?php echo e($assignment->due_date?->translatedFormat('d M Y') ?? 'Tanpa tenggat'); ?></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button id="start-grading" type="button" class="group relative inline-flex items-center justify-center bg-transparent px-5 py-2.5 text-sm font-bold text-paper-ink">
                <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:-rotate-1 group-hover:scale-105"></span>
                <span class="relative inline-flex items-center gap-2"><i data-lucide="rotate-ccw" class="h-4 w-4"></i> Trigger Ulang</span>
            </button>
            <button type="button" class="group relative inline-flex items-center justify-center bg-transparent px-5 py-2.5 text-sm font-bold text-paper-ink">
                <span class="absolute inset-0 rounded-sm bg-paper-marker shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:rotate-1 group-hover:scale-105"></span>
                <span class="relative inline-flex items-center gap-2"><i data-lucide="send" class="h-4 w-4"></i> Kirim Email</span>
            </button>
        </div>
    </section>

    <div id="grading-message"></div>

    <section aria-label="Statistik hasil grading" class="mb-8 grid gap-4 sm:gap-6 md:grid-cols-3">
        <?php $__currentLoopData = [
            ['label' => 'Skor tertinggi', 'value' => $highest, 'icon' => 'award', 'decimals' => 0, 'hero' => true],
            ['label' => 'Skor terendah', 'value' => $lowest, 'icon' => 'trending-down', 'decimals' => 0, 'hero' => false],
            ['label' => 'Perlu review', 'value' => $reviewCount, 'icon' => 'alert-triangle', 'decimals' => 0, 'hero' => false],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="paper-sheet p-6 relative flex flex-col justify-between">
                <div class="paper-clip"></div>
                <div class="flex items-center justify-between mb-4">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-paper-ink/70"><?php echo e($stat['label']); ?></p>
                    <i data-lucide="<?php echo e($stat['icon']); ?>" class="h-5 w-5 text-paper-ink/40"></i>
                </div>
                <p class="text-5xl font-extrabold tracking-tight text-paper-ink" data-count-up="<?php echo e($stat['value']); ?>" data-decimals="<?php echo e($stat['decimals']); ?>"><?php echo e($stat['value']); ?></p>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <section class="paper-sheet rounded-[1rem] overflow-hidden">
        <header class="border-b border-paper-line/50 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <label class="relative block w-full sm:max-w-xs">
                <span class="sr-only">Cari nama mahasiswa</span>
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-paper-ink/50"></i>
                <input id="result-search" type="search" placeholder="Cari nama mahasiswa..." class="w-full rounded-full border border-paper-line/50 bg-paper-bg/30 py-2 pl-9 pr-4 text-sm text-paper-ink placeholder:text-paper-ink/40 focus:border-paper-marker focus:outline-none focus:ring-2 focus:ring-paper-marker/30">
            </label>
            <button type="button" id="sort-results" class="inline-flex items-center gap-2 rounded-full border border-paper-line bg-white px-4 py-2 text-xs font-bold text-paper-ink shadow-sm transition-all hover:bg-paper-blue/10">
                <i data-lucide="arrow-up-down" class="h-3.5 w-3.5"></i>
                Urutkan Skor
            </button>
        </header>

        <div data-skeleton class="hidden p-6 text-center">
            <i data-lucide="loader-2" class="mx-auto h-8 w-8 animate-spin text-paper-marker"></i>
            <p class="mt-2 text-sm font-medium text-paper-ink/60">Memuat hasil...</p>
        </div>
        <div data-hydrated class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-paper-ink/80 border-b border-paper-line/50 bg-paper-bg/30">
                    <tr>
                        <th class="py-4 px-6 font-bold font-serif text-[15px] w-12 text-center">#</th>
                        <th class="py-4 px-6 font-bold font-serif text-[15px]">Mahasiswa</th>
                        <th class="py-4 px-6 font-bold font-serif text-[15px]">Skor</th>
                        <th class="py-4 px-6 font-bold font-serif text-[15px]">Confidence</th>
                        <th class="py-4 px-6 font-bold font-serif text-[15px]">Status</th>
                        <th class="py-4 px-6 font-bold font-serif text-[15px]">Feedback</th>
                        <th class="py-4 px-6 font-bold font-serif text-[15px] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="grading-results" class="divide-y divide-paper-line/30"></tbody>
            </table>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const maxScore = Number(<?php echo json_encode((float) ($assignment->max_score ?: 100), 15, 512) ?>);
        let rows = <?php echo json_encode($gradingRows, 15, 512) ?>;
        let sortAscending = false;
        const tbody = document.getElementById('grading-results');
        const button = document.getElementById('start-grading');
        const message = document.getElementById('grading-message');
        const assignmentStatus = document.getElementById('assignment-status');
        const searchInput = document.getElementById('result-search');
        const sortButton = document.getElementById('sort-results');

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
        const studentName = (item) => (item.student_email || item.student_id || 'Mahasiswa').split('@')[0].replace(/[._-]+/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
        const initials = (name) => name.split(' ').filter(Boolean).slice(0, 2).map((part) => part[0]).join('').toUpperCase() || 'MA';
        const scoreTone = () => '';
        const confidenceClass = (conf) => {
            if (conf === 'HIGH') return 'text-emerald-600 bg-emerald-50 border-emerald-200';
            if (conf === 'LOW') return 'text-red-600 bg-red-50 border-red-200';
            return 'text-amber-600 bg-amber-50 border-amber-200';
        };
        const statusBadge = (item) => {
            if (item.status === 'APPROVED') return '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full border border-emerald-200 bg-emerald-50 text-[10px] font-bold text-emerald-700 uppercase tracking-wider"><i data-lucide="circle-check" class="h-3 w-3"></i>Approved</span>';
            if (item.status === 'FAILED') return '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full border border-red-200 bg-red-50 text-[10px] font-bold text-red-700 uppercase tracking-wider"><i data-lucide="circle-x" class="h-3 w-3"></i>Failed</span>';
            return '<span class="marker-highlight inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-bold text-paper-ink uppercase tracking-wider"><i data-lucide="alert-triangle" class="h-3 w-3"></i>Review</span>';
        };
        const rubricRows = (item) => {
            const rubric = Array.isArray(item.rubric_breakdown) ? item.rubric_breakdown : [];
            if (!rubric.length) return '<div class="text-sm text-paper-ink/60 italic">Breakdown rubrik belum tersedia.</div>';
            return rubric.map((row) => `<div class="mb-4 border-l-2 border-paper-line pl-4"><div class="flex justify-between items-center"><strong class="text-sm text-paper-ink">${escapeHtml(row.point || row.rubric_point || row.criteria || row.name || 'Rubrik')}</strong><span class="font-mono font-bold text-paper-ink/80 text-sm">${escapeHtml(row.score ?? row.skor ?? '-')}/${escapeHtml(row.max ?? row.max_score ?? row.maks ?? '-')}</span></div><p class="text-xs text-paper-ink/70 mt-1 whitespace-pre-line">${escapeHtml(row.feedback || row.reason || '-')}</p></div>`).join('');
        };
        const detailRow = (item, index) => {
            const name = studentName(item);
            const score = item.score_ai ?? '-';
            const emailPreview = item.feedback_email || `Yth. ${name},\n\nSkor sementara Anda adalah ${score}/${maxScore}. Silakan tinjau feedback dosen jika tersedia.`;
            return `<tr class="bg-paper-bg/30 border-b border-paper-line/30"><td colspan="7" class="p-0"><div id="detail-${index}" class="overflow-hidden transition-all duration-300" hidden><div class="p-6 sm:p-8 grid gap-8 lg:grid-cols-3"><section class="lg:col-span-2"><h3 class="font-serif text-lg font-bold text-paper-ink mb-4 pb-2 border-b border-paper-line/50">Breakdown Rubrik</h3><div>${rubricRows(item)}</div></section><section class="space-y-6"><div><h3 class="font-serif text-lg font-bold text-paper-ink mb-4 pb-2 border-b border-paper-line/50">Preview Email</h3><div class="rounded-lg border border-paper-line/50 bg-white p-4 text-xs font-mono text-paper-ink/80 whitespace-pre-wrap">${escapeHtml(emailPreview)}</div></div><div><h3 class="font-serif text-lg font-bold text-paper-ink mb-4 pb-2 border-b border-paper-line/50">Aksi</h3><div class="flex flex-wrap gap-2"><button type="button" class="inline-flex items-center gap-2 rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors"><i data-lucide="circle-check" class="h-3.5 w-3.5"></i>Accept</button><button type="button" class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-1.5 text-xs font-bold text-paper-ink border border-paper-line hover:bg-paper-blue/10 transition-colors"><i data-lucide="pencil" class="h-3.5 w-3.5"></i>Edit</button><button type="button" class="inline-flex items-center gap-2 rounded-md bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 border border-red-200 hover:bg-red-100 transition-colors"><i data-lucide="x" class="h-3.5 w-3.5"></i>Reject</button></div></div></section></div></div></td></tr>`;
        };

        function renderResults(data) {
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="7"><div class="py-12 text-center"><span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-paper-bg text-paper-ink/50 border-2 border-dashed border-paper-line"><i data-lucide="brain" class="h-8 w-8"></i></span><h2 class="mt-4 font-serif text-xl font-bold text-paper-ink">Belum ada hasil penilaian</h2><p class="mt-2 text-sm text-paper-ink/70">Klik Trigger Ulang untuk memulai workflow grading.</p></div></td></tr>`;
                window.refreshLucideIcons?.();
                return;
            }
            const top = Math.max(...data.map((item) => Number.parseFloat(item.score_ai ?? 0)));
            tbody.innerHTML = data.map((item, index) => {
                const name = studentName(item);
                const score = Number.parseFloat(item.score_ai ?? 0);
                const width = Math.max(0, Math.min(100, (score / maxScore) * 100));
                const feedback = item.reason || item.feedback_email || 'Feedback belum tersedia.';
                const isTop = score === top && top > 0;
                return `<tr class="hover:bg-paper-blue/10 transition-colors group">
                    <td class="py-4 px-6 text-center text-xs font-medium text-paper-ink/50">${index + 1}</td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <span class="sketch-avatar flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-serif text-xs font-bold text-paper-ink shadow-sm">${initials(name)}</span>
                            <div class="min-w-0 flex-1">
                                <span class="block truncate font-bold text-paper-ink">${escapeHtml(name)}</span>
                                <span class="block truncate text-xs font-medium text-paper-ink/60">${escapeHtml(item.student_email || item.student_id || '-')}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-lg font-extrabold text-paper-ink w-10 text-right">${escapeHtml(item.score_ai ?? '-')}</span>
                            <div class="h-2 w-24 overflow-hidden rounded-full bg-paper-line/50">
                                <span class="block h-full bg-paper-marker rounded-full" style="width: ${width}%"></span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6"><span class="inline-flex rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider ${confidenceClass(item.confidence)}">${escapeHtml(item.confidence || 'MEDIUM')}</span></td>
                    <td class="py-4 px-6">${statusBadge(item)}</td>
                    <td class="py-4 px-6 max-w-[200px]"><p class="truncate text-xs font-medium text-paper-ink/70" title="${escapeHtml(feedback)}">${escapeHtml(feedback)}</p></td>
                    <td class="py-4 px-6 text-right">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="grid h-8 w-8 place-items-center rounded-full bg-white border border-paper-line text-paper-ink/70 shadow-sm hover:bg-paper-blue hover:text-paper-ink transition-colors" data-accordion-trigger="#detail-${index}" aria-expanded="false" aria-label="Lihat detail"><i data-lucide="eye" class="h-4 w-4"></i></button>
                            <button type="button" class="grid h-8 w-8 place-items-center rounded-full bg-white border border-paper-line text-paper-ink/70 shadow-sm hover:bg-paper-blue hover:text-paper-ink transition-colors" aria-label="Edit skor"><i data-lucide="pencil" class="h-4 w-4"></i></button>
                        </div>
                    </td>
                </tr>${detailRow(item, index)}`;
            }).join('');
            window.refreshLucideIcons?.();
            document.querySelectorAll('[data-accordion-trigger]').forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    const target = document.querySelector(trigger.dataset.accordionTrigger);
                    const expanded = trigger.getAttribute('aria-expanded') === 'true';
                    trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    if (target) {
                        target.hidden = expanded;
                    }
                });
            });
        }
        const filteredRows = () => {
            const query = (searchInput.value || '').toLowerCase();
            return rows.filter((item) => studentName(item).toLowerCase().includes(query) || String(item.student_email || '').toLowerCase().includes(query) || String(item.student_id || '').toLowerCase().includes(query)).sort((a, b) => {
                const left = Number.parseFloat(a.score_ai ?? 0);
                const right = Number.parseFloat(b.score_ai ?? 0);
                return sortAscending ? left - right : right - left;
            });
        };
        const showMessage = (text, type = 'info') => {
            message.textContent = text;
            message.hidden = false;
        };
        renderResults(filteredRows());
        searchInput.addEventListener('input', () => renderResults(filteredRows()));
        sortButton.addEventListener('click', () => { sortAscending = !sortAscending; renderResults(filteredRows()); });
        button.addEventListener('click', async () => {
            button.disabled = true;
            button.innerHTML = '<i data-lucide="rotate-ccw"></i>Memproses...';
            window.refreshLucideIcons?.();
            showMessage('Workflow n8n sedang dipanggil.');
            try {
                const response = await fetch(<?php echo json_encode(route('assignments.grading.trigger', $assignment), 512) ?>, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({}),
                });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || 'Workflow n8n gagal.');
                rows = payload.results || [];
                renderResults(filteredRows());
                assignmentStatus.textContent = payload.assignment_status === 'completed' ? 'Selesai' : 'Sedang Dinilai';
                showMessage(`${payload.message} ${payload.saved_results ?? 0} hasil tersimpan.`);
            } catch (error) {
                showMessage(error.message, 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = '<i data-lucide="rotate-ccw"></i>Trigger Ulang';
                window.refreshLucideIcons?.();
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', [
    'title' => 'Hasil Grading - AutoGrade AI',
    'pageTitle' => 'Hasil Grading',
    'pageCaption' => 'Review skor, confidence, dan feedback.',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\PW-2\Projek_AI\resources\views/assignments/grading.blade.php ENDPATH**/ ?>