# N8nTokenController

`N8nTokenController` menyediakan endpoint API internal untuk workflow n8n:

```text
POST /api/n8n/google-access-token
```

## Validasi
- Header `X-N8N-API-Key` harus sama dengan `N8N_API_KEY`.
- Body harus membawa `assignment_id` dan `temporary_token`.
- `temporary_token` didekripsi dengan Laravel `Crypt`.
- Token harus cocok dengan assignment, user, dan belum melewati `expires_at`.

## Response
Jika valid, controller mengembalikan access token Google sementara serta metadata assignment yang dibutuhkan n8n. Refresh token tidak pernah dikirim ke n8n.
