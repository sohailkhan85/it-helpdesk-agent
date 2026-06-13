<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\PdfChatController;



Route::get('/', function () {
    return view('welcome');
});


Route::get('/pdfchat', [PdfChatController::class, 'index']);

Route::get('/helpdesk', [AiAgentController::class, 'index']);

Route::get('/', [AiAgentController::class, 'home']);


// Protected dashboard routes
Route::middleware('dashboard.auth')->group(function () {
    Route::get('/dashboard', [AiAgentController::class, 'dashboard']);
    Route::get('/dashboard/export', [AiAgentController::class, 'export']);
    Route::post('/dashboard/login', [AiAgentController::class, 'dashboard']);
});

// Login route (no middleware)
Route::post('/dashboard/login', function () {
    return redirect('/dashboard');
});

// Dashboard login POST handler
Route::post('/dashboard/login', function (\Illuminate\Http\Request $request) {
    if ($request->input('password') === env('DASHBOARD_PASSWORD')) {
        session(['dashboard_authenticated' => true]);
        return redirect('/dashboard');
    }
    return back()->with('error', 'Invalid password. Please try again.');
});

