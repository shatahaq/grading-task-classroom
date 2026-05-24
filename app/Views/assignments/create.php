<?php if ($notice): ?><div class="alert warning" role="status"><?= e($notice) ?></div><?php endif; ?>
<?php
$dueDateInput = (string) old('due_date');
$dueDateValue = '';
$dueTimeValue = '';

if ($dueDateInput !== '') {
    $dueTimestamp = strtotime($dueDateInput);

    if ($dueTimestamp !== false) {
        $dueDateValue = date('Y-m-d', $dueTimestamp);
        $dueTimeValue = date('H:i', $dueTimestamp);
    }
}

$dueDateHidden = $dueDateValue !== '' ? $dueDateValue . 'T' . ($dueTimeValue ?: '23:59') : '';
?>

<form class="form-flow" method="post" action="<?= e(route('assignments.store')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <section class="task-form-panel">
        <div class="task-form-header">
            <div>
                <p class="technical-label">Google Classroom assignment</p>
                <h2>Buat Tugas Baru</h2>
            </div>
            <a class="button secondary slim" href="<?= e(route('dashboard')) ?>">Batal</a>
        </div>

        <div class="task-section">
            <h3>Informasi Tugas</h3>
            <div class="field-grid">
                <label class="field">
                    <span>Judul Tugas</span>
                    <input name="title" type="text" value="<?= e(old('title')) ?>" maxlength="255" placeholder="Masukkan judul tugas..." required>
                    <?php if ($error = error_for('title')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                </label>

                <label class="field">
                    <span>Kelas</span>
                    <select name="course_id" required>
                        <?php if ($courses): ?>
                            <?php foreach ($courses as $course): ?>
                                <?php $selected = old('course_id', $selectedCourse) === $course['id']; ?>
                                <option value="<?= e($course['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= e($course['name']) ?> - <?= e($course['section'] ?? '-') ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Belum ada kelas tersedia</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($error = error_for('course_id')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                </label>

                <label class="field span-2">
                    <span>Deskripsi</span>
                    <textarea name="description" rows="4" maxlength="2000" placeholder="Instruksi singkat untuk siswa..."><?= e(old('description')) ?></textarea>
                    <?php if ($error = error_for('description')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                </label>
            </div>
        </div>

        <div class="task-section">
            <h3>Unggah Berkas</h3>
            <div class="upload-grid">
                <?php foreach ([
                    'question_file' => ['label' => 'Soal', 'desc' => 'PDF, DOCX, TXT, PNG, JPG'],
                    'rubric_file' => ['label' => 'Rubrik', 'desc' => 'PDF, DOCX, TXT, PNG, JPG'],
                    'answer_key_file' => ['label' => 'Kunci Jawaban', 'desc' => 'PDF, DOCX, TXT, PNG, JPG'],
                ] as $field => $info): ?>
                    <label class="upload-box">
                        <div class="upload-icon">&#8679;</div>
                        <span><?= e($info['label']) ?></span>
                        <input id="<?= e($field) ?>" name="<?= e($field) ?>" type="file" data-file-input required>
                        <small data-file-name="<?= e($field) ?>"><?= e($info['desc']) ?></small>
                        <?php if ($error = error_for($field)): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="task-section">
            <h3>Pengaturan Penilaian</h3>
            <div class="settings-grid">
                <label class="field setting-card">
                    <span>Skor Maksimum</span>
                    <input name="max_score" type="number" min="0" max="999.99" step="0.01" value="<?= e(old('max_score', 100)) ?>" required>
                    <?php if ($error = error_for('max_score')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                </label>

                <label class="field setting-card">
                    <span>Grade Mode</span>
                    <select name="grade_mode">
                        <?php foreach (['draft' => 'Draft', 'final' => 'Final', 'none' => 'Tanpa Nilai'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= old('grade_mode', 'draft') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($error = error_for('grade_mode')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                </label>

                <label class="field setting-card">
                    <span>Minimum Panjang Jawaban</span>
                    <input name="min_answer_length" type="number" min="0" max="10000" step="1" value="<?= e(old('min_answer_length', 120)) ?>" required>
                    <?php if ($error = error_for('min_answer_length')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                </label>

                <div class="field setting-card due-date-card" data-due-date-control>
                    <span>Tenggat Waktu</span>
                    <input name="due_date" type="hidden" value="<?= e($dueDateHidden) ?>" data-due-datetime>
                    <div class="due-date-inputs">
                        <label>
                            <small>Tanggal</small>
                            <input type="date" value="<?= e($dueDateValue) ?>" data-due-date>
                        </label>
                        <label>
                            <small>Jam</small>
                            <input type="time" value="<?= e($dueTimeValue) ?>" data-due-time>
                        </label>
                    </div>
                    <div class="due-date-presets" aria-label="Preset tenggat waktu">
                        <button type="button" data-due-preset="tomorrow">Besok</button>
                        <button type="button" data-due-preset="three-days">3 Hari</button>
                        <button type="button" data-due-preset="week">7 Hari</button>
                        <button type="button" data-due-preset="clear">Kosongkan</button>
                    </div>
                    <?php if ($error = error_for('due_date')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                </div>
            </div>

            <div class="toggle-grid">
                <label class="check-row">
                    <input type="checkbox" name="close_on_due" value="1" <?= old('close_on_due') ? 'checked' : '' ?>>
                    <span>Tutup saat tenggat habis</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" name="auto_approval" value="1" <?= old('auto_approval') ? 'checked' : '' ?>>
                    <span>Auto approval confidence HIGH</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" name="auto_email" value="1" <?= old('auto_email') ? 'checked' : '' ?>>
                    <span>Kirim email feedback otomatis</span>
                </label>
            </div>
        </div>

        <div class="form-actions task-actions">
            <a class="button secondary" href="<?= e(route('dashboard')) ?>">Batal</a>
            <button class="button primary" type="submit">Simpan & Publikasikan</button>
        </div>
    </section>

</form>
