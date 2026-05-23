<?php
$statusLabel = static fn (?string $status): string => match ($status) {
    'completed' => 'Selesai',
    'grading' => 'Sedang dinilai',
    'failed' => 'Gagal',
    'ready' => 'Siap',
    default => ucfirst($status ?? 'Draft'),
};
?>
<section class="hero-panel compact">
    <p class="eyebrow">Assignment #<?= e($assignment['id']) ?></p>
    <h2><?= e($assignment['title']) ?></h2>
    <p><?= e($assignment['course_id']) ?> · skor maksimum <?= e($assignment['max_score']) ?> · <span class="badge <?= e($assignment['status']) ?>"><?= e($statusLabel($assignment['status'])) ?></span></p>
    <div class="actions">
        <?php if (! empty($assignment['classroom_link'])): ?><a class="button secondary" href="<?= e($assignment['classroom_link']) ?>" target="_blank" rel="noopener">Buka Classroom</a><?php endif; ?>
        <button class="button primary" type="button" data-trigger-grading data-url="<?= e(route('assignments.grading.trigger', (int) $assignment['id'])) ?>">Jalankan Grading</button>
    </div>
    <p class="muted" data-grading-message></p>
</section>

<section class="metric-grid">
    <article class="metric-card"><span>Mode</span><strong><?= e($assignment['grade_mode']) ?></strong></article>
    <article class="metric-card"><span>Auto approval</span><strong><?= (int) $assignment['auto_approval'] ? 'Aktif' : 'Nonaktif' ?></strong></article>
    <article class="metric-card"><span>Auto email</span><strong><?= (int) $assignment['auto_email'] ? 'Aktif' : 'Nonaktif' ?></strong></article>
    <article class="metric-card"><span>Minimum jawaban</span><strong><?= e($assignment['min_answer_length']) ?></strong></article>
</section>

<section class="panel">
    <header class="panel-header">
        <div>
            <p class="eyebrow">Hasil AI</p>
            <h2>Daftar hasil grading</h2>
        </div>
        <form method="post" action="<?= e(route('assignments.destroy', (int) $assignment['id'])) ?>" data-confirm="Hapus assignment dan semua hasilnya?">
            <?= csrf_field() ?><?= method_field('DELETE') ?>
            <button class="button danger" type="submit">Hapus Assignment</button>
        </form>
    </header>

    <?php if ($results): ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Mahasiswa</th><th>Skor</th><th>Confidence</th><th>Status</th><th>Alasan</th></tr></thead>
                <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td>
                            <strong><?= e($result['student_email'] ?: $result['student_id']) ?></strong>
                            <small><?= e($result['student_id']) ?></small>
                        </td>
                        <td><?= e($result['score_ai'] ?? '-') ?></td>
                        <td><?= e($result['confidence']) ?></td>
                        <td><span class="badge <?= strtolower((string) $result['status']) ?>"><?= e($result['status']) ?></span></td>
                        <td><?= e(str_limit((string) ($result['reason'] ?? '-'), 140)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>Belum ada hasil grading</h3>
            <p>Jalankan workflow n8n untuk menarik submission, menilai jawaban, dan menyimpan hasil review.</p>
        </div>
    <?php endif; ?>
</section>
