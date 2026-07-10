@extends('layouts.app')
@section('title', 'الديون — ما علينا للموردين')
@section('page-title', 'الديون')

@section('content')
<div class="page-head">
  <div><h3>الديون</h3><p>المبالغ المستحقة على الشركة للموردين (شراء آجل أو جزئي)</p></div>
</div>

{{-- Summary KPIs --}}
<div class="grid cols-3" style="margin-bottom:24px">
  <div class="card stat">
    <div class="top"><span class="label">إجمالي الديون المتبقية</span></div>
    <div class="val tnum" style="color:var(--neg)">{{ \App\Support\Money::format($totals['remaining']) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">تم سداده حتى الآن</span></div>
    <div class="val tnum" style="color:var(--pos)">{{ \App\Support\Money::format($totals['paid_so_far']) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">ديون متأخرة</span></div>
    <div class="val tnum" style="color:var(--warn)">{{ $totals['overdue_count'] }} <small>بند</small></div>
  </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
  <div class="f-field">
    <label>المشروع</label>
    <div class="f-select-wrap">
      <select name="project_id" class="f-select" onchange="this.form.submit()">
        <option value="">كل المشاريع</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-down"/></svg>
    </div>
  </div>
  <div class="f-field">
    <label>المورد</label>
    <div class="f-select-wrap">
      <select name="supplier_id" class="f-select" onchange="this.form.submit()">
        <option value="">كل الموردين</option>
        @foreach($suppliers as $s)
          <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-down"/></svg>
    </div>
  </div>
  <div class="f-field">
    <label>الحالة</label>
    <div class="tabs" style="margin-bottom:0">
      <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="tab {{ !request('status') ? 'active' : '' }}">غير مسدد</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="tab {{ request('status') === 'pending' ? 'active' : '' }}">معلق</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'partial']) }}" class="tab {{ request('status') === 'partial' ? 'active' : '' }}">جزئي</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'paid']) }}" class="tab {{ request('status') === 'paid' ? 'active' : '' }}">مسدد</a>
    </div>
  </div>
  @include('partials._sort-select', ['options' => [
    'due_asc'     => 'الأقرب استحقاقًا',
    'newest'      => 'الأحدث إضافة',
    'amount_desc' => 'الأعلى قيمة',
    'amount_asc'  => 'الأقل قيمة',
  ]])
  @if(request()->hasAny(['project_id','supplier_id','status','sort']))
    <div class="f-actions">
      <a href="{{ route('debts.index') }}" class="btn ghost sm">مسح الفلتر</a>
    </div>
  @endif
</form>

@if($debts->count())
  @php $bySupplier = $debts->groupBy(fn($d) => $d->supplier_id ?? 0); @endphp

  @foreach($bySupplier as $supplierId => $supplierDebts)
    @php
      $supplier = $supplierDebts->first()->supplier;
      $sTotal     = $supplierDebts->sum('total_amount');
      $sPaid      = $supplierDebts->sum('paid_amount');
      $sRemaining = $supplierDebts->sum(fn($d) => $d->remaining());
      $hasUnpaid  = $supplierDebts->filter(fn($d) => $d->status !== 'paid')->count() > 0;
    @endphp

    <div class="supplier-debt-group" style="margin-bottom:20px">
      {{-- Supplier Header --}}
      <div class="supplier-debt-head">
        <div style="display:flex;align-items:center;gap:12px;flex:1">
          <div class="supplier-ic">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-box"/></svg>
          </div>
          <div>
            <div style="font-size:16px;font-weight:700;margin-bottom:2px">{{ $supplier?->name ?? 'بدون مورد' }}</div>
            <div class="muted" style="font-size:12px">{{ $supplierDebts->count() }} {{ $supplierDebts->count() === 1 ? 'فاتورة' : 'فواتير' }}</div>
          </div>
        </div>
        <div class="supplier-fin-row">
          <div style="text-align:center">
            <div class="muted" style="font-size:11px">إجمالي</div>
            <div class="tnum" style="font-weight:700">{{ \App\Support\Money::format($sTotal) }}</div>
          </div>
          <div style="text-align:center">
            <div class="muted" style="font-size:11px">مسدد</div>
            <div class="tnum" style="font-weight:700;color:var(--pos)">{{ \App\Support\Money::format($sPaid) }}</div>
          </div>
          <div style="text-align:center">
            <div class="muted" style="font-size:11px">متبقي</div>
            <div class="tnum" style="font-weight:700;color:{{ $sRemaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($sRemaining) }}</div>
          </div>
          @if($hasUnpaid && $supplierId > 0)
            <div style="display:flex;gap:6px;margin-right:8px">
              <button class="btn pos sm" onclick="openSupplierPay({{ $supplierId }}, {{ $sRemaining }}, '{{ addslashes($supplier?->name) }}', 'full')">
                سداد كلي
              </button>
              <button class="btn ghost sm" onclick="openSupplierPay({{ $supplierId }}, {{ $sRemaining }}, '{{ addslashes($supplier?->name) }}', 'partial')">
                سداد جزئي
              </button>
            </div>
          @endif
        </div>
      </div>

      {{-- Supplier Debts Table --}}
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>البيان / الفاتورة</th>
              <th>المشروع</th>
              <th>البند</th>
              <th class="num">إجمالي الدين</th>
              <th class="num">المسدد</th>
              <th class="num">المتبقي</th>
              <th>الاستحقاق</th>
              <th>الحالة</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($supplierDebts as $debt)
              <tr @if($debt->isOverdue()) style="background:rgba(239,68,68,.04)" @endif>
                <td><strong>{{ $debt->description }}</strong></td>
                <td><span class="tag gray sm">{{ $debt->project?->name ?? '—' }}</span></td>
                <td class="muted">{{ $debt->band?->name ?? '—' }}</td>
                <td class="num">{{ \App\Support\Money::format($debt->total_amount) }}</td>
                <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($debt->paid_amount) }}</td>
                <td class="num" style="color:var(--neg)"><strong>{{ \App\Support\Money::format($debt->remaining()) }}</strong></td>
                <td class="muted">
                  @if($debt->due_date)
                    <span @if($debt->isOverdue()) style="color:var(--neg)" @endif>{{ $debt->due_date->format('Y-m-d') }}</span>
                    @if($debt->isOverdue()) <span class="tag red sm">متأخر</span> @endif
                  @else —
                  @endif
                </td>
                <td><span class="tag {{ $debt->statusTag() }}">{{ $debt->statusAr() }}</span></td>
                <td>
                  @if($debt->status !== 'paid')
                    <button class="btn ghost sm" onclick="openPayModal({{ $debt->id }}, {{ $debt->remaining() }}, '{{ addslashes($debt->description) }}')">
                      سداد
                    </button>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endforeach
@else
  <div class="table-card">
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check-circle"/></svg>
      <h4>لا توجد ديون</h4>
      <p>لا يوجد مستحق على الشركة للموردين حالياً</p>
    </div>
  </div>
@endif

{{-- Pay Single Debt Modal --}}
<div id="pay-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <div style="background:var(--surface);border-radius:14px;padding:28px;width:min(460px,96vw)">
    <h4 style="margin:0 0 4px">سداد فاتورة</h4>
    <p id="pay-desc" class="muted" style="margin:0 0 20px;font-size:.85rem"></p>
    <form id="pay-form" method="POST">
      @csrf
      <div class="field">
        <label>المبلغ المدفوع (ج.م) *</label>
        <input type="number" name="amount" id="pay-amount" min="0.01" step="0.01" required>
        <small class="muted" id="pay-max-note"></small>
      </div>
      @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true])
      <div class="field">
        <label>تاريخ الدفع *</label>
        <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required>
      </div>
      <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn pos">تسجيل الدفع</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('pay-modal').style.display='none'">إلغاء</button>
      </div>
    </form>
  </div>
</div>

{{-- Pay Supplier (All Debts) Modal --}}
<div id="supplier-pay-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <div style="background:var(--surface);border-radius:14px;padding:28px;width:min(460px,96vw)">
    <h4 style="margin:0 0 4px">سداد ديون المورد</h4>
    <p id="supplier-pay-name" class="muted" style="margin:0 0 20px;font-size:.85rem"></p>
    <form id="supplier-pay-form" method="POST">
      @csrf
      <div class="field">
        <label>المبلغ المدفوع (ج.م) *</label>
        <input type="number" name="amount" id="supplier-pay-amount" min="0.01" step="0.01" required>
        <small class="muted" id="supplier-pay-note"></small>
      </div>
      @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true, 'fieldName' => 'account_id'])
      <div class="field">
        <label>تاريخ الدفع *</label>
        <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required>
      </div>
      <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn pos">تسجيل الدفع</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('supplier-pay-modal').style.display='none'">إلغاء</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openPayModal(id, remaining, desc) {
  document.getElementById('pay-desc').textContent = desc;
  document.getElementById('pay-amount').max = remaining;
  document.getElementById('pay-amount').value = remaining;
  document.getElementById('pay-max-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  document.getElementById('pay-form').action = '/debts/' + id + '/pay';
  const walletSelect = document.querySelector('#pay-form select[name="account_id"]');
  if (walletSelect) walletSelect.selectedIndex = 0;
  document.getElementById('pay-modal').style.display = 'flex';
}

function openSupplierPay(supplierId, remaining, name, mode) {
  document.getElementById('supplier-pay-name').textContent =
    (mode === 'full' ? 'سداد كامل ديون المورد: ' : 'سداد جزئي لديون المورد: ') + name;
  document.getElementById('supplier-pay-amount').max = remaining;
  document.getElementById('supplier-pay-amount').value = mode === 'full' ? remaining : '';
  document.getElementById('supplier-pay-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  document.getElementById('supplier-pay-form').action = '/debts/supplier/' + supplierId + '/pay';
  const walletSelect = document.querySelector('#supplier-pay-form select[name="account_id"]');
  if (walletSelect) walletSelect.selectedIndex = 0;
  document.getElementById('supplier-pay-modal').style.display = 'flex';
}
</script>
@endpush
@endsection
