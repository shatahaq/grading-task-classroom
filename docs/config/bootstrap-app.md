# Config: bootstrap/app.php

`bootstrap/app.php` mengaktifkan route web dan API.

File ini juga mengaktifkan trusted proxy untuk mendukung tunnel/proxy HTTPS seperti ngrok. Tanpa konfigurasi ini, Laravel dapat membaca request ngrok sebagai `http` dan menghasilkan asset URL yang diblokir browser sebagai mixed content.

## Route yang dimuat
- `routes/web.php`: halaman dashboard, login Google, assignment, dan grading.
- `routes/api.php`: endpoint internal n8n tanpa CSRF session.
- `routes/console.php`: command console Laravel.

API route dibutuhkan agar n8n dapat menukar `temporary_token` menjadi access token Google sementara melalui `/api/n8n/google-access-token`.
