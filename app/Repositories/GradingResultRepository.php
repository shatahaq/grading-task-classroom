<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class GradingResultRepository
{
    public function forAssignment(int $assignmentId): array
    {
        return Database::select(
            'SELECT * FROM grading_results WHERE assignment_id = :assignment_id ORDER BY created_at DESC, id DESC',
            ['assignment_id' => $assignmentId]
        );
    }

    /** @param array<int, int> $assignmentIds */
    public function forAssignments(array $assignmentIds): array
    {
        if ($assignmentIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($assignmentIds), '?'));

        return Database::select(
            "SELECT gr.*, a.title AS assignment_title, a.course_id
               FROM grading_results gr
               JOIN assignments a ON a.id = gr.assignment_id
              WHERE gr.assignment_id IN ({$placeholders})
              ORDER BY gr.created_at DESC, gr.id DESC",
            array_values($assignmentIds)
        );
    }

    /** @param array<string, mixed> $data */
    public function upsert(int $assignmentId, string $studentId, array $data): void
    {
        $existing = Database::first(
            'SELECT id FROM grading_results WHERE assignment_id = :assignment_id AND student_id = :student_id LIMIT 1',
            ['assignment_id' => $assignmentId, 'student_id' => $studentId]
        );

        $now = now_string();
        $payload = array_merge([
            'assignment_id' => $assignmentId,
            'student_id' => $studentId,
            'student_email' => null,
            'score_ai' => null,
            'confidence' => 'MEDIUM',
            'needs_review' => 1,
            'status' => 'REVIEW',
            'extraction_status' => 'PENDING',
            'extracted_text_length' => 0,
            'output_json_valid' => 0,
            'reason' => null,
            'feedback_email' => null,
            'rubric_breakdown' => null,
            'raw_llm_output' => null,
            'email_sent' => 0,
            'updated_at' => $now,
        ], $data);

        $payload['rubric_breakdown'] = is_array($payload['rubric_breakdown'])
            ? json_encode($payload['rubric_breakdown'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $payload['rubric_breakdown'];
        $payload['raw_llm_output'] = is_array($payload['raw_llm_output'])
            ? json_encode($payload['raw_llm_output'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $payload['raw_llm_output'];

        if ($existing) {
            Database::execute(
                'UPDATE grading_results
                    SET student_email = :student_email,
                        score_ai = :score_ai,
                        confidence = :confidence,
                        needs_review = :needs_review,
                        status = :status,
                        extraction_status = :extraction_status,
                        extracted_text_length = :extracted_text_length,
                        output_json_valid = :output_json_valid,
                        reason = :reason,
                        feedback_email = :feedback_email,
                        rubric_breakdown = :rubric_breakdown,
                        raw_llm_output = :raw_llm_output,
                        email_sent = :email_sent,
                        updated_at = :updated_at
                  WHERE id = :id',
                [
                    'student_email' => $payload['student_email'],
                    'score_ai' => $payload['score_ai'],
                    'confidence' => $payload['confidence'],
                    'needs_review' => $payload['needs_review'],
                    'status' => $payload['status'],
                    'extraction_status' => $payload['extraction_status'],
                    'extracted_text_length' => $payload['extracted_text_length'],
                    'output_json_valid' => $payload['output_json_valid'],
                    'reason' => $payload['reason'],
                    'feedback_email' => $payload['feedback_email'],
                    'rubric_breakdown' => $payload['rubric_breakdown'],
                    'raw_llm_output' => $payload['raw_llm_output'],
                    'email_sent' => $payload['email_sent'],
                    'updated_at' => $payload['updated_at'],
                    'id' => (int) $existing['id'],
                ]
            );

            return;
        }

        $payload['created_at'] = $now;
        Database::execute(
            'INSERT INTO grading_results
                (assignment_id, student_id, student_email, score_ai, confidence, needs_review, status, extraction_status,
                 extracted_text_length, output_json_valid, reason, feedback_email, rubric_breakdown, raw_llm_output, email_sent,
                 created_at, updated_at)
             VALUES
                (:assignment_id, :student_id, :student_email, :score_ai, :confidence, :needs_review, :status, :extraction_status,
                 :extracted_text_length, :output_json_valid, :reason, :feedback_email, :rubric_breakdown, :raw_llm_output, :email_sent,
                 :created_at, :updated_at)',
            $payload
        );
    }

    public function deleteByAssignment(int $assignmentId): void
    {
        Database::execute('DELETE FROM grading_results WHERE assignment_id = :assignment_id', ['assignment_id' => $assignmentId]);
    }
}
