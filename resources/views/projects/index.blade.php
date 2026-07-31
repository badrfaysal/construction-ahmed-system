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

<div style="margin: 20px 0; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; background: #fff; padding: 12px 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
  <form method="GET" action="{{ route('projects.index') }}" style="display: flex; gap: 12px; align-items: center; width: 100%; flex-wrap: wrap; margin: 0;">
    <input type="hidden" name="tab" value="{{ $tab }}">
    
    <div style="flex: 1; min-width: 250px; position: relative;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"><use href="#i-search"/></svg>
      <input type="text" name="search" value="{{ request('search') }}" onblur="this.form.submit()" onkeydown="if(event.key === 'Enter'){this.form.submit()}" placeholder="ابحث باسم المشروع أو العميل..." style="width: 100%; padding: 8px 12px; padding-right: 36px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none; transition: border-color 0.2s; box-sizing: border-box;">
    </div>
    
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 4px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px;">
        <span style="font-size: 12px; color: #64748b;">من:</span>
        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" style="border: none; background: transparent; font-size: 13.5px; outline: none; color: #475569;">
        <span style="font-size: 12px; color: #64748b; border-right: 1px solid #e2e8f0; padding-right: 8px; margin-right: 4px;">إلى:</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" style="border: none; background: transparent; font-size: 13.5px; outline: none; color: #475569;">
      </div>
      
      <select name="sort" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none; background: #fff; cursor: pointer;">
          <option value="created_desc" {{ request('sort') === 'created_desc' ? 'selected' : '' }}>الإضافة (حديثاً أولاً)</option>
          <option value="created_asc" {{ request('sort') === 'created_asc' ? 'selected' : '' }}>الإضافة (قديماً أولاً)</option>
          <option value="paid_desc" {{ request('sort') === 'paid_desc' ? 'selected' : '' }}>المدفوع (الأكثر)</option>
          <option value="paid_asc" {{ request('sort') === 'paid_asc' ? 'selected' : '' }}>المدفوع (الأقل)</option>
          <option value="progress_desc" {{ request('sort') === 'progress_desc' ? 'selected' : '' }}>نسبة الإنجاز (الأعلى)</option>
          <option value="progress_asc" {{ request('sort') === 'progress_asc' ? 'selected' : '' }}>نسبة الإنجاز (الأقل)</option>
      </select>
      
      @if(request('search') || request('sort') || request('date_from') || request('date_to'))
          <a href="{{ route('projects.index', ['tab' => $tab]) }}" style="color: #ef4444; font-size: 13.5px; font-weight: 600; margin-right: 8px; text-decoration: none; white-space: nowrap;">إلغاء</a>
      @endif
    </div>
  </form>
</div>

@if($projects->count())
  <style>
    .compact-table { border-collapse: separate !important; border-spacing: 0 12px !important; width: 100%; margin-top: 4px; }
    .compact-table th { padding: 12px 16px !important; border: none !important; color: #475569; text-align: right; font-size: 14.5px; font-weight: 700; white-space: nowrap; }
    .compact-table th .th-flex { display: flex; align-items: center; justify-content: flex-start; gap: 6px; }
    .compact-table th svg { opacity: 0.9; width: 18px; height: 18px; flex-shrink: 0; }
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
            <th><div class="th-flex"><svg style="color: #3b82f6;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>المشروع والعميل</div></th>
            <th><div class="th-flex"><svg style="color: #ef4444;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pin"/></svg>العنوان</div></th>
            <th><div class="th-flex"><svg style="color: #8b5cf6;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>البنود الجارية</div></th>
            <th><div class="th-flex"><svg style="color: #06b6d4;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pie-chart"/></svg>الإنجاز</div></th>
            <th><div class="th-flex"><svg style="color: #f59e0b;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>قيمة المشروع</div></th>
            <th><div class="th-flex"><svg style="color: #10b981;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>المدفوع</div></th>
            <th><div class="th-flex"><svg style="color: #6366f1;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-calendar"/></svg>التسليم</div></th>
          </tr>
        </thead>
        <tbody>
          @foreach($projects as $p)
            @php
              $prog = $p->progressPct();
              $paid  = $p->totalCollected();
              $actual = $p->grossClientTotal();
              $activeBands = $p->bands->where('status', 'active');
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
                @elseif($activeBands->count() > 0)
                  <div style="display: flex; gap: 6px; flex-wrap: wrap; max-width: 250px; align-items: center;">
                    @foreach($activeBands->take(1) as $band)
                      <span class="tag blue sm" style="max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $band->name }}">
                        <span class="dot"></span>{{ $band->name }}
                      </span>
                    @endforeach
                    @if($activeBands->count() > 1)
                      <span class="tag sm" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; cursor: help;" title="{{ $activeBands->skip(1)->pluck('name')->join('، ') }}">
                        +{{ $activeBands->count() - 1 }} أخرى
                      </span>
                    @endif
                  </div>
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

  @if($projects->hasPages())
    <div style="margin-top: 20px; display: flex; justify-content: center;" class="pagination-wrapper">
      {{ $projects->links() }}
    </div>
  @endif

@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
    <h4>لا توجد مشاريع {{ $tab === 'done' ? 'مكتملة' : 'جارية' }}</h4>
    <p><a href="{{ route('projects.create') }}">أضف مشروعاً جديداً</a></p>
  </div>
@endif

@endsection
