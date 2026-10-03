{{-- Shared human-readable timeline. Expects $timeline (at/label/detail/url). --}}
<div class="relative">
    <div class="absolute left-3 top-1 bottom-1 w-px bg-slate-200 dark:bg-white/10"></div>
    <div class="space-y-3">
        @forelse($timeline as $event)
        <div class="relative pl-10">
            <span class="absolute left-1.5 top-1.5 w-3.5 h-3.5 rounded-full border-2 border-emerald-500 bg-white dark:bg-navy-900"></span>
            <div class="card p-3">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <strong class="text-sm text-slate-900 dark:text-white">{{ $event['label'] }}</strong>
                    <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($event['at'])->format('d M Y · H:i') }}</span>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">{{ $event['detail'] }}</p>
                @if(!empty($event['url']))<a href="{{ $event['url'] }}" class="link-arrow text-xs mt-1 inline-block">View record →</a>@endif
            </div>
        </div>
        @empty
        <p class="body-sm pl-10">No recorded events yet. Absence of a record means the event did not happen — it is never assumed.</p>
        @endforelse
    </div>
</div>
