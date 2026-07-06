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
    <div class="val tnum" style="color:var(--neg)">{{ number_format($totals['remaining']) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">تم سداده حتى الآن</span></div>
    <div class="val tnum" style="color:var(--pos)">{{ number_format($totals['paid_so_far']) }} <small>ج.م</small></div>
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
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
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
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
    </div>
  </div>
  <div class="f-field">
    <label>الحالة</label>
    <div class="tabs" style="margin-bottom:0">
      <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="tab {{ !request('status') ? 'active' : '' }}">غير مسدد فقط</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="tab {{ request('status') === 'pending' ? 'active' : '' }}">معلق</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'partial']) }}" class="tab {{ request('status') === 'partial' ? 'active' : '' }}">جزئي</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'paid']) }}" class="tab {{ request('status') === 'paid' ? 'active' : '' }}">مسدد</a>
    </div>
  </div>
  @if(request()->hasAny(['project_id','supplier_id','status']))
    <div class="f-actions">
      <a href="{{ route('debts.index') }}" class="btn ghost sm">مسح الفلتر</a>
    </div>
  @endif
</form>

<div class="table-card">
  @if($debts->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>البيان</th>
            <th>المشروع</th>
            <th>البند</th>
            <th>المورد</th>
            <th class="num">إجمالي الدين</th>
            <th class="num">المسدد</th>
            <th class="num">المتبقي</th>
            <th>الاستحقاق</th>
            <th>الحالة</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($debts as $debt)
            <tr @if($debt->isOverdue()) style="background:rgba(239,68,68,.05)" @endif>
              <td><strong>{{ $debt->description }}</strong></td>
              <td><span class="tag gray">{{ $debt->project?->name ?? '—' }}</span></td>
              <td class="muted">{{ $debt->band?->name ?? '—' }}</td>
              <td class="muted">{{ $debt->supplier?->name ?? '—' }}</td>
              <td class="num">{{ number_format($debt->total_amount) }}</td>
              <td class="num" style="color:var(--pos)">{{ number_format($debt->paid_amount) }}</td>
              <td class="num" style="color:var(--neg)"><strong>{{ number_format($debt->remaining()) }}</strong></td>
              <td class="muted">
                @if($debt->due_date)
                  <span @if($debt->isOverdue()) style="color:var(--neg)" @endif>{{ $debt->due_date->format('Y-m-d') }}</span>
                  @if($debt->isOverdue()) <span class="tag red">متأخر</span> @endif
                @else
                  —
                @endif
              </td>
              <td><span class="tag {{ $debt->statusTag() }}">{{ $debt->statusAr() }}</span></td>
              <td>
                @if($debt->status !== 'paid')
                  <button class="btn pos sm" onclick="openPayModal({{ $debt->id }}, {{ $debt->remaining() }}, '{{ addslashes($debt->description) }}')">سداد</button>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div style="padding:14px 18px;border-top:1px solid var(--line)">{{ $debts->withQueryString()->links() }}</div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check-circle"/></svg>
      <h4>لا توجد ديون</h4>
      <p>لا يوجد مستحق على الشركة للموردين حالياً</p>
    </div>
  @endif
</div>

{{-- Pay Modal --}}
<div id="pay-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <div style="background:var(--surface);border-radius:12px;padding:28px;width:min(440px,96vw)">
    <h4 style="margin:0 0 4px">سداد دين</h4>
    <p id="pay-desc" class="muted" style="margin:0 0 20px;font-size:.85rem"></p>
    <form id="pay-form" method="POST">
      @csrf
      <div class="field">
        <label>المبلغ المدفوع (ج.م) *</label>
        <input type="number" name="amount" id="pay-amount" min="0.01" step="0.01" required>
        <small class="muted" id="pay-max-note"></small>
      </div>
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

@push('scripts')
<script>
function openPayModal(id, remaining, desc) {
  document.getElementById('pay-desc').textContent = desc;
  document.getElementById('pay-amount').max = remaining;
  document.getElementById('pay-amount').value = remaining;
  document.getElementById('pay-max-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  document.getElementById('pay-form').action = '/debts/' + id + '/pay';
  document.getElementById('pay-modal').style.display = 'flex';
}
</script>
@endpush
@endsection
