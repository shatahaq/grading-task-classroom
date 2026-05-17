# Migration: create_oauth_tokens_table

Migration ini membuat tabel `oauth_tokens`.

## Struktur
- `user_id`: foreign key unik ke `users`.
- `access_token_encrypted`: access token Google terenkripsi.
- `refresh_token_encrypted`: refresh token Google terenkripsi, nullable karena Google tidak selalu mengirim ulang token ini.
- `expiry_date`: masa berlaku access token.
- `scopes`: daftar scope OAuth dalam format JSON.
- `revoked`: penanda token tidak boleh dipakai lagi.

Jika user dihapus, token ikut terhapus lewat cascade.
