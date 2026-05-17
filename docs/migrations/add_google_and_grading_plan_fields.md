# Migration: add_google_and_grading_plan_fields

Migration ini memperluas schema MVP agar mengikuti plan AutoGrade AI terbaru.

## Assignments
Kolom baru:
- `description`: deskripsi yang dikirim ke Google Classroom.
- `classroom_link`: link CourseWork di Google Classroom.
- `drive_folder_id`: folder Drive assignment.
- `question_drive_file_id`, `rubric_drive_file_id`, `answer_key_drive_file_id`: file Google Drive untuk soal, rubrik, dan kunci.
- `question_local_path`, `rubric_local_path`, `answer_key_local_path`: backup file lokal.
- `min_answer_length`: ambang minimum teks hasil ekstraksi untuk auto-approval.

## Grading Results
Kolom baru:
- `student_email`: alamat email mahasiswa untuk feedback.
- `needs_review`: flag dari LLM atau validator.
- `extraction_status`: `PENDING`, `SUCCESS`, `PARTIAL`, atau `FAILED`.
- `extracted_text_length`: panjang teks jawaban hasil ekstraksi.
- `output_json_valid`: hasil validasi JSON LLM.
- `rubric_breakdown`: rincian skor per kriteria.
- `raw_llm_output`: payload mentah dari workflow n8n.

## OAuth Tokens
Kolom baru:
- `token_type`
- `last_refreshed_at`
