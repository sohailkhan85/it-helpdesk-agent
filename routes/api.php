<?php

use App\Http\Controllers\AiAgentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfChatController;

// For Local

    Route::post('/ask-ai', [AiAgentController::class, 'ask']);


// 5 messages per day per IP - For Production
/*
Route::middleware(['throttle:5,1440'])->group(function () {
    Route::post('/ask-ai', [AiAgentController::class, 'ask']);
});
*/

// 3 lead submissions per day per IP
/*
Route::middleware(['throttle:3,1440'])->group(function () {
    Route::post('/save-lead', [AiAgentController::class, 'saveLead']);
    Route::post('/update-transcript', [AiAgentController::class, 'updateTranscript']);
});
*/

Route::post('/save-lead', [AiAgentController::class, 'saveLead']);
Route::post('/update-transcript', [AiAgentController::class, 'updateTranscript']);


/* For Prodiction - 10 PDF uploads/questions per day per IP

Route::middleware(['throttle:10,1440'])->group(function () {
    Route::post('/pdf-upload', [PdfChatController::class, 'upload']);
    Route::post('/pdf-ask', [PdfChatController::class, 'ask']);
});
*/

    Route::post('/pdf-upload', [PdfChatController::class, 'upload']);
    Route::post('/pdf-ask', [PdfChatController::class, 'ask']);