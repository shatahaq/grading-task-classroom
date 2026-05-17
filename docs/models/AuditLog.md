# AuditLog Model

`App\Models\AuditLog` mencatat aktivitas penting di AutoGrade AI.

## Field utama
- `user_id`: user terkait, nullable agar log tetap ada jika user dihapus.
- `action`: nama aksi seperti `oauth.login` atau `grading.trigger`.
- `resource`: resource terkait, misalnya `assignment:12`.
- `status`: `SUCCESS`, `FAILED`, atau `INFO`.
- `message`: detail singkat untuk troubleshooting.

## Relasi
- `user()`: pemilik aktivitas jika masih tersedia.
