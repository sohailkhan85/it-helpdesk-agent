@extends('layouts.app')

@section('content')
@php $color = config('branding.primary_color'); @endphp

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">

        {{-- Icon --}}
        <div class="text-center mb-8">
            <div class="text-5xl mb-3">🔐</div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Access</h1>
            <p class="text-gray-500 text-sm mt-2">Enter your password to view leads</p>
        </div>

        {{-- Error Message --}}
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm mb-4">
            ⚠️ {{ session('error') }}
        </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="/dashboard/login">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter dashboard password"
                    autofocus
                    class="w-full mt-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="w-full text-white py-3 rounded-xl font-medium transition hover:opacity-90 text-sm"
                style="background-color: {{ $color }}">
                🔓 Access Dashboard
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-4">
            Protected area — authorized personnel only
        </p>

    </div>
</div>

@endsection