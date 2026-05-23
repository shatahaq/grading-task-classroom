<?php
$statusLabel = static fn (?string $status): string => match ($status) {
    'completed' => 'Selesai',
    'grading' => 'Sedang dinilai',
    'failed' => 'Gagal',
    'ready' => 'Siap',
    default => ucfirst($status ?? 'Draft'),
};
?>
<?php if ($notice): ?><div class="alert warning" role="status"><?= e($notice) ?></div><?php endif; ?>

<section class="hero-panel compact">
    <p class="eyebrow">Course ID <?= e($course['id']) ?></p>
    <h2><?= e($course['name']) ?></h2>
    <p><?= e($course['section'] ?? '-') ?> · <?= e($course['students'] ?? '-') ?> mahasiswa</p>
    <div class="actions">
        <a class="button primary" href="<?= e(route('assignments.create')) ?>?course_id=<?= e(rawurlencode((string) $course['id'])) ?>">Buat Tugas</a>
        <?php if (! empty($course['alternateLink'])): ?><a class="button secondary" href="<?= e($course['alternateLink']) ?>" target="_blank" rel="noopener">Buka Classroom</a><?php endif; ?>
    </div>
</section>

<section class="split-grid">
    <article class="panel">
        <header class="panel-header">
            <div>
                <p class="eyebrow">AutoGrade lokal</p>
                <h2>Tugas yang dibuat dari aplikasi</h2>
            </div>
        </header>
        <?php if ($assignments): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tugas</th><th>Status</th><th>Hasil</th></tr></thead>
                    <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><a href="<?= e(route('assignments.grading', (int) $assignment['id'])) ?>"><?= e($assignment['title']) ?></a></td>
                            <td><span class="badge <?= e($assignment['status']) ?>"><?= e($statusLabel($assignment['status'])) ?></span></td>
                            <td><?= e($assignment['grading_results_count'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="muted">Belum ada tugas lokal untuk kelas ini.</p>
        <?php endif; ?>
    </article>

    <aside class="panel">
        <p class="eyebrow">Classroom</p>
        <h2>Tugas dari Google</h2>
        <?php if (is_array($courseWorks) && $courseWorks): ?>
            <div class="stack-list">
                <?php foreach (array_slice($courseWorks, 0, 8) as $work): ?>
                    <a href="<?= e($work['alternateLink'] ?? '#') ?>" target="_blank" rel="noopener">
                        <strong><?= e($work['title']) ?></strong>
                        <span><?= e($work['due'] ?? '-') ?> · <?= e($work['status'] ?? '-') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">Coursework belum tersedia.</p>
        <?php endif; ?>
    </aside>
</section>
