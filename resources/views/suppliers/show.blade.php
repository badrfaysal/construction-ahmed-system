@extends('layouts.app')
@section('title', $supplier->name)
@section('page-title', $supplier->name)

@section('content')
<div class="page-head">
  <div>
    <h3>{{ $supplier->name }}</h3>
    <p>{{ $supplier->phone ?: 'بدون هاتف' }}</p>
  </div>
  <div class="btn-row">
    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn ghost">تعديل</a>
    <a href="{{ route('suppliers.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

<div class="grid cols-3" style="margin-bottom:24px">
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المشتريات</span></div>
    <div class="val tnum" style="color:var(--warn)">{{ number_format($supplier->total_spent) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">المرتجعات</span></div>
    <div class="val tnum" style="color:var(--pos)">{{ number_format($supplier->total_returns) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">الصافي</span></div>
    <div class="val tnum">{{ number_format($supplier->total_spent - $supplier->total_returns) }} <small>ج.م</small></div>
  </div>
</div>

<div class="section-label">سجل المشتريات</div>
<div class="table-card">
  @if($supplier->materials->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>الصنف</th>
            <th>المشروع</th>
            <th>البند</th>
            <th class="num">الكمية</th>
            <th>الوحدة</th>
            <th class="num">السعر</th>
            <th class="num">المرتجع</th>
            <th class="num">الصافي</th>
            <th>التاريخ</th>
          </tr>
        </thead>
        <tbody>
          @foreach($supplier->materials->sortByDesc('date') as $m)
            <tr>
              <td><strong>{{ $m->item }}</strong></td>
              <td><span class="tag gray">{{ $m->project?->name ?? '—' }}</span></td>
              <td class="muted">{{ $m->band?->name ?? '—' }}</td>
              <td class="num">{{ number_format($m->qty, 1) }}</td>
              <td class="muted">{{ $m->unit }}</td>
              <td class="num">{{ number_format($m->unit_price) }}</td>
              <td class="num muted">{{ number_format($m->returnedQty(), 1) }}</td>
              <td class="num">{{ number_format($m->netCost()) }}</td>
              <td class="muted">{{ $m->date->format('d/m/Y') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد مشتريات بعد</h4></div>
  @endif
</div>
@endsection
