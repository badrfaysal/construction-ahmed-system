@extends('layouts.app')
@section('title', 'الديون — ما علينا للموردين')
@section('page-title', 'الديون')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ═══ تصميم الديون — الطابع الخاص والجاد ═══ */
.rv * { box-sizing:border-box; }
.rv { --ink:#1e293b; --mut:#64748b; --soft:#94a3b8; --ln:#e2e8f0; --bg2:#f8fafc;
      --ok:#047857; --okbg:#ecfdf5; --bad:#be123c; }

/* ── البطاقات والجداول ── */
.n-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    padding: 28px;
}
.n-tab {
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.n-tab.active-all { background: #4f46e5; color: #fff; }
.n-tab.active-active { background: #e11d48; color: #fff; }
.n-tab.active-paid { background: #10b981; color: #fff; }

.n-table { width: 100%; border-collapse: collapse; }
.n-table th {
    background: #f8fafc; padding: 16px 20px; font-size: 13px; font-weight: 800;
    color: #64748b; border-bottom: 1px solid #e2e8f0; text-align: center; white-space: nowrap;
}
.n-table td {
    padding: 18px 20px; border-bottom: 1px solid #f1f5f9; text-align: center;
    vertical-align: middle; font-size: 14.5px;
}
.n-table tbody tr { cursor: pointer; transition: 0.15s; }
.n-table tbody tr:hover td { background: #f8fafc; }

.n-main-tab {
    padding: 12px 24px; border: none; border-radius: 8px; font-weight: 800;
    cursor: pointer; transition: 0.2s; font-size: 14px; display: flex; align-items: center; gap: 10px;
    background: transparent; color: #64748b;
}
.n-main-tab.active { background: #ffffff; color: #0f172a; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

/* ── الطابع الخاص للتحليلات (Dashboard Style) ── */
.debt-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}
.dashboard-panel {
    background: linear-gradient(145deg, #1e1b4b 0%, #312e81 100%);
    border-radius: 16px;
    padding: 30px;
    color: #ffffff;
    box-shadow: 0 10px 15px -3px rgba(49, 46, 129, 0.3);
    position: relative;
    overflow: hidden;
}
.dashboard-panel::before {
    content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%);
    pointer-events: none;
}
.dashboard-panel-light {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px 30px;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* ── الطباعة المحسنة ── */
@media print {
    body { background: #fff !important; }
    .no-print, .debt-dashboard, .stat-cards, .page-header { display: none !important; }
    
    .print-only { display: block !important; }
    .n-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
    
    .n-table { width: 100% !important; margin: 0 !important; }
    .n-table th, .n-table td { border: 1px solid #000 !important; font-size: 12px !important; padding: 10px !important; color: #000 !important; }
    .n-table th { background: #f1f1f1 !important; color: #000 !important; -webkit-print-color-adjust: exact; }
    .n-table td span { border: none !important; background: transparent !important; color: #000 !important; padding: 0 !important; }
    
    /* إخفاء الألوان والخلفيات من الأفاتار */
    .avatar-print { background: none !important; color: #000 !important; border: 1px solid #000 !important; box-shadow: none !important; }
}

/* ═══ المودال ═══ */
.rv-modal { position:fixed; inset:0; z-index:1050; display:none; align-items:flex-start;
  justify-content:center; background:rgba(15,23,42,.55); padding:26px 12px; overflow-y:auto; }
.rv-x { background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; line-height:1; padding:2px 6px; }
.rv-x:hover { color:#fff; }
</style>
@endpush

@section('content')
<div class="rv" style="background-color: #f8fafc; min-height: 100vh; padding: 24px 32px 60px 32px; font-family: 'Cairo', sans-serif;">

@php
  // Calculate Groups and Counts for Supplier Debts
  $bySupplier = isset($debts) ? $debts->groupBy(fn($d) => $d->supplier_id ?? 0) : collect();
  $activeSuppliersCount = 0;
  $paidSuppliersCount = 0;
  
  foreach($bySupplier as $group) {
      if($group->sum(fn($d) => $d->remaining()) > 0.009) {
          $activeSuppliersCount++;
      } else {
          $paidSuppliersCount++;
      }
  }

  // Calculate Manual Debts
  $manualGrouped = isset($manualDebts) ? $manualDebts->groupBy('party') : collect();
  foreach($manualGrouped as $mGroup) {
      if($mGroup->sum(fn($r) => $r->remaining()) > 0.009) {
          $activeSuppliersCount++;
      } else {
          $paidSuppliersCount++;
      }
  }

  // Get Recent Transactions (Debts out)
  $recentActivities = \App\Models\Transaction::where('direction', 'out')
      ->whereIn('ref_type', ['debt', 'manual_debt'])
      ->orderByDesc('date')
      ->take(3)
      ->get();
@endphp

{{-- Print Header (Only visible in Print) --}}
<div class="print-only" style="display: none; text-align: center; margin-bottom: 40px; border-bottom: 2px solid #000; padding-bottom: 15px;">
    <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 12px 0;">تقرير ديون الشركة</h1>
    <div style="font-size: 15px; color: #333; font-weight: 600;">التاريخ: {{ now()->format('Y-m-d H:i') }}</div>
</div>

{{-- Header --}}
<div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 48px; margin-top: 16px;">
    {{-- Right side title (First in HTML for RTL to be on the right) --}}
    <div style="text-align: right;">
        <h2 style="margin: 0 0 16px 0; font-size: 28px; font-weight: 800; color: #0f172a; display: flex; align-items: center; justify-content: flex-start; gap: 16px;">
            ديون الشركة
            <i class="fa fa-building-columns" style="color: #4f46e5; background: #e0e7ff; padding: 12px; border-radius: 14px; box-shadow: 0 4px 6px -1px rgba(79,70,229,0.1);"></i>
        </h2>
        <p style="margin: 0; font-size: 16px; color: #64748b; font-weight: 600;">إدارة الالتزامات المالية للموردين والجهات الخارجية</p>
    </div>

    {{-- Left side tabs and filters --}}
    <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; gap: 8px; background: #e2e8f0; padding: 8px; border-radius: 12px;">
            <button id="tab-btn-supplier" onclick="switchTab('supplier-tab')" class="n-main-tab active"><i class="fa fa-boxes-stacked"></i> ديون الموردين</button>
            <button id="tab-btn-manual" onclick="switchTab('manual-tab')" class="n-main-tab"><i class="fa fa-hand-holding-dollar"></i> عهد وديون أخرى</button>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 6px; display: flex; gap: 6px;">
            <button class="n-tab" onclick="filterStatus('all', this)"><i class="fa fa-list"></i> الكل</button>
            <button class="n-tab active-active" onclick="filterStatus('active', this)"><i class="fa fa-fire"></i> النشط</button>
            <button class="n-tab" onclick="filterStatus('paid', this)"><i class="fa fa-check-circle"></i> المسدد</button>
        </div>
    </div>
</div>

{{-- 4 Stat Cards --}}
<div class="stat-cards no-print" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px;">
    <div class="n-card" style="border-right: 5px solid #4f46e5;">
        <div style="font-size: 15px; font-weight: 700; color: #64748b; margin-bottom: 12px; text-align: right;">إجمالي الديون (المتبقي علينا)</div>
        <div style="font-size: 26px; font-weight: 800; color: #0f172a; text-align: right;">{{ \App\Support\Money::format($totals['remaining'] + $manualTotals['remaining']) }} <span style="font-size: 16px; color: #64748b;">ج.م</span></div>
    </div>
    <div class="n-card" style="border-right: 5px solid #10b981;">
        <div style="font-size: 15px; font-weight: 700; color: #64748b; margin-bottom: 12px; text-align: right;">المبالغ المسددة</div>
        <div style="font-size: 26px; font-weight: 800; color: #0f172a; text-align: right;">{{ \App\Support\Money::format($totals['paid_so_far'] + $manualTotals['paid_so_far']) }} <span style="font-size: 16px; color: #64748b;">ج.م</span></div>
    </div>
    <div class="n-card" style="border-right: 5px solid #e11d48;">
        <div style="font-size: 15px; font-weight: 700; color: #64748b; margin-bottom: 12px; text-align: right;">جهات نشطة (لها ديون)</div>
        <div style="font-size: 26px; font-weight: 800; color: #0f172a; text-align: right;">{{ $activeSuppliersCount }} <span style="font-size: 16px; color: #64748b;">جهة</span></div>
    </div>
    <div class="n-card" style="border-right: 5px solid #10b981;">
        <div style="font-size: 15px; font-weight: 700; color: #64748b; margin-bottom: 12px; text-align: right;">جهات مكتملة السداد</div>
        <div style="font-size: 26px; font-weight: 800; color: #0f172a; text-align: right;">{{ $paidSuppliersCount }} <span style="font-size: 16px; color: #64748b;">جهة</span></div>
    </div>
</div>

{{-- Dashboard Section (Unique feature for Debts) --}}
<div class="debt-dashboard no-print">
    {{-- Left: Progress Panel --}}
    <div class="dashboard-panel">
        <h3 style="margin: 0 0 28px 0; font-size: 20px; font-weight: 800; display: flex; align-items: center; justify-content: space-between;">
            موقف سداد الالتزامات
            <i class="fa fa-chart-pie" style="color: #a5b4fc; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 12px;"></i>
        </h3>
        
        @php
            $totalExpected = ($totals['paid_so_far'] + $manualTotals['paid_so_far']) + ($totals['remaining'] + $manualTotals['remaining']);
            $collectedPct = $totalExpected > 0 ? round((($totals['paid_so_far'] + $manualTotals['paid_so_far']) / $totalExpected) * 100, 1) : 0;
            $remainingPct = $totalExpected > 0 ? round((($totals['remaining'] + $manualTotals['remaining']) / $totalExpected) * 100, 1) : 0;
        @endphp
        
        <div style="margin-bottom: 32px;">
            <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: 700; color: #e0e7ff; margin-bottom: 12px;">
                <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 12px; border-radius: 6px;">{{ $collectedPct }}%</span>
                <span>تم السداد للموردين <i class="fa fa-check-circle" style="color: #34d399; margin-right: 6px;"></i></span>
            </div>
            <div style="height: 12px; background: rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; display: flex; justify-content: flex-end; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);">
                <div style="width: {{ $collectedPct }}%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); height: 100%; border-radius: 8px;"></div>
            </div>
        </div>
        
        <div>
            <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: 700; color: #e0e7ff; margin-bottom: 12px;">
                <span style="background: rgba(225, 29, 72, 0.2); color: #fb7185; padding: 4px 12px; border-radius: 6px;">{{ $remainingPct }}%</span>
                <span>ديون متبقية (التزامات) <i class="fa fa-hourglass-half" style="color: #fb7185; margin-right: 6px;"></i></span>
            </div>
            <div style="height: 12px; background: rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; display: flex; justify-content: flex-end; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);">
                <div style="width: {{ $remainingPct }}%; background: linear-gradient(90deg, #f43f5e 0%, #e11d48 100%); height: 100%; border-radius: 8px;"></div>
            </div>
        </div>
    </div>

    {{-- Right: Recent Transactions Panel --}}
    <div class="dashboard-panel-light">
        <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; text-align: right; margin: 0 0 20px 0; display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
            أحدث عمليات الدفع
            <i class="fa fa-clock-rotate-left" style="color: #4f46e5; background: #e0e7ff; padding: 8px 10px; border-radius: 10px;"></i>
        </h3>
        
        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse($recentActivities as $act)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 18px; background: #f8fafc; border-radius: 10px; border: 1px solid #f1f5f9; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fa fa-arrow-up"></i>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 14.5px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">{{ $act->party }}</div>
                        <div style="font-size: 12px; color: #64748b; font-weight: 600;"><i class="fa fa-calendar-day" style="margin-left: 4px;"></i> {{ $act->date->format('Y-m-d') }}</div>
                    </div>
                </div>
                <div style="text-align: left;">
                    <div style="font-size: 16px; font-weight: 800; color: #10b981;">{{ \App\Support\Money::format($act->amount) }} ج.م</div>
                </div>
            </div>
            @empty
            <div style="text-align: center; color: #94a3b8; font-size: 14px; padding: 30px 0;">
                <i class="fa fa-inbox" style="font-size: 32px; margin-bottom: 12px; display: block; color: #cbd5e1;"></i>
                لا توجد عمليات دفع حديثة
            </div>
            @endforelse
        </div>
    </div>
</div>


<div id="supplier-tab" class="tab-content" style="display:block;">
    {{-- Full Width Main Card for Tables --}}
    <div class="n-card" style="padding: 0; border-top: 5px solid #0f172a;">
        <div class="no-print" style="padding: 20px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; background: #f8fafc; border-radius: 12px 12px 0 0;">
            <div style="display: flex; gap: 14px;">
                <button class="btn" style="background: #1e293b; color: white; border-radius: 8px; font-weight: 700; padding: 8px 20px; border: none; cursor: pointer; font-size: 13px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);" onclick="window.print()">
                    <i class="fa fa-print" style="margin-left: 6px;"></i> طباعة التقرير
                </button>
                <form id="sort-form" method="GET" action="{{ route('debts.index') }}">
                    <select name="sort" onchange="document.getElementById('sort-form').submit()" style="padding: 8px 18px; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 700; color: #1e293b; background: #fff; cursor: pointer; font-size: 13px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
                        <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>الأحدث أولاً</option>
                        <option value="amount_desc" {{ request('sort') == 'amount_desc' ? 'selected' : '' }}>الأعلى ديناً</option>
                        <option value="amount_asc" {{ request('sort') == 'amount_asc' ? 'selected' : '' }}>الأقل ديناً</option>
                    </select>
                </form>
                <div style="position: relative; width: 280px;">
                    <input type="text" id="main-search" oninput="filterMain()" placeholder="ابحث باسم المورد..." style="width: 100%; padding: 8px 14px 8px 36px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; text-align: right; background: #fff; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
                    <i class="fa fa-search" style="position: absolute; left: 14px; top: 12px; color: #94a3b8;"></i>
                </div>
            </div>
            <h3 style="margin: 0; font-size: 19px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                جدول ديون الموردين التفصيلي <i class="fa fa-table-list" style="color: #4f46e5;"></i>
            </h3>
        </div>

        <div style="overflow-x: auto;">
            <table class="n-table" id="main-table">
                <thead>
                    <tr>
                        <th style="text-align: right; padding-right: 30px;"><i class="fa fa-building"></i> المورد</th>
                        <th><i class="fa fa-percent"></i> نسبة السداد</th>
                        <th><i class="fa fa-file-invoice"></i> إجمالي الحساب</th>
                        <th style="color: #10b981;"><i class="fa fa-check-double"></i> إجمالي المسدد</th>
                        <th style="color: #e11d48;"><i class="fa fa-triangle-exclamation"></i> إجمالي المتبقي</th>
                        <th><i class="fa fa-circle-info"></i> الموقف</th>
                    </tr>
                </thead>
                <tbody id="main-tbody">
                    @foreach($bySupplier as $supplierId => $supplierDebts)
                        @php
                            $supplier = $supplierDebts->first()->supplier;
                            $name = $supplier?->name ?? 'بدون مورد';
                            $sTotal = $supplierDebts->sum('total_amount');
                            $sPaid = $supplierDebts->sum('paid_amount');
                            $sRemaining = $supplierDebts->sum(fn($d) => $d->remaining());
                            $isPaid = $sRemaining <= 0.009;
                            $pct    = $sTotal > 0 ? round($sPaid / $sTotal * 100) : 0;
                            $firstLetter = mb_substr($name, 0, 1);
                            $colors = ['#4f46e5', '#e11d48', '#8b5cf6', '#10b981', '#f59e0b', '#0ea5e9'];
                            $avatarColor = $colors[$loop->index % 6];
                            $partyKey = 'supp-' . $supplierId;
                        @endphp
                        <tr class="rv-row-item" onclick="openPartyModal('{{ $partyKey }}')"
                            data-name="{{ mb_strtolower($name) }}"
                            data-status="{{ $isPaid ? 'paid' : 'active' }}">
                            <td style="text-align: right; padding-right: 30px;">
                                <div style="display: flex; align-items: center; justify-content: flex-start; gap: 16px;">
                                    <div class="avatar-print" style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, {{ $avatarColor }} 0%, #0f172a 150%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                        {{ $firstLetter }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; color: #0f172a; font-size: 15px; margin-bottom: 4px;">{{ $name }}</div>
                                        <div style="color: #64748b; font-size: 12px; font-weight: 700;"><i class="fa fa-file-invoice" style="color: #cbd5e1; margin-left: 4px;"></i> {{ $supplierDebts->count() }} فواتير مسجلة</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="no-print" style="font-size: 13px; font-weight: 800; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 6px;">{{ $pct }}%</span>
                                <span class="print-only" style="display:none;">{{ $pct }}%</span>
                            </td>
                            <td style="color: #475569; font-weight: 800; font-size: 15px;">{{ \App\Support\Money::format($sTotal) }} ج</td>
                            <td style="color: #10b981; font-weight: 800; font-size: 15px;">{{ \App\Support\Money::format($sPaid) }} ج</td>
                            <td style="color: #e11d48; font-weight: 800; font-size: 16px;">{{ \App\Support\Money::format($sRemaining) }} ج</td>
                            <td>
                                @if($isPaid)
                                    <span class="no-print" style="background: #ecfdf5; color: #047857; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; border: 1px solid #a7f3d0;"><i class="fa fa-check"></i> مسدد</span>
                                    <span class="print-only" style="display:none;">مسدد</span>
                                @else
                                    <span class="no-print" style="background: #fff1f2; color: #be123c; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; border: 1px solid #fecdd3;"><i class="fa fa-clock"></i> دين نشط</span>
                                    <span class="print-only" style="display:none;">نشط</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div id="no-results" class="no-print" style="display:none; text-align: center; padding: 50px; color: #94a3b8;">
            <i class="fa fa-search" style="font-size: 40px; margin-bottom: 16px; display: block; color: #cbd5e1;"></i>
            <strong style="display: block; font-size: 16px; color: #64748b;">لا توجد نتائج مطابقة لبحثك</strong>
        </div>
    </div>
</div>


<div id="manual-tab" class="tab-content" style="display:none;">
{{-- سلف وديون أخرى (حركات يدوية) --}}
@if(isset($manualDebts) && $manualDebts->count())
<div class="n-card" style="padding: 0; border-top: 5px solid #8b5cf6;">
  <div class="no-print" style="padding: 20px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; background: #f8fafc; border-radius: 12px 12px 0 0;">
    <h3 style="margin: 0; font-size: 19px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px;">
        جدول السلف والديون اليدوية التفصيلي <i class="fa fa-hand-holding-dollar" style="color: #8b5cf6;"></i>
    </h3>
    <span style="background: #fff1f2; color: #be123c; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 800; border: 1px solid #fecdd3;">
        إجمالي المتبقي: {{ \App\Support\Money::format($manualDebts->sum(fn($r) => $r->remaining())) }} ج.م
    </span>
  </div>
  <div style="overflow-x: auto;">
      <table class="n-table" id="manual-table">
        <thead>
          <tr>
            <th style="text-align: right; padding-right: 30px;"><i class="fa fa-user"></i> الجهة / الشخص</th>
            <th><i class="fa fa-hashtag"></i> التعاملات</th>
            <th><i class="fa fa-file-invoice"></i> إجمالي المبلغ</th>
            <th style="color: #10b981;"><i class="fa fa-check-double"></i> المسدد</th>
            <th style="color: #e11d48;"><i class="fa fa-triangle-exclamation"></i> المتبقي</th>
            <th><i class="fa fa-circle-info"></i> الحالة</th>
          </tr>
        </thead>
        <tbody id="manual-tbody">
          @foreach($manualGrouped as $partyName => $partyItems)
            @php
              $partyTotal     = $partyItems->sum('total_amount');
              $partyPaid      = $partyItems->sum('paid_amount');
              $partyRemaining = $partyItems->sum(fn($r) => $r->remaining());
              $partyCount     = $partyItems->count();
              $allPaid        = $partyItems->every(fn($r) => $r->status === 'paid');
              $partyKey       = 'mdebt-' . md5($partyName);
              $firstLetter    = mb_substr($partyName, 0, 1);
            @endphp
            <tr data-status="{{ $allPaid ? 'paid' : 'pending' }}" style="cursor:pointer" onclick="openPartyModal('{{ $partyKey }}')">
              <td style="text-align: right; padding-right: 30px;">
                  <div style="display: flex; align-items: center; justify-content: flex-start; gap: 16px;">
                      <div class="avatar-print" style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6 0%, #0f172a 150%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                          {{ $firstLetter }}
                      </div>
                      <div style="font-weight: 800; color: #0f172a; font-size: 15px;">{{ $partyName }}</div>
                  </div>
              </td>
              <td>
                  <span class="no-print" style="background: #f1f5f9; color: #475569; padding: 6px 14px; border-radius: 8px; font-size: 14px; font-weight: 800;">{{ $partyCount }}</span>
                  <span class="print-only" style="display:none;">{{ $partyCount }}</span>
              </td>
              <td style="color: #475569; font-weight: 800; font-size: 15px;">{{ \App\Support\Money::format($partyTotal) }} ج</td>
              <td style="color: #10b981; font-weight: 800; font-size: 15px;">{{ \App\Support\Money::format($partyPaid) }} ج</td>
              <td style="color: #e11d48; font-weight: 800; font-size: 16px;">{{ \App\Support\Money::format($partyRemaining) }} ج</td>
              <td>
                @if($allPaid)
                  <span class="no-print" style="background: #ecfdf5; color: #047857; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; border: 1px solid #a7f3d0;"><i class="fa fa-check"></i> مسدد</span>
                  <span class="print-only" style="display:none;">مسدد</span>
                @else
                  <span class="no-print" style="background: #fff1f2; color: #be123c; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; border: 1px solid #fecdd3;"><i class="fa fa-clock"></i> معلق</span>
                  <span class="print-only" style="display:none;">نشط</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
  </div>
</div>
@endif
</div> <!-- end manual-tab -->


{{-- ── توليد المودلز الخاصة بتفاصيل الموردين ── --}}
@foreach($bySupplier as $supplierId => $supplierDebts)
  @php
    $supplier = $supplierDebts->first()->supplier;
    $name = $supplier?->name ?? 'بدون مورد';
    $sTotal = $supplierDebts->sum('total_amount');
    $sPaid = $supplierDebts->sum('paid_amount');
    $sRemaining = $supplierDebts->sum(fn($d) => $d->remaining());
    $partyKey = 'supp-' . $supplierId;
  @endphp
  <div class="rv-modal no-print" id="modal-{{ $partyKey }}" onclick="if(event.target===this) document.getElementById('modal-{{ $partyKey }}').style.display='none'">
    <div style="max-width: 900px; width: 95%; background: #ffffff; border-radius: 16px; overflow: hidden; margin: 40px auto; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
      
      {{-- Modal Header --}}
      <div style="padding: 24px 30px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; border-bottom: 1px solid #f1f5f9;">
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <h3 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 12px;">
            <i class="fa fa-boxes-stacked" style="color: #4f46e5; background: #e0e7ff; padding: 10px; border-radius: 10px;"></i> {{ $name }}
          </h3>
          <div style="display: flex; gap: 10px;">
            <span style="background: #e11d48; color: #ffffff; padding: 6px 14px; border-radius: 8px; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 6px;">
              المتبقي علينا: {{ \App\Support\Money::format($sRemaining) }} ج
            </span>
            <span style="background: #10b981; color: #ffffff; padding: 6px 14px; border-radius: 8px; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 6px;">
              المسدد: {{ \App\Support\Money::format($sPaid) }} ج
            </span>
          </div>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
          @if($sRemaining > 0)
          <button type="button" style="background: #facc15; color: #000000; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;" onclick="openSupplierPayModal({{ $supplierId }}, {{ $sRemaining }}, '{{ addslashes($name) }}', 'partial')" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
            <i class="fa fa-money-bill-wave"></i> سداد جزئي
          </button>
          <button type="button" style="background: #10b981; color: #ffffff; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;" onclick="openSupplierPayModal({{ $supplierId }}, {{ $sRemaining }}, '{{ addslashes($name) }}', 'full')" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
            <i class="fa fa-money-bill"></i> سداد كلي للمورد
          </button>
          @endif
          <button type="button" class="rv-x" onclick="document.getElementById('modal-{{ $partyKey }}').style.display='none'" style="font-size: 1.8rem; color: #94a3b8; border: none; background: transparent; cursor: pointer; padding: 0 10px;">×</button>
        </div>
      </div>

      {{-- Table Area --}}
      <div style="background: #ffffff; max-height: 400px; overflow-y: auto;">
        <table class="n-table" style="width: 100%; margin: 0;">
          <thead style="position: sticky; top: 0; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); z-index: 10;">
            <tr>
              <th style="text-align: right; padding: 16px 30px; color: #475569;">تاريخ الاستحقاق</th>
              <th style="text-align: right; color: #475569;">البيان / الفاتورة</th>
              <th style="color: #475569;">المشروع</th>
              <th style="color: #475569;">إجمالي الفاتورة</th>
              <th style="color: #475569;">المتبقي للدفع</th>
              <th style="color: #475569;">إجراءات الدفع</th>
            </tr>
          </thead>
          <tbody>
            @foreach($supplierDebts as $debt)
              <tr>
                <td style="text-align: right; padding: 20px 30px; font-weight: 700; color: #334155; font-size: 14.5px;">
                  @if($debt->due_date)
                    <span @if($debt->isOverdue()) style="color:#e11d48" @endif>{{ $debt->due_date->format('Y-m-d') }}</span>
                    @if($debt->isOverdue()) <span class="tag red sm" style="background:#fff1f2;color:#be123c;padding:4px 8px;border-radius:6px;font-size:11px; margin-right: 8px;">متأخر</span> @endif
                  @else — @endif
                </td>
                <td style="text-align: right; font-weight: 800; color: #0f172a; font-size: 14.5px;">{{ $debt->description }}</td>
                <td style="color: #64748b; font-size: 13px;">{{ $debt->project?->name ?? '—' }}</td>
                <td style="font-weight: 800; color: #475569; font-size: 14.5px;">{{ \App\Support\Money::format($debt->total_amount) }} ج</td>
                <td style="font-weight: 800; color: #e11d48; font-size: 14.5px;">{{ \App\Support\Money::format($debt->remaining()) }} ج</td>
                <td>
                  <div style="display: flex; gap: 8px; justify-content: center;">
                    @if($debt->status !== 'paid')
                      <button type="button" style="background: #e0e7ff; color: #4f46e5; border: none; padding: 6px 16px; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s;" onclick="
                        document.getElementById('modal-{{ $partyKey }}').style.display='none';
                        openPayModal({{ $debt->id }}, {{ $debt->remaining() }}, '{{ addslashes($debt->description) }}')
                      ">
                        <i class="fa fa-cash-register"></i> سداد
                      </button>
                    @else
                      <span style="color: #10b981; font-size: 13px; font-weight: 800;"><i class="fa fa-check-circle"></i> مسدد</span>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endforeach

{{-- ── توليد المودلز الخاصة بتفاصيل الديون اليدوية ── --}}
@foreach($manualGrouped as $partyName => $partyItems)
  @php
    $partyTotal     = $partyItems->sum('total_amount');
    $partyPaid      = $partyItems->sum('paid_amount');
    $partyRemaining = $partyItems->sum(fn($r) => $r->remaining());
    $partyKey       = 'mdebt-' . md5($partyName);
  @endphp
  <div class="rv-modal no-print" id="modal-{{ $partyKey }}" onclick="if(event.target===this) document.getElementById('modal-{{ $partyKey }}').style.display='none'">
    <div style="max-width: 900px; width: 95%; background: #ffffff; border-radius: 16px; overflow: hidden; margin: 40px auto; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
      
      {{-- Modal Header --}}
      <div style="padding: 24px 30px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; border-bottom: 1px solid #f1f5f9;">
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <h3 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 12px;">
            <i class="fa fa-user-circle" style="color: #8b5cf6; background: #f3e8ff; padding: 10px; border-radius: 10px;"></i> {{ $partyName }}
          </h3>
          <div style="display: flex; gap: 10px;">
            <span style="background: #e11d48; color: #ffffff; padding: 6px 14px; border-radius: 8px; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 6px;">
              المتبقي: {{ \App\Support\Money::format($partyRemaining) }} ج
            </span>
            <span style="background: #10b981; color: #ffffff; padding: 6px 14px; border-radius: 8px; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 6px;">
              المدفوع: {{ \App\Support\Money::format($partyPaid) }} ج
            </span>
          </div>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
          @if($partyRemaining > 0)
          <button type="button" style="background: #facc15; color: #000000; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;" onclick="openPartyBulkPay('{{ addslashes($partyName) }}', {{ $partyRemaining }}, 'partial', '{{ $partyKey }}')">
            <i class="fa fa-money-bill-wave"></i> سداد جزئي
          </button>
          <button type="button" style="background: #10b981; color: #ffffff; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;" onclick="openPartyBulkPay('{{ addslashes($partyName) }}', {{ $partyRemaining }}, 'full', '{{ $partyKey }}')">
            <i class="fa fa-money-bill"></i> سداد كلي للعميل
          </button>
          @endif
          <button type="button" class="rv-x" onclick="document.getElementById('modal-{{ $partyKey }}').style.display='none'" style="font-size: 1.8rem; color: #94a3b8; border: none; background: transparent; cursor: pointer; padding: 0 10px;">×</button>
        </div>
      </div>

      {{-- Table Area --}}
      <div style="background: #ffffff; max-height: 400px; overflow-y: auto;">
        <table class="n-table" style="width: 100%; margin: 0;">
          <thead style="position: sticky; top: 0; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); z-index: 10;">
            <tr>
              <th style="text-align: right; padding: 16px 30px; color: #475569;">التاريخ</th>
              <th style="text-align: right; color: #475569;">البيان</th>
              <th style="color: #475569;">المبلغ</th>
              <th style="color: #475569;">المسدد</th>
              <th style="color: #475569;">المتبقي</th>
              <th style="color: #475569;">إجراءات الدفع</th>
            </tr>
          </thead>
          <tbody>
            @foreach($partyItems as $debt)
              <tr>
                <td style="text-align: right; padding: 20px 30px; font-weight: 800; color: #334155; font-size: 14.5px;">{{ $debt->date->format('Y-m-d') }}</td>
                <td style="text-align: right; font-weight: 800; color: #0f172a; font-size: 14.5px;">{{ $debt->description ?: '—' }}</td>
                <td style="font-weight: 800; color: #475569; font-size: 14.5px;">{{ \App\Support\Money::format($debt->total_amount) }} ج</td>
                <td style="font-weight: 800; color: #10b981; font-size: 14.5px;">{{ \App\Support\Money::format($debt->paid_amount) }} ج</td>
                <td style="font-weight: 800; color: #e11d48; font-size: 14.5px;">{{ \App\Support\Money::format($debt->remaining()) }} ج</td>
                <td>
                  <div style="display: flex; gap: 8px; justify-content: center;">
                    @if($debt->status !== 'paid')
                      <button type="button" style="background: #e0e7ff; color: #4f46e5; border: none; padding: 6px 16px; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s;" onclick="
                        document.getElementById('modal-{{ $partyKey }}').style.display='none';
                        openManualPayModal({{ $debt->id }}, {{ $debt->remaining() }}, '{{ addslashes($debt->party . ($debt->description ? ' - ' . $debt->description : '')) }}')
                      ">
                        <i class="fa fa-cash-register"></i> سداد
                      </button>
                    @else
                      <span style="color: #10b981; font-size: 13px; font-weight: 800;"><i class="fa fa-check-circle"></i> مسدد</span>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endforeach

{{-- النوافذ المنبثقة للسداد (Single / Bulk / Supplier) --}}

{{-- سداد فاتورة مورد واحدة --}}
<div id="pay-modal" class="no-print" style="display:none;position:fixed;inset:0;z-index:1100;background:rgba(15,23,42,.6);align-items:center;justify-content:center" onclick="if(event.target===this) this.style.display='none'">
  <div style="background:#fff;border-radius:16px;padding:32px;width:min(480px,96vw);box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
    <h4 style="margin:0 0 8px; font-weight: 800; color: #0f172a; font-size: 20px;">سداد فاتورة</h4>
    <p id="pay-desc" style="margin:0 0 24px; font-size:14px; color: #64748b; font-weight: 600; line-height: 1.5;"></p>
    <form id="pay-form" method="POST" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
      @csrf
      <div style="margin-bottom: 20px;">
        <label style="display:block; font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 10px;">المبلغ المسدد (ج.م) *</label>
        <input type="number" name="amount" id="pay-amount" min="0.01" step="0.01" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 16px; font-weight: 700; outline: none;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
        <small style="color: #94a3b8; font-size: 12px; margin-top: 6px; display: block;" id="pay-max-note"></small>
      </div>
      <div style="margin-bottom: 20px;">
        @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true])
      </div>
      <div style="margin-bottom: 30px;">
        <label style="display:block; font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 10px;">تاريخ الدفع *</label>
        <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 15px; outline: none;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
      </div>
      <div style="display: flex; gap: 14px;">
        <button type="submit" style="flex: 1; background: #4f46e5; color: #fff; border: none; padding: 14px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">تسجيل الدفع</button>
        <button type="button" style="background: #f1f5f9; color: #475569; border: none; padding: 14px 28px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s;" onclick="document.getElementById('pay-modal').style.display='none'" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">إلغاء</button>
      </div>
    </form>
  </div>
</div>

{{-- سداد ديون مورد (كلي/جزئي) --}}
<div id="supplier-pay-modal" class="no-print" style="display:none;position:fixed;inset:0;z-index:1100;background:rgba(15,23,42,.6);align-items:center;justify-content:center" onclick="if(event.target===this) this.style.display='none'">
  <div style="background:#fff;border-radius:16px;padding:32px;width:min(480px,96vw);box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
    <h4 style="margin:0 0 8px; font-weight: 800; color: #0f172a; font-size: 20px;">سداد ديون المورد</h4>
    <p id="supplier-pay-name" style="margin:0 0 24px; font-size:14px; color: #64748b; font-weight: 600; line-height: 1.5;"></p>
    <form id="supplier-pay-form" method="POST" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
      @csrf
      <div style="margin-bottom: 20px;">
        <label style="display:block; font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 10px;">المبلغ المسدد (ج.م) *</label>
        <input type="number" name="amount" id="supplier-pay-amount" min="0.01" step="0.01" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 16px; font-weight: 700; outline: none;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
        <small style="color: #94a3b8; font-size: 12px; margin-top: 6px; display: block;" id="supplier-pay-note"></small>
      </div>
      <div style="margin-bottom: 20px;">
        @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true, 'fieldName' => 'account_id'])
      </div>
      <div style="margin-bottom: 30px;">
        <label style="display:block; font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 10px;">تاريخ الدفع *</label>
        <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 15px; outline: none;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
      </div>
      <div style="display: flex; gap: 14px;">
        <button type="submit" style="flex: 1; background: #4f46e5; color: #fff; border: none; padding: 14px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">تسجيل الدفع</button>
        <button type="button" style="background: #f1f5f9; color: #475569; border: none; padding: 14px 28px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s;" onclick="document.getElementById('supplier-pay-modal').style.display='none'" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">إلغاء</button>
      </div>
    </form>
  </div>
</div>

{{-- سداد عهدة يدوية واحدة --}}
<div id="manual-pay-modal" class="no-print" style="display:none;position:fixed;inset:0;z-index:1100;background:rgba(15,23,42,.6);align-items:center;justify-content:center" onclick="if(event.target===this) this.style.display='none'">
  <div style="background:#fff;border-radius:16px;padding:32px;width:min(480px,96vw);box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
    <h4 style="margin:0 0 8px; font-weight: 800; color: #0f172a; font-size: 20px;">سداد عهدة / دين أخرى</h4>
    <p id="manual-pay-desc" style="margin:0 0 24px; font-size:14px; color: #64748b; font-weight: 600; line-height: 1.5;"></p>
    <form id="manual-pay-form" method="POST" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
      @csrf
      <div style="margin-bottom: 20px;">
        <label style="display:block; font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 10px;">المبلغ المسدد (ج.م) *</label>
        
        <div style="display:flex;gap:12px;margin-bottom:16px">
          <button type="button" style="background:#10b981; color:#fff; border:none; padding: 10px 16px; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; flex: 1;" id="manual-pay-full-btn" onclick="
            document.getElementById('manual-pay-amount').value = document.getElementById('manual-pay-amount').max;
            document.getElementById('manual-pay-amount').readOnly = true;
            this.style.background = '#10b981'; this.style.color = '#fff';
            document.getElementById('manual-pay-partial-btn').style.background = '#f1f5f9';
            document.getElementById('manual-pay-partial-btn').style.color = '#475569';
          ">سداد كلي</button>
          
          <button type="button" style="background:#f1f5f9; color:#475569; border:none; padding: 10px 16px; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; flex: 1;" id="manual-pay-partial-btn" onclick="
            document.getElementById('manual-pay-amount').value = '';
            document.getElementById('manual-pay-amount').readOnly = false;
            document.getElementById('manual-pay-amount').focus();
            this.style.background = '#facc15'; this.style.color = '#000';
            document.getElementById('manual-pay-full-btn').style.background = '#f1f5f9';
            document.getElementById('manual-pay-full-btn').style.color = '#475569';
          ">سداد جزئي</button>
        </div>

        <input type="number" name="amount" id="manual-pay-amount" min="0.01" step="0.01" required readonly style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 16px; font-weight: 700; outline: none; background: #f8fafc;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
        <small style="color: #94a3b8; font-size: 12px; margin-top: 6px; display: block;" id="manual-pay-max-note"></small>
      </div>
      <div style="margin-bottom: 20px;">
        @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true])
      </div>
      <div style="margin-bottom: 30px;">
        <label style="display:block; font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 10px;">تاريخ السداد *</label>
        <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 15px; outline: none;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
      </div>
      <div style="display: flex; gap: 14px;">
        <button type="submit" style="flex: 1; background: #4f46e5; color: #fff; border: none; padding: 14px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">تسجيل السداد</button>
        <button type="button" style="background: #f1f5f9; color: #475569; border: none; padding: 14px 28px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s;" onclick="document.getElementById('manual-pay-modal').style.display='none'" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">إلغاء</button>
      </div>
    </form>
  </div>
</div>

{{-- سداد عهدة يدوية مجمعة (كلي/جزئي) --}}
<div id="manual-party-pay-modal" class="no-print" style="display:none;position:fixed;inset:0;z-index:1100;background:rgba(15,23,42,.6);align-items:center;justify-content:center" onclick="if(event.target===this) this.style.display='none'">
  <div style="background:#fff;border-radius:16px;padding:32px;width:min(480px,96vw);box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
    <h4 style="margin:0 0 8px; font-weight: 800; color: #0f172a; font-size: 20px;">سداد ديون <span id="manual-party-pay-name"></span></h4>
    <form id="manual-party-pay-form" method="POST" action="{{ route('debts.manual.party.pay') }}" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
      @csrf
      <input type="hidden" name="party_name" id="manual-party-pay-party">
      <div style="margin-bottom: 20px; margin-top: 24px;">
        <label style="display:block; font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 10px;">المبلغ المسدد (ج.م) *</label>
        <input type="number" name="amount" id="manual-party-pay-amount" min="0.01" step="0.01" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 16px; font-weight: 700; outline: none;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
        <small style="color: #94a3b8; font-size: 12px; margin-top: 6px; display: block;" id="manual-party-pay-max-note"></small>
      </div>
      <div style="margin-bottom: 20px;">
        @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true, 'fieldName' => 'account_id'])
      </div>
      <div style="margin-bottom: 30px;">
        <label style="display:block; font-size: 14px; font-weight: 800; color: #475569; margin-bottom: 10px;">تاريخ السداد *</label>
        <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 15px; outline: none;" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
      </div>
      <div style="display: flex; gap: 14px;">
        <button type="submit" style="flex: 1; background: #4f46e5; color: #fff; border: none; padding: 14px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">تسجيل السداد للجهة</button>
        <button type="button" style="background: #f1f5f9; color: #475569; border: none; padding: 14px 28px; border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer; transition: 0.2s;" onclick="document.getElementById('manual-party-pay-modal').style.display='none'" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">إلغاء</button>
      </div>
    </form>
  </div>
</div>

</div> {{-- end .rv --}}

@push('scripts')
<script>
function switchTab(tabId) {
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  
  document.querySelectorAll('.n-main-tab').forEach(el => {
    el.classList.remove('active');
  });
  
  document.getElementById(tabId).style.display = 'block';
  
  const btnId = tabId === 'supplier-tab' ? 'tab-btn-supplier' : 'tab-btn-manual';
  document.getElementById(btnId).classList.add('active');
}

function filterMain() {
  let search = document.getElementById('main-search').value.toLowerCase();
  let rows = document.querySelectorAll('#main-tbody .rv-row-item');
  let hasVisible = false;
  
  rows.forEach(row => {
    let name = row.getAttribute('data-name') || '';
    if (name.includes(search)) {
      row.style.display = '';
      hasVisible = true;
    } else {
      row.style.display = 'none';
    }
  });
  
  let noRes = document.getElementById('no-results');
  if(noRes) {
    noRes.style.display = hasVisible ? 'none' : 'block';
  }
}

function filterStatus(status, btn) {
    document.querySelectorAll('.n-tab').forEach(b => {
        b.className = 'n-tab';
    });
    btn.className = 'n-tab active-' + status;

    let rows = document.querySelectorAll('#main-tbody .rv-row-item');
    rows.forEach(row => {
        let rStatus = row.getAttribute('data-status');
        if(status === 'all' || rStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openPartyModal(key) {
  const modal = document.getElementById('modal-' + key);
  if (modal) {
    modal.style.display = 'flex';
  }
}

function openSupplierPayModal(supplierId, remaining, name, mode) {
  document.getElementById('supplier-pay-name').textContent =
    (mode === 'full' ? 'سداد كامل ديون المورد: ' : 'سداد جزئي لديون المورد: ') + name;
  document.getElementById('supplier-pay-amount').max = remaining;
  document.getElementById('supplier-pay-amount').value = mode === 'full' ? remaining : '';
  document.getElementById('supplier-pay-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  document.getElementById('supplier-pay-form').action = '/debts/supplier/' + supplierId + '/pay';
  
  const submitBtn = document.querySelector('#supplier-pay-form button[type=submit]');
  if(submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = 'تسجيل الدفع'; }

  document.getElementById('supplier-pay-modal').style.display = 'flex';
}

function openPayModal(id, remaining, desc) {
  document.getElementById('pay-desc').textContent = desc;
  document.getElementById('pay-amount').max = remaining;
  document.getElementById('pay-amount').value = remaining;
  document.getElementById('pay-max-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  document.getElementById('pay-form').action = '/debts/' + id + '/pay';
  
  const submitBtn = document.querySelector('#pay-form button[type=submit]');
  if(submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'تسجيل الدفع'; }

  document.getElementById('pay-modal').style.display = 'flex';
}

function openManualPayModal(id, remaining, desc) {
  document.getElementById('manual-pay-desc').textContent = desc;
  document.getElementById('manual-pay-amount').max = remaining;
  
  // Default to full payment style
  document.getElementById('manual-pay-amount').value = remaining;
  document.getElementById('manual-pay-amount').readOnly = true;
  document.getElementById('manual-pay-full-btn').style.background = '#10b981';
  document.getElementById('manual-pay-full-btn').style.color = '#fff';
  document.getElementById('manual-pay-partial-btn').style.background = '#f1f5f9';
  document.getElementById('manual-pay-partial-btn').style.color = '#475569';

  document.getElementById('manual-pay-max-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  document.getElementById('manual-pay-form').action = '/debts/manual/' + id + '/pay';
  
  const submitBtn = document.querySelector('#manual-pay-form button[type=submit]');
  if(submitBtn) { submitBtn.disabled = false; submitBtn.innerText = 'تسجيل السداد'; }

  document.getElementById('manual-pay-modal').style.display = 'flex';
}

function openPartyBulkPay(partyName, remaining, mode, partyKey) {
  document.getElementById('manual-party-pay-name').textContent = partyName;
  document.getElementById('manual-party-pay-party').value = partyName;
  document.getElementById('manual-party-pay-amount').max = remaining;
  document.getElementById('manual-party-pay-amount').value = mode === 'full' ? remaining : '';
  document.getElementById('manual-party-pay-max-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  
  const submitBtn = document.querySelector('#manual-party-pay-form button[type=submit]');
  if(submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = 'تسجيل السداد للجهة'; }

  document.getElementById('manual-party-pay-modal').style.display = 'flex';
}
</script>
@endpush
@endsection
