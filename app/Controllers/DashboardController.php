<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Repositories\AssignmentRepository;
use App\Repositories\GradingResultRepository;
use App\Repositories\OauthTokenRepository;
use App\Services\GoogleWorkspaceService;
use Throwable;

final class DashboardController
{
    public function index(Request $request): void
    {
        $user = $request->user();
        $assignments = (new AssignmentRepository())->allForTeacherWithStats((int) $user['id']);
        $assignmentIds = array_map(static fn (array $assignment): int => (int) $assignment['id'], $assignments);
        $results = (new GradingResultRepository())->forAssignments($assignmentIds);

        // Build course name map from Google Classroom
        $courseNameMap = [];

        try {
            $courses = (new GoogleWorkspaceService())->listCourses($user);

            foreach ($courses as $course) {
                $courseNameMap[(string) $course['id']] = $course['name'];
            }
        } catch (Throwable) {
            // Fallback: course names will show as course_id
        }

        // Enrich assignments with course_name
        $assignments = array_map(static function (array $assignment) use ($courseNameMap): array {
            $assignment['course_name'] = $courseNameMap[(string) $assignment['course_id']] ?? (string) $assignment['course_id'];

            return $assignment;
        }, $assignments);

        $gradeDistribution = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
            'E' => 0,
        ];

        foreach ($results as $result) {
            if ($result['score_ai'] === null) {
                continue;
            }

            $score = (float) $result['score_ai'];
            $gradeDistribution[$score >= 85 ? 'A' : ($score >= 70 ? 'B' : ($score >= 55 ? 'C' : ($score >= 40 ? 'D' : 'E')))]++;
        }

        $classSummary = $this->classSummary($assignments, $results, $courseNameMap);
        $token = (new OauthTokenRepository())->findByUserId((int) $user['id']);

        View::render('dashboard/index', [
            'title' => 'Dashboard - AutoGrade AI',
            'pageTitle' => 'Dashboard',
            'pageCaption' => 'Ruang kontrol grading otomatis.',
            'stats' => [
                'courses' => count(array_unique(array_column($assignments, 'course_id'))),
                'assignments' => count($assignments),
                'approved' => count(array_filter($results, static fn (array $result): bool => $result['status'] === 'APPROVED')),
                'review' => count(array_filter($results, static fn (array $result): bool => $result['status'] === 'REVIEW')),
            ],
            'recentAssignments' => array_slice($assignments, 0, 5),
            'recentResults' => array_slice($results, 0, 4),
            'gradeDistribution' => $gradeDistribution,
            'classSummary' => array_slice($classSummary, 0, 3),
            'tokenStatus' => $token && (int) ($token['revoked'] ?? 0) === 0 ? 'Tersambung' : 'Belum tersambung',
        ]);
    }

    private function classSummary(array $assignments, array $results, array $courseNameMap = []): array
    {
        $grouped = [];

        foreach ($assignments as $assignment) {
            $courseId = (string) $assignment['course_id'];
            $grouped[$courseId]['assignments'][] = $assignment;
        }

        foreach ($results as $result) {
            $courseId = (string) $result['course_id'];
            $grouped[$courseId]['results'][] = $result;
        }

        $summary = [];

        foreach ($grouped as $courseId => $data) {
            $courseResults = $data['results'] ?? [];
            $scores = array_values(array_filter(array_map(
                static fn (array $result): ?float => $result['score_ai'] !== null ? (float) $result['score_ai'] : null,
                $courseResults
            ), static fn (?float $score): bool => $score !== null));
            $studentIds = array_unique(array_map(static fn (array $result): string => (string) $result['student_id'], $courseResults));

            $summary[] = [
                'id' => $courseId,
                'name' => $courseNameMap[$courseId] ?? $courseId,
                'students' => count($studentIds),
                'active_assignments' => count($data['assignments'] ?? []),
                'average' => $scores ? round(array_sum($scores) / count($scores), 1) : 0,
            ];
        }

        return $summary;
    }
}
