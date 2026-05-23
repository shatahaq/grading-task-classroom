<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OauthTokenRepository;
use App\Security\Crypto;
use App\Support\HttpClient;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final class GoogleWorkspaceService
{
    private OauthTokenRepository $tokens;
    private HttpClient $http;

    public function __construct()
    {
        $this->tokens = new OauthTokenRepository();
        $this->http = new HttpClient();
    }

    public function accessToken(array $user): string
    {
        $token = $this->tokens->findByUserId((int) $user['id']);

        if (! $token || (int) ($token['revoked'] ?? 0) === 1) {
            throw new RuntimeException('Akun Google belum tersambung.');
        }

        $expiry = ! empty($token['expiry_date']) ? strtotime((string) $token['expiry_date']) : null;

        if ($expiry !== null && $expiry > time() + 120) {
            return (string) Crypto::decrypt((string) $token['access_token_encrypted']);
        }

        return $this->refreshAccessToken($token);
    }

    public function refreshAccessToken(array $token): string
    {
        $refreshToken = Crypto::decrypt($token['refresh_token_encrypted'] ?? null);

        if (! $refreshToken) {
            throw new RuntimeException('Refresh token Google tidak tersedia. Putuskan koneksi lalu login Google ulang.');
        }

        $response = $this->http->postForm('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response['successful'] || ! is_array($response['json'])) {
            throw new RuntimeException('Refresh token Google gagal: ' . str_limit((string) $response['body'], 180));
        }

        $payload = $response['json'];
        $accessToken = (string) ($payload['access_token'] ?? '');

        if ($accessToken === '') {
            throw new RuntimeException('Google tidak mengembalikan access token.');
        }

        $expiryDate = isset($payload['expires_in'])
            ? date('Y-m-d H:i:s', time() + (int) $payload['expires_in'])
            : null;

        $this->tokens->updateAccessToken(
            (int) $token['id'],
            Crypto::encrypt($accessToken),
            $expiryDate,
            (string) ($payload['token_type'] ?? 'Bearer')
        );

        return $accessToken;
    }

    public function listCourses(array $user): array
    {
        $courses = [];
        $pageToken = null;

        do {
            $response = $this->googleGet($user, 'https://classroom.googleapis.com/v1/courses', array_filter([
                'teacherId' => 'me',
                'courseStates' => 'ACTIVE',
                'pageSize' => 100,
                'pageToken' => $pageToken,
            ]));

            $courses = array_merge($courses, $response['courses'] ?? []);
            $pageToken = $response['nextPageToken'] ?? null;
        } while ($pageToken);

        return array_values(array_filter(array_map(static function (array $course): array {
            return [
                'id' => (string) ($course['id'] ?? ''),
                'name' => (string) ($course['name'] ?? 'Tanpa nama'),
                'section' => (string) ($course['section'] ?? '-'),
                'descriptionHeading' => $course['descriptionHeading'] ?? null,
                'alternateLink' => $course['alternateLink'] ?? null,
                'students' => null,
                'assignments' => [],
                'source' => 'google',
            ];
        }, $courses), static fn (array $course): bool => $course['id'] !== ''));
    }

    public function listCourseWork(array $user, string $courseId): array
    {
        $courseWork = [];
        $pageToken = null;
        $courseId = rawurlencode($courseId);

        do {
            $response = $this->googleGet($user, "https://classroom.googleapis.com/v1/courses/{$courseId}/courseWork", array_filter([
                'pageSize' => 100,
                'orderBy' => 'updateTime desc',
                'pageToken' => $pageToken,
            ]));

            $courseWork = array_merge($courseWork, $response['courseWork'] ?? []);
            $pageToken = $response['nextPageToken'] ?? null;
        } while ($pageToken);

        return array_map(fn (array $work): array => [
            'id' => (string) ($work['id'] ?? ''),
            'title' => (string) ($work['title'] ?? 'Tanpa judul'),
            'description' => $work['description'] ?? null,
            'due' => $this->formatDueDate($work),
            'status' => (string) ($work['state'] ?? 'UNKNOWN'),
            'max_points' => $work['maxPoints'] ?? null,
            'alternateLink' => $work['alternateLink'] ?? null,
            'creationTime' => $work['creationTime'] ?? null,
            'updateTime' => $work['updateTime'] ?? null,
        ], $courseWork);
    }

    public function listCourseStudents(array $user, string $courseId): array
    {
        $students = [];
        $pageToken = null;
        $courseId = rawurlencode($courseId);

        do {
            $response = $this->googleGet($user, "https://classroom.googleapis.com/v1/courses/{$courseId}/students", array_filter([
                'pageSize' => 100,
                'pageToken' => $pageToken,
            ]));

            $students = array_merge($students, $response['students'] ?? []);
            $pageToken = $response['nextPageToken'] ?? null;
        } while ($pageToken);

        return array_values(array_filter(array_map(static function (array $student): array {
            return [
                'id' => (string) ($student['userId'] ?? ''),
                'name' => (string) ($student['profile']['name']['fullName'] ?? 'Tanpa nama'),
                'email' => $student['profile']['emailAddress'] ?? null,
            ];
        }, $students), static fn (array $student): bool => $student['id'] !== ''));
    }

    public function countCourseStudents(array $user, string $courseId): int
    {
        return count($this->listCourseStudents($user, $courseId));
    }

    public function createCourseWork(array $user, array $data): array
    {
        $questionFile = $data['drive_files']['question'] ?? null;
        $materials = [];

        if (is_array($questionFile) && ! empty($questionFile['id'])) {
            $materials[] = [
                'driveFile' => [
                    'driveFile' => [
                        'id' => $questionFile['id'],
                        'title' => $questionFile['name'] ?? 'Lampiran AutoGrade AI',
                    ],
                    'shareMode' => 'VIEW',
                ],
            ];
        }

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'workType' => 'ASSIGNMENT',
            'state' => 'PUBLISHED',
            'maxPoints' => (float) $data['max_score'],
        ];

        if (! empty($data['due_date'])) {
            $dueDate = (new DateTimeImmutable((string) $data['due_date'], new DateTimeZone((string) config('app.timezone'))))
                ->setTimezone(new DateTimeZone('UTC'));

            $payload['dueDate'] = [
                'year' => (int) $dueDate->format('Y'),
                'month' => (int) $dueDate->format('m'),
                'day' => (int) $dueDate->format('d'),
            ];
            $payload['dueTime'] = [
                'hours' => (int) $dueDate->format('H'),
                'minutes' => (int) $dueDate->format('i'),
            ];

            if (! empty($data['close_on_due'])) {
                $payload['submissionModificationMode'] = 'MODIFIABLE_UNTIL_TURNED_IN';
            }
        }

        if ($materials !== []) {
            $payload['materials'] = $materials;
        }

        return $this->googlePostJson(
            $user,
            'https://classroom.googleapis.com/v1/courses/' . rawurlencode((string) $data['course_id']) . '/courseWork',
            $payload
        );
    }

    /** @param array<string, array<string, mixed>> $files */
    public function uploadAssignmentFiles(array $user, array $files): array
    {
        $rootFolderId = $this->ensureFolder($user, 'AutoGrade AI');
        $teacherFolderId = $this->ensureFolder($user, (string) $user['email'], $rootFolderId);
        $assignmentFolderId = $this->ensureFolder($user, date('Ymd-His') . '-assignment', $teacherFolderId);

        $targets = [
            'question' => 'Soal',
            'rubric' => 'Rubrik',
            'answer_key' => 'Kunci Jawaban',
        ];
        $uploaded = [
            'folder_id' => $assignmentFolderId,
            'files' => [],
        ];

        foreach ($targets as $key => $folderName) {
            if (! isset($files[$key])) {
                continue;
            }

            $folderId = $this->ensureFolder($user, $folderName, $assignmentFolderId);
            $uploaded['files'][$key] = $this->uploadFile($user, $files[$key], $folderId);
        }

        return $uploaded;
    }

    public function revoke(array $user): void
    {
        $token = (new OauthTokenRepository())->findByUserId((int) $user['id']);

        if (! $token) {
            return;
        }

        try {
            $this->http->postForm('https://oauth2.googleapis.com/revoke', [
                'token' => Crypto::decrypt($token['access_token_encrypted']) ?? '',
            ], timeout: 15);
        } catch (\Throwable) {
            // Local disconnect must still succeed even when Google revoke is temporarily unavailable.
        }

        $this->tokens->deleteByUserId((int) $user['id']);
    }

    private function googleGet(array $user, string $url, array $query = []): array
    {
        $response = $this->http->get($url, $query, ['Authorization: Bearer ' . $this->accessToken($user)]);

        return $this->expectJson($response);
    }

    private function googlePostJson(array $user, string $url, array $payload): array
    {
        $response = $this->http->postJson($url, $payload, ['Authorization: Bearer ' . $this->accessToken($user)]);

        return $this->expectJson($response);
    }

    private function ensureFolder(array $user, string $name, ?string $parentId = null): string
    {
        $existing = $this->findFolder($user, $name, $parentId);

        if ($existing) {
            return $existing;
        }

        $metadata = [
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ];

        if ($parentId) {
            $metadata['parents'] = [$parentId];
        }

        $response = $this->googlePostJson($user, 'https://www.googleapis.com/drive/v3/files?fields=id,name', $metadata);

        return (string) ($response['id'] ?? throw new RuntimeException('Google Drive tidak mengembalikan folder id.'));
    }

    private function findFolder(array $user, string $name, ?string $parentId = null): ?string
    {
        $query = "mimeType='application/vnd.google-apps.folder' and name='" . $this->escapeDriveQuery($name) . "' and trashed=false";

        if ($parentId) {
            $query .= " and '" . $this->escapeDriveQuery($parentId) . "' in parents";
        }

        $response = $this->googleGet($user, 'https://www.googleapis.com/drive/v3/files', [
            'q' => $query,
            'fields' => 'files(id,name)',
            'pageSize' => 1,
        ]);

        return $response['files'][0]['id'] ?? null;
    }

    private function uploadFile(array $user, array $file, string $folderId): array
    {
        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '' || ! is_file($tmpName)) {
            throw new RuntimeException('File upload tidak valid.');
        }

        $metadata = [
            'name' => basename((string) $file['name']),
            'parents' => [$folderId],
        ];
        $boundary = 'autograde_ai_' . bin2hex(random_bytes(8));
        $mime = mime_content_type($tmpName) ?: 'application/octet-stream';
        $body = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($metadata, JSON_THROW_ON_ERROR)
            . "\r\n--{$boundary}\r\n"
            . 'Content-Type: ' . $mime . "\r\n\r\n"
            . file_get_contents($tmpName)
            . "\r\n--{$boundary}--";

        $response = $this->http->postRaw(
            'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink,mimeType',
            $body,
            "multipart/related; boundary={$boundary}",
            ['Authorization: Bearer ' . $this->accessToken($user)],
            60
        );

        return $this->expectJson($response);
    }

    private function expectJson(array $response): array
    {
        if (! $response['successful']) {
            throw new RuntimeException('Google API gagal (' . $response['status'] . '): ' . str_limit((string) $response['body'], 180));
        }

        if (! is_array($response['json'])) {
            throw new RuntimeException('Google API mengembalikan response tidak valid.');
        }

        return $response['json'];
    }

    private function escapeDriveQuery(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }

    private function formatDueDate(array $work): string
    {
        if (! isset($work['dueDate']) || ! is_array($work['dueDate'])) {
            return '-';
        }

        $date = $work['dueDate'];

        return sprintf('%04d-%02d-%02d', $date['year'] ?? (int) date('Y'), $date['month'] ?? 1, $date['day'] ?? 1);
    }
}
