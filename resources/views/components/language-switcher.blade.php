{{-- Language Switcher --}}
<div x-data="{ langOpen: false }" class="relative">
    <button @click="langOpen = !langOpen" :aria-expanded="langOpen.toString()"
            class="flex items-center gap-1.5 px-2.5 py-2 font-mono text-xs tracking-wider text-gray-400 hover:text-white hover:bg-[#00FF00]/10 transition-colors rounded-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
        <span>{{ strtoupper($currentLocale ?? 'en') }}</span>
        <svg class="w-3 h-3 transition-transform duration-200" :class="langOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="langOpen" @click.away="langOpen = false" x-transition
         class="absolute right-0 mt-2 w-44 bg-black border border-[#00FF00]/30 py-2 z-50 shadow-xl"
         style="display: none;">
        <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-gray-500 px-3.5 pt-1 pb-1.5">Language</div>
        @foreach($supportedLocales ?? ['en' => ['name' => 'English', 'native' => 'English']] as $code => $info)
        <a href="{{ route('lang.switch', $code) }}"
           class="flex items-center gap-3 px-3.5 py-2 text-sm transition-colors {{ ($currentLocale ?? 'en') === $code ? 'text-[#00FF00] bg-[#00FF00]/10' : 'text-gray-300 hover:bg-[#00FF00]/10 hover:text-white' }}">
            <span class="font-mono text-xs w-6 {{ ($currentLocale ?? 'en') === $code ? 'text-[#00FF00]' : 'text-gray-500' }}">{{ strtoupper($code) }}</span>
            <span>{{ $info['native'] }}</span>
            @if(($currentLocale ?? 'en') === $code)
            <svg class="w-4 h-4 ml-auto text-[#00FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            @endif
        </a>
        @endforeach
    </div>
</div>
