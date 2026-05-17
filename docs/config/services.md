# Config: services.php

`config/services.php` menyimpan konfigurasi integrasi pihak ketiga.

## Google OAuth
Konfigurasi `google` dipakai oleh Laravel Socialite:
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- redirect default: `{APP_URL}/auth/google/callback`
- scope Classroom untuk course dan coursework.
- scope Drive `drive.file` untuk upload file soal/rubrik/kunci ke Drive.
- scope Drive `drive.readonly` untuk membaca attachment jawaban siswa dari Drive saat n8n melakukan grading.
- scope Gmail `gmail.send` untuk feedback email otomatis oleh workflow.

Jika scope berubah, user perlu disconnect lalu login Google ulang agar refresh token memiliki izin baru.

## n8n
Konfigurasi `n8n` dipakai `GradingController`:
- `N8N_WEBHOOK_URL`: endpoint webhook workflow n8n.
- `N8N_API_KEY`: secret header `X-N8N-API-Key`.
