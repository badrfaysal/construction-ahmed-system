@extends('layouts.app')
@section('title', 'الموردون')
@section('page-title', 'الموردون')

@section('content')

<div class="page-head">
  <div><h3>الموردون</h3><p>جميع الموردين وإجمالي تعاملاتهم</p></div>
  <div class="btn-row">
    <a href="{{ route('suppliers.compare') }}" class="btn ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bar-chart"/></svg>
      مقارنة الأسعار
    </a>
    <a href="{{ route('suppliers.create') }}" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
      مورد جديد
    </a>
  </div>
</div>

<form method="GET" class="filter-bar">
  @include('partials._sort-select', ['options' => [
    'name'        => 'أبجديًا (أ-ي)',
    'newest'      => 'الأحدث',
    'oldest'      => 'الأقدم',
    'spent_desc'  => 'الأعلى إنفاقًا',
    'spent_asc'   => 'الأقل إنفاقًا',
  ]])
</form>

@if($suppliers->count())
  <div class="sup-grid">
    @foreach($suppliers as $s)
      <div class="card sup" style="display:block;">
        <div class="h">
          <div class="av">{{ mb_substr($s->name, 0, 1) }}</div>
          <div>
            <div class="nm">{{ $s->name }}</div>
            @if($s->activity)<div class="muted" style="font-size:11.5px">{{ $s->activity }}</div>@endif
            @if($s->phone)<div class="ph">{{ $s->phone }}</div>@endif
          </div>
          <div style="margin-inline-start:auto; display:flex; align-items:center; gap:8px;">
            <span class="tag blue">{{ $s->invoices_count }} فاتورة</span>
            <a href="{{ route('suppliers.edit', $s) }}" title="تعديل المورد" style="color:var(--text-muted); display:flex; align-items:center;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </a>
          </div>
        </div>
        <div class="kv">
          <span class="k">إجمالي المشتريات</span>
          <span class="v tnum" style="color:var(--warn)">{{ \App\Support\Money::format($s->total_spent) }} ج.م</span>
        </div>
        <div class="kv">
          <span class="k">إجمالي المرتجعات</span>
          <span class="v tnum" style="color:var(--pos)">{{ \App\Support\Money::format($s->total_returns) }} ج.م</span>
        </div>
        <div class="kv">
          <span class="k">الصافي</span>
          <span class="v tnum">{{ \App\Support\Money::format($s->total_spent - $s->total_returns) }} ج.م</span>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-truck"/></svg>
    <h4>لا يوجد موردون بعد</h4>
    <p><a href="{{ route('suppliers.create') }}">أضف مورداً</a></p>
  </div>
@endif

@endsection
