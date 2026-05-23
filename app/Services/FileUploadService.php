<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class FileUploadService
{
    private const MAX_BYTES = 10_485_760;
    private const EXTENSIONS = ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg'];
    private const MIME_PREFIXES = ['application/', 'text/', 'image/'];

    public function validate(?array $file, string $label): ?string
    {
        if (! $file || ! isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
            return "{$label} wajib diunggah.";
        }

        if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > self::MAX_BYTES) {
            return "{$label} maksimal 10 MB.";
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));

        if (! in_array($extension, self::EXTENSIONS, true)) {
            return "{$label} harus berupa PDF, DOC, DOCX, TXT, PNG, JPG, atau JPEG.";
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $mime = $tmpName !== '' && is_file($tmpName) ? (mime_content_type($tmpName) ?: '') : '';
        $allowedMime = false;

        foreach (self::MIME_PREFIXES as $prefix) {
            if (str_starts_with($mime, $prefix)) {
                $allowedMime = true;
                break;
            }
        }

        if (! $allowedMime) {
            return "{$label} memiliki tipe file yang tidak didukung.";
        }

        return null;
    }

    /** @param array<string, array<string, mixed>> $files */
    public function storeAssignmentFiles(array $files): array
    {
        $root = STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'assignment-files' . DIRECTORY_SEPARATOR . uuid_v4();
        $paths = [];
        $targets = [
            'question' => 'question',
            'rubric' => 'rubric',
            'answer_key' => 'answer-key',
        ];

        foreach ($targets as $key => $folder) {
            if (! isset($files[$key])) {
                continue;
            }

            $extension = strtolower(pathinfo((string) $files[$key]['name'], PATHINFO_EXTENSION));
            $directory = $root . DIRECTORY_SEPARATOR . $folder;
            ensure_directory($directory);

            $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
            $target = $directory . DIRECTORY_SEPARATOR . $fileName;

            if (! move_uploaded_file((string) $files[$key]['tmp_name'], $target)) {
                throw new RuntimeException('Gagal menyimpan file upload lokal.');
            }

            $paths[$key] = $this->relativeStoragePath($target);
        }

        return $paths;
    }

    /** @param array<int, string|null> $paths */
    public function cleanup(array $paths): void
    {
        foreach ($paths as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            $absolute = $this->absoluteStoragePath($path);
            $storageRoot = realpath(STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads');
            $resolved = realpath($absolute);

            if ($storageRoot && $resolved && str_starts_with($resolved, $storageRoot) && is_file($resolved)) {
                unlink($resolved);
            }
        }
    }

    private function relativeStoragePath(string $absolutePath): string
    {
        return ltrim(str_replace(STORAGE_PATH . DIRECTORY_SEPARATOR, '', $absolutePath), DIRECTORY_SEPARATOR);
    }

    private function absoluteStoragePath(string $relativePath): string
    {
        return STORAGE_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    }
}
