# Migration: create_grading_results_table

Migration ini membuat tabel `grading_results`.

## Struktur
- `assignment_id`: foreign key ke `assignments`.
- `student_id`: identitas mahasiswa.
- `score_ai`: skor dari LLM.
- `confidence`: `LOW`, `MEDIUM`, atau `HIGH`.
- `status`: `APPROVED`, `REVIEW`, atau `FAILED`.
- `reason`: alasan penilaian.
- `feedback_email`: draft feedback.
- `email_sent`: status pengiriman email.

Kombinasi `assignment_id` dan `student_id` dibuat unik agar hasil webhook n8n bisa di-upsert.
