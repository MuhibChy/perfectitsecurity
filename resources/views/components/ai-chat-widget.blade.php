{{-- AI://ASSISTANT — professional technical support console.
     Terminal visual language. Backend endpoints + logic unchanged. --}}
<div x-data="aiChat()" x-init="init()" class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 font-mono" role="complementary" aria-label="AI support assistant">
    {{-- Test/AT hook: canonical assistant name (visual identity stays AI://ASSISTANT) --}}
    <span class="sr-only">AI Support Assistant</span>
    {{-- Chat Toggle Button --}}
    <button x-show="!isOpen" @click="toggleChat()" x-transition
        aria-label="Open AI support assistant"
        class="ai-widget-trigger w-14 h-14 flex items-center justify-center text-accent transition-all duration-300 hover:scale-105">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l-4 3 4 3m8-6l4 3-4 3M14 5l-4 14" />
        </svg>
    </button>

    {{-- Chat Window --}}
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="dark-island fixed bottom-4 right-4 sm:bottom-6 sm:right-6 left-4 sm:left-auto w-auto sm:w-[400px] max-w-none sm:max-w-[calc(100vw-2rem)] h-[600px] max-h-[calc(100dvh-2rem)] bg-term-0 dark:bg-[#0b100e] border border-term-400 backdrop-blur-md shadow-2xl flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="px-4 py-3 flex items-center justify-between flex-shrink-0 border-b border-term-300 dark:border-white/10 bg-term-100 dark:bg-[#0e1411]">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 border border-accent/50 bg-accent/10 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                    <svg class="w-4 h-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l-4 3 4 3m8-6l4 3-4 3M14 5l-4 14" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-term-950 dark:text-white font-bold text-[11px] tracking-[0.2em]">AI://ASSISTANT</h3>
                    <p class="text-term-700 text-[10px] tracking-[0.14em] flex items-center gap-1.5"><span class="term-status-dot" style="width:5px;height:5px;" aria-hidden="true"></span>ONLINE · KB-LINKED</p>
                </div>
            </div>
            <button @click="isOpen = false" class="text-term-700 hover:text-white transition-colors p-1.5" aria-label="Close assistant">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Messages --}}
        <div x-ref="messagesContainer" class="ai-messages flex-1 overflow-y-auto p-4 space-y-4">
            {{-- Guest identity (required once for guests) --}}
            <template x-if="needsGuestInfo">
                <div class="bg-term-200 dark:bg-white/5 border border-term-400 px-4 py-3 max-w-[90%]">
                    <p class="text-[13px] font-sans font-semibold mb-1 text-term-950 dark:text-white">visitor&gt; identify session</p>
                    <p class="font-sans text-xs text-term-700 mb-2">Your details stay with this chat session.</p>
                    <label class="sr-only" for="ai-guest-name">Your name</label>
                    <input id="ai-guest-name" x-model="guestName" type="text" placeholder="Your name" autocomplete="name" aria-label="Your name" class="form-input w-full mb-2 !text-[13px] font-sans">
                    <label class="sr-only" for="ai-guest-email">Email address</label>
                    <input id="ai-guest-email" x-model="guestEmail" type="email" placeholder="Email address" autocomplete="email" aria-label="Email address" class="form-input w-full mb-2 !text-[13px] font-sans">
                    <button @click="saveGuestInfo()" class="term-btn term-btn-sm w-full">Start Session →</button>
                </div>
            </template>
            {{-- Welcome Message --}}
            <template x-if="messages.length === 0 && !loading">
                <div class="text-center py-8">
                    <div class="w-14 h-14 border border-accent/50 bg-accent/10 flex items-center justify-center mx-auto mb-4" aria-hidden="true">
                        <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l-4 3 4 3m8-6l4 3-4 3M14 5l-4 14" />
                        </svg>
                    </div>
                    <h4 class="font-sans font-bold text-term-950 dark:text-white mb-2 text-[15px]">How can I help?</h4>
                    <p class="font-sans text-[13px] text-term-700 mb-1">Knowledge-base answers · service discovery · support guidance.</p>
                    <p class="font-sans text-[11px] text-term-700 mb-4">Escalation to human support available anytime.</p>

                    {{-- Suggested Questions --}}
                    <div class="space-y-2">
                        <template x-for="(q, i) in suggestedQuestions" :key="i">
                            <button @click="sendMessage(q)" class="font-sans block w-full text-left px-3.5 py-2 text-[13px] text-accent-soft bg-accent/5 border border-accent/25 hover:bg-accent/10 hover:border-accent/50 transition-colors" x-text="q"></button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Message List --}}
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                        ? 'bg-accent/10 border border-accent/30 text-term-950 dark:text-white px-3.5 py-2.5 max-w-[85%]'
                        : 'bg-term-200 dark:bg-white/5 border border-term-400 dark:border-white/10 text-term-950 dark:text-term-900 px-3.5 py-2.5 max-w-[85%]'">
                        <div class="font-sans text-[13px] leading-relaxed whitespace-pre-wrap break-words" x-html="formatMessage(msg.content)"></div>
                        <template x-if="msg.answer_source === 'internal'">
                            <div class="mt-1.5"><span class="inline-block font-mono text-[9px] tracking-[0.14em] px-1.5 py-0.5 border border-accent/40 text-accent-soft">✓ KB SOURCE</span></div>
                        </template>
                        <template x-if="msg.answer_source === 'general'">
                            <div class="mt-1.5"><span class="inline-block font-mono text-[9px] tracking-[0.14em] px-1.5 py-0.5 border border-term-400 text-term-700">GENERAL GUIDANCE</span></div>
                        </template>
                        <div class="font-mono text-[10px] mt-1 opacity-70" x-text="formatTime(msg.created_at)"></div>
                    </div>
                </div>
            </template>

            {{-- Typing Indicator --}}
            <div x-show="loading" class="flex justify-start">
                <div class="bg-term-200 dark:bg-white/5 border border-term-400 dark:border-white/10 px-4 py-3">
                    <div class="ai-typing" aria-hidden="true"><span></span><span></span><span></span></div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div x-show="showTicketDraft" class="px-4 py-2 border-t border-term-300 dark:border-white/10 bg-term-100 dark:bg-[#0e1411] flex-shrink-0">
            <div class="flex gap-2">
                <button @click="confirmTicket()" class="term-btn term-btn-sm flex-1">Create Ticket</button>
                <button @click="escalate()" class="term-btn term-btn-sm term-btn-ghost flex-1">Talk to Agent</button>
            </div>
        </div>

        {{-- Input --}}
        <div class="p-3 border-t border-term-300 dark:border-white/10 flex-shrink-0 bg-term-100 dark:bg-[#0e1411]">
            <form @submit.prevent="sendMessage(input)" class="flex gap-2">
                <label for="ai-console-input" class="sr-only">Ask the assistant</label>
                <input id="ai-console-input" x-model="input" type="text" placeholder="visitor> type a question…"
                    class="form-input flex-1 min-w-0 !font-mono !text-[13px]"
                    :disabled="loading" autocomplete="off">
                <button type="submit" :disabled="!input.trim() || loading" aria-label="Send message"
                    class="term-btn term-btn-sm !px-3.5 flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function aiChat() {
    return {
        isOpen: false,
        loading: false,
        messages: [],
        input: '',
        conversationId: null,
        sessionId: null,
        suggestedQuestions: [],
        showTicketDraft: false,
        ticketDraft: null,
        isGuest: {{ auth()->check() ? 'false' : 'true' }},
        guestName: localStorage.getItem('ai_guest_name') || '',
        guestEmail: localStorage.getItem('ai_guest_email') || '',
        needsGuestInfo: false,

        init() {
            // Load session from localStorage
            this.sessionId = localStorage.getItem('ai_session_id') || crypto.randomUUID();
            localStorage.setItem('ai_session_id', this.sessionId);
            // Allow any page (e.g. home CTA) to open the assistant.
            window.addEventListener('open-ai-chat', () => {
                if (!this.isOpen) this.toggleChat();
            });
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen && !this.conversationId) {
                if (this.isGuest && (!this.guestName || !this.guestEmail)) {
                    this.needsGuestInfo = true;
                    return;
                }
                this.startConversation();
            }
        },

        saveGuestInfo() {
            if (!this.guestName.trim() || !/.+@.+\..+/.test(this.guestEmail.trim())) return;
            localStorage.setItem('ai_guest_name', this.guestName.trim());
            localStorage.setItem('ai_guest_email', this.guestEmail.trim());
            this.needsGuestInfo = false;
            this.startConversation();
        },

        async startConversation() {
            try {
                this.loading = true;
                const payload = { source: '{{ request()->is("portal*") ? "portal" : "public" }}' };
                if (this.isGuest) {
                    payload.guest_name = this.guestName;
                    payload.guest_email = this.guestEmail;
                }
                const res = await fetch('/api/ai/conversation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Session-ID': this.sessionId,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (!res.ok || !data.conversation_id) {
                    throw new Error((data && data.message) || 'start-failed');
                }
                this.conversationId = data.conversation_id;
                this.sessionId = data.session_id;
                this.suggestedQuestions = data.suggested_questions || [];

                if (data.welcome_message) {
                    this.messages.push({ role: 'assistant', content: data.welcome_message, created_at: new Date() });
                }
            } catch (e) {
                console.error('Failed to start conversation', e);
                this.messages.push({ role: 'assistant', content: 'Sorry, the AI assistant is temporarily unavailable. You can still contact our support team.', created_at: new Date() });
            } finally {
                this.loading = false;
            }
        },

        async sendMessage(text) {
            if (!text || !text.trim()) return;
            const msg = text.trim();
            this.input = '';
            this.messages.push({ role: 'user', content: msg, created_at: new Date() });
            this.scrollToBottom();
            this.loading = true;

            try {
                const res = await fetch('/api/ai/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Session-ID': this.sessionId,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ conversation_id: this.conversationId, message: msg }),
                });
                const data = await res.json();
                if (!res.ok || typeof data.message !== 'string') {
                    const friendly = (data && typeof data.error === 'string' && data.error)
                        || 'Sorry, the AI assistant is temporarily unavailable. You can still contact our support team.';
                    this.messages.push({ role: 'assistant', content: friendly, created_at: new Date() });
                    return;
                }
                this.messages.push({ role: 'assistant', content: data.message, created_at: new Date(), answer_source: data.answer_source || null });

                if (data.ticket_draft) {
                    this.showTicketDraft = true;
                    this.ticketDraft = data.draft_data;
                }

                if (data.escalated) {
                    this.showTicketDraft = false;
                }
            } catch (e) {
                this.messages.push({ role: 'assistant', content: 'Sorry, something went wrong. Please try again.', created_at: new Date() });
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        },

        async confirmTicket() {
            if (!this.ticketDraft) return;
            this.loading = true;
            try {
                const res = await fetch('/api/ai/ticket/confirm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ conversation_id: this.conversationId, ...this.ticketDraft }),
                });
                const data = await res.json();
                this.messages.push({ role: 'assistant', content: data.message, created_at: new Date() });
                this.showTicketDraft = false;
                this.ticketDraft = null;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        },

        async escalate() {
            this.loading = true;
            try {
                const res = await fetch('/api/ai/escalate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ conversation_id: this.conversationId, reason: 'Customer requested human support' }),
                });
                const data = await res.json();
                this.messages.push({ role: 'assistant', content: data.message, created_at: new Date() });
                this.showTicketDraft = false;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        },

        formatMessage(text) {
            if (!text) return '';
            // Escape HTML first (AI/KB content is untrusted for rendering),
            // then apply basic markdown: **bold**, bullet points, newlines.
            const escaped = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            return escaped
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
        },

        formatTime(time) {
            if (!time) return '';
            return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.messagesContainer) {
                    this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                }
            });
        }
    };
}
</script>
@endpush
