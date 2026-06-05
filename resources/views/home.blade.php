@extends('layouts.app')

@section('content')
@php $color = config('branding.primary_color'); @endphp

{{-- Hero Section --}}
<div style="background: linear-gradient(135deg, {{ $color }}, #1e3a8a)" class="text-white py-20 px-6 text-center">
    <div class="text-6xl mb-4">{{ config('branding.logo_emoji') }}</div>
    <h1 class="text-4xl font-bold mb-4">{{ config('branding.company_name') }}</h1>
    <p class="text-xl opacity-80 mb-8 max-w-xl mx-auto">{{ config('branding.tagline') }}</p>
    <div class="flex gap-4 justify-center">
        <a href="/helpdesk"
            class="bg-white font-bold px-8 py-3 rounded-xl text-sm transition hover:opacity-90"
            style="color: {{ $color }}">
            💬 Start AI Chat Support
        </a>
        <a href="/dashboard"
            class="bg-white bg-opacity-20 text-white font-bold px-8 py-3 rounded-xl text-sm transition hover:bg-opacity-30">
            📊 View Dashboard
        </a>
    </div>
</div>

{{-- Features Section --}}
<div class="max-w-5xl mx-auto px-6 py-16">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-10">Why Use Our AI Helpdesk?</h2>
    <div class="grid grid-cols-3 gap-6">

        <div class="bg-white rounded-2xl shadow p-6 text-center hover:shadow-md transition">
            <div class="text-4xl mb-3">⚡</div>
            <h3 class="font-bold text-gray-800 mb-2">Instant Response</h3>
            <p class="text-gray-500 text-sm">Get answers to your IT issues in seconds, 24 hours a day, 7 days a week.</p>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 text-center hover:shadow-md transition">
            <div class="text-4xl mb-3">🧠</div>
            <h3 class="font-bold text-gray-800 mb-2">Expert Knowledge</h3>
            <p class="text-gray-500 text-sm">Trained on networking, surveillance, fiber optics and IT infrastructure.</p>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 text-center hover:shadow-md transition">
            <div class="text-4xl mb-3">📋</div>
            <h3 class="font-bold text-gray-800 mb-2">Lead Tracking</h3>
            <p class="text-gray-500 text-sm">Every conversation is tracked so your team can follow up and close deals.</p>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 text-center hover:shadow-md transition">
            <div class="text-4xl mb-3">🔒</div>
            <h3 class="font-bold text-gray-800 mb-2">Secure & Private</h3>
            <p class="text-gray-500 text-sm">Your data stays safe. We never share your information with third parties.</p>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 text-center hover:shadow-md transition">
            <div class="text-4xl mb-3">🌍</div>
            <h3 class="font-bold text-gray-800 mb-2">Always Available</h3>
            <p class="text-gray-500 text-sm">No waiting queues, no hold music. AI support available around the clock.</p>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 text-center hover:shadow-md transition">
            <div class="text-4xl mb-3">📊</div>
            <h3 class="font-bold text-gray-800 mb-2">Analytics Dashboard</h3>
            <p class="text-gray-500 text-sm">Track all support requests, leads and follow-ups from one place.</p>
        </div>

    </div>
</div>

{{-- CTA Section --}}
<div class="bg-white py-12 px-6 text-center border-t border-gray-100">
    <h2 class="text-2xl font-bold text-gray-800 mb-3">Ready to get IT support?</h2>
    <p class="text-gray-500 mb-6">Our AI assistant is online and ready to help you right now.</p>
    <a href="/helpdesk"
        class="text-white font-bold px-10 py-3 rounded-xl text-sm transition hover:opacity-90 inline-block"
        style="background-color: {{ $color }}">
        💬 Chat with AI Support Now
    </a>
</div>

@endsection