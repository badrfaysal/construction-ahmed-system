@extends('layouts.app')
@section('title', 'مصروف نثري')
@section('page-title', 'مصروف نثري — ' . $project->name)

@section('content')
<div class="page-head">
  <div><h3>إضافة مصروف نثري</h3><p>{{ $project->name }} — إكرامية، نقل، إفطار... (بيتحاسب على العميل زي الخامة)</p></div>
  <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

<form method="POST" action="{{ route('expenses.store', $project) }}" style="max-width:640px">
  @csrf
  <div class="form-card">
    <div class="row2">
      <div class="field">
        <label>البند</label>
        <select name="band_id">
          <option value="">— بند عام (بدون بند) —</option>
          @foreach($bands as $b)
            <option value="{{ $b->id }}" {{ old('band_id', $activeBand?->id) == $b->id ? 'selected' : '' }}>{{ $b->name }}{{ $b->status === 'active' ? ' (جاري حاليًا)' : '' }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>البيان *</label>
        <input type="text" name="item" value="{{ old('item') }}" placeholder="نقل / إكرامية / إفطار العمال" required list="misc-list">
        <datalist id="misc-list">
          <option value="نقل"><option value="إكرامية"><option value="إفطار العمال"><option value="مواصلات"><option value="نثريات">
        </datalist>
      </div>
    </div>
    <div class="row3">
      <div class="field">
        <label>المبلغ (تكلفة) *</label>
        <input type="number" id="amount-field" name="amount" value="{{ old('amount') }}" min="0" step="0.01" required oninput="syncSell()">
      </div>
      <div class="field">
        <label>سعر البيع للعميل *</label>
        <input type="number" id="sell-field" name="sell_price" value="{{ old('sell_price') }}" min="0" step="0.01" required oninput="this.dataset.touched='1'">
      </div>
      <div class="field">
        <label>نسبة الإشراف %</label>
        <input type="number" name="supervision_pct" value="{{ old('supervision_pct', $defaultSup) }}" min="0" max="100" step="0.1">
      </div>
    </div>
    <div class="field">
      <label>التاريخ *</label>
      <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
    </div>
    {{-- طريقة الدفع --}}
    <div style="background:var(--bg);border-radius:8px;padding:14px 16px;margin-bottom:14px">
      <div style="margin-bottom:10px;font-size:.85rem;font-weight:600;color:var(--text-muted)">طريقة الدفع</div>
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="radio" name="payment_type" value="immediate" checked onchange="toggleExpenseWallet(this.value)">
          <span>دفع فوري</span>
        </label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="radio" name="payment_type" value="deferred" onchange="toggleExpenseWallet(this.value)">
          <span>آجل (يُسجّل بدون خصم من المحفظة)</span>
        </label>
      </div>
      <div id="expense-wallet-row">
        @include('partials._wallet-select', ['wallets' => $wallets, 'label' => 'المحفظة (الصرف منها) *', 'required' => true, 'selectStyle' => 'width:100%'])
      </div>
    </div>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ المصروف</button>
      <a href="{{ route('projects.show', $project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

@push('scripts')
<script>
function syncSell() {
  const sell = document.getElementById('sell-field');
  if (sell.dataset.touched === '1') return;
  sell.value = document.getElementById('amount-field').value;
}
function toggleExpenseWallet(val) {
  const row = document.getElementById('expense-wallet-row');
  const sel = row.querySelector('select[name="account_id"]');
  if (val === 'deferred') {
    row.style.display = 'none';
    if (sel) sel.required = false;
  } else {
    row.style.display = '';
    if (sel) sel.required = true;
  }
}
</script>
@endpush
@endsection
