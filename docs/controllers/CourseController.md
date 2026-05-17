# CourseController

`CourseController@index` menampilkan kelas dari Google Classroom melalui `GoogleWorkspaceService`.

## Alur
1. Controller meminta daftar course aktif dari Google Classroom untuk user yang sedang login sebagai pengajar.
2. View `resources/views/courses/index.blade.php` menampilkan daftar kelas dan tombol membuat assignment baru.
3. Coursework dimuat async per kelas melalui `coursework()` agar halaman awal tetap cepat.
4. Endpoint coursework mengecek ulang bahwa `courseId` termasuk daftar kelas teacher milik user.
5. Jika Google API belum siap, controller menampilkan fallback dummy dan mencatat audit log gagal.

Fallback dummy hanya dipakai agar UI tetap bisa ditinjau saat OAuth credential belum lengkap.
