{{-- Phone input: [ searchable country | dial code | national number ].
     Server is the source of truth (hidden alpha-2 + no-JS text fallback);
     the inline script only syncs the visible selector with the dial badge.
     Native controls: keyboard accessible, mobile friendly, theme-safe. --}}
@props([
    'selected' => null,
    'national' => null,
    'required' => false,
    'label' => 'Mobile Number',
    'hint' => null,
])
@php
    $uid = 'phone_' . substr(md5(spl_object_id((object) []) . ($selected ?? '') . microtime()), 0, 8);
    $countries = \App\Support\PhoneCountries::all();
    $sel = strtoupper(old('country', $selected ?? ''));
    if ($sel === 'UK') $sel = 'GB';
    $selEntry = $sel ? \App\Support\PhoneCountries::find($sel) : null;
    $natVal = old('national_number', $national ?? '');
    $dialMap = [];
    foreach ($countries as $c) $dialMap[$c['alpha2']] = $c['dial'];
@endphp
<div>
    <label class="term-field-label" for="{{ $uid }}_search">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    <div class="flex flex-col sm:flex-row gap-2">
        <div class="sm:w-56 flex-shrink-0">
            <input type="text" id="{{ $uid }}_search" name="country_search" list="{{ $uid }}_list"
                   value="{{ $selEntry ? $selEntry['alpha2'] . ' — ' . $selEntry['name'] . ' (+' . $selEntry['dial'] . ')' : old('country_search', '') }}"
                   placeholder="Search country…" autocomplete="off" class="term-input w-full"
                   aria-label="Country">
            <datalist id="{{ $uid }}_list">
                @foreach($countries as $c)
                <option data-alpha2="{{ $c['alpha2'] }}" data-dial="{{ $c['dial'] }}" value="{{ $c['alpha2'] }} — {{ $c['name'] }} (+{{ $c['dial'] }})"></option>
                @endforeach
            </datalist>
            <input type="hidden" name="country" id="{{ $uid }}_alpha2" value="{{ $selEntry['alpha2'] ?? '' }}">
        </div>
        <div class="flex gap-2 flex-1 min-w-0">
            <span id="{{ $uid }}_dial" class="term-input !w-20 flex-shrink-0 text-center font-mono" aria-hidden="true">+{{ $selEntry['dial'] ?? '…' }}</span>
            <label class="sr-only" for="{{ $uid }}_number">National mobile number</label>
            <input type="tel" name="national_number" id="{{ $uid }}_number" value="{{ $natVal }}"
                   placeholder="7123456789" @if($required) required @endif
                   class="term-input flex-1 min-w-0 font-mono" aria-label="National mobile number"
                   inputmode="tel" autocomplete="tel-national">
        </div>
    </div>
    @if($hint)<p class="term-hint mt-1">{{ $hint }}</p>@endif
    @error('national_number')<p class="term-error">{{ $message }}</p>@enderror
    @error('country')<p class="term-error">{{ $message }}</p>@enderror
    <script>
    (function () {
        var search = document.getElementById('{{ $uid }}_search');
        var hidden = document.getElementById('{{ $uid }}_alpha2');
        var dial = document.getElementById('{{ $uid }}_dial');
        var map = @json($dialMap);
        function sync() {
            var v = (search.value || '').toUpperCase();
            var m = v.match(/^([A-Z]{2})\b/);
            var code = m ? m[1] : '';
            if (code === 'UK') code = 'GB';
            hidden.value = map[code] ? code : '';
            dial.textContent = '+' + (map[code] || '…');
        }
        if (search) search.addEventListener('input', sync);
        sync();
    })();
    </script>
</div>
