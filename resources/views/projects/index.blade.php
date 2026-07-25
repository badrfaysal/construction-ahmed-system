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
  <a class="tab {{ $tab === 'suspended' ? 'active' : '' }}" href="{{ route('projects.index', ['tab' => 'suspended']) }}">
    معلقة <span class="cnt">{{ $suspendedCnt }}</span>
  </a>
  <a class="tab {{ $tab === 'canceled' ? 'active' : '' }}" href="{{ route('projects.index', ['tab' => 'canceled']) }}">
    ملغية <span class="cnt">{{ $canceledCnt }}</span>
  </a>
</div>

@if($projects->count())
  <style>
    .compact-table { border-collapse: separate !important; border-spacing: 0 12px !important; width: 100%; margin-top: 4px; }
    .compact-table th { padding: 8px 16px !important; line-height: 1.4; border: none !important; color: #475569; text-align: right; font-size: 14.5px; font-weight: 700; white-space: nowrap; }
    .compact-table th svg { opacity: 0.5; margin-left: 6px; vertical-align: -4px; width: 18px; height: 18px; color: #64748b; }
    .compact-table td { 
        padding: 14px 16px !important; 
        line-height: 1.4; 
        border-top: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        background-color: #fff;
        font-size: 13.5px; 
    }
    .compact-table td:first-child { border-right: 1px solid #e2e8f0 !important; border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
    .compact-table td:last-child { border-left: 1px solid #e2e8f0 !important; border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .compact-table tbody tr { box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s ease; }
    .compact-table tbody tr:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .compact-table tbody tr:hover td { background-color: #f8fafc !important; border-color: #cbd5e1 !important; }
  </style>
  <div class="table-card" style="background: transparent; box-shadow: none; padding: 0; border: none;">
    <div class="table-scroll" style="padding-bottom: 12px;">
      <table class="compact-table" style="white-space: nowrap;">
        <thead>
          <tr>
            <th><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>المشروع والعميل</th>
            <th><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pin"/></svg>العنوان</th>
            <th><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>البنود الجارية</th>
            <th><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pie-chart"/></svg>الإنجاز</th>
            <th><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>قيمة المشروع</th>
            <th><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>المدفوع</th>
            <th><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-calendar"/></svg>التسليم</th>
          </tr>
        </thead>
        <tbody>
          @foreach($projects as $p)
            @php
              $prog = $p->progressPct();
              $paid  = $p->totalCollected();
              $actual = $p->grossClientTotal();
              $activeBand = $p->bands->where('status', 'active')->first();
            @endphp
            <tr onclick="window.location='{{ route('projects.show', $p) }}'" style="cursor: pointer; transition: background 0.15s;">
              <td>
                <div style="font-weight: 700; font-size: 14px; color: #0f172a">{{ $p->name }}</div>
                <div style="font-size: 12px; color: #64748b">{{ $p->client->name }}</div>
              </td>
              <td style="font-size: 12.5px; color: #475569">
                @if($p->address)
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:.6; margin-left: 2px"><use href="#i-pin"/></svg>
                  {{ \Illuminate\Support\Str::limit($p->address, 30) }}
                @else
                  —
                @endif
              </td>
              <td>
                @if($p->status === 'done')
                  <span class="tag green sm"><span class="dot"></span>مكتمل</span>
                @elseif($p->status === 'suspended')
                  <span class="tag amber sm"><span class="dot"></span>معلق</span>
                @elseif($p->status === 'canceled')
                  <span class="tag red sm"><span class="dot"></span>ملغي</span>
                @elseif($activeBand)
                  <span class="tag blue sm"><span class="dot"></span>{{ $activeBand->name }}</span>
                @else
                  <span class="tag gray sm">جاري</span>
                @endif
              </td>
              <td style="width: 140px;">
                <div style="display:flex; align-items:center; gap:8px;">
                  <div class="bar-track" style="flex:1; margin:0; height:6px"><div class="bar-fill {{ $prog >= 100 ? 'full' : '' }}" style="width:{{ $prog }}%"></div></div>
                  <span class="pct tnum" style="font-size:12px; font-weight:700; min-width:32px">{{ $prog }}%</span>
                </div>
              </td>
              <td style="font-weight: 700; font-size: 13px; color: var(--brand)" class="tnum">{{ \App\Support\Money::format($actual) }}</td>
              <td style="font-weight: 700; font-size: 13px; color: var(--pos)" class="tnum">{{ \App\Support\Money::format($paid) }}</td>
              <td style="font-size: 12.5px; color: #475569" class="tnum">
                {{ $p->status === 'done' ? ($p->delivered_date?->format('Y-m-d') ?? '—') : ($p->deliver_date?->format('Y-m-d') ?? '—') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
    <h4>لا توجد مشاريع {{ $tab === 'done' ? 'مكتملة' : 'جارية' }}</h4>
    <p><a href="{{ route('projects.create') }}">أضف مشروعاً جديداً</a></p>
  </div>
@endif

@endsection
