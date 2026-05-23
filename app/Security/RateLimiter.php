<?php

declare(strict_types=1);

namespace App\Security;

final class RateLimiter
{
    public static function hit(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        ensure_directory(STORAGE_PATH . DIRECTORY_SEPARATOR . 'cache');

        $file = STORAGE_PATH . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'rate_limit.json';
        $handle = fopen($file, 'c+');

        if ($handle === false) {
            return true;
        }

        try {
            flock($handle, LOCK_EX);
            $contents = stream_get_contents($handle) ?: '';
            $data = json_decode($contents, true);
            $data = is_array($data) ? $data : [];
            $now = time();
            $bucket = $data[$key] ?? ['reset_at' => $now + $windowSeconds, 'attempts' => 0];

            if (($bucket['reset_at'] ?? 0) <= $now) {
                $bucket = ['reset_at' => $now + $windowSeconds, 'attempts' => 0];
            }

            $bucket['attempts'] = (int) ($bucket['attempts'] ?? 0) + 1;
            $data[$key] = $bucket;

            foreach ($data as $itemKey => $item) {
                if (($item['reset_at'] ?? 0) <= $now - $windowSeconds) {
                    unset($data[$itemKey]);
                }
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($data, JSON_PRETTY_PRINT));

            return $bucket['attempts'] <= $maxAttempts;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
