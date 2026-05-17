# Config: .env

Environment project disiapkan untuk AutoGrade AI.

## Database
Default menggunakan MySQL:
- `DB_CONNECTION=mysql`
- `DB_DATABASE=autograde_ai`

## OAuth dan n8n
Variabel wajib:
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`
- `N8N_WEBHOOK_URL`
- `N8N_API_KEY`

`APP_KEY` wajib terisi karena token Google disimpan memakai enkripsi Laravel `Crypt`.

Untuk development lokal dengan n8n Cloud/ngrok, `PHP_CLI_SERVER_WORKERS=4` dipakai agar Laravel bisa menerima callback internal dari n8n saat request grading utama masih berjalan.

Setelah mengubah credential OAuth di `.env`, jalankan:

```bash
php artisan config:clear
```

Redirect URI yang perlu didaftarkan di Google Cloud Console untuk local development:

```text
http://127.0.0.1:8000/auth/google/callback
```

Jika memakai ngrok, ubah `APP_URL` dan `GOOGLE_REDIRECT_URI` ke domain HTTPS ngrok aktif, lalu daftarkan redirect URI ngrok di Google Cloud Console.
