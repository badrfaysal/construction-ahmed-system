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
  /* تخطيط شاشة الخامات: الفورم + كروت الإجماليات جنبه في عمود ثابت (sticky) */
  .mat-layout{display:flex;gap:20px;align-items:flex-start}
  .mat-layout .mat-form{flex:1;min-width:0}
  .mat-totals{position:sticky;top:20px;width:250px;flex-shrink:0;
    background:var(--surface,#fff);border:1px solid var(--line,#e6ebf3);
    border-radius:14px;padding:16px;box-shadow:0 4px 20px rgba(15,23,42,.05)}
  .mat-totals .section-label{margin:0 0 12px}
  .mat-totals .card.stat{margin:0 0 10px;background:var(--bg,#f4f7fb)}
  .mat-totals .card.stat:last-child{margin-bottom:0}
  /* على الشاشات الصغيرة الكروت تنزل تحت الفورم بعرض كامل */
  @media (max-width:900px){
    .mat-layout{flex-direction:column}
    .mat-totals{position:static;width:100%}
  }
</style>

<div class="mat-layout">
<form method="POST" action="{{ route('materials.store') }}" class="mat-form">
  @csrf

  <div class="form-card" style="max-width:none;margin-bottom:16px">
    <div class="field" style="margin-bottom:0">
      <label>المشروع *</label>
      <select name="project_id" id="project_select" required onchange="loadBandsForProject(this.value); updateSupervisionDefault(this); checkProjectLock(this)">
        <option value="">— اختر المشروع —</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" data-sup="{{ $p->default_supervision_pct > 0 ? $p->default_supervision_pct : $settings->default_supervision_pct }}" data-locked="{{ $p->hasWholeProjectInstallmentContract() ? 1 : 0 }}" {{ $selectedProject?->id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div id="wholeContractBanner" class="flash error" style="display:none">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>تم تقسيط هذا المشروع بالكامل — لا يمكن شراء خامات جديدة له.</div>
  </div>

  <div id="groups-container"></div>

  <button type="button" id="addGroupBtn" class="btn ghost" style="margin-bottom:20px" onclick="addGroup()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    إضافة بند آخر
  </button>

  <div class="btn-row">
    <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ كل الأصناف</button>
    <a href="{{ route('materials.index') }}" class="btn ghost">إلغاء</a>
  </div>
</form>

{{-- Live totals across every item — updates as you type (عمود جانبي شيك) --}}
<aside class="mat-totals">
  <div class="section-label">الإجماليات</div>
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

{{-- Shared item/unit suggestions used inside every item row --}}
<datalist id="items-list">
  <option value="أسمنت"><option value="رمل"><option value="سيراميك أرضيات">
  <option value="سيراميك حوائط"><option value="مواسير PPR"><option value="دهانات بلاستيك">
</datalist>
<datalist id="units-list">
  <option value="شيكارة"><option value="م²"><option value="نقلة">
  <option value="ماسورة"><option value="لفة"><option value="طقم"><option value="طوبة">
  <option value="بستلة"><option value="وحدة">
</datalist>

@push('scripts')
<script>
// Bands of the currently selected project — refreshed whenever the project changes
let bandsList = @json($bands);
let groupCounter = 0;

// مشروع اتقسّط بالكامل يقفل الفورم كله (مفيش خامات جديدة تتضاف له)
function checkProjectLock(sel) {
  const opt = sel.options[sel.selectedIndex];
  const locked = opt?.dataset.locked === '1';
  document.getElementById('groups-container').style.display = locked ? 'none' : '';
  document.getElementById('addGroupBtn').disabled = locked;
  document.getElementById('wholeContractBanner').style.display = locked ? 'flex' : 'none';
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

// Supplier <option> list is static (suppliers aren't tied to a project), built once
const supplierOptionsHtml = `
  <option value="">— بدون مورد —</option>
  @foreach($suppliers as $s)
    <option value="{{ $s->id }}">{{ $s->name }}</option>
  @endforeach
`;

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
    <div class="item-row">
      <div class="item-row-grid">
        <div class="irf" style="flex:2.2">
          <label>الصنف *</label>
          <input type="text" name="groups[${g}][items][${i}][item]" placeholder="أسمنت، سيراميك..." required list="items-list">
        </div>
        <div class="irf" style="flex:1.6">
          <label>المورد</label>
          <select name="groups[${g}][items][${i}][supplier_id]">${supplierOptionsHtml}</select>
        </div>
        <div class="irf" style="flex:.9">
          <label>الوحدة</label>
          <input type="text" name="groups[${g}][items][${i}][unit]" value="وحدة" required list="units-list">
        </div>
        <div class="irf" style="flex:.9">
          <label>الكمية</label>
          <input type="number" name="groups[${g}][items][${i}][qty]" class="mat-qty" placeholder="0" min="0" step="0.1" required oninput="recalcTotals()">
        </div>
        <div class="irf" style="flex:1.3">
          <label>سعر الشراء</label>
          <input type="number" name="groups[${g}][items][${i}][unit_price]" class="mat-cost" placeholder="0.00" min="0" step="0.01" required oninput="recalcTotals()">
        </div>
        <div class="irf" style="flex:1.3">
          <label>سعر البيع</label>
          <input type="number" name="groups[${g}][items][${i}][sell_price]" class="mat-sell" placeholder="0.00" min="0" step="0.01" required oninput="recalcTotals()">
        </div>
        <div class="irf" style="flex:.8">
          <label>إشراف %</label>
          <input type="number" name="groups[${g}][items][${i}][supervision_pct]" class="mat-sup" placeholder="0" min="0" max="100" step="0.1" value="${defaultSupervisionPct}" oninput="this.dataset.touched='1'; recalcTotals()">
        </div>
        <button type="button" class="btn ghost sm ir-del" onclick="this.closest('.item-row').remove(); recalcTotals()" title="حذف">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
        </button>
      </div>
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
    purchase += qty * cost;
    sell     += qty * sp;
    client   += qty * sp * (1 + pct / 100);
  });

  const priceDiff = sell - purchase;   // فرق السعر
  const supMarkup = client - sell;     // ربح الإشراف
  const profit    = client - purchase; // إجمالي الربح

  const fmt = n => Math.round(n).toLocaleString('en-US');
  setTxt('tot-purchase', fmt(purchase));
  setTxt('tot-sell',     fmt(sell));
  setTxt('tot-client',   fmt(client));
  setTxt('tot-profit',   fmt(profit));
  setTxt('tot-pricediff', fmt(priceDiff));
  setTxt('tot-sup',       fmt(supMarkup));
}
function setTxt(id, v) { const el = document.getElementById(id); if (el) el.textContent = v; }

// Add a new band group, pre-filled with one item row
function addGroup() {
  const g = groupCounter++;
  const today = new Date().toISOString().slice(0, 10);
  const html = `
    <div class="band-group" data-group="${g}">
      <div class="band-group-head">
        <span class="lbl">بند رقم ${g + 1}</span>
        <button type="button" class="btn ghost sm" onclick="this.closest('.band-group').remove()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
          حذف البند
        </button>
      </div>
      <div class="row2" style="margin-bottom:12px">
        <div class="field">
          <label>البند</label>
          <select name="groups[${g}][band_id]" class="band-select">${bandOptionsHtml()}</select>
        </div>
        <div class="field">
          <label>تاريخ الشراء *</label>
          <input type="date" name="groups[${g}][date]" value="${today}" required>
        </div>
      </div>
      <p class="muted" style="margin:0 0 8px;font-size:12px">كل صنف تقدر تختاره من مورد مختلف — المورد بقى جوه صف الصنف نفسه.</p>
      <div class="items-container" id="items-${g}"></div>
      <button type="button" class="btn ghost sm" style="margin:6px 0 14px" onclick="addItem(${g})">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة صنف
      </button>
      {{-- طريقة الدفع — آخر حاجة في كل مجموعة --}}
      <div class="pay-section-box">
        <div class="pay-section-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-wallet"/></svg>
          طريقة دفع هذا الشراء
        </div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="radio" name="groups[${g}][payment_status]" value="paid" checked onchange="togglePaidAmt(${g}, this.value)">
            <span>دفع بالكامل</span>
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="radio" name="groups[${g}][payment_status]" value="partial" onchange="togglePaidAmt(${g}, this.value)">
            <span>جزئي (دفع جزء + باقي دين)</span>
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="radio" name="groups[${g}][payment_status]" value="deferred" onchange="togglePaidAmt(${g}, this.value)">
            <span>آجل بالكامل (دين)</span>
          </label>
        </div>
        <div class="row2" style="align-items:flex-end">
          <div class="field" style="margin:0" id="wallet-row-${g}">
            <label style="font-weight:600">المحفظة (الصرف منها) *</label>
            <select name="groups[${g}][account_id]" id="wallet-${g}" required>${walletOptionsHtml}</select>
          </div>
          <div id="paid-amt-row-${g}" style="display:none">
            <div class="field" style="margin:0">
              <label>المبلغ المدفوع الآن (ج.م) *</label>
              <input type="number" name="groups[${g}][paid_amount]" id="paid-amt-${g}" min="0" step="0.01" placeholder="0">
            </div>
          </div>
        </div>
      </div>
    </div>`;
  document.getElementById('groups-container').insertAdjacentHTML('beforeend', html);
  addItem(g);
}

function togglePaidAmt(g, val) {
  const row = document.getElementById('paid-amt-row-' + g);
  const inp = document.getElementById('paid-amt-' + g);
  if (val === 'partial') {
    row.style.display = 'block';
    inp.required = true;
  } else {
    row.style.display = 'none';
    inp.required = false;
    inp.value = '';
  }

  // آجل بالكامل = مفيش فلوس بتتصرف دلوقتي، فمفيش داعي تختار محفظة
  const walletRow = document.getElementById('wallet-row-' + g);
  const walletSel = document.getElementById('wallet-' + g);
  if (val === 'deferred') {
    walletRow.style.display = 'none';
    walletSel.required = false;
    walletSel.selectedIndex = 0;
  } else {
    walletRow.style.display = 'block';
    walletSel.required = true;
  }
}

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
