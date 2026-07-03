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
        <div class="field" style="margin:0"><input type="text" name="bands[${g}][name]" placeholder="اسم البند مثل: تشطيبات" required></div>
        <div class="field" style="margin:0"><input type="number" name="bands[${g}][price]" class="band-price" placeholder="السعر الإجمالي التقريبي (ج.م)" min="0" step="0.01"></div>
      </div>
      <div class="band-items" id="band-items-${g}"></div>
      <div class="btn-row" style="margin-top:6px">
        <button type="button" class="btn ghost sm" onclick="addItem(${g})">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
          إضافة صنف تقريبي
        </button>
        <button type="button" class="btn ghost sm danger" onclick="this.closest('.band-card').remove()">حذف البند</button>
      </div>
    </div>`;
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

// The band's total price is auto-computed from its items whenever any exist —
// stays manually editable only for bands with no itemized breakdown
function recalcBandPrice(g) {
  const card = document.querySelector(`.band-card[data-band="${g}"]`);
  const items = card.querySelectorAll('.item-row');
  const priceField = card.querySelector('.band-price');

  if (items.length === 0) {
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
