<?php

use App\Http\Controllers\N8nTokenController;
use Illuminate\Support\Facades\Route;

Route::post('/n8n/google-access-token', [N8nTokenController::class, 'show'])
    ->name('api.n8n.google-access-token');
