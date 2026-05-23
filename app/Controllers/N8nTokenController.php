<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Repositories\AssignmentRepository;
use App\Repositories\AuditLogRepository;
use App\Security\Crypto;
use App\Security\RateLimiter;
use App\Services\GoogleWorkspaceService;
use Throwable;

final class N8nTokenController
{
    public function show(Request $request): never
    {
        $apiKey = (string) config('services.n8n.api_key');

        if ($apiKey === '') {
            json_response(['message' => 'N8N_API_KEY belum dikonfigurasi.'], 503);
        }

        $limiterKey = 'n8n-token:' . hash('sha256', $request->ip());

        if (! RateLimiter::hit($limiterKey, 60, 60)) {
            json_response(['message' => 'Terlalu banyak request.'], 429);
        }

        if (! hash_equals($apiKey, (string) $request->header('X-N8N-API-Key'))) {
            json_response(['message' => 'Invalid n8n API key.'], 403);
        }

        $temporaryToken = (string) $request->input('temporary_token', '');
        $assignmentId = $request->input('assignment_id');

        if ($temporaryToken === '' || ! is_numeric($assignmentId)) {
            json_response(['message' => 'temporary_token dan assignment_id wajib diisi.'], 422);
        }

        try {
            $payload = json_decode((string) Crypto::decrypt($temporaryToken), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            json_response(['message' => 'Invalid temporary token.'], 403);
        }

        if ((int) ($payload['assignment_id'] ?? 0) !== (int) $assignmentId) {
            json_response(['message' => 'Temporary token assignment mismatch.'], 403);
        }

        if (strtotime((string) ($payload['expires_at'] ?? '1970-01-01')) <= time()) {
            json_response(['message' => 'Temporary token expired.'], 403);
        }

        $assignment = (new AssignmentRepository())->findWithTeacher((int) $assignmentId);

        if (! $assignment) {
            json_response(['message' => 'Assignment tidak ditemukan.'], 404);
        }

        if ((int) $assignment['teacher_id'] !== (int) ($payload['user_id'] ?? 0)) {
            json_response(['message' => 'Temporary token user mismatch.'], 403);
        }

        $teacher = [
            'id' => (int) $assignment['teacher_id'],
            'google_id' => $assignment['teacher_google_id'] ?? null,
            'name' => $assignment['teacher_name'] ?? null,
            'email' => $assignment['teacher_email'] ?? null,
            'role' => $assignment['teacher_role'] ?? 'teacher',
        ];
        $accessToken = (new GoogleWorkspaceService())->accessToken($teacher);

        (new AuditLogRepository())->create((int) $assignment['teacher_id'], 'n8n.google_access_token', 'assignment:' . $assignment['id'], 'SUCCESS', 'n8n mengambil access token sementara untuk workflow grading.');

        json_response([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'assignment' => [
                'id' => (int) $assignment['id'],
                'course_id' => $assignment['course_id'],
                'coursework_id' => $assignment['coursework_id'],
                'max_score' => (float) $assignment['max_score'],
                'auto_approval' => (bool) $assignment['auto_approval'],
                'auto_email' => (bool) $assignment['auto_email'],
                'min_answer_length' => (int) $assignment['min_answer_length'],
                'question_drive_file_id' => $assignment['question_drive_file_id'],
                'rubric_drive_file_id' => $assignment['rubric_drive_file_id'],
                'answer_key_drive_file_id' => $assignment['answer_key_drive_file_id'],
            ],
        ]);
    }
}
