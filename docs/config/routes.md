# Routes: web.php

`routes/web.php` mendefinisikan halaman utama AutoGrade AI.

## Public
- `/`: halaman login Google.
- `/login` dan `/auth/google`: redirect ke Google OAuth.
- `/auth/google/callback`: callback Socialite.

## Authenticated
- `/dashboard`: ringkasan grading.
- `/courses`: daftar kelas dummy.
- `/courses/{courseId}/coursework`: JSON coursework Google Classroom untuk kelas yang dimiliki user sebagai teacher.
- `/assignments/create`: form tugas.
- `/assignments/{assignment}/grading`: halaman hasil grading.
- `/assignments/{assignment}/grading/trigger`: endpoint AJAX untuk memanggil webhook n8n.
- `/logout`: logout session lokal.
- `/auth/google/disconnect`: revoke best-effort dan hapus token Google lokal.

## API internal
- `/api/n8n/google-access-token`: endpoint n8n untuk menukar `temporary_token` menjadi access token Google sementara.
