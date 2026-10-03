{{-- Admin sidebar navigation: Dashboard + 5 collapsible groups (one instance).
     Driven by App\Support\AdminNavigation (add future items there, not here).
     Active route forces its parent open server-side (data attr) and client-side;
     manual open/close persists to localStorage. Decorative layer only:
     authorization stays enforced by route middleware. --}}
@props(['user' => null])
@php
    $groups = \App\Support\AdminNavigation::for($user ?? auth()->user());
    $defaults = [];
    foreach ($groups as $g) { $defaults[$g['key']] = (bool) $g['isActive']; }
    $linkClass = 'shell-link w-full text-left';
    $linkIdle = '';
    $linkActive = 'is-active';
@endphp
<div x-data="adminSidebarNav(@js($defaults))">
    <div x-show="!collapsed" class="shell-group-label px-2 pt-1 pb-2" aria-hidden="true">ADMIN://CONTROL</div>
    <a href="{{ route('admin.dashboard') }}" class="{{ $linkClass }} {{ request()->routeIs('admin.dashboard') ? $linkActive : $linkIdle }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span x-show="!collapsed" x-transition>Dashboard</span>
    </a>

    @foreach($groups as $group)
    <div class="mt-1" data-nav-group="{{ $group['key'] }}" @if($group['isActive']) data-nav-open="1" @endif>
        <button type="button" @click="toggle('{{ $group['key'] }}')"
                :aria-expanded="open['{{ $group['key'] }}'].toString()" aria-controls="nav-sub-{{ $group['key'] }}"
                title="{{ $group['label'] }}"
                class="{{ $linkClass }} w-full text-left {{ $group['isActive'] ? $linkActive : $linkIdle }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $group['icon'] }}"/></svg>
            <span x-show="!collapsed" x-transition class="flex items-center justify-between w-full">
                <span class="shell-group-label !text-[0.62rem]">{{ $group['label'] }}</span>
                <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open['{{ $group['key'] }}'] }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </span>
        </button>
        <div id="nav-sub-{{ $group['key'] }}" x-show="open['{{ $group['key'] }}'] && !collapsed" x-cloak
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-end="opacity-0 -translate-y-1"
             class="ml-4 pl-3 mt-0.5 space-y-0.5 shell-subnav">
            @foreach($group['items'] as $item)
            <a href="{{ route($item['route']) }}" @if(!empty($item['target'])) target="{{ $item['target'] }}" @endif class="{{ $linkClass }} {{ $item['isActive'] ? $linkActive : $linkIdle }} !py-2">
                <svg class="w-5 h-5 flex-shrink-0 {{ $item['iconClass'] ?? '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                <span class="flex items-center justify-between w-full">
                    <span class="flex items-center gap-2">
                        <span>{{ $item['label'] }}</span>
                        @if(!empty($item['pulse']))<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>@endif
                    </span>
                    @if(!empty($item['badge']))
                        @if(($item['badgeClass'] ?? 'primary') === 'cyan')
                        <span class="term-tag">{{ $item['badge'] }}</span>
                        @else
                        <span class="term-tag term-tag-accent">{{ $item['badge'] }}</span>
                        @endif
                    @endif
                </span>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<script>
function adminSidebarNav(defaults) {
    return {
        open: Object.assign({}, defaults),
        init() {
            try {
                const saved = JSON.parse(localStorage.getItem('pits-admin-nav') || '{}');
                for (const key of Object.keys(this.open)) {
                    // Saved state applies; the active route always wins.
                    if (typeof saved[key] === 'boolean' && !defaults[key]) this.open[key] = saved[key];
                    if (defaults[key]) this.open[key] = true;
                }
            } catch (e) {}
        },
        toggle(key) {
            this.open[key] = !this.open[key];
            try { localStorage.setItem('pits-admin-nav', JSON.stringify(this.open)); } catch (e) {}
        }
    };
}
</script>
