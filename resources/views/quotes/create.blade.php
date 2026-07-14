@extends('layouts.app')
@section('title', 'عرض سعر جديد')
@section('page-title', 'عرض سعر جديد')

@section('content')
<div class="page-head"><div><h3>عرض سعر جديد</h3></div><a href="{{ route('quotes.index') }}" class="btn ghost">رجوع</a></div>
<form method="POST" action="{{ route('quotes.store') }}">
  @csrf
  <div class="form-card">
    <div class="row2">
      <div class="field"><label>رقم المرجع *</label><input type="text" name="ref" value="{{ old('ref', $nextRef) }}" required></div>
      <div class="field">
        <label>الحالة</label>
        <select name="status">
          <option value="draft">قيد المراجعة</option>
          <option value="sent">تم الإرسال</option>
          <option value="approved">معتمد</option>
        </select>
      </div>
    </div>
    <div class="row2">
      <div class="field">
        <label>العميل *</label>
        <select name="client_id" required>
          <option value="">— اختر العميل —</option>
          @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}@if($c->phone) — {{ $c->phone }}@endif</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
      </div>
    </div>
    <div class="row2">
      <div class="field"><label>العنوان</label><input type="text" name="address" value="{{ old('address') }}"></div>
      <div class="field"><label>المساحة (م²)</label><input type="number" name="area" value="{{ old('area') }}" min="0" step="0.5"></div>
    </div>

    {{-- Band line items, each optionally broken down into itemized rows --}}
    <div class="section-label" style="margin-top:10px">البنود (بنود العمل)</div>
    <div id="bands-list"></div>
    <button type="button" class="btn ghost sm" style="margin-bottom:18px" onclick="addBand()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><use href="#i-plus"/></svg>
      إضافة بند
    </button>

    <div class="field"><label>ملاحظات</label><textarea name="note" rows="2" placeholder="أي ملاحظات خاصة...">{{ old('note') }}</textarea></div>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ العرض</button>
      <a href="{{ route('quotes.index') }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

<datalist id="band-names-list">
  @foreach($bandNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>

@push('scripts')
<script>
let bandIdx = 0;

function bandRowHtml(g) {
  return `
    <div class="band-card" data-band="${g}">
      <div class="band-card-head">
        <div class="field" style="margin:0;flex:1">
          <label>اسم البند *</label>
          <input type="text" name="bands[${g}][name]" placeholder="محارة / سيراميك / دهانات..." required list="band-names-list">
        </div>
        <div class="field" style="margin:0;width:200px">
          <label>إجمالي البند للعميل (ج.م)</label>
          <input type="number" name="bands[${g}][price]" class="band-price" placeholder="0.00" min="0" step="0.01" style="font-weight:800;color:var(--accent-ink)">
          <small style="color:var(--pos);font-size:.68rem">يُحسب تلقائياً من الأصناف</small>
        </div>
        <button type="button" class="btn ghost sm danger" onclick="this.closest('.band-card').remove()" style="align-self:flex-start;margin-top:22px">حذف البند</button>
      </div>
      <div class="band-items" id="band-items-${g}"></div>
      <button type="button" class="btn ghost sm" style="margin-top:6px" onclick="addItem(${g})">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة صنف
      </button>

      <div class="band-workers" id="band-workers-${g}" style="margin-top:14px"></div>
      <button type="button" class="btn ghost sm" style="margin-top:6px" onclick="addWorker(${g})">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة فني/مصنعية
      </button>
    </div>`;
}

function workerRowHtml(g, w) {
  return `
    <div class="worker-row" data-worker="${w}" style="border:1px solid var(--line);border-radius:8px;padding:10px;margin:8px 0;background:#fafafa">
      <div class="row2">
        <div class="field" style="margin:0"><input type="text" name="bands[${g}][workers][${w}][name]" placeholder="اسم الفني *" required></div>
        <div class="field" style="margin:0"><input type="text" name="bands[${g}][workers][${w}][specialty]" placeholder="التخصص (اختياري) مثل: كهربائي"></div>
      </div>
      <div class="field" style="margin:0 0 8px">
        <select name="bands[${g}][workers][${w}][contract_type]" class="worker-contract-type" onchange="toggleWorkerQtyWrap(${g},${w},this.value)">
          <option value="">— نوع التعاقد —</option>
          <option value="lump_sum">مقاولة مقطوعة</option>
          <option value="per_meter">بالمتر</option>
          <option value="per_piece">بالقطعة</option>
          <option value="daily">يومية</option>
        </select>
      </div>
      <div class="worker-qty-wrap row2" style="margin-top:0;display:none">
        <div class="field" style="margin:0"><input type="number" name="bands[${g}][workers][${w}][contract_qty]" class="worker-qty" placeholder="الكمية (متر/قطعة/يوم)" min="0" step="0.01" oninput="recalcWorker(${g},${w})"></div>
        <div class="field" style="margin:0;display:none"><input type="number" name="bands[${g}][workers][${w}][contract_unit_rate]" class="worker-rate" placeholder="سعر الوحدة (تكلفة)" min="0" step="0.01" oninput="recalcWorker(${g},${w})"></div>
        <div class="field" style="margin:0"><input type="number" name="bands[${g}][workers][${w}][sell_rate]" class="worker-sell-rate" placeholder="سعر الوحدة للعميل" min="0" step="0.01" oninput="recalcWorker(${g},${w})"></div>
      </div>
      <div class="row2" style="margin-top:8px">
        <div class="field" style="margin:0;display:none"><input type="number" name="bands[${g}][workers][${w}][amount]" class="worker-amount" placeholder="الأجر الإجمالي (تكلفة)" min="0" step="0.01" oninput="this.dataset.touched='1'; recalcBandPrice(${g})"></div>
        <div class="field" style="margin:0"><input type="number" name="bands[${g}][workers][${w}][sell_amount]" class="worker-sell-amount" placeholder="سعره الإجمالي للعميل" min="0" step="0.01" oninput="this.dataset.touched='1'; recalcBandPrice(${g})"></div>
      </div>
      <div class="row2" style="margin-top:8px;align-items:end">
        <div class="field" style="margin:0">
          <label style="color:#7c3aed">إشراف %</label>
          <input type="number" name="bands[${g}][workers][${w}][supervision_pct]" class="worker-sup" placeholder="0" min="0" max="100" step="0.1" value="{{ $settings->default_supervision_pct }}" oninput="recalcBandPrice(${g})" style="border-color:#c4b5fd">
        </div>
        <button type="button" class="btn ghost sm danger" onclick="this.closest('.worker-row').remove(); recalcBandPrice(${g})">حذف الفني</button>
      </div>
    </div>`;
}

// "مقاولة مقطوعة" = أجر إجمالي ثابت، مفيش كمية ولا سعر وحدة أصلاً — فاخفِ
// حقول الكمية/السعر خالص لحد ما يختار نوع تعاقد بالكمية (متر/قطعة/يوم)
function toggleWorkerQtyWrap(g, w, type) {
  const row = document.querySelector(`.band-card[data-band="${g}"] .worker-row[data-worker="${w}"]`);
  if (!row) return;
  const show = (type === 'per_meter' || type === 'per_piece' || type === 'daily');
  row.querySelectorAll('.worker-qty-wrap').forEach(wrap => { wrap.style.display = show ? '' : 'none'; });
}

// qty × rate يملي الأجر/سعر العميل تلقائيًا كبداية — لحد ما المستخدم يعدّل
// المبلغ بإيده مباشرة (data-touched)، زي نفس فكرة recalcWorkerAmounts في
// شاشات البنود الحقيقية (bands/_contract-scripts.blade.php)
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

function addWorker(g) {
  const container = document.getElementById('band-workers-' + g);
  const w = container.children.length;
  container.insertAdjacentHTML('beforeend', workerRowHtml(g, w));
  recalcBandPrice(g);
}

function itemRowHtml(g, i) {
  return `
    <div class="item-row" data-item="${i}">
      <div class="item-row-grid">
        <div class="irf" style="flex:2.5">
          <label>اسم الصنف *</label>
          <input type="text" name="bands[${g}][items][${i}][name]" placeholder="بلاط / جبس / دهان..." required oninput="recalcBandPrice(${g})">
        </div>
        <div class="irf" style="flex:1">
          <label>الكمية</label>
          <input type="number" name="bands[${g}][items][${i}][qty]" placeholder="0" min="0" step="0.01" value="1" required oninput="recalcBandPrice(${g})">
        </div>
        <div class="irf" style="flex:1.4">
          <label class="price-sell">سعر البيع للوحدة</label>
          <input type="number" name="bands[${g}][items][${i}][unit_price]" class="price-sell" placeholder="0.00" min="0" step="0.01" required oninput="recalcBandPrice(${g})">
        </div>
        <div class="irf" style="flex:1">
          <label style="color:#7c3aed">إشراف %</label>
          <input type="number" name="bands[${g}][items][${i}][supervision_pct]" placeholder="0" min="0" max="100" step="0.1" value="{{ $settings->default_supervision_pct }}" oninput="recalcBandPrice(${g})" style="border-color:#c4b5fd">
        </div>
        <button type="button" class="btn ghost sm ir-del" onclick="this.closest('.item-row').remove(); recalcBandPrice(${g})" title="حذف">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
        </button>
      </div>
    </div>`;
}

function addItem(g) {
  const container = document.getElementById('band-items-' + g);
  const i = container.querySelectorAll('.item-row').length;
  if (i === 0 && !container.querySelector('.items-header-row')) {
    container.insertAdjacentHTML('beforeend', `
      <div class="items-header-row">
        <span style="flex:2.5">اسم الصنف</span>
        <span style="flex:1">الكمية</span>
        <span style="flex:1.4" class="price-sell">سعر البيع للوحدة</span>
        <span style="flex:1;color:#7c3aed">إشراف %</span>
        <span style="width:34px"></span>
      </div>`);
  }
  container.insertAdjacentHTML('beforeend', itemRowHtml(g, i));
  recalcBandPrice(g);
}

// The band's total price is auto-computed from its items + workers whenever
// any exist — stays manually editable only for a fully "سعر مقطوع" band
function recalcBandPrice(g) {
  const card = document.querySelector(`.band-card[data-band="${g}"]`);
  const items = card.querySelectorAll('.item-row');
  const workers = card.querySelectorAll('.worker-row');
  const priceField = card.querySelector('.band-price');

  if (items.length === 0 && workers.length === 0) {
    priceField.readOnly = false;
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
}

function addBand() {
  const g = bandIdx++;
  document.getElementById('bands-list').insertAdjacentHTML('beforeend', bandRowHtml(g));
}

// Start with one empty band ready to fill in
addBand();
</script>
@endpush
@endsection
