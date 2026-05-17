# Setup n8n untuk AutoGrade AI

Panduan ini menyiapkan n8n sebagai automation engine internal untuk AutoGrade AI.

## 1. Konsep API Key

`N8N_API_KEY` di project ini adalah **shared secret internal** antara Laravel dan workflow n8n.

Ini bukan n8n Personal API Key.

Laravel mengirim header:

```http
X-N8N-API-Key: <nilai N8N_API_KEY>
```

n8n memvalidasi header itu sebelum memproses grading.

Di `.env` Laravel sudah disiapkan:

```env
N8N_WEBHOOK_URL=http://127.0.0.1:5678/webhook/autograde-ai
N8N_API_KEY=<secret-internal>
```

Jalankan setelah mengubah `.env`:

```powershell
& 'C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe' artisan config:clear
```

## 2. Jalankan n8n Lokal

Jika n8n belum terinstall, jalankan lewat `npx` dari root project:

```powershell
$env:N8N_API_KEY = ((Get-Content .env | Select-String '^N8N_API_KEY=').Line -replace '^N8N_API_KEY=', '')
npx n8n start
```

Buka:

```text
http://127.0.0.1:5678
```

Catatan:
- Jika workflow aktif, gunakan URL `/webhook/autograde-ai`.
- Jika menjalankan workflow manual dari editor n8n, URL test biasanya `/webhook-test/autograde-ai`; ubah sementara `N8N_WEBHOOK_URL` di Laravel jika ingin test mode.
- Untuk setup lokal termudah, jalankan n8n via `npx` di host Windows, bukan Docker, supaya n8n bisa mengakses Laravel di `http://127.0.0.1:8000`.

## 3. Import Workflow Template

File template tersedia di:

```text
n8n/autograde-ai-openai-workflow.template.json
```

Langkah import:

1. Buka n8n.
2. Masuk ke **Workflows**.
3. Pilih **Import from File**.
4. Pilih file template tersebut.
5. Save workflow.
6. Buka node **Google Gemini Chat Model**, lalu pilih atau buat credential Google Gemini.
7. Untuk n8n Cloud, buat variable `N8N_API_KEY` berisi nilai yang sama dengan Laravel `.env`. Jika plan n8n tidak punya Variables, buka node **Validate Laravel Secret** dan ganti placeholder `PASTE_LARAVEL_N8N_API_KEY_HERE`.
8. Aktifkan workflow.

## 4. Alur Node

Workflow template berisi node:

1. **Webhook - AutoGrade AI**
   - Method: `POST`
   - Path: `autograde-ai`
   - Response mode: `Respond to Webhook Node`

2. **Validate Laravel Secret**
   - Code Node.
   - Membandingkan header `X-N8N-API-Key` dengan `$env.N8N_API_KEY`.

3. **Exchange Google Token**
   - Code Node.
   - Membaca access token Google sementara yang dikirim Laravel di payload awal.
   - Tidak melakukan callback HTTP ke Laravel, sehingga aman untuk testing lokal via ngrok dan `php artisan serve`.
   - Refresh token tidak pernah dikirim ke n8n.

4. **Get Classroom Submissions**
   - HTTP Request ke Google Classroom API memakai access token sementara.
   - Mengambil submission mahasiswa berdasarkan `course_id` dan `coursework_id`.

5. **Build Grading Items**
   - Code Node.
   - Mengubah submission menjadi item grading.
   - Saat ini masih punya placeholder ekstraksi file Drive.

6. **Keep Submitted Work**
   - Code Node.
   - Mengabaikan submission kosong yang masih `CREATED`/belum punya jawaban.
   - Hanya meneruskan submission yang punya jawaban teks atau attachment.

7. **Get Student Profile**
   - HTTP Request ke Google Classroom user profile.
   - Mengambil `emailAddress` mahasiswa untuk kebutuhan feedback email.

8. **Attach Student Profile**
   - Code Node.
   - Menggabungkan data submission dengan nama/email mahasiswa.

9. **Extract Student Answer Text**
   - Code Node.
   - Membaca teks jawaban dari short answer atau attachment Drive.
   - Untuk testing mendukung TXT, PHP, MD, JSON, JS, HTML, CSS, XML, dan Google Docs.
   - PDF/DOCX masih membutuhkan node ekstraksi/OCR tambahan.

10. **Extract Rubric and Answer Key Text**
   - Code Node.
   - Membaca file rubrik dan kunci jawaban dari Drive.
   - Untuk testing mendukung TXT, PHP, MD, JSON, JS, HTML, CSS, XML, dan Google Docs.

11. **AI Grading Agent**
   - AI Agent n8n untuk menilai jawaban mahasiswa.
   - Terhubung ke **Google Gemini Chat Model**.
   - Meminta output JSON mentah sesuai schema AutoGrade AI.
   - Tidak memakai Structured Output Parser atau Think Tool agar kompatibel dengan Gemini.

12. **Normalize Agent Output**
   - Code Node.
   - Memastikan output AI Agent menjadi JSON yang cocok untuk Laravel.

13. **Apply AutoGrade Decision**
   - Menambahkan status `APPROVED`, `REVIEW`, atau `FAILED` berdasarkan rule AutoGrade AI.

14. **Send Feedback Email - Gmail API**
   - Code Node.
   - Mengirim feedback email lewat Gmail API memakai access token Google dosen dari Laravel.
   - Email hanya dikirim jika status `APPROVED`, `auto_email` aktif, email mahasiswa tersedia, dan `feedback_email` tidak kosong.

15. **Aggregate Results**
   - Menggabungkan semua hasil grading ke array `results`.

16. **Respond to Laravel**
   - Mengembalikan JSON ke Laravel.

## 5. Node AI Agent

Template memakai AI Agent agar prompt dan model mudah diatur dari UI n8n. Output JSON tidak dipaksa lewat Structured Output Parser karena Gemini dapat menolak schema function-calling kompleks yang berisi `$ref`.

Konfigurasi penting:

- **AI Grading Agent**
  - Prompt Type: `Define`
  - Require Output Parser: nonaktif
  - System Message: instruksi penilaian berbasis rubrik dan larangan mengarang data file yang belum diekstrak.
- **Google Gemini Chat Model**
  - Credential: Google Gemini API account dari n8n.
  - Model default template: `models/gemini-2.5-flash`.
  - Jika ingin memakai model lain seperti Gemini Pro Preview, cukup ganti model di node ini.
- **Normalize Agent Output**
  - Mem-parse JSON dari AI Agent.
  - Jika model mengembalikan output non-JSON, hasil akan ditandai `output_json_valid: false`.

Output akhir tetap menggunakan schema:

```json
{
  "student_id": "string",
  "student_email": "string",
  "score": 85,
  "confidence": "LOW|MEDIUM|HIGH",
  "needs_review": false,
  "extraction_status": "SUCCESS|PARTIAL|FAILED",
  "extracted_text_length": 1200,
  "output_json_valid": true,
  "reason": "string",
  "feedback_email": "string",
  "rubric_breakdown": [],
  "email_sent": false
}
```

Laravel akan melakukan auto-approval berdasarkan field tersebut.

## 6. Node Gmail API

Template tidak memakai credential Gmail terpisah di n8n. Node **Send Feedback Email - Gmail API** memakai access token Google sementara dari Laravel:

```http
POST https://gmail.googleapis.com/gmail/v1/users/me/messages/send
Authorization: Bearer <access_token_dosen>
```

Keuntungan pendekatan ini:

- email dikirim dari akun Google dosen yang login di AutoGrade AI,
- refresh token tetap hanya tersimpan terenkripsi di Laravel,
- n8n hanya menerima access token sementara.

Pastikan scope ini aktif di konfigurasi Google OAuth Laravel:

```text
https://www.googleapis.com/auth/gmail.send
https://www.googleapis.com/auth/drive.readonly
```

Jika user sudah login sebelum scope Gmail/Drive readonly aktif, lakukan **Disconnect Google** lalu login ulang agar Google meminta izin baru.

## 7. Ekstraksi File Drive

Template saat ini belum melakukan ekstraksi PDF/DOCX penuh. Untuk produksi, tambahkan node sebelum **AI Grading Agent**:

- Google Drive download/export attachment.
- PDF text extraction atau OCR.
- DOCX to text/Markdown converter.
- Normalisasi teks:
  - hapus header/footer,
  - hapus nomor halaman,
  - normalisasi whitespace,
  - pisahkan per soal jika perlu.

Output yang harus masuk ke node AI:

```json
{
  "student_id": "student-001",
  "student_email": "student@example.com",
  "answer_text": "jawaban mahasiswa",
  "rubric_text": "rubrik",
  "answer_key_text": "kunci jawaban",
  "max_score": 100,
  "extraction_status": "SUCCESS",
  "extracted_text_length": 1200
}
```

## 8. Test dari Laravel

Pastikan:

1. Laravel jalan:

```text
http://127.0.0.1:8000
```

2. n8n jalan:

```text
http://127.0.0.1:5678
```

3. Workflow n8n aktif.
4. `N8N_WEBHOOK_URL` di Laravel mengarah ke:

```text
http://127.0.0.1:5678/webhook/autograde-ai
```

5. Di halaman assignment Laravel, klik **Mulai AI Grading**.

Jika n8n memberi response non-2xx, Laravel akan mengubah status assignment menjadi `failed` dan detail singkat masuk ke `audit_logs`.
