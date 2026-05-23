<?php if ($notice): ?><div class="alert warning" role="status"><?= e($notice) ?></div><?php endif; ?>

<form class="form-flow" method="post" action="<?= e(route('assignments.store')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <section class="hero-panel compact">
        <p class="eyebrow">Google Classroom assignment</p>
        <h2>Buat tugas yang siap dinilai sejak pertama dipublikasi.</h2>
        <p>Lengkapi instruksi, batas penilaian, dan tiga file konteks untuk workflow grading.</p>
    </section>

    <section class="panel">
        <h2>Informasi Tugas</h2>
        <div class="field-grid">
            <label class="field span-2">
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
                <span>Judul Tugas</span>
                <input name="title" type="text" value="<?= e(old('title')) ?>" maxlength="255" required>
                <?php if ($error = error_for('title')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
            </label>
            <label class="field span-2">
                <span>Deskripsi</span>
                <textarea name="description" rows="5" maxlength="2000"><?= e(old('description')) ?></textarea>
                <?php if ($error = error_for('description')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
            </label>
        </div>
    </section>

    <section class="panel">
        <h2>Pengaturan Penilaian</h2>
        <div class="field-grid">
            <label class="field">
                <span>Skor Maksimum</span>
                <input name="max_score" type="number" min="0" max="999.99" step="0.01" value="<?= e(old('max_score', 100)) ?>" required>
                <?php if ($error = error_for('max_score')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
            </label>
            <label class="field">
                <span>Grade Mode</span>
                <select name="grade_mode">
                    <?php foreach (['draft' => 'Draft', 'final' => 'Final', 'none' => 'Tanpa Nilai'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= old('grade_mode', 'draft') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span>Minimum Panjang Jawaban</span>
                <input name="min_answer_length" type="number" min="0" max="10000" step="1" value="<?= e(old('min_answer_length', 120)) ?>" required>
                <?php if ($error = error_for('min_answer_length')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
            </label>
            <label class="field">
                <span>Tenggat Waktu</span>
                <input name="due_date" type="datetime-local" value="<?= e(old('due_date')) ?>">
                <?php if ($error = error_for('due_date')): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
            </label>
        </div>
        <label class="check-row"><input type="checkbox" name="close_on_due" value="1" <?= old('close_on_due') ? 'checked' : '' ?>> Tutup saat tenggat habis</label>
    </section>

    <section class="panel">
        <h2>Unggah Berkas</h2>
        <div class="upload-grid">
            <?php foreach ([
                'question_file' => 'File Soal',
                'rubric_file' => 'File Rubrik',
                'answer_key_file' => 'File Kunci Jawaban',
            ] as $field => $label): ?>
                <label class="upload-box">
                    <span><?= e($label) ?></span>
                    <input id="<?= e($field) ?>" name="<?= e($field) ?>" type="file" data-file-input required>
                    <small data-file-name="<?= e($field) ?>">PDF, DOCX, TXT, PNG, JPG</small>
                    <?php if ($error = error_for($field)): ?><small class="field-error"><?= e($error) ?></small><?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <h2>Opsi Otomatis</h2>
        <label class="check-row"><input type="checkbox" name="auto_approval" value="1" <?= old('auto_approval') ? 'checked' : '' ?>> Auto approval untuk confidence HIGH</label>
        <label class="check-row"><input type="checkbox" name="auto_email" value="1" <?= old('auto_email') ? 'checked' : '' ?>> Kirim email feedback otomatis</label>
    </section>

    <footer class="form-actions">
        <a class="button secondary" href="<?= e(route('dashboard')) ?>">Batal</a>
        <button class="button primary" type="submit">Simpan & Publikasikan</button>
    </footer>
</form>
