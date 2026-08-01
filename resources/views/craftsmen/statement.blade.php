@extends('layouts.app')
@section('title', 'كشف حساب صنايعي - ' . $name)
@section('page-title', 'كشف حساب: ' . $name)

@section('content')
@php
  $settings = \App\Models\Settings::first() ?? new \App\Models\Settings;
@endphp

<div class="page-head no-print">
  <div><h3>كشف حساب تفصيلي للصنايعي: <span style="color:var(--main)">{{ $name }}</span></h3><p>يعرض جميع الاستحقاقات والدفعات بتسلسل زمني</p></div>
  <div class="btn-row">
    <a href="{{ route('craftsmen.index') }}" class="btn ghost">رجوع للقائمة</a>
    <button onclick="window.print()" class="btn primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      طباعة كشف رسمي
    </button>
  </div>
</div>

<style>
  @media print {
    @page { margin: 0; } /* Removes browser's default header/footer (URL, Title, etc.) */
    body { padding: 10mm; }
    body, .statement { font-size: 11px !important; line-height: 1.3 !important; }
    
    /* Reduce Head Margins & Fonts */
    .st-head { margin-bottom: 10px !important; padding-bottom: 10px !important; }
    .st-head h2 { font-size: 16px !important; margin-bottom: 2px !important; }
    .st-head p { font-size: 10px !important; margin-bottom: 0 !important; }
    .meta { font-size: 11px !important; line-height: 1.3 !important; }

    /* Reduce Client Box */
    .st-client { padding: 8px 12px !important; margin-bottom: 10px !important; gap: 15px !important; }
    .st-client .l { font-size: 10px !important; margin-bottom: 2px !important; }
    .st-client .b { font-size: 12px !important; }

    /* Reduce Summary Boxes */
    .st-summary { gap: 10px !important; margin-bottom: 10px !important; }
    .st-box { padding: 6px !important; }
    .st-box .l { font-size: 10px !important; margin-bottom: 2px !important; }
    .st-box .v { font-size: 13px !important; }

    /* Reduce Table */
    .st-sec { font-size: 12px !important; margin: 10px 0 10px !important; padding: 0 !important; }
    .st-table th, .st-table td { padding: 6px 8px !important; font-size: 11px !important; }
    
    /* Signatures spacing */
    .only-print { margin-top: 40px !important; font-size: 12px !important; }
    .only-print > div > div { margin-top: 35px !important; }
  }
</style>

<form method="GET" class="filter-bar no-print" style="margin-bottom: 20px;">
  <div class="f-field">
    <label>المشروع</label>
    <div class="f-select-wrap">
      <select name="project_id" class="f-select" onchange="this.form.submit()">
        <option value="">كل المشاريع</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
    </div>
  </div>
  <div class="f-field">
    <label>من تاريخ</label>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="f-inp" onchange="this.form.submit()">
  </div>
  <div class="f-field">
    <label>إلى تاريخ</label>
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="f-inp" onchange="this.form.submit()">
  </div>
  @if(request()->hasAny(['project_id','date_from','date_to']))
    <div class="f-actions">
      <a href="{{ route('craftsmen.statement', $name) }}" class="btn ghost sm">مسح الفلتر</a>
    </div>
  @endif
</form>

@php
  $periodCredit = $filteredLedger->sum('credit');
  $periodDebit = $filteredLedger->sum('debit');
  $closingBalance = $openingBalance + $periodCredit - $periodDebit;
@endphp

<div class="statement">
  {{-- Company header --}}
  <div class="st-head">
    <div class="co">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></div>
      <h2>{{ $settings->company_name ?? 'شركة الإنشاءات والمقاولات' }}</h2>
      <p>{{ $settings->company_tagline ?? 'نظام إدارة المشاريع المتكامل' }} @if($settings->company_phone)· هاتف {{ $settings->company_phone }}@endif</p>
    </div>
    <div class="meta">
      <b>كشف حساب مقاول / صنايعي</b><br>
      تاريخ الطباعة: {{ now()->format('d/m/Y') }}
    </div>
  </div>

  <div class="st-body">
    {{-- Client / project info --}}
    <div class="st-client">
      <div><div class="l">اسم المقاول</div><div class="b">{{ $name }}</div></div>
      <div><div class="l">المشروع</div><div class="b">{{ request('project_id') ? ($projects->where('id', request('project_id'))->first()->name ?? 'الكل') : 'جميع المشاريع' }}</div></div>
      <div><div class="l">الفترة</div><div class="b" dir="ltr" style="font-size:13px;font-weight:600">{{ request('date_from') ?? 'البداية' }} — {{ request('date_to') ?? 'الآن' }}</div></div>
    </div>

    {{-- Summary boxes --}}
    <div class="st-summary">
      <div class="st-box tot">
        <div class="l">إجمالي مستحق له</div>
        <div class="v">{{ \App\Support\Money::format($periodCredit) }} ج.م</div>
      </div>
      <div class="st-box paid">
        <div class="l">إجمالي المسدد</div>
        <div class="v">{{ \App\Support\Money::format($periodDebit) }} ج.م</div>
      </div>
      <div class="st-box due" style="background: var(--bg-muted)">
        <div class="l">الرصيد النهائي</div>
        <div class="v" style="color: {{ $closingBalance > 0 ? 'var(--neg)' : 'var(--pos)' }}">
          {{ \App\Support\Money::format($closingBalance) }} ج.م
        </div>
      </div>
    </div>

    {{-- Ledger Details --}}
    <div class="st-sec">التفاصيل الزمنية للعمليات</div>
    <table class="st-table">
      <thead>
        <tr>
          <th style="width: 100px;">التاريخ</th>
          <th style="width: 130px;">نوع الحركة</th>
          <th>البيان</th>
          <th style="width: 110px;">مستحق له</th>
          <th style="width: 110px;">تم سداد</th>
          <th style="width: 110px;">الرصيد</th>
        </tr>
      </thead>
      <tbody>
        @if(request('date_from'))
          <tr style="background: var(--bg-muted, #f8f9fa)">
            <td>—</td>
            <td><strong>رصيد افتتاحي</strong></td>
            <td>رصيد ما قبل تاريخ {{ request('date_from') }}</td>
            <td class="num">{{ $openingBalance > 0 ? \App\Support\Money::format($openingBalance) : '-' }}</td>
            <td class="num">{{ $openingBalance < 0 ? \App\Support\Money::format(abs($openingBalance)) : '-' }}</td>
            <td class="num" style="font-weight: bold; color: {{ $openingBalance > 0 ? 'var(--neg)' : 'var(--pos)' }}">
              {{ \App\Support\Money::format($openingBalance) }}
            </td>
          </tr>
        @endif

        @php $runningBalance = $openingBalance; @endphp
        @forelse($filteredLedger as $row)
          @php $runningBalance += $row['credit'] - $row['debit']; @endphp
          <tr>
            <td style="white-space:nowrap;">{{ $row['date'] }}</td>
            <td>{{ $row['type'] }}</td>
            <td>{!! $row['description'] !!}</td>
            <td class="num" style="color:var(--pos, #10b981); font-weight:600">{{ $row['credit'] > 0 ? \App\Support\Money::format($row['credit']) : '-' }}</td>
            <td class="num" style="color:var(--neg, #ef4444); font-weight:600">{{ $row['debit'] > 0 ? \App\Support\Money::format($row['debit']) : '-' }}</td>
            <td class="num" style="font-weight: bold; color: {{ $runningBalance > 0 ? 'var(--neg)' : 'var(--pos)' }}">
              {{ \App\Support\Money::format($runningBalance) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align: center; padding: 20px;">لا توجد حركات مسجلة في هذه الفترة</td>
          </tr>
        @endforelse
        
        <tr class="tot-row" style="background: var(--bg-muted, #f8f9fa); font-weight: bold;">
          <td colspan="3" style="text-align: left;">الإجمالي الكلي</td>
          <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($periodCredit + ($openingBalance > 0 ? $openingBalance : 0)) }}</td>
          <td class="num" style="color:var(--neg)">{{ \App\Support\Money::format($periodDebit + ($openingBalance < 0 ? abs($openingBalance) : 0)) }}</td>
          <td class="num" style="color: {{ $closingBalance > 0 ? 'var(--neg)' : 'var(--pos)' }}">
            {{ \App\Support\Money::format($closingBalance) }}
          </td>
        </tr>
      </tbody>
    </table>
    
    <div style="margin-top:20px; display:flex; justify-content:space-around; font-weight:bold; page-break-inside: avoid;" class="only-print">
      <div style="text-align:center">
        توقيع المقاول / الصنايعي<br>
        <div style="margin-top:25px; border-bottom:1px solid #000; width:150px; margin-inline:auto;"></div>
      </div>
      <div style="text-align:center">
        المحاسب<br>
        <div style="margin-top:25px; border-bottom:1px solid #000; width:150px; margin-inline:auto;"></div>
      </div>
      <div style="text-align:center">
        اعتماد الإدارة<br>
        <div style="margin-top:25px; border-bottom:1px solid #000; width:150px; margin-inline:auto;"></div>
      </div>
    </div>

  </div>
</div>
@endsection
