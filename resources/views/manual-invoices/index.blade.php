@extends('layouts.app')
@section('title', 'سجل الفواتير اليدوية')
@section('page-title', 'الفواتير اليدوية')

@section('content')
<div class="page-head no-print">
  <div>
    <h3>سجل الفواتير اليدوية</h3>
    <p>جميع الفواتير اليدوية المحفوظة في النظام</p>
  </div>
  <div class="btn-row">
    <a href="{{ route('manual_invoices.create') }}" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><use href="#i-plus-circle"/></svg>
      إنشاء فاتورة جديدة
    </a>
  </div>
</div>

<style>
  .mi-layout { display: grid; grid-template-columns: 1fr 300px; gap: 20px; align-items: start; }
  .mi-stat-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--line); margin-bottom: 16px; text-align: center; }
  .mi-stat-title { font-size: 13px; font-weight: 700; color: var(--ink-2); margin-bottom: 8px; }
  .mi-stat-value { font-size: 24px; font-weight: 800; color: var(--accent); }
  @media (max-width: 900px) {
    .mi-layout { grid-template-columns: 1fr; }
    .mi-layout > .mi-sidebar { grid-row: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .mi-layout > .mi-sidebar .mi-stat-card { margin-bottom: 0; }
  }
  @media (max-width: 600px) {
    .mi-layout > .mi-sidebar { grid-template-columns: 1fr; }
  }
</style>

<div class="mi-layout">
  <div class="mi-main">


{{-- Filters --}}
<div class="card" style="margin-bottom:20px;">
  <div class="card-pad">
    <form method="GET" action="{{ route('manual_invoices.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
      <div class="field" style="flex:1;min-width:180px;">
        <label style="font-size:12px;font-weight:700;margin-bottom:4px;display:block;">اسم العميل</label>
        <input type="text" name="client" class="inp" value="{{ request('client') }}" placeholder="بحث بالاسم...">
      </div>
      <div class="field" style="min-width:140px;">
        <label style="font-size:12px;font-weight:700;margin-bottom:4px;display:block;">الحالة</label>
        <select name="status" class="inp">
          <option value="">الكل</option>
          <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
          <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>نهائية</option>
        </select>
      </div>
      <div class="field" style="min-width:150px;">
        <label style="font-size:12px;font-weight:700;margin-bottom:4px;display:block;">من تاريخ</label>
        <input type="text" name="from" class="inp flatpickr-date" value="{{ request('from') }}" placeholder="من">
      </div>
      <div class="field" style="min-width:150px;">
        <label style="font-size:12px;font-weight:700;margin-bottom:4px;display:block;">إلى تاريخ</label>
        <input type="text" name="to" class="inp flatpickr-date" value="{{ request('to') }}" placeholder="إلى">
      </div>
      <button type="submit" class="btn sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-search"/></svg>
        بحث
      </button>
      @if(request()->hasAny(['client','status','from','to']))
        <a href="{{ route('manual_invoices.index') }}" class="btn ghost sm">مسح الفلتر</a>
      @endif
    </form>
  </div>
</div>

{{-- Invoices Table --}}
<div class="card">
  <div style="overflow-x:auto;">
    <table class="tbl">
      <thead>
        <tr>
          <th>رقم الفاتورة</th>
          <th>التاريخ</th>
          <th>العميل</th>
          <th>عدد الأصناف</th>
          <th>الإجمالي</th>
          <th>المدفوع</th>
          <th>المتبقي</th>
          <th>الحالة</th>
          <th style="width:140px;">إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr>
            <td style="font-weight:700;color:var(--accent);">{{ $inv->invoice_number }}</td>
            <td>{{ $inv->date->format('Y-m-d') }}</td>
            <td style="font-weight:600;">{{ $inv->client_name }}</td>
            <td>{{ $inv->items_count }} صنف</td>
            <td style="font-weight:700;">{{ \App\Support\Money::format($inv->grandTotal()) }}</td>
            <td style="color:var(--pos);font-weight:600;">{{ \App\Support\Money::format($inv->paid_amount) }}</td>
            <td style="color:{{ $inv->remaining() > 0 ? '#b91c1c' : 'var(--pos)' }};font-weight:700;">
              {{ \App\Support\Money::format($inv->remaining()) }}
            </td>
            <td><span class="tag {{ $inv->statusTag() }}">{{ $inv->statusAr() }}</span></td>
            <td>
              <div style="display:flex;gap:6px;">
                <a href="{{ route('manual_invoices.show', $inv) }}" class="btn ghost sm" title="عرض">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-doc"/></svg>
                </a>
                <a href="{{ route('manual_invoices.edit', $inv) }}" class="btn ghost sm" title="تعديل">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-tool"/></svg>
                </a>
                <form method="POST" action="{{ route('manual_invoices.destroy', $inv) }}" style="margin:0;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn ghost sm" style="color:#ef4444;" title="حذف">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-trash"/></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="muted" style="text-align:center;padding:40px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:40px;height:40px;margin-bottom:8px;opacity:.4"><use href="#i-receipt"/></svg>
              <br>لا توجد فواتير يدوية بعد
              <br><a href="{{ route('manual_invoices.create') }}" style="color:var(--accent);font-weight:700;margin-top:8px;display:inline-block;">إنشاء فاتورة جديدة</a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($invoices->hasPages())
  <div style="margin-top:20px;display:flex;justify-content:center;">
    {{ $invoices->links() }}
  </div>
@endif
  </div>

  <div class="mi-sidebar">
    <div class="mi-stat-card">
      <div class="mi-stat-title">إجمالي الفواتير</div>
      <div class="mi-stat-value" style="color: var(--ink);">{{ $totals['count'] }} <span style="font-size:14px;color:var(--ink-2);font-weight:600">فاتورة</span></div>
    </div>
    <div class="mi-stat-card" style="background: linear-gradient(135deg, #f0fdfa, #ccfbf1); border-color: #99f6e4;">
      <div class="mi-stat-title" style="color: #0f766e;">إجمالي الفواتير الضريبية</div>
      <div class="mi-stat-value" style="color: #0f766e;">{{ $stats['tax_invoices'] }}</div>
    </div>
    @if($stats['last_client'])
    <div class="mi-stat-card">
      <div class="mi-stat-title">آخر فاتورة كانت للعميل</div>
      <div class="mi-stat-value" style="font-size:18px;color:var(--accent-ink);">{{ $stats['last_client'] }}</div>
    </div>
    @endif
    @if($stats['top_client'])
    <div class="mi-stat-card" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #bfdbfe;">
      <div class="mi-stat-title" style="color: #1e40af;">أكثر عميل اتعمله فواتير</div>
      <div class="mi-stat-value" style="font-size:18px;color: #1e3a8a;">{{ $stats['top_client'] }}</div>
      <div style="font-size:13px;font-weight:600;color:#1e40af;margin-top:6px;">
        بإجمالي: {{ \App\Support\Money::format($stats['top_client_total']) }} ج.م <br>
        ({{ $stats['top_client_count'] }} فاتورة)
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
