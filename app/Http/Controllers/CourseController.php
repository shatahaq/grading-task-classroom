<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\GoogleWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class CourseController extends Controller
{
    public function index(Request $request, GoogleWorkspaceService $google)
    {
        $notice = null;
        $syncWarnings = [];
        $user = $request->user();
        $localAssignments = $this->localAssignments($request);
        $localAssignmentsByCourse = $localAssignments->groupBy('course_id');

        try {
            $courses = collect($google->listCourses($user))
                ->map(function (array $course) use ($google, $user, $localAssignmentsByCourse, &$syncWarnings) {
                    $courseAssignments = $localAssignmentsByCourse->get($course['id'], collect());
                    $courseWork = null;
                    $students = null;

                    try {
                        $courseWork = $google->listCourseWork($user, $course['id']);
                    } catch (Throwable $exception) {
                        $syncWarnings[] = "Coursework {$course['name']}: ".$exception->getMessage();
                    }

                    try {
                        $students = $google->countCourseStudents($user, $course['id']);
                    } catch (Throwable $exception) {
                        $syncWarnings[] = "Roster {$course['name']}: ".$exception->getMessage();
                    }

                    $average = $courseAssignments
                        ->filter(fn ($assignment) => $assignment->grading_results_avg_score_ai !== null)
                        ->avg('grading_results_avg_score_ai');

                    return array_merge($course, [
                        'students' => $students,
                        'assignments' => $courseWork,
                        'classroom_assignment_count' => is_array($courseWork) ? count($courseWork) : null,
                        'local_assignment_count' => $courseAssignments->count(),
                        'average' => $average !== null ? round((float) $average, 1) : null,
                        'last_graded' => $courseAssignments->first()?->updated_at?->diffForHumans(),
                        'sync_warnings' => $syncWarnings,
                    ]);
                })
                ->all();
        } catch (Throwable $exception) {
            $courses = [];
            $notice = 'Google Classroom belum bisa diakses, jadi data kelas dikosongkan sampai sinkronisasi berhasil. Coba login ulang Google atau cek scope OAuth.';

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'classroom.list_courses',
                'resource' => 'google_classroom',
                'status' => 'FAILED',
                'message' => $exception->getMessage(),
            ]);
        }

        if (! $notice && $syncWarnings !== []) {
            $notice = 'Sebagian data Classroom belum bisa disinkronkan. Data yang tidak tersedia ditampilkan sebagai tanda "-". Detail teknis tersimpan di audit log.';

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'classroom.list_courses',
                'resource' => 'google_classroom',
                'status' => 'INFO',
                'message' => Str::limit(implode(' | ', array_unique($syncWarnings)), 500),
            ]);
        }

        return view('courses.index', [
            'courses' => $courses,
            'notice' => $notice,
        ]);
    }

    public function show(Request $request, GoogleWorkspaceService $google, string $courseId)
    {
        $notice = null;
        $user = $request->user();
        $assignments = $this->localAssignments($request)
            ->where('course_id', $courseId)
            ->values();
        $courseWorks = null;
        $studentCount = null;

        try {
            $course = collect($google->listCourses($user))->firstWhere('id', $courseId);

            abort_unless($course, 404);

            try {
                $courseWorks = $google->listCourseWork($user, $courseId);
            } catch (Throwable $exception) {
                $notice = 'Daftar tugas Classroom belum bisa dimuat. Menampilkan tugas lokal AutoGrade jika ada.';

                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'classroom.list_coursework',
                    'resource' => 'course:'.$courseId,
                    'status' => 'FAILED',
                    'message' => $exception->getMessage(),
                ]);
            }

            try {
                $studentCount = $google->countCourseStudents($user, $courseId);
            } catch (Throwable $exception) {
                $notice = $notice ?: 'Roster mahasiswa Classroom belum bisa dimuat. Jumlah mahasiswa ditampilkan sebagai "-".';

                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'classroom.list_students',
                    'resource' => 'course:'.$courseId,
                    'status' => 'FAILED',
                    'message' => $exception->getMessage(),
                ]);
            }
        } catch (Throwable $exception) {
            if ($assignments->isEmpty()) {
                throw $exception;
            }

            $course = [
                'id' => $courseId,
                'name' => $courseId,
                'section' => 'Data lokal AutoGrade',
                'alternateLink' => null,
                'students' => null,
                'source' => 'local',
            ];
            $notice = 'Google Classroom belum bisa diakses. Menampilkan data lokal AutoGrade yang sudah tersimpan.';

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'classroom.show_course',
                'resource' => 'course:'.$courseId,
                'status' => 'FAILED',
                'message' => $exception->getMessage(),
            ]);
        }

        $course['students'] = $studentCount;
        $course['assignments'] = $courseWorks;

        return view('courses.show', [
            'course' => $course,
            'assignments' => $assignments,
            'courseWorks' => $courseWorks,
            'notice' => $notice,
        ]);
    }

    public function coursework(Request $request, GoogleWorkspaceService $google, string $courseId)
    {
        try {
            $teacherCourseIds = collect($google->listCourses($request->user()))->pluck('id');

            abort_unless($teacherCourseIds->contains($courseId), 403);

            return response()->json([
                'course_id' => $courseId,
                'assignments' => $google->listCourseWork($request->user(), $courseId),
            ]);
        } catch (Throwable $exception) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'classroom.list_coursework',
                'resource' => 'course:'.$courseId,
                'status' => 'FAILED',
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Coursework belum bisa dimuat.',
            ], 502);
        }
    }

    private function localAssignments(Request $request): Collection
    {
        return $request->user()->assignments()
            ->withCount('gradingResults')
            ->withCount([
                'gradingResults as approved_results_count' => fn ($query) => $query->where('status', 'APPROVED'),
                'gradingResults as review_results_count' => fn ($query) => $query->where('status', 'REVIEW'),
            ])
            ->withAvg('gradingResults', 'score_ai')
            ->latest()
            ->get();
    }
}
