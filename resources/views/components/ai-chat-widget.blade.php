{{-- AI Chat Widget --}}
<div x-data="aiChat()" x-init="init()" class="fixed bottom-6 right-6 z-50" style="font-family: 'Inter', sans-serif;">
    {{-- Chat Toggle Button --}}
    <button x-show="!isOpen" @click="toggleChat()" x-transition
        class="w-14 h-14 bg-gradient-to-br from-brand-600 to-cyber-600 rounded-full shadow-xl shadow-brand-600/30 flex items-center justify-center text-white hover:shadow-brand-600/50 transition-all duration-300 hover:scale-105">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
    </button>

    {{-- Chat Window --}}
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed bottom-6 right-6 w-[400px] max-w-[calc(100vw-2rem)] h-[600px] max-h-[calc(100vh-3rem)] bg-white/90 dark:bg-gray-900/90 backdrop-blur-2xl border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-brand-700 to-cyber-700 px-4 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm">AI Support Assistant</h3>
                    <p class="text-white/70 text-xs">Online • Ready to help</p>
                </div>
            </div>
            <button @click="isOpen = false" class="text-white/70 hover:text-white transition-colors p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Messages --}}
        <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
            {{-- Welcome Message --}}
            <template x-if="messages.length === 0 && !loading">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-brand-600 to-cyber-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">How can I help you?</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">I can answer questions, create tickets, and more.</p>

                    {{-- Suggested Questions --}}
                    <div class="space-y-2">
                        <template x-for="(q, i) in suggestedQuestions" :key="i">
                            <button @click="sendMessage(q)" class="block w-full text-left px-4 py-2 text-sm text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 rounded-xl hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors" x-text="q"></button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Message List --}}
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                        ? 'bg-primary-600 text-white rounded-2xl rounded-br-md px-4 py-2.5 max-w-[80%]'
                        : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl rounded-bl-md px-4 py-2.5 max-w-[80%]'">
                        <div class="text-sm whitespace-pre-wrap" x-html="formatMessage(msg.content)"></div>
                        <div class="text-xs mt-1 opacity-50" x-text="formatTime(msg.created_at)"></div>
                    </div>
                </div>
            </template>

            {{-- Typing Indicator --}}
            <div x-show="loading" class="flex justify-start">
                <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl rounded-bl-md px-4 py-3">
                    <div class="flex gap-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div x-show="showTicketDraft" class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex-shrink-0">
            <div class="flex gap-2">
                <button @click="confirmTicket()" class="flex-1 bg-primary-600 text-white text-xs font-semibold py-2 rounded-xl hover:bg-primary-700 transition-colors">Create Ticket</button>
                <button @click="escalate()" class="flex-1 bg-amber-500 text-white text-xs font-semibold py-2 rounded-xl hover:bg-amber-600 transition-colors">Talk to Agent</button>
            </div>
        </div>

        {{-- Input --}}
        <div class="p-3 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
            <form @submit.prevent="sendMessage(input)" class="flex gap-2">
                <input x-model="input" type="text" placeholder="Ask a question..."
                    class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 border-0 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all"
                    :disabled="loading">
                <button type="submit" :disabled="!input.trim() || loading"
                    class="w-10 h-10 bg-primary-600 text-white rounded-xl flex items-center justify-center hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
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

        init() {
            // Load session from localStorage
            this.sessionId = localStorage.getItem('ai_session_id') || crypto.randomUUID();
            localStorage.setItem('ai_session_id', this.sessionId);
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen && !this.conversationId) {
                this.startConversation();
            }
        },

        async startConversation() {
            try {
                this.loading = true;
                const res = await fetch('/api/ai/conversation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Session-ID': this.sessionId,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ source: '{{ request()->is("portal*") ? "portal" : "public" }}' }),
                });
                const data = await res.json();
                this.conversationId = data.conversation_id;
                this.sessionId = data.session_id;
                this.suggestedQuestions = data.suggested_questions || [];

                if (data.welcome_message) {
                    this.messages.push({ role: 'assistant', content: data.welcome_message, created_at: new Date() });
                }
            } catch (e) {
                console.error('Failed to start conversation', e);
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
                        'X-Session-ID': this.sessionId,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ conversation_id: this.conversationId, message: msg }),
                });
                const data = await res.json();
                this.messages.push({ role: 'assistant', content: data.message, created_at: new Date() });

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
