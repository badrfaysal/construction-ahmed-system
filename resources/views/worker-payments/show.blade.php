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
          <input type="number" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" placeholder="{{ number_format($remaining, 2, '.', '') }}" required>
          @if($remaining > 0)<p class="muted" style="margin-top:4px;font-size:12px">المتبقي: {{ \App\Support\Money::format($remaining) }} ج.م</p>@endif
        </div>
        <div class="field">
          <label>التاريخ *</label>
          <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
        </div>
      </div>
      <div class="row2">
        <div class="field">
          <label>طريقة الدفع</label>
          <input type="text" name="method" value="{{ old('method') }}" placeholder="كاش / تحويل...">
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
              <th>الطريقة</th>
              <th>ملاحظات</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($worker->payments as $p)
              <tr>
                <td class="muted">{{ $p->date->format('Y-m-d') }}</td>
                <td class="num">{{ \App\Support\Money::format($p->amount) }}</td>
                <td class="muted">{{ $p->method ?: '—' }}</td>
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

@endsection
