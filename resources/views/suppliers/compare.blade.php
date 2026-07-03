@extends('layouts.app')
@section('title', 'مقارنة الموردين')
@section('page-title', 'مقارنة أسعار الموردين')

@section('content')
<div class="page-head">
  <div><h3>مقارنة أسعار الموردين</h3><p>متوسط سعر كل صنف عند كل مورد — الأرخص يظهر أولاً</p></div>
  <a href="{{ route('suppliers.index') }}" class="btn ghost">رجوع</a>
</div>

@if($comparison->count())
  <div class="grid cols-2">
    @foreach($comparison as $row)
      <div class="card card-pad">
        <div class="section-label" style="margin-top:0">{{ $row->item }}</div>
        @if(count($row->variants) > 1)
          <p class="muted" style="font-size:11px;margin:-8px 0 10px">صيغ مطابقة: {{ implode('، ', array_diff($row->variants, [$row->item])) }}</p>
        @endif
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
