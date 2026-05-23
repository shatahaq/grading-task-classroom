<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\AssignmentRepository;
use App\Repositories\AuditLogRepository;
use App\Services\GoogleWorkspaceService;
use Throwable;

final class CourseController
{
    private AssignmentRepository $assignments;
    private AuditLogRepository $auditLogs;
    private GoogleWorkspaceService $google;

    public function __construct()
    {
        $this->assignments = new AssignmentRepository();
        $this->auditLogs = new AuditLogRepository();
        $this->google = new GoogleWorkspaceService();
    }

    public function index(Request $request): void
    {
        $user = $request->user();
        $notice = null;
        $syncWarnings = [];
        $localAssignments = $this->localAssignments($request);
        $byCourse = $this->groupByCourse($localAssignments);

        try {
            $courses = array_map(function (array $course) use ($user, $byCourse, &$syncWarnings): array {
                $courseAssignments = $byCourse[$course['id']] ?? [];
                $courseWork = null;
                $students = null;

                try {
                    $courseWork = $this->google->listCourseWork($user, (string) $course['id']);
                } catch (Throwable $exception) {
                    $syncWarnings[] = 'Coursework ' . $course['name'] . ': ' . $exception->getMessage();
                }

                try {
                    $students = $this->google->countCourseStudents($user, (string) $course['id']);
                } catch (Throwable $exception) {
                    $syncWarnings[] = 'Roster ' . $course['name'] . ': ' . $exception->getMessage();
                }

                $averages = array_filter(array_map(
                    static fn (array $assignment): ?float => $assignment['grading_results_avg_score_ai'] !== null ? (float) $assignment['grading_results_avg_score_ai'] : null,
                    $courseAssignments
                ), static fn (?float $value): bool => $value !== null);

                return array_merge($course, [
                    'students' => $students,
                    'assignments' => $courseWork,
                    'classroom_assignment_count' => is_array($courseWork) ? count($courseWork) : null,
                    'local_assignment_count' => count($courseAssignments),
                    'average' => $averages ? round(array_sum($averages) / count($averages), 1) : null,
                    'last_graded' => $courseAssignments[0]['updated_at'] ?? null,
                    'sync_warnings' => $syncWarnings,
                ]);
            }, $this->google->listCourses($user));
        } catch (Throwable $exception) {
            $courses = [];
            $notice = 'Google Classroom belum bisa diakses, jadi data kelas dikosongkan sampai sinkronisasi berhasil. Coba login ulang Google atau cek scope OAuth.';
            $this->auditLogs->create((int) $user['id'], 'classroom.list_courses', 'google_classroom', 'FAILED', $exception->getMessage());
        }

        if (! $notice && $syncWarnings !== []) {
            $notice = 'Sebagian data Classroom belum bisa disinkronkan. Data yang tidak tersedia ditampilkan sebagai tanda "-". Detail teknis tersimpan di audit log.';
            $this->auditLogs->create((int) $user['id'], 'classroom.list_courses', 'google_classroom', 'INFO', str_limit(implode(' | ', array_unique($syncWarnings)), 500));
        }

        View::render('courses/index', [
            'title' => 'Kelas Saya - AutoGrade AI',
            'pageTitle' => 'Kelas Saya',
            'pageCaption' => 'Sinkronisasi Google Classroom dan tugas lokal.',
            'courses' => $courses,
            'notice' => $notice,
        ]);
    }

    public function show(Request $request, string $courseId): void
    {
        $this->validateCourseId($courseId);

        $user = $request->user();
        $notice = null;
        $assignments = array_values(array_filter(
            $this->localAssignments($request),
            static fn (array $assignment): bool => (string) $assignment['course_id'] === $courseId
        ));
        $courseWorks = null;
        $studentCount = null;

        try {
            $courses = $this->google->listCourses($user);
            $course = null;

            foreach ($courses as $item) {
                if ((string) $item['id'] === $courseId) {
                    $course = $item;
                    break;
                }
            }

            if (! $course) {
                abort_response(404, 'Kelas tidak ditemukan.');
            }

            try {
                $courseWorks = $this->google->listCourseWork($user, $courseId);
            } catch (Throwable $exception) {
                $notice = 'Daftar tugas Classroom belum bisa dimuat. Menampilkan tugas lokal AutoGrade jika ada.';
                $this->auditLogs->create((int) $user['id'], 'classroom.list_coursework', 'course:' . $courseId, 'FAILED', $exception->getMessage());
            }

            try {
                $studentCount = $this->google->countCourseStudents($user, $courseId);
            } catch (Throwable $exception) {
                $notice = $notice ?: 'Roster mahasiswa Classroom belum bisa dimuat. Jumlah mahasiswa ditampilkan sebagai "-".';
                $this->auditLogs->create((int) $user['id'], 'classroom.list_students', 'course:' . $courseId, 'FAILED', $exception->getMessage());
            }
        } catch (Throwable $exception) {
            if ($assignments === []) {
                throw $exception;
            }

            $course = [
                'id' => $courseId,
                'name' => $courseId,
                'section' => 'Data lokal AutoGrade',
                'alternateLink' => null,
                'source' => 'local',
            ];
            $notice = 'Google Classroom belum bisa diakses. Menampilkan data lokal AutoGrade yang sudah tersimpan.';
            $this->auditLogs->create((int) $user['id'], 'classroom.show_course', 'course:' . $courseId, 'FAILED', $exception->getMessage());
        }

        $course['students'] = $studentCount;
        $course['assignments'] = $courseWorks;

        View::render('courses/show', [
            'title' => 'Detail Kelas - AutoGrade AI',
            'pageTitle' => 'Detail Kelas',
            'pageCaption' => (string) ($course['name'] ?? $courseId),
            'course' => $course,
            'assignments' => $assignments,
            'courseWorks' => $courseWorks,
            'notice' => $notice,
        ]);
    }

    public function coursework(Request $request, string $courseId): never
    {
        $this->validateCourseId($courseId);

        try {
            $courseIds = array_map(static fn (array $course): string => (string) $course['id'], $this->google->listCourses($request->user()));

            if (! in_array($courseId, $courseIds, true)) {
                json_response(['message' => 'Anda tidak berhak mengakses course ini.'], 403);
            }

            json_response([
                'course_id' => $courseId,
                'assignments' => $this->google->listCourseWork($request->user(), $courseId),
            ]);
        } catch (Throwable $exception) {
            $this->auditLogs->create((int) $request->user()['id'], 'classroom.list_coursework', 'course:' . $courseId, 'FAILED', $exception->getMessage());
            json_response(['message' => 'Coursework belum bisa dimuat.'], 502);
        }
    }

    private function localAssignments(Request $request): array
    {
        return $this->assignments->allForTeacherWithStats((int) $request->user()['id']);
    }

    private function groupByCourse(array $assignments): array
    {
        $grouped = [];

        foreach ($assignments as $assignment) {
            $grouped[(string) $assignment['course_id']][] = $assignment;
        }

        return $grouped;
    }

    private function validateCourseId(string $courseId): void
    {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $courseId)) {
            abort_response(404, 'Kelas tidak ditemukan.');
        }
    }
}
