<?php
$hour = (int) date('G');
$greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
$statusLabel = static fn (?string $status): string => match ($status) {
    'completed' => 'Selesai',
    'grading' => 'Sedang dinilai',
    'failed' => 'Gagal',
    'ready' => 'Siap',
    default => ucfirst($status ?? 'Draft'),
};
?>
<section class="hero-panel">
    <p class="eyebrow">Human in the loop</p>
    <h2><?= e($greeting) ?>, <?= e(current_user()['name'] ?? 'Dosen') ?>.</h2>
    <p>Pantau tugas, review hasil AI, dan kirim feedback dari satu ruang kontrol.</p>
    <div class="actions">
        <a class="button primary" href="<?= e(route('assignments.create')) ?>">Tugas Baru</a>
        <a class="button secondary" href="<?= e(route('courses.index')) ?>">Lihat Kelas</a>
    </div>
</section>

<section class="metric-grid" aria-label="Ringkasan dashboard">
    <article class="metric-card"><span>Perlu review</span><strong><?= e($stats['review'] ?? 0) ?></strong></article>
    <article class="metric-card"><span>Kelas</span><strong><?= e($stats['courses'] ?? 0) ?></strong></article>
    <article class="metric-card"><span>Disetujui</span><strong><?= e($stats['approved'] ?? 0) ?></strong></article>
    <article class="metric-card"><span>Tugas</span><strong><?= e($stats['assignments'] ?? 0) ?></strong></article>
</section>

<section class="split-grid">
    <article class="panel">
        <header class="panel-header">
            <div>
                <p class="eyebrow">Assignment stream</p>
                <h2>Daftar tugas terbaru</h2>
            </div>
            <a class="button secondary" href="<?= e(route('assignments.create')) ?>">Buat Tugas</a>
        </header>

        <?php if ($recentAssignments): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tugas</th><th>Tanggal</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recentAssignments as $assignment): ?>
                        <tr>
                            <td><a href="<?= e(route('assignments.grading', (int) $assignment['id'])) ?>"><?= e($assignment['title']) ?></a></td>
                            <td><?= e(date('d M Y', strtotime((string) $assignment['created_at']))) ?></td>
                            <td><span class="badge <?= e($assignment['status']) ?>"><?= e($statusLabel($assignment['status'])) ?></span></td>
                            <td>
                                <form method="post" action="<?= e(route('assignments.destroy', (int) $assignment['id'])) ?>" data-confirm="Hapus assignment ini?">
                                    <?= csrf_field() ?><?= method_field('DELETE') ?>
                                    <button class="button danger slim" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Belum ada tugas</h3>
                <p>Mulai dengan membuat tugas dan mengunggah soal, rubrik, serta kunci jawaban.</p>
                <a class="button primary" href="<?= e(route('assignments.create')) ?>">Buat tugas pertama</a>
            </div>
        <?php endif; ?>
    </article>

    <aside class="panel">
        <p class="eyebrow">Distribusi nilai</p>
        <div class="grade-list">
            <?php foreach ($gradeDistribution as $grade => $count): ?>
                <div><span><?= e($grade) ?></span><meter min="0" max="<?= e(max(1, array_sum($gradeDistribution))) ?>" value="<?= e($count) ?>"></meter><strong><?= e($count) ?></strong></div>
            <?php endforeach; ?>
        </div>
    </aside>
</section>

<section class="panel">
    <header class="panel-header">
        <div>
            <p class="eyebrow">Kelas aktif</p>
            <h2>Ringkasan kelas</h2>
        </div>
    </header>
    <?php if ($classSummary): ?>
        <div class="course-grid">
            <?php foreach ($classSummary as $course): ?>
                <a class="course-card" href="<?= e(route('courses.show', $course['id'])) ?>">
                    <strong><?= e($course['name']) ?></strong>
                    <span><?= e($course['active_assignments']) ?> tugas lokal</span>
                    <span><?= e($course['students']) ?> mahasiswa tercatat</span>
                    <span>Rata-rata <?= e($course['average']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="muted">Belum ada ringkasan kelas dari tugas lokal.</p>
    <?php endif; ?>
</section>
