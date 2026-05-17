# AuthController

`AuthController` menangani seluruh autentikasi AutoGrade AI.

## Alur login
1. `redirect()` mengarahkan user ke Google OAuth melalui Laravel Socialite.
2. Scope Google Classroom dan Drive diambil dari `config/services.php`.
3. Parameter `access_type=offline` dan `prompt=consent` dipakai agar Google dapat mengirim refresh token.
4. `callback()` menerima profil Google, membuat atau memperbarui user, lalu login ke session Laravel.

Jika `GOOGLE_CLIENT_ID` atau `GOOGLE_CLIENT_SECRET` belum terisi, `redirect()` mengembalikan user ke halaman login dengan pesan konfigurasi. Ini mencegah error Google `Missing required parameter: client_id`.

Saat `APP_DEBUG=true`, kegagalan callback menampilkan nomor audit log dan potongan pesan error agar debugging lokal lebih cepat. Detail tetap dicatat penuh di `audit_logs`.

## Penyimpanan token
- Access token disimpan ke `oauth_tokens.access_token_encrypted`.
- Refresh token disimpan ke `oauth_tokens.refresh_token_encrypted` jika Google mengirimkannya.
- Keduanya dienkripsi dengan `Crypt::encryptString()`.

## Logout
`logout()` menghapus session lokal dan mencatat audit log. Token Google tidak dihapus agar integrasi tetap tersedia untuk login berikutnya.

## Disconnect
`disconnect()` memanggil revoke Google best-effort, menghapus token lokal dari `oauth_tokens`, dan mencatat audit log. User tetap login di aplikasi, tetapi harus login Google ulang untuk sinkronisasi Classroom/Drive.
