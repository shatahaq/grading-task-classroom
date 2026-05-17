<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Services\GoogleWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class AssignmentController extends Controller
{
    public function create(Request $request, GoogleWorkspaceService $google)
    {
        $notice = null;

        try {
            $courses = $google->listCourses($request->user());
        } catch (Throwable $exception) {
            $courses = [];
            $notice = 'Google Classroom belum bisa diakses, jadi daftar kelas dikosongkan sampai sinkronisasi berhasil. Login ulang Google atau cek koneksi Classroom. '.$exception->getMessage();
        }

        return view('assignments.create', [
            'courses' => $courses,
            'selectedCourse' => $request->query('course_id'),
            'notice' => $notice,
        ]);
    }

    public function store(Request $request, GoogleWorkspaceService $google)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'max_score' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'question_file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,png,jpg,jpeg'],
            'rubric_file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,png,jpg,jpeg'],
            'answer_key_file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,png,jpg,jpeg'],
            'grade_mode' => ['required', 'in:none,draft,final'],
            'min_answer_length' => ['required', 'integer', 'min:0', 'max:10000'],
            'due_date' => ['nullable', 'date', 'after:now'],
        ]);

        try {
            $localRoot = 'assignment-files/'.Str::uuid();
            $localPaths = [
                'question' => $request->file('question_file')->store($localRoot.'/question', 'public'),
                'rubric' => $request->file('rubric_file')->store($localRoot.'/rubric', 'public'),
                'answer_key' => $request->file('answer_key_file')->store($localRoot.'/answer-key', 'public'),
            ];

            $driveUpload = $google->uploadAssignmentFiles($request->user(), [
                'question' => $request->file('question_file'),
                'rubric' => $request->file('rubric_file'),
                'answer_key' => $request->file('answer_key_file'),
            ]);

            $courseWorkData = [
                'course_id' => $validated['course_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'max_score' => $validated['max_score'],
                'drive_files' => $driveUpload['files'],
            ];

            if (! empty($validated['due_date'])) {
                $courseWorkData['due_date'] = $validated['due_date'];
                $courseWorkData['close_on_due'] = $request->boolean('close_on_due');
            }

            $courseWork = $google->createCourseWork($request->user(), $courseWorkData);
        } catch (Throwable $exception) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'assignment.create',
                'resource' => 'google_classroom',
                'status' => 'FAILED',
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['google' => 'Gagal membuat tugas di Google Classroom/Drive: '.$exception->getMessage()]);
        }

        $assignment = Assignment::create([
            'teacher_id' => $request->user()->id,
            'course_id' => $validated['course_id'],
            'coursework_id' => $courseWork['id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'max_score' => $validated['max_score'],
            'file_id' => $driveUpload['files']['rubric']['id'] ?? null,
            'classroom_link' => $courseWork['alternateLink'] ?? null,
            'drive_folder_id' => $driveUpload['folder_id'] ?? null,
            'question_drive_file_id' => $driveUpload['files']['question']['id'] ?? null,
            'rubric_drive_file_id' => $driveUpload['files']['rubric']['id'] ?? null,
            'answer_key_drive_file_id' => $driveUpload['files']['answer_key']['id'] ?? null,
            'question_local_path' => $localPaths['question'],
            'rubric_local_path' => $localPaths['rubric'],
            'answer_key_local_path' => $localPaths['answer_key'],
            'auto_approval' => $request->boolean('auto_approval'),
            'auto_email' => $request->boolean('auto_email'),
            'grade_mode' => $validated['grade_mode'],
            'min_answer_length' => $validated['min_answer_length'],
            'due_date' => $validated['due_date'] ?? null,
            'close_on_due' => $request->boolean('close_on_due'),
            'status' => 'ready',
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'assignment.create',
            'resource' => 'assignment:'.$assignment->id,
            'status' => 'SUCCESS',
            'message' => 'Assignment dibuat, file diunggah ke Drive, dan CourseWork dibuat di Classroom.',
        ]);

        return redirect()
            ->route('assignments.grading', $assignment)
            ->with('status', 'Assignment berhasil dibuat.');
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        $title = $assignment->title;

        // Delete related grading results
        $assignment->gradingResults()->delete();

        // Delete local files if they exist
        foreach (['question_local_path', 'rubric_local_path', 'answer_key_local_path'] as $pathField) {
            if ($assignment->$pathField && \Storage::disk('public')->exists($assignment->$pathField)) {
                \Storage::disk('public')->delete($assignment->$pathField);
            }
        }

        $assignment->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'assignment.delete',
            'resource' => 'assignment',
            'status' => 'SUCCESS',
            'message' => "Assignment \"{$title}\" berhasil dihapus dari dashboard.",
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', "Assignment \"{$title}\" berhasil dihapus.");
    }

}
