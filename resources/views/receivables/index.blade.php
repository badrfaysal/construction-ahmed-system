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

/* ── شريط الإجماليات — أرقام مقسومة بفواصل بدل الكروت الملونة ── */
.rv-totals { display:flex; flex-wrap:wrap; background:#fff; border:1px solid var(--ln);
  border-radius:12px; margin-bottom:16px; overflow:hidden; }
.rv-tot { flex:1; min-width:130px; padding:14px 18px; border-inline-start:1px solid var(--ln); }
.rv-tot:first-child { border-inline-start:none; }
.rv-tot .l { font-size:.7rem; font-weight:600; color:var(--mut); margin-bottom:3px; }
.rv-tot .v { font-size:1.15rem; font-weight:700; color:var(--ink); }
.rv-tot .v small { font-size:.68rem; color:var(--soft); font-weight:400; }
.rv-tot.ok .v { color:var(--ok); }
.rv-tot.bad .v { color:var(--bad); }

/* ── صندوق الجدول ── */
.rv-box { background:#fff; border:1px solid var(--ln); border-radius:12px; overflow:hidden; margin-bottom:18px; }
.rv-boxhead { padding:12px 16px; display:flex; align-items:center; justify-content:space-between;
  gap:10px; flex-wrap:wrap; border-bottom:1px solid var(--ln); }
.rv-boxhead h2 { font-size:.95rem; font-weight:700; margin:0; color:var(--ink); }
.rv-boxhead .c { font-size:.75rem; color:var(--soft); }

.rv-filters { padding:10px 16px; border-bottom:1px solid var(--ln); display:flex;
  align-items:center; gap:8px; flex-wrap:wrap; background:var(--bg2); }
.rv-search { flex:1; min-width:180px; position:relative; }
.rv-search input { width:100%; padding:7px 32px 7px 10px; border:1px solid var(--ln);
  border-radius:8px; font-size:.83rem; background:#fff; }
.rv-search input:focus { outline:none; border-color:var(--ink); }
.rv-search .si { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--soft); font-size:.78rem; }
.rv-pill { padding:5px 13px; border-radius:8px; font-size:.77rem; font-weight:600; cursor:pointer;
  border:1px solid var(--ln); background:#fff; color:var(--mut); transition:.12s; }
.rv-pill:hover { border-color:var(--ink); color:var(--ink); }
.rv-pill.active { background:var(--ink); color:#fff; border-color:var(--ink); }

table.rv-tbl { width:100%; border-collapse:collapse; }
.rv-tbl th { padding:9px 14px; font-size:.7rem; font-weight:700; color:var(--soft);
  border-bottom:1px solid var(--ln); text-align:center; background:#fff; white-space:nowrap; }
.rv-tbl td { padding:11px 14px; font-size:.85rem; border-bottom:1px solid #f1f5f9;
  text-align:center; vertical-align:middle; }
.rv-tbl tbody tr { cursor:pointer; }
.rv-tbl tbody tr:hover td { background:var(--bg2); }
.rv-tbl tfoot td { font-weight:700; background:var(--bg2); border-top:1px solid var(--ln); font-size:.83rem; }

.rv-dot { width:7px; height:7px; border-radius:99px; display:inline-block; margin-left:5px; }
.rv-st { font-size:.74rem; font-weight:600; white-space:nowrap; }
.rv-st.pend { color:#a16207; } .rv-st.pend .rv-dot { background:#eab308; }
.rv-st.done { color:var(--ok); } .rv-st.done .rv-dot { background:#10b981; }
.rv-cont-tag { font-size:.66rem; font-weight:600; color:var(--mut); border:1px solid var(--ln);
  border-radius:6px; padding:1px 7px; display:inline-block; margin-top:3px; background:var(--bg2); }

.rv-mini-prog { width:74px; height:4px; background:#eef2f7; border-radius:99px; overflow:hidden; margin:4px auto 0; }
.rv-mini-prog i { display:block; height:100%; background:var(--ink); border-radius:99px; }

/* ── قوائم الأقساط المتأخرة/القادمة — صفوف بسيطة ── */
.rv-line { display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #f1f5f9; font-size:.83rem; }
.rv-line:last-child { border-bottom:none; }
.rv-line .grow { flex:1; min-width:0; }
.rv-line .t { font-weight:600; color:var(--ink); }
.rv-line .s { font-size:.73rem; color:var(--mut); }

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
<div class="rv">

<div class="page-head">
  <div>
    <h3>المستحقات</h3>
    <p>ما يستحقه العملاء تجاه مشاريعهم — المفوتر والمحصّل والمتبقي</p>
  </div>
  <div style="display:flex;gap:8px" class="no-print">
    <button onclick="window.print()" class="btn ghost"><i class="fa fa-print" style="font-size:.85rem"></i> طباعة القائمة</button>
    <a href="{{ route('installments.index') }}" class="btn ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-receipt"/></svg>
      الأقساط
    </a>
  </div>
</div>

{{-- شريط الإجماليات — كروت مربعة متدرّجة (نفس روح لوحة التحكم) --}}
<div class="grid cols-5" style="margin-bottom:20px">
  <div class="vstat vstat-blue">
    <div class="top"><span class="label">إجمالي المفوتر</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['total_billed']) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-green">
    <div class="top"><span class="label">المحصّل من العملاء</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['total_collected']) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-red">
    <div class="top"><span class="label">المتبقي على العملاء</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['total_remaining']) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-amber">
    <div class="top"><span class="label">الربح الدفتري</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['book_profit']) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-navy">
    <div class="top"><span class="label">الربح المحصل فعليًا</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['earned_profit']) }} <small>ج.م</small></div>
  </div>
</div>

{{-- الجدول الرئيسي --}}
<div class="rv-box">
  <div class="rv-boxhead">
    <h2>مستحقات المشاريع</h2>
    <span class="c">{{ $rows->count() }} مشروع — اضغط للتفاصيل والتحصيل</span>
  </div>

  <div class="rv-filters no-print">
    <div class="rv-search">
      <input type="text" id="main-search" placeholder="ابحث بالمشروع أو العميل..." oninput="filterMain()">
      <i class="fa fa-search si"></i>
    </div>
    <span class="rv-pill active" onclick="filterStatus('all', this)">الكل</span>
    <span class="rv-pill" onclick="filterStatus('active', this)">قيد التحصيل</span>
    <span class="rv-pill" onclick="filterStatus('paid', this)">مسدد</span>
  </div>

  @if($rows->count())
  <div style="overflow-x:auto">
    <table class="rv-tbl" id="main-table">
      <thead>
        <tr>
          <th style="text-align:right;padding-right:16px">المشروع / العميل</th>
          <th>المفوتر</th>
          <th>المحصّل</th>
          <th>المتبقي</th>
          <th>التحصيل</th>
          <th>ربح دفتري</th>
          <th>الحالة</th>
        </tr>
      </thead>
      <tbody id="main-tbody">
        @foreach($rows as $row)
          @php
            $pct    = $row->billed > 0 ? round($row->collected / $row->billed * 100) : 0;
            $isPaid = $row->remaining <= 0.009;
          @endphp
          <tr onclick="openModal({{ $row->project->id }})"
              data-name="{{ mb_strtolower($row->project->name . ' ' . $row->project->client->name) }}"
              data-status="{{ $isPaid ? 'paid' : 'active' }}">
            <td style="text-align:right;padding-right:16px">
              <strong style="display:block;font-size:.87rem">{{ $row->project->name }}</strong>
              <small style="color:var(--mut)">{{ $row->project->client->name }}</small>
              @if($row->project->hasInstallmentContract())
                <span class="rv-cont-tag"><i class="fa fa-file-contract"></i> عقد تقسيط</span>
              @endif
            </td>
            <td style="font-weight:600">{{ \App\Support\Money::format($row->billed) }}</td>
            <td style="color:var(--ok);font-weight:600">{{ \App\Support\Money::format($row->collected) }}</td>
            <td style="color:{{ $row->remaining > 0 ? 'var(--bad)' : 'var(--ok)' }};font-weight:700">{{ \App\Support\Money::format($row->remaining) }}</td>
            <td>
              <span style="font-size:.75rem;font-weight:700">{{ $pct }}%</span>
              <div class="rv-mini-prog"><i style="width:{{ min($pct,100) }}%"></i></div>
            </td>
            <td style="color:var(--mut)">{{ \App\Support\Money::format($row->book_profit) }}</td>
            <td>
              @if($isPaid)
                <span class="rv-st done"><span class="rv-dot"></span>مسدد</span>
              @else
                <span class="rv-st pend"><span class="rv-dot"></span>قيد التحصيل</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td style="text-align:right;padding-right:16px">الإجمالي ({{ $rows->count() }} مشروع)</td>
          <td>{{ \App\Support\Money::format($totals['total_billed']) }}</td>
          <td style="color:var(--ok)">{{ \App\Support\Money::format($totals['total_collected']) }}</td>
          <td style="color:{{ $totals['total_remaining'] > 0 ? 'var(--bad)' : 'var(--ok)' }}">{{ \App\Support\Money::format($totals['total_remaining']) }}</td>
          <td>{{ $totals['total_billed'] > 0 ? round($totals['total_collected'] / $totals['total_billed'] * 100) : 0 }}%</td>
          <td style="color:var(--mut)">{{ \App\Support\Money::format($totals['book_profit']) }}</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <div id="no-results" style="display:none" class="rv-empty">
    <i class="fa fa-search" style="display:block;font-size:1.4rem;margin-bottom:6px"></i>
    لا توجد نتائج مطابقة
  </div>
  @else
    <div class="rv-empty" style="padding:50px">
      <i class="fa fa-inbox" style="display:block;font-size:2rem;margin-bottom:10px"></i>
      <strong style="display:block;color:var(--mut);margin-bottom:4px">لا توجد مشاريع مُفوترة</strong>
      أضف بنوداً ومواد للمشاريع لتظهر هنا المستحقات
    </div>
  @endif
</div>

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
      <form method="POST" action="{{ route('installments.markPaid', $inst) }}" class="no-print">
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
    <div class="rv-card">

      <div class="rv-mhead">
        <div class="nm">
          {{ $proj->name }}
          <small><i class="fa fa-user" style="font-size:.62rem"></i> {{ $proj->client->name }}@if($clientPhone) · <span dir="ltr">{{ $clientPhone }}</span>@endif</small>
        </div>
        <button type="button" class="rv-x" onclick="closeModal({{ $proj->id }})">×</button>
      </div>

      <div class="rv-mbody" id="recv-print-{{ $proj->id }}">

        {{-- الأرقام --}}
        <div class="rv-nums">
          <div class="rv-num"><div class="l">المفوتر</div><div class="v">{{ \App\Support\Money::format($row->billed) }}</div></div>
          <div class="rv-num ok"><div class="l">المحصّل</div><div class="v">{{ \App\Support\Money::format($row->collected) }}</div></div>
          <div class="rv-num {{ $isPaid ? 'ok' : 'bad' }}"><div class="l">المتبقي</div><div class="v">{{ \App\Support\Money::format($row->remaining) }}</div></div>
        </div>
        <div class="rv-prog"><i style="width:{{ min($pct,100) }}%"></i></div>

        {{-- الأزرار --}}
        <div class="rv-acts no-print">
          @if($hasCont)
            <a href="{{ route('installments.index') }}" class="rv-act main" style="grid-column:span 2"><i class="fa fa-file-contract"></i> فتح صفحة الأقساط</a>
          @endif
          @if($hasExcess || (!$hasCont && !$isPaid))
            <button class="rv-act" id="rv-full-{{ $proj->id }}" onclick="recvFull({{ $proj->id }}, {{ $payAmount }})" style="grid-column:span 2; border-color:var(--ink)"><i class="fa fa-check-double"></i> سداد {{ $hasCont ? 'المستحق الإضافي' : 'كلي' }}</button>
            <button class="rv-act" id="rv-partial-{{ $proj->id }}" onclick="recvPartial({{ $proj->id }})" style="grid-column:span 2"><i class="fa fa-money-bill"></i> سداد جزئي</button>

          @elseif(!$hasCont && $isPaid)
            <span class="rv-act done" style="grid-column:span 2"><i class="fa fa-check-circle"></i> تم السداد الكامل</span>
          @endif
          <button class="rv-act" onclick="openDiscountPanel({{ $proj->id }})"><i class="fa fa-percent"></i> منح خصم</button>
          <button class="rv-act" onclick="waRecv('{{ $clientPhone }}','{{ addslashes($proj->name) }}',{{ $row->remaining }})"><i class="fa-brands fa-whatsapp"></i> واتساب</button>
          <button class="rv-act" onclick='printInvoice({{ $proj->id }}, @json($invoiceData))' style="grid-column:span 2"><i class="fa fa-print"></i> طباعة فاتورة</button>
        </div>

        @if($hasCont)
          {{-- محوّل لعقد تقسيط — عقد التقسيط شغال لوحده، التحصيل عليه من صفحة الأقساط --}}
          <div class="rv-contract">
            <div class="h"><i class="fa fa-file-contract"></i> هذا المشروع (أو بند فيه) معموله عقد تقسيط — سداد العقد نفسه بيتم من صفحة الأقساط.</div>
            <table>
              <thead><tr><th style="text-align:right">المتعاقد عليه</th><th>النوع</th><th>الإجمالي</th><th>المحصّل</th><th>المتبقي</th></tr></thead>
              <tbody>
                @foreach($proj->contracts as $c)
                  <tr>
                    <td style="text-align:right">{{ $c->product_name }}</td>
                    <td>{{ $c->band_id ? 'بند: ' . ($c->band?->name ?? '—') : 'المشروع كامل' }}</td>
                    <td style="font-weight:700">{{ \App\Support\Money::format($c->total_after_interest) }}</td>
                    <td style="color:var(--ok);font-weight:700">{{ \App\Support\Money::format($c->down_payment + $c->payments->sum('amount_paid')) }}</td>
                    <td style="color:{{ $c->remaining_balance > 0 ? 'var(--bad)' : 'var(--ok)' }};font-weight:700">{{ \App\Support\Money::format($c->remaining_balance) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif

        @if(!$hasCont || $hasExcess || $proj->clientPayments->count())
          @if($hasCont)
            {{-- فاصل يوضّح إن اللي جاي بعد كده مستحق منفصل تمامًا عن العقد --}}
            <div style="display:flex;align-items:center;gap:8px;margin:12px 0 4px">
              <span style="flex:1;height:1px;background:var(--ln)"></span>
              <span style="font-size:.7rem;color:var(--mut);font-weight:700">مستحق إضافي خارج نطاق العقد (فوترة بعد التعاقد)</span>
              <span style="flex:1;height:1px;background:var(--ln)"></span>
            </div>
          @endif

          {{-- فورم منح الخصم --}}
          <div class="rv-pay no-print" id="disc-panel-{{ $proj->id }}" style="display:none;margin-bottom:12px;border-color:var(--ink)">
            <div class="hd" style="color:var(--ink)">
              <span><i class="fa fa-percent"></i> منح خصم للمشروع</span>
              <button type="button" class="rv-x" style="color:var(--soft);font-size:1rem" onclick="hideDiscountPanel({{ $proj->id }})">×</button>
            </div>
            <form method="POST" action="{{ route('projects.discount', $proj) }}">
              @csrf
              <div style="font-size:.75rem;color:var(--mut);margin-bottom:10px">
                إجمالي الخصومات الحالية: <strong>{{ \App\Support\Money::format($proj->discounts->sum('amount')) }} ج.م</strong>
              </div>
              <div class="rv-row2">
                <div>
                  <label>مبلغ الخصم الإضافي (ج.م) *</label>
                  <input type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required>
                </div>
                <div>
                  <label>تاريخ الخصم *</label>
                  <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required>
                </div>
              </div>
              <div style="margin-top:8px">
                <label>ملاحظات/سبب الخصم</label>
                <input type="text" name="notes" placeholder="اختياري" style="width:100%;padding:8px 10px;border:1px solid var(--ln);border-radius:8px;font-size:.84rem;">
              </div>
              <button type="submit" class="rv-submit" style="background:var(--ink)">تحديث الخصم</button>
            </form>
          </div>

          {{-- فورم التحصيل --}}
          <div class="rv-pay no-print" id="pay-panel-{{ $proj->id }}" style="display:none">
            <div class="hd">
              <span><i class="fa fa-cash-register"></i> تسجيل تحصيل من العميل</span>
              <button type="button" class="rv-x" style="color:var(--soft);font-size:1rem" onclick="hidePayPanel({{ $proj->id }})">×</button>
            </div>
            @php $reopenHere = session('reopen_project') == $proj->id; @endphp
            @if($reopenHere && $errors->any())
              <div class="rv-form-errors" style="background:var(--neg-soft,#fbecea);color:var(--neg,#c0392b);border:1px solid var(--neg,#c0392b);border-radius:6px;padding:8px 12px;margin-bottom:10px;font-size:.8rem;font-weight:600;line-height:1.6">
                @foreach($errors->all() as $err)
                  <div>{{ $err }}</div>
                @endforeach
              </div>
            @endif
            @if($payAmount > 0.009)
            <form method="POST" action="{{ route('receivables.pay', $proj) }}">
              @csrf
              <div class="rv-presets">
                <span class="rv-preset hot" onclick="setAmt({{ $proj->id }}, {{ $payAmount }})">سداد كامل — {{ \App\Support\Money::format($payAmount) }} ج</span>
                <span class="rv-preset" onclick="setAmt({{ $proj->id }}, {{ round($payAmount * 0.5, 2) }})">النصف — {{ \App\Support\Money::format($payAmount * 0.5) }} ج</span>
                <span class="rv-preset" onclick="setAmt({{ $proj->id }}, {{ round($payAmount * 0.25, 2) }})">الربع — {{ \App\Support\Money::format($payAmount * 0.25) }} ج</span>
              </div>
              <div class="rv-row2">
                <div>
                  <label>المبلغ (ج.م) *</label>
                  <input type="number" step="0.01" min="0.01" name="amount" id="recv_amt_{{ $proj->id }}" placeholder="0.00" value="{{ $reopenHere ? old('amount') : '' }}" required>
                </div>
                <div>
                  <label>التاريخ *</label>
                  <input type="date" name="date" value="{{ $reopenHere ? old('date', today()->format('Y-m-d')) : today()->format('Y-m-d') }}" required>
                </div>
              </div>
              <div class="rv-row2">
                <div>
                  <label>الخصم (ج.م)</label>
                  <input type="number" step="0.01" min="0" name="discount" id="recv_disc_{{ $proj->id }}" placeholder="0.00" value="{{ $reopenHere ? old('discount') : '' }}">
                </div>
                <div>
                  <label>ملاحظات</label>
                  <input type="text" name="notes" placeholder="اختياري" value="{{ $reopenHere ? old('notes') : '' }}">
                </div>
              </div>
              <label><i class="fa fa-wallet"></i> المحفظة (التحصيل فيها) *</label>
              @include('partials._wallet-select', ['wallets' => $wallets, 'bare' => true, 'required' => true, 'selectStyle' => 'width:100%', 'selected' => $reopenHere ? old('account_id') : null])

              @if($proj->bands->count())
                @php $oldBandChoice = $reopenHere ? old('band_choice', 'general') : 'general'; @endphp
                <label>الدفعة دي تتسجّل على إيه؟</label>
                <div class="rv-radio">
                  <label><input type="radio" name="band_choice" value="general" {{ $oldBandChoice === 'general' ? 'checked' : '' }} onchange="toggleRecvBand({{ $proj->id }}, this.value)"> دفعة عامة للمشروع</label>
                  <label><input type="radio" name="band_choice" value="band" {{ $oldBandChoice === 'band' ? 'checked' : '' }} onchange="toggleRecvBand({{ $proj->id }}, this.value)"> تحت بند محدد</label>
                </div>
                <select name="band_id" id="recv-band-{{ $proj->id }}" {{ $oldBandChoice === 'band' ? '' : 'disabled' }} style="display:{{ $oldBandChoice === 'band' ? 'block' : 'none' }};margin-top:7px">
                  <option value="">— اختر البند —</option>
                  @foreach($proj->bands as $band)
                    <option value="{{ $band->id }}" {{ $reopenHere && (int) old('band_id') === $band->id ? 'selected' : '' }}>{{ $band->name }}</option>
                  @endforeach
                </select>
              @endif

              <button type="submit" class="rv-submit"><i class="fa fa-check"></i> تسجيل التحصيل</button>
            </form>
            @else
              <div style="text-align:center;color:var(--ok);font-weight:600;font-size:.83rem;padding:6px">
                <i class="fa fa-check-circle"></i> {{ $hasCont ? 'تم تحصيل كامل المستحق الإضافي' : 'تم تحصيل كامل المستحق من هذا العميل' }}
              </div>
            @endif
          </div>

          {{-- السجل --}}
          <div class="rv-hist-h">
            <h6>سجل التحصيلات{{ $hasCont ? ' (المستحق الإضافي)' : '' }}</h6>
            @if($payAmount > 0.009)<span class="rem">المتبقي: {{ \App\Support\Money::format($payAmount) }} ج</span>@endif
          </div>

          <div class="rv-hf no-print" id="hist-filters-{{ $proj->id }}">
            <button class="f active" onclick="filterHist({{ $proj->id }},'all',this)">الكل</button>
            <button class="f" onclick="filterHist({{ $proj->id }},'today',this)">اليوم</button>
            <button class="f" onclick="filterHist({{ $proj->id }},'week',this)">أسبوع</button>
            <button class="f" onclick="filterHist({{ $proj->id }},'month',this)">شهر</button>
            <button class="f" onclick="filterHist({{ $proj->id }},'custom',this)">مخصص</button>
            <input type="text" placeholder="بحث..." oninput="searchHist({{ $proj->id }}, this.value)">
          </div>
          <div id="custom-range-{{ $proj->id }}" class="no-print" style="display:none;gap:6px;align-items:center;margin-bottom:8px">
            <input type="date" id="dfrom-{{ $proj->id }}" style="padding:4px 7px;border:1px solid var(--ln);border-radius:6px;font-size:.73rem" oninput="applyCustomRange({{ $proj->id }})">
            <span style="font-size:.72rem;color:var(--mut)">إلى</span>
            <input type="date" id="dto-{{ $proj->id }}" style="padding:4px 7px;border:1px solid var(--ln);border-radius:6px;font-size:.73rem" oninput="applyCustomRange({{ $proj->id }})">
          </div>

          @if($proj->clientPayments->count())
          <table class="rv-hist" id="hist-tbl-{{ $proj->id }}">
            <thead>
              <tr><th>التاريخ</th><th style="text-align:right">البيان</th><th>المبلغ</th></tr>
            </thead>
            <tbody>
              @foreach($proj->clientPayments as $pay)
                <tr data-date="{{ \Carbon\Carbon::parse($pay->date)->format('Y-m-d') }}"
                    data-desc="{{ mb_strtolower($pay->description ?? '') }}"
                    style="cursor:pointer"
                    onclick="showPayDetail(this)"
                    data-full-date="{{ \Carbon\Carbon::parse($pay->date)->format('d/m/Y') }}"
                    data-amount="{{ number_format($pay->amount, 2) }}"
                    data-discount="{{ \App\Support\Money::format($pay->discount, 2) }}"
                    data-description="{{ $pay->description ?: 'تحصيل مباشر' }}"
                    data-band="{{ $pay->band->name ?? '' }}">
                  <td style="color:var(--mut)">{{ \Carbon\Carbon::parse($pay->date)->format('d/m/Y') }}</td>
                  <td class="desc" title="{{ $pay->description }}">{{ $pay->description ?: 'تحصيل مباشر' }}</td>
                  <td style="color:var(--ok);font-weight:700">
                    {{ \App\Support\Money::format($pay->amount) }} ج
                    @if((float) $pay->discount > 0)
                      <span style="color:var(--mut);font-size:10.5px">(خصم {{ \App\Support\Money::format($pay->discount) }})</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2" style="text-align:right;font-size:.72rem;color:var(--mut)">إجمالي التحصيلات</td>
                <td style="color:var(--ok)">{{ \App\Support\Money::format($proj->clientPayments->sum(fn($p) => (float) $p->amount + (float) $p->discount)) }} ج</td>
              </tr>
            </tfoot>
          </table>
          @else
            <div class="rv-empty">
              لا توجد تحصيلات مسجلة بعد
              @if($payAmount > 0.009)<div style="margin-top:4px;font-size:.73rem">استخدم <strong>سداد كلي</strong> أو <strong>سداد جزئي</strong> أعلاه</div>@endif
            </div>
          @endif
        @endif

        {{-- البنود --}}
        @if($proj->bands->count())
          <div class="no-print">
            <div class="rv-bands-t" onclick="toggleBands({{ $proj->id }})">
              <span><i class="fa fa-list-ul"></i> البنود المتعاقد عليها ({{ $proj->bands->count() }})</span>
              <i class="fa fa-chevron-down" id="bands-icon-{{ $proj->id }}" style="font-size:.7rem;color:var(--soft);transition:.2s"></i>
            </div>
            <div class="rv-bands-b" id="bands-body-{{ $proj->id }}">
              <table>
                @foreach($proj->bands as $band)
                  @php $st = $band->status ?? 'pending'; @endphp
                  <tr>
                    <td style="font-weight:600">{{ $band->name }}</td>
                    <td style="text-align:center;font-weight:700">{{ \App\Support\Money::format($band->client_price ?? 0) }} ج</td>
                    <td style="text-align:center;font-size:.72rem;color:var(--mut)">
                      {{ $st === 'completed' ? 'مكتمل' : ($st === 'in_progress' || $st === 'active' ? 'جاري' : 'معلق') }}
                    </td>
                  </tr>
                @endforeach
              </table>
            </div>
          </div>
        @endif

      </div>{{-- /rv-mbody --}}
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

@push('scripts')
<script>
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
function filterHist(id, period, btn) {
  document.querySelectorAll('#hist-filters-' + id + ' .f').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  const cr = document.getElementById('custom-range-' + id);
  if (cr) cr.style.display = (period === 'custom') ? 'flex' : 'none';
  if (period === 'custom') return;
  applyDateFilter(id, period, null, null);
}
function applyCustomRange(id) {
  applyDateFilter(id, 'custom',
    document.getElementById('dfrom-' + id)?.value,
    document.getElementById('dto-' + id)?.value);
}
function applyDateFilter(id, period, customFrom, customTo) {
  const today = new Date(), todayStr = today.toISOString().slice(0,10);
  let from = null, to = null;
  if (period === 'today') { from = to = todayStr; }
  else if (period === 'week')  { const d = new Date(today); d.setDate(d.getDate()-7);  from = d.toISOString().slice(0,10); }
  else if (period === 'month') { const d = new Date(today); d.setDate(d.getDate()-30); from = d.toISOString().slice(0,10); }
  else if (period === 'custom') { from = customFrom || null; to = customTo || null; }
  document.querySelectorAll('#hist-tbl-' + id + ' tbody tr[data-date]').forEach(row => {
    if (!from) { row.style.display = ''; return; }
    const d = row.dataset.date;
    row.style.display = (d >= from && d <= (to || '9999-99-99')) ? '' : 'none';
  });
}
function searchHist(id, q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('#hist-tbl-' + id + ' tbody tr[data-desc]').forEach(row => {
    row.style.display = (!q || row.dataset.desc.includes(q) || row.cells[0].textContent.includes(q)) ? '' : 'none';
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
let activeStatus = 'all';
function filterMain() {
  const q = document.getElementById('main-search').value.toLowerCase().trim();
  let visible = 0;
  document.querySelectorAll('#main-tbody tr').forEach(row => {
    const show = (!q || row.dataset.name.includes(q))
      && (activeStatus === 'all' || row.dataset.status === activeStatus);
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  const nr = document.getElementById('no-results');
  if (nr) nr.style.display = visible === 0 ? 'block' : 'none';
}
function filterStatus(status, btn) {
  activeStatus = status;
  document.querySelectorAll('.rv-pill').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  if (btn) btn.classList.add('active');
  filterMain();
}

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
</script>
@endpush
@endsection
