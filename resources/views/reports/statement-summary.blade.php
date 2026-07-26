@extends('layouts.app')
@section('title', 'كشف حساب مختصر — ' . $project->name)
@section('page-title', 'كشف حساب العميل — مختصر')

@section('content')
<div class="page-head no-print">
  <div><h3>كشف حساب مختصر — {{ $project->name }}</h3><p>{{ $project->client->name }}</p></div>
  <div class="btn-row">
    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-inline-end:10px;font-size:13px;font-weight:600">
      <input type="checkbox" id="toggle-supervision" onchange="document.body.classList.toggle('show-supervision', this.checked)">
      إظهار الإشراف
    </label>
    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-inline-end:10px;font-size:13px;font-weight:600">
      <input type="checkbox" id="toggle-tax-invoice" onchange="document.body.classList.toggle('tax-invoice-mode', this.checked)">
      فاتورة ضريبية
    </label>
    <a href="{{ route('reports.statement', $project) }}" class="btn ghost">الكشف التفصيلي</a>
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
  .tax-invoice-label { display: none; }
  body.tax-invoice-mode .tax-invoice-label { display: inline; }
  body.tax-invoice-mode .standard-statement-label { display: none; }
  
  .tax-invoice-row { display: none; }
  body.tax-invoice-mode .tax-invoice-row { display: table-row; }
  body.tax-invoice-mode .standard-total-row { display: none; }
</style>

<div class="statement">
  {{-- Company header --}}
  <div class="st-head">
    <div class="co">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></div>
      <h2>{{ $settings->company_name }}</h2>
      <p>{{ $settings->company_tagline }} @if($settings->company_phone)· هاتف {{ $settings->company_phone }}@endif</p>
    </div>
    <div class="meta">
      <b>
        <span class="standard-statement-label">كشف حساب مختصر</span>
        <span class="tax-invoice-label">فاتورة ضريبية</span>
      </b><br>
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
      <div class="st-box tot">
        <div class="l">إجمالي المستحق</div>
        <div class="v">
          <span class="standard-statement-label">{{ \App\Support\Money::format($subTotal) }} ج.م</span>
          <span class="tax-invoice-label">{{ \App\Support\Money::format($taxInvoiceTotal ?? ($actualTotal + $project->tax_amount)) }} EGP</span>
        </div>
      </div>
      @if($discountAmount > 0)
      <div class="st-box" style="background: #fee2e2; border-color: #fca5a5"><div class="l" style="color:#b91c1c">الخصم</div><div class="v" style="color:#b91c1c">{{ \App\Support\Money::format($discountAmount) }} ج.م</div></div>
      @endif
      <div class="st-box paid"><div class="l">المدفوع</div><div class="v">{{ \App\Support\Money::format($totalPaid) }} ج.م</div></div>
      <div class="st-box due">
        <div class="l">المتبقي</div>
        <div class="v">
          <span class="standard-statement-label">{{ \App\Support\Money::format($balance) }} ج.م</span>
          <span class="tax-invoice-label">{{ \App\Support\Money::format(($taxInvoiceTotal ?? ($actualTotal + $project->tax_amount)) - $totalPaid) }} EGP</span>
        </div>
      </div>
    </div>

    {{-- Per-band totals only — no material/quantity detail --}}
    <div class="st-sec">تكلفة كل بند</div>
    <table class="st-table">
      <thead><tr><th>البند</th><th class="col-sup">الإشراف (% ومبلغ)</th><th class="num">التكلفة الإجمالية</th></tr></thead>
      <tbody>
        @forelse($spentBands as $band)
          @php
            $matClientTotal = $band->materialClientCost();
            $laborClientTotal = $band->laborClientPrice();
          @endphp
          @if($matClientTotal > 0)
            @php
              $matProfit = $band->materialPercentageProfit();
              $matPct = $band->materials->count() > 0 ? $band->materials->avg('supervision_pct') : 0;
            @endphp
            <tr>
              <td><strong>{{ $band->name }} (خامات)</strong></td>
              <td class="col-sup">
                {{ (float) number_format($matPct, 1) }}%
                @if($matProfit > 0)
                  <br><small class="muted" style="font-size:12.5px;font-weight:600">({{ \App\Support\Money::format($matProfit) }} ج.م)</small>
                @endif
              </td>
              <td class="num"><b>{{ \App\Support\Money::format($matClientTotal) }} ج.م</b></td>
            </tr>
          @endif
          @if($laborClientTotal > 0)
            @php
              $laborProfit = $band->laborPercentageProfit();
              $laborPct = $band->workers->count() > 0 ? $band->workers->avg('supervision_pct') : (float) $band->labor_supervision_pct;
            @endphp
            <tr>
              <td><strong>{{ $band->name }} (مصنعية)</strong></td>
              <td class="col-sup">
                {{ (float) number_format($laborPct, 1) }}%
                @if($laborProfit > 0)
                  <br><small class="muted" style="font-size:12.5px;font-weight:600">({{ \App\Support\Money::format($laborProfit) }} ج.م)</small>
                @endif
              </td>
              <td class="num"><b>{{ \App\Support\Money::format($laborClientTotal) }} ج.م</b></td>
            </tr>
          @endif
        @empty
          <tr><td colspan="3" class="muted" style="text-align:center;padding:20px">لا توجد بنود بدأ العمل بها بعد</td></tr>
        @endforelse
        @if($generalTotal > 0)
          @php
            $genProfit = $project->generalMaterials()->sum(fn($m) => $m->percentageProfit());
            $genPct = $project->generalMaterials()->count() > 0 ? $project->generalMaterials()->avg('supervision_pct') : 0;
          @endphp
          <tr>
            <td><strong>مصروفات عامة على المشروع</strong></td>
            <td class="col-sup">
              {{ (float) number_format($genPct, 1) }}%
              @if($genProfit > 0)
                <br><small class="muted" style="font-size:12.5px;font-weight:600">({{ \App\Support\Money::format($genProfit) }} ج.م)</small>
              @endif
            </td>
            <td class="num"><b>{{ \App\Support\Money::format($generalTotal) }} ج.م</b></td>
          </tr>
        @endif
        @if(isset($interest) && $interest > 0)
        <tr>
          <td><strong>فوائد التقسيط المضافة</strong></td>
          <td class="col-sup">—</td>
          <td class="num"><b>{{ \App\Support\Money::format($interest) }} ج.م</b></td>
        </tr>
        @endif
        <tr class="sub" style="background:var(--accent-soft)">
          @php
            $projProfit = $project->percentageProfit();
          @endphp
          <td style="text-align:left;color:var(--accent-ink)">{{ $discountAmount > 0 ? 'إجمالي المستحق قبل الخصم' : 'إجمالي المستحق حتى الآن' }}</td>
          <td class="col-sup" style="color:var(--accent-ink)">
            @if($projProfit > 0)
              <span style="font-size:14px;font-weight:600;opacity:0.9">({{ \App\Support\Money::format($projProfit) }} ج.م)</span>
            @endif
          </td>
          <td class="num" style="color:var(--accent-ink)">{{ \App\Support\Money::format($subTotal) }} ج.م</td>
        </tr>
        @if($discountAmount > 0)
        <tr class="sub" style="background: #fee2e2;">
          <td colspan="2" style="text-align:left;color: #b91c1c;">الخصم</td>
          <td class="num" style="color: #b91c1c;">{{ \App\Support\Money::format($discountAmount) }} ج.م</td>
        </tr>
        <tr class="sub" style="background:var(--accent-soft)">
          <td colspan="2" style="text-align:left;color:var(--accent-ink)">إجمالي المستحق بعد الخصم</td>
          <td class="num" style="color:var(--accent-ink)">{{ \App\Support\Money::format($actualTotal) }} ج.م</td>
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
      <div class="st-final-blocks">
        <table>
          @php
             $subTax = $actualTotal + $discountAmount;
             $taxInvoiceTotal = $actualTotal + $project->tax_amount;
          @endphp
          
          {{-- TAX INVOICE MODE ROWS --}}
          <tr class="tax-invoice-row"><td class="muted" style="text-align:right">المجموع</td><td style="text-align:left;font-weight:700;color:var(--ink-2)">{{ \App\Support\Money::format($subTax) }} EGP</td></tr>
          @if($discountAmount > 0)
          <tr class="tax-invoice-row"><td class="muted" style="text-align:right">الخصم</td><td style="text-align:left;font-weight:700;color:#b91c1c">-{{ \App\Support\Money::format($discountAmount) }} EGP</td></tr>
          <tr class="tax-invoice-row"><td class="muted" style="text-align:right">المجموع بعد الخصم</td><td style="text-align:left;font-weight:700;color:var(--ink-2)">{{ \App\Support\Money::format($actualTotal) }} EGP</td></tr>
          @endif
          <tr class="tax-invoice-row"><td class="muted" style="text-align:right">الضريبة ({{ (float) $project->tax_pct }}%)</td><td style="text-align:left;font-weight:700;color:var(--pos)">+{{ \App\Support\Money::format($project->tax_amount) }} EGP</td></tr>
          <tr class="tax-invoice-row" style="background:#005c97;color:#fff"><td style="font-weight:700;color:#fff;font-size:14.5px">الإجمالي النهائي</td><td style="text-align:left;font-weight:700;color:#fff;font-size:14.5px">{{ \App\Support\Money::format($taxInvoiceTotal) }} EGP</td></tr>

          {{-- STANDARD MODE ROWS --}}
          @if($discountAmount > 0)
          <tr class="standard-total-row"><td class="muted" style="text-align:right">إجمالي المستحق قبل الخصم</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($subTotal) }} ج.م</td></tr>
          <tr class="standard-total-row"><td class="muted" style="text-align:right">الخصم</td><td style="text-align:left;font-weight:700;color:#b91c1c">-{{ \App\Support\Money::format($discountAmount) }} ج.م</td></tr>
          <tr class="standard-total-row"><td class="muted" style="text-align:right">إجمالي المستحق بعد الخصم</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($actualTotal) }} ج.م</td></tr>
          @else
          <tr class="standard-total-row"><td class="muted" style="text-align:right">إجمالي المستحق حتى الآن</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($actualTotal) }} ج.م</td></tr>
          @endif
        </table>

        <table>
          {{-- ALWAYS SHOWN PAYMENTS --}}
          <tr><td class="muted" style="text-align:right">المدفوع</td><td style="text-align:left;font-weight:700;color:var(--pos)">{{ \App\Support\Money::format($totalPaid) }} ج.م</td></tr>
          <tr class="big">
            <td style="text-align:right;font-size:16px;font-weight:700">المتبقي المطلوب</td>
            <td style="text-align:left;font-size:20px;font-weight:800">
              <span class="standard-statement-label">{{ \App\Support\Money::format($balance) }} ج.م</span>
              <span class="tax-invoice-label">{{ \App\Support\Money::format(($taxInvoiceTotal ?? ($actualTotal + $project->tax_amount)) - $totalPaid) }} EGP</span>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="st-foot">
    <span>كشف مختصر — إجمالي كل بند فقط بدون تفاصيل الخامات والكميات.</span>
    <span>توقيع الشركة: ____________</span>
  </div>
</div>
@endsection
