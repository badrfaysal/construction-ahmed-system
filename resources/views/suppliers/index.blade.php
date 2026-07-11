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
      <a class="card sup" href="{{ route('suppliers.show', $s) }}" style="display:block;transition:.16s;cursor:pointer" onmouseover="this.style.boxShadow='var(--shadow)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
        <div class="h">
          <div class="av">{{ mb_substr($s->name, 0, 1) }}</div>
          <div>
            <div class="nm">{{ $s->name }}</div>
            @if($s->activity)<div class="muted" style="font-size:11.5px">{{ $s->activity }}</div>@endif
            @if($s->phone)<div class="ph">{{ $s->phone }}</div>@endif
          </div>
          <span class="tag blue" style="margin-inline-start:auto">{{ $s->materials_count }} صفقة</span>
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
      </a>
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
