# Migration: create_audit_logs_table

Migration ini membuat tabel `audit_logs`.

## Struktur
- `user_id`: foreign key nullable ke `users`.
- `action`: nama aksi sistem.
- `resource`: resource terkait.
- `status`: `SUCCESS`, `FAILED`, atau `INFO`.
- `message`: detail tambahan.

Log tidak dihapus saat user dihapus; `user_id` akan menjadi null agar jejak aktivitas tetap tersedia.
