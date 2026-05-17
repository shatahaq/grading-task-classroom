<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Services\GoogleWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class N8nTokenController extends Controller
{
    public function show(Request $request, GoogleWorkspaceService $google)
    {
        if (blank(config('services.n8n.api_key'))) {
            return response()->json(['message' => 'N8N_API_KEY belum dikonfigurasi.'], 503);
        }

        if (! hash_equals((string) config('services.n8n.api_key'), (string) $request->header('X-N8N-API-Key'))) {
            return response()->json(['message' => 'Invalid n8n API key.'], 403);
        }

        $validated = $request->validate([
            'temporary_token' => ['required', 'string'],
            'assignment_id' => ['required', 'integer'],
        ]);

        try {
            $payload = json_decode(Crypt::decryptString($validated['temporary_token']), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return response()->json(['message' => 'Invalid temporary token.'], 403);
        }

        if (($payload['assignment_id'] ?? null) !== (int) $validated['assignment_id']) {
            return response()->json(['message' => 'Temporary token assignment mismatch.'], 403);
        }

        if (now()->greaterThan(\Carbon\Carbon::parse($payload['expires_at'] ?? '1970-01-01'))) {
            return response()->json(['message' => 'Temporary token expired.'], 403);
        }

        $assignment = Assignment::query()
            ->with('teacher.oauthToken')
            ->findOrFail($validated['assignment_id']);

        if ($assignment->teacher_id !== (int) ($payload['user_id'] ?? 0)) {
            return response()->json(['message' => 'Temporary token user mismatch.'], 403);
        }

        $accessToken = $google->accessToken($assignment->teacher);

        AuditLog::create([
            'user_id' => $assignment->teacher_id,
            'action' => 'n8n.google_access_token',
            'resource' => 'assignment:'.$assignment->id,
            'status' => 'SUCCESS',
            'message' => 'n8n mengambil access token sementara untuk workflow grading.',
        ]);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $assignment->teacher->oauthToken?->expiry_date?->toIso8601String(),
            'assignment' => [
                'id' => $assignment->id,
                'course_id' => $assignment->course_id,
                'coursework_id' => $assignment->coursework_id,
                'max_score' => (float) $assignment->max_score,
                'auto_approval' => $assignment->auto_approval,
                'auto_email' => $assignment->auto_email,
                'min_answer_length' => $assignment->min_answer_length,
                'question_drive_file_id' => $assignment->question_drive_file_id,
                'rubric_drive_file_id' => $assignment->rubric_drive_file_id,
                'answer_key_drive_file_id' => $assignment->answer_key_drive_file_id,
            ],
        ]);
    }
}
