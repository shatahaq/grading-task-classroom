<?php

declare(strict_types=1);

use App\Controllers\AssignmentController;
use App\Controllers\AuthController;
use App\Controllers\CourseController;
use App\Controllers\DashboardController;
use App\Controllers\GradingController;
use App\Controllers\N8nTokenController;

$router->get('home', '/', [AuthController::class, 'welcome']);
$router->get('login', '/login', [AuthController::class, 'redirect'], ['guest']);
$router->get('auth.google.redirect', '/auth/google', [AuthController::class, 'redirect'], ['guest']);
$router->get('auth.google.callback', '/auth/google/callback', [AuthController::class, 'callback'], ['guest']);

$router->post('logout', '/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);
$router->post('auth.google.disconnect', '/auth/google/disconnect', [AuthController::class, 'disconnect'], ['auth', 'csrf']);

$router->get('dashboard', '/dashboard', [DashboardController::class, 'index'], ['auth']);
$router->get('courses.index', '/courses', [CourseController::class, 'index'], ['auth']);
$router->get('courses.show', '/courses/{courseId}', [CourseController::class, 'show'], ['auth']);
$router->get('courses.coursework', '/courses/{courseId}/coursework', [CourseController::class, 'coursework'], ['auth']);

$router->get('assignments.create', '/assignments/create', [AssignmentController::class, 'create'], ['auth']);
$router->post('assignments.store', '/assignments', [AssignmentController::class, 'store'], ['auth', 'csrf']);
$router->delete('assignments.destroy', '/assignments/{assignmentId}', [AssignmentController::class, 'destroy'], ['auth', 'csrf']);
$router->get('assignments.grading', '/assignments/{assignmentId}/grading', [GradingController::class, 'show'], ['auth']);
$router->post('assignments.grading.trigger', '/assignments/{assignmentId}/grading/trigger', [GradingController::class, 'trigger'], ['auth', 'csrf']);

$router->post('api.n8n.google-access-token', '/api/n8n/google-access-token', [N8nTokenController::class, 'show']);
