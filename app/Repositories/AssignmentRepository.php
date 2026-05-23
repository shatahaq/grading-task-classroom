<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class AssignmentRepository
{
    public function allForTeacherWithStats(int $teacherId): array
    {
        return Database::select(
            'SELECT a.*,
                    (SELECT COUNT(*) FROM grading_results gr WHERE gr.assignment_id = a.id) AS grading_results_count,
                    (SELECT COUNT(*) FROM grading_results gr WHERE gr.assignment_id = a.id AND gr.status = "APPROVED") AS approved_results_count,
                    (SELECT COUNT(*) FROM grading_results gr WHERE gr.assignment_id = a.id AND gr.status = "REVIEW") AS review_results_count,
                    (SELECT AVG(gr.score_ai) FROM grading_results gr WHERE gr.assignment_id = a.id) AS grading_results_avg_score_ai
               FROM assignments a
              WHERE a.teacher_id = :teacher_id
              ORDER BY a.created_at DESC, a.id DESC',
            ['teacher_id' => $teacherId]
        );
    }

    public function findForTeacher(int $id, int $teacherId): ?array
    {
        return Database::first(
            'SELECT * FROM assignments WHERE id = :id AND teacher_id = :teacher_id LIMIT 1',
            ['id' => $id, 'teacher_id' => $teacherId]
        );
    }

    public function findById(int $id): ?array
    {
        return Database::first('SELECT * FROM assignments WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findWithTeacher(int $id): ?array
    {
        return Database::first(
            'SELECT a.*, u.google_id AS teacher_google_id, u.name AS teacher_name, u.email AS teacher_email, u.role AS teacher_role
               FROM assignments a
               JOIN users u ON u.id = a.teacher_id
              WHERE a.id = :id
              LIMIT 1',
            ['id' => $id]
        );
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $now = now_string();
        $data = array_merge([
            'description' => null,
            'coursework_id' => null,
            'classroom_link' => null,
            'file_id' => null,
            'drive_folder_id' => null,
            'question_drive_file_id' => null,
            'rubric_drive_file_id' => null,
            'answer_key_drive_file_id' => null,
            'question_local_path' => null,
            'rubric_local_path' => null,
            'answer_key_local_path' => null,
            'auto_approval' => 0,
            'auto_email' => 0,
            'grade_mode' => 'draft',
            'min_answer_length' => 120,
            'due_date' => null,
            'close_on_due' => 0,
            'status' => 'ready',
            'created_at' => $now,
            'updated_at' => $now,
        ], $data);

        Database::execute(
            'INSERT INTO assignments
                (teacher_id, course_id, coursework_id, title, description, max_score, file_id, classroom_link, drive_folder_id,
                 question_drive_file_id, rubric_drive_file_id, answer_key_drive_file_id, question_local_path, rubric_local_path,
                 answer_key_local_path, auto_approval, auto_email, grade_mode, min_answer_length, due_date, close_on_due, status,
                 created_at, updated_at)
             VALUES
                (:teacher_id, :course_id, :coursework_id, :title, :description, :max_score, :file_id, :classroom_link, :drive_folder_id,
                 :question_drive_file_id, :rubric_drive_file_id, :answer_key_drive_file_id, :question_local_path, :rubric_local_path,
                 :answer_key_local_path, :auto_approval, :auto_email, :grade_mode, :min_answer_length, :due_date, :close_on_due, :status,
                 :created_at, :updated_at)',
            $data
        );

        return $this->findById((int) Database::pdo()->lastInsertId());
    }

    public function updateStatus(int $id, string $status): void
    {
        Database::execute(
            'UPDATE assignments SET status = :status, updated_at = :updated_at WHERE id = :id',
            ['status' => $status, 'updated_at' => now_string(), 'id' => $id]
        );
    }

    public function delete(int $id): void
    {
        Database::execute('DELETE FROM assignments WHERE id = :id', ['id' => $id]);
    }
}
