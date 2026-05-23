<?php

declare(strict_types=1);

namespace App\Support;

final class HttpClient
{
    /** @param array<string, mixed> $query */
    public function get(string $url, array $query = [], array $headers = [], int $timeout = 15): array
    {
        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        return $this->request('GET', $url, null, $headers, $timeout);
    }

    /** @param array<string, mixed> $payload */
    public function postJson(string $url, array $payload, array $headers = [], int $timeout = 30): array
    {
        $headers[] = 'Content-Type: application/json';

        return $this->request('POST', $url, json_encode($payload, JSON_THROW_ON_ERROR), $headers, $timeout);
    }

    /** @param array<string, mixed> $payload */
    public function postForm(string $url, array $payload, array $headers = [], int $timeout = 30): array
    {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';

        return $this->request('POST', $url, http_build_query($payload), $headers, $timeout);
    }

    public function postRaw(string $url, string $body, string $contentType, array $headers = [], int $timeout = 60): array
    {
        $headers[] = 'Content-Type: ' . $contentType;

        return $this->request('POST', $url, $body, $headers, $timeout);
    }

    private function request(string $method, string $url, ?string $body, array $headers, int $timeout): array
    {
        $ch = curl_init($url);

        if ($ch === false) {
            throw new \RuntimeException('cURL tidak tersedia.');
        }

        $headers[] = 'Accept: application/json';

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);

        if ($raw === false) {
            $message = curl_error($ch);
            curl_close($ch);

            throw new \RuntimeException($message ?: 'Request HTTP gagal.');
        }

        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseBody = substr($raw, $headerSize);
        curl_close($ch);

        $json = json_decode($responseBody, true);

        return [
            'status' => $status,
            'successful' => $status >= 200 && $status < 300,
            'body' => $responseBody,
            'json' => is_array($json) ? $json : null,
        ];
    }
}
