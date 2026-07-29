@extends('layouts.app')
@section('title', 'نثريات ومصروفات')
@section('page-title', 'نثريات ومصروفات — ' . $project->name)

@section('content')
<div class="page-head">
  <div><h3>إضافة نثريات ومصروفات</h3><p>{{ $project->name }} — إكرامية، نقل، إفطار... (بيتحاسب على العميل زي الخامة)</p></div>
  <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
</div>

@include('partials._errors')

<form method="POST" action="{{ route('expenses.store', $project) }}" style="max-width:640px">
  @csrf
  <div class="form-card">
    <div class="row2">
      <div class="field">
        <label>البند</label>
        <select name="band_id">
          <option value="">— بند عام (بدون بند) —</option>
          @foreach($bands as $b)
            <option value="{{ $b->id }}" {{ old('band_id', $activeBand?->id) == $b->id ? 'selected' : '' }}>{{ $b->name }}{{ $b->status === 'active' ? ' (جاري حاليًا)' : '' }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>البيان *</label>
        <input type="text" name="item" value="{{ old('item') }}" placeholder="نقل / إكرامية / إفطار العمال" required list="misc-list">
        <datalist id="misc-list">
          <option value="نقل"><option value="إكرامية"><option value="إفطار العمال"><option value="مواصلات"><option value="نثريات">
        </datalist>
      </div>
    </div>
    <div style="margin-top:10px; margin-bottom:10px;">
      <label style="display:block; font-size:0.85rem; font-weight:600; color:var(--text-muted); margin-bottom:6px;">نوع الجهة *</label>
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="radio" name="party_type" value="supplier" checked onchange="togglePartyList()">
          <span>مورد</span>
        </label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="radio" name="party_type" value="craftsman" onchange="togglePartyList()">
          <span>صنايعي</span>
        </label>
      </div>
    </div>
    <div class="field" style="margin-bottom:10px;">
      <label>اسم المورد / الصنايعي *</label>
      <input type="text" id="supplier-name-input" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="ابحث أو اكتب الاسم..." required list="suppliers-list">
      
      <datalist id="suppliers-list">
        @foreach($supplierNames as $sup)
          <option value="{{ $sup }}">
        @endforeach
      </datalist>

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
    <div class="field">
      <label>التاريخ *</label>
      <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
    </div>
    {{-- طريقة الدفع --}}
    <div style="background:var(--bg);border-radius:8px;padding:14px 16px;margin-bottom:14px">
      <div style="margin-bottom:10px;font-size:.85rem;font-weight:600;color:var(--text-muted)">طريقة الدفع</div>
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="radio" name="payment_type" value="immediate" checked onchange="toggleExpenseWallet(this.value)">
          <span>دفع فوري</span>
        </label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="radio" name="payment_type" value="deferred" onchange="toggleExpenseWallet(this.value)">
          <span>آجل (يُسجّل بدون خصم من المحفظة)</span>
        </label>
      </div>
      <div id="expense-wallet-row">
        @include('partials._wallet-select', ['wallets' => $wallets, 'label' => 'المحفظة (الصرف منها) *', 'required' => true, 'selectStyle' => 'width:100%'])
      </div>
    </div>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ المصروف</button>
      <a href="{{ route('projects.show', $project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

@push('scripts')
<script>
function syncSell() {
  const sell = document.getElementById('sell-field');
  if (sell.dataset.touched === '1') return;
  sell.value = document.getElementById('amount-field').value;
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

function togglePartyList() {
  const type = document.querySelector('input[name="party_type"]:checked').value;
  const input = document.getElementById('supplier-name-input');
  if (type === 'supplier') {
      input.setAttribute('list', 'suppliers-list');
  } else {
      input.setAttribute('list', 'craftsmen-list');
  }
}

updateExpenseUI();
toggleExpenseWallet(document.querySelector('input[name="payment_type"]:checked').value);
</script>
@endpush
@endsection
