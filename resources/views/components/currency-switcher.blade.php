{{-- Currency Switcher (resolves via CurrencyService; no extra view data required) --}}
@php
    $fx = app(\App\Services\CurrencyService::class);
    try {
        $currencies = $fx->supportedCurrencies();
    } catch (\Throwable $e) {
        $currencies = [];
    }
    try {
        $currentCurrency = $fx->currentCurrency();
    } catch (\Throwable $e) {
        $currentCurrency = 'USD';
    }
@endphp
@if(!empty($currencies))
<div x-data="{ curOpen: false }" class="relative">
    <button @click="curOpen = !curOpen" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5 transition-all duration-200" aria-label="Switch currency">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-xs">{{ strtoupper($currentCurrency) }}</span>
        <svg class="w-3 h-3 transition-transform duration-200" :class="curOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="curOpen" @click.away="curOpen = false" x-transition
         class="absolute right-0 mt-2 w-48 bg-white dark:bg-navy-800 rounded-xl shadow-2xl border border-surface-200 dark:border-white/10 py-2 z-50"
         style="display: none;">
        @foreach($currencies as $currency)
        @php $code = is_array($currency) ? ($currency['currency_code'] ?? $currency['code'] ?? null) : (is_object($currency) ? ($currency->currency_code ?? null) : $currency); @endphp
        @if($code)
        <a href="{{ route('currency.switch', strtoupper($code)) }}"
           class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors {{ strtoupper($currentCurrency) === strtoupper($code) ? 'text-cyber-400 bg-cyber-500/10 font-semibold' : 'text-surface-600 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-white/5' }}">
            <span class="font-semibold text-xs w-8">{{ strtoupper($code) }}</span>
            @if(strtoupper($currentCurrency) === strtoupper($code))
            <svg class="w-4 h-4 ml-auto text-cyber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @endif
        </a>
        @endif
        @endforeach
    </div>
</div>
@endif
