# User Model

`App\Models\User` adalah identitas dosen atau admin yang masuk melalui Google OAuth.

## Field utama
- `google_id`: ID akun Google, wajib unik.
- `name`: nama dari profil Google.
- `email`: email Google, wajib unik.
- `role`: `admin` atau `teacher`, default `teacher`.

## Relasi
- `oauthToken()`: satu token OAuth aktif milik user.
- `assignments()`: daftar assignment yang dibuat user sebagai teacher.
- `auditLogs()`: aktivitas penting yang dicatat sistem.

Model ini tidak memiliki password karena AutoGrade AI hanya memakai Google OAuth.
