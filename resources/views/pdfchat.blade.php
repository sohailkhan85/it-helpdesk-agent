@extends('layouts.app')

@push('scripts_head')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<style>
    #chat-box {
        min-width: 0;
        overflow-wrap: break-word;
    }
    #chat-box * {
        max-width: 100%;
        box-sizing: border-box;
    }
    #chat-box pre, #chat-box code {
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    #chat-box ul, #chat-box ol {
        padding-left: 1.2rem;
        margin: 0.5rem 0;
    }

</style>
@endpush

@section('content')
@php $color = config('branding.primary_color'); @endphp

<div class="flex items-center justify-center py-6 px-4" style="height: 100vh; box-sizing: border-box;">
<div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl flex flex-col" style="height: 80vh; max-height: 80vh; overflow: hidden;">
    {{-- Header --}}
    <div class="text-white px-6 py-4 rounded-t-2xl flex items-center gap-3"
         style="background-color: {{ $color }}">
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg"
             style="background-color: rgba(255,255,255,0.2)">
            📄
        </div>
        <div>
            <div class="font-bold text-lg">AI PDF Chat Assistant</div>
            <div class="text-sm opacity-75">Upload a PDF and ask questions about it</div>
        </div>
    </div>

    {{-- Upload Area --}}
    <div id="upload-area" class="px-6 py-8 flex-1 flex flex-col items-center justify-center">
        <div class="text-5xl mb-4">📄</div>
        <h3 class="font-bold text-gray-800 mb-2">Upload a PDF to get started</h3>
        <p class="text-gray-500 text-sm mb-4 text-center">Ask questions, get summaries, and extract insights from your document</p>
        
        <label for="pdf-file" class="cursor-pointer text-white px-6 py-3 rounded-xl text-sm font-medium transition hover:opacity-90"
               style="background-color: {{ $color }}">
            Choose PDF File
        </label>
        <input type="file" id="pdf-file" accept=".pdf" class="hidden" onchange="uploadPdf()">
        
        <p id="upload-status" class="text-sm text-gray-400 mt-4"></p>
    </div>

    {{-- Chat Area (hidden until PDF uploaded) --}}
    <div id="chat-area" class="hidden flex-1 flex flex-col" style="min-height: 0; overflow: hidden;">

        {{-- Document Info --}}
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
            <span class="text-lg">📄</span>
            <span id="doc-name" class="text-sm font-medium text-gray-700 truncate"></span>
            <button onclick="resetChat()" class="ml-auto text-xs text-gray-400 hover:text-gray-600">
                Upload New PDF
            </button>
        </div>

        {{-- Chat Messages --}}
       
        <div id="chat-box" class="overflow-y-auto px-6 py-4" style="flex: 1 1 0%; min-height: 0;">
        </div>

        {{-- Quick Actions --}}
        <div class="px-6 pb-2 flex gap-2 flex-wrap">
            <button onclick="quickAsk('Summarize this document')" class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition">
                📋 Summarize
            </button>
            <button onclick="quickAsk('What are the key insights from this document?')" class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition">
                💡 Key Insights
            </button>
            <button onclick="quickAsk('What are the main topics covered?')" class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition">
                📑 Main Topics
            </button>
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
                    placeholder="Ask anything about this document..."
                    class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onkeydown="handleKey(event)"
                ></textarea>
                <button onclick="sendMessage()" id="send-btn"
                    class="text-white px-5 py-3 rounded-xl text-sm font-medium transition hover:opacity-90"
                    style="background-color: {{ $color }}">
                    Send
                </button>
            </div>
        </div>
    </div>

</div>
</div>

@endsection

@push('scripts')
<script>
    const PRIMARY_COLOR = "{{ $color }}";
    let history = [];
    let docId = null;

    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    async function uploadPdf() {
        const fileInput = document.getElementById('pdf-file');
        const file = fileInput.files[0];
        if (!file) return;

        document.getElementById('upload-status').textContent = 'Uploading and processing PDF...';

        const formData = new FormData();
        formData.append('pdf', file);

        try {
            const res = await fetch('/api/pdf-upload', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: formData
            });

            if (res.status === 429) {
                document.getElementById('upload-status').textContent = '⏳ Demo limit reached. Visit aihelpswift.com to get your own PDF chat assistant!';
                return;
            }

            const data = await res.json();

            if (data.success) {
                docId = data.doc_id;
                document.getElementById('doc-name').textContent = data.filename;
                document.getElementById('upload-area').classList.add('hidden');
                document.getElementById('chat-area').classList.remove('hidden');

                appendMessage('assistant', `I've read through **${data.filename}**! Here's a preview of the content:\n\n> ${data.preview}\n\nFeel free to ask me anything about this document, or use the quick action buttons below!`);
            } else {
                document.getElementById('upload-status').textContent = '❌ Upload failed. Please try again.';
            }
        } catch (err) {
            console.log(err);
            document.getElementById('upload-status').textContent = '❌ Something went wrong. Please try again.';
        }
    }

    function resetChat() {
        docId = null;
        history = [];
        document.getElementById('chat-box').innerHTML = '';
        document.getElementById('upload-area').classList.remove('hidden');
        document.getElementById('chat-area').classList.add('hidden');
        document.getElementById('upload-status').textContent = '';
        document.getElementById('pdf-file').value = '';
    }

    function appendMessage(role, content) {
        const box = document.getElementById('chat-box');
        const isUser = role === 'user';

        const wrapper = document.createElement('div');
        wrapper.className = 'flex gap-3 w-full';
        wrapper.style.marginBottom = '12px';
        wrapper.style.maxWidth = '100%';
        wrapper.style.overflow = 'hidden';

        const avatar = document.createElement('div');
        avatar.className = 'w-8 h-8 rounded-full flex items-center justify-center text-sm flex-shrink-0';
        avatar.style.cssText = `background:${isUser ? '#16a34a' : PRIMARY_COLOR}; color:white;`;
        avatar.textContent = isUser ? '👤' : '🤖';

        const bubble = document.createElement('div');
        bubble.style.cssText = `
            padding: 10px 16px;
            border-radius: 16px;
            max-width: 100%;
            width: fit-content;
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

    function quickAsk(question) {
        document.getElementById('user-input').value = question;
        sendMessage();
    }

    async function sendMessage() {
        const input = document.getElementById('user-input');
        const question = input.value.trim();
        if (!question || !docId) return;

        input.value = '';
        appendMessage('user', question);

        document.getElementById('typing').classList.remove('hidden');
        document.getElementById('send-btn').disabled = true;

        try {
            const res = await fetch('/api/pdf-ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ doc_id: docId, question, history })
            });

            if (res.status === 429) {
                appendMessage('assistant', '⏳ You\'ve reached the demo limit! Visit https://aihelpswift.com to get your own PDF Chat Assistant.');
                document.getElementById('send-btn').disabled = true;
                document.getElementById('user-input').disabled = true;
                return;
            }

            const data = await res.json();
            console.log('FULL RESPONSE:', data);
            console.log('ANSWER LENGTH:', data.answer ? data.answer.length : 'NO ANSWER');
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