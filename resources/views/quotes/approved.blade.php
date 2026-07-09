@extends('layouts.app')
@section('title', 'العروض المعتمدة')
@section('page-title', 'العروض المعتمدة')

@section('content')
<div class="page-head">
  <div><h3>العروض المعتمدة</h3><p>عروض الأسعار التي وافق عليها العميل — جاهزة للتحويل لمشروع وعقد</p></div>
</div>

<div class="grid cols-3" style="margin-bottom:20px">
  <div class="card stat">
    <div class="top"><span class="label">عدد العروض المعتمدة</span><span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg></span></div>
    <div class="val">{{ $stats['count'] }}</div>
    <div class="note">جاهزة للتحويل لمشاريع</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي القيمة</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span></div>
    <div class="val tnum">{{ \App\Support\Money::format($stats['total_value']) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">متوسط قيمة العرض</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span></div>
    <div class="val tnum">{{ \App\Support\Money::format($stats['avg_value']) }} <small>ج.م</small></div>
  </div>
</div>

@if($quotes->count())
  <div class="qcards">
    @foreach($quotes as $q)
      @include('quotes._card', ['q' => $q])
    @endforeach
  </div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>
    <h4>لا توجد عروض معتمدة بعد</h4>
    <p>اعتمد عرضاً من <a href="{{ route('quotes.index') }}">عروض الأسعار</a></p>
  </div>
@endif
@endsection
