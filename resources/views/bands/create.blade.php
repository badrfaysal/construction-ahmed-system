@extends('layouts.app')
@section('title', 'بند جديد')
@section('page-title', 'إضافة بند — ' . $project->name)

@section('content')
<div class="page-head">
  <div><h3>بند جديد</h3><p>{{ $project->name }}</p></div>
  <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

<form method="POST" action="{{ route('projects.bands.store', $project) }}">
  @csrf
  <div class="form-card band-form-card">
    <div class="field">
      <label>اسم البند *</label>
      <input type="text" name="name" value="{{ old('name') }}" placeholder="محارة / سيراميك / دهانات..." required>
    </div>

    {{-- تابات لتقليل السكرول — كل قسم كبير في تابة لوحده --}}
    <div class="tabs" id="band-form-tabs" style="margin-top:18px">
      <button type="button" class="tab active" data-tab="workers" onclick="switchBandTab('workers')">
        المصنعية <span class="cnt" id="bt-workers-cnt">0</span>
      </button>
      <button type="button" class="tab" data-tab="materials" onclick="switchBandTab('materials')">
        الخامات والنثريات <span class="cnt" id="bt-items-cnt">0</span>
      </button>
    </div>

    <div class="band-tab-panel" data-band-panel="workers">
      <p class="muted" style="margin:12px 0 10px">أضف فني أو أكتر، كل واحد باسمه ورقم موبايله ونوع تعاقده وأجره.</p>
      <div id="workers-list"></div>
      <button type="button" class="btn ghost sm" style="margin:6px 0 4px" onclick="addWorker()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة فني
      </button>
    </div>

    <div class="band-tab-panel" data-band-panel="materials" style="display:none">
      <div class="section-label" style="margin-top:14px">الخامات — اختياري، لو هتشتري خامات مع بداية البند</div>
      <p class="muted" style="margin-bottom:6px">هتتضاف لسعر البند الكلي تلقائيًا زي المصنعية بالظبط.</p>

      <div id="band-materials-list"></div>
      <button type="button" class="btn ghost sm" style="margin:6px 0 18px" onclick="addBandMaterial()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة خامة
      </button>

      <div class="section-label" style="margin-top:10px">نثريات — اختياري (إكرامية / نقل / إفطار...)</div>
      <div id="band-misc-list"></div>
      <button type="button" class="btn ghost sm" style="margin:6px 0 18px" onclick="addBandMisc()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة نثرية
      </button>

      {{-- طريقة الدفع — بتظهر بس لو فيه خامات أو نثريات مُضافة --}}
      <div class="pay-section-box" id="band-pay-section" style="display:none">
        <div class="pay-section-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-wallet"/></svg>
          طريقة دفع الخامات والنثريات
        </div>
        <p class="muted" style="margin:0 0 10px;font-size:12px">المورد بقى يُختار لكل خامة لوحدها (جوه صف الصنف) — هنا بس تاريخ الشراء وطريقة الدفع.</p>
        <div class="field" style="max-width:260px;margin-bottom:12px">
          <label>تاريخ الشراء</label>
          <input type="date" name="purchase_date" value="{{ old('purchase_date', today()->format('Y-m-d')) }}">
        </div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="radio" name="payment_status" value="paid" checked onchange="toggleBandPay(this.value)">
            <span>دفع بالكامل</span>
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="radio" name="payment_status" value="partial" onchange="toggleBandPay(this.value)">
            <span>جزئي (دفع جزء + باقي دين)</span>
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
            <input type="radio" name="payment_status" value="deferred" onchange="toggleBandPay(this.value)">
            <span>آجل بالكامل (دين)</span>
          </label>
        </div>
        <div class="row2" style="align-items:flex-end">
          <div class="field" style="margin:0" id="band-wallet-row">
            <label style="font-weight:600">المحفظة (الصرف منها) *</label>
            <select name="account_id" id="band-wallet-select">
              <option value="" disabled selected>— اختر المحفظة —</option>
              @foreach($wallets->groupBy(fn($w) => $w->categoryAr()) as $cat => $grp)
                <optgroup label="{{ $cat }}">
                  @foreach($grp as $w)
                    <option value="{{ $w->id }}">{{ $w->account_name }}@if($w->id == \App\Models\Account::WALLET_ID) ★@endif — {{ \App\Support\Money::format($w->balance) }} ج</option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          </div>
          <div id="band-paid-amt-row" style="display:none">
            <div class="field" style="margin:0">
              <label>المبلغ المدفوع الآن (ج.م) *</label>
              <input type="number" name="paid_amount" id="band-paid-amt" min="0" step="0.01" placeholder="0">
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- الإجمالي الكلي للعميل — يتحدث تلقائيًا (مصنعية + خامات + نثريات)، وبيبان دايمًا مهما كانت التابة المفتوحة --}}
    <div class="band-total-box">
      <div class="band-total-lbl">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-coins"/></svg>
        الإجمالي الكلي للعميل (ج.م)
      </div>
      <input type="number" id="client-price-field" name="client_price" value="{{ old('client_price', 0) }}"
             min="0" step="0.01" oninput="this.dataset.touched='1'" required class="band-total-inp">
      <div class="band-total-hint">مصنعية + خامات + نثريات — يتملى تلقائيًا، يمكن تعديله يدويًا</div>
    </div>

    <div class="btn-row" style="margin-top:16px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('projects.show', $project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

<datalist id="items-list">
  <option value="أسمنت"><option value="رمل"><option value="سيراميك أرضيات">
  <option value="سيراميك حوائط"><option value="مواسير PPR"><option value="دهانات بلاستيك">
</datalist>
<datalist id="units-list">
  <option value="شيكارة"><option value="م²"><option value="نقلة">
  <option value="ماسورة"><option value="لفة"><option value="طقم"><option value="طوبة">
  <option value="بستلة"><option value="وحدة">
</datalist>
<datalist id="misc-list">
  <option value="نقل"><option value="إكرامية"><option value="إفطار العمال"><option value="مواصلات"><option value="نثريات">
</datalist>

@push('scripts')
@include('bands._contract-scripts', ['defaultSupervisionPct' => $project->defaultSupervisionPct()])
<script>
function switchBandTab(name) {
  document.querySelectorAll('#band-form-tabs .tab').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
  document.querySelectorAll('.band-tab-panel').forEach(p => p.style.display = p.dataset.bandPanel === name ? '' : 'none');
}

let workerIdx = 0;
function addWorker(prefill = null) {
  const g = workerIdx++;
  document.getElementById('workers-list').insertAdjacentHTML('beforeend', workerRowHtml(g));
  if (prefill) fillWorker(g, prefill);
  updateWorkerUI(document.querySelector(`.worker-row[data-worker="${g}"]`));
  updateBandTabCounts();
}

// لو الفورم رجع بعد فشل فاليديشن، رجّع نفس صفوف الفنيين اللي المستخدم كتبها
// بدل ما نبدأ بصف فاضي واحد ونضيع كل اللي كان مكتوب
@if(count(old('workers', [])))
  @foreach(old('workers') as $ow)
    addWorker(@json($ow));
  @endforeach
@else
  addWorker();
@endif

// ── خامات البند ──────────────────────────────────────────────────
const bandSupplierOptionsHtml = `
  <option value="">— بدون مورد —</option>
  @foreach($suppliers as $s)
    <option value="{{ $s->id }}">{{ $s->name }}</option>
  @endforeach
`;
let bmIdx = 0;
function bandMaterialRowHtml(i) {
  return `
    <div class="item-row">
      <div class="item-row-grid">
        <div class="irf">
          <label>الصنف *</label>
          <input type="text" name="materials[${i}][item]" placeholder="أسمنت، سيراميك..." required list="items-list" oninput="recalcBandItemsVisibility()">
        </div>
        <div class="irf">
          <label>المورد</label>
          <select name="materials[${i}][supplier_id]">${bandSupplierOptionsHtml}</select>
        </div>
        <div class="irf">
          <label>الوحدة</label>
          <input type="text" name="materials[${i}][unit]" value="وحدة" required list="units-list">
        </div>
        <div class="irf">
          <label>الكمية</label>
          <input type="number" name="materials[${i}][qty]" class="bm-qty" placeholder="0" min="0" step="0.1" required oninput="updateClientPrice()">
        </div>
        <div class="irf">
          <label>سعر الشراء</label>
          <input type="number" name="materials[${i}][unit_price]" class="bm-cost" placeholder="0.00" min="0" step="0.01" required oninput="updateClientPrice()">
        </div>
        <div class="irf">
          <label>سعر البيع</label>
          <input type="number" name="materials[${i}][sell_price]" class="bm-sell" placeholder="0.00" min="0" step="0.01" required oninput="updateClientPrice()">
        </div>
        <div class="irf">
          <label>إشراف %</label>
          <input type="number" name="materials[${i}][supervision_pct]" class="bm-sup" placeholder="0" min="0" max="100" step="0.1" value="{{ $project->defaultSupervisionPct() }}" oninput="updateClientPrice()">
        </div>
        <button type="button" class="btn ghost sm ir-del" onclick="this.closest('.item-row').remove(); recalcBandItemsVisibility(); updateClientPrice()" title="حذف">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
        </button>
      </div>
    </div>`;
}
function addBandMaterial(prefill = null) {
  const i = bmIdx++;
  document.getElementById('band-materials-list').insertAdjacentHTML('beforeend', bandMaterialRowHtml(i));
  if (prefill) {
    const row = document.getElementById('band-materials-list').lastElementChild;
    row.querySelector(`[name="materials[${i}][item]"]`).value = prefill.item || '';
    row.querySelector(`[name="materials[${i}][supplier_id]"]`).value = prefill.supplier_id || '';
    row.querySelector(`[name="materials[${i}][unit]"]`).value = prefill.unit || 'وحدة';
    row.querySelector('.bm-qty').value = prefill.qty || '';
    row.querySelector('.bm-cost').value = prefill.unit_price || '';
    row.querySelector('.bm-sell').value = prefill.sell_price || '';
    row.querySelector('.bm-sup').value = prefill.supervision_pct ?? {{ $project->defaultSupervisionPct() }};
  }
  recalcBandItemsVisibility();
}

// ── نثريات البند ─────────────────────────────────────────────────
let bmiscIdx = 0;
function bandMiscRowHtml(i) {
  return `
    <div class="item-row">
      <div class="item-row-grid">
        <div class="irf">
          <label>البيان *</label>
          <input type="text" name="misc[${i}][item]" placeholder="نقل / إكرامية / إفطار العمال" required list="misc-list" oninput="recalcBandItemsVisibility()">
        </div>
        <div class="irf">
          <label>المبلغ</label>
          <input type="number" name="misc[${i}][amount]" class="bmisc-cost" placeholder="0.00" min="0" step="0.01" required oninput="updateClientPrice()">
        </div>
        <div class="irf">
          <label>سعر البيع للعميل</label>
          <input type="number" name="misc[${i}][sell_price]" class="bmisc-sell" placeholder="0.00" min="0" step="0.01" required oninput="updateClientPrice()">
        </div>
        <div class="irf">
          <label>إشراف %</label>
          <input type="number" name="misc[${i}][supervision_pct]" class="bmisc-sup" placeholder="0" min="0" max="100" step="0.1" value="{{ $project->defaultSupervisionPct() }}" oninput="updateClientPrice()">
        </div>
        <button type="button" class="btn ghost sm ir-del" onclick="this.closest('.item-row').remove(); recalcBandItemsVisibility(); updateClientPrice()" title="حذف">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
        </button>
      </div>
    </div>`;
}
function addBandMisc(prefill = null) {
  const i = bmiscIdx++;
  document.getElementById('band-misc-list').insertAdjacentHTML('beforeend', bandMiscRowHtml(i));
  if (prefill) {
    const row = document.getElementById('band-misc-list').lastElementChild;
    row.querySelector(`[name="misc[${i}][item]"]`).value = prefill.item || '';
    row.querySelector('.bmisc-cost').value = prefill.amount || '';
    row.querySelector('.bmisc-sell').value = prefill.sell_price || '';
    row.querySelector('.bmisc-sup').value = prefill.supervision_pct ?? {{ $project->defaultSupervisionPct() }};
  }
  recalcBandItemsVisibility();
}

// إظهار/إخفاء عناوين الأعمدة + قسم طريقة الدفع حسب وجود صفوف فعلية
function recalcBandItemsVisibility() {
  const matCount = document.querySelectorAll('#band-materials-list .item-row').length;
  const miscCount = document.querySelectorAll('#band-misc-list .item-row').length;

  const walletSel = document.getElementById('band-wallet-select');
  if (matCount + miscCount > 0) {
    document.getElementById('band-pay-section').style.display = 'block';
    const status = document.querySelector('input[name="payment_status"]:checked')?.value;
    if (status !== 'deferred') walletSel.required = true;
  } else {
    document.getElementById('band-pay-section').style.display = 'none';
    walletSel.required = false;
  }
  updateBandTabCounts();
}

// عداد صغير جنب كل تابة — يوريك عدد الفنيين/الخامات والنثريات من غير ما تفتحها
function updateBandTabCounts() {
  document.getElementById('bt-workers-cnt').textContent = document.querySelectorAll('#workers-list .worker-row').length;
  document.getElementById('bt-items-cnt').textContent =
    document.querySelectorAll('#band-materials-list .item-row').length +
    document.querySelectorAll('#band-misc-list .item-row').length;
}

function toggleBandPay(val) {
  const row = document.getElementById('band-paid-amt-row');
  const inp = document.getElementById('band-paid-amt');
  if (val === 'partial') {
    row.style.display = 'block';
    inp.required = true;
  } else {
    row.style.display = 'none';
    inp.required = false;
    inp.value = '';
  }

  const walletRow = document.getElementById('band-wallet-row');
  const walletSel = document.getElementById('band-wallet-select');
  if (val === 'deferred') {
    walletRow.style.display = 'none';
    walletSel.required = false;
  } else {
    walletRow.style.display = 'block';
    walletSel.required = true;
  }
}

// بيستبدل updateClientPrice() المعرّفة في bands/_contract-scripts.blade.php —
// بدل ما تجمع المصنعية بس، بتجمع المصنعية + الخامات + النثريات مع بعض
function updateClientPrice() {
  const clientField = document.getElementById('client-price-field');
  if (! clientField || clientField.dataset.touched === '1') return;

  let laborTotal = 0;
  document.querySelectorAll('.worker-row').forEach(row => {
    const amount = parseFloat(row.querySelector('.final-amount').value) || 0;
    const sellAmount = parseFloat(row.querySelector('.sell-amount-field').value) || 0;
    const pct = parseFloat(row.querySelector('.supervision-pct').value) || 0;
    const base = sellAmount || amount;
    laborTotal += base * (1 + pct / 100);
  });

  let materialsTotal = 0;
  document.querySelectorAll('#band-materials-list .item-row').forEach(row => {
    const qty  = parseFloat(row.querySelector('.bm-qty')?.value)  || 0;
    const sell = parseFloat(row.querySelector('.bm-sell')?.value) || 0;
    const pct  = parseFloat(row.querySelector('.bm-sup')?.value)  || 0;
    materialsTotal += qty * sell * (1 + pct / 100);
  });

  let miscTotal = 0;
  document.querySelectorAll('#band-misc-list .item-row').forEach(row => {
    const sell = parseFloat(row.querySelector('.bmisc-sell')?.value) || 0;
    const pct  = parseFloat(row.querySelector('.bmisc-sup')?.value)  || 0;
    miscTotal += sell * (1 + pct / 100);
  });

  clientField.value = (laborTotal + materialsTotal + miscTotal).toFixed(2);
}

// رجّع صفوف الخامات/النثريات لو الفورم رجع بعد فشل فاليديشن
@if(count(old('materials', [])))
  @foreach(old('materials') as $om)
    addBandMaterial(@json($om));
  @endforeach
@endif
@if(count(old('misc', [])))
  @foreach(old('misc') as $omi)
    addBandMisc(@json($omi));
  @endforeach
@endif
@if(old('payment_status'))
  document.querySelector('input[name="payment_status"][value="{{ old('payment_status') }}"]').checked = true;
  toggleBandPay('{{ old('payment_status') }}');
@endif
recalcBandItemsVisibility();
updateClientPrice();
updateBandTabCounts();

{{-- لو الفورم رجع بعد فشل فاليديشن مرتبط بالخامات/النثريات/الدفع، افتح تابتهم
     مباشرة بدل ما يفضل المستخدم مش شايف مين البند اللي فيه المشكلة --}}
@if($errors->hasAny(['materials.*', 'misc.*', 'account_id', 'paid_amount', 'supplier_id', 'purchase_date', 'payment_status']) || count(old('materials', [])) || count(old('misc', [])))
  switchBandTab('materials');
@endif
</script>
@endpush
@endsection
