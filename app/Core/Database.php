<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $connection = (string) Config::get('database.connection', 'mysql');
        $database = (string) Config::get('database.database', '');

        if ($connection === 'sqlite') {
            $path = $database;

            if ($path !== ':memory:' && ! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $path)) {
                $path = BASE_PATH . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
            }

            $dsn = 'sqlite:' . $path;
            $username = null;
            $password = null;
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                Config::get('database.host', '127.0.0.1'),
                Config::get('database.port', '3306'),
                $database
            );
            $username = (string) Config::get('database.username', 'root');
            $password = (string) Config::get('database.password', '');
        }

        self::$pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }

    /** @param array<string, mixed> $bindings */
    public static function select(string $sql, array $bindings = []): array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($bindings);

        return $statement->fetchAll();
    }

    /** @param array<string, mixed> $bindings */
    public static function first(string $sql, array $bindings = []): ?array
    {
        $rows = self::select($sql, $bindings);

        return $rows[0] ?? null;
    }

    /** @param array<string, mixed> $bindings */
    public static function execute(string $sql, array $bindings = []): int
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($bindings);

        return $statement->rowCount();
    }

    /** @param callable(): mixed $callback */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        try {
            $result = $callback();
            $pdo->commit();

            return $result;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }
}
