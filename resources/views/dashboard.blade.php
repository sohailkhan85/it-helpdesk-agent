@extends('layouts.app')

@section('content')
@php $color = config('branding.primary_color'); @endphp

{{-- Stats Bar --}}
<div class="max-w-6xl mx-auto px-6 py-6 grid grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl shadow p-5 text-center">
        <div class="text-3xl font-bold" style="color: {{ $color }}">{{ $leads->count() }}</div>
        <div class="text-gray-500 text-sm mt-1">Total Leads</div>
    </div>
    <div class="bg-white rounded-2xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-green-600">
            {{ $leads->where('created_at', '>=', now()->startOfDay())->count() }}
        </div>
        <div class="text-gray-500 text-sm mt-1">Today's Leads</div>
    </div>
    <div class="bg-white rounded-2xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-purple-600">
            {{ $leads->where('created_at', '>=', now()->startOfWeek())->count() }}
        </div>
        <div class="text-gray-500 text-sm mt-1">This Week</div>
    </div>
</div>

{{-- Leads Table --}}
<div class="max-w-6xl mx-auto px-6 pb-10">
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        {{-- Table Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-lg">All Leads</h2>
            <a href="/dashboard/export"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                Export CSV
            </a>
        </div>

        @if($leads->count() > 0)
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Issue</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($leads as $lead)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm text-gray-400">{{ $lead->id }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm text-white"
                                 style="background-color: {{ $color }}">
                                {{ strtoupper(substr($lead->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-800 text-sm">{{ $lead->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-blue-600">
                        <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                        <div class="truncate" title="{{ $lead->issue }}">
                            {{ $lead->issue ?? 'No issue provided' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-400 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($lead->created_at)->format('M d, Y h:i A') }}
                    </td>
<td class="px-6 py-4 flex gap-2">
    <a href="mailto:{{ $lead->email }}?subject=Following up on your IT issue"
        class="text-white px-3 py-1 rounded-lg text-xs font-medium transition hover:opacity-80"
        style="background-color: {{ $color }}">
        Follow Up
    </a>

    @if($lead->transcript)
    <button onclick="showTranscript({{ $lead->id }})"
        class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded-lg text-xs font-medium transition">
        View Chat
    </button>
    @endif
</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-16 text-gray-400">
            <div class="text-5xl mb-4">📭</div>
            <div class="text-lg font-medium">No leads yet</div>
            <div class="text-sm mt-2">Share your chat agent link to start capturing leads</div>
            <a href="/helpdesk"
               class="mt-4 inline-block text-white px-6 py-2 rounded-xl text-sm font-medium"
               style="background-color: {{ $color }}">
                Open Chat Agent
            </a>
        </div>
        @endif

    </div>
</div>
{{-- Transcript Modal --}}
<div id="transcript-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[80vh] flex flex-col">
        <div class="px-6 py-4 border-b flex items-center justify-between"
             style="background-color: {{ $color }}">
            <h3 class="font-bold text-white">💬 Conversation Transcript</h3>
            <button onclick="closeTranscript()" class="text-white opacity-75 hover:opacity-100 text-xl">✕</button>
        </div>
        <div id="transcript-content" class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
        </div>
    </div>
</div>

{{-- Transcript Data --}}
<script>
const transcripts = {
    @foreach($leads as $lead)
        @if($lead->transcript)
        {{ $lead->id }}: {!! $lead->transcript !!},
        @endif
    @endforeach
};

function showTranscript(id) {
    const messages = transcripts[id] || [];
    const container = document.getElementById('transcript-content');
    container.innerHTML = '';

    messages.forEach(msg => {
        const isUser = msg.role === 'user';
        const div = document.createElement('div');
        div.style.cssText = `
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            max-width: 85%;
            margin-left: ${isUser ? 'auto' : '0'};
            background: ${isUser ? '{{ $color }}' : '#f3f4f6'};
            color: ${isUser ? 'white' : '#1f2937'};
            margin-bottom: 8px;
        `;
        div.textContent = msg.content;
        container.appendChild(div);
    });

    document.getElementById('transcript-modal').classList.remove('hidden');
}

function closeTranscript() {
    document.getElementById('transcript-modal').classList.add('hidden');
}
</script>
@endsection