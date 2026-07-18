@extends('layouts.app')
@section('title', 'عرض سعر جديد')
@section('page-title', 'عرض سعر جديد')

@section('content')
<div class="page-head"><div><h3>عرض سعر جديد</h3></div><a href="{{ route('quotes.index') }}" class="btn ghost">رجوع</a></div>
<style>
  /* تخطيط الشاشة الأساسي */
  .mat-layout {
    --accent: #2563eb;
    --accent-2: #3b82f6;
    --accent-soft: #eff6ff;
    --accent-ink: #1e3a8a;
    display:flex; gap:32px; align-items:flex-start;
  }
  .mat-layout .mat-form{flex:1;min-width:0}
  
  /* الإجماليات العائمة */
  .mat-totals {
    position:sticky;top:24px;width:300px;flex-shrink:0;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 32px;
    padding: 28px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.04), inset 0 2px 4px rgba(255,255,255,0.5);
  }
  .mat-totals .section-label{margin:0 0 20px; font-size: 1.3rem; color: var(--ink); font-weight: 800; text-align: center;}
  .mat-totals .card.stat {
    margin:0 0 16px;
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border: none;
    box-shadow: 0 8px 20px rgba(0,0,0,0.03);
    padding: 16px 20px;
    border-radius: 20px;
    transition: transform 0.3s;
  }
  .mat-totals .card.stat:hover { transform: translateY(-3px); }
  .mat-totals .card.stat:last-child{margin-bottom:0}
  
  /* البنية الأساسية للمجموعة (Fluid Group Card) */
  .neo-group-card {
    background: linear-gradient(145deg, #f8fafc, #f1f5f9);
    border-radius: 36px;
    padding: 32px;
    margin-bottom: 32px;
    box-shadow: inset 0 2px 4px rgba(255,255,255,0.8), 0 15px 35px rgba(0,0,0,0.03);
    border: 1px solid rgba(255,255,255,0.6);
  }
  .neo-group-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
  }
  .neo-group-title {
    font-size: 1.4rem;
    font-weight: 800;
    background: linear-gradient(45deg, var(--accent), var(--accent-2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  /* الفقاعات العائمة للأصناف (Floating Bubbles) */
  .neo-item-bubble {
    background: #ffffff;
    border-radius: 24px;
    padding: 14px 24px;
    display: flex;
    gap: 20px;
    align-items: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03), 0 2px 10px rgba(0,0,0,0.01);
    margin-bottom: 16px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(255,255,255,0.5);
    flex-wrap: wrap;
  }
  .neo-item-bubble:focus-within {
    box-shadow: 0 15px 40px rgba(99, 102, 241, 0.15), 0 4px 15px rgba(99, 102, 241, 0.1);
    transform: translateY(-4px) scale(1.01);
    border-color: rgba(99, 102, 241, 0.3);
  }
  
  .neo-col {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
    min-width: 100px;
  }
  .neo-col-main { flex: 2; min-width: 200px; }
  .neo-col-sm { flex: 0.8; min-width: 80px; }
  
  .neo-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--ink-3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding-inline-start: 4px;
  }
  .neo-input {
    border: none !important;
    background: transparent !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    color: var(--ink) !important;
    padding: 8px 4px !important;
    width: 100% !important;
    transition: all 0.2s !important;
    box-shadow: none !important;
    height: auto !important;
    border-radius: 0 !important;
    border-bottom: 2px solid transparent !important;
  }
  .neo-input:focus {
    outline: none !important;
    color: var(--accent) !important;
    border-bottom: 2px solid var(--accent) !important;
  }
  .neo-input::placeholder {
    color: #cbd5e1;
    font-weight: 500;
  }
  
  /* زر الحذف الدائري */
  .neo-delete-btn {
    background: #fef2f2;
    color: #dc2626;
    border: none;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
    margin-top: 18px;
  }
  .neo-delete-btn:hover {
    background: #dc2626;
    color: #fff;
    transform: scale(1.15) rotate(90deg);
    box-shadow: 0 10px 20px rgba(220, 38, 38, 0.3);
  }

  /* الحقول الكبيرة */
  .neo-big-input {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 14px 20px !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 10px rgba(0,0,0,0.01) !important;
    transition: all 0.3s !important;
    height: 54px !important;
  }
  .neo-big-input:focus {
    border-color: var(--accent) !important;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.15) !important;
  }

  .neo-add-btn {
    background: linear-gradient(45deg, var(--accent), var(--accent-2));
    color: white;
    border: none;
    border-radius: 50px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.2);
    transition: all 0.3s;
    margin-top: 8px;
  }
  .neo-add-btn:hover {
    box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
    transform: translateY(-2px);
  }

  @media (max-width:1100px) {
    .mat-layout{flex-direction:column}
    .mat-totals{position:static;width:100%}
    .neo-item-bubble { gap: 12px; }
  }
</style>

<div class="mat-layout">
<form method="POST" action="{{ route('quotes.store') }}" class="mat-form">
  @csrf
  <div class="form-card" style="max-width:none;margin-bottom:16px; border-radius: 28px; padding: 32px">
    <div class="row2" style="margin-bottom:16px; gap:24px;">
      <div class="field" style="margin-bottom:0">
        <label>العميل *</label>
        <select name="client_id" required class="neo-big-input">
          <option value="">— اختر العميل —</option>
          @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}@if($c->phone) — {{ $c->phone }}@endif</option>
          @endforeach
        </select>
      </div>
      <div class="field" style="margin-bottom:0">
        <label>رقم المرجع *</label>
        <input type="text" name="ref" value="{{ old('ref', $nextRef) }}" required class="neo-big-input">
      </div>
    </div>
    
    <div class="row3" style="margin-bottom:16px; gap:24px;">
      <div class="field" style="margin-bottom:0">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required class="neo-big-input">
      </div>
      <div class="field" style="margin-bottom:0">
        <label>الحالة</label>
        <select name="status" class="neo-big-input">
          <option value="draft">قيد المراجعة</option>
          <option value="sent">تم الإرسال</option>
          <option value="approved">معتمد</option>
        </select>
      </div>
      <div class="field" style="margin-bottom:0">
        <label>المساحة (م²)</label>
        <input type="number" name="area" value="{{ old('area') }}" min="0" step="0.5" class="neo-big-input" placeholder="0">
      </div>
    </div>

    <div class="row2" style="margin-bottom:0; gap:24px;">
      <div class="field" style="margin-bottom:0">
        <label>العنوان</label>
        <input type="text" name="address" value="{{ old('address') }}" class="neo-big-input" placeholder="عنوان المشروع...">
      </div>
      <div class="field" style="margin-bottom:0">
        <label>ملاحظات</label>
        <input type="text" name="note" value="{{ old('note') }}" class="neo-big-input" placeholder="أي ملاحظات إضافية...">
      </div>
    </div>
  </div>

  <div id="bands-list"></div>
  
  <button type="button" class="btn ghost" style="margin-bottom:20px; font-size: 15px; font-weight: 700" onclick="addBand()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><use href="#i-plus"/></svg>
    إضافة بند جديد لعرض السعر
  </button>

  <div class="btn-row" style="margin-top: 32px">
    <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ عرض السعر</button>
    <a href="{{ route('quotes.index') }}" class="btn ghost">إلغاء</a>
  </div>
</form>

<aside class="mat-totals">
  <div class="section-label">الإجماليات</div>
  <div class="card stat">
    <div class="top"><span class="label">عدد البنود</span></div>
    <div class="val tnum"><span id="tot-bands-count">0</span></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">عدد الخامات المُضافة</span></div>
    <div class="val tnum"><span id="tot-items-count">0</span></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">عدد مصنعيات/فنيين</span></div>
    <div class="val tnum"><span id="tot-workers-count">0</span></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">الإجمالي النهائي للعميل</span></div>
    <div class="val tnum" style="color: var(--pos); font-size: 1.8rem"><span id="tot-quote-price">0</span> <small style="font-size: 1rem">ج.م</small></div>
  </div>
</aside>
</div>

<datalist id="band-names-list">
  @foreach($bandNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>

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

  document.getElementById('tot-bands-count').innerText = bandsCount;
  document.getElementById('tot-items-count').innerText = itemsCount;
  document.getElementById('tot-workers-count').innerText = workersCount;
  document.getElementById('tot-quote-price').innerText = totalPrice.toFixed(2);
}

function bandRowHtml(g) {
  return `
    <div class="neo-group-card band-card" data-band="${g}">
      <div class="neo-group-header">
        <div class="neo-group-title">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-layers"/></svg>
          بند #${g + 1}
        </div>
        <button type="button" class="btn ghost sm danger" onclick="this.closest('.band-card').remove(); updateGlobalTotals()" style="border:1px solid rgba(220,38,38,0.2)">
          حذف البند بالكامل
        </button>
      </div>

      <div class="row2" style="margin-bottom: 24px">
        <div class="field" style="margin:0">
          <label style="font-weight:700">اسم البند *</label>
          <input type="text" name="bands[${g}][name]" placeholder="محارة / سيراميك / دهانات..." required list="band-names-list" class="neo-big-input">
        </div>
        <div class="field" style="margin:0; width: 250px">
          <label style="font-weight:700">إجمالي البند للعميل (ج.م)</label>
          <input type="number" name="bands[${g}][price]" class="neo-big-input band-price" placeholder="0.00" min="0" step="0.01" style="font-weight:800;color:var(--accent-ink);background:#eff6ff !important; border-color: #bfdbfe !important" oninput="updateGlobalTotals()">
          <small style="color:var(--pos);font-size:.75rem;margin-top:4px;display:block">يُحسب تلقائياً من الأصناف إذا وُجدت</small>
        </div>
      </div>

      <div style="background: rgba(255,255,255,0.5); border-radius: 20px; padding: 20px; border: 1px dashed rgba(0,0,0,0.1); margin-bottom: 16px">
        <div style="font-weight:800; color:var(--ink-2); margin-bottom:12px; display:flex; align-items:center; gap:8px">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-box"/></svg>
          خامات البند
        </div>
        <div class="band-items" id="band-items-${g}"></div>
        <button type="button" class="neo-add-btn" onclick="addItem(${g})">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-plus"/></svg>
          إضافة خامة
        </button>
      </div>

      <div style="background: rgba(255,255,255,0.5); border-radius: 20px; padding: 20px; border: 1px dashed rgba(0,0,0,0.1)">
        <div style="font-weight:800; color:var(--ink-2); margin-bottom:12px; display:flex; align-items:center; gap:8px">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-users"/></svg>
          مصنعيات البند (الفنيين)
        </div>
        <div class="band-workers" id="band-workers-${g}"></div>
        <button type="button" class="neo-add-btn" onclick="addWorker(${g})" style="background: linear-gradient(45deg, #10b981, #34d399)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-plus"/></svg>
          إضافة فني / مصنعية
        </button>
      </div>
    </div>`;
}

function itemRowHtml(g, i) {
  return `
    <div class="neo-item-bubble item-row" data-item="${i}">
      <div class="neo-col neo-col-main">
        <span class="neo-label">اسم الخامة *</span>
        <input type="text" name="bands[${g}][items][${i}][name]" class="neo-input" placeholder="أسمنت، سيراميك..." required oninput="recalcBandPrice(${g})">
      </div>
      <div class="neo-col neo-col-sm">
        <span class="neo-label">الكمية</span>
        <input type="number" name="bands[${g}][items][${i}][qty]" class="neo-input" placeholder="0" min="0" step="0.01" value="1" required oninput="recalcBandPrice(${g})">
      </div>
      <div class="neo-col">
        <span class="neo-label" style="color:var(--pos)">سعر البيع للعميل</span>
        <input type="number" name="bands[${g}][items][${i}][unit_price]" class="neo-input" placeholder="0.00" min="0" step="0.01" required oninput="recalcBandPrice(${g})">
      </div>
      <div class="neo-col neo-col-sm">
        <span class="neo-label" style="color:#7c3aed">إشراف %</span>
        <input type="number" name="bands[${g}][items][${i}][supervision_pct]" class="neo-input" placeholder="0" min="0" max="100" step="0.1" value="{{ $settings->default_supervision_pct }}" oninput="recalcBandPrice(${g})">
      </div>
      <button type="button" class="neo-delete-btn ir-del" onclick="this.closest('.item-row').remove(); recalcBandPrice(${g})" title="حذف الخامة">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
      </button>
    </div>`;
}

function workerRowHtml(g, w) {
  return `
    <div class="neo-item-bubble worker-row" data-worker="${w}" style="border: 2px solid #e2e8f0; border-left: 4px solid #10b981; align-items: flex-start;">
      <div style="flex: 1; display: flex; flex-direction: column; gap: 12px; width: 100%">
        
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
          <div class="neo-col neo-col-main">
            <span class="neo-label">اسم الفني *</span>
            <input type="text" name="bands[${g}][workers][${w}][name]" class="neo-input" placeholder="مثال: أحمد مقاول المحارة..." required>
          </div>
          <div class="neo-col">
            <span class="neo-label">التخصص (اختياري)</span>
            <input type="text" name="bands[${g}][workers][${w}][specialty]" class="neo-input" placeholder="مثال: مبيض محارة">
          </div>
          <div class="neo-col">
            <span class="neo-label">نوع التعاقد</span>
            <select name="bands[${g}][workers][${w}][contract_type]" class="neo-input worker-contract-type" onchange="toggleWorkerQtyWrap(${g},${w},this.value)">
              <option value="">— نوع التعاقد —</option>
              <option value="lump_sum">مقاولة مقطوعة</option>
              <option value="per_meter">بالمتر</option>
              <option value="per_piece">بالقطعة</option>
              <option value="daily">يومية</option>
            </select>
          </div>
        </div>

        <div class="worker-qty-wrap" style="display:none; gap: 20px; flex-wrap: wrap; background: #f8fafc; padding: 12px; border-radius: 16px;">
          <div class="neo-col">
            <span class="neo-label">الكمية</span>
            <input type="number" name="bands[${g}][workers][${w}][contract_qty]" class="neo-input worker-qty" placeholder="الكمية (متر/قطعة/يوم)" min="0" step="0.01" oninput="recalcWorker(${g},${w})">
          </div>
          <div class="neo-col" style="display:none">
            <span class="neo-label">سعر الوحدة (تكلفة)</span>
            <input type="number" name="bands[${g}][workers][${w}][contract_unit_rate]" class="neo-input worker-rate" placeholder="0.00" min="0" step="0.01" oninput="recalcWorker(${g},${w})">
          </div>
          <div class="neo-col">
            <span class="neo-label" style="color:var(--pos)">سعر الوحدة للعميل</span>
            <input type="number" name="bands[${g}][workers][${w}][sell_rate]" class="neo-input worker-sell-rate" placeholder="0.00" min="0" step="0.01" oninput="recalcWorker(${g},${w})">
          </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
          <div class="neo-col" style="display:none">
            <span class="neo-label">الأجر الإجمالي (تكلفة)</span>
            <input type="number" name="bands[${g}][workers][${w}][amount]" class="neo-input worker-amount" placeholder="0.00" min="0" step="0.01" oninput="this.dataset.touched='1'; recalcBandPrice(${g})">
          </div>
          <div class="neo-col">
            <span class="neo-label" style="color:var(--pos)">إجمالي المصنعية للعميل</span>
            <input type="number" name="bands[${g}][workers][${w}][sell_amount]" class="neo-input worker-sell-amount" placeholder="0.00" min="0" step="0.01" oninput="this.dataset.touched='1'; recalcBandPrice(${g})">
          </div>
          <div class="neo-col neo-col-sm">
            <span class="neo-label" style="color:#7c3aed">إشراف %</span>
            <input type="number" name="bands[${g}][workers][${w}][supervision_pct]" class="neo-input worker-sup" placeholder="0" min="0" max="100" step="0.1" value="{{ $settings->default_supervision_pct }}" oninput="recalcBandPrice(${g})">
          </div>
        </div>

      </div>

      <button type="button" class="neo-delete-btn" onclick="this.closest('.worker-row').remove(); recalcBandPrice(${g})" title="حذف الفني" style="align-self: center; margin-top: 0">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
      </button>
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

function addItem(g) {
  const container = document.getElementById('band-items-' + g);
  const i = container.querySelectorAll('.item-row').length;
  container.insertAdjacentHTML('beforeend', itemRowHtml(g, i));
  recalcBandPrice(g);
}

function addWorker(g) {
  const container = document.getElementById('band-workers-' + g);
  const w = container.children.length;
  container.insertAdjacentHTML('beforeend', workerRowHtml(g, w));
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

function addBand() {
  const g = bandIdx++;
  document.getElementById('bands-list').insertAdjacentHTML('beforeend', bandRowHtml(g));
  updateGlobalTotals();
}

addBand();
</script>
@endpush
@endsection
