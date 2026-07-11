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
<script>
const MATERIAL_OPTIONS = `
  <option value="">— اختر عملية الشراء —</option>
  @foreach($materials as $m)
    <option value="{{ $m->id }}" data-net="{{ $m->netQty() }}" data-unit="{{ $m->unit }}" data-price="{{ $m->unit_price }}">{{ $m->item }} — اشتُرى {{ rtrim(rtrim($m->qty, '0'), '.') }} {{ $m->unit }} بسعر {{ \App\Support\Money::format($m->unit_price) }} يوم {{ $m->date->format('Y-m-d') }}@if($m->supplier) من {{ $m->supplier->name }}@endif (المتبقي: {{ rtrim(rtrim($m->netQty(), '0'), '.') }})</option>
  @endforeach
`;

let returnIdx = 0;
function addReturnRow() {
  const g = returnIdx++;
  const html = `
    <div class="worker-row" data-row="${g}" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:10px">
      <div class="field">
        <label style="margin-bottom:4px">العملية المطلوب الإرجاع منها *</label>
        <select name="returns[${g}][material_id]" class="mat-select" required onchange="showNet(this)">${MATERIAL_OPTIONS}</select>
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

function showNet(select) {
  const row = select.closest('[data-row]');
  const opt = select.options[select.selectedIndex];
  const el = row.querySelector('.mat-net');
  el.textContent = (opt && opt.value) ? ('الصافي المتاح للإرجاع: ' + opt.dataset.net + ' ' + opt.dataset.unit) : '';
  row.querySelector('.ret-orig-price').value = (opt && opt.value) ? opt.dataset.price : '';
  row.dataset.gIdx = row.dataset.row;
  calcLoss(row.dataset.row);
}

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
