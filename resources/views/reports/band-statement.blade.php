@extends('layouts.app')
@section('title', 'كشف حساب بند: ' . $band->name)
@section('page-title', 'كشف حساب البند')

@section('content')
<div class="page-head no-print">
  <div><h3>كشف حساب — بند {{ $band->name }}</h3><p>{{ $band->project->name }} · {{ $band->project->client->name }}</p></div>
  <div class="btn-row">
    <button onclick="window.print()" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      طباعة / حفظ PDF
    </button>
    <a href="{{ route('projects.show', $band->project) }}" class="btn ghost">رجوع</a>
  </div>
</div>

@php
  $materialCost = $band->materialCost();
  $laborCost = (float) $band->labor_amount;
  $totalCost = $band->totalCost();
  $actualTotal = $band->actualClientTotal();
  $profit = $band->profit();
@endphp

<div class="statement">
  <div class="st-head">
    <div class="co">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></div>
      <h2>{{ $settings->company_name }}</h2>
      <p>{{ $settings->company_tagline }} @if($settings->company_phone)· هاتف {{ $settings->company_phone }}@endif</p>
    </div>
    <div class="meta">
      <b>كشف حساب بند — داخلي</b><br>
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
      <div class="st-box tot"><div class="l">إجمالي التكلفة</div><div class="v">{{ \App\Support\Money::format($totalCost) }} ج.م</div></div>
      <div class="st-box"><div class="l">مستحق العميل</div><div class="v">{{ \App\Support\Money::format($actualTotal) }} ج.م</div></div>
      <div class="st-box {{ $profit >= 0 ? 'paid' : 'due' }}"><div class="l">الربح</div><div class="v">{{ \App\Support\Money::format($profit) }} ج.م</div></div>
    </div>

    {{-- Materials --}}
    <div class="st-sec">الخامات ({{ $band->materials->count() }})</div>
    <table class="st-table">
      <thead><tr><th>التاريخ</th><th>الصنف</th><th>المورد</th><th class="num">الكمية</th><th class="num">سعر الشراء</th><th class="num">سعر العميل</th><th class="num">التكلفة</th></tr></thead>
      <tbody>
        @forelse($band->materials->sortBy('date') as $m)
          <tr>
            <td>{{ $m->date->format('Y-m-d') }}</td>
            <td>
              {{ $m->item }}
              @if($m->returnedQty() > 0)
                <span style="color:var(--neg);font-size:10.5px">(مرتجع {{ \App\Support\Money::format($m->returnedQty(), 1) }})</span>
              @endif
            </td>
            <td class="muted">{{ $m->supplier?->name ?? '—' }}</td>
            <td class="num">{{ \App\Support\Money::format($m->netQty(), 1) }} {{ $m->unit }}</td>
            <td class="num">{{ \App\Support\Money::format($m->unit_price) }}</td>
            <td class="num">{{ \App\Support\Money::format($m->clientUnitPrice()) }}</td>
            <td class="num"><b>{{ \App\Support\Money::format($m->netCost()) }}</b></td>
          </tr>
        @empty
          <tr><td colspan="7" class="muted" style="text-align:center;padding:16px">لا توجد خامات مسجلة لهذا البند</td></tr>
        @endforelse
        <tr class="sub">
          <td colspan="6" style="text-align:left">إجمالي تكلفة الخامات</td>
          <td class="num">{{ \App\Support\Money::format($materialCost) }} ج.م</td>
        </tr>
      </tbody>
    </table>

    {{-- Labor --}}
    <div class="st-sec">المصنعية ({{ $band->workers->count() ?: ($band->labor_amount > 0 ? 1 : 0) }})</div>
    <table class="st-table">
      <thead><tr><th>الاسم</th><th>التخصص</th><th>نوع التعاقد</th><th class="num">التكلفة</th><th class="num">سعر العميل</th></tr></thead>
      <tbody>
        @forelse($band->workers as $w)
          <tr>
            <td>{{ $w->name }}</td>
            <td class="muted">{{ $w->specialty ?: '—' }}</td>
            <td class="muted">{{ $w->contractTypeAr() }}</td>
            <td class="num"><b>{{ \App\Support\Money::format($w->amount) }}</b></td>
            <td class="num">—</td>
          </tr>
        @empty
          @if($band->labor_amount > 0)
            <tr>
              <td>{{ $band->team_name ?: '—' }}</td>
              <td class="muted">—</td>
              <td class="muted">{{ $band->contract_type ? $band->contractTypeAr() : '—' }}</td>
              <td class="num"><b>{{ \App\Support\Money::format($band->labor_amount) }}</b></td>
              <td class="num">{{ \App\Support\Money::format($band->laborClientPrice()) }}</td>
            </tr>
          @else
            <tr><td colspan="5" class="muted" style="text-align:center;padding:16px">لا توجد مصنعية مسجلة لهذا البند</td></tr>
          @endif
        @endforelse
        <tr class="sub">
          <td colspan="3" style="text-align:left">إجمالي المصنعية</td>
          <td class="num">{{ \App\Support\Money::format($laborCost) }} ج.م</td>
          <td class="num">{{ \App\Support\Money::format($band->laborClientPrice()) }} ج.م</td>
        </tr>
      </tbody>
    </table>

    {{-- Final summary --}}
    <div class="st-final">
      <table>
        <tr><td class="muted">إجمالي التكلفة (خامات + مصنعية)</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($totalCost) }} ج.م</td></tr>
        <tr><td class="muted">مستحق العميل عن هذا البند</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($actualTotal) }} ج.م</td></tr>
        <tr class="big"><td>الربح</td><td style="text-align:left">{{ \App\Support\Money::format($profit) }} ج.م</td></tr>
      </table>
    </div>
  </div>

  <div class="st-foot">
    <span>كشف داخلي — لا يُشارك مع العميل.</span>
    <span>توقيع: ____________</span>
  </div>
</div>
@endsection
