# AutoGrade AI Native PHP

AutoGrade AI sekarang berjalan dengan PHP native, bukan Laravel. Runtime utama memakai front controller `public/index.php`, route table eksplisit, PDO prepared statements, CSRF protection, session hardening, OAuth state validation, dan enkripsi token Google berbasis `AES-256-GCM`.

## Kebutuhan

- PHP 8.2+
- Ekstensi PHP: `pdo`, `curl`, `openssl`, `fileinfo`, `mbstring`
- MySQL/MariaDB
- Google OAuth client dengan redirect URI sesuai `GOOGLE_REDIRECT_URI`

## Setup

1. Salin `.env.example` menjadi `.env`.
2. Isi `APP_KEY` dengan key 32 byte:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

3. Isi konfigurasi database, Google OAuth, dan n8n di `.env`.
4. Import schema:

```bash
mysql -u root -p autograde_ai < database/schema.sql
```

5. Jalankan server lokal:

```bash
php -S 127.0.0.1:8000 -t public
```

## Struktur Penting

- `bootstrap/native.php` memuat env, config, autoload, error handling, dan session.
- `routes/web.php` berisi seluruh route web dan API n8n.
- `app/Controllers` berisi controller native.
- `app/Repositories` berisi query PDO.
- `app/Services` berisi integrasi Google, upload file, dan workflow pendukung.
- `app/Security` berisi CSRF, auth session, rate limiting, dan crypto.
- `app/Views` berisi template PHP native.
- `public/assets` berisi CSS dan JS statis tanpa Vite.

## Catatan Migrasi

Tabel database lama dari Laravel tetap dipakai agar data assignment, grading result, audit log, user, dan token tidak perlu dipindahkan. Token OAuth lama dari Laravel dicoba dibaca secara kompatibel; token baru disimpan dengan format native terenkripsi.
