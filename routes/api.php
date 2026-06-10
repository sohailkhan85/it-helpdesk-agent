<?php

use App\Http\Controllers\AiAgentController;
use Illuminate\Support\Facades\Route;

// 5 messages per day per IP
Route::middleware(['throttle:5,1440'])->group(function () {
    Route::post('/ask-ai', [AiAgentController::class, 'ask']);
});

// 3 lead submissions per day per IP
Route::middleware(['throttle:3,1440'])->group(function () {
    Route::post('/save-lead', [AiAgentController::class, 'saveLead']);
    Route::post('/update-transcript', [AiAgentController::class, 'updateTranscript']);
});