@extends('layouts.app')
@section('title', 'متابعة أسعار الخامات')
@section('page-title', 'متابعة أسعار الخامات')

@section('content')
<div class="page-head">
  <div><h3>متابعة أسعار الخامات</h3><p>مبنية تلقائياً من الخامات المسجلة فعلياً — بدون إدخال يدوي</p></div>
</div>

<form method="GET" class="filter-bar">
  @include('partials._sort-select', ['options' => [
    'name'       => 'أبجديًا',
    'price_desc' => 'الأعلى سعرًا',
    'price_asc'  => 'الأقل سعرًا',
    'count_desc' => 'الأكثر شراءً',
  ]])
</form>

<div class="table-card">
  @if($items->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>الصنف</th><th>الوحدة</th><th class="num">عدد مرات الشراء</th><th class="num">أقل سعر</th><th class="num">أعلى سعر</th><th class="num">آخر سعر</th><th class="num">نسبة التغير</th><th></th></tr></thead>
        <tbody>
          @foreach($items as $item)
            <tr class="row-click" onclick="location.href='{{ route('price-history.show', $item->name) }}'">
              <td>
                <strong>{{ $item->name }}</strong>
                @if(count($item->variants) > 1)
                  <div class="muted" style="font-size:11px">صيغ مطابقة: {{ implode('، ', array_diff($item->variants, [$item->name])) }}</div>
                @endif
              </td>
              <td class="muted">{{ $item->unit }}</td>
              <td class="num">{{ $item->purchase_count }}</td>
              <td class="num">{{ number_format($item->min_price, 2) }}</td>
              <td class="num">{{ number_format($item->max_price, 2) }}</td>
              <td class="num"><strong>{{ number_format($item->latest_price, 2) }}</strong></td>
              <td class="num">
                @if(is_null($item->change_pct))
                  <span class="muted">—</span>
                @elseif($item->change_pct > 0)
                  <span style="color:var(--neg)">▲ {{ $item->change_pct }}%</span>
                @elseif($item->change_pct < 0)
                  <span style="color:var(--pos)">▼ {{ abs($item->change_pct) }}%</span>
                @else
                  <span class="muted">0%</span>
                @endif
              </td>
              <td><a href="{{ route('price-history.show', $item->name) }}" class="btn ghost sm">السجل الكامل</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg>
      <h4>لا توجد خامات مسجلة بعد</h4>
      <p>هتظهر الأسعار هنا تلقائياً أول ما تسجل خامات في أي مشروع</p>
    </div>
  @endif
</div>
@endsection
