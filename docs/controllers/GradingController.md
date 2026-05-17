# GradingController

`GradingController` menghubungkan AutoGrade AI dengan workflow n8n.

## show()
Menampilkan halaman grading untuk assignment milik user login beserta hasil yang sudah tersimpan.

## trigger()
1. Memastikan assignment dimiliki user login.
2. Mengubah status assignment menjadi `grading`.
3. Membuat `temporary_token` terenkripsi berumur 15 menit.
4. Mengambil access token Google aktif milik dosen melalui `GoogleWorkspaceService`.
5. Mengirim `assignment_id`, `temporary_token`, access token Google sementara, metadata assignment, file ID Drive, dan konfigurasi auto-approval ke `N8N_WEBHOOK_URL`.
6. Menambahkan header `X-N8N-API-Key` dari `N8N_API_KEY`.
7. Membaca JSON response n8n dan menyimpan `results` ke tabel `grading_results`.
8. Mencatat audit log sukses atau gagal.

Access token yang dikirim ke n8n hanya token sementara. Refresh token tetap tersimpan terenkripsi di Laravel dan tidak pernah dikirim ke n8n.

## Auto-Approval
Status `APPROVED` hanya diberikan jika:
- `auto_approval` aktif.
- `confidence` adalah `HIGH`.
- `needs_review` bernilai `false`.
- `score` valid dan berada dalam rentang `0..max_score`.
- `extraction_status` adalah `SUCCESS`.
- `extracted_text_length >= min_answer_length`.
- `output_json_valid` bernilai `true`.

Jika ekstraksi gagal, status menjadi `FAILED`. Kondisi lain menjadi `REVIEW`.

## Format response n8n
Controller mengharapkan JSON:

```json
{
  "results": [
    {
      "student_id": "student-001",
      "score_ai": 87.5,
      "confidence": "HIGH",
      "needs_review": false,
      "status": "APPROVED",
      "extraction_status": "SUCCESS",
      "extracted_text_length": 1200,
      "output_json_valid": true,
      "reason": "Jawaban sesuai rubrik.",
      "feedback_email": "Draft feedback untuk mahasiswa.",
      "rubric_breakdown": [],
      "email_sent": false
    }
  ]
}
```
