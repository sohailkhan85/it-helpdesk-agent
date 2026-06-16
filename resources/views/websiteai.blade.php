@extends('layouts.app')

@push('scripts_head')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<style>
    #chat-box { min-height: 0; overflow-wrap: break-word; }
    #chat-box * { max-width: 100%; box-sizing: border-box; }
    #chat-box ul, #chat-box ol { padding-left: 1.2rem; margin: 0.5rem 0; }
</style>
@endpush

@section('content')
@php $color = config('branding.primary_color'); @endphp

{{-- Lead Capture Modal --}}
<div id="lead-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4">
        <div class="text-center mb-6">
            <div class="text-4xl mb-3">🤖</div>
            <h2 class="text-xl font-bold text-gray-800">Get Instant AI Support</h2>
            <p class="text-gray-500 text-sm mt-2">Enter your details to continue — we may follow up with more help!</p>
        </div>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700">Your Name</label>
                <input id="lead-name" type="text" placeholder="John Smith"
                    class="w-full mt-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Email Address</label>
                <input id="lead-email" type="email" placeholder="john@company.com"
                    class="w-full mt-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <p id="lead-error" class="text-red-500 text-xs hidden">Please enter your name and a valid email.</p>
            <button onclick="submitLead()"
                class="w-full text-white py-3 rounded-xl font-medium transition text-sm hover:opacity-90"
                style="background-color: {{ $color }}">
                Continue to AI Support →
            </button>
            <p class="text-center text-xs text-gray-400">No spam. We respect your privacy.</p>
        </div>
    </div>
</div>

{{-- Escalation Notice --}}
<div id="escalation-notice" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4 text-center">
        <div class="text-5xl mb-4">🚨</div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Issue Escalated!</h2>
        <p class="text-gray-500 text-sm mb-6">Your issue has been flagged as high priority. A support ticket has been created and our team has been notified. We'll get back to you shortly!</p>
        <button onclick="document.getElementById('escalation-notice').classList.add('hidden')"
            class="text-white px-8 py-3 rounded-xl font-medium transition hover:opacity-90"
            style="background-color: {{ $color }}">
            OK, Got It
        </button>
    </div>
</div>

<div class="flex items-center justify-center py-6 px-4" style="min-height: calc(100vh - 80px);">
<div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl flex flex-col" style="height: 80vh; overflow: hidden;">

    {{-- Header --}}
    <div class="text-white px-6 py-4 rounded-t-2xl flex items-center gap-3"
         style="background-color: {{ $color }}">
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg"
             style="background-color: rgba(255,255,255,0.2)">
            🌐
        </div>
        <div>
            <div class="font-bold text-lg" id="agent-title">Website AI Agent</div>
            <div class="text-sm opacity-75" id="agent-subtitle">Enter a website URL to get started</div>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <div id="status-dot" class="w-2 h-2 bg-yellow-400 rounded-full"></div>
            <span id="status-text" class="text-sm text-yellow-300">Not Ready</span>
        </div>
    </div>

    {{-- URL Input Area --}}
    <div id="url-area" class="px-6 py-8 flex flex-col items-center justify-center border-b border-gray-100">
        <div class="text-4xl mb-3">🌐</div>
        <h3 class="font-bold text-gray-800 mb-2">Enter Website URL</h3>
        <p class="text-gray-500 text-sm mb-4 text-center">The AI will learn your website and answer customer questions automatically</p>
        <div class="w-full flex gap-2">
            <input id="website-url" type="url" placeholder="https://yourwebsite.com"
                class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button onclick="scrapeWebsite()" id="scrape-btn"
                class="text-white px-6 py-3 rounded-xl text-sm font-medium transition hover:opacity-90 whitespace-nowrap"
                style="background-color: {{ $color }}">
                Learn Site
            </button>
        </div>
        <p id="scrape-status" class="text-sm text-gray-400 mt-3"></p>
    </div>

    {{-- Chat Area --}}
    <div id="chat-area" class="hidden flex-1 flex flex-col" style="min-height: 0; overflow: hidden;">

        {{-- Business Info Bar --}}
        <div class="px-6 py-2 bg-green-50 border-b border-green-100 flex items-center gap-2">
            <span class="text-green-600 text-sm">✅</span>
            <span id="business-name" class="text-sm font-medium text-green-700"></span>
            <span class="text-xs text-green-500 ml-1">— AI has learned this website</span>
            <button onclick="resetAgent()" class="ml-auto text-xs text-gray-400 hover:text-gray-600">
                Change URL
            </button>
        </div>

        {{-- Chat Messages --}}
        <div id="chat-box" class="overflow-y-auto px-6 py-4" style="flex: 1 1 0%; min-height: 0;">
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
                    placeholder="Ask anything about this business..."
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
</div>

@endsection

@push('scripts')
<script>
    const PRIMARY_COLOR = "{{ $color }}";
    let history = [];
    let agentId = null;
    let messageCount = 0;
    let leadCaptured = false;
    let pendingQuestion = null;
    let visitorName = '';
    let visitorEmail = '';

    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    async function scrapeWebsite() {
        const url = document.getElementById('website-url').value.trim();
        if (!url) return;

        const btn = document.getElementById('scrape-btn');
        const status = document.getElementById('scrape-status');

        btn.disabled = true;
        btn.textContent = 'Learning...';
        status.textContent = '🔍 Scanning website pages... this may take 30-60 seconds';

        try {
            const res = await fetch('/api/website-scrape', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ url })
            });

            if (res.status === 429) {
                status.textContent = '⏳ Demo limit reached. Visit aihelpswift.com to get your own Website AI Agent!';
                btn.disabled = false;
                btn.textContent = 'Learn Site';
                return;
            }

            const data = await res.json();

            if (data.success) {
                agentId = data.agent_id;

                // Update header
                document.getElementById('agent-title').textContent = data.business_name + ' AI Agent';
                document.getElementById('agent-subtitle').textContent = 'Powered by AI • Always Online';
                document.getElementById('status-dot').className = 'w-2 h-2 bg-green-400 rounded-full animate-pulse';
                document.getElementById('status-text').className = 'text-sm text-green-300';
                document.getElementById('status-text').textContent = 'Online';

                // Update business name bar
                document.getElementById('business-name').textContent = data.business_name;

                // Show chat, hide URL input
                document.getElementById('url-area').classList.add('hidden');
                document.getElementById('chat-area').classList.remove('hidden');

                // Welcome message
                appendMessage('assistant', `Hi! I'm the AI assistant for **${data.business_name}**. I've learned your website and I'm ready to help your visitors!\n\nI can answer questions about your products, services, pricing, and more. What would you like to know?`);

            } else {
                status.textContent = '❌ ' + (data.error || 'Failed to scan website. Please check the URL and try again.');
                btn.disabled = false;
                btn.textContent = 'Learn Site';
            }

        } catch (err) {
            status.textContent = '❌ Something went wrong. Please try again.';
            btn.disabled = false;
            btn.textContent = 'Learn Site';
        }
    }

    function resetAgent() {
        agentId = null;
        history = [];
        messageCount = 0;
        leadCaptured = false;
        visitorName = '';
        visitorEmail = '';
        document.getElementById('chat-box').innerHTML = '';
        document.getElementById('url-area').classList.remove('hidden');
        document.getElementById('chat-area').classList.add('hidden');
        document.getElementById('website-url').value = '';
        document.getElementById('scrape-btn').disabled = false;
        document.getElementById('scrape-btn').textContent = 'Learn Site';
        document.getElementById('scrape-status').textContent = '';
        document.getElementById('status-dot').className = 'w-2 h-2 bg-yellow-400 rounded-full';
        document.getElementById('status-text').className = 'text-sm text-yellow-300';
        document.getElementById('status-text').textContent = 'Not Ready';
        document.getElementById('agent-title').textContent = 'Website AI Agent';
        document.getElementById('agent-subtitle').textContent = 'Enter a website URL to get started';
    }

    function appendMessage(role, content) {
        const box = document.getElementById('chat-box');
        const isUser = role === 'user';

        const wrapper = document.createElement('div');
        wrapper.className = 'flex gap-3 w-full';
        wrapper.style.marginBottom = '12px';

        const avatar = document.createElement('div');
        avatar.className = 'w-8 h-8 rounded-full flex items-center justify-center text-sm flex-shrink-0';
        avatar.style.cssText = `background:${isUser ? '#16a34a' : PRIMARY_COLOR}; color:white;`;
        avatar.textContent = isUser ? '👤' : '🤖';

        const bubble = document.createElement('div');
        bubble.style.cssText = `
            padding: 10px 16px;
            border-radius: 16px;
            max-width: 75%;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
            overflow-wrap: anywhere;
            background: ${isUser ? PRIMARY_COLOR : '#f3f4f6'};
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
        if (!question || !agentId) return;

        input.value = '';
        messageCount++;

        // Show lead modal after first message
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
        visitorName  = name;
        visitorEmail = email;

        await fetch('/api/website-lead', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name,
                email,
                issue:    pendingQuestion,
                agent_id: agentId
            })
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
            const res = await fetch('/api/website-ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    agent_id:      agentId,
                    question,
                    history,
                    visitor_name:  visitorName,
                    visitor_email: visitorEmail
                })
            });

            if (res.status === 429) {
                appendMessage('assistant', `⏳ You've reached the demo limit!\n\nWant this AI agent for your own website? Get started at https://aihelpswift.com`);
                document.getElementById('send-btn').disabled = true;
                document.getElementById('user-input').disabled = true;
                document.getElementById('user-input').placeholder = '🔒 Demo limit reached';
                return;
            }

            const data = await res.json();
            history = data.history;
            appendMessage('assistant', data.answer);

            // Show escalation notice if needed
            if (data.escalated) {
                setTimeout(() => {
                    document.getElementById('escalation-notice').classList.remove('hidden');
                }, 1000);
            }

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