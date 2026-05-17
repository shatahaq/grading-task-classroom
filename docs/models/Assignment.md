# Assignment Model

`App\Models\Assignment` menyimpan konfigurasi tugas yang akan dinilai otomatis.

## Field utama
- `teacher_id`: pemilik tugas.
- `course_id` dan `coursework_id`: referensi Google Classroom.
- `title`, `description`, `max_score`: metadata tugas.
- `classroom_link`: tautan tugas Google Classroom.
- `drive_folder_id`: folder Google Drive assignment.
- `question_drive_file_id`, `rubric_drive_file_id`, `answer_key_drive_file_id`: file utama di Google Drive.
- `question_local_path`, `rubric_local_path`, `answer_key_local_path`: backup local storage.
- `auto_approval`, `auto_email`: kontrol otomatisasi setelah AI grading.
- `grade_mode`: `none`, `draft`, atau `final`.
- `min_answer_length`: batas minimum teks hasil ekstraksi untuk auto-approval.
- `status`: `draft`, `ready`, `grading`, `completed`, atau `failed`.

## Relasi
- `teacher()`: user pengajar.
- `gradingResults()`: hasil penilaian AI untuk tugas ini.
