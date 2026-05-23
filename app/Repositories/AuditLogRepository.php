<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class AuditLogRepository
{
    public function create(?int $userId, string $action, ?string $resource, string $status, ?string $message = null): void
    {
        Database::execute(
            'INSERT INTO audit_logs (user_id, action, resource, status, message, created_at, updated_at)
             VALUES (:user_id, :action, :resource, :status, :message, :created_at, :updated_at)',
            [
                'user_id' => $userId,
                'action' => $action,
                'resource' => $resource,
                'status' => $status,
                'message' => $message,
                'created_at' => now_string(),
                'updated_at' => now_string(),
            ]
        );
    }
}
