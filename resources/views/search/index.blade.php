@extends('layouts.app')
@section('title', 'نتائج البحث')
@section('page-title', 'نتائج البحث' . ($q !== '' ? ': ' . $q : ''))

@section('content')
<div class="page-head">
  <div><h3>نتائج البحث</h3><p>@if($q !== '') عن "{{ $q }}" @else اكتب كلمة في مربع البحث أعلى الصفحة @endif</p></div>
</div>

@if($q !== '' && $projects->isEmpty() && $suppliers->isEmpty() && $items->isEmpty())
  <div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-search"/></svg>
    <h4>لا توجد نتائج</h4>
  </div>
@endif

@if($projects->count())
  <div class="section-label">المشاريع ({{ $projects->count() }})</div>
  <div class="table-card" style="margin-bottom:24px">
    <div class="table-scroll">
      <table>
        <thead><tr><th>المشروع</th><th>العميل</th><th></th></tr></thead>
        <tbody>
          @foreach($projects as $p)
            <tr class="row-click" onclick="location.href='{{ route('projects.show', $p) }}'">
              <td><strong>{{ $p->name }}</strong></td>
              <td class="muted">{{ $p->client->name }}</td>
              <td><a href="{{ route('projects.show', $p) }}" class="btn ghost sm">فتح</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

@if($suppliers->count())
  <div class="section-label">الموردون ({{ $suppliers->count() }})</div>
  <div class="table-card" style="margin-bottom:24px">
    <div class="table-scroll">
      <table>
        <thead><tr><th>المورد</th><th>الهاتف</th><th></th></tr></thead>
        <tbody>
          @foreach($suppliers as $s)
            <tr class="row-click" onclick="location.href='{{ route('suppliers.show', $s) }}'">
              <td><strong>{{ $s->name }}</strong></td>
              <td class="muted">{{ $s->phone ?: '—' }}</td>
              <td><a href="{{ route('suppliers.show', $s) }}" class="btn ghost sm">فتح</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

@if($items->count())
  <div class="section-label">الأصناف والخامات ({{ $items->count() }})</div>
  <div class="table-card" style="margin-bottom:24px">
    <div class="table-scroll">
      <table>
        <thead><tr><th>الصنف</th><th class="num">عدد مرات الشراء</th><th></th></tr></thead>
        <tbody>
          @foreach($items as $i)
            <tr class="row-click" onclick="location.href='{{ route('price-history.show', $i->name) }}'">
              <td><strong>{{ $i->name }}</strong></td>
              <td class="num">{{ $i->count }}</td>
              <td><a href="{{ route('price-history.show', $i->name) }}" class="btn ghost sm">سجل الأسعار</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif
@endsection
