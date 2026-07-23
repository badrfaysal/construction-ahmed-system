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
  <div class="form-card band-form-card">
    <div class="field">
      <label>اسم البند *</label>
      <input type="text" name="name" value="{{ old('name') }}" placeholder="محارة / سيراميك / دهانات..." required list="band-names-list">
    </div>

    <div style="margin-top:20px">
      <div style="font-weight:700; margin-bottom:4px; font-size:1.1rem; color:var(--brand)">المصنعية</div>
      <p class="muted" style="margin:0 0 10px">أضف فني أو أكتر، كل واحد باسمه ورقم موبايله ونوع تعاقده وأجره.</p>
      <div id="workers-list"></div>
      <button type="button" class="btn ghost sm" style="margin:6px 0 4px" onclick="addWorker()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
        إضافة فني
      </button>
    </div>

    {{-- الإجمالي الكلي للعميل — يتحدث تلقائيًا (مصنعية + خامات + نثريات)، وبيبان دايمًا مهما كانت التابة المفتوحة --}}
    <div class="band-total-box">
      <div class="band-total-lbl">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-coins"/></svg>
        الإجمالي الكلي للعميل (ج.م)
      </div>
      <input type="number" id="client-price-field" name="client_price" value="{{ old('client_price', 0) }}"
             min="0" step="0.01" oninput="this.dataset.touched='1'" required class="band-total-inp">
      <div class="band-total-hint">يُحسب إجمالي المصنعيات تلقائياً، ويمكن تعديله يدوياً.</div>
    </div>

    <div class="btn-row" style="margin-top:16px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('projects.show', $project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>


<datalist id="band-names-list">
  @foreach($bandNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>

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

// لو الفورم رجع بعد فشل فاليديشن، رجّع نفس صفوف الفنيين اللي المستخدم كتبها
// بدل ما نبدأ بصف فاضي واحد ونضيع كل اللي كان مكتوب
@if(count(old('workers', [])))
  @foreach(old('workers') as $ow)
    addWorker(@json($ow));
  @endforeach
@else
  addWorker();
@endif

function updateClientPrice() {
  const clientField = document.getElementById('client-price-field');
  if (! clientField || clientField.dataset.touched === '1') return;

  let laborTotal = 0;
  document.querySelectorAll('.worker-row').forEach(row => {
    const amount = parseFloat(row.querySelector('.final-amount').value) || 0;
    const sellAmount = parseFloat(row.querySelector('.sell-amount-field').value) || 0;
    const pct = parseFloat(row.querySelector('.supervision-pct').value) || 0;
    const base = sellAmount || amount;
    laborTotal += base * (1 + pct / 100);
  });

  clientField.value = laborTotal.toFixed(2);
}

updateClientPrice();
</script>
@endpush
@endsection
