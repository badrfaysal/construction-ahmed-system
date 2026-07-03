@extends('layouts.app')
@section('title', 'كشف حساب — ' . $project->name)
@section('page-title', 'كشف حساب العميل')

@section('content')
<div class="page-head no-print">
  <div><h3>كشف حساب — {{ $project->name }}</h3><p>{{ $project->client->name }}</p></div>
  <div class="btn-row">
    <button onclick="window.print()" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      طباعة / حفظ PDF
    </button>
    <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
  </div>
</div>

<div class="statement">
  {{-- Company header --}}
  <div class="st-head">
    <div class="co">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></div>
      <h2>{{ $settings->company_name }}</h2>
      <p>{{ $settings->company_tagline }} @if($settings->company_phone)· هاتف {{ $settings->company_phone }}@endif @if($settings->company_registration)· سجل تجاري {{ $settings->company_registration }}@endif</p>
    </div>
    <div class="meta">
      <b>كشف حساب</b><br>
      رقم: INV-{{ 1000 + $project->id }}<br>
      التاريخ: {{ now()->format('d/m/Y') }}
    </div>
  </div>

  <div class="st-body">
    {{-- Client / project info --}}
    <div class="st-client">
      <div><div class="l">العميل</div><div class="b">{{ $project->client->name }}</div></div>
      <div><div class="l">المشروع</div><div class="b">{{ $project->name }} @if($project->area)· {{ rtrim(rtrim($project->area, '0'), '.') }} م²@endif</div></div>
      <div><div class="l">العنوان</div><div class="b" style="font-size:13px;font-weight:600">{{ $project->address ?: '—' }}</div></div>
    </div>

    {{-- Summary boxes --}}
    <div class="st-summary">
      <div class="st-box tot"><div class="l">قيمة التعاقد المبدئي</div><div class="v">{{ number_format($initialContractValue) }} ج.م</div></div>
      <div class="st-box paid"><div class="l">المدفوع</div><div class="v">{{ number_format($totalPaid) }} ج.م</div></div>
      <div class="st-box due"><div class="l">المتبقي</div><div class="v">{{ number_format($balance) }} ج.م</div></div>
    </div>

    {{-- Per-band expense breakdown --}}
    <div class="st-sec">تفاصيل المصروفات لكل بند</div>
    <table class="st-table">
      <thead><tr><th>التاريخ</th><th>البيان</th><th>الكمية</th><th>الوحدة</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead>
      <tbody>
        @forelse($spentBands as $band)
          <tr class="grp">
            <td colspan="6">بند: {{ $band->name }} <span class="bt">إجمالي البند: {{ number_format($band->actualClientTotal()) }} ج.م</span></td>
          </tr>
          @foreach($band->materials->sortBy('date') as $m)
            <tr>
              <td>{{ $m->date->format('Y-m-d') }}</td>
              <td>
                {{ $m->item }}
                @if($m->returnedQty() > 0)
                  <span style="color:var(--neg);font-size:10.5px">(مرتجع {{ number_format($m->returnedQty(), 1) }})</span>
                @endif
              </td>
              <td>{{ number_format($m->netQty(), 1) }}</td>
              <td>{{ $m->unit }}</td>
              <td>{{ number_format($m->clientUnitPrice()) }}</td>
              <td><b>{{ number_format($m->netClientCost()) }}</b></td>
            </tr>
          @endforeach
          @if($band->labor_amount > 0)
            <tr>
              <td>{{ $band->labor_date?->format('Y-m-d') ?? '—' }}</td>
              <td>مصنعية وتنفيذ — {{ $band->team_name ?: '—' }} ({{ $band->contract_type ?: '—' }})</td>
              <td>—</td><td>—</td><td>—</td>
              <td><b>{{ number_format($band->laborClientPrice()) }}</b></td>
            </tr>
          @endif
          <tr class="sub">
            <td colspan="5" style="text-align:left">إجمالي بند {{ $band->name }}</td>
            <td>{{ number_format($band->actualClientTotal()) }} ج.م</td>
          </tr>
        @empty
          <tr><td colspan="6" class="muted" style="text-align:center;padding:20px">لا توجد بنود بدأ العمل بها بعد</td></tr>
        @endforelse
        @if($generalMaterials->count())
          <tr class="grp">
            <td colspan="6">مصروفات عامة على المشروع <span class="bt">الإجمالي: {{ number_format($generalMaterials->sum(fn($m) => $m->netClientCost())) }} ج.م</span></td>
          </tr>
          @foreach($generalMaterials as $m)
            <tr>
              <td>{{ $m->date->format('Y-m-d') }}</td>
              <td>{{ $m->item }}</td>
              <td>{{ $m->category === 'misc' ? '—' : number_format($m->netQty(), 1) }}</td>
              <td>{{ $m->category === 'misc' ? '—' : $m->unit }}</td>
              <td>{{ number_format($m->clientUnitPrice()) }}</td>
              <td><b>{{ number_format($m->netClientCost()) }}</b></td>
            </tr>
          @endforeach
        @endif
        <tr class="sub" style="background:var(--accent-soft)">
          <td colspan="5" style="text-align:left;color:var(--accent-ink)">إجمالي المستحق حتى الآن</td>
          <td style="color:var(--accent-ink)">{{ number_format($actualTotal) }} ج.م</td>
        </tr>
      </tbody>
    </table>

    {{-- Payment plan --}}
    <div class="st-sec">خطة السداد ({{ $project->installments->count() }} دفعة)</div>
    <table class="st-table st-plan">
      <thead><tr><th>الدفعة</th><th>تاريخ الاستحقاق</th><th>المبلغ</th><th>الحالة</th></tr></thead>
      <tbody>
        @foreach($project->installments as $inst)
          <tr>
            <td style="font-weight:600">{{ $inst->label }}</td>
            <td>{{ $inst->due_date->format('Y-m-d') }}</td>
            <td><b>{{ number_format($inst->amount) }} ج.م</b></td>
            <td><span class="tag {{ $inst->statusTag() }}">{{ $inst->statusAr() }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Final summary --}}
    <div class="st-final">
      <table>
        <tr><td class="muted">قيمة التعاقد المبدئي</td><td style="text-align:left;font-weight:700">{{ number_format($initialContractValue) }} ج.م</td></tr>
        <tr><td class="muted">إجمالي المستحق حتى الآن</td><td style="text-align:left;font-weight:700">{{ number_format($actualTotal) }} ج.م</td></tr>
        <tr><td class="muted">إجمالي المدفوع</td><td style="text-align:left;font-weight:700;color:var(--pos)">{{ number_format($totalPaid) }} ج.م</td></tr>
        <tr class="big"><td>المتبقي المطلوب</td><td style="text-align:left">{{ number_format($balance) }} ج.م</td></tr>
      </table>
    </div>
  </div>

  <div class="st-foot">
    <span>كشف تفصيلي معتمد بكل بند ومصروفاته وكمياته وتواريخه.</span>
    <span>توقيع الشركة: ____________</span>
  </div>
</div>
@endsection
