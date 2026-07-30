@extends('layouts.app')
@section('title', 'كشف حساب بند: ' . $band->name)
@section('page-title', 'كشف حساب البند')

@section('content')
<div class="page-head no-print">
  <div><h3>كشف حساب للعميل — بند {{ $band->name }}</h3><p>{{ $band->project->name }} · {{ $band->project->client->name }}</p></div>
  <div class="btn-row">
    <button onclick="window.print()" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      طباعة / حفظ PDF
    </button>
    <a href="{{ route('projects.show', $band->project) }}" class="btn ghost">رجوع</a>
  </div>
</div>

@php
  $actualTotal = $band->actualClientTotal();
@endphp

<div class="statement">
  <div class="st-head">
    <div class="co">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></div>
      <h2>{{ $settings->company_name }}</h2>
      <p>{{ $settings->company_tagline }} @if($settings->company_phone)· هاتف {{ $settings->company_phone }}@endif</p>
    </div>
    <div class="meta">
      <b>كشف حساب بند</b><br>
      البند: {{ $band->name }}<br>
      التاريخ: {{ now()->format('d/m/Y') }}
    </div>
  </div>

  <div class="st-body">
    <div class="st-client">
      <div><div class="l">المشروع</div><div class="b">{{ $band->project->name }}</div></div>
      <div><div class="l">العميل</div><div class="b">{{ $band->project->client->name }}</div></div>
      <div><div class="l">الحالة</div><div class="b">{{ $band->status === 'done' ? 'منفذ' : ($band->status === 'active' ? 'جاري' : 'لم يبدأ') }}</div></div>
    </div>

    {{-- Summary --}}
    <div class="st-summary">
      <div class="st-box tot"><div class="l">إجمالي المستحق</div><div class="v">{{ \App\Support\Money::format($actualTotal) }} ج.م</div></div>
    </div>

    {{-- Materials --}}
    <div class="st-sec">الخامات ({{ $band->materials->count() }})</div>
    <table class="st-table">
      <thead><tr><th>التاريخ</th><th>الصنف</th><th class="num">الوحدة</th><th class="num">السعر</th><th class="num">الإجمالي</th></tr></thead>
      <tbody>
        @php $clientMaterialTotal = 0; @endphp
        @forelse($band->materials->sortBy('date') as $m)
          @php $rowTotal = $m->netQty() * $m->clientUnitPrice(); $clientMaterialTotal += $rowTotal; @endphp
          <tr>
            <td>{{ $m->date->format('Y-m-d') }}</td>
            <td>
              {{ $m->item }}
              @if($m->returnedQty() > 0)
                <span style="color:var(--neg);font-size:10.5px">(مرتجع {{ \App\Support\Money::format($m->returnedQty(), 1) }})</span>
              @endif
            </td>
            <td class="num">{{ $m->unit ?: 'وحدة' }}</td>
            <td class="num">{{ \App\Support\Money::format($m->clientUnitPrice()) }}</td>
            <td class="num"><b>{{ \App\Support\Money::format($rowTotal) }}</b></td>
          </tr>
        @empty
          <tr><td colspan="5" class="muted" style="text-align:center;padding:16px">لا توجد خامات مسجلة لهذا البند</td></tr>
        @endforelse
        <tr class="sub">
          <td colspan="4" style="text-align:left">إجمالي الخامات</td>
          <td class="num">{{ \App\Support\Money::format($clientMaterialTotal) }} ج.م</td>
        </tr>
      </tbody>
    </table>

    {{-- Labor --}}
    <div class="st-sec">المصنعية</div>
    <table class="st-table">
      <thead><tr><th>البيان</th><th class="num">الإجمالي</th></tr></thead>
      <tbody>
        @if($band->laborClientPrice() > 0)
          <tr>
            <td>مصنعية</td>
            <td class="num"><b>{{ \App\Support\Money::format($band->laborClientPrice()) }}</b></td>
          </tr>
        @else
          <tr><td colspan="2" class="muted" style="text-align:center;padding:16px">لا توجد مصنعية مسجلة لهذا البند</td></tr>
        @endif
        <tr class="sub">
          <td style="text-align:left">إجمالي المصنعية</td>
          <td class="num">{{ \App\Support\Money::format($band->laborClientPrice()) }} ج.م</td>
        </tr>
      </tbody>
    </table>

    {{-- Final summary --}}
    <div class="st-final">
      <table>
        <tr class="big"><td>إجمالي مستحق العميل عن البند</td><td style="text-align:left">{{ \App\Support\Money::format($actualTotal) }} ج.م</td></tr>
      </table>
    </div>
  </div>

  <div class="st-foot">
    <span>توقيع العميل: ____________</span>
    <span>توقيع الإدارة: ____________</span>
  </div>
</div>
@endsection
