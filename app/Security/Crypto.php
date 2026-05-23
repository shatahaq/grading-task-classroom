<?php

declare(strict_types=1);

namespace App\Security;

final class Crypto
{
    public static function encrypt(string $plainText): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipherText = openssl_encrypt($plainText, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($cipherText === false) {
            throw new \RuntimeException('Enkripsi data gagal.');
        }

        return 'v1:' . base64_encode(json_encode([
            'alg' => 'AES-256-GCM',
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'value' => base64_encode($cipherText),
        ], JSON_THROW_ON_ERROR));
    }

    public static function decrypt(?string $payload): ?string
    {
        if ($payload === null || $payload === '') {
            return null;
        }

        if (str_starts_with($payload, 'v1:')) {
            return self::decryptNative(substr($payload, 3));
        }

        return self::decryptLaravelPayload($payload);
    }

    private static function decryptNative(string $payload): string
    {
        $decoded = json_decode(base64_decode($payload, true) ?: '', true, flags: JSON_THROW_ON_ERROR);

        foreach (['iv', 'tag', 'value'] as $field) {
            if (! isset($decoded[$field]) || ! is_string($decoded[$field])) {
                throw new \RuntimeException('Payload enkripsi tidak valid.');
            }
        }

        $plainText = openssl_decrypt(
            base64_decode($decoded['value'], true) ?: '',
            'aes-256-gcm',
            self::key(),
            OPENSSL_RAW_DATA,
            base64_decode($decoded['iv'], true) ?: '',
            base64_decode($decoded['tag'], true) ?: ''
        );

        if ($plainText === false) {
            throw new \RuntimeException('Dekripsi data gagal.');
        }

        return $plainText;
    }

    private static function decryptLaravelPayload(string $payload): string
    {
        $decoded = json_decode(base64_decode($payload, true) ?: '', true);

        if (! is_array($decoded) || ! isset($decoded['iv'], $decoded['value'])) {
            throw new \RuntimeException('Payload enkripsi legacy tidak valid.');
        }

        $key = self::key();
        $iv = (string) $decoded['iv'];
        $value = (string) $decoded['value'];

        if (isset($decoded['mac'])) {
            $mac = hash_hmac('sha256', $iv . $value, $key);

            if (! hash_equals((string) $decoded['mac'], $mac)) {
                throw new \RuntimeException('MAC payload legacy tidak valid.');
            }
        }

        if (isset($decoded['tag']) && is_string($decoded['tag']) && $decoded['tag'] !== '') {
            $plainText = openssl_decrypt(
                base64_decode($value, true) ?: '',
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                base64_decode($iv, true) ?: '',
                base64_decode($decoded['tag'], true) ?: ''
            );
        } else {
            $plainText = openssl_decrypt($value, 'aes-256-cbc', $key, 0, base64_decode($iv, true) ?: '');
        }

        if ($plainText === false) {
            throw new \RuntimeException('Dekripsi payload legacy gagal.');
        }

        return $plainText;
    }

    private static function key(): string
    {
        $key = (string) config('app.key', '');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            if ($decoded !== false && strlen($decoded) === 32) {
                return $decoded;
            }
        }

        if (strlen($key) === 32) {
            return $key;
        }

        if ($key !== '') {
            return hash('sha256', $key, true);
        }

        throw new \RuntimeException('APP_KEY wajib diisi untuk enkripsi.');
    }
}
