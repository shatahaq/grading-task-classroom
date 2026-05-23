<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\AssignmentRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\GradingResultRepository;
use App\Services\FileUploadService;
use App\Services\GoogleWorkspaceService;
use Throwable;

final class AssignmentController
{
    private AssignmentRepository $assignments;
    private AuditLogRepository $auditLogs;
    private GoogleWorkspaceService $google;
    private FileUploadService $files;

    public function __construct()
    {
        $this->assignments = new AssignmentRepository();
        $this->auditLogs = new AuditLogRepository();
        $this->google = new GoogleWorkspaceService();
        $this->files = new FileUploadService();
    }

    public function create(Request $request): void
    {
        $notice = null;

        try {
            $courses = $this->google->listCourses($request->user());
        } catch (Throwable $exception) {
            $courses = [];
            $notice = 'Google Classroom belum bisa diakses, jadi daftar kelas dikosongkan sampai sinkronisasi berhasil. Login ulang Google atau cek koneksi Classroom. ' . $exception->getMessage();
        }

        View::render('assignments/create', [
            'title' => 'Buat Tugas - AutoGrade AI',
            'pageTitle' => 'Buat Tugas',
            'pageCaption' => 'Publikasikan tugas dan siapkan konteks AI.',
            'courses' => $courses,
            'selectedCourse' => $request->query('course_id'),
            'notice' => $notice,
        ]);
    }

    public function store(Request $request): never
    {
        [$validated, $errors] = $this->validate($request);

        if ($errors !== []) {
            flash('errors', $errors);
            flash_old($request->all());
            redirect_back(route('assignments.create'));
        }

        $user = $request->user();
        $uploadFiles = [
            'question' => $request->file('question_file'),
            'rubric' => $request->file('rubric_file'),
            'answer_key' => $request->file('answer_key_file'),
        ];

        try {
            $driveUpload = $this->google->uploadAssignmentFiles($user, $uploadFiles);
            $courseWorkData = [
                'course_id' => $validated['course_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'max_score' => $validated['max_score'],
                'drive_files' => $driveUpload['files'],
                'due_date' => $validated['due_date'],
                'close_on_due' => $validated['close_on_due'],
            ];
            $courseWork = $this->google->createCourseWork($user, $courseWorkData);
            $localPaths = $this->files->storeAssignmentFiles($uploadFiles);
        } catch (Throwable $exception) {
            $this->auditLogs->create((int) $user['id'], 'assignment.create', 'google_classroom', 'FAILED', $exception->getMessage());
            flash('errors', ['google' => 'Gagal membuat tugas di Google Classroom/Drive: ' . $exception->getMessage()]);
            flash_old($request->all());
            redirect_back(route('assignments.create'));
        }

        $assignment = $this->assignments->create([
            'teacher_id' => (int) $user['id'],
            'course_id' => $validated['course_id'],
            'coursework_id' => $courseWork['id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'max_score' => $validated['max_score'],
            'file_id' => $driveUpload['files']['rubric']['id'] ?? null,
            'classroom_link' => $courseWork['alternateLink'] ?? null,
            'drive_folder_id' => $driveUpload['folder_id'] ?? null,
            'question_drive_file_id' => $driveUpload['files']['question']['id'] ?? null,
            'rubric_drive_file_id' => $driveUpload['files']['rubric']['id'] ?? null,
            'answer_key_drive_file_id' => $driveUpload['files']['answer_key']['id'] ?? null,
            'question_local_path' => $localPaths['question'] ?? null,
            'rubric_local_path' => $localPaths['rubric'] ?? null,
            'answer_key_local_path' => $localPaths['answer_key'] ?? null,
            'auto_approval' => $validated['auto_approval'] ? 1 : 0,
            'auto_email' => $validated['auto_email'] ? 1 : 0,
            'grade_mode' => $validated['grade_mode'],
            'min_answer_length' => $validated['min_answer_length'],
            'due_date' => $validated['due_date'],
            'close_on_due' => $validated['close_on_due'] ? 1 : 0,
            'status' => 'ready',
        ]);

        $this->auditLogs->create((int) $user['id'], 'assignment.create', 'assignment:' . $assignment['id'], 'SUCCESS', 'Assignment dibuat, file diunggah ke Drive, dan CourseWork dibuat di Classroom.');

        flash('status', 'Assignment berhasil dibuat.');
        redirect(route('assignments.grading', (int) $assignment['id']));
    }

    public function destroy(Request $request, string $assignmentId): never
    {
        if (! ctype_digit($assignmentId)) {
            abort_response(404, 'Assignment tidak ditemukan.');
        }

        $assignment = $this->assignments->findForTeacher((int) $assignmentId, (int) $request->user()['id']);

        if (! $assignment) {
            abort_response(404, 'Assignment tidak ditemukan.');
        }

        $title = (string) $assignment['title'];
        $this->files->cleanup([
            $assignment['question_local_path'] ?? null,
            $assignment['rubric_local_path'] ?? null,
            $assignment['answer_key_local_path'] ?? null,
        ]);
        (new GradingResultRepository())->deleteByAssignment((int) $assignment['id']);
        $this->assignments->delete((int) $assignment['id']);
        $this->auditLogs->create((int) $request->user()['id'], 'assignment.delete', 'assignment:' . $assignment['id'], 'SUCCESS', 'Assignment "' . $title . '" berhasil dihapus dari dashboard.');

        flash('status', 'Assignment "' . $title . '" berhasil dihapus.');
        redirect(route('dashboard'));
    }

    private function validate(Request $request): array
    {
        $errors = [];
        $courseId = trim((string) $request->input('course_id', ''));
        $title = trim((string) $request->input('title', ''));
        $description = trim((string) $request->input('description', ''));
        $maxScore = $request->input('max_score', null);
        $gradeMode = (string) $request->input('grade_mode', 'draft');
        $minAnswerLength = $request->input('min_answer_length', 120);
        $dueDateInput = trim((string) $request->input('due_date', ''));

        if ($courseId === '' || strlen($courseId) > 100 || ! preg_match('/^[A-Za-z0-9._-]+$/', $courseId)) {
            $errors['course_id'] = 'Kelas wajib dipilih.';
        }

        if ($title === '' || mb_strlen($title) > 255) {
            $errors['title'] = 'Judul tugas wajib diisi dan maksimal 255 karakter.';
        }

        if (mb_strlen($description) > 2000) {
            $errors['description'] = 'Deskripsi maksimal 2000 karakter.';
        }

        if (! is_numeric($maxScore) || (float) $maxScore < 0 || (float) $maxScore > 999.99) {
            $errors['max_score'] = 'Skor maksimum harus berupa angka antara 0 sampai 999.99.';
        }

        if (! in_array($gradeMode, ['none', 'draft', 'final'], true)) {
            $errors['grade_mode'] = 'Grade mode tidak valid.';
        }

        if (! ctype_digit((string) $minAnswerLength) || (int) $minAnswerLength < 0 || (int) $minAnswerLength > 10000) {
            $errors['min_answer_length'] = 'Minimum panjang jawaban harus 0 sampai 10000.';
        }

        $dueDate = null;

        if ($dueDateInput !== '') {
            $timestamp = strtotime($dueDateInput);

            if ($timestamp === false || $timestamp <= time()) {
                $errors['due_date'] = 'Tenggat waktu harus berupa tanggal di masa depan.';
            } else {
                $dueDate = date('Y-m-d H:i:s', $timestamp);
            }
        }

        $fileLabels = [
            'question_file' => 'File soal',
            'rubric_file' => 'File rubrik',
            'answer_key_file' => 'File kunci jawaban',
        ];

        foreach ($fileLabels as $field => $label) {
            $error = $this->files->validate($request->file($field), $label);

            if ($error !== null) {
                $errors[$field] = $error;
            }
        }

        return [[
            'course_id' => $courseId,
            'title' => $title,
            'description' => $description,
            'max_score' => round((float) $maxScore, 2),
            'grade_mode' => $gradeMode,
            'min_answer_length' => (int) $minAnswerLength,
            'due_date' => $dueDate,
            'close_on_due' => bool_input($request->input('close_on_due')),
            'auto_approval' => bool_input($request->input('auto_approval')),
            'auto_email' => bool_input($request->input('auto_email')),
        ], $errors];
    }
}
