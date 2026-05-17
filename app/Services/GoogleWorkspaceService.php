<?php

namespace App\Services;

use App\Models\OauthToken;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleWorkspaceService
{
    public function accessToken(User $user): string
    {
        $oauthToken = $user->oauthToken;

        if (! $oauthToken || $oauthToken->revoked) {
            throw new RuntimeException('Akun Google belum tersambung.');
        }

        if ($oauthToken->expiry_date && $oauthToken->expiry_date->isFuture() && $oauthToken->expiry_date->greaterThan(now()->addMinutes(2))) {
            return $oauthToken->accessToken();
        }

        return $this->refreshAccessToken($oauthToken);
    }

    public function refreshAccessToken(OauthToken $oauthToken): string
    {
        $refreshToken = $oauthToken->refreshToken();

        if (! $refreshToken) {
            throw new RuntimeException('Refresh token Google tidak tersedia. Silakan disconnect lalu login Google ulang.');
        }

        $response = Http::asForm()
            ->timeout(30)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ])
            ->throw();

        $payload = $response->json();

        $oauthToken->update([
            'access_token_encrypted' => Crypt::encryptString($payload['access_token']),
            'expiry_date' => isset($payload['expires_in']) ? now()->addSeconds((int) $payload['expires_in']) : null,
            'token_type' => $payload['token_type'] ?? 'Bearer',
            'last_refreshed_at' => now(),
            'revoked' => false,
        ]);

        return $payload['access_token'];
    }

    public function listCourses(User $user): array
    {
        $courses = [];
        $pageToken = null;

        do {
            $response = $this->google($user)
                ->get('https://classroom.googleapis.com/v1/courses', array_filter([
                    'teacherId' => 'me',
                    'courseStates' => 'ACTIVE',
                    'pageSize' => 100,
                    'pageToken' => $pageToken,
                ]))
                ->throw();

            $courses = array_merge($courses, $response->json('courses', []));
            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        return collect($courses)
            ->map(fn (array $course) => [
                'id' => $course['id'] ?? '',
                'name' => $course['name'] ?? 'Tanpa nama',
                'section' => $course['section'] ?? '-',
                'descriptionHeading' => $course['descriptionHeading'] ?? null,
                'alternateLink' => $course['alternateLink'] ?? null,
                'students' => null,
                'assignments' => [],
                'source' => 'google',
            ])
            ->filter(fn (array $course) => filled($course['id']))
            ->values()
            ->all();
    }

    public function listCourseWork(User $user, string $courseId): array
    {
        $courseWork = [];
        $pageToken = null;

        do {
            $response = $this->google($user)
                ->get("https://classroom.googleapis.com/v1/courses/{$courseId}/courseWork", array_filter([
                    'pageSize' => 100,
                    'orderBy' => 'updateTime desc',
                    'pageToken' => $pageToken,
                ]))
                ->throw();

            $courseWork = array_merge($courseWork, $response->json('courseWork', []));
            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        return collect($courseWork)
            ->map(fn (array $work) => [
                'id' => $work['id'] ?? '',
                'title' => $work['title'] ?? 'Tanpa judul',
                'description' => $work['description'] ?? null,
                'due' => $this->formatDueDate($work),
                'status' => $work['state'] ?? 'UNKNOWN',
                'max_points' => $work['maxPoints'] ?? null,
                'alternateLink' => $work['alternateLink'] ?? null,
                'creationTime' => $work['creationTime'] ?? null,
                'updateTime' => $work['updateTime'] ?? null,
            ])
            ->values()
            ->all();
    }

    public function listCourseStudents(User $user, string $courseId): array
    {
        $students = [];
        $pageToken = null;

        do {
            $response = $this->google($user)
                ->get("https://classroom.googleapis.com/v1/courses/{$courseId}/students", array_filter([
                    'pageSize' => 100,
                    'pageToken' => $pageToken,
                ]))
                ->throw();

            $students = array_merge($students, $response->json('students', []));
            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        return collect($students)
            ->map(fn (array $student) => [
                'id' => $student['userId'] ?? '',
                'name' => $student['profile']['name']['fullName'] ?? 'Tanpa nama',
                'email' => $student['profile']['emailAddress'] ?? null,
            ])
            ->filter(fn (array $student) => filled($student['id']))
            ->values()
            ->all();
    }

    public function countCourseStudents(User $user, string $courseId): int
    {
        return count($this->listCourseStudents($user, $courseId));
    }

    public function createCourseWork(User $user, array $data): array
    {
        // Only the question file is attached to Classroom. Rubric and answer key stay private for n8n grading.
        $classroomFiles = [
            'question' => $data['drive_files']['question'] ?? null,
        ];

        $materials = collect($classroomFiles)
            ->filter(fn ($file) => is_array($file) && filled($file['id'] ?? null))
            ->map(fn (array $file) => [
                'driveFile' => [
                    'driveFile' => [
                        'id' => $file['id'],
                        'title' => $file['name'] ?? 'Lampiran AutoGrade AI',
                    ],
                    'shareMode' => 'VIEW',
                ],
            ])
            ->values()
            ->all();

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'workType' => 'ASSIGNMENT',
            'state' => 'PUBLISHED',
            'maxPoints' => (float) $data['max_score'],
        ];

        if (! empty($data['due_date'])) {
            $dueDate = \Carbon\Carbon::parse($data['due_date'])->utc();
            $payload['dueDate'] = [
                'year' => $dueDate->year,
                'month' => $dueDate->month,
                'day' => $dueDate->day,
            ];
            $payload['dueTime'] = [
                'hours' => $dueDate->hour,
                'minutes' => $dueDate->minute,
            ];

            if (! empty($data['close_on_due'])) {
                $payload['submissionModificationMode'] = 'MODIFIABLE_UNTIL_TURNED_IN';
            }
        }

        if ($materials !== []) {
            $payload['materials'] = $materials;
        }

        $response = $this->google($user)
            ->post("https://classroom.googleapis.com/v1/courses/{$data['course_id']}/courseWork", $payload)
            ->throw();

        return $response->json();
    }

    public function uploadAssignmentFiles(User $user, array $files): array
    {
        $rootFolderId = $this->ensureFolder($user, 'AutoGrade AI');
        $teacherFolderId = $this->ensureFolder($user, $user->email, $rootFolderId);
        $assignmentFolderId = $this->ensureFolder($user, now()->format('Ymd-His').'-assignment', $teacherFolderId);

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
            if (! isset($files[$key]) || ! $files[$key] instanceof UploadedFile) {
                continue;
            }

            $folderId = $this->ensureFolder($user, $folderName, $assignmentFolderId);
            $uploaded['files'][$key] = $this->uploadFile($user, $files[$key], $folderId);
        }

        return $uploaded;
    }

    public function revoke(User $user): void
    {
        $oauthToken = $user->oauthToken;

        if (! $oauthToken) {
            return;
        }

        rescue(function () use ($oauthToken) {
            Http::asForm()
                ->timeout(15)
                ->post('https://oauth2.googleapis.com/revoke', [
                    'token' => $oauthToken->accessToken(),
                ]);
        }, report: false);

        $oauthToken->delete();
    }

    public function google(User $user, int $timeout = 15)
    {
        return Http::withToken($this->accessToken($user))
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout($timeout);
    }

    private function ensureFolder(User $user, string $name, ?string $parentId = null): string
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

        $response = $this->google($user)
            ->post('https://www.googleapis.com/drive/v3/files?fields=id,name', $metadata)
            ->throw();

        return $response->json('id');
    }

    private function findFolder(User $user, string $name, ?string $parentId = null): ?string
    {
        $query = "mimeType='application/vnd.google-apps.folder' and name='".$this->escapeDriveQuery($name)."' and trashed=false";

        if ($parentId) {
            $query .= " and '".$this->escapeDriveQuery($parentId)."' in parents";
        }

        $response = $this->google($user)
            ->get('https://www.googleapis.com/drive/v3/files', [
                'q' => $query,
                'fields' => 'files(id,name)',
                'pageSize' => 1,
            ])
            ->throw();

        return $response->json('files.0.id');
    }

    private function uploadFile(User $user, UploadedFile $file, string $folderId): array
    {
        $metadata = [
            'name' => $file->getClientOriginalName(),
            'parents' => [$folderId],
        ];

        $boundary = 'autograde_ai_'.bin2hex(random_bytes(8));
        $body = "--{$boundary}\r\n"
            ."Content-Type: application/json; charset=UTF-8\r\n\r\n"
            .json_encode($metadata)
            ."\r\n--{$boundary}\r\n"
            .'Content-Type: '.($file->getMimeType() ?: 'application/octet-stream')."\r\n\r\n"
            .file_get_contents($file->getRealPath())
            ."\r\n--{$boundary}--";

        $response = $this->google($user, 60)
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink,mimeType')
            ->throw();

        return $response->json();
    }

    private function escapeDriveQuery(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }

    private function formatDueDate(array $work): string
    {
        if (! isset($work['dueDate'])) {
            return '-';
        }

        $date = $work['dueDate'];

        return sprintf(
            '%04d-%02d-%02d',
            $date['year'] ?? now()->year,
            $date['month'] ?? 1,
            $date['day'] ?? 1,
        );
    }
}
