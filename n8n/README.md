# AutoGrade AI n8n Setup

Folder ini berisi template workflow n8n untuk AutoGrade AI.

## Jenis API key

`N8N_API_KEY` di Laravel adalah shared secret internal antara Laravel dan workflow n8n. Ini bukan n8n Personal API Key.

Laravel mengirim header:

```http
X-N8N-API-Key: <N8N_API_KEY>
```

n8n memvalidasi header itu sebelum memproses grading.

## Jalankan n8n lokal

Dari PowerShell di root project:

```powershell
$env:N8N_API_KEY = ((Get-Content .env | Select-String '^N8N_API_KEY=').Line -replace '^N8N_API_KEY=', '')
npx n8n start
```

Buka:

```text
http://127.0.0.1:5678
```

## Import workflow

1. Di n8n, buka **Workflows**.
2. Pilih **Import from File**.
3. Pilih `n8n/autograde-ai-openai-workflow.template.json`.
4. Save workflow.
5. Buka node **Google Gemini Chat Model**, lalu pilih atau buat credential Google Gemini.
6. Untuk n8n Cloud, buat variable `N8N_API_KEY` berisi nilai yang sama dengan Laravel `.env`. Jika plan n8n tidak punya Variables, buka node **Validate Laravel Secret** dan ganti placeholder `PASTE_LARAVEL_N8N_API_KEY_HERE`.
7. Aktifkan workflow.

Production webhook URL:

```text
http://127.0.0.1:5678/webhook/autograde-ai
```

URL itu sudah diset di Laravel `.env` sebagai `N8N_WEBHOOK_URL`.

## Node AI

Template memakai **AI Grading Agent** dengan sub-node:

- **Google Gemini Chat Model**

Node **Exchange Google Token** pada template ini adalah Code node. Laravel mengirim access token Google sementara di payload awal, jadi n8n Cloud tidak perlu melakukan callback ke Laravel untuk menukar token. Ini menghindari deadlock saat testing lokal via ngrok dan `php artisan serve`.

Template sengaja tidak memakai **Structured Output Parser** atau **Think Tool**. Gemini dapat menolak function-calling schema kompleks yang berisi `$ref`, jadi JSON grading diminta lewat prompt dan divalidasi di node **Normalize Agent Output**.

Node **Extract Student Answer Text** membaca jawaban dari short answer atau attachment Drive. Node **Extract Rubric and Answer Key Text** membaca rubrik dan kunci jawaban dari Drive. Untuk testing, gunakan file TXT/PHP/Google Docs. PDF/DOCX masih perlu node ekstraksi/OCR tambahan.

Node **Keep Submitted Work** mengabaikan submission kosong yang belum punya jawaban/attachment, supaya workflow tidak menilai placeholder `CREATED` dari Classroom sebagai jawaban gagal.

## Node email

Template juga sudah menyertakan **Send Feedback Email - Gmail API** sebelum **Aggregate Results**.

Node ini mengirim email memakai access token Google dosen yang login di Laravel, bukan credential Gmail terpisah di n8n. Email hanya dikirim jika:

- hasil grading `APPROVED`,
- assignment mengaktifkan `auto_email`,
- email mahasiswa tersedia dari Google Classroom profile,
- AI menghasilkan `feedback_email`.

Pastikan OAuth Google di Laravel memiliki scope `https://www.googleapis.com/auth/gmail.send` dan `https://www.googleapis.com/auth/drive.readonly`. Jika user sudah login sebelum scope ini aktif, lakukan disconnect/login ulang agar token baru membawa izin Gmail dan Drive readonly.

Untuk produksi, tambahkan node ekstraksi PDF/DOCX/OCR sebelum node AI dan isi `answer_text`, `rubric_text`, serta `answer_key_text` dengan teks bersih dari Drive.
