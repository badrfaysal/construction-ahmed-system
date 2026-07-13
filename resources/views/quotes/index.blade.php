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
  <div class="f-field" style="flex:1">
    <div style="position:relative">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;right:12px;top:9px;width:16px;height:16px;color:var(--ink-3)"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      <input type="text" name="q" class="f-input" placeholder="ابحث باسم الشقة أو العميل..." value="{{ request('q') }}" style="padding-inline-start:36px">
    </div>
  </div>
  @include('partials._sort-select', ['options' => [
    'newest' => 'الأحدث',
    'oldest' => 'الأقدم',
    'project_asc' => 'الشقة (أ-ي)',
    'project_desc' => 'الشقة (ي-أ)',
  ]])
  @if(request()->hasAny(['q']) || request('sort', 'newest') !== 'newest')
    <div class="f-actions">
      <a href="{{ route('quotes.index', ['tab' => $tab]) }}" class="btn ghost sm">مسح الفلتر</a>
    </div>
  @endif
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
