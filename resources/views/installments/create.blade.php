@extends('layouts.app')
@section('title', 'قسط جديد')
@section('page-title', 'إضافة قسط')
@section('content')
<div class="page-head">
  <div><h3>قسط جديد</h3><p>تحصيل دفعة من العميل وربطها ببند معين</p></div>
  <div style="display:flex;gap:8px">
    <a href="{{ route('installments.plan.form') }}" class="btn ghost">مولد خطة التقسيط</a>
    <a href="{{ route('installments.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  </div>
@endif

<form method="POST" action="{{ route('installments.store') }}">
  @csrf
  <div class="form-card">
    <div class="row2">
      <div class="field">
        <label>المشروع *</label>
        <select name="project_id" id="inst-project" required onchange="loadInstBands(this.value)">
          <option value="">— اختر المشروع —</option>
          @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ (old('project_id') ?? $selectedProjectId) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>البند (اختياري — هذا المبلغ تحت أي بند؟)</label>
        <select name="band_id" id="inst-band">
          <option value="">— بدون بند محدد —</option>
          @foreach($bands as $b)
            <option value="{{ $b->id }}" {{ old('band_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="field">
      <label>اسم القسط / الدفعة *</label>
      <input type="text" name="label" value="{{ old('label') }}" placeholder="دفعة مقدم / القسط الأول / دفعة التشطيب..." required>
    </div>

    <div class="row3">
      <div class="field">
        <label>المبلغ (ج.م) *</label>
        <input type="number" name="amount" value="{{ old('amount') }}" min="0" step="0.01" required>
      </div>
      <div class="field">
        <label>تاريخ الاستحقاق *</label>
        <input type="date" name="due_date" value="{{ old('due_date') }}" required>
      </div>
      <div class="field">
        <label>الحالة</label>
        <select name="status">
          <option value="upcoming" {{ old('status') === 'upcoming' ? 'selected' : '' }}>قادم</option>
          <option value="due"      {{ old('status') === 'due'      ? 'selected' : '' }}>مستحق</option>
          <option value="paid"     {{ old('status') === 'paid'     ? 'selected' : '' }}>مدفوع</option>
        </select>
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label>طريقة الدفع</label>
        <input type="text" name="payment_method" value="{{ old('payment_method') }}" placeholder="كاش / تحويل بنكي">
      </div>
      <div class="field">
        <label>تاريخ الدفع الفعلي</label>
        <input type="date" name="paid_date" value="{{ old('paid_date') }}">
      </div>
    </div>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('installments.index') }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

@push('scripts')
<script>
function loadInstBands(projectId) {
  const sel = document.getElementById('inst-band');
  sel.innerHTML = '<option value="">— بدون بند محدد —</option>';
  if (!projectId) return;
  fetch('/api/projects/' + projectId + '/bands')
    .then(r => r.json())
    .then(bands => {
      bands.forEach(b => {
        sel.innerHTML += `<option value="${b.id}">${b.name}</option>`;
      });
    });
}
</script>
@endpush
@endsection
