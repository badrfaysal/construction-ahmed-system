@extends('layouts.app')
@section('title', 'المحفظة')
@section('page-title', 'محفظة المقاولات')

@section('content')
<div class="page-head">
  <div><h3>محفظة المقاولات</h3><p>الرصيد الفعلي، وتسجيل الحركات اليدوية اللي مش تابعة لمشروع (رأس مال، مسحوبات، مصاريف إدارية)</p></div>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

@php
  // لمسة لون لكل فئة محفظة — نفس روح الكروت المتدرّجة في لوحة التحكم
  $catAccent = fn ($cat) => match ($cat) {
      'bank_wallet'    => 'var(--accent)',
      'safe_cash'      => 'var(--info)',
      'project_sector' => 'var(--brand)',
      default          => 'var(--purple)',
  };
@endphp

<div class="grid cols-3" style="margin-bottom:20px">
  <div class="vstat {{ $balance >= 0 ? 'vstat-green' : 'vstat-red' }}">
    <div class="top">
      <span class="label">رصيد محفظة المقاولات</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($balance) }} <small>ج.م</small></div>
    <div class="note">بيتحرك تلقائيًا مع كل مشتريات، تحصيلات، ودفعات الصنايعية</div>
  </div>
</div>

{{-- كل المحافظ المتاحة (بعد دمج السيستمين) — تقدر تصرف/تحصّل من أي واحدة --}}
<div class="table-card" style="margin-bottom:20px">
  <div class="section-label" style="margin:14px 18px 6px">كل المحافظ المتاحة <span class="muted" style="font-weight:400">— أي حركة تقدر تختار منها المحفظة</span></div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;padding:6px 18px 18px">
    @foreach($wallets as $w)
      @php $accent = $catAccent($w->category); $isDefault = $w->id == \App\Models\Account::WALLET_ID; @endphp
      <div style="border:1px solid var(--line);border-inline-start:3px solid {{ $accent }};border-radius:10px;padding:11px 13px;background:{{ $isDefault ? 'var(--accent-soft)' : '#fff' }};box-shadow:var(--shadow-sm)">
        <div style="font-size:12px;color:var(--ink-2);margin-bottom:3px;font-weight:600">
          {{ $w->account_name }}@if($isDefault) <span style="color:var(--brand)">★</span>@endif
        </div>
        <div style="font-weight:700;font-size:1.05rem;color:{{ (float)$w->balance >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
          {{ \App\Support\Money::format($w->balance) }} <small style="font-weight:400;color:var(--ink-3)">ج.م</small>
        </div>
      </div>
    @endforeach
  </div>
</div>

<div class="grid cols-2" style="align-items:start;gap:20px">
  {{-- Record a manual money move --}}
  <form method="POST" action="{{ route('wallet.store') }}">
    @csrf
    <div class="form-card" style="max-width:none">
      <div class="section-label" style="margin-top:0">تسجيل حركة يدوية</div>
      <div class="field">
        <label>نوع الحركة *</label>
        <select name="kind" id="wallet-kind" required onchange="updateKind()">
          <option value="capital">تغذية رأس مال (وارد +)</option>
          <option value="withdrawal">مسحوبات شخصية (صادر −)</option>
          <option value="admin_expense">مصروف إداري عام (صادر −)</option>
        </select>
        <p class="muted" id="kind-hint" style="margin-top:6px;font-size:12px"></p>
      </div>
      <div class="field">
        @include('partials._wallet-select', ['wallets' => $wallets, 'label' => 'المحفظة *', 'required' => true, 'hint' => 'الحركة هتخصم/تضيف على المحفظة دي، وهتظهر في سجل السيستم الأول بعلامة 🏗️ [مقاولات].', 'selectStyle' => 'width:100%'])
      </div>
      <div class="row2">
        <div class="field">
          <label>المبلغ (ج.م) *</label>
          <input type="number" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" required>
        </div>
        <div class="field">
          <label>التاريخ *</label>
          <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
        </div>
      </div>
      <div class="field">
        <label id="party-label">الجهة / المصدر</label>
        <input type="text" name="party" id="party-input" value="{{ old('party') }}">
      </div>
      <div class="field">
        <label>ملاحظات</label>
        <input type="text" name="description" value="{{ old('description') }}" placeholder="تفاصيل إضافية (اختياري)">
      </div>
      <div class="btn-row" style="margin-top:8px">
        <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ الحركة</button>
      </div>
    </div>
  </form>

  {{-- Manual entries history --}}
  <div class="table-card">
    <div class="section-label" style="margin:14px 18px 0">الحركات اليدوية</div>
    @if($manual->count())
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>التاريخ</th>
              <th>النوع</th>
              <th>المحفظة</th>
              <th>الجهة</th>
              <th class="num">المبلغ</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($manual as $tx)
              <tr>
                <td class="muted">{{ $tx->date->format('Y-m-d') }}</td>
                <td>
                  <span class="tag {{ $tx->direction === 'in' ? 'green' : 'red' }}">{{ $tx->directionAr() }}</span>
                  <div class="muted" style="font-size:12px;margin-top:2px">{{ $tx->type }}</div>
                </td>
                <td>
                  <span style="font-size:12px;font-weight:600">{{ $tx->account?->account_name ?? 'المقاولات' }}</span>
                </td>
                <td>
                  {{ $tx->party }}
                  @if($tx->description)<div class="muted" style="font-size:12px">{{ $tx->description }}</div>@endif
                </td>
                <td class="num" style="color:{{ $tx->direction === 'in' ? 'var(--pos)' : 'var(--neg)' }}">
                  {{ $tx->direction === 'in' ? '+ ' : '− ' }}{{ \App\Support\Money::format($tx->amount) }}
                </td>
                <td>
                  <form method="POST" action="{{ route('wallet.destroy', $tx) }}" onsubmit="return confirm('حذف هذه الحركة؟ هيتعدّل رصيد المحفظة.')">
                    @csrf @method('DELETE')
                    <button class="btn ghost sm" style="color:var(--neg)">حذف</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div style="padding:14px 18px;border-top:1px solid var(--line)">
        {{ $manual->links() }}
      </div>
    @else
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
        <h4>لا توجد حركات يدوية</h4>
      </div>
    @endif
  </div>
</div>

@push('scripts')
<script>
const KIND_META = {
  capital:       { hint: 'فلوس دخلت الشركة (رأس مال / تمويل) — بتزوّد الرصيد.', partyLabel: 'مصدر التمويل', partyPh: 'مثال: رأس المال / صاحب الشركة' },
  withdrawal:    { hint: 'سحب أرباح أو مبلغ شخصي من الخزنة — بيقلّل الرصيد.',   partyLabel: 'المستفيد',      partyPh: 'مثال: صاحب الشركة' },
  admin_expense: { hint: 'مصروف عام مش تابع لمشروع (إيجار، موبايل، ...) — بيقلّل الرصيد.', partyLabel: 'بند المصروف', partyPh: 'مثال: إيجار المكتب' },
};
function updateKind() {
  const k = document.getElementById('wallet-kind').value;
  const m = KIND_META[k];
  document.getElementById('kind-hint').textContent = m.hint;
  document.getElementById('party-label').textContent = m.partyLabel;
  document.getElementById('party-input').placeholder = m.partyPh;
}
updateKind();
</script>
@endpush
@endsection
