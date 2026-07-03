@extends('layouts.app')
@section('title', 'تعديل بند')
@section('page-title', 'تعديل بند — ' . $band->project->name)

@section('content')
<div class="page-head">
  <div><h3>تعديل بند: {{ $band->name }}</h3><p>{{ $band->project->name }}</p></div>
  <a href="{{ route('projects.show', $band->project) }}" class="btn ghost">رجوع</a>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

<form method="POST" action="{{ route('bands.update', $band) }}">
  @csrf
  @method('PUT')
  <div class="form-card">
    <div class="row2">
      <div class="field">
        <label>اسم البند *</label>
        <input type="text" name="name" value="{{ old('name', $band->name) }}" required>
      </div>
      <div class="field">
        <label>سعر العميل (ج.م) *</label>
        <input type="number" id="client-price-field" name="client_price" value="{{ old('client_price', $band->client_price) }}" min="0" step="0.01" oninput="this.dataset.touched='1'" required>
      </div>
    </div>
    <div class="field">
      <label>الحالة</label>
      <select name="status">
        <option value="pending" {{ old('status', $band->status) === 'pending' ? 'selected' : '' }}>لم يبدأ</option>
        <option value="active" {{ old('status', $band->status) === 'active' ? 'selected' : '' }}>جاري</option>
        <option value="done" {{ old('status', $band->status) === 'done' ? 'selected' : '' }}>منفذ</option>
      </select>
    </div>

    <div class="section-label" style="margin-top:22px">المصنعية — الفنيين الشغالين في البند ده</div>
    <p class="muted" style="margin-bottom:10px">
      أضف فني أو أكتر — "سعر العميل" فوق بيتملى تلقائيًا من مجموعهم.
      لو نوع تعاقد فني "بالمتر" واستغرق الشغل أمتار أكتر من المتوقع، زوّد "الكمية" وهيتحدث أجره وسعره للعميل تلقائيًا.
    </p>

    <div id="workers-list"></div>
    <button type="button" class="btn ghost sm" style="margin:6px 0 18px" onclick="addWorker()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
      إضافة فني
    </button>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ التعديلات</button>
      <a href="{{ route('projects.show', $band->project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

<div class="btn-row" style="margin-top:16px">
  <form method="POST" action="{{ route('bands.destroy', $band) }}" onsubmit="return confirm('حذف هذا البند؟')">
    @csrf @method('DELETE')
    <button class="btn danger">حذف البند</button>
  </form>
</div>

@php
  // Pre-computed in PHP so Blade's @json() directive doesn't have to parse
  // a nested-closure expression (see quotes/edit.blade.php for the same fix).
  $existingWorkers = $band->workers->map(fn ($w) => [
      'name'               => $w->name,
      'phone'              => $w->phone,
      'specialty'          => $w->specialty,
      'contract_type'      => $w->contract_type,
      'contract_qty'       => $w->contract_qty,
      'contract_unit_rate' => $w->contract_unit_rate,
      'sell_rate'          => $w->sell_rate,
      'amount'             => $w->amount,
      'sell_amount'        => $w->sell_amount,
      'supervision_pct'    => $w->supervision_pct,
      'start_date'         => $w->start_date?->format('Y-m-d'),
      'notes'              => $w->notes,
  ]);
@endphp

@push('scripts')
@include('bands._contract-scripts', ['defaultSupervisionPct' => $band->project->defaultSupervisionPct()])
<script>
let workerIdx = 0;
function addWorker(prefill = null) {
  const g = workerIdx++;
  document.getElementById('workers-list').insertAdjacentHTML('beforeend', workerRowHtml(g));
  if (prefill) fillWorker(g, prefill);
  updateWorkerUI(document.querySelector(`.worker-row[data-worker="${g}"]`));
}

// Existing workers, or — for a band saved before this feature shipped — a
// synthetic single-worker seed built from the old team_name/labor_amount
// fields, so nothing looks blank/lost the first time this band is reopened.
const existingWorkers = @json($existingWorkers);
const legacySeed = @json($legacySeed);

if (existingWorkers.length) {
  existingWorkers.forEach(w => addWorker(w));
} else if (legacySeed) {
  addWorker(legacySeed);
} else {
  addWorker();
}
</script>
@endpush
@endsection
