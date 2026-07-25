@extends('layouts.app')
@section('title', 'تعديل عرض سعر')
@section('page-title', 'تعديل عرض سعر: ' . $quote->ref)

@section('content')
<div class="page-head"><div><h3>تعديل عرض سعر</h3></div><a href="{{ route('quotes.show', $quote) }}" class="btn ghost">رجوع</a></div>

<style>
  /* Professional UI Styles */
  .c-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  }
  .c-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
  }
  .c-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
  }
  .c-input {
    width: 100%;
    height: 42px;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    color: #0f172a;
    background: #f8fafc;
    transition: all 0.2s ease-in-out;
  }
  .c-input:focus {
    background: #fff;
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
  }
  textarea.c-input {
    height: auto;
    padding: 12px;
    line-height: 1.5;
  }
  
  .c-band {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    overflow: hidden;
  }
  .c-band-header {
    background: #f8fafc;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
  }
  .c-band-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .c-band-body {
    padding: 24px;
  }
  
  .c-table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    margin-bottom: 24px;
    overflow: hidden;
  }
  .c-table-header {
    background: #f1f5f9;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  /* Flex Rows for items and workers */
  .c-row {
    display: flex;
    gap: 16px;
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    align-items: flex-end;
  }
  .c-row:last-child {
    border-bottom: none;
  }
  .c-col {
    flex: 1;
    min-width: 0;
  }
  .c-col.sm { flex: 0.5; }
  .c-col.lg { flex: 2; }
  
  .c-del-btn {
    width: 42px;
    height: 42px;
    background: #fee2e2;
    color: #ef4444;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s;
  }
  .c-del-btn:hover {
    background: #ef4444;
    color: #fff;
  }
  
  /* Sticky Footer for Totals & Save */
  .c-footer {
    position: sticky;
    bottom: 0;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(12px);
    border: 1px solid #e2e8f0;
    border-radius: 12px 12px 0 0;
    padding: 20px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 100;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
    margin: 40px 0 0 0;
  }
  .c-totals-flex {
    display: flex;
    gap: 40px;
    align-items: center;
  }
  .c-tot-box {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .c-tot-lbl {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
  }
  .c-tot-val {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
  }
  .c-tot-val.final {
    font-size: 24px;
    color: #10b981;
  }
</style>

<form method="POST" action="{{ route('quotes.update', $quote) }}">
  @csrf
  @method('PUT')
  
  <div class="c-card">
    <div class="c-grid" style="margin-bottom: 12px;">
      <div>
        <label class="c-label">العميل *</label>
        <select name="client_id" required class="c-input">
          <option value="">— اختر العميل —</option>
          @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ old('client_id', $quote->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}@if($c->phone) — {{ $c->phone }}@endif</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="c-label">رقم المرجع *</label>
        <input type="text" name="ref" value="{{ old('ref', $quote->ref) }}" required class="c-input">
      </div>
      <div>
        <label class="c-label">التاريخ *</label>
        <input type="date" name="date" value="{{ old('date', $quote->date->format('Y-m-d')) }}" required class="c-input">
      </div>
      <div>
        <label class="c-label">الحالة</label>
        <select name="status" class="c-input">
          <option value="draft" {{ old('status', $quote->status) === 'draft' ? 'selected' : '' }}>قيد المراجعة</option>
          <option value="sent" {{ old('status', $quote->status) === 'sent' ? 'selected' : '' }}>تم الإرسال</option>
          <option value="approved" {{ old('status', $quote->status) === 'approved' ? 'selected' : '' }}>معتمد</option>
        </select>
      </div>
      <div>
        <label class="c-label">المساحة (م²)</label>
        <input type="number" name="area" value="{{ old('area', $quote->area) }}" min="0" step="0.5" class="c-input" placeholder="0">
      </div>
    </div>
    <div class="c-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 12px;">
      <div>
        <label class="c-label">العنوان</label>
        <input type="text" name="address" value="{{ old('address', $quote->address) }}" class="c-input" placeholder="عنوان المشروع...">
      </div>
      <div>
        <label class="c-label">ملاحظات</label>
        <input type="text" name="note" value="{{ old('note', $quote->note) }}" class="c-input" placeholder="ملاحظات إضافية...">
      </div>
    </div>

    <div class="c-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 12px;">
      <div>
        <label class="c-label">الضريبة %</label>
        <input type="number" name="tax_pct" id="tax_pct" value="{{ old('tax_pct', $quote->tax_pct) }}" min="0" max="100" step="0.1" class="c-input" placeholder="0" oninput="updateGlobalTotals()" style="font-weight:700; color:#059669;">
      </div>
      <div>
        <label class="c-label">الخصم (ج.م) <span style="font-weight:400; color:#94a3b8; font-size:11px;">(شكلي فقط — لا يؤثر على الإجمالي)</span></label>
        <input type="number" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $quote->discount_amount) }}" min="0" step="0.01" class="c-input" placeholder="0" oninput="updateGlobalTotals()" style="font-weight:700; color:#ef4444;">
      </div>
    </div>
    
    <div>
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
        <label class="c-label" style="margin:0">الشروط والأحكام</label>
        <div style="display: flex; gap: 4px;">
          <button type="button" class="btn ghost sm" style="padding: 2px 8px; font-size: 11px; height: auto;" onclick="document.getElementById('terms_input').value = '- هذا العرض ساري المفعول لمدة 15 يوماً من تاريخ إصداره.\n- الأسعار لا تشمل ضريبة القيمة المضافة ما لم يُذكر خلاف ذلك.\n- التنفيذ يبدأ خلال 3 إلى 5 أيام عمل من تاريخ تأكيد الطلب واستلام الدفعة.\n- شروط الدفع: دفعة مقدمة 50%، و50% عند الاستلام.'">شروط عامة</button>
          <button type="button" class="btn ghost sm" style="padding: 2px 8px; font-size: 11px; height: auto;" onclick="document.getElementById('terms_input').value = '- هذا العرض ساري المفعول لمدة أسبوع من تاريخ إصداره.\n- الأسعار نهائية وشاملة التركيب.\n- شروط الدفع: 100% دفعة مقدمة قبل البدء في التنفيذ.'">شروط مسبقة الدفع</button>
        </div>
      </div>
      <textarea name="terms" id="terms_input" class="c-input" style="height:60px" placeholder="أدخل الشروط والأحكام الخاصة بهذا العرض...">{{ old('terms', $quote->terms) }}</textarea>
    </div>
  </div>

  <div id="bands-list"></div>
  
  <button type="button" class="btn ghost" style="margin-bottom:40px; font-size: 14px; font-weight: 700; width: 100%; border: 2px dashed #cbd5e1" onclick="addBand()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><use href="#i-plus"/></svg>
    إضافة بند جديد (محارة، سيراميك، إلخ)
  </button>

  <div class="c-footer">
    <div class="c-totals-flex">
      <div class="c-tot-box">
        <span class="c-tot-lbl">عدد البنود</span>
        <span class="c-tot-val tnum" id="tot-bands-count">0</span>
      </div>
      <div class="c-tot-box">
        <span class="c-tot-lbl">الخامات</span>
        <span class="c-tot-val tnum" id="tot-items-count">0</span>
      </div>
      <div class="c-tot-box">
        <span class="c-tot-lbl">المصنعيات</span>
        <span class="c-tot-val tnum" id="tot-workers-count">0</span>
      </div>
      <div class="c-tot-box" id="tot-tax-box" style="display:none;">
        <span class="c-tot-lbl" style="color:#059669;">+ ضريبة</span>
        <span class="c-tot-val tnum" id="tot-tax-val" style="color:#059669; font-size:14px;">0.00</span>
      </div>
      <div class="c-tot-box" id="tot-discount-box" style="display:none;">
        <span class="c-tot-lbl" style="color:#ef4444;">خصم (شكلي)</span>
        <span class="c-tot-val tnum" id="tot-discount-val" style="color:#ef4444; font-size:14px;">0.00</span>
      </div>
      <div class="c-tot-box" style="border-right: 2px solid #e2e8f0; padding-right: 16px;">
        <span class="c-tot-lbl">الإجمالي النهائي للعميل</span>
        <div class="c-tot-val final tnum">
          <span id="tot-quote-price">0.00</span> <small style="font-size:12px; color:#64748b">ج.م</small>
        </div>
      </div>
    </div>
    
    <div style="display:flex; gap:12px;">
      <a href="{{ route('quotes.show', $quote) }}" class="btn ghost">إلغاء</a>
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><use href="#i-check"/></svg>حفظ التعديلات</button>
    </div>
  </div>
</form>

<datalist id="band-names-list">
  @foreach($bandNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>

@php
  $existingBandsData = $quote->bands->map(function ($b) {
      return [
          'name'  => $b->name,
          'price' => $b->price,
          'items' => $b->items->map(function ($i) {
              return [
                  'name'            => $i->name,
                  'qty'             => $i->qty,
                  'unit_price'      => $i->unit_price,
                  'supervision_pct' => $i->supervision_pct,
              ];
          }),
          'workers' => $b->workers->map(function ($w) {
              return [
                  'name'               => $w->name,
                  'specialty'          => $w->specialty,
                  'contract_type'      => $w->contract_type,
                  'contract_qty'       => $w->contract_qty,
                  'contract_unit_rate' => $w->contract_unit_rate,
                  'sell_rate'          => $w->sell_rate,
                  'amount'             => $w->amount,
                  'sell_amount'        => $w->sell_amount,
                  'supervision_pct'    => $w->supervision_pct,
              ];
          }),
      ];
  });
@endphp

@push('scripts')
<script>
let bandIdx = 0;

function updateGlobalTotals() {
  let bandsCount = 0;
  let itemsCount = 0;
  let workersCount = 0;
  let totalPrice = 0;

  document.querySelectorAll('.band-card').forEach(card => {
    bandsCount++;
    itemsCount += card.querySelectorAll('.item-row').length;
    workersCount += card.querySelectorAll('.worker-row').length;
    totalPrice += parseFloat(card.querySelector('.band-price').value) || 0;
  });

  const taxPct = parseFloat(document.getElementById('tax_pct')?.value) || 0;
  const discountAmount = parseFloat(document.getElementById('discount_amount')?.value) || 0;
  const taxAmount = totalPrice * taxPct / 100;
  const finalTotal = totalPrice + taxAmount;

  document.getElementById('tot-bands-count').innerText = bandsCount;
  document.getElementById('tot-items-count').innerText = itemsCount;
  document.getElementById('tot-workers-count').innerText = workersCount;
  document.getElementById('tot-quote-price').innerText = finalTotal.toFixed(2);

  // Show/hide tax box
  const taxBox = document.getElementById('tot-tax-box');
  if (taxPct > 0) {
    taxBox.style.display = '';
    document.getElementById('tot-tax-val').innerText = taxAmount.toFixed(2);
  } else {
    taxBox.style.display = 'none';
  }

  // Show/hide discount box (display only)
  const discountBox = document.getElementById('tot-discount-box');
  if (discountAmount > 0) {
    discountBox.style.display = '';
    document.getElementById('tot-discount-val').innerText = discountAmount.toFixed(2);
  } else {
    discountBox.style.display = 'none';
  }
}

function bandRowHtml(g) {
  return `
    <div class="c-band band-card" data-band="${g}">
      <div class="c-band-header">
        <div class="c-band-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-layers"/></svg>
          بند #${g + 1}
        </div>
        <div style="display: flex; gap: 12px; align-items: center">
          <div style="display:flex; align-items:center; gap:6px;">
            <label class="c-label" style="margin:0">إجمالي البند (ج.م):</label>
            <input type="number" name="bands[${g}][price]" class="c-input band-price" placeholder="0.00" min="0" step="0.01" style="width: 140px; font-weight:800; background:#fff" oninput="updateGlobalTotals()">
          </div>
          <button type="button" class="btn ghost sm danger" style="height: 36px" onclick="this.closest('.band-card').remove(); updateGlobalTotals()">
            حذف البند
          </button>
        </div>
      </div>

      <div class="c-band-body">
        <div style="margin-bottom: 16px;">
          <label class="c-label">اسم البند *</label>
          <input type="text" name="bands[${g}][name]" placeholder="محارة / سيراميك / دهانات..." required list="band-names-list" class="c-input" style="max-width: 400px; font-weight: bold;">
        </div>

        <!-- الخامات -->
        <div class="c-table-wrap">
          <div class="c-table-header">
            <span>الخامات</span>
            <button type="button" class="btn sm" style="height: 26px; padding: 0 10px;" onclick="addItem(${g})">+ إضافة خامة</button>
          </div>
          <div id="band-items-${g}"></div>
        </div>

        <!-- المصنعيات -->
        <div class="c-table-wrap">
          <div class="c-table-header">
            <span>المصنعيات (الفنيين)</span>
            <button type="button" class="btn sm" style="height: 26px; padding: 0 10px; background: #10b981" onclick="addWorker(${g})">+ إضافة فني</button>
          </div>
          <div id="band-workers-${g}"></div>
        </div>
      </div>
    </div>`;
}

function itemRowHtml(g, i) {
  return `
    <div class="c-row item-row" data-item="${i}">
      <div class="c-col lg">
        <label class="c-label">اسم الخامة *</label>
        <input type="text" name="bands[${g}][items][${i}][name]" class="c-input" placeholder="أسمنت، رمل..." required oninput="recalcBandPrice(${g})">
      </div>
      <div class="c-col sm">
        <label class="c-label">الكمية</label>
        <input type="number" name="bands[${g}][items][${i}][qty]" class="c-input" placeholder="0" min="0" step="0.01" value="1" required oninput="recalcBandPrice(${g})">
      </div>
      <div class="c-col">
        <label class="c-label">سعر البيع للعميل</label>
        <input type="number" name="bands[${g}][items][${i}][unit_price]" class="c-input" placeholder="0.00" min="0" step="0.01" required oninput="recalcBandPrice(${g})">
      </div>
      <div class="c-col sm">
        <label class="c-label">إشراف %</label>
        <input type="number" name="bands[${g}][items][${i}][supervision_pct]" class="c-input" placeholder="0" min="0" max="100" step="0.1" value="{{ $settings->default_supervision_pct }}" oninput="recalcBandPrice(${g})">
      </div>
      <button type="button" class="c-del-btn" onclick="this.closest('.item-row').remove(); recalcBandPrice(${g})" title="حذف الخامة">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
      </button>
    </div>`;
}

function workerRowHtml(g, w) {
  return `
    <div class="c-row worker-row" data-worker="${w}" style="flex-wrap: wrap; background: #fafafa">
      <div style="display: flex; gap: 8px; width: 100%; align-items: flex-end;">
        <div class="c-col lg">
          <label class="c-label">اسم الفني *</label>
          <input type="text" name="bands[${g}][workers][${w}][name]" class="c-input" placeholder="اسم الفني..." required>
        </div>
        <div class="c-col">
          <label class="c-label">التعاقد</label>
          <select name="bands[${g}][workers][${w}][contract_type]" class="c-input worker-contract-type" onchange="toggleWorkerQtyWrap(${g},${w},this.value)">
            <option value="">— النوع —</option>
            <option value="lump_sum">مقطوعية</option>
            <option value="per_meter">بالمتر</option>
            <option value="per_piece">بالقطعة</option>
            <option value="daily">يومية</option>
          </select>
        </div>
        
        <div class="worker-qty-wrap" style="display:none; gap: 8px; flex: 2">
          <div class="c-col sm">
            <label class="c-label">الكمية</label>
            <input type="number" name="bands[${g}][workers][${w}][contract_qty]" class="c-input worker-qty" placeholder="0" min="0" step="0.01" oninput="recalcWorker(${g},${w})">
          </div>
          <div class="c-col" style="display:none">
            <label class="c-label">تكلفة الوحدة</label>
            <input type="number" name="bands[${g}][workers][${w}][contract_unit_rate]" class="c-input worker-rate" placeholder="0.00" oninput="recalcWorker(${g},${w})">
          </div>
          <div class="c-col">
            <label class="c-label">سعر الوحدة للعميل</label>
            <input type="number" name="bands[${g}][workers][${w}][sell_rate]" class="c-input worker-sell-rate" placeholder="0.00" oninput="recalcWorker(${g},${w})">
          </div>
        </div>

        <div class="c-col" style="display:none">
          <label class="c-label">التكلفة الإجمالية</label>
          <input type="number" name="bands[${g}][workers][${w}][amount]" class="c-input worker-amount" placeholder="0.00" oninput="this.dataset.touched='1'; recalcBandPrice(${g})">
        </div>
        <div class="c-col">
          <label class="c-label">الإجمالي للعميل</label>
          <input type="number" name="bands[${g}][workers][${w}][sell_amount]" class="c-input worker-sell-amount" placeholder="0.00" required oninput="this.dataset.touched='1'; recalcBandPrice(${g})">
        </div>
        <div class="c-col sm">
          <label class="c-label">إشراف %</label>
          <input type="number" name="bands[${g}][workers][${w}][supervision_pct]" class="c-input worker-sup" placeholder="0" max="100" step="0.1" value="{{ $settings->default_supervision_pct }}" oninput="recalcBandPrice(${g})">
        </div>
        <button type="button" class="c-del-btn" onclick="this.closest('.worker-row').remove(); recalcBandPrice(${g})" title="حذف الفني">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
        </button>
      </div>
    </div>`;
}

function toggleWorkerQtyWrap(g, w, type) {
  const row = document.querySelector(`.band-card[data-band="${g}"] .worker-row[data-worker="${w}"]`);
  if (!row) return;
  const show = (type === 'per_meter' || type === 'per_piece' || type === 'daily');
  row.querySelectorAll('.worker-qty-wrap').forEach(wrap => { 
    wrap.style.display = show ? 'flex' : 'none'; 
  });
}

function recalcWorker(g, w) {
  const row = document.querySelector(`.band-card[data-band="${g}"] .worker-row[data-worker="${w}"]`);
  if (!row) return;
  const qty = parseFloat(row.querySelector('.worker-qty').value) || 0;
  const rate = parseFloat(row.querySelector('.worker-rate').value) || 0;
  const sellRate = parseFloat(row.querySelector('.worker-sell-rate').value) || 0;
  const amountField = row.querySelector('.worker-amount');
  const sellField = row.querySelector('.worker-sell-amount');
  
  if (amountField.dataset.touched !== '1') amountField.value = (qty * rate).toFixed(2);
  if (sellField.dataset.touched !== '1' && sellRate > 0) sellField.value = (qty * sellRate).toFixed(2);
  recalcBandPrice(g);
}

function addItem(g, prefill = null) {
  const container = document.getElementById('band-items-' + g);
  const i = container.querySelectorAll('.item-row').length;
  container.insertAdjacentHTML('beforeend', itemRowHtml(g, i));
  if (prefill) {
    const row = document.querySelector(`.band-card[data-band="${g}"] .item-row[data-item="${i}"]`);
    row.querySelector('[name*="[name]"]').value = prefill.name;
    row.querySelector('[name*="[qty]"]').value = prefill.qty;
    row.querySelector('[name*="[unit_price]"]').value = prefill.unit_price;
    row.querySelector('[name*="[supervision_pct]"]').value = prefill.supervision_pct;
  }
  recalcBandPrice(g);
}

function addWorker(g, prefill = null) {
  const container = document.getElementById('band-workers-' + g);
  const w = container.querySelectorAll('.worker-row').length;
  container.insertAdjacentHTML('beforeend', workerRowHtml(g, w));
  if (prefill) {
    const row = document.querySelector(`.band-card[data-band="${g}"] .worker-row[data-worker="${w}"]`);
    row.querySelector('[name*="[name]"]').value = prefill.name || '';
    row.querySelector('[name*="[contract_type]"]').value = prefill.contract_type || '';
    row.querySelector('.worker-qty').value = prefill.contract_qty ?? '';
    row.querySelector('.worker-rate').value = prefill.contract_unit_rate ?? '';
    row.querySelector('.worker-sell-rate').value = prefill.sell_rate ?? '';
    
    const amountField = row.querySelector('.worker-amount');
    const sellField = row.querySelector('.worker-sell-amount');
    amountField.value = prefill.amount ?? 0;
    sellField.value = prefill.sell_amount ?? 0;
    amountField.dataset.touched = '1';
    sellField.dataset.touched = '1';
    
    row.querySelector('.worker-sup').value = prefill.supervision_pct ?? 0;
    
    toggleWorkerQtyWrap(g, w, prefill.contract_type || '');
  }
  recalcBandPrice(g);
}

function recalcBandPrice(g) {
  const card = document.querySelector(`.band-card[data-band="${g}"]`);
  if (!card) return;
  const items = card.querySelectorAll('.item-row');
  const workers = card.querySelectorAll('.worker-row');
  const priceField = card.querySelector('.band-price');

  if (items.length === 0 && workers.length === 0) {
    priceField.readOnly = false;
    updateGlobalTotals();
    return;
  }

  let sum = 0;
  items.forEach(row => {
    const qty = parseFloat(row.querySelector('[name*="[qty]"]').value) || 0;
    const price = parseFloat(row.querySelector('[name*="[unit_price]"]').value) || 0;
    const pct = parseFloat(row.querySelector('[name*="[supervision_pct]"]').value) || 0;
    sum += qty * price * (1 + pct / 100);
  });
  workers.forEach(row => {
    const amount = parseFloat(row.querySelector('.worker-amount').value) || 0;
    const sellAmount = parseFloat(row.querySelector('.worker-sell-amount').value) || 0;
    const pct = parseFloat(row.querySelector('.worker-sup').value) || 0;
    const base = sellAmount || amount;
    sum += base * (1 + pct / 100);
  });
  
  priceField.value = sum.toFixed(2);
  priceField.readOnly = true;
  updateGlobalTotals();
}

function addBand(prefill = null) {
  const g = bandIdx++;
  document.getElementById('bands-list').insertAdjacentHTML('beforeend', bandRowHtml(g));
  if (prefill) {
    const card = document.querySelector(`.band-card[data-band="${g}"]`);
    card.querySelector('[name*="[name]"]').value = prefill.name;
    card.querySelector('.band-price').value = prefill.price;
    (prefill.items || []).forEach(item => addItem(g, item));
    (prefill.workers || []).forEach(worker => addWorker(g, worker));
  }
  updateGlobalTotals();
}

const existingBands = @json($existingBandsData);

if (existingBands.length) {
  existingBands.forEach(b => addBand(b));
} else {
  addBand();
}
</script>
@endpush
@endsection
