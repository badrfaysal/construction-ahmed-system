@extends('layouts.app')
@section('title', 'كشف حساب الشركة: ' . $project->name)
@section('page-title', 'كشف حساب الشركة')

@section('content')
<div class="page-head no-print">
  <div><h3>كشف حساب الشركة (التكلفة) — {{ $project->name }}</h3><p>{{ $project->client->name }} · تكلفة كل بند وكل عملية شراء</p></div>
  <div class="btn-row">
    <button onclick="window.print()" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      طباعة / حفظ PDF
    </button>
    <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
  </div>
</div>

<div class="statement">
  <div class="st-head">
    <div class="co">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></div>
      <h2>{{ $settings->company_name }}</h2>
      <p>{{ $settings->company_tagline }} @if($settings->company_phone)· هاتف {{ $settings->company_phone }}@endif</p>
    </div>
    <div class="meta">
      <b>كشف حساب الشركة — داخلي</b><br>
      المشروع: {{ $project->name }}<br>
      التاريخ: {{ now()->format('d/m/Y') }}
    </div>
  </div>

  <div class="st-body">
    <div class="st-client">
      <div><div class="l">المشروع</div><div class="b">{{ $project->name }}</div></div>
      <div><div class="l">العميل</div><div class="b">{{ $project->client->name }}</div></div>
      <div><div class="l">عدد البنود</div><div class="b">{{ $project->bands->count() }}</div></div>
    </div>

    {{-- Project summary --}}
    <div class="st-summary">
      <div class="st-box tot"><div class="l">إجمالي التكلفة</div><div class="v">{{ \App\Support\Money::format($totalCost) }} ج.م</div></div>
      <div class="st-box"><div class="l">المفوتر على العميل</div><div class="v">{{ \App\Support\Money::format($totalBilled) }} ج.م</div></div>
      <div class="st-box paid"><div class="l">المحصّل</div><div class="v">{{ \App\Support\Money::format($totalCollected) }} ج.م</div></div>
      <div class="st-box {{ $totalProfit >= 0 ? 'paid' : 'due' }}"><div class="l">الربح</div><div class="v">{{ \App\Support\Money::format($totalProfit) }} ج.م</div></div>
    </div>

    @foreach($project->bands as $band)
      @php
        $bMaterialCost = $band->materialCost();
        $bLaborCost    = (float) $band->labor_amount;
        $bTotalCost    = $band->totalCost();
        $bProfit       = $band->profit();
      @endphp

      <div class="st-sec" style="margin-top:22px;background:var(--bg-soft, #f4f6fa)">
        بند: {{ $band->name }}
        <span class="muted" style="font-weight:400">— تكلفة {{ \App\Support\Money::format($bTotalCost) }} ج.م · ربح {{ \App\Support\Money::format($bProfit) }} ج.م</span>
      </div>

      {{-- Purchases (materials + misc expenses) --}}
      <table class="st-table">
        <thead><tr><th>التاريخ</th><th>البيان</th><th>المورد</th><th class="num">الكمية</th><th class="num">سعر الشراء</th><th class="num">التكلفة</th></tr></thead>
        <tbody>
          @forelse($band->materials->sortBy('date') as $m)
            <tr>
              <td>{{ $m->date->format('Y-m-d') }}</td>
              <td>
                {{ $m->item }}
                @if($m->isMisc())<span class="muted" style="font-size:10.5px">(نثري)</span>@endif
                @if($m->returnedQty() > 0)<span style="color:var(--neg);font-size:10.5px">(مرتجع {{ \App\Support\Money::format($m->returnedQty(), 1) }})</span>@endif
              </td>
              <td class="muted">{{ $m->supplier?->name ?? '—' }}</td>
              <td class="num">{{ \App\Support\Money::format($m->netQty(), 1) }} {{ $m->unit }}</td>
              <td class="num">{{ \App\Support\Money::format($m->unit_price) }}</td>
              <td class="num"><b>{{ \App\Support\Money::format($m->netCost()) }}</b></td>
            </tr>
          @empty
            <tr><td colspan="6" class="muted" style="text-align:center;padding:12px">لا مشتريات لهذا البند</td></tr>
          @endforelse
          <tr class="sub">
            <td colspan="5" style="text-align:left">إجمالي تكلفة المشتريات</td>
            <td class="num">{{ \App\Support\Money::format($bMaterialCost) }} ج.م</td>
          </tr>
        </tbody>
      </table>

      {{-- Labor / technicians --}}
      <table class="st-table" style="margin-top:8px">
        <thead><tr><th>الفني</th><th>التخصص</th><th>نوع التعاقد</th><th>بداية العمل</th><th class="num">الأجر (التكلفة)</th></tr></thead>
        <tbody>
          @forelse($band->workers as $w)
            <tr>
              <td>{{ $w->name }}</td>
              <td class="muted">{{ $w->specialty ?: '—' }}</td>
              <td class="muted">{{ $w->contractTypeAr() }}</td>
              <td class="muted">{{ $w->start_date?->format('Y-m-d') ?? '—' }}</td>
              <td class="num"><b>{{ \App\Support\Money::format($w->amount) }}</b></td>
            </tr>
          @empty
            @if($band->labor_amount > 0)
              <tr>
                <td>{{ $band->team_name ?: '—' }}</td>
                <td class="muted">—</td>
                <td class="muted">{{ $band->contract_type ? $band->contractTypeAr() : '—' }}</td>
                <td class="muted">—</td>
                <td class="num"><b>{{ \App\Support\Money::format($band->labor_amount) }}</b></td>
              </tr>
            @else
              <tr><td colspan="5" class="muted" style="text-align:center;padding:12px">لا مصنعية لهذا البند</td></tr>
            @endif
          @endforelse
          <tr class="sub">
            <td colspan="4" style="text-align:left">إجمالي المصنعية</td>
            <td class="num">{{ \App\Support\Money::format($bLaborCost) }} ج.م</td>
          </tr>
        </tbody>
      </table>
    @endforeach

    {{-- Final project summary --}}
    <div class="st-final">
      <table>
        <tr><td class="muted">إجمالي تكلفة المشروع (مشتريات + مصنعية)</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($totalCost) }} ج.م</td></tr>
        <tr><td class="muted">المفوتر على العميل</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($totalBilled) }} ج.م</td></tr>
        <tr><td class="muted">المحصّل من العميل</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($totalCollected) }} ج.م</td></tr>
        <tr class="big"><td>صافي الربح</td><td style="text-align:left">{{ \App\Support\Money::format($totalProfit) }} ج.م</td></tr>
      </table>
    </div>
  </div>

  <div class="st-foot">
    <span>كشف داخلي — لا يُشارك مع العميل.</span>
    <span>توقيع: ____________</span>
  </div>
</div>
@endsection
