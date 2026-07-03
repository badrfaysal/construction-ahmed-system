@extends('layouts.app')
@section('title', 'لوحة التحليلات')
@section('page-title', 'لوحة التحليلات')

@section('content')
<div class="page-head">
  <div><h3>لوحة التحليلات</h3><p>التدفقات النقدية وأداء الموردين خلال آخر ٦ أشهر</p></div>
</div>

<div class="grid cols-2" style="margin-bottom:24px">
  <div class="card stat">
    <div class="top"><span class="label">مشاريع نشطة</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></span></div>
    <div class="val tnum">{{ $statusCounts['active'] }}</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">مشاريع منتهية</span><span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg></span></div>
    <div class="val tnum">{{ $statusCounts['done'] }}</div>
  </div>
</div>

{{-- Monthly cash flow — simple bar chart built with CSS, no JS library needed --}}
<div class="section-label">التدفق النقدي الشهري (وارد / صادر)</div>
<div class="card card-pad" style="margin-bottom:24px">
  @if(count($cashFlow))
    @php $maxVal = max(array_map(fn($m) => max($m['in'] ?? 0, $m['out'] ?? 0), $cashFlow)) ?: 1; @endphp
    <div style="display:flex;align-items:flex-end;gap:18px;height:200px;padding-top:10px">
      @foreach($cashFlow as $month => $vals)
        @php
          $in  = $vals['in']  ?? 0;
          $out = $vals['out'] ?? 0;
        @endphp
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%">
          <div style="flex:1;display:flex;align-items:flex-end;gap:4px;width:100%;justify-content:center">
            <div title="وارد: {{ number_format($in) }}" style="width:18px;border-radius:4px 4px 0 0;background:var(--pos);height:{{ ($in / $maxVal) * 100 }}%"></div>
            <div title="صادر: {{ number_format($out) }}" style="width:18px;border-radius:4px 4px 0 0;background:var(--neg);height:{{ ($out / $maxVal) * 100 }}%"></div>
          </div>
          <span class="muted tnum">{{ $month }}</span>
        </div>
      @endforeach
    </div>
    <div class="btn-row" style="margin-top:14px">
      <span class="muted"><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:var(--pos);margin-inline-end:5px"></span>وارد</span>
      <span class="muted"><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:var(--neg);margin-inline-end:5px"></span>صادر</span>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد حركات مالية كافية لعرض الرسم</h4></div>
  @endif
</div>

{{-- Top suppliers --}}
<div class="section-label">أكبر ٥ موردين من حيث الإنفاق</div>
<div class="table-card">
  @if($topSuppliers->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>المورد</th><th class="num">صافي الإنفاق</th></tr></thead>
        <tbody>
          @foreach($topSuppliers as $s)
            <tr>
              <td><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
              <td class="num">{{ number_format($s->net_spend) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد بيانات موردين بعد</h4></div>
  @endif
</div>
@endsection
