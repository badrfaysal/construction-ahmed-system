@extends('layouts.app')
@section('title', 'إضافة مرتجع')
@section('page-title', 'إضافة مرتجع — ' . $project->name)

@section('content')
<div class="page-head">
  <div><h3>إضافة مرتجع</h3><p>{{ $project->name }} — تقدر ترجّع أكتر من صنف في نفس المرة</p></div>
  <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

<form method="POST" action="{{ route('returns.store', $project) }}" style="max-width:760px">
  @csrf
  <div class="form-card">
    <div class="row2">
      <div class="field">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
      </div>
      <div class="field">
        <label>ملاحظات</label>
        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="سبب الإرجاع...">
      </div>
    </div>

    <div class="section-label" style="margin-top:18px">الأصناف المرتجعة</div>
    <div id="returns-list"></div>
    <button type="button" class="btn ghost sm" style="margin:6px 0 14px" onclick="addReturnRow()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
      إضافة صنف
    </button>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ المرتجعات</button>
      <a href="{{ route('projects.show', $project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

@push('scripts')
<style>
.ac-item { padding: 8px 12px; cursor: pointer; font-size: 13.5px; border-bottom: 1px solid #f0f0f0; color: var(--ink); line-height: 1.4; }
.ac-item:last-child { border-bottom: none; }
.ac-item:hover { background: #f8f9fa; }
</style>
<script>
const MATERIALS = [
  @foreach($materials as $m)
  {
    id: {{ $m->id }},
    text: "{!! addslashes($m->item) !!} — اشتُرى {{ rtrim(rtrim($m->qty, '0'), '.') }} {{ $m->unit }} بسعر {{ \App\Support\Money::format($m->unit_price) }} يوم {{ $m->date->format('Y-m-d') }}@if($m->supplier) من {!! addslashes($m->supplier->name) !!}@endif (المتبقي: {{ rtrim(rtrim($m->netQty(), '0'), '.') }})",
    net: "{{ $m->netQty() }}",
    unit: "{{ $m->unit }}",
    price: "{{ $m->unit_price }}"
  },
  @endforeach
];

let returnIdx = 0;
function addReturnRow() {
  const g = returnIdx++;
  const html = `
    <div class="worker-row" data-row="${g}" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:10px">
      <div class="field" style="position:relative">
        <label style="margin-bottom:4px">العملية المطلوب الإرجاع منها *</label>
        <input type="text" class="ac-input" placeholder="ابحث واختر الخـامة..." oninput="acSearch(this)" onfocus="acSearch(this)" autocomplete="off" required style="padding:8px 10px; border:1px solid #ccc; border-radius:6px; width:100%; box-sizing:border-box; font-size:14px;">
        <input type="hidden" name="returns[${g}][material_id]" class="ac-hidden" required>
        <div class="ac-list" style="display:none; position:absolute; top:100%; left:0; right:0; max-height:220px; overflow-y:auto; background:#fff; border:1px solid #ccc; z-index:100; border-radius:4px; box-shadow:0 4px 12px rgba(0,0,0,0.15)"></div>
        <p class="muted mat-net" style="margin-top:6px"></p>
      </div>
      <div class="row3" style="margin-top:10px">
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">الكمية المرتجعة *</label>
          <input type="number" name="returns[${g}][qty]" class="ret-qty" min="0.01" step="0.01" required oninput="calcLoss(${g})">
        </div>
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">سعر الشراء الأصلي</label>
          <input type="text" class="ret-orig-price" readonly style="background:var(--bg);color:var(--ink-2)" placeholder="—">
        </div>
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">سعر المرتجع (لو مختلف)</label>
          <input type="number" name="returns[${g}][return_price]" class="ret-price" min="0" step="0.01" placeholder="زي سعر الشراء" oninput="calcLoss(${g})">
        </div>
      </div>
      <p class="ret-loss-note" style="display:none;margin:8px 0 0;font-size:12.5px;color:var(--neg);font-weight:600"></p>
      <div class="btn-row" style="margin-top:8px">
        <button type="button" class="btn ghost sm danger" onclick="this.closest('[data-row]').remove()">حذف</button>
      </div>
    </div>`;
  document.getElementById('returns-list').insertAdjacentHTML('beforeend', html);
}

function normalizeArabic(text) {
  if (!text) return '';
  return text
    .replace(/[أإآ]/g, 'ا')
    .replace(/ة/g, 'ه')
    .replace(/[ىي]/g, 'ي');
}

function acSearch(inp) {
  const term = normalizeArabic(inp.value.toLowerCase());
  const list = inp.parentElement.querySelector('.ac-list');
  const hidden = inp.parentElement.querySelector('.ac-hidden');
  
  hidden.value = ''; // clear previous selection if they type again
  inp.parentElement.querySelector('.mat-net').textContent = '';
  inp.closest('[data-row]').querySelector('.ret-orig-price').value = '';
  
  let html = '';
  let count = 0;
  for (let i = 0; i < MATERIALS.length; i++) {
    const textNorm = normalizeArabic(MATERIALS[i].text.toLowerCase());
    if (textNorm.includes(term)) {
      html += `<div class="ac-item" onclick="acSelect(this, ${i})">${MATERIALS[i].text}</div>`;
      count++;
    }
  }
  
  if (count === 0) {
    html = `<div style="padding:8px 12px; color:#888; font-size:13.5px">لا توجد خامات مطابقة...</div>`;
  }
  
  list.innerHTML = html;
  list.style.display = 'block';
}

function acSelect(itemEl, idx) {
  const m = MATERIALS[idx];
  const container = itemEl.parentElement.parentElement;
  const inp = container.querySelector('.ac-input');
  const hidden = container.querySelector('.ac-hidden');
  const list = container.querySelector('.ac-list');
  
  inp.value = m.text;
  hidden.value = m.id;
  list.style.display = 'none';
  
  const row = container.closest('[data-row]');
  row.querySelector('.mat-net').textContent = 'الصافي المتاح للإرجاع: ' + m.net + ' ' + m.unit;
  row.querySelector('.ret-orig-price').value = m.price;
  
  calcLoss(row.dataset.row);
}

document.addEventListener('click', function(e) {
  if (!e.target.matches('.ac-input')) {
    document.querySelectorAll('.ac-list').forEach(list => list.style.display = 'none');
  }
});

function calcLoss(g) {
  const row = document.querySelector(`[data-row="${g}"]`);
  if (!row) return;
  const qty = parseFloat(row.querySelector('.ret-qty').value) || 0;
  const orig = parseFloat(row.querySelector('.ret-orig-price').value) || 0;
  const retPriceInp = row.querySelector('.ret-price');
  const ret = retPriceInp.value !== '' ? parseFloat(retPriceInp.value) : orig;
  const note = row.querySelector('.ret-loss-note');
  const loss = ret < orig ? qty * (orig - ret) : 0;
  if (loss > 0) {
    note.style.display = 'block';
    note.textContent = '⚠ خسارة متوقعة على المرتجع ده: ' + loss.toFixed(2) + ' ج.م (رجّعت بسعر أقل من الشراء)';
  } else {
    note.style.display = 'none';
  }
}

addReturnRow();
</script>
@endpush
@endsection
