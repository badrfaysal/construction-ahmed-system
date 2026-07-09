@extends('layouts.app')
@section('title', 'دفعات الصنايعي')
@section('page-title', 'دفعات ' . $worker->name)

@section('content')
@php
  $project   = $worker->band->project;
  $contract  = (float) $worker->amount;
  $paid      = $worker->paidTotal();
  $remaining = $worker->remaining();
@endphp

<div class="page-head">
  <div>
    <h3>دفعات: {{ $worker->name }}</h3>
    <p>
      {{ $project->name }} — بند {{ $worker->band->name }}
      · {{ $worker->contractTypeAr() }}
      @if(in_array($worker->contract_type, ['per_meter','per_piece','daily']) && $worker->contract_qty)
        ({{ rtrim(rtrim(number_format($worker->contract_qty, 2), '0'), '.') }} × {{ \App\Support\Money::format($worker->contract_unit_rate) }})
      @endif
    </p>
  </div>
  <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع للمشروع</a>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

<div class="grid cols-3" style="margin-bottom:20px">
  <div class="card stat">
    <div class="top"><span class="label">المتعاقد عليه</span></div>
    <div class="val tnum">{{ \App\Support\Money::format($contract) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">المدفوع للصنايعي</span></div>
    <div class="val tnum" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">المتبقي (مستحق)</span></div>
    <div class="val tnum" style="color:{{ $remaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($remaining) }} <small>ج.م</small></div>
  </div>
</div>

<div class="grid cols-2" style="align-items:start;gap:20px">
  {{-- Record a new installment --}}
  <form method="POST" action="{{ route('workers.payments.store', $worker) }}">
    @csrf
    <div class="form-card" style="max-width:none">
      <div class="section-label" style="margin-top:0">تسجيل دفعة جديدة</div>
      <div class="row2">
        <div class="field">
          <label>المبلغ (ج.م) *</label>
          <input type="number" id="pay_amt" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" placeholder="{{ number_format($remaining, 2, '.', '') }}" required>
          @if($remaining > 0)
          <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">
            <button type="button" class="btn ghost sm pay-btn" id="btn-full" onclick="setPayAmount({{ $remaining }}, 'full')">سداد كلي</button>
            <button type="button" class="btn ghost sm pay-btn" id="btn-partial" onclick="setPayAmount({{ round($remaining * 0.5, 2) }}, 'partial')">النصف</button>
            <button type="button" class="btn ghost sm pay-btn" id="btn-quarter" onclick="setPayAmount({{ round($remaining * 0.25, 2) }}, 'quarter')">الربع</button>
          </div>
          @endif
        </div>
        <div class="field">
          <label>التاريخ *</label>
          <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
        </div>
      </div>
      <div class="row2">
        <div class="field">
          <label>الخصم (ج.م)</label>
          <input type="number" name="discount" step="0.01" min="0" value="{{ old('discount') }}" placeholder="0.00">
        </div>
        <div class="field">
          @include('partials._wallet-select', ['wallets' => $wallets, 'label' => 'المحفظة (الصرف منها) *', 'required' => true, 'selectStyle' => 'width:100%'])
        </div>
      </div>
      <div class="field">
        <label>ملاحظات</label>
        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="اختياري">
      </div>
      <div class="btn-row" style="margin-top:8px">
        <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ الدفعة</button>
      </div>
    </div>
  </form>

  {{-- Payment history --}}
  <div class="table-card">
    <div class="section-label" style="margin:14px 18px 0">سجل الدفعات</div>
    @if($worker->payments->count())
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>التاريخ</th>
              <th class="num">المبلغ</th>
              <th>الخصم</th>
              <th>ملاحظات</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($worker->payments as $p)
              <tr style="cursor:pointer" onclick="showPayDetail(this)"
                  data-date="{{ $p->date->format('Y-m-d') }}"
                  data-amount="{{ \App\Support\Money::format($p->amount) }}"
                  data-discount="{{ \App\Support\Money::format($p->discount) }}"
                  data-notes="{{ $p->notes ?: '—' }}">
                <td class="muted">{{ $p->date->format('Y-m-d') }}</td>
                <td class="num">{{ \App\Support\Money::format($p->amount) }}</td>
                <td class="muted">{{ $p->discount > 0 ? \App\Support\Money::format($p->discount) : '—' }}</td>
                <td class="muted">{{ $p->notes ?: '—' }}</td>
                <td>
                  <form method="POST" action="{{ route('worker-payments.destroy', $p) }}" onsubmit="return confirm('حذف هذه الدفعة؟')">
                    @csrf @method('DELETE')
                    <button class="btn ghost sm" style="color:var(--neg)">حذف</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td>الإجمالي</td>
              <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    @else
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
        <h4>لسه مفيش دفعات مسجلة</h4>
      </div>
    @endif
  </div>
</div>

{{-- تبديل الفني — متاح طول ما لسه فاضل مستحق يتسلّم لحد تاني --}}
@if($remaining > 0)
<details class="form-card" style="max-width:none;margin-top:20px" {{ $errors->hasAny(['new_name', 'new_phone', 'new_amount']) ? 'open' : '' }}>
  <summary style="cursor:pointer;font-weight:700;display:flex;align-items:center;gap:8px;list-style:none">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" style="color:var(--accent,#4f46e5)"><use href="#i-users"/></svg>
    تبديل الفني (مشي بدري وحد تاني هيكمّل)
  </summary>
  <p class="muted" style="margin:10px 0 14px;font-size:13px;line-height:1.7">
    لو <strong>{{ $worker->name }}</strong> عمل جزء من الشغل واستلم دفعاته ومشي، هنثبّت تعاقده على اللي استلمه فعلاً
    (<strong>{{ \App\Support\Money::format($paid) }} ج.م</strong> — يبقى مستحقه صفر)، وهنضيف فني جديد يكمّل الباقي
    (<strong>{{ \App\Support\Money::format($remaining) }} ج.م</strong>) في نفس البند من غير ما يتغيّر إجمالي البند أو تتمسح دفعات حد.
  </p>
  <form method="POST" action="{{ route('workers.swap', $worker) }}"
        onsubmit="return confirm('تأكيد التبديل؟\n{{ $worker->name }} هيتثبّت على {{ \App\Support\Money::format($paid) }} ج.م، والفني الجديد هياخد الباقي.');">
    @csrf
    <div class="row2">
      <div class="field">
        <label>اسم الفني الجديد *</label>
        <input type="text" name="new_name" value="{{ old('new_name') }}" placeholder="مثال: عم محمود" required>
      </div>
      <div class="field">
        <label>موبايل الفني الجديد</label>
        <input type="text" name="new_phone" value="{{ old('new_phone') }}" placeholder="اختياري">
      </div>
    </div>
    <div class="row2">
      <div class="field">
        <label>المبلغ المتعاقد عليه للفني الجديد (ج.م) *</label>
        <input type="number" name="new_amount" value="{{ old('new_amount', number_format($remaining, 2, '.', '')) }}" min="0.01" step="0.01" required>
        <p class="muted" style="margin-top:4px;font-size:12px">افتراضياً = المتبقّي من التعاقد، وتقدر تعدّله لو اتفقت على مبلغ تاني.</p>
      </div>
      <div class="field">
        <label>تاريخ التبديل *</label>
        <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
      </div>
    </div>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow"/></svg>
        تبديل وتسليم الباقي للفني الجديد
      </button>
    </div>
  </form>
</details>
@endif

{{-- بوب أب تفاصيل الدفعة --}}
<div class="rv-modal" id="payDetailModal" onclick="if(event.target===this) closePayDetail()">
  <div class="rv-card" style="max-width:420px;margin:20px;background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.1);padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #eee;padding-bottom:12px">
      <h3 style="margin:0;font-size:1.1rem">تفاصيل الدفعة</h3>
      <button class="btn ghost sm" onclick="closePayDetail()" style="padding:4px 8px"><i class="fa fa-times"></i></button>
    </div>
    <div style="display:grid;grid-template-columns:100px 1fr;gap:12px;font-size:0.95rem">
      <div style="color:var(--muted);font-weight:600">التاريخ:</div><div id="pd-date" style="font-weight:700"></div>
      <div style="color:var(--muted);font-weight:600">المبلغ:</div><div id="pd-amount" style="font-weight:700;color:var(--pos)"></div>
      <div style="color:var(--muted);font-weight:600" id="pd-disc-lbl">الخصم:</div><div id="pd-discount" style="font-weight:700;color:var(--warning)"></div>
      <div style="color:var(--muted);font-weight:600">ملاحظات:</div><div id="pd-notes" style="font-weight:600"></div>
    </div>
  </div>
</div>

<style>
.rv-modal { position:fixed; inset:0; z-index:1060; display:none; align-items:center; justify-content:center; background:rgba(15,23,42,.55); }
.rv-modal.open { display:flex; }
.pay-btn { transition: 0.2s; }
.pay-btn.main { background: var(--accent); color: white; border-color: var(--accent); }
</style>

<script>
function setPayAmount(amt, type) {
  document.getElementById('pay_amt').value = amt;
  document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('main'));
  document.getElementById('btn-' + type).classList.add('main');
}
function showPayDetail(row) {
  const d = row.dataset;
  document.getElementById('pd-date').textContent = d.date;
  document.getElementById('pd-amount').textContent = d.amount + ' ج.م';
  const disc = parseFloat(d.discount);
  if (disc > 0) {
    document.getElementById('pd-discount').textContent = d.discount + ' ج.م';
    document.getElementById('pd-disc-lbl').style.display = 'block';
    document.getElementById('pd-discount').style.display = 'block';
  } else {
    document.getElementById('pd-disc-lbl').style.display = 'none';
    document.getElementById('pd-discount').style.display = 'none';
  }
  document.getElementById('pd-notes').textContent = d.notes;
  document.getElementById('payDetailModal').classList.add('open');
}
function closePayDetail() {
  document.getElementById('payDetailModal').classList.remove('open');
}
</script>
@endsection
