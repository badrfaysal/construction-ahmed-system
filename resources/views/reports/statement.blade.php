@extends('layouts.app')
@section('title', 'كشف حساب — ' . $project->name)
@section('page-title', 'كشف حساب العميل')

@section('content')
<div class="page-head no-print">
  <div><h3>كشف حساب — {{ $project->name }}</h3><p>{{ $project->client->name }}</p></div>
  <div class="btn-row">
    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-inline-end:10px;font-size:13px;font-weight:600">
      <input type="checkbox" id="toggle-supervision" onchange="document.body.classList.toggle('show-supervision', this.checked)">
      إظهار نسبة الإشراف
    </label>
    <a href="{{ route('reports.statement.summary', $project) }}" class="btn ghost">الكشف المختصر</a>
    <button onclick="window.print()" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      طباعة / حفظ PDF
    </button>
    <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
  </div>
</div>

<style>
  .col-sup { display: none; }
  body.show-supervision .col-sup { display: table-cell; }
</style>

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
      <div class="st-box tot"><div class="l">إجمالي المستحق</div><div class="v">{{ \App\Support\Money::format($actualTotal) }} ج.م</div></div>
      @if($discountAmount > 0)
      <div class="st-box" style="background: #fee2e2; border-color: #fca5a5"><div class="l" style="color:#b91c1c">الخصم</div><div class="v" style="color:#b91c1c">{{ \App\Support\Money::format($discountAmount) }} ج.م</div></div>
      @endif
      <div class="st-box paid"><div class="l">المدفوع</div><div class="v">{{ \App\Support\Money::format($totalPaid) }} ج.م</div></div>
      <div class="st-box due"><div class="l">المتبقي</div><div class="v">{{ \App\Support\Money::format($balance) }} ج.م</div></div>
    </div>

    {{-- Per-band expense breakdown --}}
    <div class="st-sec">تفاصيل المصروفات لكل بند</div>
    <table class="st-table">
      <thead><tr><th>التاريخ</th><th>البيان</th><th>الكمية</th><th>الوحدة</th><th>سعر الوحدة</th><th class="col-sup">الإشراف (% ومبلغ)</th><th>الإجمالي</th></tr></thead>
      <tbody>
        @forelse($spentBands as $band)
          <tr class="grp">
            <td colspan="7">بند: {{ $band->name }} <span class="bt">إجمالي البند: {{ \App\Support\Money::format($band->actualClientTotal()) }} ج.م</span></td>
          </tr>
          @foreach($band->materials->sortBy('date') as $m)
            <tr>
              <td>{{ $m->date->format('Y-m-d') }}</td>
              <td>{{ $m->item }}</td>
              <td>{{ \App\Support\Money::format($m->netQty(), 1) }}</td>
              <td>{{ $m->unit }}</td>
              <td>{{ \App\Support\Money::format($m->clientUnitPrice()) }}</td>
              <td class="col-sup">
                {{ (float) $m->supervision_pct }}%
                @if((float) $m->supervision_pct > 0)
                  <br><small class="muted" style="font-size:12.5px;font-weight:600">({{ \App\Support\Money::format($m->percentageProfit()) }} ج.م)</small>
                @endif
              </td>
              <td><b>{{ \App\Support\Money::format($m->netClientCost()) }}</b></td>
            </tr>
          @endforeach
          @if($band->labor_amount > 0)
            <tr>
              <td>{{ $band->labor_date?->format('Y-m-d') ?? '—' }}</td>
              <td>مصنعية وتنفيذ — {{ $band->team_name ?: '—' }} ({{ $band->contract_type ?: '—' }})</td>
              <td>—</td><td>—</td><td>—</td>
              <td class="col-sup">
                @if($band->workers->isNotEmpty())
                  متفاوتة
                @else
                  {{ (float) $band->labor_supervision_pct }}%
                @endif
                @if($band->laborPercentageProfit() > 0)
                  <br><small class="muted" style="font-size:12.5px;font-weight:600">({{ \App\Support\Money::format($band->laborPercentageProfit()) }} ج.م)</small>
                @endif
              </td>
              <td><b>{{ \App\Support\Money::format($band->laborClientPrice()) }}</b></td>
            </tr>
          @endif
          <tr class="sub">
            <td colspan="6" style="text-align:left">إجمالي بند {{ $band->name }}</td>
            <td>{{ \App\Support\Money::format($band->actualClientTotal()) }} ج.م</td>
          </tr>
        @empty
          <tr><td colspan="7" class="muted" style="text-align:center;padding:20px">لا توجد بنود بدأ العمل بها بعد</td></tr>
        @endforelse
        @if($generalMaterials->count())
          <tr class="grp">
            <td colspan="7">مصروفات عامة على المشروع <span class="bt">الإجمالي: {{ \App\Support\Money::format($generalMaterials->sum(fn($m) => $m->netClientCost())) }} ج.م</span></td>
          </tr>
          @foreach($generalMaterials as $m)
            <tr>
              <td>{{ $m->date->format('Y-m-d') }}</td>
              <td>{{ $m->item }}</td>
              <td>{{ $m->category === 'misc' ? '—' : \App\Support\Money::format($m->netQty(), 1) }}</td>
              <td>{{ $m->category === 'misc' ? '—' : $m->unit }}</td>
              <td>{{ \App\Support\Money::format($m->clientUnitPrice()) }}</td>
              <td class="col-sup">
                {{ (float) $m->supervision_pct }}%
                @if((float) $m->supervision_pct > 0)
                  <br><small class="muted" style="font-size:12.5px;font-weight:600">({{ \App\Support\Money::format($m->percentageProfit()) }} ج.م)</small>
                @endif
              </td>
              <td><b>{{ \App\Support\Money::format($m->netClientCost()) }}</b></td>
            </tr>
          @endforeach
        @endif
        <tr class="sub" style="background:var(--accent-soft)">
          <td colspan="6" style="text-align:left;color:var(--accent-ink)">{{ $discountAmount > 0 ? 'إجمالي المستحق قبل الخصم' : 'إجمالي المستحق حتى الآن' }}</td>
          <td style="color:var(--accent-ink)">{{ \App\Support\Money::format($subTotal) }} ج.م</td>
        </tr>
        @if($discountAmount > 0)
        <tr class="sub" style="background: #fee2e2;">
          <td colspan="6" style="text-align:left;color: #b91c1c;">الخصم</td>
          <td style="color: #b91c1c;">{{ \App\Support\Money::format($discountAmount) }} ج.م</td>
        </tr>
        <tr class="sub" style="background:var(--accent-soft)">
          <td colspan="6" style="text-align:left;color:var(--accent-ink)">إجمالي المستحق بعد الخصم</td>
          <td style="color:var(--accent-ink)">{{ \App\Support\Money::format($actualTotal) }} ج.م</td>
        </tr>
        @endif
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
            <td><b>{{ \App\Support\Money::format($inst->amount) }} ج.م</b></td>
            <td><span class="tag {{ $inst->statusTag() }}">{{ $inst->statusAr() }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Final summary --}}
    <div class="st-final">
      <table>
        @if($discountAmount > 0)
        <tr><td class="muted">إجمالي المستحق قبل الخصم</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($subTotal) }} ج.م</td></tr>
        <tr><td class="muted">الخصم</td><td style="text-align:left;font-weight:700;color:#b91c1c">{{ \App\Support\Money::format($discountAmount) }} ج.م</td></tr>
        <tr><td class="muted">إجمالي المستحق بعد الخصم</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($actualTotal) }} ج.م</td></tr>
        @else
        <tr><td class="muted">إجمالي المستحق حتى الآن</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($actualTotal) }} ج.م</td></tr>
        @endif
        <tr><td class="muted">إجمالي المدفوع</td><td style="text-align:left;font-weight:700;color:var(--pos)">{{ \App\Support\Money::format($totalPaid) }} ج.م</td></tr>
        <tr class="big"><td>المتبقي المطلوب</td><td style="text-align:left">{{ \App\Support\Money::format($balance) }} ج.م</td></tr>
      </table>
    </div>
  </div>

  <div class="st-foot">
    <span>كشف تفصيلي معتمد بكل بند ومصروفاته وكمياته وتواريخه.</span>
    <span>توقيع الشركة: ____________</span>
  </div>
</div>
@endsection
