<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if already authenticated
        if (session('dashboard_authenticated')) {
            return $next($request);
        }

        // Handle login form submission
        if ($request->isMethod('post')) {
            if ($request->input('password') === env('DASHBOARD_PASSWORD')) {
                session(['dashboard_authenticated' => true]);
                return redirect('/dashboard');
            }
            return back()->with('error', 'Invalid password. Please try again.');
        }

        // Show login page
        return response()->view('dashboard-login');
    }
}
