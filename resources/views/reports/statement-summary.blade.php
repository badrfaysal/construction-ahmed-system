@extends('layouts.app')
@section('title', 'كشف حساب مختصر — ' . $project->name)
@section('page-title', 'كشف حساب العميل — مختصر')

@section('content')
<div class="page-head no-print">
  <div><h3>كشف حساب مختصر — {{ $project->name }}</h3><p>{{ $project->client->name }}</p></div>
  <div class="btn-row">
    <a href="{{ route('reports.statement', $project) }}" class="btn ghost">الكشف التفصيلي</a>
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
      <b>كشف حساب مختصر</b><br>
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
      <div class="st-box paid"><div class="l">المدفوع</div><div class="v">{{ \App\Support\Money::format($totalPaid) }} ج.م</div></div>
      <div class="st-box due"><div class="l">المتبقي</div><div class="v">{{ \App\Support\Money::format($balance) }} ج.م</div></div>
    </div>

    {{-- Per-band totals only — no material/quantity detail --}}
    <div class="st-sec">تكلفة كل بند</div>
    <table class="st-table">
      <thead><tr><th>البند</th><th>الحالة</th><th class="num">التكلفة الإجمالية</th></tr></thead>
      <tbody>
        @forelse($spentBands as $band)
          <tr>
            <td><strong>{{ $band->name }}</strong></td>
            <td><span class="tag {{ $band->status === 'done' ? 'green' : ($band->status === 'active' ? 'blue' : 'gray') }}">{{ $band->status === 'done' ? 'مكتمل' : ($band->status === 'active' ? 'جاري' : 'معلق') }}</span></td>
            <td class="num"><b>{{ \App\Support\Money::format($band->actualClientTotal()) }} ج.م</b></td>
          </tr>
        @empty
          <tr><td colspan="3" class="muted" style="text-align:center;padding:20px">لا توجد بنود بدأ العمل بها بعد</td></tr>
        @endforelse
        @if($generalTotal > 0)
          <tr>
            <td><strong>مصروفات عامة على المشروع</strong></td>
            <td>—</td>
            <td class="num"><b>{{ \App\Support\Money::format($generalTotal) }} ج.م</b></td>
          </tr>
        @endif
        <tr class="sub" style="background:var(--accent-soft)">
          <td colspan="2" style="text-align:left;color:var(--accent-ink)">إجمالي المستحق حتى الآن</td>
          <td class="num" style="color:var(--accent-ink)">{{ \App\Support\Money::format($actualTotal) }} ج.م</td>
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
            <td><b>{{ \App\Support\Money::format($inst->amount) }} ج.م</b></td>
            <td><span class="tag {{ $inst->statusTag() }}">{{ $inst->statusAr() }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Final summary --}}
    <div class="st-final">
      <table>
        <tr><td class="muted">إجمالي المستحق حتى الآن</td><td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($actualTotal) }} ج.م</td></tr>
        <tr><td class="muted">إجمالي المدفوع</td><td style="text-align:left;font-weight:700;color:var(--pos)">{{ \App\Support\Money::format($totalPaid) }} ج.م</td></tr>
        <tr class="big"><td>المتبقي المطلوب</td><td style="text-align:left">{{ \App\Support\Money::format($balance) }} ج.م</td></tr>
      </table>
    </div>
  </div>

  <div class="st-foot">
    <span>كشف مختصر — إجمالي كل بند فقط بدون تفاصيل الخامات والكميات.</span>
    <span>توقيع الشركة: ____________</span>
  </div>
</div>
@endsection
