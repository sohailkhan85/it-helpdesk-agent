<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiAgentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/ask-ai', [AiAgentController::class, 'ask']);
Route::post('/save-lead', [AiAgentController::class, 'saveLead']);
Route::post('/update-transcript', [AiAgentController::class, 'updateTranscript']);
