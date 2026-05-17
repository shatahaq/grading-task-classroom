# OauthToken Model

`App\Models\OauthToken` menyimpan token Google untuk integrasi Classroom dan Drive.

## Keamanan
- `access_token_encrypted` dan `refresh_token_encrypted` selalu berisi nilai terenkripsi dari `Crypt::encryptString()`.
- Helper `accessToken()` dan `refreshToken()` mendekripsi token saat benar-benar dibutuhkan.

## Relasi
- `user()`: token dimiliki oleh satu user.

## Cast
- `expiry_date` sebagai datetime.
- `scopes` sebagai array.
- `last_refreshed_at` sebagai datetime.
- `revoked` sebagai boolean.
