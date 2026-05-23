<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\AssignmentRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\GradingResultRepository;
use App\Security\Crypto;
use App\Services\GoogleWorkspaceService;
use App\Support\HttpClient;
use Throwable;

final class GradingController
{
    private AssignmentRepository $assignments;
    private GradingResultRepository $results;
    private AuditLogRepository $auditLogs;

    public function __construct()
    {
        $this->assignments = new AssignmentRepository();
        $this->results = new GradingResultRepository();
        $this->auditLogs = new AuditLogRepository();
    }

    public function show(Request $request, string $assignmentId): void
    {
        $assignment = $this->assignmentForUser($request, $assignmentId);

        View::render('assignments/grading', [
            'title' => 'Hasil Grading - AutoGrade AI',
            'pageTitle' => 'Hasil Grading',
            'pageCaption' => (string) $assignment['title'],
            'assignment' => $assignment,
            'results' => $this->results->forAssignment((int) $assignment['id']),
        ]);
    }

    public function trigger(Request $request, string $assignmentId): never
    {
        $assignment = $this->assignmentForUser($request, $assignmentId);
        $user = $request->user();
        $webhookUrl = (string) config('services.n8n.webhook_url');

        if ($webhookUrl === '') {
            $this->auditLogs->create((int) $user['id'], 'grading.trigger', 'assignment:' . $assignment['id'], 'FAILED', 'N8N_WEBHOOK_URL belum dikonfigurasi.');
            json_response(['message' => 'N8N_WEBHOOK_URL belum dikonfigurasi.'], 422);
        }

        $this->assignments->updateStatus((int) $assignment['id'], 'grading');

        $temporaryToken = Crypto::encrypt(json_encode([
            'assignment_id' => (int) $assignment['id'],
            'user_id' => (int) $user['id'],
            'nonce' => bin2hex(random_bytes(24)),
            'expires_at' => date(DATE_ATOM, time() + 900),
        ], JSON_THROW_ON_ERROR));

        try {
            $google = new GoogleWorkspaceService();
            $googleAccessToken = $google->accessToken($user);
            $headers = [];
            $apiKey = (string) config('services.n8n.api_key');

            if ($apiKey !== '') {
                $headers[] = 'X-N8N-API-Key: ' . $apiKey;
            }

            $response = (new HttpClient())->postJson($webhookUrl, [
                'assignment_id' => (int) $assignment['id'],
                'temporary_token' => $temporaryToken,
                'token_exchange_url' => url(route('api.n8n.google-access-token')),
                'google_access_token' => $googleAccessToken,
                'google_token_type' => 'Bearer',
                'course_id' => $assignment['course_id'],
                'coursework_id' => $assignment['coursework_id'],
                'grade_mode' => $assignment['grade_mode'],
                'max_score' => (float) $assignment['max_score'],
                'auto_approval' => (bool) $assignment['auto_approval'],
                'auto_email' => (bool) $assignment['auto_email'],
                'min_answer_length' => (int) $assignment['min_answer_length'],
                'files' => [
                    'question_drive_file_id' => $assignment['question_drive_file_id'],
                    'rubric_drive_file_id' => $assignment['rubric_drive_file_id'],
                    'answer_key_drive_file_id' => $assignment['answer_key_drive_file_id'],
                ],
                'assignment' => [
                    'id' => (int) $assignment['id'],
                    'title' => $assignment['title'],
                    'course_id' => $assignment['course_id'],
                    'coursework_id' => $assignment['coursework_id'],
                    'grade_mode' => $assignment['grade_mode'],
                    'max_score' => (float) $assignment['max_score'],
                    'auto_approval' => (bool) $assignment['auto_approval'],
                    'auto_email' => (bool) $assignment['auto_email'],
                    'min_answer_length' => (int) $assignment['min_answer_length'],
                    'question_drive_file_id' => $assignment['question_drive_file_id'],
                    'rubric_drive_file_id' => $assignment['rubric_drive_file_id'],
                    'answer_key_drive_file_id' => $assignment['answer_key_drive_file_id'],
                ],
            ], $headers, 60);

            if (! $response['successful']) {
                $this->assignments->updateStatus((int) $assignment['id'], 'failed');
                $this->auditLogs->create((int) $user['id'], 'grading.trigger', 'assignment:' . $assignment['id'], 'FAILED', 'n8n memberi response ' . $response['status'] . ': ' . str_limit((string) $response['body'], 180));
                json_response(['message' => 'Workflow n8n gagal dipanggil.', 'status' => $response['status']], 502);
            }

            $savedResults = $this->storeResults($assignment, is_array($response['json']) ? $response['json'] : []);
            $this->assignments->updateStatus((int) $assignment['id'], $savedResults > 0 ? 'completed' : 'grading');
            $this->auditLogs->create((int) $user['id'], 'grading.trigger', 'assignment:' . $assignment['id'], 'SUCCESS', "Workflow n8n berhasil dipanggil. {$savedResults} hasil disimpan.");

            json_response([
                'message' => 'Workflow n8n berhasil dipanggil.',
                'saved_results' => $savedResults,
                'assignment_status' => $savedResults > 0 ? 'completed' : 'grading',
                'results' => $this->results->forAssignment((int) $assignment['id']),
            ]);
        } catch (Throwable $exception) {
            $this->assignments->updateStatus((int) $assignment['id'], 'failed');
            $this->auditLogs->create((int) $user['id'], 'grading.trigger', 'assignment:' . $assignment['id'], 'FAILED', $exception->getMessage());
            json_response(['message' => 'Tidak bisa menghubungi workflow n8n.'], 500);
        }
    }

    private function storeResults(array $assignment, array $payload): int
    {
        $results = $payload['results'] ?? [];

        if (! is_array($results)) {
            return 0;
        }

        $saved = 0;

        foreach ($results as $result) {
            if (! is_array($result) || empty($result['student_id'])) {
                continue;
            }

            $studentId = (string) $result['student_id'];

            if (in_array($studentId, ['no-submission', 'no-submitted-work', 'unknown'], true)) {
                error_log('AutoGrade skipped placeholder submission for assignment ' . $assignment['id']);
                continue;
            }

            $score = $result['score_ai'] ?? $result['score'] ?? null;
            $confidence = strtoupper((string) ($result['confidence'] ?? 'MEDIUM'));
            $needsReview = (bool) ($result['needs_review'] ?? $confidence !== 'HIGH');
            $extractionStatus = strtoupper((string) ($result['extraction_status'] ?? 'SUCCESS'));
            $extractedTextLength = (int) ($result['extracted_text_length'] ?? mb_strlen((string) ($result['student_answer'] ?? '')));
            $outputJsonValid = (bool) ($result['output_json_valid'] ?? true);
            $numericScore = is_numeric($score) ? (float) $score : null;
            $status = $this->determineStatus($assignment, $numericScore, $confidence, $needsReview, $extractionStatus, $extractedTextLength, $outputJsonValid);

            $this->results->upsert((int) $assignment['id'], $studentId, [
                'student_email' => $result['student_email'] ?? null,
                'score_ai' => $numericScore,
                'confidence' => in_array($confidence, ['LOW', 'MEDIUM', 'HIGH'], true) ? $confidence : 'MEDIUM',
                'needs_review' => $needsReview ? 1 : 0,
                'status' => $status,
                'extraction_status' => in_array($extractionStatus, ['PENDING', 'SUCCESS', 'PARTIAL', 'FAILED'], true) ? $extractionStatus : 'PARTIAL',
                'extracted_text_length' => $extractedTextLength,
                'output_json_valid' => $outputJsonValid ? 1 : 0,
                'reason' => $result['reason'] ?? null,
                'feedback_email' => $result['feedback_email'] ?? null,
                'rubric_breakdown' => $result['rubric_breakdown'] ?? null,
                'raw_llm_output' => $result,
                'email_sent' => $status === 'APPROVED'
                    && (bool) $assignment['auto_email']
                    && ! empty($result['student_email'])
                    && (bool) ($result['email_sent'] ?? false) ? 1 : 0,
            ]);

            $saved++;
        }

        return $saved;
    }

    private function determineStatus(array $assignment, ?float $score, string $confidence, bool $needsReview, string $extractionStatus, int $extractedTextLength, bool $outputJsonValid): string
    {
        if ($extractionStatus === 'FAILED') {
            return 'FAILED';
        }

        $canApprove = (bool) $assignment['auto_approval']
            && $confidence === 'HIGH'
            && ! $needsReview
            && $score !== null
            && $score >= 0
            && $score <= (float) $assignment['max_score']
            && $extractionStatus === 'SUCCESS'
            && $extractedTextLength >= (int) $assignment['min_answer_length']
            && $outputJsonValid;

        return $canApprove ? 'APPROVED' : 'REVIEW';
    }

    private function assignmentForUser(Request $request, string $assignmentId): array
    {
        if (! ctype_digit($assignmentId)) {
            abort_response(404, 'Assignment tidak ditemukan.');
        }

        $assignment = $this->assignments->findForTeacher((int) $assignmentId, (int) $request->user()['id']);

        if (! $assignment) {
            abort_response(404, 'Assignment tidak ditemukan.');
        }

        return $assignment;
    }
}
