{{-- Shows the actual (real, sell-price-based) total next to the locked-in
     initial contract value, but only when work has genuinely grown beyond
     the original estimate (e.g. a daily-rate band's days were increased) --}}
@php
  $diff = ($actual ?? 0) - ($initial ?? 0);
@endphp
@if($diff > 1)
  <div style="font-size:11px;color:var(--warn);margin-top:3px;font-weight:600">
    الفعلي: {{ \App\Support\Money::format($actual) }} ج.م (زاد {{ \App\Support\Money::format($diff) }} ج.م)
  </div>
@endif
