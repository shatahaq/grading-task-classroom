<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class OauthTokenRepository
{
    public function findByUserId(int $userId): ?array
    {
        return Database::first('SELECT * FROM oauth_tokens WHERE user_id = :user_id LIMIT 1', ['user_id' => $userId]);
    }

    /** @param array<int, string>|null $scopes */
    public function upsert(int $userId, string $accessToken, ?string $refreshToken, ?string $expiryDate, ?array $scopes, string $tokenType = 'Bearer'): array
    {
        $existing = $this->findByUserId($userId);
        $now = now_string();
        $bindings = [
            'user_id' => $userId,
            'access_token_encrypted' => $accessToken,
            'refresh_token_encrypted' => $refreshToken,
            'expiry_date' => $expiryDate,
            'scopes' => $scopes ? json_encode(array_values($scopes), JSON_UNESCAPED_SLASHES) : null,
            'token_type' => $tokenType,
            'updated_at' => $now,
        ];

        if ($existing) {
            Database::execute(
                'UPDATE oauth_tokens
                    SET access_token_encrypted = :access_token_encrypted,
                        refresh_token_encrypted = COALESCE(:refresh_token_encrypted, refresh_token_encrypted),
                        expiry_date = :expiry_date,
                        scopes = :scopes,
                        token_type = :token_type,
                        revoked = 0,
                        updated_at = :updated_at
                  WHERE user_id = :user_id',
                $bindings
            );
        } else {
            $bindings['created_at'] = $now;
            Database::execute(
                'INSERT INTO oauth_tokens
                    (user_id, access_token_encrypted, refresh_token_encrypted, expiry_date, scopes, token_type, revoked, created_at, updated_at)
                 VALUES
                    (:user_id, :access_token_encrypted, :refresh_token_encrypted, :expiry_date, :scopes, :token_type, 0, :created_at, :updated_at)',
                $bindings
            );
        }

        return $this->findByUserId($userId);
    }

    public function updateAccessToken(int $id, string $accessToken, ?string $expiryDate, string $tokenType = 'Bearer'): void
    {
        Database::execute(
            'UPDATE oauth_tokens
                SET access_token_encrypted = :access_token_encrypted,
                    expiry_date = :expiry_date,
                    token_type = :token_type,
                    last_refreshed_at = :last_refreshed_at,
                    revoked = 0,
                    updated_at = :updated_at
              WHERE id = :id',
            [
                'access_token_encrypted' => $accessToken,
                'expiry_date' => $expiryDate,
                'token_type' => $tokenType,
                'last_refreshed_at' => now_string(),
                'updated_at' => now_string(),
                'id' => $id,
            ]
        );
    }

    public function deleteByUserId(int $userId): void
    {
        Database::execute('DELETE FROM oauth_tokens WHERE user_id = :user_id', ['user_id' => $userId]);
    }
}
