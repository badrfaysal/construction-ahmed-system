@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')

{{-- Summary stats row --}}
<div class="grid cols-4" style="margin-bottom:20px">

  <div class="card stat">
    <div class="top">
      <span class="label">المشاريع الجارية</span>
      <span class="ic ic-blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      </span>
    </div>
    <div class="val">{{ $activeProjects->count() }}</div>
    <div class="note">{{ $activeProjects->pluck('name')->join(' · ') ?: 'لا توجد مشاريع جارية' }}</div>
  </div>

  <div class="card stat">
    <div class="top">
      <span class="label">إجمالي المحصّل</span>
      <span class="ic ic-green">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ number_format($totalCollected) }} <small>ج.م</small></div>
    <div class="note">من إجمالي {{ number_format($totalContract) }} ج.م</div>
  </div>

  <div class="card stat">
    <div class="top">
      <span class="label">محفظة المقاولات</span>
      <span class="ic ic-{{ $walletBalance >= 0 ? 'green' : 'red' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
      </span>
    </div>
    <div class="val tnum" style="color:var(--{{ $walletBalance >= 0 ? 'pos' : 'neg' }})">
      {{ number_format($walletBalance, 2) }} <small>ج.م</small>
    </div>
    <div class="note">الرصيد الفعلي — كل مصروف ودفعة بيتحدّث فيها تلقائيًا</div>
  </div>

  <a class="card stat" href="{{ route('alerts.index') }}" style="display:flex;text-decoration:none">
    <div class="top">
      <span class="label">أقساط مستحقة</span>
      <span class="ic ic-amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      </span>
    </div>
    <div class="val">{{ $overdueCount }}</div>
    <div class="note">{{ $overdueCount > 0 ? 'تحتاج متابعة عاجلة — اضغط للتفاصيل' : 'لا توجد أقساط متأخرة' }}</div>
  </a>

</div>

{{-- Active projects --}}
@if($activeProjects->count())
  <div class="section-label">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
    المشاريع الجارية
  </div>
  <div class="pcards" style="margin-bottom:28px">
    @foreach($activeProjects as $p)
      @php
        $prog   = $p->progressPct();
        $paid   = $p->totalCollected();
        $total  = $p->initialContractValue();
        $actual = $p->actualClientTotal();
        $due    = $total - $paid;
        $activeBand = $p->bands->where('status', 'active')->first();
      @endphp
      <a class="pcard" href="{{ route('projects.show', $p) }}">
        <div class="pc-band"></div>
        <div class="pc-body">
          <div class="pc-head">
            <div>
              <div class="pc-name">{{ $p->name }}</div>
              <div class="pc-client">{{ $p->client->name }}</div>
            </div>
            @if($activeBand)
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
            <div class="bar-track"><div class="bar-fill" style="width:{{ $prog }}%"></div></div>
            <span class="pct">{{ $prog }}%</span>
          </div>
          <div class="pc-fin">
            <div>
              <div class="l">قيمة التعاقد</div>
              <div class="v">{{ number_format($total) }}</div>
              @include('partials._actual-vs-initial', ['initial' => $total, 'actual' => $actual])
            </div>
            <div>
              <div class="l">محصّل</div>
              <div class="v" style="color:var(--pos)">{{ number_format($paid) }}</div>
            </div>
            <div>
              <div class="l">متبقي</div>
              <div class="v" style="color:var(--warn)">{{ number_format($due) }}</div>
            </div>
          </div>
        </div>
      </a>
    @endforeach
  </div>
@endif

{{-- Recent transactions --}}
@if($recentTransactions->count())
  <div class="section-label">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
    آخر الحركات المالية
    <a href="{{ route('transactions.index') }}" class="btn ghost sm" style="margin-inline-start:auto">عرض الكل</a>
  </div>
  <div class="table-card">
    <div class="feed">
      @foreach($recentTransactions as $tx)
        <div class="tx {{ $tx->direction }}">
          <div class="tx-ic">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="{{ $tx->direction === 'in' ? '#i-down' : '#i-chart' }}"/>
            </svg>
          </div>
          <div class="tx-main">
            <div class="t">{{ $tx->party }}</div>
            <div class="s">
              <span>{{ $tx->type }}</span>
              @if($tx->project)
                <span class="tag gray">{{ $tx->project->name }}</span>
              @endif
              <span>{{ $tx->date->format('d/m/Y') }}</span>
            </div>
          </div>
          <div class="tx-amt">{{ $tx->direction === 'in' ? '+ ' : '− ' }}{{ number_format($tx->amount) }} ج.م</div>
        </div>
      @endforeach
    </div>
  </div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
    <h4>لا توجد حركات مالية بعد</h4>
    <p>ابدأ بإضافة مشروع أو تسجيل حركة مالية</p>
  </div>
@endif

@endsection
