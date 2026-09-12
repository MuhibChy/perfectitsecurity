{{-- Language Switcher --}}
<div x-data="{ langOpen: false }" class="relative">
    <button @click="langOpen = !langOpen" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5 transition-all duration-200">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
        <span class="text-xs">{{ strtoupper($currentLocale ?? 'en') }}</span>
        <svg class="w-3 h-3 transition-transform duration-200" :class="langOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="langOpen" @click.away="langOpen = false" x-transition
         class="absolute right-0 mt-2 w-48 bg-white dark:bg-navy-800 rounded-xl shadow-2xl border border-surface-200 dark:border-white/10 py-2 z-50"
         style="display: none;">
        @foreach($supportedLocales ?? ['en' => ['name' => 'English', 'native' => 'English']] as $code => $info)
        <a href="{{ route('lang.switch', $code) }}"
           class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors {{ ($currentLocale ?? 'en') === $code ? 'text-cyber-400 bg-cyber-500/10 font-semibold' : 'text-surface-600 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-white/5' }}">
            <span class="font-semibold text-xs w-6">{{ strtoupper($code) }}</span>
            <span>{{ $info['native'] }}</span>
            @if(($currentLocale ?? 'en') === $code)
            <svg class="w-4 h-4 ml-auto text-cyber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @endif
        </a>
        @endforeach
    </div>
</div>
