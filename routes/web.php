<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiAgentController;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/helpdesk', [AiAgentController::class, 'index']);
Route::get('/dashboard', [AiAgentController::class, 'dashboard']);
Route::get('/dashboard/export', [AiAgentController::class, 'export']);
Route::get('/', [AiAgentController::class, 'home']);
