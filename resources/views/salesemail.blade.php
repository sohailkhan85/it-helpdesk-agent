@extends('layouts.app')

@section('content')
@php $color = config('branding.primary_color'); @endphp

<div class="max-w-4xl mx-auto px-6 py-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="text-5xl mb-3">✉️</div>
        <h1 class="text-3xl font-bold text-gray-800">AI Sales Email Generator</h1>
        <p class="text-gray-500 mt-2">Generate personalized cold emails, follow-up sequences and LinkedIn messages in seconds</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Input Form --}}
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h2 class="font-bold text-gray-800 text-lg mb-4">📝 Campaign Details</h2>

            {{-- Sender Info --}}
            <div class="mb-4">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">YOUR DETAILS</label>
                <input id="sender_name" type="text" placeholder="Your Name"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input id="sender_company" type="text" placeholder="Your Company Name"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input id="product_service" type="text" placeholder="Your Product/Service (e.g. AI Helpdesk Agent)"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Target Info --}}
            <div class="mb-4">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">TARGET PROSPECT</label>
                <input id="target_company" type="text" placeholder="Target Company Name"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input id="target_name" type="text" placeholder="Contact Name (optional)"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input id="target_role" type="text" placeholder="Their Role (e.g. IT Manager, CEO)"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Pain Point --}}
            <div class="mb-4">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">THEIR PAIN POINT</label>
                <textarea id="pain_point" rows="3"
                    placeholder="What problem does this company have that your product solves? (e.g. They spend too much on Tier-1 IT support staff and have no after-hours coverage)"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            {{-- Tone --}}
            <div class="mb-6">
                <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">EMAIL TONE</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="tone" value="professional" checked class="text-blue-600">
                        <span class="text-sm">💼 Professional</span>
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="tone" value="friendly" class="text-blue-600">
                        <span class="text-sm">😊 Friendly</span>
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="tone" value="urgent" class="text-blue-600">
                        <span class="text-sm">🔥 Urgent</span>
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="tone" value="consultative" class="text-blue-600">
                        <span class="text-sm">🎯 Consultative</span>
                    </label>
                </div>
            </div>

            <button onclick="generateEmail()" id="generate-btn"
                class="w-full text-white py-4 rounded-xl font-bold text-sm transition hover:opacity-90"
                style="background-color: {{ $color }}">
                ✨ Generate Email Package
            </button>

            <p id="generate-status" class="text-center text-sm text-gray-400 mt-3 hidden">
                🤖 AI is crafting your personalized email package...
            </p>
        </div>

        {{-- Output Area --}}
        <div id="output-area" class="space-y-4">

            {{-- Empty State --}}
            <div id="empty-state" class="bg-white rounded-2xl shadow-xl p-8 text-center h-full flex flex-col items-center justify-center">
                <div class="text-5xl mb-4">✉️</div>
                <h3 class="font-bold text-gray-700 mb-2">Your Email Package Will Appear Here</h3>
                <p class="text-gray-400 text-sm">Fill in the details and click Generate to create your personalized sales email sequence</p>

                {{-- Example Tags --}}
                <div class="mt-6 flex flex-wrap gap-2 justify-center">
                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs">📧 Cold Email</span>
                    <span class="bg-green-50 text-green-600 px-3 py-1 rounded-full text-xs">🔄 3 Follow-ups</span>
                    <span class="bg-purple-50 text-purple-600 px-3 py-1 rounded-full text-xs">💼 LinkedIn Message</span>
                    <span class="bg-orange-50 text-orange-600 px-3 py-1 rounded-full text-xs">📌 Subject Lines</span>
                </div>
            </div>

            {{-- Results (hidden until generated) --}}
            <div id="results" class="hidden space-y-4">

                {{-- Subject Lines --}}
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-800">📌 Subject Lines</h3>
                        <button onclick="copyText('subject-lines-content')"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">Copy</button>
                    </div>
                    <div id="subject-lines-content" class="text-sm text-gray-600 whitespace-pre-line bg-gray-50 rounded-xl p-3"></div>
                </div>

                {{-- Cold Email --}}
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-800">📧 Cold Email</h3>
                        <button onclick="copyText('cold-email-content')"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">Copy</button>
                    </div>
                    <div id="cold-email-content" class="text-sm text-gray-600 whitespace-pre-line bg-gray-50 rounded-xl p-3"></div>
                </div>

                {{-- Follow-up Sequence --}}
                <div class="bg-white rounded-2xl shadow p-5">
                    <h3 class="font-bold text-gray-800 mb-3">🔄 Follow-up Sequence</h3>

                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-orange-600 uppercase">Day 3 — Follow-up #1</span>
                                <button onclick="copyText('followup1-content')"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">Copy</button>
                            </div>
                            <div id="followup1-content" class="text-sm text-gray-600 whitespace-pre-line bg-orange-50 rounded-xl p-3"></div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-yellow-600 uppercase">Day 7 — Follow-up #2</span>
                                <button onclick="copyText('followup2-content')"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">Copy</button>
                            </div>
                            <div id="followup2-content" class="text-sm text-gray-600 whitespace-pre-line bg-yellow-50 rounded-xl p-3"></div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-red-600 uppercase">Day 14 — Breakup Email</span>
                                <button onclick="copyText('followup3-content')"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">Copy</button>
                            </div>
                            <div id="followup3-content" class="text-sm text-gray-600 whitespace-pre-line bg-red-50 rounded-xl p-3"></div>
                        </div>
                    </div>
                </div>

                {{-- LinkedIn Message --}}
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-800">💼 LinkedIn Message</h3>
                        <button onclick="copyText('linkedin-content')"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">Copy</button>
                    </div>
                    <div id="linkedin-content" class="text-sm text-gray-600 whitespace-pre-line bg-blue-50 rounded-xl p-3"></div>
                </div>

                {{-- Generate Again Button --}}
                <button onclick="resetForm()"
                    class="w-full border-2 border-gray-200 text-gray-600 py-3 rounded-xl font-medium text-sm hover:bg-gray-50 transition">
                    ↩️ Generate Another Email
                </button>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const PRIMARY_COLOR = "{{ $color }}";

    async function generateEmail() {
        const senderName    = document.getElementById('sender_name').value.trim();
        const senderCompany = document.getElementById('sender_company').value.trim();
        const productService= document.getElementById('product_service').value.trim();
        const targetCompany = document.getElementById('target_company').value.trim();
        const targetName    = document.getElementById('target_name').value.trim();
        const targetRole    = document.getElementById('target_role').value.trim();
        const painPoint     = document.getElementById('pain_point').value.trim();
        const tone          = document.querySelector('input[name="tone"]:checked').value;

        // Validate required fields
        if (!senderName || !senderCompany || !productService || !targetCompany || !painPoint) {
            alert('Please fill in all required fields!');
            return;
        }

        // Show loading state
        const btn = document.getElementById('generate-btn');
        const status = document.getElementById('generate-status');
        btn.disabled = true;
        btn.textContent = '🤖 Generating...';
        status.classList.remove('hidden');

        // Hide results, show empty state
        document.getElementById('results').classList.add('hidden');
        document.getElementById('empty-state').classList.remove('hidden');

        try {
            const res = await fetch('/api/generate-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    sender_name:     senderName,
                    sender_company:  senderCompany,
                    product_service: productService,
                    target_company:  targetCompany,
                    target_name:     targetName,
                    target_role:     targetRole,
                    pain_point:      painPoint,
                    tone:            tone
                })
            });

            if (res.status === 429) {
                alert('⏳ Demo limit reached! Visit aihelpswift.com to get your own AI Sales Email Generator.');
                return;
            }

            const data = await res.json();

            if (data.success) {
                // Populate results
                document.getElementById('subject-lines-content').textContent = data.subject_lines;
                document.getElementById('cold-email-content').textContent    = data.cold_email;
                document.getElementById('followup1-content').textContent     = data.followup_1;
                document.getElementById('followup2-content').textContent     = data.followup_2;
                document.getElementById('followup3-content').textContent     = data.followup_3;
                document.getElementById('linkedin-content').textContent      = data.linkedin;

                // Show results
                document.getElementById('empty-state').classList.add('hidden');
                document.getElementById('results').classList.remove('hidden');

                // Scroll to results
                document.getElementById('results').scrollIntoView({ behavior: 'smooth' });
            }

        } catch (err) {
            console.log(err);
            alert('❌ Something went wrong. Please try again.');
        } finally {
            btn.disabled = false;
            btn.textContent = '✨ Generate Email Package';
            status.classList.add('hidden');
        }
    }

    function copyText(elementId) {
        const text = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(text).then(() => {
            // Show brief "Copied!" feedback
            const btn = event.target;
            const original = btn.textContent;
            btn.textContent = '✅ Copied!';
            setTimeout(() => btn.textContent = original, 2000);
        });
    }

    function resetForm() {
        document.getElementById('results').classList.add('hidden');
        document.getElementById('empty-state').classList.remove('hidden');
        document.getElementById('sender_name').focus();
    }
</script>
@endpush