{{-- Cookie Consent Banner --}}
<div x-data="cookieConsent()" x-show="showBanner" x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-full opacity-0"
     class="fixed bottom-0 left-0 right-0 z-50 p-4 sm:p-6" style="display: none;">
    <div class="max-w-6xl mx-auto cosmic-glass p-6 sm:p-8 rounded-2xl border border-white/10 shadow-2xl backdrop-blur-2xl bg-[#040816]/95">
        <div class="flex flex-col lg:flex-row items-start lg:items-center gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">Cookie Preferences</h3>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed">
                    We use essential cookies to ensure our platform works correctly. Optional analytics cookies help us improve your experience. You can manage your preferences at any time. See our
                    <a href="{{ route('legal.cookies') }}" class="text-cyan-400 hover:text-cyan-300 underline underline-offset-2">Cookie Policy</a>
                    for details.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto flex-shrink-0">
                <button @click="rejectAll()"
                        class="px-6 py-3 rounded-xl text-sm font-semibold text-slate-300 border border-white/10 hover:border-white/20 hover:bg-white/5 transition-all">
                    Essential Only
                </button>
                <button @click="acceptSelected()"
                        class="px-6 py-3 rounded-xl text-sm font-semibold text-slate-300 border border-white/10 hover:border-cyan-400/50 hover:bg-cyan-500/10 transition-all">
                    Accept Selected
                </button>
                <button @click="acceptAll()"
                        class="px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all hover:scale-105"
                        style="background: linear-gradient(135deg, #16A34A, #2563EB);">
                    Accept All
                </button>
            </div>
        </div>
    </div>
</div>

@once
<script>
    function cookieConsent() {
        return {
            showBanner: false,
            init() {
                const consent = localStorage.getItem('cookie_consent');
                if (!consent) {
                    setTimeout(() => { this.showBanner = true; }, 1500);
                }
            },
            acceptAll() {
                localStorage.setItem('cookie_consent', JSON.stringify({
                    essential: true,
                    analytics: true,
                    marketing: true,
                    timestamp: new Date().toISOString()
                }));
                this.showBanner = false;
            },
            rejectAll() {
                localStorage.setItem('cookie_consent', JSON.stringify({
                    essential: true,
                    analytics: false,
                    marketing: false,
                    timestamp: new Date().toISOString()
                }));
                this.showBanner = false;
            },
            acceptSelected() {
                localStorage.setItem('cookie_consent', JSON.stringify({
                    essential: true,
                    analytics: true,
                    marketing: false,
                    timestamp: new Date().toISOString()
                }));
                this.showBanner = false;
            }
        };
    }
</script>
@endonce
