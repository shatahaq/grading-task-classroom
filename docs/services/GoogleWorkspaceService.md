# GoogleWorkspaceService

`App\Services\GoogleWorkspaceService` menjadi pintu tunggal untuk integrasi Google OAuth, Classroom, dan Drive.

## Tanggung jawab
- Mengambil access token aktif dari `oauth_tokens`.
- Refresh access token dengan refresh token terenkripsi jika hampir kedaluwarsa.
- Mengambil daftar kelas Google Classroom dengan filter `teacherId=me`, sehingga kelas yang hanya diikuti sebagai siswa/member tidak tampil.
- Mengambil daftar coursework per kelas.
- Upload file soal, rubrik, dan kunci jawaban ke struktur Google Drive:
  `AutoGrade AI/{teacher_email}/{timestamp-assignment}/{Soal|Rubrik|Kunci Jawaban}`.
- Membuat CourseWork di Google Classroom.
- Hanya file soal yang dilampirkan ke CourseWork agar siswa tidak melihat rubrik dan kunci jawaban. Rubrik dan kunci tetap tersimpan di Drive untuk dipakai n8n melalui access token dosen.
- Revoke token saat user melakukan disconnect.
- Timeout request Google dibuat pendek untuk halaman dashboard/kelas, dan lebih panjang hanya untuk upload file.

## Keamanan
Service tidak pernah menulis token mentah ke audit log atau UI. Access token hanya dikembalikan ke caller backend yang sah, termasuk endpoint n8n yang divalidasi dengan temporary token.
