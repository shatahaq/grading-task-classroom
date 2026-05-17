<?php

namespace App\Http\Controllers;

use App\Models\GradingResult;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $assignments = $user->assignments()
            ->withCount('gradingResults')
            ->withCount([
                'gradingResults as approved_results_count' => fn ($query) => $query->where('status', 'APPROVED'),
                'gradingResults as review_results_count' => fn ($query) => $query->where('status', 'REVIEW'),
            ])
            ->withAvg('gradingResults', 'score_ai')
            ->latest()
            ->get();

        $assignmentIds = $assignments->pluck('id');
        $results = GradingResult::with('assignment')
            ->whereIn('assignment_id', $assignmentIds)
            ->latest()
            ->get();

        $gradeDistribution = [
            'A' => $results->filter(fn ($result) => (float) $result->score_ai >= 85)->count(),
            'B' => $results->filter(fn ($result) => (float) $result->score_ai >= 70 && (float) $result->score_ai < 85)->count(),
            'C' => $results->filter(fn ($result) => (float) $result->score_ai >= 55 && (float) $result->score_ai < 70)->count(),
            'D' => $results->filter(fn ($result) => (float) $result->score_ai >= 40 && (float) $result->score_ai < 55)->count(),
            'E' => $results->filter(fn ($result) => $result->score_ai !== null && (float) $result->score_ai < 40)->count(),
        ];

        $classSummary = $assignments
            ->groupBy('course_id')
            ->map(function ($courseAssignments, string $courseId) use ($results) {
                $courseResults = $results->filter(fn ($result) => $result->assignment?->course_id === $courseId);
                $average = $courseResults->filter(fn ($result) => $result->score_ai !== null)->avg('score_ai');

                return [
                    'id' => $courseId,
                    'name' => $this->courseName($courseId),
                    'students' => max($courseResults->pluck('student_id')->unique()->count(), 0),
                    'active_assignments' => $courseAssignments->count(),
                    'average' => $average ? round($average, 1) : 0,
                ];
            })
            ->values()
            ->take(3);

        return view('dashboard', [
            'stats' => [
                'courses' => $assignments->pluck('course_id')->unique()->count(),
                'assignments' => $assignments->count(),
                'approved' => $results->where('status', 'APPROVED')->count(),
                'review' => $results->where('status', 'REVIEW')->count(),
            ],
            'recentAssignments' => $assignments->take(5),
            'recentResults' => $results->take(4),
            'gradeDistribution' => $gradeDistribution,
            'classSummary' => $classSummary,
            'tokenStatus' => $user->oauthToken && ! $user->oauthToken->revoked ? 'Tersambung' : 'Belum tersambung',
        ]);
    }

    private function courseName(string $courseId): string
    {
        return $courseId;
    }
}
