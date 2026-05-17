# AssignmentController

`AssignmentController` menangani pembuatan assignment grading.

## create()
Menampilkan form `assignments/create.blade.php` dengan daftar kelas dari Google Classroom. Jika API gagal, form tetap tampil dengan fallback dummy dan pesan peringatan.

## store()
1. Memvalidasi judul, course, deskripsi, skor maksimum, file soal, file rubrik, file kunci, `grade_mode`, dan `min_answer_length`.
2. Menyimpan backup file ke disk `public`.
3. Mengunggah file soal/rubrik/kunci ke Google Drive lewat `GoogleWorkspaceService`.
4. Membuat CourseWork di Google Classroom.
5. Membuat record `assignments` dengan `coursework_id`, `classroom_link`, dan file ID Drive.
6. Mencatat `audit_logs`.
7. Mengarahkan user ke halaman grading assignment.

Flag `auto_approval`, `auto_email`, dan `grade_mode` disimpan sebagai konfigurasi workflow n8n.
