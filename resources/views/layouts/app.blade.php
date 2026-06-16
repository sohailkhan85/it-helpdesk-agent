<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('branding.company_name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('scripts_head')
</head>
<body class="bg-gray-100 min-h-screen">

{{-- Top Navigation --}}
@php $color = config('branding.primary_color'); @endphp
<nav style="background-color: {{ $color }}" class="text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        {{-- Logo & Brand --}}
        <div class="flex items-center gap-3">
            <div class="text-2xl">{{ config('branding.logo_emoji') }}</div>
            <div>
                <div class="font-bold text-lg leading-tight">{{ config('branding.company_name') }}</div>
                <div class="text-xs opacity-75">{{ config('branding.tagline') }}</div>
            </div>
        </div>

        {{-- Nav Links --}}
        <div class="flex items-center gap-2">
            <a href="/"
                class="px-4 py-2 rounded-xl text-sm font-medium transition
                {{ request()->is('/') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                🏠 Home
            </a>
            <a href="/helpdesk"
                class="px-4 py-2 rounded-xl text-sm font-medium transition
                {{ request()->is('helpdesk') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                💬 Chat Support
            </a>
            <a href="/dashboard"
                class="px-4 py-2 rounded-xl text-sm font-medium transition
                {{ request()->is('dashboard') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                📊 Dashboard
            </a>
            <a href="/pdfchat"
                class="px-4 py-2 rounded-xl text-sm font-medium transition
                {{ request()->is('pdfchat') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
                📄 PDF Chat
            </a>
            <a href="/websiteai"
    class="px-4 py-2 rounded-xl text-sm font-medium transition
    {{ request()->is('websiteai') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }}">
    🌐 Website AI
            </a>
        </div>
        

        {{-- Status Badge --}}
        <div class="flex items-center gap-2 bg-white bg-opacity-10 px-3 py-1 rounded-full">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-xs font-medium">AI Online</span>
        </div>

    </div>
</nav>

{{-- Page Content --}}
<main>
    @yield('content')
</main>

{{-- Footer --}}
<footer class="text-center text-gray-400 text-xs py-6">
    © {{ date('Y') }} {{ config('branding.company_name') }} — Powered by AI
</footer>

@stack('scripts')
</body>
</html>