@extends('layouts.app')
@section('title', 'تسجيل خامات')
@section('page-title', 'تسجيل خامات')

@section('content')
<div class="page-head">
  <div><h3>تسجيل خامات</h3><p>اختر المشروع، ثم أضف بنداً وسجّل كل الأصناف المشتراة له، وكرر لأي بند آخر — كل ده هيتسجل مرة واحدة</p></div>
  <a href="{{ route('materials.index') }}" class="btn ghost">رجوع</a>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>
      @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  </div>
@endif

<style>
  /* تخطيط شاشة الخامات الأساسي */
  .mat-layout {
    --accent: #2563eb;
    --accent-2: #3b82f6;
    --accent-soft: #eff6ff;
    --accent-ink: #1e3a8a;
    display:flex; gap:32px; align-items:flex-start;
  }
  .mat-layout .mat-form{flex:1;min-width:0}
  
  /* الإجماليات العائمة (Glassmorphism Sidebar) */
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

  /* زر الإضافة الأنيق */
  .neo-add-btn {
    background: linear-gradient(45deg, var(--accent), var(--accent-2));
    color: white;
    border: none;
    border-radius: 50px;
    padding: 16px 32px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35);
    transition: all 0.3s;
    margin-top: 12px;
  }
  .neo-add-btn:hover {
    box-shadow: 0 15px 35px rgba(99, 102, 241, 0.5);
    transform: translateY(-3px);
  }

  /* قسم الدفع الناعم */
  .neo-pay-section {
    background: #ffffff;
    border-radius: 28px;
    padding: 28px;
    margin-top: 32px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.02);
  }
  .neo-radio-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 24px;
    background: #f8fafc;
    border: 2px solid transparent;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 14px;
    font-weight: 700;
    color: var(--ink-2);
  }
  .neo-radio-pill:hover { background: #f1f5f9; transform: translateY(-2px); }
  .neo-radio-pill:has(input:checked) {
    background: #ffffff;
    border-color: var(--accent);
    color: var(--accent);
    box-shadow: 0 10px 20px rgba(99, 102, 241, 0.15);
  }
  .neo-radio-pill input[type="radio"] { margin: 0; width:18px; height:18px; accent-color: var(--accent); }

  /* الحقول الكبيرة (Select / Date) في الهيدر والدفع */
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

  @media (max-width:1100px) {
    .mat-layout{flex-direction:column}
    .mat-totals{position:static;width:100%}
    .neo-item-bubble { gap: 12px; }
  }
</style>

<div class="mat-layout">
<form method="POST" action="{{ route('materials.store') }}" class="mat-form">
  @csrf

  <div class="form-card" style="max-width:none;margin-bottom:16px">
    <div class="row3" style="margin-bottom:16px; gap:24px;">
      <div class="field" style="margin-bottom:0">
        <label>المشروع *</label>
        <select name="project_id" id="project_select" required class="neo-big-input" onchange="loadBandsForProject(this.value); updateSupervisionDefault(this); checkProjectLock(this)">
          <option value="">— اختر المشروع —</option>
          @foreach($projects as $p)
            <option value="{{ $p->id }}" data-sup="{{ $p->default_supervision_pct > 0 ? $p->default_supervision_pct : $settings->default_supervision_pct }}" data-locked="{{ $p->hasWholeProjectInstallmentContract() ? 1 : 0 }}" {{ $selectedProject?->id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="field" style="margin-bottom:0">
        <label>اسم الفاتورة *</label>
        <input type="text" name="invoice_name" id="invoice_name" class="neo-big-input" placeholder="مثال: فاتورة مشروع كذا..." required oninput="invoiceNameTouched = true">
      </div>
    </div>
    
    <div class="row2" style="margin-bottom:0; gap:24px;">
      <div class="field" style="margin-bottom:0">
        <label>تاريخ الفاتورة *</label>
        <input type="date" name="date" id="invoice_date" required class="neo-big-input" onchange="suggestInvoiceName()" value="{{ today()->format('Y-m-d') }}">
      </div>
      <div class="field" style="margin-bottom:0">
        <label>المورد *</label>
        <select name="supplier_id" class="neo-big-input">
          <option value="">— بدون مورد —</option>
          @foreach($suppliers as $s)
            <option value="{{ $s->id }}">{{ $s->name }} {{ $s->activity ? '— ' . $s->activity : '' }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  <div id="wholeContractBanner" class="flash error" style="display:none">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>تم تقسيط هذا المشروع بالكامل — لا يمكن شراء خامات جديدة له.</div>
  </div>

  <div id="groups-container"></div>

  <button type="button" id="addGroupBtn" class="btn ghost" style="margin-bottom:20px" onclick="addGroup()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    إضافة بند آخر للفاتورة
  </button>

  {{-- طريقة الدفع --}}
  <div class="neo-pay-section" id="payment_section">
    <div style="font-size:1.15rem;font-weight:800;color:var(--ink);margin-bottom:20px;display:flex;align-items:center;gap:10px;">
      <div style="background:#f1f5f9; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--ink-2);">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-credit-card"/></svg>
      </div>
      كيف سيتم سداد هذه الفاتورة؟
    </div>
    
    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px">
      <label class="neo-radio-pill">
        <input type="radio" name="payment_status" value="paid" checked onchange="togglePaidAmt(this.value)">
        <span>دفع نقدي بالكامل</span>
      </label>
      <label class="neo-radio-pill">
        <input type="radio" name="payment_status" value="partial" onchange="togglePaidAmt(this.value)">
        <span>دفع جزء و تبقي دين</span>
      </label>
      <label class="neo-radio-pill">
        <input type="radio" name="payment_status" value="deferred" onchange="togglePaidAmt(this.value)">
        <span>آجل بالكامل (دين)</span>
      </label>
    </div>
    
    <div class="row2" style="align-items:flex-start; gap:24px;">
      <div class="field" style="margin:0" id="wallet-row">
        <label style="font-weight:700; color:var(--ink-2); margin-bottom:8px; display:block;">المحفظة للصرف *</label>
        <select name="account_id" id="wallet_select" required class="neo-big-input"></select>
      </div>
      <div id="paid-amt-row" style="display:none">
        <div class="field" style="margin:0">
          <label style="font-weight:700; color:var(--ink-2); margin-bottom:8px; display:block;">المبلغ المدفوع الآن (ج.م) *</label>
          <input type="number" name="paid_amount" id="paid-amt" min="0" step="0.01" placeholder="مثال: 1500" class="neo-big-input">
        </div>
      </div>
    </div>
  </div>

  <div class="btn-row" style="margin-top: 32px">
    <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ كل الأصناف</button>
    <a href="{{ route('materials.index') }}" class="btn ghost">إلغاء</a>
  </div>
</form>

{{-- Live totals across every item — updates as you type (عمود جانبي شيك) --}}
<aside class="mat-totals">
  <div class="section-label">الإجماليات</div>
  <div class="card stat">
    <div class="top"><span class="label">عدد الأصناف</span></div>
    <div class="val tnum"><span id="tot-items-count">0</span></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي الشراء (تكلفة)</span></div>
    <div class="val tnum"><span id="tot-purchase">0</span> <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي البيع</span></div>
    <div class="val tnum"><span id="tot-sell">0</span> <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">الإجمالي للعميل (بعد الإشراف)</span></div>
    <div class="val tnum"><span id="tot-client">0</span> <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي الربح</span></div>
    <div class="val tnum" style="color:var(--pos)"><span id="tot-profit">0</span> <small>ج.م</small></div>
    <div class="note">فرق السعر: <span id="tot-pricediff">0</span> · إشراف: <span id="tot-sup">0</span></div>
  </div>
</aside>
</div>{{-- /mat-layout --}}

{{-- Shared item/unit suggestions used inside every item row — مبنية من كل
     الأصناف والوحدات اللي سبق كتابتها فعلاً في النظام (autocomplete حقيقي) --}}
<datalist id="items-list">
  @foreach($itemNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>
<datalist id="units-list">
  @foreach($unitNames as $unit)
    <option value="{{ $unit }}">
  @endforeach
</datalist>

@push('scripts')
<script>
// Bands of the currently selected project — refreshed whenever the project changes
let bandsList = @json($bands);
let groupCounter = 0;
let invoiceNameTouched = false;

function suggestInvoiceName() {
  if (invoiceNameTouched) return;
  const projSel = document.getElementById('project_select');
  const projName = projSel && projSel.selectedIndex > 0 ? projSel.options[projSel.selectedIndex].text : '';
  const dateInput = document.getElementById('invoice_date');
  const dateVal = dateInput ? dateInput.value : '';
  const bandSel = document.querySelector('.band-select');
  const bandName = bandSel && bandSel.selectedIndex > 0 ? bandSel.options[bandSel.selectedIndex].text : '';

  let nameParts = ['فاتورة'];
  if (projName) nameParts.push(projName);
  if (bandName) nameParts.push(bandName);
  if (dateVal) nameParts.push(dateVal);

  const invNameInput = document.getElementById('invoice_name');
  if (invNameInput && nameParts.length > 1) {
    invNameInput.value = nameParts.join(' - ');
  }
}

// مشروع اتقسّط بالكامل يقفل الفورم كله (مفيش خامات جديدة تتضاف له)
function checkProjectLock(sel) {
  const opt = sel.options[sel.selectedIndex];
  const locked = opt?.dataset.locked === '1';
  document.getElementById('groups-container').style.display = locked ? 'none' : '';
  document.getElementById('addGroupBtn').disabled = locked;
  document.getElementById('wholeContractBanner').style.display = locked ? 'flex' : 'none';
  suggestInvoiceName();
}

// Default supervision % for new item rows — follows the selected project's
// default (falls back to the global settings value). Editable per row.
let defaultSupervisionPct = {{ $selectedProject ? ($selectedProject->default_supervision_pct > 0 ? $selectedProject->default_supervision_pct : $settings->default_supervision_pct) : $settings->default_supervision_pct }};
function updateSupervisionDefault(select) {
  const opt = select.options[select.selectedIndex];
  if (opt && opt.dataset.sup !== undefined) defaultSupervisionPct = parseFloat(opt.dataset.sup) || 0;
  // Push the project's rate into every supervision field the user hasn't
  // manually overridden — so picking the project fills them all in, instead of
  // leaving the first row stuck on whatever default existed at page load.
  document.querySelectorAll('.mat-sup').forEach(inp => {
    if (inp.dataset.touched !== '1') inp.value = defaultSupervisionPct;
  });
  recalcTotals();
}

// Supplier list — built once, then rendered as <option> HTML per-call so we
// can mark the group's chosen default as selected on every new item row
// (بدل ما تختار نفس المورد يدويًا في كل صنف لو الـ 40 صنف كلهم من مورد واحد)
const suppliersData = @json($suppliers->map(fn ($s) => ['id' => $s->id, 'label' => $s->name . ($s->activity ? ' — ' . $s->activity : '')]));

function supplierOptionsHtml(selectedId = '') {
  let html = `<option value="" ${String(selectedId) === '' ? 'selected' : ''}>— بدون مورد —</option>`;
  suppliersData.forEach(s => {
    html += `<option value="${s.id}" ${String(selectedId) === String(s.id) ? 'selected' : ''}>${s.label}</option>`;
  });
  return html;
}

// المورد الافتراضي المختار لكل مجموعة (بيتطبق على كل صنف جديد يتضاف للمجموعة
// دي، ما لم يكن المستخدم غيّر مورد الصنف ده بنفسه — شايف mat-supplier/touched)
const groupDefaultSupplier = {};
function updateGroupSupplierDefault(g, supplierId) {
  groupDefaultSupplier[g] = supplierId;
  document.querySelectorAll('#items-' + g + ' .mat-supplier').forEach(sel => {
    if (sel.dataset.touched !== '1') sel.value = supplierId;
  });
}

// Wallet <option> list — أي محفظة بعد دمج السيستمين (إجباري الاختيار)
const walletOptionsHtml = `
  <option value="" disabled selected>— اختر المحفظة —</option>
  @foreach($wallets->groupBy(fn($w) => $w->categoryAr()) as $cat => $grp)
    <optgroup label="{{ $cat }}">
      @foreach($grp as $w)
        <option value="{{ $w->id }}">{{ $w->account_name }}@if($w->id == \App\Models\Account::WALLET_ID) ★@endif — {{ \App\Support\Money::format($w->balance) }} ج</option>
      @endforeach
    </optgroup>
  @endforeach
`;

function bandOptionsHtml() {
  let html = '<option value="">— بند عام (بدون بند) —</option>';
  bandsList.forEach(b => {
    if (b.has_contract) {
      html += `<option value="${b.id}" disabled>${b.name} (بند مقسط — اعمل بند جديد)</option>`;
    } else {
      html += `<option value="${b.id}">${b.name}</option>`;
    }
  });
  return html;
}

// Refresh every band <select> already on the page after the project changes
function refreshAllBandSelects() {
  document.querySelectorAll('.band-select').forEach(sel => {
    sel.innerHTML = bandOptionsHtml();
  });
}

// Load the bands of the chosen project via the JSON API used elsewhere in the app
function loadBandsForProject(projectId) {
  bandsList = [];
  if (!projectId) { refreshAllBandSelects(); return; }
  fetch('/api/projects/' + projectId + '/bands')
    .then(r => r.json())
    .then(bands => { bandsList = bands; refreshAllBandSelects(); })
    .catch(() => {});
}

function itemRowHtml(g, i) {
  return `
    <div class="neo-item-bubble item-row">
      <div class="neo-col neo-col-main">
        <div class="neo-label">اسم الصنف</div>
        <input type="text" name="groups[${g}][items][${i}][item]" class="neo-input" placeholder="أسمنت، سيراميك، دهانات..." required list="items-list">
      </div>
      <input type="hidden" name="groups[${g}][items][${i}][unit]" value="وحدة">
      <div class="neo-col neo-col-sm">
        <div class="neo-label">الكمية</div>
        <input type="number" name="groups[${g}][items][${i}][qty]" class="neo-input mat-qty" placeholder="0" min="0" step="0.1" required oninput="recalcTotals()">
      </div>
      <div class="neo-col">
        <div class="neo-label">سعر الشراء</div>
        <input type="number" name="groups[${g}][items][${i}][unit_price]" class="neo-input mat-cost" placeholder="0.00" min="0" step="0.01" required oninput="recalcTotals()">
      </div>
      <div class="neo-col" style="background:#f8fafc; border-radius:12px; padding:0 8px;">
        <div class="neo-label">إجمالي الشراء</div>
        <div class="neo-input item-total" style="color:var(--accent) !important; font-weight:800 !important; border:none !important; cursor:default; padding-top:10px !important;">0 ج.م</div>
      </div>
      <div class="neo-col">
        <div class="neo-label">سعر البيع</div>
        <input type="number" name="groups[${g}][items][${i}][sell_price]" class="neo-input mat-sell" placeholder="0.00" min="0" step="0.01" required oninput="recalcTotals()">
      </div>
      <div class="neo-col neo-col-sm">
        <div class="neo-label">إشراف %</div>
        <input type="number" name="groups[${g}][items][${i}][supervision_pct]" class="neo-input mat-sup" placeholder="0" min="0" max="100" step="0.1" value="${defaultSupervisionPct}" oninput="this.dataset.touched='1'; recalcTotals()">
      </div>
      <button type="button" class="neo-delete-btn" onclick="this.closest('.neo-item-bubble').remove(); recalcTotals()" title="حذف الصنف">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#i-x"/></svg>
      </button>
    </div>`;
}

// Add another item row inside a specific band group
function addItem(g) {
  const container = document.getElementById('items-' + g);
  const i = container.children.length;
  container.insertAdjacentHTML('beforeend', itemRowHtml(g, i));
  recalcTotals();
}

// Live running totals across every item in every band group. Shows what we pay
// (شراء), what the client pays before markup (بيع), the full client total
// after the supervision markup (الكل), and the profit split into its two
// sources: فرق السعر (بيع − شراء) and الإشراف (نسبة الإشراف فوق البيع).
function recalcTotals() {
  let purchase = 0, sell = 0, client = 0;
  document.querySelectorAll('.item-row').forEach(row => {
    const qty  = parseFloat(row.querySelector('.mat-qty')?.value)  || 0;
    const cost = parseFloat(row.querySelector('.mat-cost')?.value) || 0;
    const sp   = parseFloat(row.querySelector('.mat-sell')?.value) || 0;
    const pct  = parseFloat(row.querySelector('.mat-sup')?.value)  || 0;
    
    const itemTotalEl = row.querySelector('.item-total');
    if (itemTotalEl) {
        itemTotalEl.textContent = Math.round(qty * cost).toLocaleString('en-US') + ' ج.م';
    }

    purchase += qty * cost;
    sell     += qty * sp;
    client   += qty * sp * (1 + pct / 100);
  });

  const itemsCount = document.querySelectorAll('.item-row').length;

  const priceDiff = sell - purchase;   // فرق السعر
  const supMarkup = client - sell;     // ربح الإشراف
  const profit    = client - purchase; // إجمالي الربح

  const fmt = n => Math.round(n).toLocaleString('en-US');
  setTxt('tot-items-count', itemsCount);
  setTxt('tot-purchase', fmt(purchase));
  setTxt('tot-sell',     fmt(sell));
  setTxt('tot-client',   fmt(client));
  setTxt('tot-profit',   fmt(profit));
  setTxt('tot-pricediff', fmt(priceDiff));
  setTxt('tot-sup',       fmt(supMarkup));
}
function setTxt(id, v) { const el = document.getElementById(id); if (el) el.textContent = v; }

function addGroup() {
  const g = groupCounter++;
  const html = `
    <div class="neo-group-card band-group" data-group="${g}">
      <div class="neo-group-header">
        <div class="neo-group-title">
          <div style="background:var(--accent-soft); width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--accent);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-box"/></svg>
          </div>
          مجموعة أصناف (${g + 1})
        </div>
        <button type="button" class="btn ghost danger" style="border-radius:50px; font-weight:700;" onclick="this.closest('.band-group').remove(); recalcTotals()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
          حذف المجموعة
        </button>
      </div>
      
      <div class="row3" style="margin-bottom:32px; gap:24px;">
        <div class="field" style="margin:0; flex:2;">
          <label style="font-weight:700; color:var(--ink-2); margin-bottom:8px; display:block;">البند التابع له هذه الأصناف</label>
          <select name="groups[${g}][band_id]" class="neo-big-input band-select" onchange="suggestInvoiceName()">${bandOptionsHtml()}</select>
        </div>
      </div>
      
      <div class="items-container" id="items-${g}"></div>
      
      <button type="button" class="neo-add-btn" onclick="addItem(${g})">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة صنف آخر لهذا البند
      </button>
    </div>`;
  document.getElementById('groups-container').insertAdjacentHTML('beforeend', html);
  addItem(g);
}

function togglePaidAmt(val) {
  const row = document.getElementById('paid-amt-row');
  const inp = document.getElementById('paid-amt');
  if (val === 'partial') {
    row.style.display = 'block';
    inp.required = true;
  } else {
    row.style.display = 'none';
    inp.required = false;
    inp.value = '';
  }

  // آجل بالكامل = مفيش فلوس بتتصرف دلوقتي، فمفيش داعي تختار محفظة
  const walletRow = document.getElementById('wallet-row');
  const walletSel = document.getElementById('wallet_select');
  if (val === 'deferred') {
    walletRow.style.display = 'none';
    walletSel.required = false;
    walletSel.selectedIndex = 0;
  } else {
    walletRow.style.display = 'block';
    walletSel.required = true;
  }
}

// init defaults
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('invoice_date').value = new Date().toISOString().slice(0, 10);
  document.getElementById('wallet_select').innerHTML = walletOptionsHtml;
});

// Start with one band group ready to fill in
addGroup();

// لو المشروع متحدد مسبقًا من الرابط (project_id) وهو متقسّط بالكامل، اقفل
// الفورم من أول ما الصفحة تحمّل بدل ما ينتظر المستخدم يغيّر السيلكت
@if($selectedProject)
  checkProjectLock(document.getElementById('project_select'));
@endif
</script>
@endpush
@endsection
