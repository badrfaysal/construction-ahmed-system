@extends('layouts.app')
@section('title', 'سجل الحركات')
@section('page-title', 'سجل الحركات المالية')

@section('content')

<div class="page-head">
  <div><h3>سجل الحركات المالية</h3><p>كل حركة حصلت فعلاً في النظام — إنشاء وتعديل وحذف — مسجّلة تلقائياً ولا تُمحى أبداً</p></div>
</div>

{{-- Totals strip --}}
<div class="row g-3 mb-3">
  <div class="col-6 col-md-4">
    <div class="card" style="padding:16px 18px">
      <div class="muted" style="font-size:.78rem;margin-bottom:4px">إجمالي الوارد (حي)</div>
      <div style="font-size:1.25rem;font-weight:700;color:#059669">{{ \App\Support\Money::format($totalIn) }} <small class="muted" style="font-size:.75rem;font-weight:400">ج.م</small></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="card" style="padding:16px 18px">
      <div class="muted" style="font-size:.78rem;margin-bottom:4px">إجمالي الصادر (حي)</div>
      <div style="font-size:1.25rem;font-weight:700;color:#dc2626">{{ \App\Support\Money::format($totalOut) }} <small class="muted" style="font-size:.75rem;font-weight:400">ج.م</small></div>
    </div>
  </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      المشروع
    </label>
    <div class="f-select-wrap">
      <select name="project_id" class="f-select" onchange="this.form.submit()">
        <option value="">كل المشاريع</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
    </div>
  </div>
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
      نوع الحركة
    </label>
    <div class="tabs" style="margin-bottom:0">
      <a href="{{ request()->fullUrlWithQuery(['direction' => null]) }}" class="tab {{ !request('direction') ? 'active' : '' }}">الكل</a>
      <a href="{{ request()->fullUrlWithQuery(['direction' => 'in']) }}" class="tab {{ request('direction') === 'in' ? 'active' : '' }}">وارد</a>
      <a href="{{ request()->fullUrlWithQuery(['direction' => 'out']) }}" class="tab {{ request('direction') === 'out' ? 'active' : '' }}">صادر</a>
    </div>
  </div>
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      الإجراء
    </label>
    <div class="tabs" style="margin-bottom:0">
      <a href="{{ request()->fullUrlWithQuery(['action' => null]) }}" class="tab {{ !request('action') ? 'active' : '' }}">الكل</a>
      <a href="{{ request()->fullUrlWithQuery(['action' => 'created']) }}" class="tab {{ request('action') === 'created' ? 'active' : '' }}">إنشاء</a>
      <a href="{{ request()->fullUrlWithQuery(['action' => 'updated']) }}" class="tab {{ request('action') === 'updated' ? 'active' : '' }}">تعديل</a>
      <a href="{{ request()->fullUrlWithQuery(['action' => 'deleted']) }}" class="tab {{ request('action') === 'deleted' ? 'active' : '' }}">حذف/إلغاء</a>
    </div>
  </div>
  @include('partials._sort-select', ['options' => [
    'newest'      => 'الأحدث',
    'oldest'      => 'الأقدم',
    'amount_desc' => 'الأعلى مبلغًا',
    'amount_asc'  => 'الأقل مبلغًا',
  ]])
  @if(request()->hasAny(['project_id','direction','action','sort']))
    <div class="f-actions">
      <a href="{{ route('transactions.index') }}" class="btn ghost sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
        مسح الفلتر
      </a>
    </div>
  @endif
</form>

<div class="table-card">
  @if($logs->count())
    <div class="feed">
      @foreach($logs as $log)
        <div class="tx {{ $log->direction }}" style="{{ $log->action === 'deleted' ? 'opacity:.65' : '' }}">
          <div class="tx-ic">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              @if($log->action === 'deleted')
                <use href="#i-x"/>
              @elseif($log->action === 'updated')
                <use href="#i-chart"/>
              @else
                <use href="{{ $log->direction === 'in' ? '#i-down' : '#i-chart' }}"/>
              @endif
            </svg>
          </div>
          <div class="tx-main">
            <div class="t">
              {{ $log->party ?: '—' }}
              <span class="tag {{ $log->action === 'created' ? 'green' : ($log->action === 'deleted' ? 'red' : 'amber') }}" style="margin-inline-start:6px">
                {{ $log->actionAr() }}
              </span>
              @if($log->action === 'deleted')
                <span class="tag gray">ملغي</span>
              @endif
            </div>
            <div class="s">
              @if($log->direction)
                <span class="tag {{ $log->direction === 'in' ? 'green' : 'red' }}">{{ $log->directionAr() }}</span>
              @endif
              <span>{{ $log->type }}</span>
              @if($log->project)
                <span class="tag gray">{{ $log->project->name }}</span>
              @endif
              @if($log->band)
                <span class="tag gray">{{ $log->band->name }}</span>
              @endif
              @if($log->date)
                <span>{{ $log->date->format('d/m/Y') }}</span>
              @endif
              <span class="muted" title="{{ $log->happened_at }}">سُجّل: {{ $log->happened_at->format('d/m/Y H:i') }}</span>
              @if($log->performedBy)
                <span class="muted">— {{ $log->performedBy->name }}</span>
              @endif
              @if($log->description)
                <span class="muted">{{ $log->description }}</span>
              @endif
            </div>
            @if($log->action === 'updated' && $log->old_values)
              <div class="s" style="margin-top:4px;color:#b7791f">
                <i class="fa fa-clock-rotate-left" style="font-size:.75rem"></i>
                قبل التعديل:
                @foreach($log->old_values as $field => $val)
                  <span style="margin-inline-end:8px">{{ $field }}: <strong>{{ $val }}</strong></span>
                @endforeach
              </div>
            @endif
          </div>
          <div class="tx-amt" style="{{ $log->action === 'deleted' ? 'text-decoration:line-through' : '' }}">
            {{ $log->direction === 'in' ? '+ ' : '− ' }}{{ \App\Support\Money::format($log->amount) }} ج.م
          </div>
        </div>
      @endforeach
    </div>
    <div style="padding:14px 18px;border-top:1px solid var(--line)">
      {{ $logs->withQueryString()->links() }}
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
      <h4>لا توجد حركات</h4>
    </div>
  @endif
</div>

@endsection
