@extends('layouts.app')
@section('title', 'سجل الحركات')
@section('page-title', 'سجل الحركات المالية')

@section('content')

<div class="page-head">
  <div><h3>سجل الحركات المالية</h3><p>كل وارد وصادر من الخزنة — مسجل تلقائياً من حركة النظام (مشتريات، تحصيلات، أجور)</p></div>
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
    <div class="f-select-wrap">
      <select name="direction" class="f-select" onchange="this.form.submit()">
        <option value="">كل الحركات</option>
        <option value="in" {{ request('direction') === 'in' ? 'selected' : '' }}>وارد فقط</option>
        <option value="out" {{ request('direction') === 'out' ? 'selected' : '' }}>صادر فقط</option>
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
    </div>
  </div>
  @if(request()->hasAny(['project_id','direction']))
    <div class="f-actions">
      <a href="{{ route('transactions.index') }}" class="btn ghost sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
        مسح الفلتر
      </a>
    </div>
  @endif
</form>

<div class="table-card">
  @if($transactions->count())
    <div class="feed">
      @foreach($transactions as $tx)
        <div class="tx {{ $tx->direction }}">
          <div class="tx-ic">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use href="{{ $tx->direction === 'in' ? '#i-down' : '#i-chart' }}"/>
            </svg>
          </div>
          <div class="tx-main">
            <div class="t">{{ $tx->party }}</div>
            <div class="s">
              <span class="tag {{ $tx->direction === 'in' ? 'green' : 'red' }}">{{ $tx->directionAr() }}</span>
              <span>{{ $tx->type }}</span>
              @if($tx->project)
                <span class="tag gray">{{ $tx->project->name }}</span>
              @endif
              <span>{{ $tx->date->format('d/m/Y') }}</span>
              @if($tx->description)
                <span class="muted">{{ $tx->description }}</span>
              @endif
            </div>
          </div>
          <div class="tx-amt">{{ $tx->direction === 'in' ? '+ ' : '− ' }}{{ number_format($tx->amount) }} ج.م</div>
        </div>
      @endforeach
    </div>
    <div style="padding:14px 18px;border-top:1px solid var(--line)">
      {{ $transactions->withQueryString()->links() }}
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
      <h4>لا توجد حركات</h4>
    </div>
  @endif
</div>

@endsection
