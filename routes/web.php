<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/login', [AuthController::class, 'redirect'])->name('login');
Route::get('/auth/google', [AuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/auth/google/disconnect', [AuthController::class, 'disconnect'])->name('auth.google.disconnect');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{courseId}', [CourseController::class, 'show'])
        ->where('courseId', '[A-Za-z0-9._-]+')
        ->name('courses.show');
    Route::get('/courses/{courseId}/coursework', [CourseController::class, 'coursework'])
        ->where('courseId', '[A-Za-z0-9._-]+')
        ->name('courses.coursework');

    Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::get('/assignments/{assignment}/grading', [GradingController::class, 'show'])->name('assignments.grading');
    Route::post('/assignments/{assignment}/grading/trigger', [GradingController::class, 'trigger'])->name('assignments.grading.trigger');
});
