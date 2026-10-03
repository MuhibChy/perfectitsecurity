{{-- Service price block: local price + USD reference + promotion (33% campaign).
     Props: service (Service), row (ServiceCountryPrice with country), size (card|hero|inline).
     - Preserves existing per-row discounts (never stacks the campaign on them).
     - USD reference converts via CurrencyService; on missing/stale rates the
       reference line is hidden with a "temporarily unavailable" note.
     - Local amounts are display guidance; checkout uses the row currency. --}}
@props(['service', 'row', 'size' => 'card'])
@php
    $promo = null; $usdRef = null; $rateOk = true;
    try {
        $promo = app(\App\Services\PromotionService::class)->priceFor($service, $row);
    } catch (\Throwable $e) { $promo = null; }
    $code = strtoupper($row->country->currency_code ?? 'USD');
    $sym = $row->country->currency_symbol ?? '$';
    $rowDiscountLive = $row->discount_price && $row->discount_valid_until
        && \Carbon\Carbon::parse($row->discount_valid_until)->isFuture();
    $final = $promo
        ? (float) $promo['final']
        : ($rowDiscountLive ? (float) $row->effective_price : (float) $row->price);
    if ($code !== 'USD') {
        try {
            $usdRef = app(\App\Services\CurrencyService::class)->convert($final, $code, 'USD');
        } catch (\Throwable $e) { $rateOk = false; $usdRef = null; }
    }
    $isHero = $size === 'hero';
    $mainCls = $isHero ? 'font-display text-4xl font-bold' : ($size === 'inline' ? 'text-sm font-bold' : 'text-base font-bold');
    $mainColor = ($rowDiscountLive || ($promo && $promo['applies'])) ? 'text-accent-soft' : 'text-navy-900 dark:text-white';
    $unit = match($row->pricing_type) {
        'hourly' => '/hr', 'daily' => '/day', 'monthly' => '/mo', 'recurring' => '/mo',
        'starting_from' => $rowDiscountLive || ($promo && $promo['applies']) ? '' : 'From ',
        default => '',
    };
@endphp
@if($row->pricing_type === 'custom_quote')
    <span class="font-mono text-[11px] font-bold text-accent-soft uppercase tracking-wider">Custom Quote</span>
@else
    @if($rowDiscountLive)
        <span class="text-xs text-term-700 line-through">{{ $sym }}{{ number_format($row->price, 0) }}</span>
        <span class="{{ $mainCls }} {{ $mainColor }} ml-1">{{ $unit }}{{ $sym }}{{ number_format($row->effective_price, 0) }}</span>
        <span class="ml-1 term-tag term-tag-accent">PROMO</span>
        <span class="block text-term-700 font-mono text-[10px]">until {{ \Carbon\Carbon::parse($row->discount_valid_until)->format('M d, Y') }}</span>
    @elseif($promo && $promo['applies'])
        <span class="text-xs text-term-700 line-through">{{ $sym }}{{ number_format($promo['original'], 0) }}</span>
        <span class="{{ $mainCls }} {{ $mainColor }} ml-1">{{ $unit }}{{ $sym }}{{ number_format($promo['final'], 0) }}</span>
        <span class="ml-1 term-tag term-tag-accent">-{{ rtrim(rtrim(number_format($promo['percent'], 1), '0'), '.') }}%</span>
        @php $win = app(\App\Services\PromotionService::class)->window(); $cfg = app(\App\Services\PromotionService::class)->campaign(); @endphp
        <span class="block text-term-700 font-mono text-[10px]" title="{{ $cfg['promo.terms'] ?? '' }}">{{ $cfg['promo.name'] ?? 'Promotion' }}@if($win['ends_at']) · ends {{ $win['ends_at']->format('M d, Y') }}@endif</span>
    @else
        <span class="{{ $mainCls }} {{ $mainColor }}">{{ $unit }}{{ $sym }}{{ number_format($final, $row->pricing_type === 'hourly' ? 2 : 0) }}</span>
        @if(!in_array($row->pricing_type, ['fixed', 'starting_from']))
        <span class="text-[11px] text-term-700 ml-1">/ {{ ucfirst($row->pricing_type) }}</span>
        @endif
    @endif
    @if($code !== 'USD')
        @if($rateOk && $usdRef !== null)
        <span class="block font-mono text-[10px] tracking-wider text-term-700">USD ${{ number_format($usdRef, 2) }} ref · local estimate, checkout in {{ $code }}</span>
        @else
        <span class="block font-mono text-[10px] tracking-wider text-term-700">USD reference temporarily unavailable · checkout in {{ $code }}</span>
        @endif
    @else
        <span class="block font-mono text-[10px] tracking-wider text-term-700">USD reference price</span>
    @endif
@endif
