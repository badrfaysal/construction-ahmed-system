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
    <div class="val tnum" style="color:var(--neg)">{{ \App\Support\Money::format($totals['remaining'] + $manualTotals['remaining']) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">تم سداده حتى الآن</span></div>
    <div class="val tnum" style="color:var(--pos)">{{ \App\Support\Money::format($totals['paid_so_far'] + $manualTotals['paid_so_far']) }} <small>ج.م</small></div>
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
    <div class="tabs">
      <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="tab {{ !request('status') ? 'active' : '' }}">غير مسدد</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="tab {{ request('status') === 'pending' ? 'active' : '' }}">معلق</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'partial']) }}" class="tab {{ request('status') === 'partial' ? 'active' : '' }}">جزئي</a>
      <a href="{{ request()->fullUrlWithQuery(['status' => 'paid']) }}" class="tab {{ request('status') === 'paid' ? 'active' : '' }}">مسدد</a>
    </div>
  </div>
  <div class="f-field" style="min-width: auto; flex: 0 0 auto;">
    <label>التاريخ</label>
    <div class="btn ghost" id="date-filter-btn" style="height: 42px; padding: 0 16px; border: 1px solid var(--line); background: var(--surface-2); color: var(--ink); box-shadow: none; position: relative; cursor: pointer;" title="تصفية بالتاريخ">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-calendar"/></svg>
      @if(request('date_from') || request('date_to'))
        <span style="position: absolute; top: -3px; right: -3px; width: 10px; height: 10px; background: var(--pos); border-radius: 50%; border: 2px solid #fff;"></span>
      @endif
    </div>
    <input type="hidden" name="date_from" id="date_from" value="{{ request('date_from') }}">
    <input type="hidden" name="date_to" id="date_to" value="{{ request('date_to') }}">
    <input type="hidden" name="tab" id="tab_input" value="{{ request('tab') }}">
  </div>
  @include('partials._sort-select', ['options' => [
    'newest'      => 'الأحدث إضافة',
    'due_asc'     => 'الأقرب استحقاقًا',
    'amount_desc' => 'الأعلى قيمة',
    'amount_asc'  => 'الأقل قيمة',
  ]])
  @if(request()->hasAny(['project_id','supplier_id','status','sort','date_from','date_to']))
    <div class="f-actions">
      <a href="{{ route('debts.index') }}" class="btn ghost sm">مسح الفلتر</a>
    </div>
  @endif
  <style>
    .filter-bar { 
      display: flex; 
      flex-wrap: nowrap;
      gap: 12px; 
      align-items: flex-end; 
      padding: 16px 20px; 
      background: var(--surface); 
      border: 1px solid var(--line); 
      border-radius: 12px; 
      margin-bottom: 24px; 
      box-shadow: 0 2px 8px rgba(0,0,0,0.02);
      overflow-x: auto;
    }
    .filter-bar::-webkit-scrollbar { height: 4px; }
    .filter-bar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    
    .filter-bar .f-field { 
      flex: 1; 
      min-width: 140px; 
      margin-bottom: 0 !important; 
    }
    
    .filter-bar .f-actions { 
      display: flex; 
      flex: 0 0 auto;
      align-items: center; 
      margin-inline-start: auto; 
    }
    
    .filter-bar .tabs { 
      height: 42px; 
      border: 1px solid var(--line); 
      border-radius: 8px; 
      padding: 4px; 
      background: var(--surface-2); 
      display: flex; 
      gap: 4px; 
      flex-wrap: nowrap; 
      margin-bottom: 0 !important; 
    }
    .filter-bar .tabs .tab { 
      flex: 1; 
      margin: 0; 
      text-align: center; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      padding: 0 8px; 
      font-size: 13px; 
      font-weight: 600; 
      white-space: nowrap; 
      border-radius: 6px; 
      border: none; 
      text-decoration: none; 
      color: var(--ink-2); 
      transition: all 0.2s ease; 
    }
    .filter-bar .tabs .tab:hover { color: var(--ink); background: var(--surface); }
    .filter-bar .tabs .tab.active { 
      background: var(--surface); 
      color: var(--neg); 
      box-shadow: 0 1px 3px rgba(0,0,0,0.06); 
      font-weight: 700; 
    }
    .filter-bar .tabs .tab[href*="pending"].active { color: var(--warn); }
    .filter-bar .tabs .tab[href*="partial"].active { color: var(--accent); }
    .filter-bar .tabs .tab[href*="paid"].active { color: var(--pos); }
    
    .filter-bar .f-select, .filter-bar input[type="date"] { 
      height: 42px; 
      line-height: normal; 
      padding: 0 12px; 
      border: 1px solid var(--line); 
      border-radius: 8px; 
      background: var(--surface-2); 
      font-size: 13px;
      font-weight: 600;
      color: var(--ink);
      transition: 0.14s;
    }
    .filter-bar .f-select:hover, .filter-bar input[type="date"]:hover {
      border-color: #cfd8e3;
      background: var(--surface);
    }
    .filter-bar .f-select:focus, .filter-bar input[type="date"]:focus { 
      border-color: var(--accent); 
      box-shadow: 0 0 0 3px var(--accent-soft); 
      background: var(--surface);
    }
    .filter-bar .btn { 
      height: 42px; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      padding: 0 24px; 
      margin: 0; 
      font-weight: 700; 
      font-size: 13px; 
      border-radius: 8px; 
    }
  </style>
</form>

<div class="tabs-container" style="margin-bottom: 20px;">
  <div class="tabs" style="border-bottom: 1px solid var(--line); display:flex; gap:16px;">
    <div class="tab-btn {{ !request('tab') || request('tab') === 'supplier' ? 'active' : '' }}" id="tab-btn-supplier" onclick="switchTab('supplier-tab')" style="padding: 10px 16px; cursor:pointer; border-bottom: 2px solid {{ !request('tab') || request('tab') === 'supplier' ? 'var(--brand)' : 'transparent' }}; font-weight:bold; color:{{ !request('tab') || request('tab') === 'supplier' ? 'var(--brand)' : 'var(--mut)' }}">ديون الموردين</div>
    <div class="tab-btn {{ request('tab') === 'manual' ? 'active' : '' }}" id="tab-btn-manual" onclick="switchTab('manual-tab')" style="padding: 10px 16px; cursor:pointer; border-bottom: 2px solid {{ request('tab') === 'manual' ? 'var(--brand)' : 'transparent' }}; font-weight:bold; color:{{ request('tab') === 'manual' ? 'var(--brand)' : 'var(--mut)' }}">عهد وديون أخرى</div>
  </div>
</div>

<div id="supplier-tab" class="tab-content" style="display:{{ !request('tab') || request('tab') === 'supplier' ? 'block' : 'none' }};">
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
      <div class="supplier-debt-head" style="cursor:pointer;transition:background 0.2s" onclick="toggleSupplierDebts('{{ $supplierId }}')" onmouseover="this.style.background='var(--surface-hover)'" onmouseout="this.style.background=''">
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
          @if($hasUnpaid)
            <div style="display:flex;gap:6px;margin-right:8px">
              <button class="btn sm" style="background:#198754; color:#fff; border:none;" onclick="event.stopPropagation(); openSupplierPay({{ $supplierId }}, {{ $sRemaining }}, '{{ addslashes($supplier?->name ?? 'بدون مورد') }}', 'full')">
                سداد كلي
              </button>
              <button class="btn ghost sm" onclick="event.stopPropagation(); openSupplierPay({{ $supplierId }}, {{ $sRemaining }}, '{{ addslashes($supplier?->name ?? 'بدون مورد') }}', 'partial')">
                سداد جزئي
              </button>
            </div>
          @endif
        </div>
      </div>
      {{-- Supplier Debts Table --}}
      <div class="table-scroll" id="supplier-debts-{{ $supplierId }}" style="display:none;margin-top:-1px;border-top-left-radius:0;border-top-right-radius:0;">
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
  <div class="table-card" style="margin-bottom:20px">
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check-circle"/></svg>
      <h4>لا توجد ديون موردين</h4>
    </div>
  </div>
@endif

</div> <!-- end supplier-tab -->

<div id="manual-tab" class="tab-content" style="display:{{ request('tab') === 'manual' ? 'block' : 'none' }};">
@if(isset($manualDebts) && $manualDebts->count() > 0)
  <h4 style="margin:20px 0 10px; border-bottom:1px solid var(--border); padding-bottom:8px;">عهد وديون أخرى (حركات يدوية)</h4>
  @php $groupedDebts = $manualDebts->groupBy('party'); @endphp
  <div class="table-scroll">
    <table>
      <thead>
        <tr>
          <th>الجهة / الشخص</th>
          <th>عدد التعاملات</th>
          <th class="num">إجمالي المبلغ</th>
          <th class="num">المسدد</th>
          <th class="num">المتبقي</th>
          <th>الحالة</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($groupedDebts as $partyName => $partyItems)
          @php
            $partyTotal     = $partyItems->sum('total_amount');
            $partyPaid      = $partyItems->sum('paid_amount');
            $partyRemaining = $partyItems->sum(fn($r) => $r->remaining());
            $partyCount     = $partyItems->count();
            $allPaid        = $partyItems->every(fn($r) => $r->status === 'paid');
            $partyKey       = 'mdebt-' . md5($partyName);
          @endphp
          <tr data-status="{{ $allPaid ? 'paid' : 'pending' }}" style="cursor:pointer; background: {{ $allPaid ? 'var(--bg-main)' : '' }}" onclick="openPartyModal('{{ $partyKey }}')">
            <td><strong>{{ $partyName }}</strong></td>
            <td><span style="background:var(--bg2);padding:2px 10px;border-radius:6px;font-size:.75rem;font-weight:700">{{ $partyCount }}</span></td>
            <td class="num">{{ \App\Support\Money::format($partyTotal) }}</td>
            <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($partyPaid) }}</td>
            <td class="num" style="color:var(--neg)"><strong>{{ \App\Support\Money::format($partyRemaining) }}</strong></td>
            <td>
              @if($allPaid)
                <span class="tag green sm">مسدد بالكامل</span>
              @else
                <span class="tag yellow sm">معلق</span>
              @endif
            </td>
            <td>
              <button class="btn ghost sm" style="font-size:0.75rem">التفاصيل</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

{{-- توليد المودلز الخاصة بتفاصيل كل جهة/شخص للعهد والديون --}}
@foreach($groupedDebts as $partyName => $partyItems)
  @php
    $partyTotal     = $partyItems->sum('total_amount');
    $partyPaid      = $partyItems->sum('paid_amount');
    $partyRemaining = $partyItems->sum(fn($r) => $r->remaining());
    $partyKey       = 'mdebt-' . md5($partyName);
  @endphp
  <div class="rv-modal" id="modal-{{ $partyKey }}" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.5);align-items:center;justify-content:center" onclick="if(event.target===this) document.getElementById('modal-{{ $partyKey }}').style.display='none'">
    <div class="rv-card" style="background:var(--surface);border-radius:14px;width:min(800px,96vw);max-height:90vh;overflow-y:auto;padding:0;">
      <div class="rv-mhead" style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:10px;">
          <h3 style="margin:0; font-size:1.2rem; color:var(--text); display:flex; align-items:center; gap:8px;">
            <i class="fa fa-user-circle" style="color:var(--main)"></i> {{ $partyName }}
          </h3>
          <div style="display:flex; gap:10px; margin-right:15px;">
            <button type="button" style="background:var(--warn); color:#000; padding:4px 12px; border:none; border-radius:6px; font-weight:bold; cursor:pointer;" onclick="openPartyBulkPay('{{ addslashes($partyName) }}', {{ $partyRemaining }}, 'partial', '{{ $partyKey }}')">
              سداد جزئي
            </button>
            <button type="button" style="background:#198754; color:#fff; padding:4px 12px; border:none; border-radius:6px; font-weight:bold; cursor:pointer;" onclick="openPartyBulkPay('{{ addslashes($partyName) }}', {{ $partyRemaining }}, 'full', '{{ $partyKey }}')">
              سداد كلي للعميل
            </button>
          </div>
        </div>
        <div style="display:flex; align-items:center; gap:15px;">
          <div style="display:flex; gap:10px;">
            <span style="background:var(--pos); color:#fff; padding:3px 10px; border-radius:4px; font-size:0.8rem; font-weight:bold;">
              المسدد: {{ \App\Support\Money::format($partyPaid) }} ج.م
            </span>
            <span style="background:var(--neg); color:#fff; padding:3px 10px; border-radius:4px; font-size:0.8rem; font-weight:bold;">
              المتبقي: {{ \App\Support\Money::format($partyRemaining) }} ج.م
            </span>
          </div>
          <button type="button" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--soft)" onclick="document.getElementById('modal-{{ $partyKey }}').style.display='none'">×</button>
        </div>
      </div>
      <div class="rv-mbody" style="padding:0;">
        <table style="margin:0; border-radius:0; border:none; box-shadow:none;">
          <thead>
            <tr style="background:#f8f9fa;">
              <th>التاريخ</th>
              <th>البيان</th>
              <th class="num">المبلغ</th>
              <th class="num">المسدد</th>
              <th class="num">المتبقي</th>
              <th>الحالة</th>
              <th>إجراءات الدفع</th>
            </tr>
          </thead>
          <tbody>
            @foreach($partyItems as $debt)
              <tr>
                <td class="muted">{{ $debt->date->format('Y-m-d') }}</td>
                <td class="muted"><strong>{{ $debt->description ?: '—' }}</strong></td>
                <td class="num" style="font-weight:600;">{{ \App\Support\Money::format($debt->total_amount) }}</td>
                <td class="num" style="color:var(--pos); font-weight:600;">{{ \App\Support\Money::format($debt->paid_amount) }}</td>
                <td class="num" style="color:var(--neg); font-weight:700;"><strong>{{ \App\Support\Money::format($debt->remaining()) }}</strong></td>
                <td><span class="tag {{ $debt->statusTag() }} sm">{{ $debt->statusAr() }}</span></td>
                <td>
                  @if($debt->status !== 'paid')
                    <button class="btn ghost sm" onclick="
                      document.getElementById('modal-{{ $partyKey }}').style.display='none';
                      openManualPayModal({{ $debt->id }}, {{ $debt->remaining() }}, '{{ addslashes($debt->party . ($debt->description ? ' - ' . $debt->description : '')) }}')
                    ">
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
  </div>
@endforeach

@endif
</div> <!-- end manual-tab -->

{{-- Pay Single Debt Modal --}}
<div id="pay-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <div style="background:var(--surface);border-radius:14px;padding:28px;width:min(460px,96vw)">
    <h4 style="margin:0 0 4px">سداد فاتورة</h4>
    <p id="pay-desc" class="muted" style="margin:0 0 20px;font-size:.85rem"></p>
    <form id="pay-form" method="POST" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
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
    <form id="supplier-pay-form" method="POST" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
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

{{-- Pay Manual Debt Modal --}}
<div id="manual-pay-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <div style="background:var(--surface);border-radius:14px;padding:28px;width:min(460px,96vw)">
    <h4 style="margin:0 0 4px">سداد عهدة / دين أخرى</h4>
    <p id="manual-pay-desc" class="muted" style="margin:0 0 20px;font-size:.85rem"></p>
    <form id="manual-pay-form" method="POST" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
      @csrf
      <div class="field">
        <label style="margin-bottom:8px;display:block">المبلغ المسدد (ج.م) *</label>
        <div style="display:flex;gap:10px;margin-bottom:12px">
          <button type="button" class="btn sm" style="background:#198754; color:#fff; border:none;" id="manual-pay-full-btn" onclick="
            document.getElementById('manual-pay-amount').value = document.getElementById('manual-pay-amount').max;
            document.getElementById('manual-pay-amount').readOnly = true;
            this.style.background = '#198754'; this.style.color = '#fff';
            document.getElementById('manual-pay-partial-btn').style.background = '';
            document.getElementById('manual-pay-partial-btn').classList.add('ghost');
          ">سداد كلي</button>
          
          <button type="button" class="btn ghost sm" id="manual-pay-partial-btn" onclick="
            document.getElementById('manual-pay-amount').value = '';
            document.getElementById('manual-pay-amount').readOnly = false;
            document.getElementById('manual-pay-amount').focus();
            this.classList.add('warn'); this.classList.remove('ghost');
            document.getElementById('manual-pay-full-btn').classList.add('ghost');
            document.getElementById('manual-pay-full-btn').classList.remove('pos');
          ">سداد جزئي</button>
        </div>
        <input type="number" name="amount" id="manual-pay-amount" min="0.01" step="0.01" required readonly>
        <small class="muted" id="manual-pay-max-note"></small>
      </div>
      @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true])
      <div class="field">
        <label>تاريخ السداد *</label>
        <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required>
      </div>
      <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn pos">تسجيل السداد</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('manual-pay-modal').style.display='none'">إلغاء</button>
      </div>
    </form>
  </div>
</div>

{{-- Pay Manual Debt Party (Bulk) Modal --}}
<div id="manual-party-pay-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.5);align-items:center;justify-content:center" onclick="if(event.target===this) this.style.display='none'">
  <div style="background:var(--surface);border-radius:14px;padding:28px;width:min(460px,96vw)">
    <h4 style="margin:0 0 4px">سداد ديون <span id="manual-party-pay-name"></span></h4>
    <form id="manual-party-pay-form" method="POST" action="{{ route('debts.manual.party.pay') }}" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
      @csrf
      <input type="hidden" name="party_name" id="manual-party-pay-party">
      <div class="field" style="margin-top:20px">
        <label>المبلغ المسدد (ج.م) *</label>
        <input type="number" name="amount" id="manual-party-pay-amount" min="0.01" step="0.01" required>
        <small class="muted" id="manual-party-pay-max-note"></small>
      </div>
      @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true, 'fieldName' => 'account_id'])
      <div class="field">
        <label>تاريخ السداد *</label>
        <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required>
      </div>
      <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn pos">تسجيل السداد للعميل</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('manual-party-pay-modal').style.display='none'">إلغاء</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof flatpickr !== 'undefined') {
    flatpickr("#date-filter-btn", {
      mode: "range",
      locale: "ar",
      defaultDate: [
        "{{ request('date_from') }}",
        "{{ request('date_to') }}"
      ],
      onClose: function(selectedDates) {
        if (selectedDates.length === 2) {
          const formatDate = (date) => {
            let d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
          }
          let newFrom = formatDate(selectedDates[0]);
          let newTo = formatDate(selectedDates[1]);
          
          let currentFrom = document.getElementById('date_from').value;
          let currentTo = document.getElementById('date_to').value;
          
          if (newFrom !== currentFrom || newTo !== currentTo) {
            document.getElementById('date_from').value = newFrom;
            document.getElementById('date_to').value = newTo;
            document.getElementById('date_from').form.submit();
          }
        }
      }
    });
  }
});

function openPartyModal(key) {
  const modal = document.getElementById('modal-' + key);
  if (modal) {
    modal.style.display = 'flex';
  }
}

function openManualPayModal(id, remaining, desc) {
  document.getElementById('manual-pay-desc').textContent = desc;
  document.getElementById('manual-pay-amount').max = remaining;
  document.getElementById('manual-pay-amount').value = remaining;
  document.getElementById('manual-pay-amount').readOnly = true; // Default to full
  
  const fullBtn = document.getElementById('manual-pay-full-btn');
  const partBtn = document.getElementById('manual-pay-partial-btn');
  if(fullBtn && partBtn) {
    fullBtn.className = 'btn pos sm';
    partBtn.className = 'btn ghost sm';
  }

  document.getElementById('manual-pay-max-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  document.getElementById('manual-pay-form').action = '/debts/manual/' + id + '/pay';
  
  // Reset submit button state in case it was disabled previously
  const submitBtn = document.querySelector('#manual-pay-form button[type=submit]');
  if(submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'تسجيل الدفع'; }

  const walletSelect = document.querySelector('#manual-pay-form select[name="account_id"]');
  if (walletSelect) walletSelect.selectedIndex = 0;
  document.getElementById('manual-pay-modal').style.display = 'flex';
}

function openPartyBulkPay(partyName, remaining, mode, partyKey) {
  // Hide party modal
  document.getElementById('modal-' + partyKey).style.display = 'none';
  
  document.getElementById('manual-party-pay-name').textContent = partyName;
  document.getElementById('manual-party-pay-party').value = partyName;
  document.getElementById('manual-party-pay-amount').max = remaining;
  document.getElementById('manual-party-pay-amount').value = mode === 'full' ? remaining : '';
  document.getElementById('manual-party-pay-max-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  
  const submitBtn = document.querySelector('#manual-party-pay-form button[type=submit]');
  if(submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = 'تسجيل السداد للعميل'; }

  document.getElementById('manual-party-pay-modal').style.display = 'flex';
}

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

function toggleSupplierDebts(supplierId) {
  const el = document.getElementById('supplier-debts-' + supplierId);
  if (el.style.display === 'none') {
    el.style.display = 'block';
  } else {
    el.style.display = 'none';
  }
}

function switchTab(tabId) {
  // Hide all tab content
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  
  // Remove active class from all tab buttons
  document.querySelectorAll('.tab-btn').forEach(el => {
    el.classList.remove('active');
    el.style.borderBottomColor = 'transparent';
    el.style.color = 'var(--mut)';
  });
  
  // Show target tab
  document.getElementById(tabId).style.display = 'block';
  
  // Add active styling to clicked button
  const btnId = tabId === 'supplier-tab' ? 'tab-btn-supplier' : 'tab-btn-manual';
  const btn = document.getElementById(btnId);
  btn.classList.add('active');
  btn.style.borderBottomColor = 'var(--brand)';
  btn.style.color = 'var(--brand)';
  
  if(document.getElementById('tab_input')) {
    document.getElementById('tab_input').value = tabId === 'manual-tab' ? 'manual' : 'supplier';
  }
}
</script>
@endpush
@endsection
