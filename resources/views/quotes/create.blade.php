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

@push('scripts')
<script>
let bandIdx = 0;

function bandRowHtml(g) {
  return `
    <div class="band-card" data-band="${g}" style="border:1px solid var(--line);border-radius:10px;padding:12px;margin-bottom:10px">
      <div class="row2">
        <div class="field" style="margin:0">
          <label style="font-size:.76rem;color:var(--muted)">اسم البند *</label>
          <input type="text" name="bands[${g}][name]" placeholder="مثل: محارة، سيراميك، دهانات..." required>
        </div>
        <div class="field" style="margin:0">
          <label style="font-size:.76rem;color:var(--muted)">سعر البيع للعميل — إجمالي البند (ج.م) <span style="color:#059669">يُحسب تلقائياً من الأصناف</span></label>
          <input type="number" name="bands[${g}][price]" class="band-price" placeholder="0.00" min="0" step="0.01">
        </div>
      </div>
      <div class="band-items" id="band-items-${g}"></div>
      <div class="btn-row" style="margin-top:6px">
        <button type="button" class="btn ghost sm" onclick="addItem(${g})">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
          إضافة صنف
        </button>
        <button type="button" class="btn ghost sm danger" onclick="this.closest('.band-card').remove()">حذف البند</button>
      </div>
    </div>`;
}

function itemRowHtml(g, i) {
  return `
    <div class="row4 item-row" data-item="${i}" style="margin:3px 0;align-items:end">
      <div class="field" style="margin:0"><input type="text" name="bands[${g}][items][${i}][name]" placeholder="مثل: بلاط، جبس، دهان..." required oninput="recalcBandPrice(${g})"></div>
      <div class="field" style="margin:0"><input type="number" name="bands[${g}][items][${i}][qty]" placeholder="الكمية" min="0" step="0.01" value="1" required oninput="recalcBandPrice(${g})"></div>
      <div class="field" style="margin:0">
        <input type="number" name="bands[${g}][items][${i}][unit_price]" placeholder="السعر للوحدة" min="0" step="0.01" required oninput="recalcBandPrice(${g})" style="border-color:#059669">
        <small style="color:#059669;font-size:.68rem">سعر البيع للعميل (بدون إشراف)</small>
      </div>
      <div class="field" style="margin:0">
        <input type="number" name="bands[${g}][items][${i}][supervision_pct]" placeholder="%" min="0" max="100" step="0.1" value="{{ $settings->default_supervision_pct }}" oninput="recalcBandPrice(${g})" style="border-color:#7c3aed">
        <small style="color:#7c3aed;font-size:.68rem">نسبة الإشراف — تُضاف على السعر</small>
        <button type="button" class="btn ghost sm" onclick="this.closest('.item-row').remove(); recalcBandPrice(${g})" style="margin-top:4px">حذف الصنف</button>
      </div>
    </div>`;
}

function addItem(g) {
  const container = document.getElementById('band-items-' + g);
  const i = container.querySelectorAll('.item-row').length;
  // Only show headers if this is the first item in this band
  if (i === 0 && !container.querySelector('.item-headers')) {
    container.insertAdjacentHTML('beforeend', `
      <div class="item-headers" style="display:grid;grid-template-columns:2fr 1fr 1.2fr 1fr;gap:8px;margin:8px 0 2px;padding:0 2px">
        <div style="font-size:.72rem;font-weight:700;color:#64748b">اسم الصنف</div>
        <div style="font-size:.72rem;font-weight:700;color:#64748b">الكمية</div>
        <div style="font-size:.72rem;font-weight:700;color:#059669">سعر البيع للعميل (ج.م)</div>
        <div style="font-size:.72rem;font-weight:700;color:#7c3aed">نسبة الإشراف %</div>
      </div>`);
  }
  container.insertAdjacentHTML('beforeend', itemRowHtml(g, i));
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

function addBand() {
  const g = bandIdx++;
  document.getElementById('bands-list').insertAdjacentHTML('beforeend', bandRowHtml(g));
}

// Start with one empty band ready to fill in
addBand();
</script>
@endpush
@endsection
