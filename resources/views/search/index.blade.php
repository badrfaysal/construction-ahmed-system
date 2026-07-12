@extends('layouts.app')
@section('title', 'نتائج البحث')
@section('page-title', 'نتائج البحث' . ($q !== '' ? ': ' . $q : ''))

@section('content')
<div class="page-head">
  <div><h3>نتائج البحث</h3><p>@if($q !== '') عن "{{ $q }}" @else اكتب كلمة في مربع البحث أعلى الصفحة @endif</p></div>
</div>

@php
  $noResults = $projects->isEmpty() && $clients->isEmpty() && $suppliers->isEmpty()
             && $items->isEmpty() && $returns->isEmpty() && $debts->isEmpty();
@endphp

@if($q !== '' && $noResults)
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
              <td class="muted">{{ $p->client->name ?? '—' }}</td>
              <td><a href="{{ route('projects.show', $p) }}" class="btn ghost sm">فتح</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

@if($clients->count())
  <div class="section-label">العملاء ({{ $clients->count() }})</div>
  <div class="table-card" style="margin-bottom:24px">
    <div class="table-scroll">
      <table>
        <thead><tr><th>العميل</th><th>الهاتف</th><th class="num">المشاريع</th><th></th></tr></thead>
        <tbody>
          @foreach($clients as $c)
            <tr class="row-click" onclick="location.href='{{ route('clients.show', $c) }}'">
              <td><strong>{{ $c->name }}</strong></td>
              <td class="muted">{{ $c->phone ?: '—' }}</td>
              <td class="num">{{ $c->projects_count }}</td>
              <td><a href="{{ route('clients.show', $c) }}" class="btn ghost sm">فتح</a></td>
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

@if($returns->count())
  <div class="section-label">المرتجعات ({{ $returns->count() }})</div>
  <div class="table-card" style="margin-bottom:24px">
    <div class="table-scroll">
      <table>
        <thead><tr><th>الصنف المرتجع</th><th>المشروع</th><th class="num">الكمية</th><th>التاريخ</th><th></th></tr></thead>
        <tbody>
          @foreach($returns as $r)
            @php $proj = $r->material->project ?? null; @endphp
            <tr @if($proj) class="row-click" onclick="location.href='{{ route('projects.show', $proj) }}'" @endif>
              <td><strong>{{ $r->material->item ?? '—' }}</strong>@if($r->notes)<div class="muted">{{ $r->notes }}</div>@endif</td>
              <td class="muted">{{ $proj->name ?? '—' }}</td>
              <td class="num">{{ rtrim(rtrim(number_format((float) $r->qty, 2), '0'), '.') }}</td>
              <td class="muted">{{ optional($r->date)->format('Y-m-d') ?? '—' }}</td>
              <td>@if($proj)<a href="{{ route('projects.show', $proj) }}" class="btn ghost sm">فتح المشروع</a>@endif</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

@if($debts->count())
  <div class="section-label">الديون ({{ $debts->count() }})</div>
  <div class="table-card" style="margin-bottom:24px">
    <div class="table-scroll">
      <table>
        <thead><tr><th>المورد / البيان</th><th>المشروع</th><th class="num">المتبقي</th><th>الحالة</th><th></th></tr></thead>
        <tbody>
          @foreach($debts as $d)
            <tr class="row-click" onclick="location.href='{{ route('debts.index') }}'">
              <td><strong>{{ $d->supplier->name ?? '—' }}</strong>@if($d->description)<div class="muted">{{ $d->description }}</div>@endif</td>
              <td class="muted">{{ $d->project->name ?? '—' }}</td>
              <td class="num">{{ number_format($d->remaining(), 2) }} ج.م</td>
              <td><span class="tag {{ $d->statusTag() }}">{{ $d->statusAr() }}</span></td>
              <td><a href="{{ route('debts.index') }}" class="btn ghost sm">فتح الديون</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif
@endsection
