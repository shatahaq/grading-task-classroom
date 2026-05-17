# DashboardController

`DashboardController@index` menampilkan ringkasan operasional untuk dosen atau asisten pengajar.

## Data yang dihitung
- Jumlah kelas unik dari assignment milik user.
- Jumlah assignment.
- Jumlah hasil grading berstatus `APPROVED`.
- Jumlah hasil grading berstatus `REVIEW`.
- Status token Google OAuth user.
- Audit log terbaru.

Semua query dibatasi pada assignment milik user yang sedang login.
