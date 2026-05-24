<?php if ($notice): ?><div class="alert warning" role="status"><?= e($notice) ?></div><?php endif; ?>

<?php
$totalCourses = count($courses);
$totalStudents = array_sum(array_map(static fn (array $course): int => is_numeric($course['students'] ?? null) ? (int) $course['students'] : 0, $courses));
$totalLocalAssignments = array_sum(array_map(static fn (array $course): int => (int) ($course['local_assignment_count'] ?? 0), $courses));
$totalClassroomAssignments = array_sum(array_map(static fn (array $course): int => is_numeric($course['classroom_assignment_count'] ?? null) ? (int) $course['classroom_assignment_count'] : 0, $courses));
?>

<section class="course-overview" aria-label="Ringkasan kelas">
    <article class="metric-card"><strong><?= e($totalCourses) ?></strong><span>Kelas aktif</span></article>
    <article class="metric-card"><strong><?= e($totalStudents) ?></strong><span>Mahasiswa</span></article>
    <article class="metric-card"><strong><?= e($totalLocalAssignments) ?></strong><span>Tugas lokal</span></article>
    <article class="metric-card"><strong><?= e($totalClassroomAssignments) ?></strong><span>Tugas Classroom</span></article>
</section>

<section class="course-board">
    <header class="course-board-header">
        <div>
            <h2>Daftar Kelas</h2>
        </div>

        <div class="course-tools">
            <label class="course-search">
                <span>Cari kelas</span>
                <input type="search" placeholder="Nama kelas..." data-course-search>
            </label>
            <button class="sync-button" type="button" onclick="window.location.reload()">
                Sync
            </button>
        </div>
    </header>

    <?php if ($courses): ?>
        <div class="course-grid enhanced-course-grid" data-course-grid>
            <?php foreach ($courses as $course): ?>
                <?php
                $students = $course['students'] ?? null;
                $localAssignments = (int) ($course['local_assignment_count'] ?? 0);
                $classroomAssignments = $course['classroom_assignment_count'] ?? null;
                $lastGraded = ! empty($course['last_graded']) ? date('d M Y', strtotime((string) $course['last_graded'])) : '-';
                $searchText = strtolower(trim((string) $course['name'] . ' ' . ($course['section'] ?? '') . ' ' . $course['id']));
                ?>
                <a class="course-card enhanced-course-card" href="<?= e(route('courses.show', $course['id'])) ?>" data-course-card data-course-search-text="<?= e($searchText) ?>">
                    <span class="course-card-topline">
                        <span class="course-initial"><?= e(mb_strtoupper(mb_substr((string) $course['name'], 0, 1))) ?></span>
                        <span class="sync-badge">Sinkron</span>
                    </span>

                    <span class="course-title-block">
                        <strong><?= e($course['name']) ?></strong>
                        <span><?= e($course['section'] ?? 'Tanpa section') ?></span>
                    </span>

                    <span class="course-stat-strip">
                        <span><strong><?= e($students ?? '-') ?></strong><small>Mahasiswa</small></span>
                        <span><strong><?= e($localAssignments) ?></strong><small>Lokal</small></span>
                        <span><strong><?= e($classroomAssignments ?? '-') ?></strong><small>Classroom</small></span>
                    </span>

                    <div class="course-card-footer">
                        <span>Update <?= e($lastGraded) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="empty-state course-search-empty" data-course-search-empty hidden>
            <h3>Kelas tidak ditemukan</h3>
            <p>Tidak ada kelas sesuai pencarian.</p>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>Belum ada kelas tersedia</h3>
            <p>Pastikan akun Google sudah memiliki akses dosen di Google Classroom.</p>
        </div>
    <?php endif; ?>
</section>
