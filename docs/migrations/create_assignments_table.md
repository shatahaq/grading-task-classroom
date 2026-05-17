# Migration: create_assignments_table

Migration ini membuat tabel `assignments` sebagai konfigurasi tugas AI grading.

## Struktur
- `teacher_id`: foreign key ke `users`.
- `course_id` dan `coursework_id`: referensi Google Classroom.
- `title`, `max_score`, dan `file_id`: metadata tugas.
- `auto_approval`, `auto_email`: flag otomatisasi.
- `grade_mode`: `none`, `draft`, atau `final`.
- `status`: lifecycle tugas dari `draft` sampai `completed` atau `failed`.

Index `teacher_id` dan `course_id` disediakan untuk dashboard dan daftar kelas.
