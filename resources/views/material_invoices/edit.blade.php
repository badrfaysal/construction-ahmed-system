@extends('layouts.app')
@section('title', 'تعديل فاتورة مشتريات')
@section('page-title', 'تعديل فاتورة مشتريات')

@section('content')
<div class="page-head">
  <div>
    <h3>تعديل فاتورة مشتريات: {{ $invoice->name ?: '#' . $invoice->id }}</h3>
    <p>مشروع: {{ $invoice->project->name }} | المورد: {{ $invoice->supplier?->name ?? 'بدون مورد' }}</p>
  </div>
  <div class="btn-row">
    <a href="{{ route('material_invoices.show', $invoice->id) }}" class="btn ghost">رجوع للتفاصيل</a>
    <a href="{{ route('material_invoices.index') }}" class="btn ghost">كل الفواتير</a>
  </div>
</div>

@include('partials._errors')

@php
  $currentStatus = old('payment_status', $invoice->remainingBalance() <= 0 ? 'paid' : ($invoice->paid_amount == 0 ? 'deferred' : 'partial'));
@endphp

<form method="POST" action="{{ route('material_invoices.update', $invoice->id) }}" id="invoice-edit-form">
  @csrf
  @method('PUT')

  {{-- 1. البيانات الأساسية للفاتورة --}}
  <div class="form-card" style="max-width:none; margin-bottom:20px;">
    <h4 style="margin-top:0; margin-bottom:16px; border-bottom:1px solid var(--line); padding-bottom:10px; color:var(--ink-1);">
      البيانات الأساسية للفاتورة
    </h4>

    <div class="row3" style="gap:20px; margin-bottom:16px;">
      <div class="field" style="margin-bottom:0">
        <label>المشروع</label>
        <input type="text" class="input" value="{{ $invoice->project->name }}" readonly style="background:#f1f5f9; cursor:not-allowed; width:100%;">
      </div>

      <div class="field" style="margin-bottom:0">
        <label>اسم / رقم الفاتورة *</label>
        <input type="text" name="name" required class="input" value="{{ old('name', $invoice->name) }}" style="width:100%;">
      </div>

      <div class="field" style="margin-bottom:0">
        <label>تاريخ الفاتورة *</label>
        <input type="date" name="date" required class="input" value="{{ old('date', $invoice->date?->format('Y-m-d')) }}" style="width:100%;">
      </div>
    </div>

    <div class="row2" style="gap:20px; margin-bottom:0;">
      <div class="field" style="margin-bottom:0">
        <label>المورد *</label>
        <select name="supplier_id" required class="input" style="width:100%;">
          <option value="">اختر المورد...</option>
          @foreach($suppliers as $s)
            <option value="{{ $s->id }}" {{ old('supplier_id', $invoice->supplier_id) == $s->id ? 'selected' : '' }}>
              {{ $s->name }} {{ $s->activity ? "({$s->activity})" : '' }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="field" style="margin-bottom:0">
        <label>ملاحظات</label>
        <input type="text" name="notes" class="input" value="{{ old('notes', $invoice->notes) }}" placeholder="أي ملاحظات تخص الفاتورة..." style="width:100%;">
      </div>
    </div>
  </div>

  {{-- 2. أصناف وخامات الفاتورة --}}
  <div class="form-card" style="max-width:none; margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--line); padding-bottom:10px;">
      <h4 style="margin:0; color:var(--ink-1);">أصناف وخامات الفاتورة</h4>
      <button type="button" class="btn sm primary" onclick="addItemRow()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        إضافة صنف جديد
      </button>
    </div>

    <table class="table" id="items-table" style="width:100%; table-layout:fixed; margin:0;">
      <thead>
        <tr style="background:#f8fafc;">
          <th style="width:24%">اسم الصنف *</th>
          <th style="width:20%">البند المرتبط بالمشروع</th>
          <th style="width:10%">الوحدة *</th>
          <th style="width:10%" class="num">الكمية *</th>
          <th style="width:11%" class="num">سعر التكلفة *</th>
          <th style="width:11%" class="num">سعر البيع *</th>
          <th style="width:10%" class="num">إجمالي التكلفة</th>
          <th style="width:4%; text-align:center;"></th>
        </tr>
      </thead>
      <tbody id="items-tbody">
        @foreach($invoice->materials as $idx => $mat)
          <tr class="item-row" data-index="{{ $idx }}">
            <td>
              <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $mat->id }}">
              <input type="text" name="items[{{ $idx }}][item]" value="{{ old("items.$idx.item", $mat->item) }}" required class="input sm" placeholder="اسم الصنف..." list="known-items">
              @if($mat->returnedQty() > 0)
                <div style="margin-top:2px"><span class="tag sm amber" style="font-size:10px">مرتجع: {{ number_format($mat->returnedQty(), 1) }}</span></div>
              @endif
            </td>
            <td>
              <select name="items[{{ $idx }}][band_id]" class="input sm">
                <option value="">بدون بند (عام)</option>
                @foreach($invoice->project->bands as $b)
                  <option value="{{ $b->id }}" {{ old("items.$idx.band_id", $mat->band_id) == $b->id ? 'selected' : '' }}>
                    {{ $b->name }}
                  </option>
                @endforeach
              </select>
            </td>
            <td>
              <input type="text" name="items[{{ $idx }}][unit]" value="{{ old("items.$idx.unit", $mat->unit) }}" required class="input sm" placeholder="متر، طن..." list="known-units">
            </td>
            <td>
              <input type="number" name="items[{{ $idx }}][qty]" value="{{ old("items.$idx.qty", (float)$mat->qty) }}" min="{{ $mat->returnedQty() ?: 0.01 }}" step="0.01" required class="input sm num row-qty" oninput="updateLiveTotals()">
            </td>
            <td>
              <input type="number" name="items[{{ $idx }}][unit_price]" value="{{ old("items.$idx.unit_price", (float)$mat->unit_price) }}" min="0" step="0.01" required class="input sm num row-price" oninput="updateLiveTotals()">
            </td>
            <td>
              <input type="number" name="items[{{ $idx }}][sell_price]" value="{{ old("items.$idx.sell_price", (float)$mat->sell_price) }}" min="0" step="0.01" required class="input sm num">
            </td>
            <td class="num row-total" style="font-weight:bold; color:var(--ink-1); vertical-align:middle;">
              {{ \App\Support\Money::format($mat->qty * $mat->unit_price) }}
            </td>
            <td style="text-align:center; vertical-align:middle;">
              @if($mat->returnedQty() > 0)
                <span class="muted" title="لا يمكن حذف الصنف لوجود مرتجع مسجل عليه" style="font-size:11px;">محمي</span>
              @else
                <button type="button" class="btn ghost danger sm" title="حذف السطر" onclick="removeItemRow(this)" style="padding:4px 6px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
                </button>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- 3. حالة السداد والدفع + إجماليات الفاتورة بالأسفل --}}
  <div class="form-card" style="max-width:none; margin-bottom:20px;">
    <h4 style="margin-top:0; margin-bottom:16px; border-bottom:1px solid var(--line); padding-bottom:10px; color:var(--ink-1);">
      حالة السداد والدفع *
    </h4>

    <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:16px;">
      <label class="radio-pill">
        <input type="radio" name="payment_status" value="paid" {{ $currentStatus === 'paid' ? 'checked' : '' }} onchange="togglePaymentFields(this.value)">
        <span>تم سدادها بالكامل</span>
      </label>
      <label class="radio-pill">
        <input type="radio" name="payment_status" value="partial" {{ $currentStatus === 'partial' ? 'checked' : '' }} onchange="togglePaymentFields(this.value)">
        <span>سداد جزء + متبقي آجل</span>
      </label>
      <label class="radio-pill">
        <input type="radio" name="payment_status" value="deferred" {{ $currentStatus === 'deferred' ? 'checked' : '' }} onchange="togglePaymentFields(this.value)">
        <span>آجل بالكامل (دين)</span>
      </label>
    </div>

    <div class="row2" style="gap:20px; margin-bottom:20px;">
      <div class="field" id="account-field" style="margin-bottom:0; display:{{ $currentStatus === 'deferred' ? 'none' : 'block' }}">
        <label>الخزنة / المحفظة المدفوع منها *</label>
        <select name="account_id" id="account_id" class="input" style="width:100%;">
          <option value="">اختر الخزنة...</option>
          @foreach($accounts as $acc)
            <option value="{{ $acc->id }}" {{ old('account_id', $invoice->account_id) == $acc->id ? 'selected' : '' }}>
              {{ $acc->name }} (الرصيد: {{ \App\Support\Money::format($acc->balance) }} ج.م)
            </option>
          @endforeach
        </select>
      </div>

      <div class="field" id="paid-amount-field" style="margin-bottom:0; display:{{ $currentStatus === 'partial' ? 'block' : 'none' }}">
        <label>المبلغ المدفوع نقداً (ج.م) *</label>
        <input type="number" name="paid_amount" id="paid_amount" min="0" step="0.01" class="input" value="{{ old('paid_amount', (float)$invoice->paid_amount) }}" oninput="updateLiveTotals()" style="width:100%;">
      </div>
    </div>

    <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px;">
      <div style="display:flex; gap:32px; flex-wrap:wrap;">
        <div>
          <span style="display:block; font-size:12px; color:var(--mut);">إجمالي الفاتورة:</span>
          <strong id="live-total" style="font-size:20px; color:var(--ink-1);">{{ \App\Support\Money::format($invoice->total_amount) }}</strong> ج.م
        </div>
        <div>
          <span style="display:block; font-size:12px; color:var(--mut);">المدفوع:</span>
          <strong id="live-paid" style="font-size:20px; color:var(--pos);">{{ \App\Support\Money::format($invoice->paid_amount) }}</strong> ج.م
        </div>
        <div>
          <span style="display:block; font-size:12px; color:var(--mut);">المتبقي (المديونية):</span>
          <strong id="live-remaining" style="font-size:20px; color:var(--warn);">{{ \App\Support\Money::format($invoice->remainingBalance()) }}</strong> ج.م
        </div>
      </div>

      <div style="display:flex; gap:12px;">
        <a href="{{ route('material_invoices.show', $invoice->id) }}" class="btn ghost">إلغاء</a>
        <button type="submit" class="btn primary" style="font-size:16px; padding:10px 24px;">
          حفظ تعديلات الفاتورة
        </button>
      </div>
    </div>
  </div>
</form>

<datalist id="known-items">
  @foreach($knownItems as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>

<datalist id="known-units">
  @foreach($knownUnits as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>

<style>
#items-table td {
  padding: 8px 6px;
}
#items-table input,
#items-table select {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 6px 8px;
}
.radio-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 8px;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  cursor: pointer;
  font-weight: 600;
  color: var(--ink-2);
}
.radio-pill:has(input:checked) {
  background: #e0e7ff;
  border-color: #4f46e5;
  color: #3730a3;
}
</style>

<script>
let rowIndex = {{ $invoice->materials->count() }};
const bandsOptions = `@foreach($invoice->project->bands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach`;

function togglePaymentFields(status) {
  const accField = document.getElementById('account-field');
  const paidField = document.getElementById('paid-amount-field');
  const accSelect = document.getElementById('account_id');

  if (status === 'deferred') {
    accField.style.display = 'none';
    paidField.style.display = 'none';
    accSelect.required = false;
  } else if (status === 'partial') {
    accField.style.display = 'block';
    paidField.style.display = 'block';
    accSelect.required = true;
  } else {
    accField.style.display = 'block';
    paidField.style.display = 'none';
    accSelect.required = true;
  }
  updateLiveTotals();
}

function addItemRow() {
  const tbody = document.getElementById('items-tbody');
  const tr = document.createElement('tr');
  tr.className = 'item-row';
  tr.dataset.index = rowIndex;

  tr.innerHTML = `
    <td>
      <input type="text" name="items[${rowIndex}][item]" required class="input sm" placeholder="اسم الصنف..." list="known-items">
    </td>
    <td>
      <select name="items[${rowIndex}][band_id]" class="input sm">
        <option value="">بدون بند (عام)</option>
        ${bandsOptions}
      </select>
    </td>
    <td>
      <input type="text" name="items[${rowIndex}][unit]" required class="input sm" placeholder="متر، طن..." list="known-units">
    </td>
    <td>
      <input type="number" name="items[${rowIndex}][qty]" min="0.01" step="0.01" value="1" required class="input sm num row-qty" oninput="updateLiveTotals()">
    </td>
    <td>
      <input type="number" name="items[${rowIndex}][unit_price]" min="0" step="0.01" value="0" required class="input sm num row-price" oninput="updateLiveTotals()">
    </td>
    <td>
      <input type="number" name="items[${rowIndex}][sell_price]" min="0" step="0.01" value="0" required class="input sm num">
    </td>
    <td class="num row-total" style="font-weight:bold; color:var(--ink-1); vertical-align:middle;">0.00</td>
    <td style="text-align:center; vertical-align:middle;">
      <button type="button" class="btn ghost danger sm" title="حذف السطر" onclick="removeItemRow(this)" style="padding:4px 6px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
      </button>
    </td>
  `;

  tbody.appendChild(tr);
  rowIndex++;
  updateLiveTotals();
}

function removeItemRow(btn) {
  const rows = document.querySelectorAll('.item-row');
  if (rows.length <= 1) {
    alert('يجب أن تحتوي الفاتورة على صنف واحد على الأقل.');
    return;
  }
  btn.closest('tr').remove();
  updateLiveTotals();
}

function updateLiveTotals() {
  let grandTotal = 0;
  document.querySelectorAll('.item-row').forEach(row => {
    const qty = parseFloat(row.querySelector('.row-qty')?.value) || 0;
    const price = parseFloat(row.querySelector('.row-price')?.value) || 0;
    const total = qty * price;
    grandTotal += total;
    const totalCell = row.querySelector('.row-total');
    if (totalCell) {
      totalCell.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  });

  const paymentStatus = document.querySelector('input[name="payment_status"]:checked')?.value || 'paid';
  let paid = 0;
  if (paymentStatus === 'paid') {
    paid = grandTotal;
  } else if (paymentStatus === 'deferred') {
    paid = 0;
  } else {
    paid = parseFloat(document.getElementById('paid_amount')?.value) || 0;
  }

  const remaining = Math.max(0, grandTotal - paid);

  document.getElementById('live-total').textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  document.getElementById('live-paid').textContent = paid.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  document.getElementById('live-remaining').textContent = remaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.addEventListener('DOMContentLoaded', () => {
  updateLiveTotals();
});
</script>
@endsection
