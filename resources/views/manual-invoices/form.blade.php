@extends('layouts.app')
@section('title', $invoice ? 'تعديل فاتورة — ' . $invoice->invoice_number : 'إنشاء فاتورة يدوية')
@section('page-title', $invoice ? 'تعديل فاتورة يدوية' : 'إنشاء فاتورة يدوية')

@section('content')
<style>
  /* ─── Card sections ─────────────────────────────────────────────── */
  .mi-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    margin-bottom: 24px;
    position: relative;
    z-index: 2;
  }
  .mi-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--line);
    font-weight: 800;
    font-size: 15px;
    color: var(--ink);
    gap: 8px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
  }
  .mi-card-head > span, .mi-card-head > button { position: relative; z-index: 2; }
  .mi-card-head svg:not(.decor-icon) { width: 20px; height: 20px; flex-shrink: 0; }
  
  @keyframes float1 {
    0% { transform: translateY(0) rotate(-15deg); }
    50% { transform: translateY(-15px) rotate(-5deg); }
    100% { transform: translateY(0) rotate(-15deg); }
  }
  @keyframes float2 {
    0% { transform: translateY(0) rotate(20deg); }
    50% { transform: translateY(-10px) rotate(30deg); }
    100% { transform: translateY(0) rotate(20deg); }
  }
  .decor-icon {
    position: absolute;
    opacity: 0.05;
    z-index: 1;
    pointer-events: none;
  }
  .mi-card-body { padding: 24px; }

  /* ─── Form fields ───────────────────────────────────────────────── */
  .mi-field { margin-bottom: 0; }
  .mi-field label {
    display: block;
    font-weight: 700;
    font-size: 13px;
    margin-bottom: 6px;
    color: var(--ink);
  }
  .mi-field .req { color: #ef4444; }
  .mi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 18px;
  }

  /* ─── Premium Inputs ────────────────────────────────────────────── */
  .inp {
    width: 100%;
    box-sizing: border-box;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    color: var(--ink);
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    transition: all 0.2s ease;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
  }
  .inp::placeholder { color: #94a3b8; font-weight: 500; }
  .inp:hover { border-color: #cbd5e1; background: #fff; }
  .inp:focus {
    outline: none;
    border-color: var(--accent);
    background: #fff;
    box-shadow: 0 0 0 4px var(--accent-soft);
  }
  .inp[readonly] {
    background: #f1f5f9;
    border-color: #e2e8f0;
    color: var(--ink-2);
    cursor: not-allowed;
  }
  /* Flatpickr input fix */
  .flatpickr-input[readonly] {
    background: #f8fafc;
    cursor: text;
    color: var(--ink);
  }
  .flatpickr-input[readonly]:focus {
    background: #fff;
  }

  /* ─── Autocomplete ──────────────────────────────────────────────── */
  .ac-wrap { position: relative; }
  .ac-list {
    position: fixed;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 10px;
    box-shadow: 0 12px 32px rgba(0,0,0,.15);
    z-index: 999999;
    max-height: 240px;
    overflow-y: auto;
    display: none;

  }
  .ac-list.show { display: block; }
  .ac-list .ac-item {
    padding: 10px 16px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    border-bottom: 1px solid var(--line);
    transition: background .12s;
  }
  .ac-list .ac-item:last-child { border-bottom: none; }
  .ac-list .ac-item:hover { background: var(--accent-soft); color: var(--accent-ink); }
  .ac-list .ac-empty {
    padding: 12px 16px;
    color: var(--ink-3);
    font-size: 13px;
    text-align: center;
    font-weight: 600;
  }

  /* ─── Items table ───────────────────────────────────────────────── */
  .mi-items-wrap {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
  }
  .mi-items-table {
    width: 100%;
    min-width: 820px;
    border-collapse: collapse;
  }
  .mi-items-table thead th {
    padding: 12px 10px;
    font-size: 13px;
    font-weight: 800;
    color: #1e40af;
    text-align: right;
    border-bottom: 2px solid #bfdbfe;
    white-space: nowrap;
    background: linear-gradient(90deg, #f8fafc, #eff6ff);
  }
  @keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .row-anim {
    animation: slideIn 0.3s ease-out forwards;
  }
  .mi-items-table tbody td {
    padding: 8px 8px;
    vertical-align: top;
    border-bottom: 1px solid var(--line);
  }
  .mi-items-table tbody tr:last-child td { border-bottom: none; }
  .mi-items-table .inp {
    min-width: 0;
    font-size: 13.5px;
    padding: 8px 12px;
    border-radius: 8px;
  }
  .mi-items-table .row-num {
    font-weight: 700;
    color: var(--ink-3);
    text-align: center;
    font-size: 13px;
    padding-top: 14px;
  }
  .mi-items-table .row-total {
    font-weight: 800;
    font-size: 14px;
    text-align: center;
    padding-top: 14px;
    color: var(--ink);
    white-space: nowrap;
  }
  .mi-items-table tfoot td {
    padding: 14px 10px;
    background: var(--bg2, var(--bg));
    font-weight: 800;
    font-size: 15px;
    border-top: 2px solid var(--line);
  }

  /* ─── Row action buttons ────────────────────────────────────────── */
  .del-row-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #ef4444;
    padding: 8px;
    border-radius: 8px;
    transition: background .15s;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 4px;
  }
  .del-row-btn:hover { background: #fef2f2; }
  .del-row-btn svg { width: 18px; height: 18px; }

  /* ─── Summary section ───────────────────────────────────────────── */
  .mi-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed var(--line);
  }
  .mi-summary-row:last-child { border-bottom: none; }
  .mi-summary-input {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 6px 0;
  }
  .mi-summary-input label {
    white-space: nowrap;
    font-weight: 700;
    font-size: 13px;
    min-width: 100px;
  }
  .mi-summary-input .inp { max-width: 160px; }
  .mi-highlight {
    margin: 8px -24px;
    padding: 14px 24px;
    border-radius: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .mi-highlight:first-of-type { border-radius: 12px 12px 0 0; }
  .mi-highlight:last-of-type { border-radius: 0 0 12px 12px; }

  /* ─── Bottom layout ─────────────────────────────────────────────── */
  .mi-bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
  }

  /* ─── Responsive ────────────────────────────────────────────────── */
  @media (max-width: 768px) {
    .mi-grid { grid-template-columns: 1fr !important; }
    .mi-grid .span-2 { grid-column: span 1 !important; }
    .mi-bottom-grid { grid-template-columns: 1fr; }
    .mi-card-body { padding: 16px; }
    .mi-highlight { margin: 8px -16px; padding: 14px 16px; }
  }
</style>

<div class="page-head no-print">
  <div>
    <h3>
      @if($invoice && !($isDuplicate ?? false))
        تعديل فاتورة — {{ $invoice->invoice_number }}
      @elseif($isDuplicate ?? false)
        إنشاء فاتورة (نسخة)
      @else
        إنشاء فاتورة يدوية جديدة
      @endif
    </h3>
    <p>{{ ($invoice && !($isDuplicate ?? false)) ? 'تعديل بيانات وأصناف الفاتورة' : 'أدخل بيانات العميل والأصناف لإنشاء فاتورة' }}</p>
  </div>
  <div class="btn-row" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
    @if(!$invoice && !($isDuplicate ?? false))
    <div style="position:relative;">
      <select class="inp" style="background:#fff;border-color:var(--accent);color:var(--accent-ink);font-weight:700;padding-inline-end:30px;min-width:220px;" onchange="if(this.value) window.location.href='/manual-invoices/create?'+this.value">
        <option value="">+ نسخ من فاتورة سابقة...</option>
        @if(isset($recentInvoices) && $recentInvoices->count() > 0)
          <optgroup label="الفواتير اليدوية للعملاء">
            @foreach($recentInvoices as $ri)
              <option value="copy_from_manual={{ $ri->id }}">{{ $ri->invoice_number }} ({{ $ri->client_name }})</option>
            @endforeach
          </optgroup>
        @endif
        @if(isset($recentMaterialInvoices) && $recentMaterialInvoices->count() > 0)
          <optgroup label="فواتير المشتريات / الخامات">
            @foreach($recentMaterialInvoices as $rmi)
              <option value="copy_from_material={{ $rmi->id }}">فاتورة مشتريات: {{ $rmi->name ?: 'بدون اسم' }}</option>
            @endforeach
          </optgroup>
        @endif
      </select>
    </div>
    @endif
    <a href="{{ route('manual_invoices.index') }}" class="btn ghost">رجوع للسجل</a>
  </div>
</div>

<form method="POST"
      action="{{ ($invoice && !($isDuplicate ?? false)) ? route('manual_invoices.update', $invoice) : route('manual_invoices.store') }}"
      id="invoiceForm">
  @csrf
  @if($invoice && !($isDuplicate ?? false)) @method('PUT') @endif

  @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;padding:16px 20px;margin-bottom:20px;border-radius:12px;">
      <ul style="margin:0;padding:0 20px;color:#b91c1c;font-weight:600;font-size:14px;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Client Information --}}
  <div class="mi-card">
    <div class="mi-card-head" style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border-bottom: 1px solid #bae6fd;">
      <svg class="decor-icon" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 60px; height: 60px; left: 20px; top: -10px; animation: float2 9s ease-in-out infinite;"><use href="#i-users"/></svg>
      <span style="display:flex;align-items:center;gap:8px; color: #0369a1;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-credit-card"/></svg>
        بيانات العميل والفاتورة
      </span>
    </div>
    <div class="mi-card-body">
      <div class="mi-grid">
        <div class="mi-field">
          <label>رقم الفاتورة</label>
          <input type="text" value="{{ $nextNumber }}" readonly class="inp" style="background:var(--bg);font-weight:700;color:var(--accent);">
        </div>
        <div class="mi-field">
          <label>تاريخ الفاتورة <span class="req">*</span></label>
          <input type="text" name="date" class="inp flatpickr-date" value="{{ old('date', $invoice?->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        </div>
        <div class="mi-field">
          <label>اسم العميل <span class="req">*</span></label>
          <div class="ac-wrap">
            <input type="text" name="client_name" id="client_name" class="inp" data-ac-type="client"
                   value="{{ old('client_name', $invoice?->client_name) }}" required autocomplete="off" placeholder="ابدأ بكتابة اسم العميل...">
            <div class="ac-list" id="ac-client_name"></div>
          </div>
        </div>
        <div class="mi-field">
          <label>التلفون</label>
          <input type="text" name="client_phone" id="client_phone" class="inp"
                 value="{{ old('client_phone', $invoice?->client_phone) }}" placeholder="رقم التلفون">
        </div>
        <div class="mi-field span-2" style="grid-column: span 2;">
          <label>العنوان</label>
          <input type="text" name="client_address" id="client_address" class="inp"
                 value="{{ old('client_address', $invoice?->client_address) }}" placeholder="عنوان العميل">
        </div>
      </div>
    </div>
  </div>

  {{-- Items Table --}}
  <div class="mi-card">
    <div class="mi-card-head" style="background: linear-gradient(135deg, #ecfeff, #cffafe); border-bottom: 1px solid #a5f3fc;">
      <svg class="decor-icon" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 60px; height: 60px; left: 200px; top: -15px; animation: float1 7s ease-in-out infinite;"><use href="#i-box"/></svg>
      <span style="display:flex;align-items:center;gap:8px; color: #0e7490;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
        الأصناف والبنود
      </span>
      <button type="button" class="btn sm" onclick="addRow()" style="background: linear-gradient(45deg, #0e7490, #0891b2); color: #fff; border: none; font-weight: 700; box-shadow: 0 2px 6px rgba(8,145,178,0.3);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-plus-circle"/></svg>
        إضافة صنف
      </button>
    </div>
    <div class="mi-items-wrap">
      <table class="mi-items-table" id="itemsTable">
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th style="width:120px;">التاريخ</th>
            <th style="min-width:240px;">البيان / الصنف</th>
            <th style="width:100px;">الكمية</th>
            <th style="width:130px;">الوحدة</th>
            <th style="width:140px;">سعر الوحدة</th>
            <th style="width:130px;">الإجمالي</th>
            <th style="width:50px;"></th>
          </tr>
        </thead>
        <tbody id="itemsBody"></tbody>
        <tfoot>
          <tr>
            <td colspan="6" style="text-align:left;">إجمالي الأصناف</td>
            <td id="subtotalDisplay" style="color:var(--accent);text-align:center;">0.00</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- Totals & Notes --}}
  <div class="mi-bottom-grid">
    <div class="mi-card">
      <div class="mi-card-head" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
        <svg class="decor-icon" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 50px; height: 50px; left: 10px; top: -5px; animation: float2 8s ease-in-out infinite;"><use href="#i-doc"/></svg>
        <span style="display:flex;align-items:center;gap:8px; color: #475569;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
          ملاحظات / الشروط والأحكام
        </span>
      </div>
      <div class="mi-card-body">
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
          <button type="button" class="btn ghost sm" onclick="document.getElementById('notesArea').value = 'شروط وأحكام التعامل:\n١- يرجى مراجعة الأصناف فور الاستلام، ولا يقبل الاسترجاع أو الاستبدال بعد ١٤ يوماً من تاريخ الفاتورة.\n٢- تعتبر هذه الفاتورة سنداً ملزماً بمجرد التوقيع عليها أو استلام البضاعة.\n٣- البضاعة تظل ملكاً للشركة حتى يتم سداد كامل القيمة الموضحة أعلاه.'">شروط التعامل والاسترجاع</button>
          <button type="button" class="btn ghost sm" onclick="document.getElementById('notesArea').value = 'طرق الدفع والتحويل البنكي:\nبرجاء سداد قيمة الفاتورة عن طريق التحويل على الحساب التالي:\n- اسم الحساب: شركة الضبع للتجارة والتوريدات\n- البنك: [اسم البنك هنا]\n- رقم الحساب: 000000000000\nبرجاء إرسال إشعار التحويل بعد إتمام العملية.'">بيانات التحويل البنكي</button>
          <button type="button" class="btn ghost sm" onclick="document.getElementById('notesArea').value = ''">مسح</button>
        </div>
        <textarea name="notes" id="notesArea" class="inp" rows="5" placeholder="اكتب الشروط والأحكام أو ملاحظات الفاتورة هنا..." style="resize:vertical;">{{ old('notes', $invoice?->notes) }}</textarea>
      </div>
    </div>

    <div class="mi-card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #cbd5e1;">
      <div class="mi-card-head" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-bottom: 1px solid #c7d2fe;">
        <svg class="decor-icon" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 70px; height: 70px; left: 15px; top: -10px; animation: float1 9s ease-in-out infinite alternate;"><use href="#i-coins"/></svg>
        <span style="display:flex;align-items:center;gap:8px; color: #4338ca;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-coins"/></svg>
          الحساب الإجمالي
        </span>
      </div>
      <div class="mi-card-body" style="display:flex;flex-direction:column;gap:4px;">
        <div class="mi-summary-row">
          <span style="color:var(--ink-3);font-weight:600;font-size:14px;">إجمالي الأصناف</span>
          <b id="summarySubtotal" style="font-size:15px;">0.00 ج.م</b>
        </div>

        <div class="mi-summary-input">
          <label style="color:#b91c1c;">الخصم</label>
          <input type="number" name="discount" id="discountInput" class="inp" value="{{ old('discount', $invoice?->discount ?? 0) }}" min="0" step="0.01" onchange="recalcTotals()" oninput="recalcTotals()">
        </div>

        <div class="mi-summary-row">
          <span style="color:var(--ink-3);font-weight:600;font-size:14px;">الإجمالي بعد الخصم</span>
          <b id="summaryAfterDiscount" style="font-size:15px;">0.00 ج.م</b>
        </div>

        <div class="mi-summary-input">
          <label style="color:var(--pos);">نسبة الضريبة %</label>
          <input type="number" name="tax_pct" id="taxPctInput" class="inp" value="{{ old('tax_pct', $invoice?->tax_pct ?? 0) }}" min="0" max="100" step="0.01" style="max-width:120px;" onchange="recalcTotals()" oninput="recalcTotals()">
        </div>

        <div class="mi-summary-row" id="taxRow">
          <span style="color:var(--ink-3);font-weight:600;font-size:14px;">مبلغ الضريبة</span>
          <b id="summaryTax" style="font-size:15px;color:var(--pos);">0.00 ج.م</b>
        </div>

        {{-- Tax Registration Fields - appear when tax > 0 --}}
        <div id="taxRegistrationFields" style="display:none; border:1px solid #a5f3fc; background:linear-gradient(135deg,#ecfeff,#f0fdfa); border-radius:10px; padding:14px 16px; margin:8px 0; transition:all 0.3s ease;">
          <div style="font-size:12px; font-weight:800; color:#0e7490; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-doc"/></svg>
            بيانات الفاتورة الضريبية
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div>
              <label style="font-size:11.5px; font-weight:700; color:#0e7490; margin-bottom:4px; display:block;">الرقم الضريبي</label>
              <input type="text" name="tax_number" id="taxNumberInput" class="inp" value="{{ old('tax_number', $invoice?->tax_number) }}" placeholder="أدخل الرقم الضريبي..." style="font-size:13px;">
            </div>
            <div>
              <label style="font-size:11.5px; font-weight:700; color:#0e7490; margin-bottom:4px; display:block;">السجل التجاري</label>
              <input type="text" name="commercial_register" id="commercialRegisterInput" class="inp" value="{{ old('commercial_register', $invoice?->commercial_register) }}" placeholder="أدخل رقم السجل التجاري..." style="font-size:13px;">
            </div>
          </div>
        </div>

        <div class="mi-highlight" style="background:var(--accent-soft);">
          <span style="font-weight:800;font-size:15px;color:var(--accent-ink);">الإجمالي النهائي</span>
          <b id="summaryGrandTotal" style="font-size:18px;color:var(--accent-ink);">0.00 ج.م</b>
        </div>

        <div class="mi-summary-input">
          <label>المدفوع</label>
          <input type="number" name="paid_amount" id="paidInput" class="inp" value="{{ old('paid_amount', $invoice?->paid_amount ?? 0) }}" min="0" step="0.01" onchange="recalcTotals()" oninput="recalcTotals()">
        </div>

        <div class="mi-highlight" style="background:#fef2f2;">
          <span style="font-weight:800;font-size:15px;color:#b91c1c;">المتبقي</span>
          <b id="summaryRemaining" style="font-size:18px;color:#b91c1c;">0.00 ج.م</b>
        </div>
      </div>
    </div>
  </div>

  {{-- Action buttons --}}
  <div class="mi-card" style="margin-bottom:40px;">
    <div style="padding:16px 24px;display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap;">
      <input type="hidden" name="status" id="statusInput" value="{{ old('status', $invoice?->status ?? 'draft') }}">
      <button type="submit" class="btn" onclick="document.getElementById('statusInput').value='draft'" style="background:var(--surface);color:var(--ink);border:1px solid var(--line);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><use href="#i-doc"/></svg>
        حفظ كمسودة
      </button>
      <button type="submit" class="btn pos" onclick="document.getElementById('statusInput').value='final'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><use href="#i-check-circle"/></svg>
        حفظ كفاتورة نهائية
      </button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // ─── Autocomplete System ──────────────────────────────────────────────
  const acUrl = @json(route('manual_invoices.autocomplete'));
  let acTimeout = null;

  function setupAutocomplete(input, listEl, type, onSelect) {
    // Append to body to avoid overflow clipping
    document.body.appendChild(listEl);
    
    function positionList() {
      if (!listEl.classList.contains('show')) return;
      const rect = input.getBoundingClientRect();
      listEl.style.top = (rect.bottom + 4) + 'px';
      listEl.style.left = rect.left + 'px';
      listEl.style.width = rect.width + 'px';
    }

    window.addEventListener('scroll', positionList, true);
    window.addEventListener('resize', positionList);

    input.addEventListener('input', function() {
      clearTimeout(acTimeout);
      const q = this.value.trim();
      if (q.length < 1) { listEl.classList.remove('show'); return; }

      acTimeout = setTimeout(() => {
        fetch(`${acUrl}?type=${type}&q=${encodeURIComponent(q)}`)
          .then(r => r.json())
          .then(items => {
            listEl.innerHTML = '';
            if (items.length === 0) {
              listEl.innerHTML = '<div class="ac-empty">لا توجد نتائج</div>';
            } else {
              items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'ac-item';
                div.textContent = item;
                div.addEventListener('mousedown', (e) => {
                  e.preventDefault(); // prevent blur from firing before click
                  input.value = item;
                  listEl.classList.remove('show');
                  if (onSelect) onSelect(item);
                });
                listEl.appendChild(div);
              });
            }
            listEl.classList.add('show');
            positionList();
          });
      }, 200);
    });

    input.addEventListener('blur', () => setTimeout(() => listEl.classList.remove('show'), 150));
    input.addEventListener('focus', function() {
      const q = this.value.trim();
      if (q.length >= 1 && listEl.children.length > 0) {
        listEl.classList.add('show');
        positionList();
      }
    });
  }

  // Client name autocomplete — also fills phone & address
  const clientInput = document.getElementById('client_name');
  const clientList = document.getElementById('ac-client_name');
  if (clientInput && clientList) {
    setupAutocomplete(clientInput, clientList, 'client', function(name) {
      fetch(`${acUrl}?type=client_phone&q=${encodeURIComponent(name)}`)
        .then(r => r.json())
        .then(phones => { if (phones.length) document.getElementById('client_phone').value = phones[0]; });
      fetch(`${acUrl}?type=client_address&q=${encodeURIComponent(name)}`)
        .then(r => r.json())
        .then(addrs => { if (addrs.length) document.getElementById('client_address').value = addrs[0]; });
    });
  }

  // ─── Dynamic Rows ─────────────────────────────────────────────────────
  window.rowIndex = 0;

  window.addRow = function(data) {
    const tbody = document.getElementById('itemsBody');
    const idx = window.rowIndex++;
    
    // Defaults: Today's date and "وحدة" for the unit
    const today = new Date();
    const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
    const rowDate = (data && data.date) ? data.date : todayStr;
    const rowUnit = (data && data.unit) ? data.unit : 'وحدة';

    const tr = document.createElement('tr');
    tr.id = `row-${idx}`;
    tr.className = 'row-anim';
    tr.innerHTML = `
      <td class="row-num" style="color:#64748b; font-weight:800;">${idx + 1}</td>
      <td>
        <input type="text" name="items[${idx}][date]" class="inp flatpickr-date row-date" value="${rowDate}" placeholder="اختياري">
      </td>
      <td>
        <div class="ac-wrap">
          <input type="text" name="items[${idx}][description]" class="inp" data-ac-type="item" data-ac-idx="${idx}" value="${data?.description || ''}" required autocomplete="off" placeholder="اسم الصنف أو البيان...">
          <div class="ac-list" id="ac-item-${idx}"></div>
        </div>
      </td>
      <td>
        <input type="number" name="items[${idx}][qty]" class="inp row-qty" value="${data?.qty || 1}" min="0.01" step="0.01" required oninput="calcRowTotal(${idx})">
      </td>
      <td>
        <div class="ac-wrap">
          <input type="text" name="items[${idx}][unit]" class="inp" data-ac-type="unit" data-ac-idx="${idx}" value="${rowUnit}" autocomplete="off" placeholder="الوحدة">
          <div class="ac-list" id="ac-unit-${idx}"></div>
        </div>
      </td>
      <td>
        <input type="number" name="items[${idx}][unit_price]" class="inp row-price" value="${data?.unit_price || 0}" min="0" step="0.01" required oninput="calcRowTotal(${idx})">
      </td>
      <td class="row-total" style="font-weight:800; color:#334155; background:#f8fafc; border-radius:6px; padding-inline:12px; border: 1px solid #e2e8f0;">${formatMoney((data?.qty || 1) * (data?.unit_price || 0))}</td>
      <td>
        <button type="button" class="del-row-btn" onclick="removeRow(${idx})" title="حذف" style="color:#64748b; background:#f1f5f9; border:none; padding:6px; border-radius:6px; cursor:pointer; transition: 0.2s;" onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2';" onmouseout="this.style.color='#64748b';this.style.background='#f1f5f9';">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><use href="#i-trash"/></svg>
        </button>
      </td>
    `;
    tbody.appendChild(tr);

    // Init flatpickr on the date input
    if (window.flatpickr) {
      flatpickr(tr.querySelector('.row-date'), {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        allowInput: true,
      });
    }

    // Setup autocomplete for item description
    const itemInput = tr.querySelector(`[data-ac-type="item"]`);
    const itemList = tr.querySelector(`#ac-item-${idx}`);
    setupAutocomplete(itemInput, itemList, 'item');

    // Setup autocomplete for unit
    const unitInput = tr.querySelector(`[data-ac-type="unit"]`);
    const unitList = tr.querySelector(`#ac-unit-${idx}`);
    setupAutocomplete(unitInput, unitList, 'unit');

    // Add enter key support to jump to next row
    tr.querySelectorAll('.inp').forEach(input => {
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          // If autocomplete is open, let it handle the enter key selection instead of adding a row
          if (this.nextElementSibling && this.nextElementSibling.classList.contains('show')) {
            return;
          }
          const newRow = addRow();
          setTimeout(() => {
            newRow.querySelector('[data-ac-type="item"]')?.focus();
          }, 50);
        }
      });
    });

    recalcTotals();
    return tr;
  };

  window.removeRow = function(idx) {
    const row = document.getElementById(`row-${idx}`);
    if (row) {
      row.remove();
      renumberRows();
      recalcTotals();
    }
  };

  window.calcRowTotal = function(idx) {
    const row = document.getElementById(`row-${idx}`);
    if (!row) return;
    const qty = parseFloat(row.querySelector('.row-qty').value) || 0;
    const price = parseFloat(row.querySelector('.row-price').value) || 0;
    row.querySelector('.row-total').textContent = formatMoney(qty * price);
    recalcTotals();
  };

  window.recalcTotals = function() {
    let subtotal = 0;
    document.querySelectorAll('#itemsBody tr').forEach(row => {
      const qty = parseFloat(row.querySelector('.row-qty')?.value) || 0;
      const price = parseFloat(row.querySelector('.row-price')?.value) || 0;
      subtotal += qty * price;
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const afterDiscount = subtotal - discount;
    const taxPct = parseFloat(document.getElementById('taxPctInput').value) || 0;
    const taxAmount = Math.round(afterDiscount * (taxPct / 100) * 100) / 100;
    const grandTotal = afterDiscount + taxAmount;
    const paid = parseFloat(document.getElementById('paidInput').value) || 0;
    const remaining = grandTotal - paid;

    document.getElementById('subtotalDisplay').textContent = formatMoney(subtotal);
    document.getElementById('summarySubtotal').textContent = formatMoney(subtotal) + ' ج.م';
    document.getElementById('summaryAfterDiscount').textContent = formatMoney(afterDiscount) + ' ج.م';
    document.getElementById('summaryTax').textContent = formatMoney(taxAmount) + ' ج.م';
    document.getElementById('summaryGrandTotal').textContent = formatMoney(grandTotal) + ' ج.م';
    document.getElementById('summaryRemaining').textContent = formatMoney(remaining) + ' ج.م';

    document.getElementById('taxRow').style.display = taxPct > 0 ? 'flex' : 'none';

    // Show/hide tax registration fields
    var taxRegFields = document.getElementById('taxRegistrationFields');
    if (taxRegFields) {
      taxRegFields.style.display = taxPct > 0 ? 'block' : 'none';
    }
  };

  function renumberRows() {
    let i = 1;
    document.querySelectorAll('#itemsBody tr .row-num').forEach(td => { td.textContent = i++; });
  }

  function formatMoney(n) {
    return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // ─── Load existing items (edit mode) or add first empty row ────────
  @if($invoice && $invoice->items->count())
    @foreach($invoice->items as $item)
      addRow({
        date: @json($item->date?->format('Y-m-d') ?? ''),
        description: @json($item->description),
        qty: {{ $item->qty }},
        unit: @json($item->unit ?? ''),
        unit_price: {{ $item->unit_price }},
      });
    @endforeach
  @else
    addRow();
  @endif

  recalcTotals();
});
</script>
@endsection
