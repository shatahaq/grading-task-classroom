<?php if ($notice): ?><div class="alert warning" role="status"><?= e($notice) ?></div><?php endif; ?>

<section class="panel">
    <header class="panel-header">
        <div>
            <p class="eyebrow">Google Classroom</p>
            <h2>Kelas tersinkron</h2>
        </div>
        <a class="button secondary" href="<?= e(route('assignments.create')) ?>">Tugas Baru</a>
    </header>

    <?php if ($courses): ?>
        <div class="course-grid">
            <?php foreach ($courses as $course): ?>
                <a class="course-card" href="<?= e(route('courses.show', $course['id'])) ?>">
                    <strong><?= e($course['name']) ?></strong>
                    <span><?= e($course['section'] ?? '-') ?></span>
                    <span><?= e($course['students'] ?? '-') ?> mahasiswa</span>
                    <span><?= e($course['classroom_assignment_count'] ?? '-') ?> tugas Classroom, <?= e($course['local_assignment_count'] ?? 0) ?> lokal</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>Belum ada kelas tersedia</h3>
            <p>Pastikan akun Google sudah memiliki akses dosen di Google Classroom.</p>
        </div>
    <?php endif; ?>
</section>
