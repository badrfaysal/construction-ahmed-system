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

<form method="POST" action="{{ route('materials.store') }}" style="max-width:900px">
  @csrf

  <div class="form-card" style="max-width:none;margin-bottom:16px">
    <div class="field" style="margin-bottom:0">
      <label>المشروع *</label>
      <select name="project_id" id="project_select" required onchange="loadBandsForProject(this.value); updateSupervisionDefault(this)">
        <option value="">— اختر المشروع —</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" data-sup="{{ $p->default_supervision_pct > 0 ? $p->default_supervision_pct : $settings->default_supervision_pct }}" {{ $selectedProject?->id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div id="groups-container"></div>

  <button type="button" class="btn ghost" style="margin-bottom:20px" onclick="addGroup()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    إضافة بند آخر
  </button>

  <div class="btn-row">
    <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ كل الأصناف</button>
    <a href="{{ route('materials.index') }}" class="btn ghost">إلغاء</a>
  </div>
</form>

{{-- Shared item/unit suggestions used inside every item row --}}
<datalist id="items-list">
  <option value="أسمنت"><option value="رمل"><option value="سيراميك أرضيات">
  <option value="سيراميك حوائط"><option value="مواسير PPR"><option value="دهانات بلاستيك">
</datalist>
<datalist id="units-list">
  <option value="شيكارة"><option value="م²"><option value="نقلة">
  <option value="ماسورة"><option value="لفة"><option value="طقم"><option value="طوبة">
</datalist>

@push('scripts')
<script>
// Bands of the currently selected project — refreshed whenever the project changes
let bandsList = @json($bands->map(fn($b) => ['id' => $b->id, 'name' => $b->name]));
let groupCounter = 0;

// Default supervision % for new item rows — follows the selected project's
// default (falls back to the global settings value). Editable per row.
let defaultSupervisionPct = {{ $selectedProject ? ($selectedProject->default_supervision_pct > 0 ? $selectedProject->default_supervision_pct : $settings->default_supervision_pct) : $settings->default_supervision_pct }};
function updateSupervisionDefault(select) {
  const opt = select.options[select.selectedIndex];
  if (opt && opt.dataset.sup !== undefined) defaultSupervisionPct = parseFloat(opt.dataset.sup) || 0;
}

// Supplier <option> list is static (suppliers aren't tied to a project), built once
const supplierOptionsHtml = `
  <option value="">— بدون مورد —</option>
  @foreach($suppliers as $s)
    <option value="{{ $s->id }}">{{ $s->name }}</option>
  @endforeach
`;

function bandOptionsHtml() {
  let html = '<option value="">— بند عام (بدون بند) —</option>';
  bandsList.forEach(b => {
    html += `<option value="${b.id}">${b.name}</option>`;
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
      <div class="item-row-top">
        <div class="field">
          <label>اسم الصنف</label>
          <input type="text" name="groups[${g}][items][${i}][item]" placeholder="مثل: أسمنت" required list="items-list">
        </div>
        <button type="button" class="btn ghost sm" onclick="this.closest('.item-row').remove()" title="حذف الصنف">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
        </button>
      </div>
      <div class="row3">
        <div class="field" style="margin:0">
          <label>الوحدة</label>
          <input type="text" name="groups[${g}][items][${i}][unit]" placeholder="شيكارة" required list="units-list">
        </div>
        <div class="field" style="margin:0">
          <label>الكمية</label>
          <input type="number" name="groups[${g}][items][${i}][qty]" placeholder="0" min="0" step="0.1" required>
        </div>
        <div class="field" style="margin:0">
          <label>سعر الشراء (تكلفة)</label>
          <input type="number" name="groups[${g}][items][${i}][unit_price]" placeholder="0" min="0" step="0.01" required>
        </div>
      </div>
      <div class="row3">
        <div class="field" style="margin:0">
          <label>سعر البيع للعميل</label>
          <input type="number" name="groups[${g}][items][${i}][sell_price]" placeholder="0" min="0" step="0.01" required>
        </div>
        <div class="field" style="margin:0">
          <label>نسبة الإشراف % (اختياري)</label>
          <input type="number" name="groups[${g}][items][${i}][supervision_pct]" placeholder="0" min="0" max="100" step="0.1" value="${defaultSupervisionPct}">
        </div>
      </div>
    </div>`;
}

// Add another item row inside a specific band group
function addItem(g) {
  const container = document.getElementById('items-' + g);
  const i = container.children.length;
  container.insertAdjacentHTML('beforeend', itemRowHtml(g, i));
}

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
      <div class="row3">
        <div class="field">
          <label>البند</label>
          <select name="groups[${g}][band_id]" class="band-select">${bandOptionsHtml()}</select>
        </div>
        <div class="field">
          <label>المورد</label>
          <select name="groups[${g}][supplier_id]">${supplierOptionsHtml}</select>
        </div>
        <div class="field">
          <label>تاريخ الشراء *</label>
          <input type="date" name="groups[${g}][date]" value="${today}" required>
        </div>
      </div>
      {{-- Payment type for this purchase group --}}
      <div style="background:var(--bg);border-radius:8px;padding:14px 16px;margin-bottom:14px">
        <div style="margin-bottom:10px;font-size:.85rem;font-weight:600;color:var(--text-muted)">طريقة دفع هذا الشراء</div>
        <div style="display:flex;gap:16px;flex-wrap:wrap">
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
        <div id="paid-amt-row-${g}" style="display:none;margin-top:12px">
          <div class="field" style="max-width:260px;margin:0">
            <label>المبلغ المدفوع الآن (ج.م) *</label>
            <input type="number" name="groups[${g}][paid_amount]" id="paid-amt-${g}" min="0" step="0.01" placeholder="0">
          </div>
        </div>
      </div>
      <div class="items-container" id="items-${g}"></div>
      <button type="button" class="btn ghost sm" onclick="addItem(${g})">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة صنف
      </button>
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
}

// Start with one band group ready to fill in
addGroup();
</script>
@endpush
@endsection
