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
    <option value="{{ $m->id }}" data-net="{{ $m->netQty() }}" data-unit="{{ $m->unit }}">{{ $m->item }} — اشتُرى {{ rtrim(rtrim($m->qty, '0'), '.') }} {{ $m->unit }} يوم {{ $m->date->format('Y-m-d') }}@if($m->supplier) من {{ $m->supplier->name }}@endif (المتبقي: {{ rtrim(rtrim($m->netQty(), '0'), '.') }})</option>
  @endforeach
`;

let returnIdx = 0;
function addReturnRow() {
  const g = returnIdx++;
  const html = `
    <div class="worker-row" data-row="${g}" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:10px">
      <div class="row2">
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">العملية المطلوب الإرجاع منها *</label>
          <select name="returns[${g}][material_id]" class="mat-select" required onchange="showNet(this)">${MATERIAL_OPTIONS}</select>
          <p class="muted mat-net" style="margin-top:6px"></p>
        </div>
        <div class="field" style="margin:0">
          <label style="margin-bottom:4px">الكمية المرتجعة *</label>
          <input type="number" name="returns[${g}][qty]" min="0.01" step="0.01" required>
        </div>
      </div>
      <div class="btn-row" style="margin-top:8px">
        <button type="button" class="btn ghost sm danger" onclick="this.closest('[data-row]').remove()">حذف</button>
      </div>
    </div>`;
  document.getElementById('returns-list').insertAdjacentHTML('beforeend', html);
}

function showNet(select) {
  const opt = select.options[select.selectedIndex];
  const el = select.closest('[data-row]').querySelector('.mat-net');
  el.textContent = (opt && opt.value) ? ('الصافي المتاح للإرجاع: ' + opt.dataset.net + ' ' + opt.dataset.unit) : '';
}

addReturnRow();
</script>
@endpush
@endsection
