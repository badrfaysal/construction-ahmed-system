@extends('layouts.app')

@section('title', 'الشقق والمشاريع')
@section('page-title', 'الشقق والمشاريع')

@section('content')

<div class="page-head">
  <div>
    <h3>الشقق والمشاريع</h3>
    <p>جميع مشاريع التشطيب والتجديد</p>
  </div>
  <a href="{{ route('projects.create') }}" class="btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    مشروع جديد
  </a>
</div>

{{-- Status tabs --}}
<div class="tabs">
  <a class="tab {{ $tab === 'active' ? 'active' : '' }}" href="{{ route('projects.index', ['tab' => 'active']) }}">
    <span class="dot" style="width:7px;height:7px;background:var(--accent);border-radius:50%"></span>
    جارية <span class="cnt">{{ $activeCnt }}</span>
  </a>
  <a class="tab {{ $tab === 'done' ? 'active' : '' }}" href="{{ route('projects.index', ['tab' => 'done']) }}">
    مكتملة <span class="cnt">{{ $doneCnt }}</span>
  </a>
</div>

@if($projects->count())
  <div class="pcards">
    @foreach($projects as $p)
      @php
        $prog = $p->progressPct();
        $paid  = $p->totalCollected();
        $total = $p->initialContractValue();
        $actual = $p->actualClientTotal();
        $activeBand = $p->bands->where('status', 'active')->first();
        $paidWorkers = $p->bands->flatMap(fn($b) => $b->workers)->sum(fn($w) => $w->paidTotal());
      @endphp
      <a class="pcard {{ $p->status === 'done' ? 'is-done' : '' }}" href="{{ route('projects.show', $p) }}">
        <div class="pc-band"></div>
        <div class="pc-body">
          <div class="pc-head">
            <div>
              <div class="pc-name">{{ $p->name }}</div>
              <div class="pc-client">{{ $p->client->name }}</div>
            </div>
            @if($p->status === 'done')
              <span class="tag green"><span class="dot"></span>مكتمل ومسلّم</span>
            @elseif($activeBand)
              <span class="tag blue"><span class="dot"></span>{{ $activeBand->name }}</span>
            @else
              <span class="tag gray">قيد الإعداد</span>
            @endif
          </div>
          @if($p->address)
            <div class="pc-addr">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pin"/></svg>
              {{ $p->address }}
            </div>
          @endif
          <div class="pc-prog">
            <span class="muted" style="font-size:11px">الإنجاز</span>
            <div class="bar-track"><div class="bar-fill {{ $prog >= 100 ? 'full' : '' }}" style="width:{{ $prog }}%"></div></div>
            <span class="pct">{{ $prog }}%</span>
          </div>
          @if($p->bands->count())
            <div class="pc-bands">
              @foreach($p->bands as $band)
                <span class="tag {{ $band->status === 'done' ? 'green' : ($band->status === 'active' ? 'blue' : 'gray') }} sm">
                  @if($band->status === 'done') ✓ @endif{{ $band->name }}
                </span>
              @endforeach
            </div>
          @endif
          <div class="pc-fin">
            <div>
              <div class="l">قيمة المشروع</div>
              <div class="v" style="color:var(--brand)">{{ \App\Support\Money::format($actual) }}</div>
              @if($actual > $total + 1)
                <div style="font-size:11px;color:var(--muted);margin-top:3px;font-weight:600">
                  المتفق عليه: {{ \App\Support\Money::format($total) }} ج.م
                </div>
              @endif
            </div>
            <div>
              <div class="l">محصّل من العميل</div>
              <div class="v" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</div>
            </div>
            <div>
              <div class="l">{{ $p->status === 'done' ? 'تاريخ التسليم' : 'موعد التسليم' }}</div>
              <div class="v">{{ $p->status === 'done' ? ($p->delivered_date?->format('Y-m-d') ?? '—') : ($p->deliver_date?->format('Y-m-d') ?? '—') }}</div>
            </div>
          </div>
          {{-- شريط مدفوعات مختصر: دفعات العميل + المدفوع للصنايعية --}}
          <div class="pc-pays">
            <div class="pc-pay in">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
              <span class="l">دفعات العميل</span>
              <span class="v">{{ \App\Support\Money::format($paid) }}</span>
            </div>
            <div class="pc-pay out">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
              <span class="l">مدفوع للصنايعية</span>
              <span class="v">{{ \App\Support\Money::format($paidWorkers) }}</span>
            </div>
          </div>
        </div>
      </a>
    @endforeach
  </div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
    <h4>لا توجد مشاريع {{ $tab === 'done' ? 'مكتملة' : 'جارية' }}</h4>
    <p><a href="{{ route('projects.create') }}">أضف مشروعاً جديداً</a></p>
  </div>
@endif

@endsection
