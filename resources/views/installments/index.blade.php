@extends('layouts.app')
@section('title', 'العقود والأقساط')
@section('page-title', 'منظومة العقود والأقساط')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  /* ── منقول بروح صفحة الأقساط في السيستم الأول (مكوّنات مستقلة، prefixed) ── */
  :root{
    --i-accent:#4f46e5; --i-accent2:#393984; --i-accent-bg:#eef2ff;
    --i-success:#059669; --i-success-bg:#ecfdf5; --i-danger:#dc2626; --i-danger-bg:#fef2f2;
    --i-warning:#d97706; --i-warning-bg:#fffbeb; --i-violet:#7c3aed; --i-violet-bg:#f5f3ff;
    --i-surface:#fff; --i-surface2:#fafbfd; --i-text:#0f172a; --i-muted:#5a6478; --i-soft:#8b95a9;
    --i-border:#e6ebf3; --i-border2:#d4dbe6; --i-hover:#f1f4f9;
  }
  .inst-wrap{font-feature-settings:'tnum' 1;}
  .inst-wrap .stat-card{background:var(--i-surface);border:1px solid var(--i-border);border-radius:12px;padding:16px 18px;position:relative;overflow:hidden;transition:.2s;}
  .inst-wrap .stat-card::before{content:'';position:absolute;top:0;right:0;bottom:0;width:3px;background:var(--i-soft);}
  .inst-wrap .stat-card.blue::before{background:var(--i-accent);} .inst-wrap .stat-card.green::before{background:var(--i-success);}
  .inst-wrap .stat-card.orange::before{background:var(--i-warning);} .inst-wrap .stat-card.red::before{background:var(--i-danger);}
  .inst-wrap .stat-card.purple::before{background:var(--i-violet);}
  .inst-wrap .stat-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(15,23,42,.06);}
  .inst-wrap .sc-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem;margin-bottom:9px;}
  .inst-wrap .stat-card.blue .sc-icon{background:var(--i-accent-bg);color:var(--i-accent);} .inst-wrap .stat-card.green .sc-icon{background:var(--i-success-bg);color:var(--i-success);}
  .inst-wrap .stat-card.orange .sc-icon{background:var(--i-warning-bg);color:var(--i-warning);} .inst-wrap .stat-card.red .sc-icon{background:var(--i-danger-bg);color:var(--i-danger);}
  .inst-wrap .stat-card.purple .sc-icon{background:var(--i-violet-bg);color:var(--i-violet);}
  .inst-wrap .stat-card p{font-size:.78rem;font-weight:500;color:var(--i-muted);margin:0 0 4px;}
  .inst-wrap .stat-card h3{font-weight:700;margin:0;font-size:1.5rem;color:var(--i-text);letter-spacing:-.02em;}
  .inst-wrap .stat-card h3 small{font-size:.76rem;font-weight:400;color:var(--i-soft);}

  .inst-wrap .table-box{background:var(--i-surface);border:1px solid var(--i-border);border-radius:12px;padding:18px;}
  .inst-wrap .nav-pills{background:var(--i-surface2);border:1px solid var(--i-border);border-radius:12px;padding:4px;display:inline-flex;}
  .inst-wrap .nav-pills .nav-link{font-weight:600;border-radius:8px;padding:8px 16px;color:var(--i-muted);font-size:.86rem;background:transparent;border:none;}
  .inst-wrap .nav-pills .nav-link.active{background:var(--i-text);color:#fff;}

  .inst-wrap .filters-card{background:var(--i-surface);border:1px solid var(--i-border);border-radius:12px;padding:14px 16px;display:flex;flex-direction:column;gap:12px;}
  .inst-wrap .filters-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
  .inst-wrap .filter-search{position:relative;flex:1 1 260px;min-width:200px;}
  .inst-wrap .filter-search i{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--i-soft);font-size:.85rem;}
  .inst-wrap .filter-search input{width:100%;padding:10px 40px 10px 14px;border:1px solid var(--i-border);border-radius:10px;background:var(--i-hover);font-weight:600;font-size:.88rem;color:var(--i-text);outline:none;}
  .inst-wrap .status-pills{display:inline-flex;background:var(--i-hover);border:1px solid var(--i-border);border-radius:11px;padding:3px;gap:2px;}
  .inst-wrap .status-pill{border:none;background:transparent;color:var(--i-muted);font-weight:700;font-size:.82rem;padding:7px 14px;border-radius:8px;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;gap:5px;}
  .inst-wrap .status-pill.active{background:var(--i-surface);box-shadow:0 1px 4px rgba(0,0,0,.1);}
  .inst-wrap .status-pill.active.p-full{color:#15803d;background:#f0fdf4;} .inst-wrap .status-pill.active.p-part{color:#1d4ed8;background:#eff6ff;} .inst-wrap .status-pill.active.p-none{color:#dc2626;background:#fef2f2;}
  .inst-wrap .filter-group{display:inline-flex;align-items:center;gap:8px;flex-wrap:wrap;}
  .inst-wrap .filter-group>label{font-size:.8rem;font-weight:700;color:var(--i-muted);margin:0;white-space:nowrap;}
  .inst-wrap .filter-select{padding:8px 12px;border:1px solid var(--i-border);border-radius:9px;background:var(--i-surface);font-weight:600;font-size:.82rem;color:var(--i-text);outline:none;cursor:pointer;}
  .inst-wrap .filter-chip{padding:8px 14px;border:1px solid var(--i-accent);border-radius:9px;background:transparent;color:var(--i-accent);font-weight:700;font-size:.82rem;cursor:pointer;}
  .inst-wrap .filter-chip:hover{background:var(--i-accent);color:#fff;}
  .inst-wrap .filter-actions{display:inline-flex;gap:8px;margin-right:auto;}
  .inst-wrap .btn-filter-print{border:1px solid #16a34a;background:#16a34a;color:#fff;padding:8px 16px;border-radius:9px;font-weight:700;font-size:.82rem;cursor:pointer;}
  .inst-wrap .btn-filter-reset{border:1px solid var(--i-border);background:var(--i-surface);color:var(--i-muted);padding:8px 16px;border-radius:9px;font-weight:700;font-size:.82rem;cursor:pointer;}

  .inst-wrap .kpi-strip{display:grid;grid-template-columns:repeat(7,1fr);gap:10px;}
  .inst-wrap .kpi-item{background:var(--i-surface);border:1px solid var(--i-border);border-radius:10px;padding:10px 8px;text-align:center;}
  .inst-wrap .kpi-label{font-size:.72rem;font-weight:700;color:var(--i-soft);margin-bottom:3px;}
  .inst-wrap .kpi-val{font-size:1.2rem;font-weight:900;line-height:1.1;}
  @media(max-width:992px){.inst-wrap .kpi-strip{grid-template-columns:repeat(4,1fr);}}
  @media(max-width:576px){.inst-wrap .kpi-strip{grid-template-columns:repeat(2,1fr);}}

  .inst-wrap .custom-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--i-surface);border-radius:12px;overflow:hidden;border:1px solid var(--i-border);}
  .inst-wrap .custom-table thead{background:var(--i-surface2);}
  .inst-wrap .custom-table th{padding:12px 14px;font-size:.74rem;font-weight:600;color:var(--i-muted);border-bottom:1px solid var(--i-border);white-space:nowrap;text-align:center;}
  .inst-wrap .custom-table td{padding:13px 14px;font-size:.88rem;font-weight:500;border-bottom:1px solid var(--i-border);vertical-align:middle;text-align:center;color:var(--i-text);}
  .inst-wrap .custom-table tbody tr:hover{background:var(--i-hover);}
  .inst-wrap .clickable-row{cursor:pointer;}
  .inst-wrap .client-avatar{width:36px;height:36px;border-radius:9px;background:var(--i-accent-bg);color:var(--i-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.95rem;flex-shrink:0;}
  @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.7)}}
  .inst-wrap .whatsapp-link{color:#25d366;font-size:1.1rem;}
  .inst-wrap .text-start{text-align:right !important;}

  /* ── كشف الحساب: paper-xls + tabs (بروح السيستم الأول) ── */
  .paper-xls{width:100%;border-collapse:collapse;background:#fff;}
  .paper-xls td{border:1px solid #e6ebf3;padding:10px 14px;font-size:.86rem;font-weight:500;color:#0f172a;}
  .pxls-label{background:#fafbfd;color:#5a6478!important;text-align:right;width:52%;font-weight:600!important;}
  .pxls-value{background:#fff;text-align:center;font-weight:600;font-size:.92rem;color:#0f172a!important;}
  .paper-xls tr:not([class]) td{background:rgba(59,130,246,.10)!important;}
  .pxls-title-row .pxls-label{background:#4f46e5;color:#fff!important;font-size:.92rem;border-color:#4f46e5;}
  .pxls-title-row .pxls-value.name-val{background:#eef2ff;color:#4f46e5!important;font-size:1rem;font-weight:700;}
  .pxls-pay-row td{background:#fff;}
  .pxls-pay-row:nth-child(even) td{background:#fafbfd;}
  .pxls-pay-date{text-align:center;font-size:.82rem;color:#5a6478!important;font-weight:500!important;width:52%;}
  .pxls-pay-num{text-align:center;position:relative;width:48%;}
  .pxls-pay-amount{font-size:.92rem;font-weight:700;color:#059669!important;}
  .pxls-row-badge{position:absolute;left:8px;top:50%;transform:translateY(-50%);background:#4f46e5;color:#fff!important;border-radius:50%;width:22px;height:22px;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;}
  .pxls-empty-row td{background:#fafbfd!important;color:#8b95a9!important;}
  .pxls-summary-row td{border-top:2px solid #d4dbe6;}
  .pxls-sum-label{background:#0f172a;color:#fff!important;text-align:right;padding:12px 14px;font-weight:600;font-size:.88rem;}
  .pxls-sum-value{text-align:center;font-size:1rem;font-weight:700;padding:12px 14px;}
  .remaining-label,.remaining-val{background:#fef2f2!important;color:#dc2626!important;}
  .paid-summary .pxls-sum-label{background:#4f46e5!important;color:#fff!important;} .paid-val{background:#eef2ff!important;color:#4f46e5!important;}
  .cst-tabs-strip{display:flex;flex-wrap:wrap;gap:5px;padding:10px 12px;background:#fafbfd;border-bottom:1px solid #e6ebf3;}
  .cst-tab{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;cursor:pointer;border:1.5px solid #d4dbe6;background:#fff;color:#5a6478;white-space:nowrap;user-select:none;}
  .cst-tab:hover{border-color:#4f46e5;color:#4f46e5;background:#eef2ff;}
  .cst-tab.active-tab{background:#4f46e5;color:#fff;border-color:#4f46e5;}
  .cst-tab-summary{border-color:#4f46e5;color:#4f46e5;}
  .cst-num{background:rgba(0,0,0,.12);border-radius:10px;padding:0 5px;font-size:10px;font-weight:800;}
  .cst-tab.active-tab .cst-num{background:rgba(255,255,255,.25);}

  @media print{
    @page{size:A4;margin:8mm;}
    body *{visibility:hidden;}
    #printArea, #printArea *{visibility:visible;}
    #printArea{position:absolute;inset:0;width:100%;}
    .no-print{display:none !important;}
  }

  /* ── حماية السايدبار من Bootstrap (اللي بيعرّف .nav كـ flex-wrap) ── */
  .sidebar .nav { display:block !important; flex:1 1 auto; overflow-y:auto; padding:14px 12px; margin:0; list-style:none; }
  .sidebar .nav-item { display:flex !important; width:auto; float:none; }
  .sidebar .nav-label { display:block; }
</style>
@endpush

@section('content')
<div class="inst-wrap">

  {{-- ═══ Header actions ═══ --}}
  <div class="page-head" style="align-items:flex-start">
    <div>
      <h3>منظومة العقود والأقساط</h3>
      <p style="color:var(--muted);font-size:.86rem;margin:4px 0 0">إدارة عقود التقسيط — تحصيل الدفعات — كشوف الحسابات</p>
    </div>
    <div class="d-flex gap-2 align-items-center no-print">
      <button class="btn ghost" onclick="window.print()"><i class="fa fa-print"></i> طباعة</button>
      <button class="btn" data-bs-toggle="modal" data-bs-target="#newContractModal"><i class="fa fa-plus"></i> عقد جديد</button>
    </div>
  </div>

  {{-- ═══ Stat cards ═══ --}}
  <div class="row g-3 mb-4">
    <div class="col-md col-6"><div class="stat-card blue"><div class="sc-icon"><i class="fa fa-money-bill-wave"></i></div><p>إجمالي المتبقي بالخارج</p><h3>{{ \App\Support\Money::format($totalOut) }} <small>ج</small></h3></div></div>
    <div class="col-md col-6"><div class="stat-card green"><div class="sc-icon"><i class="fa fa-circle-check"></i></div><p>إجمالي المحصّل</p><h3>{{ \App\Support\Money::format($totalCollected) }} <small>ج</small></h3></div></div>
    <div class="col-md col-6"><div class="stat-card orange"><div class="sc-icon"><i class="fa fa-file-contract"></i></div><p>العقود النشطة</p><h3>{{ $active->count() }} <small>عقد</small></h3></div></div>
    <div class="col-md col-6"><div class="stat-card red"><div class="sc-icon"><i class="fa fa-flag-checkered"></i></div><p>العقود المنتهية</p><h3>{{ $completed->count() }} <small>عقد</small></h3></div></div>
    <div class="col-md col-6"><div class="stat-card purple"><div class="sc-icon"><i class="fa fa-calendar-day"></i></div><p>أقساط اليوم ({{ date('d') }})</p><h3>{{ $todayContracts->count() }} <small>قسط</small></h3></div></div>
  </div>

  <div class="table-box">
    <ul class="nav nav-pills mb-3" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#activeTab"><i class="fa fa-list-check me-1"></i> نشطة ({{ $active->count() }})</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#completedTab"><i class="fa fa-check-double me-1"></i> منتهية ({{ $completed->count() }})</button></li>
    </ul>

    <div class="tab-content">
      {{-- ═══════ نشطة ═══════ --}}
      <div class="tab-pane fade show active" id="activeTab">
        <div class="filters-card mb-3 no-print">
          <div class="filters-row">
            <div class="filter-search">
              <i class="fa fa-search"></i>
              <input type="text" id="activeSearch" placeholder="ابحث باسم العميل أو رقم الهاتف..." oninput="applyActiveFilters()" autocomplete="off">
            </div>
            <div class="status-pills" id="statusPills">
              <button type="button" class="status-pill active" data-status="all" onclick="setStatusFilter('all', this)"><i class="fa fa-layer-group"></i> الكل</button>
              <button type="button" class="status-pill p-full" data-status="full" onclick="setStatusFilter('full', this)"><i class="fa fa-check-circle"></i> دفع كامل</button>
              <button type="button" class="status-pill p-part" data-status="partial" onclick="setStatusFilter('partial', this)"><i class="fa fa-adjust"></i> جزئي</button>
              <button type="button" class="status-pill p-none" data-status="unpaid" onclick="setStatusFilter('unpaid', this)"><i class="fa fa-circle-xmark"></i> لم يسدد</button>
            </div>
          </div>
          <div class="filters-row">
            <div class="filter-group">
              <label><i class="fa fa-calendar-day"></i> يوم الاستحقاق</label>
              <select id="dueRangeFrom" class="filter-select" onchange="applyActiveFilters()">
                <option value="0">من: الكل</option>
                @for($dy=1;$dy<=31;$dy++)<option value="{{ $dy }}">من يوم {{ $dy }}</option>@endfor
              </select>
              <select id="dueRangeTo" class="filter-select" onchange="applyActiveFilters()">
                <option value="0">إلى: الكل</option>
                @for($dy=1;$dy<=31;$dy++)<option value="{{ $dy }}">إلى يوم {{ $dy }}</option>@endfor
              </select>
              <button type="button" class="filter-chip" onclick="setTodayDueFilter()"><i class="fa fa-bolt"></i> اليوم</button>
            </div>
            <div class="filter-actions">
              <button type="button" class="btn-filter-print" onclick="window.print()"><i class="fa fa-print"></i> طباعة</button>
              <button type="button" class="btn-filter-reset" onclick="resetActiveFilters()"><i class="fa fa-rotate-left"></i> مسح</button>
            </div>
          </div>
        </div>

        <div id="dueStatsBar" class="kpi-strip mb-3">
          <div class="kpi-item"><div class="kpi-label">العملاء</div><div class="kpi-val" style="color:#0369a1" id="statTotal">0</div></div>
          <div class="kpi-item"><div class="kpi-label">إجمالي القسط الشهري</div><div class="kpi-val" style="color:#b45309" id="statDue">0 ج</div></div>
          <div class="kpi-item"><div class="kpi-label">دفع كامل</div><div class="kpi-val" style="color:#15803d" id="statFullPaid">0</div></div>
          <div class="kpi-item"><div class="kpi-label">جزئي</div><div class="kpi-val" style="color:#1d4ed8" id="statPartialPaid">0</div></div>
          <div class="kpi-item"><div class="kpi-label">لم يسدد</div><div class="kpi-val" style="color:#dc2626" id="statUnpaid">0</div></div>
          <div class="kpi-item"><div class="kpi-label">محصّل الشهر</div><div class="kpi-val" style="color:#16a34a" id="statCollected">0 ج</div></div>
          <div class="kpi-item"><div class="kpi-label">متبقي الشهر</div><div class="kpi-val" style="color:#dc2626" id="statRemaining">0 ج</div></div>
        </div>

        <div id="printArea">
        <div class="table-responsive">
          <table class="custom-table" id="dueByDayTable">
            <thead>
              <tr>
                <th class="text-start">العميل</th><th>عدد العقود</th><th>إجمالي القسط الشهري</th>
                <th>إجمالي المتبقي</th><th>نسبة الإنجاز</th><th>حالة السداد</th><th class="no-print">إجراء</th>
              </tr>
            </thead>
            <tbody id="dueByDayBody"></tbody>
          </table>
        </div>
        </div>
        <div id="duePager" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 no-print" style="display:none">
          <small class="text-muted fw-bold" id="duePagerInfo"></small>
          <div class="d-flex gap-1" id="duePagerBtns"></div>
        </div>

        <script id="allInstallmentsData" type="application/json">
        {!! json_encode($active->map(function($c){
            $ym = now()->format('Y-m');
            $paidThisMonth = $c->payments->filter(fn($p)=> $p->payment_date && $p->payment_date->format('Y-m') === $ym)->sum('amount_paid');
            return [
                'id'=>$c->id,
                'customer_name'=>$c->customer_name,
                'customer_phone'=>$c->customer_phone ?? '',
                'product_name'=>$c->product_name,
                'due_day'=>(int)$c->due_day,
                'monthly_installment'=>(float)$c->monthly_installment,
                'remaining_balance'=>(float)$c->remaining_balance,
                'paid_this_month'=> $paidThisMonth >= ((float)$c->monthly_installment * 0.99) && (float)$c->monthly_installment > 0,
                'paid_this_month_amount'=>(float)$paidThisMonth,
            ];
        })->values(), JSON_UNESCAPED_UNICODE) !!}
        </script>
        <script id="customerProgressData" type="application/json">
        {!! json_encode($contracts->groupBy(function($c){
            $phone = trim((string)($c->customer_phone ?? ''));
            return ($phone !== '' && $phone !== '—') ? $phone : ('n:'.($c->customer_name ?? ''));
        })->map(fn($g)=>[
            'total_value'=>(float)$g->sum('total_after_interest'),
            'total_remaining'=>(float)$g->sum(fn($c)=>max(0,(float)$c->remaining_balance)),
        ]), JSON_UNESCAPED_UNICODE) !!}
        </script>
      </div>

      {{-- ═══════ منتهية ═══════ --}}
      <div class="tab-pane fade" id="completedTab">
        <div class="table-responsive">
          <table class="custom-table" style="opacity:.9">
            <thead><tr>
              <th class="text-start">العميل</th><th>المشروع</th><th>إجمالي العقد</th><th>المقدم</th><th>تاريخ الإنشاء</th><th>الحالة</th><th class="no-print">كشف</th>
            </tr></thead>
            <tbody>
              @forelse($completed as $c)
              <tr class="clickable-row" onclick="openStatement(@js($c->customer_phone), @js($c->customer_name))">
                <td class="text-start"><strong class="d-block">{{ $c->customer_name }}</strong><small class="text-muted" dir="ltr">{{ $c->customer_phone ?: '—' }}</small></td>
                <td>{{ Str::limit($c->product_name, 24) }}</td>
                <td class="fw-bold">{{ \App\Support\Money::format($c->total_after_interest) }} ج</td>
                <td class="text-success fw-bold">{{ \App\Support\Money::format($c->down_payment) }} ج</td>
                <td class="text-muted">{{ $c->created_at->format('Y/m/d') }}</td>
                <td><span class="badge bg-success">مسدد بالكامل ✓</span></td>
                <td class="no-print"><button class="btn btn-sm btn-outline-success fw-bold" onclick="event.stopPropagation(); openStatement(@js($c->customer_phone), @js($c->customer_name))"><i class="fa fa-table me-1"></i> كشف</button></td>
              </tr>
              @empty
              <tr><td colspan="7" class="text-center py-5 text-muted fw-bold">لا توجد عقود منتهية.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- host لكشف الحساب (يُحمّل AJAX) --}}
  <div id="statementModalHost"></div>

  {{-- ═══ مودال: عقد جديد (مربوط بمشروع) ═══ --}}
  <div class="modal fade" id="newContractModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <form method="POST" action="{{ route('installments.store') }}" class="modal-content border-0 shadow-lg" onsubmit="return validateContract(event, this)">
        @csrf
        <div class="modal-header text-white" style="background:linear-gradient(135deg,#0f172a,#4f46e5)">
          <h5 class="modal-title fw-bold"><i class="fa fa-file-signature me-2"></i>إنشاء عقد تقسيط جديد</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4" style="background:#fafbfd">
          <div class="mb-3">
            <label class="fw-bold mb-1 d-block">المشروع / العميل <span class="text-danger">*</span></label>
            <select name="project_id" id="nc_project" class="form-select" required onchange="ncProjectChanged(this)">
              <option value="">— اختر المشروع —</option>
              @foreach($projectsForContract as $p)
                <option value="{{ $p->id }}" data-billed="{{ $p->billed }}" data-paid="{{ $p->already_paid }}" data-paid-total="{{ $p->already_paid_total }}" data-has-contract="{{ $p->has_contract ? '1' : '0' }}" data-bands='@json($p->bands)'>
                  {{ $p->name }} — {{ $p->client_name }} ({{ \App\Support\Money::format($p->billed) }} ج)
                </option>
              @endforeach
            </select>
            @if($projectsForContract->isEmpty())
              <small class="text-muted d-block mt-1">لا توجد مشاريع بعد. اعمل مشروع أولاً.</small>
            @endif
          </div>

          {{-- تقسيط بند محدد فقط (اختياري) --}}
          <div class="mb-3">
            <label class="fw-bold mb-1 d-block">تقسيط بند محدد؟ <span class="text-muted small">(اختياري)</span></label>
            <select name="band_id" id="nc_band" class="form-select" onchange="ncBandChanged(this)">
              <option value="">المشروع كامل</option>
            </select>
            <small class="text-muted d-block mt-1">سيب «المشروع كامل» لتقسيط الفاتورة كلها، أو اختر بند معيّن لتقسيطه لوحده — قيمة العقد بتتظبط تلقائيًا.</small>
          </div>

          <div class="row g-3">
            <div class="col-md-4"><label class="fw-bold mb-1 small">قيمة العقد (كاش) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" name="cash_price" id="nc_cash" class="form-control fw-bold" required oninput="ncCalc()"></div>
            <div class="col-md-4"><label class="fw-bold mb-1 small" style="color:#0ea5e9">خصم</label>
              <input type="number" step="0.01" min="0" name="discount" id="nc_disc" class="form-control" value="0" oninput="ncCalc()"></div>
            <div class="col-md-4"><label class="fw-bold mb-1 small text-success">المقدم المدفوع الآن</label>
              <input type="number" step="0.01" min="0" name="down_payment" id="nc_down" class="form-control text-success fw-bold" value="0" oninput="ncCalc()">
              <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" id="nc_use_paid" onchange="ncUsePaidToggled(this)">
                <label class="form-check-label small text-muted" for="nc_use_paid">اعتبر كل المبلغ اللي اتحصّل فعليًا من العميل في المشروع مقدم (مش بس تحت البند ده)</label>
              </div>
              <small id="nc_down_warning" class="text-danger d-none d-block mt-1"><i class="fa fa-triangle-exclamation me-1"></i>المقدم أكبر من قيمة العقد بعد الخصم — قلّل المقدم أو اختر بند/مشروع بقيمة أعلى.</small></div>
            <div class="col-md-4"><label class="fw-bold mb-1 small text-secondary">نسبة الفائدة %</label>
              <input type="number" step="0.1" min="0" name="interest_rate" id="nc_rate" class="form-control" value="0" oninput="ncCalc()"></div>
            <div class="col-md-4"><label class="fw-bold mb-1 small text-warning">عدد الشهور <span class="text-danger">*</span></label>
              <input type="number" step="1" min="1" name="installment_months" id="nc_months" class="form-control text-warning fw-bold" required oninput="ncCalc()"></div>
            <div class="col-md-4"><label class="fw-bold mb-1 small text-primary">يوم السداد الشهري <span class="text-danger">*</span></label>
              <select name="due_day" class="form-select text-primary fw-bold" required>
                @for($dy=1;$dy<=31;$dy++)<option value="{{ $dy }}" {{ (int)date('d')==$dy?'selected':'' }}>يوم {{ $dy }}</option>@endfor
              </select></div>
            <div class="col-md-6"><label class="fw-bold mb-1 small">تاريخ العقد <span class="text-danger">*</span></label>
              <input type="date" name="start_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required></div>
            <div class="col-md-6"><label class="fw-bold mb-1 small">ملاحظات</label>
              <input type="text" name="notes" class="form-control" placeholder="اختياري"></div>
            <div class="col-md-6"><label class="fw-bold mb-1 small"><i class="fa fa-wallet text-primary me-1"></i>المحفظة الافتراضية لتحصيل الأقساط القادمة</label>
              <select name="account_id" class="form-select">
                <option value="">— المحفظة الافتراضية (المقاولات) —</option>
                @foreach($wallets->groupBy(fn($w) => $w->categoryAr()) as $cat => $grp)
                  <optgroup label="{{ $cat }}">
                    @foreach($grp as $w)
                      <option value="{{ $w->id }}">{{ $w->account_name }}@if($w->id == \App\Models\Account::WALLET_ID) ★@endif — {{ \App\Support\Money::format($w->balance) }} ج</option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
              <small class="text-muted d-block mt-1"><i class="fa fa-circle-info me-1"></i>المقدم نفسه مش هيتحرك في أي محفظة (اتحصّل فعليًا قبل كده) — ده بس اختيار المحفظة اللي هتتقترح افتراضيًا لما تحصّل قسط شهري لاحقًا.</small></div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-6"><div class="p-3 rounded-3" style="background:#fffbeb;border:1px solid #fcd34d">
              <div class="small fw-bold text-warning">إجمالي المديونية (المتبقي)</div>
              <div class="fw-black fs-4 text-dark" id="nc_total">0</div><small class="text-muted">ج.م</small></div></div>
            <div class="col-md-6"><div class="p-3 rounded-3" style="background:#fef2f2;border:1px solid #fca5a5">
              <div class="small fw-bold text-danger">القسط الشهري الثابت</div>
              <div class="fw-black fs-4 text-danger" id="nc_monthly">0</div><small class="text-muted">ج.م / شهر</small></div></div>
          </div>
        </div>
        <div class="modal-footer bg-white">
          <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" id="nc_submit_btn" class="btn btn-primary fw-bold px-4 flex-grow-1"><i class="fa fa-check-circle me-2"></i>اعتماد وحفظ العقد</button>
        </div>
      </form>
    </div>
  </div>

</div>{{-- /inst-wrap --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ═══ عقد جديد: حساب حي + بنود المشروع ═══
// اتنين قيمة بتتحدّث كل مرة يتغيّر فيها المشروع/البند:
//  - ncAlreadyPaidScoped: اللي اتدفع تحت البند المختار بس (أو دفعات عامة لو
//    "المشروع كامل") — ده الافتراضي اللي بيتكتب في المقدم.
//  - ncAlreadyPaidTotal: كل فلوس المشروع مهما كان البند — بيتكتب بدل الأول
//    لو المستخدم فعّل checkbox «اعتبر كل المبلغ... في المشروع».
let ncAlreadyPaidScoped = 0;
let ncAlreadyPaidTotal = 0;

function ncApplyPaidToDown(){
  const cb = document.getElementById('nc_use_paid');
  const downInput = document.getElementById('nc_down');
  const val = cb.checked ? ncAlreadyPaidTotal : ncAlreadyPaidScoped;
  downInput.value = val>0 ? val.toFixed(2) : 0;
  // downInput.readOnly = true;
  ncCalc();
}
function ncUsePaidToggled(cb){
  ncApplyPaidToDown();
}
function ncProjectChanged(sel){
  const opt = sel.options[sel.selectedIndex];
  const billed = parseFloat(opt?.dataset.billed)||0;
  ncAlreadyPaidScoped = parseFloat(opt?.dataset.paid)||0;
  ncAlreadyPaidTotal = parseFloat(opt?.dataset.paidTotal)||0;
  // املأ قائمة البنود من المشروع المختار
  const hasContract = opt?.dataset.hasContract === '1';
  const bandSel = document.getElementById('nc_band');
  
  bandSel.innerHTML = '';
  const wholeProjectOpt = document.createElement('option');
  wholeProjectOpt.value = '';
  if (hasContract) {
      wholeProjectOpt.textContent = 'المشروع كامل (مُقسّط مسبقاً)';
      wholeProjectOpt.disabled = true;
  } else {
      wholeProjectOpt.textContent = 'المشروع كامل ('+(billed?billed.toLocaleString('en-US'):'0')+' ج)';
  }
  bandSel.appendChild(wholeProjectOpt);

  let bands = [];
  try { bands = JSON.parse(opt?.dataset.bands||'[]'); } catch(e){ bands = []; }
  bands.forEach(b=>{
    const o = document.createElement('option');
    o.value = b.id; o.dataset.billed = b.billed; o.dataset.paid = b.already_paid; o.dataset.paidTotal = b.already_paid_total;
    if (b.has_contract) {
        o.textContent = b.name + ' (مُقسّط مسبقاً)';
        o.disabled = true;
    } else {
        o.textContent = b.name + ' (' + (Number(b.billed)||0).toLocaleString('en-US') + ' ج)';
    }
    bandSel.appendChild(o);
  });

  if (bandSel.options[bandSel.selectedIndex] && bandSel.options[bandSel.selectedIndex].disabled) {
      const valid = Array.from(bandSel.options).find(x => !x.disabled);
      if (valid) valid.selected = true;
  }
  document.getElementById('nc_cash').value = billed>0 ? billed.toFixed(2) : '';
  // أي فلوس اتحصّلت من العميل قبل كده تتملى تلقائي كمقدم — على نطاق البند
  // المختار بالافتراض، أو كل فلوس المشروع لو الـ checkbox مفعّل
  ncApplyPaidToDown();
}
function ncBandChanged(sel){
  const opt = sel.options[sel.selectedIndex];
  let billed;
  if(sel.value){
    billed = parseFloat(opt?.dataset.billed)||0;
    ncAlreadyPaidScoped = parseFloat(opt?.dataset.paid)||0;
    ncAlreadyPaidTotal = parseFloat(opt?.dataset.paidTotal)||0;
  } else {
    const p = document.getElementById('nc_project').selectedOptions[0];
    billed = parseFloat(p?.dataset.billed)||0;
    ncAlreadyPaidScoped = parseFloat(p?.dataset.paid)||0;
    ncAlreadyPaidTotal = parseFloat(p?.dataset.paidTotal)||0;
  }
  document.getElementById('nc_cash').value = billed>0 ? billed.toFixed(2) : '';
  // نفس فكرة ncProjectChanged: تحديد بند بيبدّل النطاق الافتراضي لفلوس البند
  // ده بس، إلا لو الـ checkbox مفعّل فبيفضل ياخد كل فلوس المشروع
  ncApplyPaidToDown();
}
function ncCalc(){
  const cash=parseFloat(document.getElementById('nc_cash').value)||0;
  const disc=parseFloat(document.getElementById('nc_disc').value)||0;
  const down=parseFloat(document.getElementById('nc_down').value)||0;
  const rate=parseFloat(document.getElementById('nc_rate').value)||0;
  const mos =parseInt(document.getElementById('nc_months').value)||0;
  const afterDisc=Math.max(0,cash-disc);
  const baseForInt=Math.max(0,afterDisc-down);
  const interest=baseForInt*(rate/100);
  const total=afterDisc+interest;
  const rem=Math.max(0,total-down);
  const monthly=mos>0?rem/mos:0;
  const fmt=v=> v%1===0? v.toLocaleString('en-US'): v.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
  document.getElementById('nc_total').innerText=fmt(rem);
  document.getElementById('nc_monthly').innerText=fmt(monthly);

  // المقدم لازم يكون ≤ قيمة العقد بعد الخصم — لو أكبر امنع الحفظ فورًا بدل
  // ما المستخدم يكتشف الرفض بعد ما يبعت الفورم (نفس القاعدة اللي السيرفر
  // بيتحقق منها في InstallmentController::store/update)
  const warnEl = document.getElementById('nc_down_warning');
  const submitBtn = document.getElementById('nc_submit_btn');
  const invalid = down > afterDisc + 0.01;
  warnEl.classList.toggle('d-none', !invalid);
  if(submitBtn) submitBtn.disabled = invalid;
}
function validateContract(e,form){
  const pid=form.project_id.value;
  const mos=parseInt(form.installment_months.value)||0;
  if(!pid){e.preventDefault();Swal.fire('بيانات ناقصة','اختر المشروع','warning');return false;}
  if(mos<=0){e.preventDefault();Swal.fire('بيانات ناقصة','اكتب عدد الشهور','warning');return false;}

  e.preventDefault();
  
  const isWholeProject = !form.band_id.value;
  const title = isWholeProject ? 'تقسيط المشروع بالكامل؟' : 'تقسيط بند محدد؟';
  const message = isWholeProject 
    ? '<span style="color:#dc2626;font-weight:bold;">تحذير هام: هذا قرار نهائي!</span><br><br>لو أكدت تقسيط المشروع بالكامل، مش هتقدر تشتري أو تضيف خامات جديدة، ولا هتقدر تعمل أي مرتجعات لهذا المشروع، ولن تتمكن من إلغاء عقد التقسيط بعد إنشائه.<br><br>هل أنت متأكد؟'
    : '<span style="color:#dc2626;font-weight:bold;">تحذير هام: هذا قرار نهائي!</span><br><br>لو أكدت تقسيط هذا البند، مش هتقدر تشتري أو تضيف خامات جديدة للبند ده، ولا هتقدر تعمل أي مرتجعات تخصه، ولن تتمكن من إلغاء عقد التقسيط بعد إنشائه.<br><br>هل أنت متأكد؟';

  Swal.fire({
    icon: 'warning',
    title: title,
    html: message,
    showCancelButton: true,
    confirmButtonText: 'أيوه، متأكد (قرار نهائي)',
    cancelButtonText: 'تراجع',
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6c757d',
    reverseButtons: true,
  }).then(result => {
    if(result.isConfirmed) form.submit();
  });
  
  return false;
}

// ═══ كشف الحساب (AJAX) ═══
function _injectAndShow(html,hostId){
  const host=document.getElementById(hostId);
  host.innerHTML=html;
  const modalEl=host.querySelector('.modal');
  if(!modalEl)return null;
  const m=bootstrap.Modal.getOrCreateInstance(modalEl);
  modalEl.addEventListener('hidden.bs.modal',()=>{host.innerHTML='';},{once:true});
  m.show();
  return modalEl;
}
async function openStatement(phone,name,prefill){
  if (window.showConstructionLoader) window.showConstructionLoader();
  try{
    const qs=new URLSearchParams({phone:phone||'',name:name||''});
    const res=await fetch(`{{ route('installments.customer_statement') }}?${qs.toString()}`,{headers:{'X-Requested-With':'XMLHttpRequest'}});
    if(!res.ok)throw new Error('فشل');
    _injectAndShow(await res.text(),'statementModalHost');
    if(prefill && prefill.contractId) applyStatementPrefill(prefill);
  }catch(e){Swal.fire('خطأ','تعذّر فتح كشف الحساب','error');}
  finally{ if (window.hideConstructionLoader) window.hideConstructionLoader(); }
}

// بعد فشل حفظ سداد/تعديل جوه كشف الحساب (AJAX modal)، الصفحة بترجع تحمّل تاني
// وكشف الحساب بيتفتح من جديد فاضي — الدالة دي بتفتح نفس فورم السداد/التعديل
// المطلوب وترجّع القيم اللي المستخدم كتبها + تعرض رسالة الخطأ، عشان مفيش داتا
// تضيع ولا خطأ يفوت من غير ما يتشاف.
function applyStatementPrefill(prefill){
  const cid = prefill.contractId;
  const which = prefill.form === 'edit' ? 'edit' : 'pay';
  const tab = document.querySelector('.cst-tab[data-pane="contract_'+cid+'"]');
  if (tab) tab.click();
  toggleStmtForm(cid, which);
  const formHost = document.getElementById('stmt' + (which === 'pay' ? 'Pay' : 'Edit') + '_' + cid);
  if (!formHost) return;
  const form = formHost.querySelector('form');
  if (!form) return;
  if (prefill.old) {
    Object.entries(prefill.old).forEach(([key, val]) => {
      const input = form.querySelector('[name="'+key+'"]');
      if (input && val !== null && val !== undefined) input.value = val;
    });
  }
  if (prefill.errors && prefill.errors.length) {
    const banner = document.createElement('div');
    banner.style.cssText = 'background:#fbecea;color:#c0392b;border:1px solid #c0392b;border-radius:6px;padding:8px 12px;margin-bottom:8px;font-size:12px;font-weight:600;line-height:1.6';
    banner.innerHTML = prefill.errors.map(e => '<div>' + e + '</div>').join('');
    form.prepend(banner);
  }
  formHost.scrollIntoView({behavior:'smooth', block:'nearest'});
}

// أزرار سرعة تعبئة مبلغ الدفعة داخل كشف الحساب (عالمية عشان الـ partial بيتحقن بـ innerHTML)
function stmtSetPay(cid, monthly, remaining, type){
  const inp=document.getElementById('pay_amt_'+cid);
  if(!inp)return;
  if(type==='monthly') inp.value=Math.min(monthly,remaining).toFixed(2);
  else if(type==='full') inp.value=remaining.toFixed(2);
  else { inp.value=''; inp.focus(); }
}

// ═══ كشف الحساب: تبديل التابات + إظهار فورم السداد/التعديل + طباعة/تحميل/واتساب ═══
function switchTab(group, pane){
  const root=document.getElementById('captureCustomer_'+group);
  if(!root)return;
  root.querySelectorAll('.cst-pane').forEach(p=>p.style.display='none');
  const el=document.getElementById('pane_'+group+'_'+pane);
  if(el)el.style.display='block';
  const strip=document.getElementById('tabs_'+group);
  if(strip){
    strip.querySelectorAll('.cst-tab').forEach(t=>t.classList.remove('active-tab'));
    const tab=[...strip.querySelectorAll('.cst-tab')].find(t=>t.getAttribute('data-pane')===pane);
    if(tab)tab.classList.add('active-tab');
  }
  root.setAttribute('data-active-pane', pane);
}
function toggleStmtForm(id, which){
  const el =document.getElementById('stmt'+(which==='pay'?'Pay':'Edit')+'_'+id);
  const oth=document.getElementById('stmt'+(which==='pay'?'Edit':'Pay')+'_'+id);
  if(oth)oth.style.display='none';
  if(el)el.style.display=(el.style.display==='none'||!el.style.display)?'block':'none';
}
function _activePane(group){
  const root=document.getElementById('captureCustomer_'+group);
  return root?document.getElementById('pane_'+group+'_'+root.getAttribute('data-active-pane')):null;
}
function sendCustomerSheetWhatsApp(group){
  const p=_activePane(group); const url=p&&p.getAttribute('data-wa');
  if(url){window.open(url,'_blank');return;}
  const root=document.getElementById('captureCustomer_'+group); const all=root&&root.getAttribute('data-wa-all');
  if(all)window.open(all,'_blank');
}
function sendAllContractsWhatsApp(group){
  const root=document.getElementById('captureCustomer_'+group); const url=root&&root.getAttribute('data-wa-all');
  if(url)window.open(url,'_blank');
}
function printCustomerStatement(group){
  const node=document.getElementById('captureCustomer_'+group);
  if(!node)return;
  const styles=[...document.querySelectorAll('style, link[rel="stylesheet"]')].map(e=>e.outerHTML).join('');
  const clone=node.cloneNode(true);
  clone.querySelectorAll('.cst-pane').forEach(p=>p.style.display='block');
  clone.querySelectorAll('.no-print, .sheet-no-export').forEach(e=>e.remove());
  const ph=clone.querySelector('[class*="print-header-"]'); if(ph)ph.style.display='block';
  const w=window.open('','_blank','width=800,height=700');
  w.document.write('<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8">'+styles+'<style>@page{size:A4;margin:8mm}body{background:#fff;padding:10px;font-family:\'IBM Plex Sans Arabic\',sans-serif}</style></head><body>'+clone.innerHTML+'</body></html>');
  w.document.close();
  setTimeout(()=>{w.focus();w.print();},400);
}
function downloadCustomerSheet(group){
  const p=_activePane(group)||document.getElementById('captureCustomer_'+group);
  if(!p||typeof html2canvas==='undefined')return;
  html2canvas(p,{scale:2,backgroundColor:'#fff'}).then(canvas=>{
    const a=document.createElement('a');a.href=canvas.toDataURL('image/png');a.download='statement_'+group+'.png';a.click();
  });
}
// طباعة كشف الحساب المفتوح في نافذة مستقلة (تتجنّب تعارض print CSS)
function printStatement(){
  const node=document.getElementById('stmtPrint');
  if(!node)return;
  const styles=[...document.querySelectorAll('style, link[rel="stylesheet"]')].map(e=>e.outerHTML).join('');
  const w=window.open('','_blank','width=900,height=700');
  w.document.write('<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8">'+styles+'<style>body{background:#fff;padding:16px;font-family:"IBM Plex Sans Arabic",sans-serif}.no-print{display:none!important}</style></head><body class="inst-wrap">'+node.innerHTML+'</body></html>');
  w.document.close();
  setTimeout(()=>{w.focus();w.print();},400);
}

// إعادة فتح كشف نفس العميل بعد سداد/عكس دفعة — ولو الحفظ فشل (فاليديشن)، بترجّع
// تفتح نفس فورم السداد/التعديل مع رسالة الخطأ والقيم اللي المستخدم كتبها
@if(session('reopen_phone') !== null || session('reopen_name') !== null)
(function(){
  const rP=@json(session('reopen_phone','')), rN=@json(session('reopen_name',''));
  @if(session('reopen_contract_id') && $errors->any())
  const prefill = {
    contractId: @json(session('reopen_contract_id')),
    form: @json(session('reopen_form', 'pay')),
    errors: @json($errors->all()),
    old: @json(session()->getOldInput()),
  };
  @else
  const prefill = null;
  @endif
  setTimeout(()=>{try{openStatement(rP,rN,prefill);}catch(e){}},350);
})();
@endif

// ═══ فلاتر + إحصائيات + رسم الجدول (بروح السيستم الأول) ═══
let allInstData=[], customerProgress={}, currentStatusFilter='all';
function loadInstData(){
  try{allInstData=JSON.parse(document.getElementById('allInstallmentsData').textContent);}catch(e){allInstData=[];}
  try{customerProgress=JSON.parse(document.getElementById('customerProgressData').textContent);}catch(e){customerProgress={};}
}
function custKey(i){const p=String(i.customer_phone||'').trim();return (p&&p!=='—')?p:('n:'+(i.customer_name||''));}
function setStatusFilter(s,btn){currentStatusFilter=s;document.querySelectorAll('#statusPills .status-pill').forEach(p=>p.classList.remove('active'));if(btn)btn.classList.add('active');applyActiveFilters();}
function setTodayDueFilter(){const t=new Date().getDate();document.getElementById('dueRangeFrom').value=String(t);document.getElementById('dueRangeTo').value=String(t);applyActiveFilters();}
function resetActiveFilters(){document.getElementById('activeSearch').value='';document.getElementById('dueRangeFrom').value='0';document.getElementById('dueRangeTo').value='0';currentStatusFilter='all';document.querySelectorAll('#statusPills .status-pill').forEach(p=>p.classList.toggle('active',p.dataset.status==='all'));applyActiveFilters();}

function updateDueStats(range){
  const totalAmt=range.reduce((s,i)=>s+i.monthly_installment,0);
  const full=range.filter(i=>i._isPaid).length;
  const partial=range.filter(i=>!i._isPaid&&i._collected>0).length;
  const unpaid=range.filter(i=>i._collected===0).length;
  const collected=range.reduce((s,i)=>s+i._collected,0);
  document.getElementById('statTotal').innerText=range.length;
  document.getElementById('statDue').innerText=totalAmt.toLocaleString('en-US')+' ج';
  document.getElementById('statFullPaid').innerText=full;
  document.getElementById('statPartialPaid').innerText=partial;
  document.getElementById('statUnpaid').innerText=unpaid;
  document.getElementById('statCollected').innerText=collected.toLocaleString('en-US')+' ج';
  document.getElementById('statRemaining').innerText=(totalAmt-collected).toLocaleString('en-US')+' ج';
}
function computeStatus(i){return {paid:i.paid_this_month_amount, isFull:i.paid_this_month};}
function groupByCustomer(list){
  const g={};
  list.forEach(i=>{const k=custKey(i);if(!g[k])g[k]={key:k,name:i.customer_name,phone:i.customer_phone,items:[]};g[k].items.push(i);});
  return Object.values(g).map(x=>{
    const full=x.items.filter(c=>c._isPaid).length, partial=x.items.filter(c=>!c._isPaid&&c._collected>0).length, unpaid=x.items.filter(c=>c._collected===0).length;
    const prog=customerProgress[x.key]; let pct=0;
    if(prog&&prog.total_value>0){pct=Math.round((1-(prog.total_remaining/prog.total_value))*100);pct=Math.max(0,Math.min(100,pct));}
    return {name:x.name,phone:x.phone,count:x.items.length,
      totalMonthly:x.items.reduce((s,c)=>s+c.monthly_installment,0),
      totalRemaining:x.items.reduce((s,c)=>s+c.remaining_balance,0),
      full,partial,unpaid,pct};
  });
}
const PAGE=15; let _sorted=[], _page=1;
function renderRows(rows){_sorted=[...rows].sort((a,b)=>b.totalRemaining-a.totalRemaining);_page=1;renderPage();}
function gotoPage(p){_page=p;renderPage();}
function renderPage(){
  const tb=document.getElementById('dueByDayBody');tb.innerHTML='';
  const total=_sorted.length, pages=Math.max(1,Math.ceil(total/PAGE));
  if(_page>pages)_page=pages;
  const start=(_page-1)*PAGE, rows=_sorted.slice(start,start+PAGE);
  rows.forEach(r=>{
    const ini=r.name?r.name.charAt(0):'?';
    const wa=r.phone?`<a href="https://wa.me/2${r.phone}?text=${encodeURIComponent('السلام عليكم، تذكير بموعد سداد القسط.')}" target="_blank" onclick="event.stopPropagation()" class="whatsapp-link" title="واتساب"><i class="fab fa-whatsapp"></i></a>`:'';
    let badge='';
    if(r.unpaid===0&&r.partial===0) badge=`<span style="background:#f0fdf4;color:#15803d;border:1px solid #86efac;border-radius:20px;padding:4px 12px;font-size:.82rem;font-weight:800"><i class="fa fa-check-circle"></i> الكل مدفوع</span>`;
    else if(r.full===0&&r.partial===0) badge=`<span style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:20px;padding:4px 12px;font-size:.82rem;font-weight:800"><span style="width:8px;height:8px;border-radius:50%;background:#dc2626;display:inline-block;animation:pulse 1.5s infinite"></span> الكل لم يسدد</span>`;
    else{const p=[];if(r.full>0)p.push(`${r.full} كامل`);if(r.partial>0)p.push(`${r.partial} جزئي`);if(r.unpaid>0)p.push(`${r.unpaid} لسه`);badge=`<span style="background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd;border-radius:20px;padding:4px 12px;font-size:.82rem;font-weight:800"><i class="fa fa-chart-pie"></i> مختلط (${p.join(' / ')})</span>`;}
    const ph=String(r.phone||'').replace(/'/g,"\\'"), nm=String(r.name||'').replace(/'/g,"\\'");
    const allPaid=r.unpaid===0&&r.partial===0;
    tb.innerHTML+=`<tr class="clickable-row" onclick="openStatement('${ph}','${nm}')">
      <td class="text-start"><div class="d-flex align-items-center gap-2">
        <div class="client-avatar" style="background:${allPaid?'linear-gradient(135deg,#059669,#10b981)':'linear-gradient(135deg,#4f46e5,#60a5fa)'};color:#fff">${ini}</div>
        <div><strong class="d-block" style="font-size:.9rem">${r.name}</strong><small class="text-muted" dir="ltr">${r.phone||'—'}</small></div>${wa}</div></td>
      <td><span class="badge bg-secondary">${r.count} عقد</span></td>
      <td class="fw-bold text-danger">${r.totalMonthly.toLocaleString('en-US')} ج</td>
      <td class="fw-bold" style="color:#7c3aed">${r.totalRemaining.toLocaleString('en-US')} ج</td>
      <td style="min-width:110px"><div class="d-flex align-items-center gap-2">
        <div style="flex:1;height:8px;border-radius:5px;background:#e5e7eb;overflow:hidden"><div style="height:100%;width:${r.pct}%;background:${r.pct>=100?'#059669':(r.pct>=50?'#2563eb':'#d97706')}"></div></div>
        <small class="fw-bold" style="min-width:34px">${r.pct}%</small></div></td>
      <td>${badge}</td>
      <td class="no-print"><button class="btn btn-sm btn-outline-dark fw-bold" onclick="event.stopPropagation(); openStatement('${ph}','${nm}')"><i class="fa fa-table me-1"></i> كشف حساب</button></td>
    </tr>`;
  });
  const pager=document.getElementById('duePager');
  if(total<=PAGE){pager.style.display='none';}
  else{pager.style.display='flex';const from=start+1,to=Math.min(start+PAGE,total);
    document.getElementById('duePagerInfo').innerText=`عرض ${from}–${to} من ${total} عميل`;
    const btns=document.getElementById('duePagerBtns');btns.innerHTML='';
    const mk=(l,p,o={})=>{const b=document.createElement('button');b.type='button';b.className='btn btn-sm '+(o.active?'btn-dark':'btn-outline-secondary')+' fw-bold';b.innerHTML=l;if(o.disabled)b.disabled=true;else b.onclick=()=>gotoPage(p);return b;};
    btns.appendChild(mk('<i class="fa fa-angle-right"></i>',_page-1,{disabled:_page===1}));
    let s=Math.max(1,_page-2),e=Math.min(pages,_page+2);
    for(let p=s;p<=e;p++)btns.appendChild(mk(String(p),p,{active:p===_page}));
    btns.appendChild(mk('<i class="fa fa-angle-left"></i>',_page+1,{disabled:_page===pages}));
  }
}
function applyActiveFilters(){
  loadInstData();
  const fromDay=parseInt(document.getElementById('dueRangeFrom').value)||1;
  const toDay=parseInt(document.getElementById('dueRangeTo').value)||31;
  const term=(document.getElementById('activeSearch').value||'').trim().toLowerCase();
  allInstData.forEach(i=>{const st=computeStatus(i);i._collected=st.paid;i._isPaid=st.isFull;});
  let inRange=allInstData.filter(i=>i.due_day>=fromDay&&i.due_day<=toDay);
  if(term)inRange=inRange.filter(i=>String(i.customer_name||'').toLowerCase().includes(term)||String(i.customer_phone||'').toLowerCase().includes(term));
  updateDueStats(inRange);
  let matching=inRange;
  if(currentStatusFilter==='full')matching=inRange.filter(i=>i._isPaid);
  else if(currentStatusFilter==='partial')matching=inRange.filter(i=>!i._isPaid&&i._collected>0);
  else if(currentStatusFilter==='unpaid')matching=inRange.filter(i=>i._collected===0);
  const tb=document.getElementById('dueByDayBody');
  if(matching.length===0){tb.innerHTML='<tr><td colspan="7" class="text-center py-5 text-muted fw-bold"><i class="fa fa-calendar-check fa-2x d-block mb-2" style="opacity:.4"></i>لا يوجد عملاء مطابقين للفلتر</td></tr>';document.getElementById('duePager').style.display='none';return;}
  const keys=new Set(matching.map(custKey));
  renderRows(groupByCustomer(allInstData.filter(i=>keys.has(custKey(i)))));
}
document.addEventListener('DOMContentLoaded',applyActiveFilters);
</script>
@endpush
