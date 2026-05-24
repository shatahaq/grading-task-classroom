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
<section class="welcome-section">
    <h2>Halo, <?= e(current_user()['name'] ?? 'Dosen') ?>!</h2>
    <p>Selamat datang kembali di AutoGrade Workspace.</p>
</section>

<section class="metric-grid" aria-label="Ringkasan dashboard">
    <article class="metric-card"><strong><?= e($stats['review'] ?? 0) ?></strong><span>Perlu Review</span></article>
    <article class="metric-card"><strong><?= e($stats['courses'] ?? 0) ?></strong><span>Kelas</span></article>
    <article class="metric-card"><strong><?= e($stats['approved'] ?? 0) ?></strong><span>Disetujui</span></article>
    <article class="metric-card"><strong><?= e($stats['assignments'] ?? 0) ?></strong><span>Tugas</span></article>
</section>

<section class="panel">
    <header class="panel-header">
        <div>
            <h2>Daftar Tugas Terbaru</h2>
        </div>
    </header>

    <?php if ($recentAssignments): ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tugas</th><th>Tanggal</th><th>Status</th><th>Kelas</th></tr></thead>
                <tbody>
                <?php foreach ($recentAssignments as $assignment): ?>
                    <tr>
                        <td><a href="<?= e(route('assignments.grading', (int) $assignment['id'])) ?>"><?= e($assignment['title']) ?></a></td>
                        <td><?= e(date('d M Y', strtotime((string) $assignment['created_at']))) ?></td>
                        <td><span class="badge <?= e($assignment['status']) ?>"><?= e($statusLabel($assignment['status'])) ?></span></td>
                        <td><?= e($assignment['course_name'] ?? $assignment['course_id']) ?></td>
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
</section>
