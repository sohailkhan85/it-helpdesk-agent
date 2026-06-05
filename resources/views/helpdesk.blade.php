@extends('layouts.app')

@push('scripts_head')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
@endpush

@section('content')
<script>
    const BRANDING = {
        welcomeMsg:   "{{ config('branding.welcome_msg') }}",
        placeholder:  "{{ config('branding.placeholder') }}",
        primaryColor: "{{ config('branding.primary_color') }}",
    };
</script>

{{-- Lead Capture Modal --}}
<div id="lead-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4">
        <div class="text-center mb-6">
            <div class="text-4xl mb-3">🤖</div>
            <h2 class="text-xl font-bold text-gray-800">Get Your Free IT Support</h2>
            <p class="text-gray-500 text-sm mt-2">Enter your details and our AI assistant will continue helping you. We may follow up with a solution!</p>
        </div>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700">Your Name</label>
                <input id="lead-name" type="text" placeholder="John Smith"
                    class="w-full mt-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Work Email</label>
                <input id="lead-email" type="email" placeholder="john@company.com"
                    class="w-full mt-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <p id="lead-error" class="text-red-500 text-xs hidden">Please enter your name and a valid email.</p>
            <button onclick="submitLead()"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white py-3 rounded-xl font-medium transition text-sm">
                Continue to AI Support →
            </button>
            <p class="text-center text-xs text-gray-400">No spam. We respect your privacy.</p>
        </div>
    </div>
</div>

{{-- Chat Window --}}
<div class="flex items-center justify-center py-6 px-4">
<div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl flex flex-col" style="height: 80vh;">

    {{-- Chat Header --}}
    @php $color = config('branding.primary_color'); @endphp
    <div class="text-white px-6 py-4 rounded-t-2xl flex items-center gap-3"
         style="background-color: {{ $color }}">
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg"
             style="background-color: rgba(255,255,255,0.2)">
            {{ config('branding.logo_emoji') }}
        </div>
        <div>
            <div class="font-bold text-lg">{{ config('branding.company_name') }}</div>
            <div class="text-sm opacity-75">{{ config('branding.tagline') }}</div>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-sm text-green-300">Online</span>
        </div>
    </div>

    {{-- Chat Messages --}}
    <div id="chat-box" class="flex-1 overflow-y-auto px-6 py-4">
        {{-- Welcome message injected by JS --}}
    </div>

    {{-- Typing Indicator --}}
    <div id="typing" class="hidden px-6 pb-2">
        <div class="flex gap-3 items-center">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm"
                 style="background-color: {{ $color }}">🤖</div>
            <div class="bg-gray-100 rounded-2xl px-4 py-3 text-gray-500 text-sm flex gap-1 items-center">
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>
        </div>
    </div>

    {{-- Input Area --}}
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex gap-3 items-end">
            <textarea
                id="user-input"
                rows="1"
                placeholder="Describe your IT issue..."
                class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                onkeydown="handleKey(event)"
            ></textarea>
            <button onclick="sendMessage()" id="send-btn"
                class="text-white px-5 py-3 rounded-xl text-sm font-medium transition hover:opacity-90"
                style="background-color: {{ $color }}">
                Send
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-2 text-center">Press Enter to send • Shift+Enter for new line</p>
    </div>

</div>
</div>

@endsection

@push('scripts')
<script>
    window.addEventListener('load', () => {
        document.getElementById('user-input').placeholder = BRANDING.placeholder;
        appendMessage('assistant', BRANDING.welcomeMsg);
    });

    let history = [];
    let messageCount = 0;
    let leadCaptured = false;
    let pendingQuestion = null;

    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    function appendMessage(role, content) {
        const box = document.getElementById('chat-box');
        const isUser = role === 'user';

        const wrapper = document.createElement('div');
        wrapper.className = 'flex gap-3 w-full';
        wrapper.style.marginBottom = '12px';

        const avatar = document.createElement('div');
        avatar.className = 'w-8 h-8 rounded-full flex items-center justify-center text-sm flex-shrink-0';
        avatar.style.cssText = `background:${isUser ? '#16a34a' : BRANDING.primaryColor}; color:white;`;
        avatar.textContent = isUser ? '👤' : '🤖';

        const bubble = document.createElement('div');
        bubble.style.cssText = `
            padding: 10px 16px;
            border-radius: 16px;
            max-width: 75%;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
            background: ${isUser ? BRANDING.primaryColor : '#f3f4f6'};
            color: ${isUser ? 'white' : '#1f2937'};
        `;

        if (isUser) {
            bubble.textContent = content;
        } else {
            bubble.innerHTML = marked.parse(content);
        }

        if (isUser) {
            wrapper.style.justifyContent = 'flex-end';
            wrapper.appendChild(bubble);
            wrapper.appendChild(avatar);
        } else {
            wrapper.appendChild(avatar);
            wrapper.appendChild(bubble);
        }

        box.appendChild(wrapper);
        box.scrollTop = box.scrollHeight;
    }

    async function sendMessage() {
        const input = document.getElementById('user-input');
        const question = input.value.trim();
        if (!question) return;

        input.value = '';
        messageCount++;

        if (messageCount === 1 && !leadCaptured) {
            pendingQuestion = question;
            appendMessage('user', question);
            document.getElementById('lead-modal').classList.remove('hidden');
            return;
        }

        appendMessage('user', question);
        await callApi(question);
    }

    async function submitLead() {
        const name  = document.getElementById('lead-name').value.trim();
        const email = document.getElementById('lead-email').value.trim();
        const error = document.getElementById('lead-error');

        if (!name || !email || !email.includes('@')) {
            error.classList.remove('hidden');
            return;
        }

        error.classList.add('hidden');

        await fetch('/api/save-lead', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, email, issue: pendingQuestion })
        });

        leadCaptured = true;
        document.getElementById('lead-modal').classList.add('hidden');

        if (pendingQuestion) {
            await callApi(pendingQuestion);
            pendingQuestion = null;
        }
    }

    async function callApi(question) {
        document.getElementById('typing').classList.remove('hidden');
        document.getElementById('send-btn').disabled = true;

        try {
            const res = await fetch('/api/ask-ai', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                },
                body: JSON.stringify({ question, history })
            });

            const data = await res.json();
            history = data.history;
            appendMessage('assistant', data.answer);

        } catch (err) {
            appendMessage('assistant', '❌ Something went wrong. Please try again.');
        } finally {
            document.getElementById('typing').classList.add('hidden');
            document.getElementById('send-btn').disabled = false;
            document.getElementById('user-input').focus();
        }
    }
</script>
@endpush