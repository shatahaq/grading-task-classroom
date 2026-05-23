<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class UserRepository
{
    public function findById(int $id): ?array
    {
        return Database::first('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findByGoogleIdOrEmail(string $googleId, string $email): ?array
    {
        return Database::first(
            'SELECT * FROM users WHERE google_id = :google_id OR email = :email ORDER BY google_id = :order_google_id DESC LIMIT 1',
            [
                'google_id' => $googleId,
                'email' => $email,
                'order_google_id' => $googleId,
            ]
        );
    }

    public function saveGoogleUser(string $googleId, string $name, string $email): array
    {
        $existing = $this->findByGoogleIdOrEmail($googleId, $email);
        $now = now_string();

        if ($existing) {
            Database::execute(
                'UPDATE users SET google_id = :google_id, name = :name, email = :email, role = COALESCE(NULLIF(role, ""), "teacher"), updated_at = :updated_at WHERE id = :id',
                [
                    'google_id' => $googleId,
                    'name' => $name,
                    'email' => $email,
                    'updated_at' => $now,
                    'id' => (int) $existing['id'],
                ]
            );

            return $this->findById((int) $existing['id']);
        }

        Database::execute(
            'INSERT INTO users (google_id, name, email, role, created_at, updated_at) VALUES (:google_id, :name, :email, :role, :created_at, :updated_at)',
            [
                'google_id' => $googleId,
                'name' => $name,
                'email' => $email,
                'role' => 'teacher',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return $this->findById((int) Database::pdo()->lastInsertId());
    }
}
