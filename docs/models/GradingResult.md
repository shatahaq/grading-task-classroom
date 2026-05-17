# GradingResult Model

`App\Models\GradingResult` adalah hasil penilaian AI per mahasiswa.

## Field utama
- `student_id`: ID mahasiswa dari Classroom atau sistem eksternal.
- `student_email`: email mahasiswa untuk feedback.
- `score_ai`: skor hasil LLM.
- `confidence`: `LOW`, `MEDIUM`, atau `HIGH`.
- `needs_review`: flag review manual dari LLM/validator.
- `status`: `APPROVED`, `REVIEW`, atau `FAILED`.
- `extraction_status`: status ekstraksi dokumen.
- `extracted_text_length`: panjang teks jawaban bersih.
- `output_json_valid`: validasi JSON output LLM.
- `reason`: alasan/ringkasan evaluasi.
- `feedback_email`: draft email feedback.
- `rubric_breakdown`: breakdown rubrik dalam JSON.
- `raw_llm_output`: payload mentah dari n8n/LLM.
- `email_sent`: status pengiriman email.

## Relasi
- `assignment()`: hasil terkait satu assignment.
