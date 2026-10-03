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
    // Authenticated customers choose only from their own allowed set
    // (supported local currency + USD). Guests/staff keep the catalog.
    try {
        $switchUser = auth()->user();
        if ($switchUser && $switchUser->isCustomer()) {
            $allowed = app(\App\Services\CustomerCurrencyService::class)->availableFor($switchUser);
            $currencies = array_values(array_filter($currencies, function ($c) use ($allowed) {
                $code = is_array($c) ? ($c['currency_code'] ?? $c['code'] ?? null) : (is_object($c) ? ($c->currency_code ?? null) : $c);
                return $code && in_array(strtoupper($code), $allowed, true);
            }));
            if (!in_array(strtoupper($currentCurrency), $allowed, true)) {
                $currentCurrency = $allowed[0] ?? 'USD';
            }
        }
    } catch (\Throwable $e) {
        // Fail open to the catalog list; the switch route still enforces.
    }
@endphp
@if(!empty($currencies))
<div x-data="{ curOpen: false }" class="relative">
    <button @click="curOpen = !curOpen" :aria-expanded="curOpen.toString()"
            class="flex items-center gap-1.5 px-2.5 py-2 font-mono text-xs tracking-wider text-gray-400 hover:text-white hover:bg-[#00FF00]/10 transition-colors rounded-sm" aria-label="Switch currency">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 01-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ strtoupper($currentCurrency) }}</span>
        <svg class="w-3 h-3 transition-transform duration-200" :class="curOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="curOpen" @click.away="curOpen = false" x-transition
         class="absolute right-0 mt-2 w-44 bg-black border border-[#00FF00]/30 py-2 z-50 shadow-xl"
         style="display: none;">
        <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-gray-500 px-3.5 pt-1 pb-1.5">Currency</div>
        @foreach($currencies as $currency)
        @php $code = is_array($currency) ? ($currency['currency_code'] ?? $currency['code'] ?? null) : (is_object($currency) ? ($currency->currency_code ?? null) : $currency); @endphp
        @if($code)
        <a href="{{ route('currency.switch', strtoupper($code)) }}"
           class="flex items-center gap-3 px-3.5 py-2 text-sm transition-colors {{ strtoupper($currentCurrency) === strtoupper($code) ? 'text-[#00FF00] bg-[#00FF00]/10' : 'text-gray-300 hover:bg-[#00FF00]/10 hover:text-white' }}">
            <span class="font-mono text-xs w-8 {{ strtoupper($currentCurrency) === strtoupper($code) ? 'text-[#00FF00]' : 'text-gray-500' }}">{{ strtoupper($code) }}</span>
            @if(strtoupper($currentCurrency) === strtoupper($code))
            <svg class="w-4 h-4 ml-auto text-[#00FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            @endif
        </a>
        @endif
        @endforeach
    </div>
</div>
@endif
