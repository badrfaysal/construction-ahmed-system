@extends('layouts.app')
@section('title', 'بند فرعي جديد')
@section('page-title', 'بند فرعي جديد — ' . $project->name)

@section('content')
<div class="page-head">
  <div><h3>إضافة بند فرعي جديد</h3><p>{{ $project->name }} — مصنعيات أو خامات تحت البند الرئيسي</p></div>
  <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
</div>

@include('partials._errors')

<form method="POST" action="{{ route('expenses.store', $project) }}" style="max-width:720px" id="sub-band-form">
  @csrf
  <div class="form-card">
    <div class="row2">
      <div class="field">
        <label>البند الرئيسي (اختياري)</label>
        <select name="band_id">
          <option value="">— عام للمشروع ككل (بدون بند) —</option>
          @foreach($bands as $b)
            <option value="{{ $b->id }}" {{ old('band_id', $activeBand?->id) == $b->id ? 'selected' : '' }}>{{ $b->name }}{{ $b->status === 'active' ? ' (جاري حاليًا)' : '' }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>اسم البند الفرعي *</label>
        <input type="text" name="item" value="{{ old('item') }}" placeholder="مثال: تنظيف، شد شبك، دهانات..." required>
      </div>
    </div>

    {{-- نوع الجهة --}}
    <div style="margin-top:10px; margin-bottom:16px;">
      <label style="display:block; font-size:0.85rem; font-weight:600; color:var(--text-muted); margin-bottom:8px;">نوع البند الفرعي *</label>
      <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 18px;border:2px solid #e2e8f0;border-radius:10px;transition:all 0.2s;" class="party-option" data-value="craftsman">
          <input type="radio" name="party_type" value="craftsman" {{ old('party_type', 'craftsman') === 'craftsman' ? 'checked' : '' }} onchange="switchPartyType(this.value)">
          <div>
            <div style="font-weight:700;font-size:0.9rem">مصنعية (صنايعي)</div>
            <div style="font-size:0.75rem;color:var(--ink-3)">مقاولة / بالمتر / بالقطعة</div>
          </div>
        </label>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 18px;border:2px solid #e2e8f0;border-radius:10px;transition:all 0.2s;" class="party-option" data-value="supplier">
          <input type="radio" name="party_type" value="supplier" {{ old('party_type') === 'supplier' ? 'checked' : '' }} onchange="switchPartyType(this.value)">
          <div>
            <div style="font-weight:700;font-size:0.9rem">خامات (مورد)</div>
            <div style="font-size:0.75rem;color:var(--ink-3)">أصناف متعددة كفاتورة</div>
          </div>
        </label>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 18px;border:2px solid #e2e8f0;border-radius:10px;transition:all 0.2s;" class="party-option" data-value="general">
          <input type="radio" name="party_type" value="general" {{ old('party_type') === 'general' ? 'checked' : '' }} onchange="switchPartyType(this.value)">
          <div>
            <div style="font-weight:700;font-size:0.9rem">مصروف عام</div>
            <div style="font-size:0.75rem;color:var(--ink-3)">بدون مورد أو فني</div>
          </div>
        </label>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- قسم المصنعية (صنايعي) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div id="craftsman-section">
      <div class="field" style="margin-bottom:10px;">
        <label>اسم الصنايعي *</label>
        <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="ابحث أو اكتب الاسم..." list="craftsmen-list" id="craftsman-name-input">
        <datalist id="craftsmen-list">
          @foreach($craftsmenNames as $craftsman)
            <option value="{{ $craftsman }}">
          @endforeach
        </datalist>
      </div>
      <div class="row2">
        <div class="field">
          <label>طريقة المحاسبة</label>
          <select name="contract_type" id="expense-contract-select" onchange="updateExpenseUI()">
            <option value="lump_sum" {{ old('contract_type') === 'lump_sum' ? 'selected' : '' }}>مبلغ مقطوع</option>
            <option value="per_meter" {{ old('contract_type') === 'per_meter' ? 'selected' : '' }}>بالمتر</option>
            <option value="per_piece" {{ old('contract_type') === 'per_piece' ? 'selected' : '' }}>بالقطعة</option>
          </select>
        </div>
        <div class="field" id="expense-qty-wrap" style="{{ in_array(old('contract_type'), ['per_meter', 'per_piece']) ? '' : 'display:none;' }}">
          <label id="expense-qty-label">الكمية</label>
          <input type="number" name="qty" value="{{ old('qty') }}" min="0" step="0.01">
        </div>
      </div>
      <div class="row3">
        <div class="field">
          <label id="expense-cost-label">التكلفة *</label>
          <input type="number" id="amount-field" name="amount" value="{{ old('amount') }}" min="0" step="0.01" required oninput="syncSell()">
        </div>
        <div class="field">
          <label id="expense-sell-label">سعر البيع للعميل *</label>
          <input type="number" id="sell-field" name="sell_price" value="{{ old('sell_price') }}" min="0" step="0.01" required oninput="this.dataset.touched='1'">
        </div>
        <div class="field">
          <label>نسبة الإشراف %</label>
          <input type="number" name="supervision_pct" value="{{ old('supervision_pct', $defaultSup) }}" min="0" max="100" step="0.1">
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- قسم الخامات (مورد) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div id="supplier-section" style="display:none;">
      <div class="field" style="margin-bottom:10px;">
        <label>اسم المورد *</label>
        <input type="text" name="sup_supplier_name" value="{{ old('sup_supplier_name') }}" placeholder="ابحث أو اكتب الاسم..." list="suppliers-list" id="supplier-name-input-sec">
        <datalist id="suppliers-list">
          @foreach($supplierNames as $sup)
            <option value="{{ $sup }}">
          @endforeach
        </datalist>
      </div>

      <div style="margin-bottom:8px;font-size:.85rem;font-weight:700;color:var(--ink-2)">أصناف الخامات</div>
      <div id="sup-items-container">
        {{-- سيتم إنشاؤه بالجافاسكربت --}}
      </div>
      <button type="button" class="btn ghost sm" onclick="addSupplierItem()" style="margin-top:8px;margin-bottom:12px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><use href="#i-plus"/></svg>
        إضافة صنف آخر
      </button>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- قسم المصروف العام (بدون جهة) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div id="general-section" style="display:none;">
      <div class="row3">
        <div class="field">
          <label>المبلغ (التكلفة) *</label>
          <input type="number" id="gen-amount-field" name="amount" value="{{ old('amount') }}" min="0" step="0.01" required oninput="syncGenSell()">
        </div>
        <div class="field">
          <label>سعر البيع للعميل *</label>
          <input type="number" id="gen-sell-field" name="sell_price" value="{{ old('sell_price') }}" min="0" step="0.01" required oninput="this.dataset.touched='1'">
        </div>
        <div class="field">
          <label>نسبة الإشراف %</label>
          <input type="number" name="supervision_pct" value="{{ old('supervision_pct', $defaultSup) }}" min="0" max="100" step="0.1">
        </div>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- مشترك: التاريخ وطريقة الدفع والملاحظات --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="field">
      <label>التاريخ *</label>
      <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
    </div>

    {{-- طريقة الدفع --}}
    <div style="background:var(--bg);border-radius:8px;padding:14px 16px;margin-bottom:14px">
      <div style="margin-bottom:10px;font-size:.85rem;font-weight:600;color:var(--text-muted)">طريقة الدفع</div>
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="radio" name="payment_type" value="immediate" onchange="toggleExpenseWallet(this.value)">
          <span>نقدي فوراً (من الخزنة/المحفظة)</span>
        </label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="radio" name="payment_type" value="deferred" checked onchange="toggleExpenseWallet(this.value)">
          <span>آجل (تُسجل في مستحقات الجهة)</span>
        </label>
      </div>
      <div id="expense-wallet-row">
        @include('partials._wallet-select', ['wallets' => $wallets, 'label' => 'المحفظة (الصرف منها) *', 'required' => true, 'selectStyle' => 'width:100%'])
      </div>
    </div>
    <div class="field" style="margin-bottom:14px">
      <label>ملاحظات (تظهر في كشف الحساب)</label>
      <input type="text" name="notes" value="{{ old('notes') }}" placeholder="أي تفاصيل إضافية...">
    </div>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ البند الفرعي</button>
      <a href="{{ route('projects.show', $project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

<style>
  .party-option { transition: all 0.2s; }
  .party-option:has(input:checked) { border-color: var(--accent) !important; background: var(--accent-soft, #eff6ff); }
  .sup-item-row {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 10px; position: relative;
  }
  .sup-item-row .remove-item {
    position: absolute; top: 8px; left: 8px; background: none; border: none; color: var(--neg, #ef4444); cursor: pointer; padding: 4px; border-radius: 6px; transition: background 0.15s;
  }
  .sup-item-row .remove-item:hover { background: rgba(239,68,68,0.1); }
</style>

@push('scripts')
<script>
let supItemIndex = 0;

function switchPartyType(type) {
  const craftsmanSec = document.getElementById('craftsman-section');
  const supplierSec = document.getElementById('supplier-section');
  const generalSec = document.getElementById('general-section');
  
  if (type === 'craftsman') {
    craftsmanSec.style.display = '';
    supplierSec.style.display = 'none';
    generalSec.style.display = 'none';
    
    craftsmanSec.querySelectorAll('input, select').forEach(el => el.disabled = false);
    supplierSec.querySelectorAll('input, select').forEach(el => el.disabled = true);
    generalSec.querySelectorAll('input, select').forEach(el => el.disabled = true);
  } else if (type === 'supplier') {
    craftsmanSec.style.display = 'none';
    supplierSec.style.display = '';
    generalSec.style.display = 'none';
    
    craftsmanSec.querySelectorAll('input, select').forEach(el => el.disabled = true);
    supplierSec.querySelectorAll('input, select').forEach(el => el.disabled = false);
    generalSec.querySelectorAll('input, select').forEach(el => el.disabled = true);
    
    if (document.querySelectorAll('.sup-item-row').length === 0) {
      addSupplierItem();
    }
  } else if (type === 'general') {
    craftsmanSec.style.display = 'none';
    supplierSec.style.display = 'none';
    generalSec.style.display = '';
    
    craftsmanSec.querySelectorAll('input, select').forEach(el => el.disabled = true);
    supplierSec.querySelectorAll('input, select').forEach(el => el.disabled = true);
    generalSec.querySelectorAll('input, select').forEach(el => el.disabled = false);
  }
  // Highlight selected card
  document.querySelectorAll('.party-option').forEach(el => {
    el.style.borderColor = el.dataset.value === type ? 'var(--accent)' : '#e2e8f0';
    el.style.background = el.dataset.value === type ? 'var(--accent-soft, #eff6ff)' : '';
  });
}

function addSupplierItem() {
  const i = supItemIndex++;
  const container = document.getElementById('sup-items-container');
  const div = document.createElement('div');
  div.className = 'sup-item-row';
  div.innerHTML = `
    <button type="button" class="remove-item" onclick="this.parentElement.remove()" title="حذف الصنف">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    </button>
    <div class="row2" style="margin-bottom:8px">
      <div class="field" style="margin-bottom:0">
        <label style="font-size:0.8rem">اسم الصنف *</label>
        <input type="text" name="sup_items[${i}][item]" placeholder="رمل / أسمنت / حديد..." required list="item-names-list">
      </div>
      <div class="field" style="margin-bottom:0">
        <label style="font-size:0.8rem">الوحدة *</label>
        <input type="text" name="sup_items[${i}][unit]" placeholder="طن / متر / كيس..." required list="unit-names-list" style="max-width:140px">
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;">
      <div class="field" style="margin-bottom:0">
        <label style="font-size:0.8rem">الكمية *</label>
        <input type="number" name="sup_items[${i}][qty]" min="0.01" step="0.01" required placeholder="0">
      </div>
      <div class="field" style="margin-bottom:0">
        <label style="font-size:0.8rem">سعر الوحدة (تكلفة) *</label>
        <input type="number" name="sup_items[${i}][unit_price]" min="0" step="0.01" required placeholder="0.00">
      </div>
      <div class="field" style="margin-bottom:0">
        <label style="font-size:0.8rem">سعر البيع للعميل *</label>
        <input type="number" name="sup_items[${i}][sell_price]" min="0" step="0.01" required placeholder="0.00">
      </div>
      <div class="field" style="margin-bottom:0">
        <label style="font-size:0.8rem">إشراف %</label>
        <input type="number" name="sup_items[${i}][supervision_pct]" min="0" max="100" step="0.1" value="{{ $defaultSup }}">
      </div>
    </div>
  `;
  container.appendChild(div);
}

function syncSell() {
  const sell = document.getElementById('sell-field');
  if (sell.dataset.touched === '1') return;
  sell.value = document.getElementById('amount-field').value;
}

function syncGenSell() {
  const sell = document.getElementById('gen-sell-field');
  if (sell.dataset.touched === '1') return;
  sell.value = document.getElementById('gen-amount-field').value;
}

function updateExpenseUI() {
  const ctype = document.getElementById('expense-contract-select').value;
  const qtyWrap = document.getElementById('expense-qty-wrap');
  const costLabel = document.getElementById('expense-cost-label');
  const sellLabel = document.getElementById('expense-sell-label');
  
  if (ctype === 'lump_sum') {
    qtyWrap.style.display = 'none';
    costLabel.textContent = 'التكلفة (مقطوع) *';
    sellLabel.textContent = 'سعر البيع للعميل (مقطوع) *';
  } else {
    qtyWrap.style.display = 'block';
    const unit = ctype === 'per_meter' ? 'متر' : 'قطعة';
    document.getElementById('expense-qty-label').textContent = 'الكمية (' + unit + ')';
    costLabel.textContent = 'سعر ال' + unit + ' (تكلفة) *';
    sellLabel.textContent = 'سعر ال' + unit + ' للعميل *';
  }
}

function toggleExpenseWallet(val) {
  const row = document.getElementById('expense-wallet-row');
  const sel = row.querySelector('select[name="account_id"]');
  if (val === 'deferred') {
    row.style.display = 'none';
    if (sel) sel.required = false;
  } else {
    row.style.display = '';
    if (sel) sel.required = true;
  }
}

// Init
switchPartyType(document.querySelector('input[name="party_type"]:checked')?.value || 'craftsman');
updateExpenseUI();
toggleExpenseWallet(document.querySelector('input[name="payment_type"]:checked').value);
</script>

<datalist id="item-names-list">
  @foreach($itemNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>
<datalist id="unit-names-list">
  @foreach($unitNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>
@endpush
@endsection
