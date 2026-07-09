@extends('layouts.app')
@section('title', 'عروض الأسعار')
@section('page-title', 'عروض الأسعار')

@section('content')

<div class="page-head">
  <div><h3>عروض الأسعار</h3><p>كل العروض المرسلة والمعتمدة وقيد المراجعة</p></div>
  <a href="{{ route('quotes.create') }}" class="btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    عرض جديد
  </a>
</div>

<div class="tabs">
  @php $tabData = ['all' => 'الكل', 'draft' => 'قيد المراجعة', 'sent' => 'تم الإرسال', 'approved' => 'تم الموافقة']; @endphp
  @foreach($tabData as $key => $label)
    <a class="tab {{ $tab === $key ? 'active' : '' }}" href="{{ route('quotes.index', ['tab' => $key]) }}">
      {{ $label }}
      @if($key !== 'all' && isset($counts[$key]))
        <span class="cnt">{{ $counts[$key] }}</span>
      @endif
    </a>
  @endforeach
</div>

<form method="GET" class="filter-bar">
  <input type="hidden" name="tab" value="{{ $tab }}">
  @include('partials._sort-select', ['options' => [
    'newest' => 'الأحدث',
    'oldest' => 'الأقدم',
  ]])
</form>

@if($quotes->count())
  <div class="qcards">
    @foreach($quotes as $q)
      @include('quotes._card', ['q' => $q])
    @endforeach
  </div>
  <div style="margin-top:16px">{{ $quotes->withQueryString()->links() }}</div>
@else
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
    <h4>لا توجد عروض أسعار</h4>
    <p><a href="{{ route('quotes.create') }}">أنشئ عرضاً جديداً</a></p>
  </div>
@endif

@endsection
