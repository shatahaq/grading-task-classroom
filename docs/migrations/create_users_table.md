# Migration: create_users_table

Migration ini membuat tabel `users` dan `sessions`.

## Users
Tabel `users` hanya menyimpan identitas Google:
- `google_id` unik untuk akun Google.
- `name` dan `email`.
- `role` dengan pilihan `admin` atau `teacher`.
- `remember_token` dan timestamps untuk dukungan session Laravel.

Tidak ada kolom password atau password reset token karena aplikasi hanya mendukung Google OAuth.

## Sessions
Tabel `sessions` dipakai oleh `SESSION_DRIVER=database` agar login Socialite dan dashboard menggunakan session database.
