@extends('layouts.app')
@section('title', 'تعديل عرض سعر')
@section('page-title', 'تعديل عرض سعر: ' . $quote->ref)

@section('content')
<div class="page-head"><div><h3>تعديل عرض سعر</h3></div><a href="{{ route('quotes.show', $quote) }}" class="btn ghost">رجوع</a></div>
<form method="POST" action="{{ route('quotes.update', $quote) }}">
  @csrf
  @method('PUT')
  <div class="form-card">
    <div class="row2">
      <div class="field"><label>رقم المرجع *</label><input type="text" name="ref" value="{{ old('ref', $quote->ref) }}" required></div>
      <div class="field">
        <label>الحالة</label>
        <select name="status">
          <option value="draft" {{ old('status', $quote->status) === 'draft' ? 'selected' : '' }}>قيد المراجعة</option>
          <option value="sent" {{ old('status', $quote->status) === 'sent' ? 'selected' : '' }}>تم الإرسال</option>
          <option value="approved" {{ old('status', $quote->status) === 'approved' ? 'selected' : '' }}>معتمد</option>
        </select>
      </div>
    </div>
    <div class="row2">
      <div class="field">
        <label>العميل *</label>
        <select name="client_id" required>
          <option value="">— اختر العميل —</option>
          @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ old('client_id', $quote->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}@if($c->phone) — {{ $c->phone }}@endif</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ old('date', $quote->date->format('Y-m-d')) }}" required>
      </div>
    </div>
    <div class="row2">
      <div class="field"><label>العنوان</label><input type="text" name="address" value="{{ old('address', $quote->address) }}"></div>
      <div class="field"><label>المساحة (م²)</label><input type="number" name="area" value="{{ old('area', $quote->area) }}" min="0" step="0.5"></div>
    </div>

    {{-- Band line items, each optionally broken down into itemized rows --}}
    <div class="section-label" style="margin-top:10px">البنود (بنود العمل)</div>
    <div id="bands-list"></div>
    <button type="button" class="btn ghost sm" style="margin-bottom:18px" onclick="addBand()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><use href="#i-plus"/></svg>
      إضافة بند
    </button>

    <div class="field"><label>ملاحظات</label><textarea name="note" rows="2" placeholder="أي ملاحظات خاصة...">{{ old('note', $quote->note) }}</textarea></div>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ التعديلات</button>
      <a href="{{ route('quotes.show', $quote) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

<datalist id="band-names-list">
  @foreach($bandNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>

@php
  // Pre-computed in PHP (not inline in @json()) so Blade's directive parser
  // doesn't have to untangle nested fn() closures inside the expression.
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

function bandRowHtml(g) {
  return `
    <div class="band-card" data-band="${g}" style="border:1px solid var(--line);border-radius:10px;padding:12px;margin-bottom:10px">
      <div class="row2">
        <div class="field" style="margin:0"><input type="text" name="bands[${g}][name]" placeholder="اسم البند مثل: تشطيبات" required list="band-names-list"></div>
        <div class="field" style="margin:0"><input type="number" name="bands[${g}][price]" class="band-price" placeholder="السعر الإجمالي التقريبي (ج.م)" min="0" step="0.01"></div>
      </div>
      <div class="band-items" id="band-items-${g}"></div>
      <div class="band-workers" id="band-workers-${g}" style="margin-top:10px"></div>
      <div class="btn-row" style="margin-top:6px">
        <button type="button" class="btn ghost sm" onclick="addItem(${g})">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
          إضافة صنف تقريبي
        </button>
        <button type="button" class="btn ghost sm" onclick="addWorker(${g})">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
          إضافة فني/مصنعية
        </button>
        <button type="button" class="btn ghost sm danger" onclick="this.closest('.band-card').remove()">حذف البند</button>
      </div>
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

function fillWorker(g, w, worker) {
  const row = document.querySelector(`.band-card[data-band="${g}"] .worker-row[data-worker="${w}"]`);
  row.querySelector('[name*="[name]"]').value = worker.name || '';
  row.querySelector('[name*="[specialty]"]').value = worker.specialty || '';
  row.querySelector('[name*="[contract_type]"]').value = worker.contract_type || '';
  row.querySelector('.worker-qty').value = worker.contract_qty ?? '';
  row.querySelector('.worker-rate').value = worker.contract_unit_rate ?? '';
  row.querySelector('.worker-sell-rate').value = worker.sell_rate ?? '';
  const amountField = row.querySelector('.worker-amount');
  const sellField = row.querySelector('.worker-sell-amount');
  amountField.value = worker.amount ?? 0;
  sellField.value = worker.sell_amount ?? 0;
  // القيمة المحفوظة تفضل زي ما هي وقت الفتح، مش تتحسب من جديد من الكمية×السعر
  amountField.dataset.touched = '1';
  sellField.dataset.touched = '1';
  row.querySelector('.worker-sup').value = worker.supervision_pct ?? 0;
  toggleWorkerQtyWrap(g, w, worker.contract_type || '');
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

function addWorker(g, prefill = null) {
  const container = document.getElementById('band-workers-' + g);
  const w = container.children.length;
  container.insertAdjacentHTML('beforeend', workerRowHtml(g, w));
  if (prefill) fillWorker(g, w, prefill);
  recalcBandPrice(g);
}

function itemRowHtml(g, i) {
  return `
    <div class="row4 item-row" data-item="${i}" style="margin:6px 0;align-items:end">
      <div class="field" style="margin:0"><input type="text" name="bands[${g}][items][${i}][name]" placeholder="اسم الصنف" required oninput="recalcBandPrice(${g})"></div>
      <div class="field" style="margin:0"><input type="number" name="bands[${g}][items][${i}][qty]" placeholder="الكمية" min="0" step="0.01" value="1" required oninput="recalcBandPrice(${g})"></div>
      <div class="field" style="margin:0"><input type="number" name="bands[${g}][items][${i}][unit_price]" placeholder="السعر" min="0" step="0.01" required oninput="recalcBandPrice(${g})"></div>
      <div class="field" style="margin:0"><input type="number" name="bands[${g}][items][${i}][supervision_pct]" placeholder="نسبة الإشراف %" min="0" max="100" step="0.1" value="{{ $settings->default_supervision_pct }}" oninput="recalcBandPrice(${g})">
        <button type="button" class="btn ghost sm" onclick="this.closest('.item-row').remove(); recalcBandPrice(${g})" style="margin-top:4px">حذف الصنف</button>
      </div>
    </div>`;
}

function fillItem(g, i, item) {
  const row = document.querySelector(`.band-card[data-band="${g}"] .item-row[data-item="${i}"]`);
  row.querySelector('[name*="[name]"]').value = item.name;
  row.querySelector('[name*="[qty]"]').value = item.qty;
  row.querySelector('[name*="[unit_price]"]').value = item.unit_price;
  row.querySelector('[name*="[supervision_pct]"]').value = item.supervision_pct;
}

function addItem(g, prefill = null) {
  const container = document.getElementById('band-items-' + g);
  const i = container.children.length;
  container.insertAdjacentHTML('beforeend', itemRowHtml(g, i));
  if (prefill) fillItem(g, i, prefill);
  recalcBandPrice(g);
}

// The band's total price is auto-computed from its items + workers whenever
// any exist — stays manually editable only for bands with no breakdown
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
}

// Pre-fill with the quote's existing bands/items, or start with one empty band
const existingBands = @json($existingBandsData);

if (existingBands.length) {
  existingBands.forEach(b => addBand(b));
} else {
  addBand();
}
</script>
@endpush
@endsection
