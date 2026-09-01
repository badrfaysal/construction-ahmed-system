@extends('layouts.app')
@section('title', 'مقارنة الموردين')
@section('page-title', 'مقارنة أسعار الموردين')

@section('content')
<div class="page-head">
  <div><h3>مقارنة أسعار الموردين</h3><p>متوسط سعر كل صنف عند كل مورد — الأرخص يظهر أولاً</p></div>
  <a href="{{ route('suppliers.index') }}" class="btn ghost">رجوع</a>
</div>

<form method="GET" action="{{ route('suppliers.compare') }}" class="filter-bar" style="margin-bottom: 20px;">
  <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
    <input type="text" name="q" value="{{ $search ?? '' }}" list="compare-items" placeholder="ابحث عن خامة معينة..." style="flex:1; min-width: 200px; padding:8px 12px; border:1px solid var(--ln); border-radius:6px; background:var(--surface);">
    <datalist id="compare-items">
      @foreach($autocompleteItems as $item)
        <option value="{{ $item }}"></option>
      @endforeach
    </datalist>
    <button type="submit" class="btn primary">بحث</button>
    @if(request('q'))
      <a href="{{ route('suppliers.compare') }}" class="btn ghost">إلغاء</a>
    @endif
  </div>
</form>

@if($comparison->count())
  <div class="grid cols-2">
    @foreach($comparison as $row)
      <div class="card card-pad">
        <div class="section-label" style="margin-top:0; margin-bottom: 12px;">{{ $row->item }}</div>
        @foreach($row->suppliers as $i => $s)
          <div class="kv">
            <span class="k">
              {{ $s->supplier->name }}
              @if($i === 0)<span class="tag green" style="margin-inline-start:6px">الأرخص</span>@endif
            </span>
            <span class="v tnum">{{ number_format($s->avg_price, 2) }} ج.م / {{ $s->unit }}</span>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bar-chart"/></svg>
    <h4>لا توجد بيانات كافية للمقارنة</h4>
    <p>سجّل مشتريات نفس الصنف من أكثر من مورد لرؤية المقارنة هنا</p>
  </div>
@endif
@endsection
