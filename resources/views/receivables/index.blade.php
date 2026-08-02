@extends('layouts.app')
@section('title', 'المستحقات')
@section('page-title', 'المستحقات')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ═══ تصميم المستحقات — مينيمال هادي (مقصود يبقى مختلف عن صفحة الأقساط) ═══
   لوحان بس: سلات غامق + أخضر للفلوس الداخلة، وأحمر للمتبقي فقط. */
.rv * { box-sizing:border-box; }
.rv { --ink:#1e293b; --mut:#64748b; --soft:#94a3b8; --ln:#e2e8f0; --bg2:#f8fafc;
      --ok:#047857; --okbg:#ecfdf5; --bad:#b91c1c; }

/* ── التصميم الجديد الفاخر (Premium Redesign) ── */
.n-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    padding: 24px;
}
.n-tab {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.n-tab.active-all {
    background: #3b82f6;
    color: #fff;
}
.n-tab.active-active {
    background: #ea580c;
    color: #fff;
}
.n-tab.active-paid {
    background: #10b981;
    color: #fff;
}
.n-table {
    width: 100%;
    border-collapse: collapse;
}
.n-table th {
    background: #f8fafc;
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 700;
    color: #94a3b8;
    border-bottom: 1px solid #e2e8f0;
    text-align: center;
    white-space: nowrap;
}
.n-table td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    text-align: center;
    vertical-align: middle;
    font-size: 14px;
}
.n-table tbody tr {
    cursor: pointer;
    transition: 0.15s;
}
.n-table tbody tr:hover td {
    background: #f8fafc;
}

.n-main-tab {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 800;
    cursor: pointer;
    transition: 0.2s;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    color: #64748b;
}
.n-main-tab.active {
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

@media print {
    body { background: #fff !important; }
    .page-head, .grid, .tabs-container, .no-print, .analysis-col { display: none !important; }
    #project-tab > div { grid-template-columns: 1fr !important; gap: 0 !important; }
    .n-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
    .n-table th, .n-table td { border: 1px solid #000 !important; font-size: 11px !important; padding: 6px !important; }
    .n-table th { background: #f1f1f1 !important; color: #000 !important; }
    .n-table td span { border: none !important; background: transparent !important; color: #000 !important; padding: 0 !important; }
}

/* ═══ المودال — ضيّق وطولي، كل حاجة قريبة ═══ */
.rv-modal { position:fixed; inset:0; z-index:1050; display:none; align-items:flex-start;
  justify-content:center; background:rgba(15,23,42,.55); padding:26px 12px; overflow-y:auto; }
.rv-modal.open { display:flex; }
.rv-card { width:min(520px, 96vw); background:#fff; border-radius:14px; overflow:hidden;
  box-shadow:0 24px 60px rgba(15,23,42,.3); animation:rvIn .18s ease; }
@keyframes rvIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:none} }

.rv-mhead { background:var(--ink); color:#fff; padding:14px 18px; display:flex; align-items:center; gap:10px; }
.rv-mhead .nm { font-size:1rem; font-weight:700; flex:1; min-width:0; }
.rv-mhead .nm small { display:block; font-size:.72rem; color:#cbd5e1; font-weight:400; margin-top:2px; }
.rv-x { background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; line-height:1; padding:2px 6px; }
.rv-x:hover { color:#fff; }

.rv-mbody { padding:14px 18px 18px; }

/* أرقام مضغوطة: مفوتر/محصل/متبقي في سطر واحد */
.rv-nums { display:flex; border:1px solid var(--ln); border-radius:10px; overflow:hidden; margin-bottom:10px; }
.rv-num { flex:1; text-align:center; padding:9px 6px; border-inline-start:1px solid var(--ln); }
.rv-num:first-child { border-inline-start:none; }
.rv-num .l { font-size:.66rem; color:var(--mut); font-weight:600; margin-bottom:2px; }
.rv-num .v { font-size:.95rem; font-weight:700; color:var(--ink); }
.rv-num.ok .v { color:var(--ok); }
.rv-num.bad .v { color:var(--bad); }
.rv-prog { height:5px; background:#eef2f7; border-radius:99px; overflow:hidden; margin-bottom:12px; }
.rv-prog i { display:block; height:100%; background:var(--ink); }

/* أزرار الإجراءات — شبكة 2×2 مضغوطة */
.rv-acts { display:grid; grid-template-columns:1fr 1fr; gap:7px; margin-bottom:12px; }
.rv-act { padding:9px 10px; border-radius:9px; font-size:.8rem; font-weight:700; cursor:pointer;
  border:1px solid var(--ln); background:#fff; color:var(--ink); text-align:center;
  display:flex; align-items:center; justify-content:center; gap:6px; text-decoration:none; transition:.12s; }
.rv-act:hover { background:var(--bg2); }
.rv-act.main { background:var(--ink); color:#fff; border-color:var(--ink); }
.rv-act.main:hover { background:#0f172a; }
.rv-act.done { color:var(--ok); border-color:#a7f3d0; background:var(--okbg); cursor:default; }

/* فورم التحصيل — طولي ومتلاصق */
.rv-pay { border:1px solid var(--ln); border-radius:10px; padding:13px; margin-bottom:12px; background:var(--bg2); }
.rv-pay .hd { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;
  font-size:.83rem; font-weight:700; color:var(--ink); }
.rv-presets { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px; }
.rv-preset { padding:5px 11px; border-radius:7px; font-size:.74rem; font-weight:600; cursor:pointer;
  border:1px solid var(--ln); background:#fff; color:var(--mut); }
.rv-preset:hover, .rv-preset.hot { background:var(--ink); color:#fff; border-color:var(--ink); }
.rv-pay label { display:block; font-size:.72rem; font-weight:700; color:var(--mut); margin:8px 0 3px; }
.rv-pay label:first-of-type { margin-top:0; }
.rv-pay input, .rv-pay select { width:100%; padding:8px 10px; border:1px solid var(--ln);
  border-radius:8px; font-size:.84rem; background:#fff; }
.rv-pay input:focus, .rv-pay select:focus { outline:none; border-color:var(--ink); }
.rv-row2 { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.rv-radio { display:flex; gap:14px; margin-top:4px; }
.rv-radio label { display:flex; align-items:center; gap:5px; font-size:.78rem; font-weight:600;
  color:var(--ink); margin:0; cursor:pointer; }
.rv-submit { width:100%; margin-top:12px; padding:10px; background:var(--ok); color:#fff;
  border:none; border-radius:9px; font-weight:700; font-size:.86rem; cursor:pointer; }
.rv-submit:hover { background:#065f46; }

/* السجل */
.rv-hist-h { display:flex; justify-content:space-between; align-items:center; margin:2px 0 8px; }
.rv-hist-h h6 { font-size:.82rem; font-weight:700; margin:0; color:var(--ink); }
.rv-hist-h .rem { font-size:.73rem; font-weight:700; color:var(--bad); }
.rv-hf { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:8px; align-items:center; }
.rv-hf .f { padding:3px 9px; border-radius:6px; font-size:.7rem; font-weight:600; cursor:pointer;
  border:1px solid var(--ln); background:#fff; color:var(--mut); }
.rv-hf .f.active { background:var(--ink); color:#fff; border-color:var(--ink); }
.rv-hf input[type="text"] { flex:1; min-width:110px; padding:4px 8px; border:1px solid var(--ln);
  border-radius:6px; font-size:.75rem; }
table.rv-hist { width:100%; border-collapse:collapse; font-size:.78rem; }
.rv-hist th { padding:6px 8px; font-size:.66rem; color:var(--soft); font-weight:700;
  border-bottom:1px solid var(--ln); text-align:center; }
.rv-hist td { padding:7px 8px; border-bottom:1px solid #f1f5f9; text-align:center; }
.rv-hist td.desc { text-align:right; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.rv-hist .del { background:none; border:none; color:var(--soft); cursor:pointer; font-size:.78rem; padding:2px 5px; }
.rv-hist .del:hover { color:var(--bad); }
.rv-hist tfoot td { font-weight:700; background:var(--bg2); }

/* البنود (طي) */
.rv-bands-t { border:1px solid var(--ln); border-radius:9px; padding:9px 13px; margin-top:11px;
  display:flex; justify-content:space-between; align-items:center; cursor:pointer;
  font-size:.8rem; font-weight:600; color:var(--ink); background:#fff; }
.rv-bands-b { display:none; border:1px solid var(--ln); border-top:none; border-radius:0 0 9px 9px; }
.rv-bands-b table { width:100%; border-collapse:collapse; font-size:.78rem; }
.rv-bands-b td { padding:7px 13px; border-bottom:1px solid #f1f5f9; }

/* إشعار عقد التقسيط */
.rv-contract { border:1px solid var(--ln); border-radius:10px; padding:13px; background:var(--bg2); }
.rv-contract .h { font-size:.82rem; font-weight:700; color:var(--ink); margin-bottom:9px; }
.rv-contract table { width:100%; border-collapse:collapse; font-size:.76rem; }
.rv-contract th { padding:6px 8px; font-size:.66rem; color:var(--soft); border-bottom:1px solid var(--ln); }
.rv-contract td { padding:7px 8px; border-bottom:1px solid #f1f5f9; text-align:center; }

.rv-empty { text-align:center; padding:22px; color:var(--soft); font-size:.8rem; }

/* حماية السايدبار (الصفحة دي من غير Bootstrap أصلاً، بس احتياط) */
.sidebar .nav { display:block !important; }

@media (max-width:640px) {
  .rv-tot { min-width:45%; border-top:1px solid var(--ln); }
  .rv-acts { grid-template-columns:1fr 1fr; }
  .rv-row2 { grid-template-columns:1fr; }
}
@media print {
  .rv-modal, .rv-filters, .no-print { display:none !important; }
}
</style>
@endpush

@section('content')
<div class="rv" style="background-color: #f3f6f9; min-height: 100vh; padding-bottom: 40px; font-family: 'Cairo', sans-serif;">

{{-- Header --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; gap: 8px; background: #e2e8f0; padding: 6px; border-radius: 12px;">
            <button id="tab-btn-project" onclick="switchTab('project-tab')" class="n-main-tab active"><i class="fa fa-building"></i> مستحقات المشاريع</button>
            <button id="tab-btn-manual-recv" onclick="switchTab('manual-recv-tab')" class="n-main-tab"><i class="fa fa-hand-holding-dollar"></i> سلف وديون حرة</button>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px; display: flex; gap: 4px;">
            <button class="n-tab" onclick="filterStatus('all', this)"><i class="fa fa-list"></i> الكل</button>
            <button class="n-tab active-active" onclick="filterStatus('active', this)"><i class="fa fa-fire"></i> الديون النشطة</button>
            <button class="n-tab" onclick="filterStatus('paid', this)"><i class="fa fa-check-circle"></i> المسدد</button>
        </div>
    </div>

    {{-- Right side title --}}
    <div style="text-align: right;">
        <h2 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
            مستحقات العملاء
            <i class="fa fa-chart-pie" style="color: #3b82f6;"></i>
        </h2>
        <p style="margin: 4px 0 0 0; font-size: 14px; color: #64748b; font-weight: 500;">إدارة الفواتير والمدفوعات الخاصة بالعملاء</p>
    </div>
</div>

{{-- 4 Stat Cards --}}
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
    {{-- Card 1: Total Remaining --}}
    <div class="n-card" style="border-right: 4px solid #3b82f6;">
        <div style="font-size: 14px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-align: right;">إجمالي المستحقات (المتبقي لنا)</div>
        <div style="font-size: 24px; font-weight: 800; color: #0f172a; text-align: right;">{{ \App\Support\Money::format($totals['total_remaining'] + $manualTotals['remaining']) }} <span style="font-size: 16px;">ج.م</span></div>
        <div style="font-size: 12px; color: #94a3b8; text-align: right; margin-top: 4px;">المبالغ المتبقية بالسوق للفترة المحددة</div>
    </div>
    {{-- Card 2: Total Collected --}}
    <div class="n-card" style="border-right: 4px solid #10b981;">
        <div style="font-size: 14px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-align: right;">المبالغ المحصلة</div>
        <div style="font-size: 24px; font-weight: 800; color: #0f172a; text-align: right;">{{ \App\Support\Money::format($totals['total_collected'] + $manualTotals['collected']) }} <span style="font-size: 16px;">ج.م</span></div>
        <div style="font-size: 12px; color: #94a3b8; text-align: right; margin-top: 4px;">من إجمالي الحسابات</div>
    </div>
    {{-- Card 3: Active Accounts --}}
    <div class="n-card" style="border-right: 4px solid #ea580c;">
        <div style="font-size: 14px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-align: right;">حسابات نشطة</div>
        <div style="font-size: 24px; font-weight: 800; color: #0f172a; text-align: right;">{{ $activeClientsCount }} <span style="font-size: 16px;">عميل</span></div>
        <div style="font-size: 12px; color: #94a3b8; text-align: right; margin-top: 4px;">لديهم مستحقات معلقة</div>
    </div>
    {{-- Card 4: Paid Accounts --}}
    <div class="n-card" style="border-right: 4px solid #ef4444;">
        <div style="font-size: 14px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-align: right;">حسابات مكتملة السداد</div>
        <div style="font-size: 24px; font-weight: 800; color: #0f172a; text-align: right;">{{ $paidClientsCount }} <span style="font-size: 16px;">عميل</span></div>
        <div style="font-size: 12px; color: #94a3b8; text-align: right; margin-top: 4px;">أنهوا كافة ديونهم</div>
    </div>
</div>

<div id="project-tab" class="tab-content" style="display:block;">
{{-- Main Grid --}}
<div style="display: grid; grid-template-columns: 2.7fr 1fr; gap: 20px;">
    
    {{-- Right Column (Client List) -> Now first in HTML because 2.7fr is first --}}
    <div class="n-card" style="padding: 0; border-top: 4px solid #0f172a;">
        <div style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; background: #f8fafc; border-radius: 12px 12px 0 0;">
            <div style="display: flex; gap: 10px;">
                <button class="btn" style="background: #1e293b; color: white; border-radius: 8px; font-weight: 700; padding: 6px 14px; border: none; cursor: pointer; font-size: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);" onclick="window.print()">
                    <i class="fa fa-print" style="margin-left: 4px;"></i> طباعة القائمة
                </button>
                <form id="sort-form" method="GET" action="{{ route('receivables.index') }}">
                    <select name="sort" onchange="document.getElementById('sort-form').submit()" style="padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600; color: #1e293b; background: #fff; cursor: pointer; font-size: 12px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
                        <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>الأحدث أولاً</option>
                        <option value="amount_desc" {{ request('sort') == 'amount_desc' ? 'selected' : '' }}>الأعلى متبقي</option>
                        <option value="amount_asc" {{ request('sort') == 'amount_asc' ? 'selected' : '' }}>الأقل متبقي</option>
                    </select>
                </form>
                <div style="position: relative; width: 240px;">
                    <input type="text" id="main-search" oninput="filterMain()" placeholder="ابحث باسم العميل أو المشروع..." style="width: 100%; padding: 6px 12px 6px 32px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; text-align: right; background: #fff; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);">
                    <i class="fa fa-search" style="position: absolute; left: 12px; top: 8px; color: #94a3b8;"></i>
                </div>
            </div>
            <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                قائمة العملاء والمشاريع <i class="fa fa-users" style="color: #3b82f6;"></i>
            </h3>
        </div>

        <div style="overflow-x: auto;">
            <table class="n-table" id="main-table">
                <thead>
                    <tr>
                        <th style="text-align: right; padding-right: 24px;"><i class="fa fa-user-tag"></i> العميل / المشروع</th>
                        <th><i class="fa fa-percent"></i> التحصيل</th>
                        <th><i class="fa fa-file-invoice"></i> إجمالي الحساب</th>
                        <th style="color: #10b981;"><i class="fa fa-check-double"></i> المدفوع</th>
                        <th style="color: #ef4444;"><i class="fa fa-triangle-exclamation"></i> المتبقي</th>
                        <th><i class="fa fa-circle-info"></i> الحالة</th>
                    </tr>
                </thead>
                <tbody id="main-tbody">
                    @foreach($rows as $row)
                        @php
                            $isPaid = $row->remaining <= 0.009;
                            $pct    = $row->billed > 0 ? round($row->collected / $row->billed * 100) : 0;
                            $firstLetter = mb_substr($row->project->client->name, 0, 1);
                            $colors = ['#3b82f6', '#ea580c', '#8b5cf6', '#10b981', '#f43f5e', '#0ea5e9'];
                            $avatarColor = $colors[$loop->index % 6];
                        @endphp
                        <tr class="rv-row-item" onclick="openModal({{ $row->project->id }})"
                            data-name="{{ mb_strtolower($row->project->name . ' ' . $row->project->client->name) }}"
                            data-status="{{ $isPaid ? 'paid' : 'active' }}">
                            <td style="text-align: right; padding-right: 24px;">
                                <div style="display: flex; align-items: center; justify-content: flex-start; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, {{ $avatarColor }} 0%, #0f172a 150%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        {{ $firstLetter }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; color: #0f172a; font-size: 13px;">{{ $row->project->client->name }}</div>
                                        <div style="color: #64748b; font-size: 11px; margin-top: 3px; font-weight: 600;"><i class="fa fa-briefcase" style="color: #cbd5e1; margin-left: 2px;"></i> {{ $row->project->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 12px; font-weight: 800; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">{{ $pct }}%</span>
                            </td>
                            <td style="color: #475569; font-weight: 700; font-size: 14px;">{{ \App\Support\Money::format($row->billed) }} ج</td>
                            <td style="color: #10b981; font-weight: 800; font-size: 14px;">{{ \App\Support\Money::format($row->collected) }} ج</td>
                            <td style="color: #ef4444; font-weight: 800; font-size: 15px;">{{ \App\Support\Money::format($row->remaining) }} ج</td>
                            <td>
                                @if($isPaid)
                                    <span style="background: #ecfdf5; color: #047857; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #a7f3d0;"><i class="fa fa-check"></i> مسدد</span>
                                @else
                                    <span style="background: #fffbeb; color: #b45309; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #fde68a;"><i class="fa fa-clock"></i> قيد الانتظار</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div id="no-results" style="display:none; text-align: center; padding: 40px; color: #94a3b8;">
            <i class="fa fa-search" style="font-size: 32px; margin-bottom: 12px; display: block; color: #cbd5e1;"></i>
            <strong style="display: block; font-size: 15px; color: #64748b;">لا توجد نتائج مطابقة لبحثك</strong>
        </div>

    </div>

    {{-- Left Column (Analysis & Activities) -> Now second in HTML because 1fr is second --}}
    <div class="analysis-col">
        {{-- General Collection Analysis --}}
        <div class="n-card" style="margin-bottom: 20px; border-top: 4px solid #10b981;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; text-align: right; margin: 0 0 20px 0; display: flex; align-items: center; justify-content: space-between;">
                تحليل التحصيل العام
                <i class="fa fa-chart-line" style="color: #10b981; background: #ecfdf5; padding: 6px; border-radius: 8px;"></i>
            </h3>
            
            @php
                $totalExpected = $totals['total_collected'] + $totals['total_remaining'];
                $collectedPct = $totalExpected > 0 ? round(($totals['total_collected'] / $totalExpected) * 100, 1) : 0;
                $remainingPct = $totalExpected > 0 ? round(($totals['total_remaining'] / $totalExpected) * 100, 1) : 0;
            @endphp
            
            <div style="margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px;">
                    <span style="background: #ecfdf5; color: #047857; padding: 2px 6px; border-radius: 4px;">{{ $collectedPct }}%</span>
                    <span>المدفوعات المكتملة <i class="fa fa-check-circle" style="color: #10b981; margin-right: 4px;"></i></span>
                </div>
                <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; display: flex; justify-content: flex-end;">
                    <div style="width: {{ $collectedPct }}%; background: linear-gradient(90deg, #34d399 0%, #059669 100%); height: 100%; border-radius: 4px;"></div>
                </div>
            </div>
            
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px;">
                    <span style="background: #fffbeb; color: #b45309; padding: 2px 6px; border-radius: 4px;">{{ $remainingPct }}%</span>
                    <span>ديون قيد الانتظار <i class="fa fa-hourglass-half" style="color: #ea580c; margin-right: 4px;"></i></span>
                </div>
                <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; display: flex; justify-content: flex-end;">
                    <div style="width: {{ $remainingPct }}%; background: linear-gradient(90deg, #fb923c 0%, #ea580c 100%); height: 100%; border-radius: 4px;"></div>
                </div>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="n-card" style="border-top: 4px solid #8b5cf6;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; text-align: right; margin: 0 0 20px 0; display: flex; align-items: center; justify-content: space-between;">
                آخر النشاطات
                <i class="fa fa-bolt" style="color: #8b5cf6; background: #f3e8ff; padding: 6px 8px; border-radius: 8px;"></i>
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @forelse($recentActivities as $act)
                <div style="display: flex; gap: 12px; align-items: flex-start; text-align: right; direction: rtl; padding-bottom: 12px; border-bottom: 1px dashed #e2e8f0;">
                    @if(str_contains($act->type, 'خصم'))
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; font-weight: bold;">
                            <i class="fa fa-percent"></i>
                        </div>
                    @else
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; font-weight: bold;">
                            <i class="fa fa-arrow-down"></i>
                        </div>
                    @endif
                    
                    <div style="flex: 1;">
                        <div style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 4px;">
                            {{ str_contains($act->type, 'خصم') ? 'خصم ممنوح' : 'عملية تحصيل' }}
                        </div>
                        <div style="font-size: 11px; color: #64748b; line-height: 1.6; margin-bottom: 4px;">
                            @if(str_contains($act->type, 'خصم'))
                                <strong style="color: #0f172a;">{{ $act->party }}</strong><br>بقيمة: <span style="color: #ea580c; font-weight: bold;">{{ \App\Support\Money::format($act->discount ?? 0) }} ج.م</span>
                            @else
                                <strong style="color: #0f172a;">{{ $act->party }}</strong><br>تم تحصيل: <span style="color: #10b981; font-weight: bold;">{{ \App\Support\Money::format($act->amount) }} ج.م</span>
                            @endif
                        </div>
                        <div style="font-size: 10px; color: #94a3b8; font-weight: 600; text-align: left;">
                            <i class="fa fa-clock" style="margin-left: 2px;"></i> {{ $act->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align: center; color: #94a3b8; font-size: 13px; padding: 20px 0;">
                    <i class="fa fa-inbox" style="font-size: 24px; margin-bottom: 8px; display: block; color: #cbd5e1;"></i>
                    لا توجد نشاطات مؤخراً
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div> <!-- end project-tab -->


<div id="manual-recv-tab" class="tab-content" style="display:none;">
{{-- سلف ومستحقات أخرى (حركات يدوية) --}}
@if(isset($manualReceivables) && $manualReceivables->count())
@php $groupedRecv = $manualReceivables->groupBy('party'); @endphp
<div class="n-card" style="padding: 0; border-top: 4px solid #8b5cf6;">
  <div style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; background: #f8fafc; border-radius: 12px 12px 0 0;">
    <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
        سلف ومستحقات أخرى (حركات يدوية) <i class="fa fa-hand-holding-dollar" style="color: #8b5cf6;"></i>
    </h3>
    <span style="background: #fffbeb; color: #b45309; padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 800; border: 1px solid #fde68a;">
        إجمالي المتبقي: {{ \App\Support\Money::format($manualReceivables->sum(fn($r) => $r->remaining())) }} ج.م
    </span>
  </div>
  <div style="overflow-x: auto;">
      <table class="n-table" id="manual-table">
        <thead>
          <tr>
            <th style="text-align: right; padding-right: 24px;"><i class="fa fa-user"></i> الجهة / الشخص</th>
            <th><i class="fa fa-hashtag"></i> التعاملات</th>
            <th><i class="fa fa-file-invoice"></i> إجمالي المبلغ</th>
            <th style="color: #10b981;"><i class="fa fa-check-double"></i> المسدد</th>
            <th style="color: #ef4444;"><i class="fa fa-triangle-exclamation"></i> المتبقي</th>
            <th><i class="fa fa-circle-info"></i> الحالة</th>
          </tr>
        </thead>
        <tbody id="manual-tbody">
          @foreach($groupedRecv as $partyName => $partyItems)
            @php
              $partyTotal     = $partyItems->sum('total_amount');
              $partyPaid      = $partyItems->sum('paid_amount');
              $partyRemaining = $partyItems->sum(fn($r) => $r->remaining());
              $partyCount     = $partyItems->count();
              $allPaid        = $partyItems->every(fn($r) => $r->status === 'paid');
              $partyKey       = 'mrecv-' . md5($partyName);
              $firstLetter    = mb_substr($partyName, 0, 1);
            @endphp
            <tr data-status="{{ $allPaid ? 'paid' : 'pending' }}" style="cursor:pointer" onclick="openPartyModal('{{ $partyKey }}')">
              <td style="text-align: right; padding-right: 24px;">
                  <div style="display: flex; align-items: center; justify-content: flex-start; gap: 12px;">
                      <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6 0%, #0f172a 150%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                          {{ $firstLetter }}
                      </div>
                      <div style="font-weight: 800; color: #0f172a; font-size: 14px;">{{ $partyName }}</div>
                  </div>
              </td>
              <td><span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 800;">{{ $partyCount }}</span></td>
              <td style="color: #475569; font-weight: 800; font-size: 14px;">{{ \App\Support\Money::format($partyTotal) }} ج</td>
              <td style="color: #10b981; font-weight: 800; font-size: 14px;">{{ \App\Support\Money::format($partyPaid) }} ج</td>
              <td style="color: #ef4444; font-weight: 800; font-size: 15px;">{{ \App\Support\Money::format($partyRemaining) }} ج</td>
              <td>
                @if($allPaid)
                  <span style="background: #ecfdf5; color: #047857; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #a7f3d0;"><i class="fa fa-check"></i> مسدد</span>
                @else
                  <span style="background: #fffbeb; color: #b45309; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #fde68a;"><i class="fa fa-clock"></i> معلق</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
  </div>
</div>

{{-- توليد المودلز الخاصة بتفاصيل كل جهة/شخص --}}
@foreach($groupedRecv as $partyName => $partyItems)
  @php
    $partyTotal     = $partyItems->sum('total_amount');
    $partyPaid      = $partyItems->sum('paid_amount');
    $partyRemaining = $partyItems->sum(fn($r) => $r->remaining());
    $partyKey       = 'mrecv-' . md5($partyName);
  @endphp
  <div class="rv-modal" id="modal-{{ $partyKey }}" onclick="if(event.target===this) document.getElementById('modal-{{ $partyKey }}').style.display='none'">
    <div style="max-width: 900px; width: 95%; background: #ffffff; border-radius: 16px; overflow: hidden; margin: 40px auto; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
      
      {{-- Modal Header --}}
      <div style="padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; border-bottom: 1px solid #f1f5f9;">
        {{-- Right Side (Title & Tags) --}}
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <h3 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-user-circle" style="color: #3b82f6;"></i> {{ $partyName }}
          </h3>
          <div style="display: flex; gap: 8px;">
            <span style="background: #ef4444; color: #ffffff; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 6px;">
              المتبقي: {{ \App\Support\Money::format($partyRemaining) }} ج
            </span>
            <span style="background: #10b981; color: #ffffff; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 6px;">
              المدفوع: {{ \App\Support\Money::format($partyPaid) }} ج
            </span>
          </div>
        </div>

        {{-- Left Side (Action Buttons) --}}
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <button type="button" style="background: #334155; color: #ffffff; padding: 8px 16px; border: none; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
            <i class="fa fa-print"></i> طباعة التفاصيل
          </button>
          <button type="button" style="background: #facc15; color: #000000; padding: 8px 16px; border: none; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s;" onclick="openPartyBulkPay('{{ addslashes($partyName) }}', {{ $partyRemaining }}, 'partial', '{{ $partyKey }}')" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
            <i class="fa fa-money-bill-wave"></i> سداد جزئي
          </button>
          <button type="button" style="background: #10b981; color: #ffffff; padding: 8px 16px; border: none; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s;" onclick="openPartyBulkPay('{{ addslashes($partyName) }}', {{ $partyRemaining }}, 'full', '{{ $partyKey }}')" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
            <i class="fa fa-money-bill"></i> سداد كلي للعميل
          </button>
          <button type="button" style="background: #ffffff; color: #10b981; padding: 8px 12px; border: 1px solid #10b981; border-radius: 8px; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" onclick="waRecv('','{{ addslashes($partyName) }}',{{ $partyRemaining }})" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='#ffffff'">
            <i class="fa-brands fa-whatsapp"></i>
          </button>
          <button type="button" class="rv-x" onclick="document.getElementById('modal-{{ $partyKey }}').style.display='none'" style="font-size: 1.5rem; color: #94a3b8; border: none; background: transparent; cursor: pointer; padding: 0 8px;">×</button>
        </div>
      </div>

      {{-- Filters Area --}}
      <div style="padding: 16px 24px; background: #ffffff;">
        <div class="m-filter-bar" id="mf-{{ $partyKey }}" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center; justify-content: space-between; margin-bottom: 12px;">
          
          {{-- Status --}}
          <div style="display: flex; gap: 8px; align-items: center;">
            <button class="m-stat-btn active" data-stat="pending" onclick="applyModalFilter('{{ $partyKey }}', 'status', 'pending', this)" style="background: #2563eb; color: #ffffff; border: 1px solid #2563eb; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s;"><i class="fa fa-fire"></i> النشط</button>
            <button class="m-stat-btn" data-stat="paid" onclick="applyModalFilter('{{ $partyKey }}', 'status', 'paid', this)" style="background: #ffffff; color: #10b981; border: 1px solid #10b981; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s;"><i class="fa fa-check-circle"></i> المسدد</button>
            <button class="m-stat-btn" data-stat="all" onclick="applyModalFilter('{{ $partyKey }}', 'status', 'all', this)" style="background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s;"><i class="fa fa-list"></i> الكل</button>
          </div>

          {{-- Period --}}
          <div style="display: flex; align-items: center;">
            <div style="display: flex; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
              <button class="m-per-btn active" onclick="applyModalFilter('{{ $partyKey }}', 'period', 'all', this)" style="background: #475569; color: #ffffff; border: none; border-left: 1px solid #cbd5e1; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">الكل</button>
              <button class="m-per-btn" onclick="applyModalFilter('{{ $partyKey }}', 'period', 'month', this)" style="background: #ffffff; color: #475569; border: none; border-left: 1px solid #cbd5e1; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">شهر</button>
              <button class="m-per-btn" onclick="applyModalFilter('{{ $partyKey }}', 'period', 'week', this)" style="background: #ffffff; color: #475569; border: none; border-left: 1px solid #cbd5e1; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">أسبوع</button>
              <button class="m-per-btn" onclick="applyModalFilter('{{ $partyKey }}', 'period', 'yesterday', this)" style="background: #ffffff; color: #475569; border: none; border-left: 1px solid #cbd5e1; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">أمس</button>
              <button class="m-per-btn" onclick="applyModalFilter('{{ $partyKey }}', 'period', 'today', this)" style="background: #ffffff; color: #475569; border: none; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">اليوم</button>
            </div>
          </div>

          {{-- Dates --}}
          <div style="display: flex; gap: 8px; align-items: center; font-size: 12px; font-weight: 700; color: #475569;">
            من: <input type="date" id="dfrom-{{ $partyKey }}" onchange="applyModalFilter('{{ $partyKey }}', 'custom')" style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; outline: none; font-family: inherit;">
            إلى: <input type="date" id="dto-{{ $partyKey }}" onchange="applyModalFilter('{{ $partyKey }}', 'custom')" style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; outline: none; font-family: inherit;">
          </div>
        </div>

        <div style="position: relative;">
          <input type="text" id="search-{{ $partyKey }}" oninput="applyModalFilter('{{ $partyKey }}', 'search', this.value)" placeholder="ابحث في عمليات هذا العميل (اسم العملية، تاريخ)..." style="width: 100%; padding: 10px 16px 10px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #334155; outline: none; font-family: inherit; transition: 0.2s;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
          <i class="fa fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
        </div>
      </div>

      {{-- Blue Banner --}}
      <div style="background: #2563eb; color: #ffffff; padding: 8px 24px; font-size: 14px; font-weight: 800; display: flex; justify-content: flex-start; align-items: center;">
        إجمالي المتبقي: {{ \App\Support\Money::format($partyRemaining) }} ج
      </div>

      {{-- Table Area --}}
      <div style="background: #ffffff; max-height: 400px; overflow-y: auto;">
        <table class="n-table" id="mtbl-{{ $partyKey }}" style="width: 100%; margin: 0;">
          <thead style="position: sticky; top: 0; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); z-index: 10;">
            <tr>
              <th style="text-align: right; padding: 12px 24px; color: #475569;">التاريخ</th>
              <th style="text-align: right; color: #475569;">العملية / المنتج</th>
              <th style="color: #475569;">إجمالي الفاتورة</th>
              <th style="color: #475569;">المتبقي للدفع</th>
              <th style="color: #475569;">إجراءات الدفع</th>
            </tr>
          </thead>
          <tbody>
            @foreach($partyItems as $recv)
              <tr data-date="{{ $recv->date->format('Y-m-d') }}" data-status="{{ $recv->status === 'paid' ? 'paid' : 'pending' }}" data-search="{{ mb_strtolower($recv->description ?: 'عهدة / سلفة') }} {{ $recv->total_amount }} {{ $recv->date->format('Y-m-d') }}">
                <td style="text-align: right; padding: 16px 24px; font-weight: 700; color: #334155;">{{ $recv->date->format('Y-m-d') }}</td>
                <td style="text-align: right;">
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
                    <i class="fa fa-briefcase" style="color: #8b5cf6;"></i>
                    <span style="font-weight: 800; color: #0f172a; font-size: 13px;">{{ $recv->description ?: 'عهدة / سلفة' }}</span>
                  </div>
                </td>
                <td style="font-weight: 700; color: #475569; font-size: 13px;">{{ \App\Support\Money::format($recv->total_amount) }} ج</td>
                <td style="font-weight: 800; color: #ef4444; font-size: 13px;">{{ \App\Support\Money::format($recv->remaining()) }} ج</td>
                <td>
                  <div style="display: flex; gap: 8px; justify-content: center;">
                    @if($recv->status !== 'paid')
                      <button type="button" style="background: #eff6ff; color: #3b82f6; border: none; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'" onclick="
                        document.getElementById('modal-{{ $partyKey }}').style.display='none';
                        openManualRecvPayModal({{ $recv->id }}, {{ $recv->remaining() }}, '{{ addslashes($recv->party . ($recv->description ? ' - ' . $recv->description : '')) }}')
                      ">
                        <i class="fa fa-cash-register"></i> تحصيل
                      </button>
                      <button type="button" style="background: #fffbeb; color: #d97706; border: none; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s;" onmouseover="this.style.background='#fef3c7'" onmouseout="this.style.background='#fffbeb'" onclick="
                         document.getElementById('modal-{{ $partyKey }}').style.display='none';
                      ">
                        <i class="fa fa-percent"></i> خصم
                      </button>
                    @else
                      <span style="color: #10b981; font-size: 12px; font-weight: 800;"><i class="fa fa-check-circle"></i> مسدد</span>
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
@endif
</div> <!-- end manual-recv-tab -->

{{-- أقساط متأخرة --}}
@if($overdueInstallments->count())
<div class="rv-box">
  <div class="rv-boxhead">
    <h2 style="color:var(--bad)"><i class="fa fa-triangle-exclamation"></i> أقساط متأخرة ({{ $overdueInstallments->count() }})</h2>
    <span class="c" style="color:var(--bad);font-weight:700">إجمالي: {{ \App\Support\Money::format($overdueInstallments->sum('amount')) }} ج.م</span>
  </div>
  @foreach($overdueInstallments as $inst)
    <div class="rv-line">
      <div class="grow">
        <div class="t">{{ $inst->label }}</div>
        <div class="s">{{ $inst->project->name }} — {{ $inst->project->client->name }}@if($inst->band) | {{ $inst->band->name }}@endif</div>
      </div>
      <div style="text-align:left">
        <div style="font-weight:700;color:var(--bad)">{{ \App\Support\Money::format($inst->amount) }} ج</div>
        <div class="s">استحق: {{ $inst->due_date->format('d/m/Y') }}</div>
      </div>
      <form method="POST" action="{{ route('installments.markPaid', $inst) }}" class="no-print" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
        @csrf
        <button type="submit" style="padding:6px 13px;background:var(--ink);color:#fff;border:none;border-radius:7px;font-weight:700;font-size:.75rem;cursor:pointer">تحصيل</button>
      </form>
    </div>
  @endforeach
</div>
@endif

{{-- أقساط قادمة --}}
@if($upcomingInstallments->count())
<div class="rv-box">
  <div class="rv-boxhead">
    <h2><i class="fa fa-calendar-days" style="color:var(--mut)"></i> أقساط قادمة — خلال 60 يوماً ({{ $upcomingInstallments->count() }})</h2>
    <span class="c" style="font-weight:700">إجمالي: {{ \App\Support\Money::format($upcomingInstallments->sum('amount')) }} ج.م</span>
  </div>
  @foreach($upcomingInstallments as $inst)
    @php $daysLeft = (int) now()->diffInDays($inst->due_date, false); @endphp
    <div class="rv-line">
      <span style="min-width:44px;text-align:center;font-size:.7rem;font-weight:700;color:var(--mut);border:1px solid var(--ln);border-radius:7px;padding:4px 2px">{{ $daysLeft }} يوم</span>
      <div class="grow">
        <div class="t">{{ $inst->label }}</div>
        <div class="s">{{ $inst->project->name }} — {{ $inst->project->client->name }}@if($inst->band) | {{ $inst->band->name }}@endif</div>
      </div>
      <div style="text-align:left">
        <div style="font-weight:700">{{ \App\Support\Money::format($inst->amount) }} ج</div>
        <div class="s">يستحق: {{ $inst->due_date->format('d/m/Y') }}</div>
      </div>
    </div>
  @endforeach
</div>
@endif

{{-- ═══ المودالات — ضيّقة وطولية ═══ --}}
@foreach($rows as $row)
  @php
    $proj    = $row->project;
    $pct     = $row->billed > 0 ? round($row->collected / $row->billed * 100) : 0;
    $isPaid  = $row->remaining <= 0.009;
    $hasCont = $proj->contracts->count() > 0;
    // مستحق إضافي خارج نطاق العقد (فوترة جديدة بعد العقد) — لو المشروع معموله
    // عقد، التحصيل المباشر هنا بيقتصر على القدر ده بس، وعقد التقسيط فاضل شغال لوحده
    $hasExcess  = $hasCont && $row->excess > 0.009;
    $payAmount  = $hasCont ? (float) $row->excess : (float) $row->remaining;
    $clientPhone = $proj->client->phone ?? '';
    $invoiceData = ['project' => $proj->name, 'client' => $proj->client->name, 'phone' => $clientPhone, 'billed' => (float) $row->billed, 'collected' => (float) $row->collected, 'remaining' => (float) $row->remaining, 'company' => $settings->company_name ?? ''];
  @endphp
  <div class="rv-modal" id="modal{{ $proj->id }}" onclick="if(event.target===this) closeModal({{ $proj->id }})">
    <div style="max-width: 900px; width: 95%; background: #ffffff; border-radius: 16px; overflow: hidden; margin: 40px auto; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
      
      {{-- Modal Header --}}
      <div style="padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; border-bottom: 1px solid #f1f5f9;">
        {{-- Right Side (Title & Tags) --}}
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <h3 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-building" style="color: #3b82f6;"></i> {{ $proj->name }}
          </h3>
          <small style="color: #64748b; font-weight: 700; display: flex; align-items: center; gap: 6px;">
            <i class="fa fa-user"></i> {{ $proj->client->name }} @if($clientPhone) · <span dir="ltr">{{ $clientPhone }}</span> @endif
          </small>
          <div style="display: flex; gap: 8px; margin-top: 4px; flex-wrap: wrap;">
            <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 800; border: 1px solid #e2e8f0;">
              المفوتر: {{ \App\Support\Money::format($row->billed) }} ج
            </span>
            <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">
              المحصّل: {{ \App\Support\Money::format($row->collected) }} ج
            </span>
            <span style="background: {{ $isPaid ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $isPaid ? '#10b981' : '#ef4444' }}; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 800; border: 1px solid {{ $isPaid ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)' }};">
              المتبقي: {{ \App\Support\Money::format($row->remaining) }} ج
            </span>
            @if($row->discount > 0)
            <span style="background: rgba(245, 158, 11, 0.1); color: #d97706; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 800; border: 1px solid rgba(245, 158, 11, 0.3);">
              إجمالي الخصومات: {{ \App\Support\Money::format($row->discount) }} ج
            </span>
            @endif
          </div>
        </div>

        {{-- Left Side (Action Buttons) --}}
        <div class="no-print" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          @if($hasCont)
            <a href="{{ route('installments.index') }}" style="background: #2563eb; color: #ffffff; padding: 8px 16px; border: none; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; text-decoration: none; transition: 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'"><i class="fa fa-file-contract"></i> صفحة الأقساط</a>
          @endif
          @if($hasExcess || (!$hasCont && !$isPaid))
            <button type="button" id="rv-partial-{{ $proj->id }}" onclick="recvPartial({{ $proj->id }})" style="background: #facc15; color: #000000; padding: 8px 16px; border: none; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; transition: 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'"><i class="fa fa-money-bill-wave"></i> تحصيل جزئي</button>
            <button type="button" id="rv-full-{{ $proj->id }}" onclick="recvFull({{ $proj->id }}, {{ $payAmount }})" style="background: #10b981; color: #ffffff; padding: 8px 16px; border: none; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; transition: 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'"><i class="fa fa-check-double"></i> تحصيل {{ $hasCont ? 'الزيادة' : 'كلي' }}</button>
          @elseif(!$hasCont && $isPaid)
            <span style="background: #ecfdf5; color: #047857; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 800; border: 1px solid #a7f3d0;"><i class="fa fa-check-circle"></i> مكتمل</span>
          @endif
          <button type="button" style="background: #334155; color: #ffffff; padding: 8px 16px; border: none; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; transition: 0.2s;" onclick="openDiscountPanel({{ $proj->id }})" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'"><i class="fa fa-percent"></i> خصم</button>
          <button type="button" style="background: #ffffff; color: #10b981; padding: 8px 12px; border: 1px solid #10b981; border-radius: 8px; font-size: 16px; cursor: pointer; transition: 0.2s;" onclick="waRecv('{{ $clientPhone }}','{{ addslashes($proj->name) }}',{{ $row->remaining }})" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='#ffffff'"><i class="fa-brands fa-whatsapp"></i></button>
          <button type="button" style="background: #ffffff; color: #334155; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 16px; cursor: pointer; transition: 0.2s;" onclick='printInvoice({{ $proj->id }}, @json($invoiceData))' onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'"><i class="fa fa-print"></i></button>
          <button type="button" class="rv-x" onclick="closeModal({{ $proj->id }})" style="font-size: 1.5rem; color: #94a3b8; border: none; background: transparent; cursor: pointer; padding: 0 8px;">×</button>
        </div>
      </div>

      <div style="padding: 24px; background: #ffffff;">
        
        {{-- Progress Bar --}}
        <div style="background: #f1f5f9; border-radius: 99px; height: 6px; margin-bottom: 24px; overflow: hidden;">
          <div style="height: 100%; background: {{ $isPaid ? '#10b981' : '#3b82f6' }}; width: {{ min($pct, 100) }}%; transition: 1s ease-in-out;"></div>
        </div>

        {{-- Forms (Discount & Pay) --}}
        <div class="rv-pay no-print" id="disc-panel-{{ $proj->id }}" style="display:none;margin-bottom:24px;border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px;">
          <div class="hd" style="color:#0f172a; margin-bottom: 12px; font-weight: 800;">
            <span><i class="fa fa-percent" style="color: #3b82f6;"></i> منح خصم للمشروع</span>
            <button type="button" class="rv-x" style="color:var(--soft);font-size:1rem" onclick="hideDiscountPanel({{ $proj->id }})">×</button>
          </div>
          <form method="POST" action="{{ route('projects.discount', $proj) }}" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
            @csrf
            <div style="font-size: 13px; color: #475569; margin-bottom: 12px; font-weight: 700;">إجمالي الخصومات الحالية: <strong>{{ \App\Support\Money::format($proj->discounts->sum('amount')) }} ج</strong></div>
            <div class="rv-row2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569;">مبلغ الخصم الإضافي (ج) *</label>
                <input type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; margin-top: 4px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569;">تاريخ الخصم *</label>
                <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; margin-top: 4px;">
              </div>
            </div>
            <div style="margin-top: 12px;">
              <label style="font-size: 12px; font-weight: 700; color: #475569;">ملاحظات/سبب الخصم</label>
              <input type="text" name="notes" placeholder="اختياري" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; margin-top: 4px;">
            </div>
            <button type="submit" style="background: #0f172a; color: #ffffff; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 800; cursor: pointer; margin-top: 16px; width: 100%;">تحديث الخصم</button>
          </form>
        </div>

        <div class="rv-pay no-print" id="pay-panel-{{ $proj->id }}" style="display:none;margin-bottom:24px;border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px;">
          <div class="hd" style="margin-bottom: 12px; font-weight: 800; color: #0f172a;">
            <span><i class="fa fa-cash-register" style="color: #10b981;"></i> تسجيل تحصيل من العميل</span>
            <button type="button" class="rv-x" style="color:var(--soft);font-size:1rem" onclick="hidePayPanel({{ $proj->id }})">×</button>
          </div>
          @php $reopenHere = session('reopen_project') == $proj->id; @endphp
          @if($reopenHere && $errors->any())
            <div style="background: #fef2f2; color: #b91c1c; border: 1px solid #f87171; border-radius: 8px; padding: 10px; margin-bottom: 12px; font-size: 13px; font-weight: 700;">
              @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
            </div>
          @endif
          @if($payAmount > 0.009)
          <form method="POST" action="{{ route('receivables.pay', $proj) }}" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
            @csrf
            <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
              <span style="background: #eff6ff; color: #2563eb; padding: 4px 12px; border-radius: 99px; font-size: 12px; font-weight: 800; cursor: pointer;" onclick="setAmt({{ $proj->id }}, {{ $payAmount }})">المتبقي: {{ \App\Support\Money::format($payAmount) }} ج</span>
              <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 99px; font-size: 12px; font-weight: 800; cursor: pointer;" onclick="setAmt({{ $proj->id }}, {{ round($payAmount * 0.5, 2) }})">النصف: {{ \App\Support\Money::format($payAmount * 0.5) }} ج</span>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
              <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569;">المبلغ (ج) *</label>
                <input type="number" step="0.01" min="0.01" name="amount" id="recv_amt_{{ $proj->id }}" value="{{ $reopenHere ? old('amount') : '' }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; margin-top: 4px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569;">التاريخ *</label>
                <input type="date" name="date" value="{{ $reopenHere ? old('date', today()->format('Y-m-d')) : today()->format('Y-m-d') }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; margin-top: 4px;">
              </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
              <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569;">الخصم (ج)</label>
                <input type="number" step="0.01" min="0" name="discount" id="recv_disc_{{ $proj->id }}" placeholder="0.00" value="{{ $reopenHere ? old('discount') : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; margin-top: 4px;">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569;">ملاحظات</label>
                <input type="text" name="notes" placeholder="اختياري" value="{{ $reopenHere ? old('notes') : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; margin-top: 4px;">
              </div>
            </div>
            <div style="margin-bottom: 12px;">
              <label style="font-size: 12px; font-weight: 700; color: #475569;"><i class="fa fa-wallet"></i> المحفظة *</label>
              <div style="margin-top: 4px;">
                @include('partials._wallet-select', ['wallets' => $wallets, 'bare' => true, 'required' => true, 'selectStyle' => 'width:100%', 'selected' => $reopenHere ? old('account_id') : null])
              </div>
            </div>
            @if($proj->bands->count())
              @php $oldBandChoice = $reopenHere ? old('band_choice', 'general') : 'general'; @endphp
              <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                <label style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 8px; display: block;">ارتباط التحصيل ببند</label>
                <div style="display: flex; gap: 16px; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px;">
                  <label style="cursor: pointer; display: flex; align-items: center; gap: 4px;"><input type="radio" name="band_choice" value="general" {{ $oldBandChoice === 'general' ? 'checked' : '' }} onchange="toggleRecvBand({{ $proj->id }}, this.value)"> دفعة عامة للمشروع</label>
                  <label style="cursor: pointer; display: flex; align-items: center; gap: 4px;"><input type="radio" name="band_choice" value="band" {{ $oldBandChoice === 'band' ? 'checked' : '' }} onchange="toggleRecvBand({{ $proj->id }}, this.value)"> دفعة لبند معين</label>
                </div>
                <select name="band_id" id="recv-band-{{ $proj->id }}" {{ $oldBandChoice === 'band' ? '' : 'disabled' }} style="display:{{ $oldBandChoice === 'band' ? 'block' : 'none' }}; width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; margin-top: 4px;">
                  <option value="">— اختر البند —</option>
                  @foreach($proj->bands as $band)
                    <option value="{{ $band->id }}" {{ $reopenHere && (int) old('band_id') === $band->id ? 'selected' : '' }}>{{ $band->name }}</option>
                  @endforeach
                </select>
              </div>
            @endif
            <button type="submit" style="background: #10b981; color: #ffffff; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 800; cursor: pointer; margin-top: 20px; width: 100%;"><i class="fa fa-check"></i> تسجيل التحصيل</button>
          </form>
          @else
            <div style="text-align: center; color: #10b981; font-weight: 800; padding: 12px; background: #ecfdf5; border-radius: 8px;">
              <i class="fa fa-check-circle"></i> تم تحصيل كامل المستحق.
            </div>
          @endif
        </div>

        {{-- Contracts --}}
        @if($hasCont)
          <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
            <div style="font-size: 13px; font-weight: 800; color: #d97706; margin-bottom: 12px;"><i class="fa fa-file-contract"></i> نظام التقسيط مفعل للمشروع - يتم السداد من صفحة الأقساط.</div>
            <table class="n-table" style="width: 100%; background: #ffffff;">
              <thead><tr><th style="text-align:right">المتعاقد عليه</th><th>النوع</th><th>الإجمالي</th><th>المحصّل</th><th>المتبقي</th></tr></thead>
              <tbody>
                @foreach($proj->contracts as $c)
                  <tr>
                    <td style="text-align:right; font-weight: 800;">{{ $c->product_name }}</td>
                    <td style="font-weight: 700; color: #475569;">{{ $c->band_id ? 'بند: ' . ($c->band?->name ?? '—') : 'المشروع كامل' }}</td>
                    <td style="font-weight: 700;">{{ \App\Support\Money::format($c->total_after_interest) }}</td>
                    <td style="color: #10b981; font-weight: 800;">{{ \App\Support\Money::format($c->down_payment + $c->payments->sum('amount_paid')) }}</td>
                    <td style="color: {{ $c->remaining_balance > 0 ? '#ef4444' : '#10b981' }}; font-weight: 800;">{{ \App\Support\Money::format($c->remaining_balance) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif

        {{-- Chic Filters for History --}}
        <div class="m-filter-bar no-print" id="mf-{{ $proj->id }}" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center; justify-content: space-between; margin-bottom: 16px;">
          
          <div style="display: flex; align-items: center;">
            <div style="display: flex; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
              <button type="button" class="m-per-btn active" onclick="applyModalFilter('{{ $proj->id }}', 'period', 'all', this)" style="background: #475569; color: #ffffff; border: none; border-left: 1px solid #cbd5e1; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">الكل</button>
              <button type="button" class="m-per-btn" onclick="applyModalFilter('{{ $proj->id }}', 'period', 'month', this)" style="background: #ffffff; color: #475569; border: none; border-left: 1px solid #cbd5e1; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">شهر</button>
              <button type="button" class="m-per-btn" onclick="applyModalFilter('{{ $proj->id }}', 'period', 'week', this)" style="background: #ffffff; color: #475569; border: none; border-left: 1px solid #cbd5e1; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">أسبوع</button>
              <button type="button" class="m-per-btn" onclick="applyModalFilter('{{ $proj->id }}', 'period', 'yesterday', this)" style="background: #ffffff; color: #475569; border: none; border-left: 1px solid #cbd5e1; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">أمس</button>
              <button type="button" class="m-per-btn" onclick="applyModalFilter('{{ $proj->id }}', 'period', 'today', this)" style="background: #ffffff; color: #475569; border: none; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s;">اليوم</button>
            </div>
          </div>

          <div style="display: flex; gap: 8px; align-items: center; font-size: 12px; font-weight: 700; color: #475569;">
            من: <input type="date" id="dfrom-{{ $proj->id }}" onchange="applyModalFilter('{{ $proj->id }}', 'custom')" style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; outline: none; font-family: inherit;">
            إلى: <input type="date" id="dto-{{ $proj->id }}" onchange="applyModalFilter('{{ $proj->id }}', 'custom')" style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; outline: none; font-family: inherit;">
          </div>
          
          <div style="flex-grow: 1; max-width: 300px; position: relative;">
            <input type="text" id="search-{{ $proj->id }}" oninput="applyModalFilter('{{ $proj->id }}', 'search', this.value)" placeholder="ابحث في التحصيلات..." style="width: 100%; padding: 8px 16px 8px 36px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #334155; outline: none; font-family: inherit; transition: 0.2s;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
            <i class="fa fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
          </div>
        </div>

        {{-- History Table --}}
        <div style="border-radius: 12px; overflow: hidden; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px;">
          <table class="n-table" id="ptbl-{{ $proj->id }}" style="width: 100%; margin: 0;">
            <thead style="background: #f8fafc;">
              <tr>
                <th style="text-align: right; padding: 12px 24px; color: #475569;">التاريخ</th>
                <th style="text-align: right; color: #475569;">البيان</th>
                <th style="color: #475569;">المبلغ</th>
              </tr>
            </thead>
            <tbody>
              @forelse($proj->clientPayments as $pay)
                <tr data-date="{{ \Carbon\Carbon::parse($pay->date)->format('Y-m-d') }}" data-search="{{ mb_strtolower($pay->description ?: 'تحصيل مباشر') }} {{ $pay->amount }}" style="cursor: pointer;" onclick="showPayDetail(this)" data-full-date="{{ \Carbon\Carbon::parse($pay->date)->format('d/m/Y') }}" data-amount="{{ number_format($pay->amount, 2) }}" data-discount="{{ \App\Support\Money::format($pay->discount, 2) }}" data-description="{{ $pay->description ?: 'تحصيل مباشر' }}" data-band="{{ $pay->band->name ?? '' }}">
                  <td style="text-align: right; padding: 16px 24px; font-weight: 700; color: #334155;">{{ \Carbon\Carbon::parse($pay->date)->format('Y-m-d') }}</td>
                  <td style="text-align: right;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6;"></span>
                      <span style="font-weight: 800; color: #0f172a; font-size: 13px;">{{ $pay->description ?: 'تحصيل مباشر' }}</span>
                    </div>
                  </td>
                  <td style="font-weight: 800; color: #10b981; font-size: 13px;">
                    {{ \App\Support\Money::format($pay->amount) }} ج
                    @if((float) $pay->discount > 0)
                      <span style="color: #94a3b8; font-size: 11px; margin-right: 4px;">(خصم: {{ \App\Support\Money::format($pay->discount) }})</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" style="text-align: center; padding: 24px; color: #94a3b8; font-weight: 700;">لا توجد تحصيلات مسجلة بعد</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Bands --}}
        @if($proj->bands->count())
          <div class="no-print">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer;" onclick="toggleBands({{ $proj->id }})">
              <span style="font-weight: 800; color: #334155;"><i class="fa fa-list-ul" style="margin-left: 6px;"></i> البنود المتعاقد عليها ({{ $proj->bands->count() }})</span>
              <i class="fa fa-chevron-down" id="bands-icon-{{ $proj->id }}" style="font-size: 12px; color: #94a3b8; transition: 0.2s;"></i>
            </div>
            <div id="bands-body-{{ $proj->id }}" style="display: none; padding-top: 8px;">
              <table class="n-table" style="width: 100%;">
                <tbody>
                  @foreach($proj->bands as $band)
                    @php $st = $band->status ?? 'pending'; @endphp
                    <tr>
                      <td style="font-weight: 800; color: #0f172a; text-align: right; padding: 12px 16px;">{{ $band->name }}</td>
                      <td style="font-weight: 700; color: #475569; text-align: center;">{{ \App\Support\Money::format($band->client_price ?? 0) }} ج</td>
                      <td style="text-align: left; padding: 12px 16px;">
                        <span style="background: {{ $st === 'completed' ? '#ecfdf5' : ($st === 'in_progress' || $st === 'active' ? '#eff6ff' : '#f1f5f9') }}; color: {{ $st === 'completed' ? '#10b981' : ($st === 'in_progress' || $st === 'active' ? '#3b82f6' : '#64748b') }}; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800;">
                          {{ $st === 'completed' ? 'مكتمل' : ($st === 'in_progress' || $st === 'active' ? 'جاري' : 'معلق') }}
                        </span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif

      </div>
    </div>
  </div>
  @if(session('reopen_project') == $proj->id)
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        openModal({{ $proj->id }});
        showPayPanel({{ $proj->id }});
      });
    </script>
  @endif
@endforeach

</div>{{-- /rv --}}

{{-- تفاصيل تحصيل — مودال واحد مشترك لكل الصفوف، بيتملى بالـ JS من data-* الصف
     اللي اتدوس عليه، بدل ما نعمل مودال منفصل لكل تحصيل --}}
<div class="rv-modal" id="payDetailModal" onclick="if(event.target===this) closePayDetail()">
  <div class="rv-card" style="max-width:420px">
    <div class="rv-mhead">
      <div class="nm">تفاصيل التحصيل</div>
      <button type="button" class="rv-x" onclick="closePayDetail()">×</button>
    </div>
    <div class="rv-mbody" style="padding:16px 18px">
      <div class="rv-nums" style="grid-template-columns:1fr 1fr">
        <div class="rv-num"><div class="l">التاريخ</div><div class="v" id="pd-date" style="font-size:1rem"></div></div>
        <div class="rv-num ok"><div class="l">المبلغ</div><div class="v" id="pd-amount" style="font-size:1rem"></div></div>
      </div>
      <div style="margin-top:10px;font-size:.8rem;line-height:1.9">
        <div id="pd-discount-row"><b>الخصم:</b> <span id="pd-discount"></span></div>
        <div id="pd-band-row"><b>البند:</b> <span id="pd-band"></span></div>
        <div><b>البيان:</b> <span id="pd-desc"></span></div>
      </div>
    </div>
  </div>
</div>

{{-- Pay Manual Receivable Modal --}}
<div class="rv-modal" id="manual-recv-pay-modal" onclick="if(event.target===this) closeManualRecvPayModal()">
  <div class="rv-card" style="max-width:460px">
    <div class="rv-mhead">
      <div class="nm">تحصيل سلفة / مستحق</div>
      <button type="button" class="rv-x" onclick="closeManualRecvPayModal()">×</button>
    </div>
    <div class="rv-mbody" style="padding:16px 18px">
      <p id="manual-recv-pay-desc" class="muted" style="margin:0 0 20px;font-size:.85rem;color:var(--mut)"></p>
      <form id="manual-recv-pay-form" method="POST" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
        @csrf
        <div class="rv-pay" style="border:none;padding:0;background:none">
          <label style="margin-bottom:8px;display:block">المبلغ المحصل (ج.م) *</label>
          <div style="display:flex;gap:10px;margin-bottom:12px">
            <button type="button" class="btn pos sm" id="manual-recv-pay-full-btn" onclick="
              document.getElementById('manual-recv-pay-amount').value = document.getElementById('manual-recv-pay-amount').max;
              document.getElementById('manual-recv-pay-amount').readOnly = true;
              this.classList.add('pos'); this.classList.remove('ghost');
              document.getElementById('manual-recv-pay-partial-btn').classList.add('ghost');
              document.getElementById('manual-recv-pay-partial-btn').classList.remove('warn');
            ">تحصيل كلي</button>
            
            <button type="button" class="btn ghost sm" id="manual-recv-pay-partial-btn" onclick="
              document.getElementById('manual-recv-pay-amount').value = '';
              document.getElementById('manual-recv-pay-amount').readOnly = false;
              document.getElementById('manual-recv-pay-amount').focus();
              this.classList.add('warn'); this.classList.remove('ghost');
              document.getElementById('manual-recv-pay-full-btn').classList.add('ghost');
              document.getElementById('manual-recv-pay-full-btn').classList.remove('pos');
            ">تحصيل جزئي</button>
          </div>
          <input type="number" name="amount" id="manual-recv-pay-amount" min="0.01" step="0.01" required readonly>
          <small class="muted" id="manual-recv-pay-max-note" style="display:block;margin-top:4px"></small>
          
          <label style="margin-top:12px">المحفظة *</label>
          @include('partials._wallet-select', ['wallets' => $wallets, 'bare' => true, 'required' => true, 'selectStyle' => 'width:100%'])
          
          <label style="margin-top:12px">تاريخ التحصيل *</label>
          <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required>
          
          <button type="submit" style="margin-top:20px; background:#0d6efd; color:#fff; padding:10px 20px; border-radius:8px; border:none; font-weight:bold; cursor:pointer; width:100%; display:block;"><i class="fa fa-check"></i> تسجيل التحصيل</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Pay Manual Receivable Party (Bulk) Modal --}}
<div class="rv-modal" id="manual-party-pay-modal" onclick="if(event.target===this) closePartyBulkPay()">
  <div class="rv-card" style="max-width:460px">
    <div class="rv-mhead">
      <div class="nm">تحصيل ديون <span id="manual-party-pay-name"></span></div>
      <button type="button" class="rv-x" onclick="closePartyBulkPay()">×</button>
    </div>
    <div class="rv-mbody" style="padding:16px 18px">
      <form id="manual-party-pay-form" method="POST" action="{{ route('receivables.manual.party.pay') }}" onsubmit="const b=this.querySelector('button[type=submit]'); setTimeout(() => { b.style.pointerEvents='none'; b.style.opacity='0.8'; b.style.color='#fff'; b.style.backgroundColor='#0d6efd'; b.style.borderColor='#0d6efd'; b.innerHTML='<i class=\'fa fa-spinner fa-spin\'></i> جاري التنفيذ...'; }, 10);">
        @csrf
        <input type="hidden" name="party_name" id="manual-party-pay-party">
        <div class="rv-pay" style="border:none;padding:0;background:none">
          <label style="margin-bottom:8px;display:block">المبلغ المحصل (ج.م) *</label>
          <input type="number" name="amount" id="manual-party-pay-amount" min="0.01" step="0.01" required>
          <small class="muted" id="manual-party-pay-max-note" style="display:block;margin-top:4px"></small>
          
          <label style="margin-top:12px">المحفظة *</label>
          @include('partials._wallet-select', ['wallets' => $wallets, 'bare' => true, 'required' => true, 'selectStyle' => 'width:100%', 'fieldName' => 'account_id'])
          
          <label style="margin-top:12px">تاريخ التحصيل *</label>
          <input type="date" name="pay_date" value="{{ today()->toDateString() }}" required>
          
          <button type="submit" style="margin-top:20px; background:#0d6efd; color:#fff; padding:10px 20px; border-radius:8px; border:none; font-weight:bold; cursor:pointer; width:100%; display:block;"><i class="fa fa-check"></i> تسجيل التحصيل للعميل</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
function openManualRecvPayModal(id, remaining, desc) {
  document.getElementById('manual-recv-pay-desc').textContent = desc;
  document.getElementById('manual-recv-pay-amount').max = remaining;
  document.getElementById('manual-recv-pay-amount').value = remaining;
  document.getElementById('manual-recv-pay-amount').readOnly = true;
  
  const fullBtn = document.getElementById('manual-recv-pay-full-btn');
  const partBtn = document.getElementById('manual-recv-pay-partial-btn');
  if(fullBtn && partBtn) {
    fullBtn.className = 'btn pos sm';
    partBtn.className = 'btn ghost sm';
  }

  document.getElementById('manual-recv-pay-max-note').textContent = 'الحد الأقصى: ' + remaining.toLocaleString('ar-EG') + ' ج.م';
  document.getElementById('manual-recv-pay-form').action = '/receivables/manual/' + id + '/pay';
  
  const submitBtn = document.querySelector('#manual-recv-pay-form button[type=submit]');
  if(submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fa fa-check"></i> تسجيل التحصيل'; }

  const walletSelect = document.querySelector('#manual-recv-pay-form select[name="account_id"]');
  if (walletSelect) walletSelect.selectedIndex = 0;
  
  document.getElementById('manual-recv-pay-modal').classList.add('open');
  document.body.style.overflow = 'hidden';
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
  if(submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fa fa-check"></i> تسجيل التحصيل للعميل'; }

  document.getElementById('manual-party-pay-modal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closePartyBulkPay() {
  document.getElementById('manual-party-pay-modal').classList.remove('open');
  document.body.style.overflow = '';
}

function closeManualRecvPayModal() {
  document.getElementById('manual-recv-pay-modal').classList.remove('open');
  document.body.style.overflow = '';
}
/* ── مودال خفيف (من غير Bootstrap) ─────────── */
function openModal(id) {
  document.getElementById('modal' + id).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById('modal' + id).classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.rv-modal.open').forEach(m => {
    m.classList.remove('open'); document.body.style.overflow = '';
  });
});

function openPartyModal(key) {
  const modal = document.getElementById('modal-' + key);
  if (modal) {
    modal.style.display = 'flex';
  }
}

/* ── فورم التحصيل ──────────────────────────── */
function showPayPanel(id) {
  const p = document.getElementById('pay-panel-' + id);
  if (p) { p.style.display = 'block'; p.scrollIntoView({behavior:'smooth', block:'nearest'}); }
}
function hidePayPanel(id) {
  const p = document.getElementById('pay-panel-' + id);
  if (p) p.style.display = 'none';
}
// الزرار اللي المستخدم اختاره (كلي/جزئي) هو الوحيد اللي بيتلوّن — التاني بيرجع لشكله العادي
function markRecvMode(id, mode) {
  const full = document.getElementById('rv-full-' + id);
  const partial = document.getElementById('rv-partial-' + id);
  if (full) full.classList.toggle('main', mode === 'full');
  if (partial) partial.classList.toggle('main', mode === 'partial');
}
function recvPartial(id) {
  showPayPanel(id);
  markRecvMode(id, 'partial');
}
function recvFull(id, amt) {
  showPayPanel(id);
  setAmt(id, amt);
  markRecvMode(id, 'full');
}
function setAmt(id, amt) {
  showPayPanel(id);
  const i = document.getElementById('recv_amt_' + id);
  if (i) { i.value = parseFloat(amt).toFixed(2); i.focus(); }
}

/* ── بوب أب تفاصيل صف في سجل التحصيلات ────── */
function showPayDetail(row) {
  const d = row.dataset;
  document.getElementById('pd-date').textContent = d.fullDate;
  document.getElementById('pd-amount').textContent = d.amount + ' ج';
  document.getElementById('pd-desc').textContent = d.description;
  const discRow = document.getElementById('pd-discount-row');
  if (parseFloat(d.discount) > 0) {
    document.getElementById('pd-discount').textContent = d.discount + ' ج';
    discRow.style.display = '';
  } else {
    discRow.style.display = 'none';
  }
  const bandRow = document.getElementById('pd-band-row');
  if (d.band) {
    document.getElementById('pd-band').textContent = d.band;
    bandRow.style.display = '';
  } else {
    bandRow.style.display = 'none';
  }
  document.getElementById('payDetailModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closePayDetail() {
  document.getElementById('payDetailModal').classList.remove('open');
  document.body.style.overflow = '';
}

/* ── دفعة عامة أم تحت بند ─────────────────── */
function toggleRecvBand(id, val) {
  const sel = document.getElementById('recv-band-' + id);
  if (!sel) return;
  if (val === 'band') {
    sel.style.display = 'block'; sel.disabled = false; sel.required = true;
  } else {
    sel.style.display = 'none'; sel.disabled = true; sel.required = false; sel.value = '';
  }
}

/* ── فلاتر سجل التحصيلات ───────────────────── */
function applyModalFilter(id, filterType, filterValue = null, btnEl = null) {
  const tableId = id.toString().startsWith('mrecv-') ? 'mtbl-' + id : 'ptbl-' + id;
  const table = document.getElementById(tableId);
  if (!table) return;

  const bar = document.getElementById('mf-' + id);
  if (!bar) return;

  if (!bar.dataset.status) bar.dataset.status = 'all';
  if (!bar.dataset.period) bar.dataset.period = 'all';
  if (!bar.dataset.search) bar.dataset.search = '';

  if (filterType === 'status') {
    bar.dataset.status = filterValue;
    if (btnEl) {
      bar.querySelectorAll('.m-stat-btn').forEach(b => { b.classList.remove('active'); b.style.background = '#ffffff'; b.style.color = '#64748b'; b.style.borderColor = '#cbd5e1'; });
      btnEl.classList.add('active');
      if(filterValue === 'pending') { btnEl.style.background = '#2563eb'; btnEl.style.color = '#ffffff'; btnEl.style.borderColor = '#2563eb'; }
      else if(filterValue === 'paid') { btnEl.style.background = '#10b981'; btnEl.style.color = '#ffffff'; btnEl.style.borderColor = '#10b981'; }
      else { btnEl.style.background = '#64748b'; btnEl.style.color = '#ffffff'; btnEl.style.borderColor = '#64748b'; }
    }
  } else if (filterType === 'period') {
    bar.dataset.period = filterValue;
    const dfrom = document.getElementById('dfrom-' + id);
    const dto = document.getElementById('dto-' + id);
    if(dfrom) dfrom.value = '';
    if(dto) dto.value = '';
    
    if (btnEl) {
      bar.querySelectorAll('.m-per-btn').forEach(b => { b.classList.remove('active'); b.style.background = '#ffffff'; b.style.color = '#475569'; });
      btnEl.classList.add('active');
      btnEl.style.background = '#475569'; btnEl.style.color = '#ffffff';
    }
  } else if (filterType === 'search') {
    bar.dataset.search = (filterValue || '').toLowerCase().trim();
  } else if (filterType === 'custom') {
    bar.dataset.period = 'custom';
    bar.querySelectorAll('.m-per-btn').forEach(b => { b.classList.remove('active'); b.style.background = '#ffffff'; b.style.color = '#475569'; });
  }

  const today = new Date();
  const todayStr = today.toISOString().slice(0, 10);
  let dStart = null, dEnd = null;

  if (bar.dataset.period === 'today') { dStart = dEnd = todayStr; }
  else if (bar.dataset.period === 'yesterday') { 
    const y = new Date(today); y.setDate(y.getDate()-1); 
    dStart = dEnd = y.toISOString().slice(0,10); 
  }
  else if (bar.dataset.period === 'week') {
    const w = new Date(today); w.setDate(w.getDate()-7);
    dStart = w.toISOString().slice(0,10);
  }
  else if (bar.dataset.period === 'month') {
    const m = new Date(today); m.setDate(m.getDate()-30);
    dStart = m.toISOString().slice(0,10);
  }
  else if (bar.dataset.period === 'custom') {
    const dfrom = document.getElementById('dfrom-' + id);
    const dto = document.getElementById('dto-' + id);
    dStart = dfrom ? dfrom.value : null;
    dEnd = dto ? dto.value : null;
  }

  const searchStr = bar.dataset.search;
  const targetStatus = bar.dataset.status;

  table.querySelectorAll('tbody tr[data-date]').forEach(row => {
    const rowStatus = row.getAttribute('data-status') || 'all';
    const rowDate = row.getAttribute('data-date') || '';
    const rowSearch = row.getAttribute('data-search') || '';

    let matchStatus = (targetStatus === 'all') || (rowStatus === targetStatus);
    let matchSearch = (!searchStr || rowSearch.includes(searchStr));
    let matchDate = true;
    
    if (dStart || dEnd) {
      if (dStart && rowDate < dStart) matchDate = false;
      if (dEnd && rowDate > dEnd) matchDate = false;
    }

    if (matchStatus && matchSearch && matchDate) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

/* ── طي البنود ─────────────────────────────── */
function toggleBands(id) {
  const body = document.getElementById('bands-body-' + id);
  const icon = document.getElementById('bands-icon-' + id);
  const open = body.style.display === 'block';
  body.style.display = open ? 'none' : 'block';
  if (icon) icon.style.transform = open ? '' : 'rotate(180deg)';
}

/* ── فلترة الجدول الرئيسي ──────────────────── */
let activeStatus = 'unpaid';
function filterMain() {
  const q = document.getElementById('main-search') ? document.getElementById('main-search').value.toLowerCase().trim() : '';
  let visible = 0;
  
  let sumBilled = 0, sumDiscount = 0, sumCollected = 0, sumRemaining = 0, sumProfit = 0;
  
  document.querySelectorAll('#main-tbody tr').forEach(row => {
    const st = row.dataset.status;
    const matchStatus = (activeStatus === 'all') 
                     || (activeStatus === 'unpaid' && st !== 'paid') 
                     || (st === activeStatus);
    const show = (!q || row.dataset.name.includes(q)) && matchStatus;
    row.style.display = show ? '' : 'none';
    if (show) {
      visible++;
      sumBilled += parseFloat(row.dataset.billed) || 0;
      sumDiscount += parseFloat(row.dataset.discount) || 0;
      sumCollected += parseFloat(row.dataset.collected) || 0;
      sumRemaining += parseFloat(row.dataset.remaining) || 0;
      sumProfit += parseFloat(row.dataset.profit) || 0;
    }
  });
  
  const fmt = n => Number(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  const ftTitle = document.getElementById('ft-title');
  if (ftTitle) ftTitle.innerText = `الإجمالي (${visible} مشروع)`;
  
  const ftBilled = document.getElementById('ft-billed');
  if (ftBilled) ftBilled.innerText = fmt(sumBilled);
  
  const ftDiscount = document.getElementById('ft-discount');
  if (ftDiscount) ftDiscount.innerText = fmt(sumDiscount);
  
  const ftCollected = document.getElementById('ft-collected');
  if (ftCollected) ftCollected.innerText = fmt(sumCollected);
  
  const ftRemaining = document.getElementById('ft-remaining');
  if (ftRemaining) {
    ftRemaining.innerText = fmt(sumRemaining);
    ftRemaining.style.color = sumRemaining > 0 ? 'var(--bad)' : 'var(--ok)';
  }
  
  const ftPct = document.getElementById('ft-pct');
  if (ftPct) ftPct.innerText = sumBilled > 0 ? Math.round((sumCollected / sumBilled) * 100) + '%' : '0%';
  
  const ftProfit = document.getElementById('ft-profit');
  if (ftProfit) ftProfit.innerText = fmt(sumProfit);
  
  document.querySelectorAll('#manual-tbody tr').forEach(row => {
    const partyCell = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
    const st = row.dataset.status;
    const matchStatus = (activeStatus === 'all') 
                     || (activeStatus === 'unpaid' && st !== 'paid') 
                     || (st === activeStatus);
    const show = (!q || partyCell.includes(q)) && matchStatus;
    row.style.display = show ? '' : 'none';
  });
  
  const nr = document.getElementById('no-results');
  if (nr) nr.style.display = visible === 0 && document.querySelectorAll('#main-tbody tr:not([style*="display: none"])').length === 0 ? 'block' : 'none';
}
function filterStatus(status, btn) {
  activeStatus = status === 'active' ? 'unpaid' : status; // handle old 'unpaid' vs new 'active'
  document.querySelectorAll('.n-tab, .rv-pill').forEach(b => {
      b.classList.remove('active', 'active-all', 'active-active', 'active-paid');
  });
  if (btn) btn.classList.add('active-' + status);
  filterMain();
}

document.addEventListener('DOMContentLoaded', () => {
  filterMain(); // Run initial filter on load
});

/* ── الخصم ────────────────────────────────── */
function openDiscountPanel(id) {
  document.getElementById('pay-panel-' + id).style.display = 'none';
  document.getElementById('disc-panel-' + id).style.display = 'block';
}
function hideDiscountPanel(id) {
  document.getElementById('disc-panel-' + id).style.display = 'none';
}

/* ── واتساب ────────────────────────────────── */
function waRecv(phone, name, remaining) {
  const msg = encodeURIComponent(
    `مرحباً، نذكّركم بمستحقاتكم على مشروع "${name}".\n` +
    `المبلغ المتبقي: ${Number(remaining).toLocaleString('ar-EG')} ج.م\n` +
    `نرجو التواصل لترتيب السداد. شكراً لتعاملكم معنا.`
  );
  let clean = (phone || '').replace(/\D/g, '');
  if (clean.startsWith('0')) clean = '2' + clean;
  window.open(clean ? `https://wa.me/${clean}?text=${msg}` : `https://wa.me/?text=${msg}`, '_blank');
}

/* ── فاتورة رسمية للعميل (من غير أي أرقام ربح/تكلفة) ── */
function printInvoice(id, d) {
  const fmt = n => Number(n || 0).toLocaleString('ar-EG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  const today = new Date().toLocaleDateString('ar-EG');
  const invNo = 'INV-' + id + '-' + new Date().toISOString().slice(0,10).replace(/-/g,'');

  let rows = '';
  const tbl = document.querySelector('#hist-tbl-' + id + ' tbody');
  if (tbl) {
    tbl.querySelectorAll('tr[data-date]').forEach((tr, i) => {
      const tds = tr.querySelectorAll('td');
      rows += `<tr><td style="text-align:center">${i+1}</td>`
            + `<td style="text-align:center">${tds[0]?.innerText.trim() || ''}</td>`
            + `<td>${tds[1]?.innerText.trim() || ''}</td>`
            + `<td style="text-align:center;font-weight:700">${tds[2]?.innerText.trim() || ''}</td></tr>`;
    });
  }
  if (!rows) rows = `<tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:14px">لا توجد دفعات مسجّلة بعد</td></tr>`;

  const win = window.open('', '_blank', 'width=900,height=700');
  win.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head>
    <meta charset="utf-8"><title>فاتورة — ${d.project}</title>
    <style>
      *{box-sizing:border-box}
      body{font-family:'Cairo','IBM Plex Sans Arabic',Arial,sans-serif;padding:32px;color:#0f172a;font-size:13px;margin:0}
      .inv-head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #0f172a;padding-bottom:16px;margin-bottom:20px}
      .inv-company{font-size:1.4rem;font-weight:800}
      .inv-company small{display:block;font-size:.72rem;color:#64748b;font-weight:500;margin-top:2px}
      .inv-meta{text-align:left;font-size:.8rem;color:#475569;line-height:1.9}
      .inv-meta b{color:#0f172a}
      .inv-title{text-align:center;font-size:1.15rem;font-weight:800;letter-spacing:.05em;margin:6px 0 20px;color:#1e293b}
      .inv-parties{display:flex;gap:14px;margin-bottom:20px}
      .inv-card{flex:1;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;background:#f8fafc}
      .inv-card .lbl{font-size:.68rem;color:#64748b;font-weight:700;margin-bottom:4px}
      .inv-card .val{font-size:.95rem;font-weight:700}
      table{width:100%;border-collapse:collapse;margin-bottom:20px}
      th{background:#0f172a;color:#fff;border:1px solid #0f172a;padding:9px 10px;font-size:.75rem;font-weight:700}
      td{border:1px solid #e2e8f0;padding:8px 10px;font-size:.82rem}
      tbody tr:nth-child(even) td{background:#f8fafc}
      .summary{width:340px;margin-inline-start:auto;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden}
      .summary .r{display:flex;justify-content:space-between;padding:10px 16px;font-size:.88rem;border-bottom:1px solid #eef2f7}
      .summary .r.total{background:#0f172a;color:#fff;font-weight:800;font-size:1rem;border:none}
      .summary .r .g{color:#047857;font-weight:700}
      .inv-foot{margin-top:34px;display:flex;justify-content:space-between;color:#64748b;font-size:.78rem}
      .sign{text-align:center;border-top:1px solid #cbd5e1;padding-top:6px;width:200px}
      @media print{body{padding:14mm}@page{size:A4;margin:0}}
    </style></head><body>
    <div class="inv-head">
      <div class="inv-company">${d.company || 'شركة المقاولات'}<small>نظام إدارة المشاريع والمقاولات</small></div>
      <div class="inv-meta"><div>رقم الفاتورة: <b>${invNo}</b></div><div>التاريخ: <b>${today}</b></div></div>
    </div>
    <div class="inv-title">فاتورة حساب العميل</div>
    <div class="inv-parties">
      <div class="inv-card"><div class="lbl">العميل</div><div class="val">${d.client || '—'}</div>${d.phone ? `<div style="font-size:.78rem;color:#64748b;margin-top:3px;direction:ltr;text-align:right">${d.phone}</div>` : ''}</div>
      <div class="inv-card"><div class="lbl">المشروع</div><div class="val">${d.project || '—'}</div></div>
    </div>
    <table>
      <thead><tr><th style="width:40px">#</th><th style="width:110px">التاريخ</th><th>البيان</th><th style="width:120px">المبلغ المدفوع</th></tr></thead>
      <tbody>${rows}</tbody>
    </table>
    <div class="summary">
      <div class="r"><span>إجمالي قيمة الحساب</span><span>${fmt(d.billed)} ج.م</span></div>
      <div class="r"><span>إجمالي المدفوع</span><span class="g">${fmt(d.collected)} ج.م</span></div>
      <div class="r total"><span>المبلغ المتبقي</span><span>${fmt(d.remaining)} ج.م</span></div>
    </div>
    <div class="inv-foot">
      <div class="sign">توقيع العميل</div>
      <div class="sign">توقيع المسؤول</div>
    </div>
  </body></html>`);
  win.document.close();
  setTimeout(() => { win.focus(); win.print(); }, 400);
}

function switchTab(tabId) {
  // Hide all tab content
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  
  // Remove active class from all tab buttons
  document.querySelectorAll('.n-main-tab').forEach(el => {
    el.classList.remove('active');
  });
  
  // Show target tab
  if (tabId) {
      document.getElementById(tabId).style.display = 'block';
  } else {
      document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'block');
  }
  
  // Add active styling to clicked button
  let btnId = 'tab-btn-project';
  if (tabId === 'manual-recv-tab') btnId = 'tab-btn-manual-recv';
  
  const btn = document.getElementById(btnId);
  if (btn) {
      btn.classList.add('active');
  }
  
  // Reset any global JS filters
  if (tabId) {
      filterStatus('active', null); // default to active debts
      const defaultBtn = document.querySelector('.n-tab[onclick*="active"]');
      if(defaultBtn) {
          document.querySelectorAll('.n-tab').forEach(b => b.classList.remove('active-all', 'active-active', 'active-paid'));
          defaultBtn.classList.add('active-active');
      }
  }
}

function showPaidGlobal(btn) {
    switchTab(null);
    filterStatus('paid');
}

document.addEventListener('DOMContentLoaded', function() {
    filterMain(); // Run filter on load to hide paid items initially
});
</script>
@endpush
@endsection
