<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\GradingResult;
use App\Services\GoogleWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GradingController extends Controller
{
    public function show(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 403);

        return view('assignments.grading', [
            'assignment' => $assignment->load('gradingResults'),
            'results' => $assignment->gradingResults()->latest()->get(),
        ]);
    }

    public function trigger(Request $request, Assignment $assignment, GoogleWorkspaceService $google)
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 403);

        $webhookUrl = config('services.n8n.webhook_url');

        if (! $webhookUrl) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'grading.trigger',
                'resource' => 'assignment:'.$assignment->id,
                'status' => 'FAILED',
                'message' => 'N8N_WEBHOOK_URL belum dikonfigurasi.',
            ]);

            return response()->json([
                'message' => 'N8N_WEBHOOK_URL belum dikonfigurasi.',
            ], 422);
        }

        $assignment->update(['status' => 'grading']);

        $temporaryToken = Crypt::encryptString(json_encode([
            'assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'nonce' => Str::random(24),
            'expires_at' => now()->addMinutes(15)->toIso8601String(),
        ]));

        try {
            $googleAccessToken = $google->accessToken($request->user());

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-N8N-API-Key' => config('services.n8n.api_key'),
            ])->timeout(60)->post($webhookUrl, [
                'assignment_id' => $assignment->id,
                'temporary_token' => $temporaryToken,
                'token_exchange_url' => route('api.n8n.google-access-token'),
                'google_access_token' => $googleAccessToken,
                'google_token_type' => 'Bearer',
                'google_token_expires_at' => $request->user()->oauthToken?->expiry_date?->toIso8601String(),
                'course_id' => $assignment->course_id,
                'coursework_id' => $assignment->coursework_id,
                'grade_mode' => $assignment->grade_mode,
                'max_score' => (float) $assignment->max_score,
                'auto_approval' => $assignment->auto_approval,
                'auto_email' => $assignment->auto_email,
                'min_answer_length' => $assignment->min_answer_length,
                'files' => [
                    'question_drive_file_id' => $assignment->question_drive_file_id,
                    'rubric_drive_file_id' => $assignment->rubric_drive_file_id,
                    'answer_key_drive_file_id' => $assignment->answer_key_drive_file_id,
                ],
                'assignment' => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'course_id' => $assignment->course_id,
                    'coursework_id' => $assignment->coursework_id,
                    'grade_mode' => $assignment->grade_mode,
                    'max_score' => (float) $assignment->max_score,
                    'auto_approval' => $assignment->auto_approval,
                    'auto_email' => $assignment->auto_email,
                    'min_answer_length' => $assignment->min_answer_length,
                    'question_drive_file_id' => $assignment->question_drive_file_id,
                    'rubric_drive_file_id' => $assignment->rubric_drive_file_id,
                    'answer_key_drive_file_id' => $assignment->answer_key_drive_file_id,
                ],
            ]);

            if (! $response->successful()) {
                $assignment->update(['status' => 'failed']);

                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'grading.trigger',
                    'resource' => 'assignment:'.$assignment->id,
                    'status' => 'FAILED',
                    'message' => 'n8n memberi response '.$response->status().': '.Str::limit($response->body(), 180),
                ]);

                return response()->json([
                    'message' => 'Workflow n8n gagal dipanggil.',
                    'status' => $response->status(),
                ], 502);
            }

            $savedResults = $this->storeResults($assignment, $response->json() ?? []);
            $assignment->update(['status' => $savedResults > 0 ? 'completed' : 'grading']);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'grading.trigger',
                'resource' => 'assignment:'.$assignment->id,
                'status' => 'SUCCESS',
                'message' => "Workflow n8n berhasil dipanggil. {$savedResults} hasil disimpan.",
            ]);

            return response()->json([
                'message' => 'Workflow n8n berhasil dipanggil.',
                'saved_results' => $savedResults,
                'assignment_status' => $assignment->fresh()->status,
                'results' => $assignment->gradingResults()->latest()->get(),
            ]);
        } catch (Throwable $exception) {
            $assignment->update(['status' => 'failed']);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'grading.trigger',
                'resource' => 'assignment:'.$assignment->id,
                'status' => 'FAILED',
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Tidak bisa menghubungi workflow n8n.',
            ], 500);
        }
    }

    private function storeResults(Assignment $assignment, array $payload): int
    {
        $results = data_get($payload, 'results', []);

        if (! is_array($results)) {
            return 0;
        }

        $saved = 0;

        foreach ($results as $result) {
            if (! is_array($result) || empty($result['student_id'])) {
                continue;
            }

            // Skip placeholder results from n8n (no real student submission)
            $studentId = (string) $result['student_id'];
            if (in_array($studentId, ['no-submission', 'no-submitted-work', 'unknown'], true)) {
                \Log::info('AutoGrade: Skipped placeholder submission', [
                    'student_id' => $studentId,
                    'assignment_id' => $assignment->id,
                    '_debug' => array_filter([
                        'submission_states' => $result['_debug_submission_states'] ?? null,
                        'total_submissions' => $result['_debug_total_submissions'] ?? null,
                        'raw' => $result['_debug_raw'] ?? null,
                    ]),
                ]);
                continue;
            }

            $score = $result['score_ai'] ?? $result['score'] ?? null;
            $confidence = strtoupper((string) ($result['confidence'] ?? 'MEDIUM'));
            $needsReview = (bool) ($result['needs_review'] ?? $confidence !== 'HIGH');
            $extractionStatus = strtoupper((string) ($result['extraction_status'] ?? 'SUCCESS'));
            $extractedTextLength = (int) ($result['extracted_text_length'] ?? mb_strlen((string) ($result['student_answer'] ?? '')));
            $outputJsonValid = (bool) ($result['output_json_valid'] ?? true);
            $numericScore = is_numeric($score) ? (float) $score : null;
            $status = $this->determineStatus(
                assignment: $assignment,
                score: $numericScore,
                confidence: $confidence,
                needsReview: $needsReview,
                extractionStatus: $extractionStatus,
                extractedTextLength: $extractedTextLength,
                outputJsonValid: $outputJsonValid,
            );

            GradingResult::updateOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'student_id' => (string) $result['student_id'],
                ],
                [
                    'student_email' => $result['student_email'] ?? null,
                    'score_ai' => $numericScore,
                    'confidence' => in_array($confidence, ['LOW', 'MEDIUM', 'HIGH'], true) ? $confidence : 'MEDIUM',
                    'needs_review' => $needsReview,
                    'status' => $status,
                    'extraction_status' => in_array($extractionStatus, ['PENDING', 'SUCCESS', 'PARTIAL', 'FAILED'], true) ? $extractionStatus : 'PARTIAL',
                    'extracted_text_length' => $extractedTextLength,
                    'output_json_valid' => $outputJsonValid,
                    'reason' => $result['reason'] ?? null,
                    'feedback_email' => $result['feedback_email'] ?? null,
                    'rubric_breakdown' => $result['rubric_breakdown'] ?? null,
                    'raw_llm_output' => $result,
                    'email_sent' => $status === 'APPROVED'
                        && $assignment->auto_email
                        && filled($result['student_email'] ?? null)
                        && (bool) ($result['email_sent'] ?? false),
                ],
            );

            $saved++;
        }

        return $saved;
    }

    private function determineStatus(
        Assignment $assignment,
        ?float $score,
        string $confidence,
        bool $needsReview,
        string $extractionStatus,
        int $extractedTextLength,
        bool $outputJsonValid,
    ): string {
        if ($extractionStatus === 'FAILED') {
            return 'FAILED';
        }

        $canApprove = $assignment->auto_approval
            && $confidence === 'HIGH'
            && ! $needsReview
            && $score !== null
            && $score >= 0
            && $score <= (float) $assignment->max_score
            && $extractionStatus === 'SUCCESS'
            && $extractedTextLength >= $assignment->min_answer_length
            && $outputJsonValid;

        return $canApprove ? 'APPROVED' : 'REVIEW';
    }
}
