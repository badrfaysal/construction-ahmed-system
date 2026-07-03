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
  <div class="form-card">
    <div class="row2">
      <div class="field">
        <label>اسم البند *</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="محارة / سيراميك / دهانات..." required>
      </div>
      <div class="field">
        <label>سعر العميل (ج.م) *</label>
        <input type="number" id="client-price-field" name="client_price" value="{{ old('client_price') }}" min="0" step="0.01" oninput="this.dataset.touched='1'" required>
      </div>
    </div>
    <div class="field">
      <label>الحالة</label>
      <select name="status">
        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>لم يبدأ</option>
        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>جاري</option>
        <option value="done" {{ old('status') === 'done' ? 'selected' : '' }}>منفذ</option>
      </select>
    </div>

    <div class="section-label" style="margin-top:22px">المصنعية — الفنيين الشغالين في البند ده</div>
    <p class="muted" style="margin-bottom:10px">أضف فني أو أكتر، كل واحد باسمه ورقم موبايله ونوع تعاقده وأجره — "سعر العميل" فوق بيتملى تلقائيًا من مجموعهم.</p>

    <div id="workers-list"></div>
    <button type="button" class="btn ghost sm" style="margin:6px 0 18px" onclick="addWorker()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
      إضافة فني
    </button>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('projects.show', $project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

@push('scripts')
@include('bands._contract-scripts', ['defaultSupervisionPct' => $project->defaultSupervisionPct()])
<script>
let workerIdx = 0;
function addWorker(prefill = null) {
  const g = workerIdx++;
  document.getElementById('workers-list').insertAdjacentHTML('beforeend', workerRowHtml(g));
  if (prefill) fillWorker(g, prefill);
  updateWorkerUI(document.querySelector(`.worker-row[data-worker="${g}"]`));
}

// Start with one empty worker row ready to fill in
addWorker();
</script>
@endpush
@endsection
