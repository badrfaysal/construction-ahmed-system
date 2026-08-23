@extends('layouts.app')
@section('title', 'التقارير')
@section('page-title', 'التقارير')

@section('content')
<div class="page-head no-print">
  <div><h3>التقارير</h3><p>تحليل تفصيلي للأرباح والمصروفات عبر كل المشاريع</p></div>
  <button onclick="window.print()" class="btn ghost">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
    طباعة
  </button>
</div>

@push('styles')
<style>
.vstat { padding: 16px; min-height: 110px; }
.vstat:hover .ic { transform: scale(1.15) rotate(8deg); background: rgba(255, 255, 255, 0.3); }
.vstat .vstat-bg { position: absolute; left: -10px; bottom: -15px; width: 90px; height: 90px; color: rgba(255, 255, 255, 0.15); z-index: 0; transform: rotate(-15deg); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none; }
.vstat:hover .vstat-bg { transform: scale(1.2) rotate(5deg); color: rgba(255, 255, 255, 0.25); }
.vstat .val { font-size: 1.5rem; }
.vstat .note { font-size: 0.75rem; }

.charts-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.charts-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
@media(max-width: 1024px) { .charts-grid-4 { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 768px) { .charts-grid-4 { grid-template-columns: 1fr; } .charts-grid-2 { grid-template-columns: 1fr; } }
@media(max-width: 768px) { .cols-4 { grid-template-columns: 1fr !important; } }
</style>
@endpush

{{-- Filters --}}
<form method="GET" class="filter-bar no-print">
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-filter"/></svg>
      من تاريخ
    </label>
    <input type="date" name="from" value="{{ $from?->format('Y-m-d') }}" class="f-select">
  </div>
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-filter"/></svg>
      إلى تاريخ
    </label>
    <input type="date" name="to" value="{{ $to?->format('Y-m-d') }}" class="f-select">
  </div>
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      المشروع
    </label>
    <div class="f-select-wrap">
      <select name="project_id" class="f-select">
        <option value="">كل المشاريع</option>
        @foreach($allProjects as $p)
          <option value="{{ $p->id }}" {{ (string) $projectId === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
    </div>
  </div>
  <div class="f-actions">
    <button type="submit" class="btn sm">تطبيق</button>
    @if($from || $to || $projectId)
      <a href="{{ route('reports.dashboard') }}" class="btn ghost sm">مسح الفلاتر</a>
    @endif
  </div>
</form>

{{-- Summary KPIs --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:20px">
  
  <div class="vstat {{ $totalProfit >= 0 ? 'vstat-green' : 'vstat-red' }}">
    <div class="top">
      <span class="label">إجمالي الربح</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalProfit) }} <small>ج.م</small></div>
    <div class="note" style="margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 8px;">
      <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
        <span>ربح تجاري:</span> 
        <strong>{{ \App\Support\Money::format($totalTradeProfit) }}</strong>
      </div>
      <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
        <span>نسبة إشراف:</span> 
        <strong>{{ \App\Support\Money::format($totalPercentageProfit) }}</strong>
      </div>
      <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
        <span>أرباح تقسيط:</span> 
        <strong>{{ \App\Support\Money::format($totalInstallmentProfit) }}</strong>
      </div>
      @if($totalDiscounts > 0)
      <div style="display:flex; justify-content:space-between; margin-bottom: 2px; padding-top: 4px; margin-top: 4px; border-top: 1px dashed rgba(255,255,255,0.2);">
        <span>خصومات (تُطرح):</span> 
        <strong>-{{ \App\Support\Money::format($totalDiscounts) }}</strong>
      </div>
      @endif
      @if(isset($totalMarketerCommissions) && $totalMarketerCommissions > 0)
      <div style="display:flex; justify-content:space-between;">
        <span>عمولات مسوقين:</span> 
        <strong>-{{ \App\Support\Money::format($totalMarketerCommissions) }}</strong>
      </div>
      @endif
    </div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg>
  </div>

  <div class="vstat vstat-red">
    <div class="top">
      <span class="label">إجمالي المصروف</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalSpent) }} <small>ج.م</small></div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg>
  </div>

  <div class="vstat vstat-blue">
    <div class="top">
      <span class="label">إجمالي المحصّل</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalCollected) }} <small>ج.م</small></div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
  </div>

  <div class="vstat vstat-amber">
    <div class="top">
      <span class="label">الخصومات الممنوحة</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-percent"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalDiscounts) }} <small>ج.م</small></div>
    @if($topDiscountProject)
      <div class="note" style="color:rgba(255,255,255,0.8)">أعلى خصم: <a href="{{ route('projects.show', $topDiscountProject->id) }}" style="color:#fff;text-decoration:underline">{{ $topDiscountProject->name }}</a> ({{ \App\Support\Money::format($topDiscountProject->totalDiscount()) }})</div>
    @endif
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-percent"/></svg>
  </div>

  <div class="vstat vstat-teal">
    <div class="top">
      <span class="label">إجمالي عمولات المسوقين</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalMarketerCommissions ?? 0) }} <small>ج.م</small></div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
  </div>

  <div class="vstat vstat-red">
    <div class="top">
      <span class="label">إجمالي المصروفات (عامة + مشاريع)</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalAllExpenses ?? 0) }} <small>ج.م</small></div>
    <div class="note" style="color:rgba(255,255,255,0.9); margin-top:8px; font-size:11px; display:flex; justify-content:space-between;">
      <span>عامة: {{ \App\Support\Money::format($totalCompanyExpenses ?? 0) }}</span>
      <span>مشاريع: {{ \App\Support\Money::format($totalProjectExpenses ?? 0) }}</span>
    </div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
  </div>
</div>

{{-- ═══ Charts Grid: 4 compact cards ═══ --}}
<div class="charts-grid-4">

  {{-- 1. Area line: cash flow --}}
  <div class="card card-pad" style="padding:14px 16px">
    <div style="font-size:11px;font-weight:700;color:var(--ink-2);margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em">التدفق الشهري</div>
    <canvas id="cashFlowChart" height="110"></canvas>
  </div>

  {{-- 2. Doughnut: spend distribution --}}
  <div class="card card-pad" style="padding:14px 16px;display:flex;flex-direction:column;align-items:center">
    <div style="font-size:11px;font-weight:700;color:var(--ink-2);margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em;align-self:flex-start">توزيع المصروف</div>
    <div style="position:relative;width:130px;height:130px;flex-shrink:0">
      <canvas id="spendDonut"></canvas>
      <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none">
        <div style="font-size:9px;color:var(--ink-3)">إجمالي</div>
        <div class="tnum" style="font-size:12px;font-weight:800">{{ number_format($totalSpent/1000,1) }}K</div>
      </div>
    </div>
    <div id="donut-legend" style="margin-top:8px;display:flex;flex-direction:column;gap:4px;width:100%"></div>
  </div>

  {{-- 3. Polar area: top bands --}}
  <div class="card card-pad" style="padding:14px 16px;display:flex;flex-direction:column;align-items:center">
    <div style="font-size:11px;font-weight:700;color:var(--ink-2);margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em;align-self:flex-start">أكثر البنود</div>
    <canvas id="topBandsChart" height="150" style="max-width:170px"></canvas>
  </div>

  {{-- 4. Horizontal bar: top projects --}}
  <div class="card card-pad" style="padding:14px 16px">
    <div style="font-size:11px;font-weight:700;color:var(--ink-2);margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em">أعلى مشاريع ربحًا</div>
    <canvas id="topProjectsChart" height="110"></canvas>
  </div>

</div>

{{-- ═══ Materials Charts: purchased + returned ═══ --}}
<div class="charts-grid-2">

  <div class="card card-pad" style="padding:14px 16px">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
      <div style="width:28px;height:28px;border-radius:7px;background:#ececfe;display:grid;place-items:center;flex-shrink:0">
        <svg viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><use href="#i-box"/></svg>
      </div>
      <div>
        <div style="font-weight:700;font-size:13px">أكثر الخامات شراءً</div>
        <div style="font-size:10px;color:var(--ink-2)">حسب التكلفة الصافية</div>
      </div>
    </div>
    @if(count($topPurchasedMaterials) > 0)
      <canvas id="topPurchasedChart" height="180"></canvas>
    @else
      <div style="height:180px;display:flex;align-items:center;justify-content:center;color:var(--ink-3);font-size:13px;background:var(--bg);border-radius:8px">لا توجد خامات مشتراة بعد</div>
    @endif
  </div>

  <div class="card card-pad" style="padding:14px 16px">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
      <div style="width:28px;height:28px;border-radius:7px;background:#fdeae7;display:grid;place-items:center;flex-shrink:0">
        <svg viewBox="0 0 24 24" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
      </div>
      <div>
        <div style="font-weight:700;font-size:13px">أكثر الخامات مرتجعاً</div>
        <div style="font-size:10px;color:var(--ink-2)">حسب قيمة المرتجع</div>
      </div>
    </div>
    @if(count($topReturnedMaterials) > 0)
      <canvas id="topReturnedChart" height="180"></canvas>
    @else
      <div style="height:180px;display:flex;align-items:center;justify-content:center;color:var(--ink-3);font-size:13px;background:var(--bg);border-radius:8px">لا توجد خامات مرتجعة بعد</div>
    @endif
  </div>

</div>

{{-- Top projects tables --}}
<div class="grid cols-4" style="margin-bottom:24px">
  <div class="table-card">
    <div class="table-top"><h4>أكتر مشروع صرفت فيه</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>#</th><th>المشروع</th><th class="num">المصروف</th></tr></thead>
        <tbody>
          @forelse($topProjectsBySpend as $i => $row)
            <tr>
              <td><span style="width:22px;height:22px;border-radius:50%;background:{{ ['#4f46e5','#6366f1','#818cf8','#a5b4fc','#c7d2fe'][$i % 5] }};color:#fff;display:grid;place-items:center;font-size:10px;font-weight:700">{{ $i+1 }}</span></td>
              <td>{{ $row->name }}</td>
              <td class="num" style="font-weight:700">{{ \App\Support\Money::format($row->spent) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="table-card">
    <div class="table-top"><h4>أكتر مشروع ربحت منه</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>#</th><th>المشروع</th><th class="num">الربح</th></tr></thead>
        <tbody>
          @forelse($topProjectsByProfit as $i => $row)
            <tr>
              <td><span style="width:22px;height:22px;border-radius:50%;background:{{ ['#0f8a5f','#16b87e','#4ade80','#86efac','#bbf7d0'][$i % 5] }};color:#fff;display:grid;place-items:center;font-size:10px;font-weight:700">{{ $i+1 }}</span></td>
              <td>{{ $row->name }}</td>
              <td class="num" style="font-weight:700;color:{{ $row->profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($row->profit) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="table-card">
    <div class="table-top"><h4>أكتر مشروع أخد خصم</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>#</th><th>المشروع</th><th class="num">الخصم</th></tr></thead>
        <tbody>
          @forelse($topProjectsByDiscount as $i => $row)
            <tr>
              <td><span style="width:22px;height:22px;border-radius:50%;background:{{ ['#d97706','#f59e0b','#fbbf24','#fcd34d','#fde68a'][$i % 5] }};color:#fff;display:grid;place-items:center;font-size:10px;font-weight:700">{{ $i+1 }}</span></td>
              <td>{{ $row->name }}</td>
              <td class="num" style="font-weight:700;color:var(--amber)">{{ \App\Support\Money::format($row->discount) }}</td>
            </tr>
          @empty
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="table-card">
    <div class="table-top"><h4>أكثر مسوق حصل على عمولات</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>#</th><th>المسوق</th><th class="num">إجمالي العمولات</th></tr></thead>
        <tbody>
          @forelse($topMarketers as $i => $row)
            <tr>
              <td><span style="width:22px;height:22px;border-radius:50%;background:{{ ['#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe','#ede9fe'][$i % 5] }};color:#fff;display:grid;place-items:center;font-size:10px;font-weight:700">{{ $i+1 }}</span></td>
              <td>{{ $row->name }}</td>
              <td class="num" style="font-weight:700;color:var(--pos)">{{ \App\Support\Money::format($row->total_paid) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- ═══ تفاصيل وتحليل المصروفات (عامة + مشاريع + مجمّع) ═══ --}}
<div style="margin-bottom:28px;">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
    <div style="display:flex; align-items:center; gap:10px;">
      <div style="width:36px; height:36px; border-radius:10px; background:#fef2f2; display:grid; place-items:center;">
        <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px;"><use href="#i-receipt"/></svg>
      </div>
      <div>
        <h3 style="margin:0; font-size:1.2rem; color:var(--ink);">تفاصيل وتحليل المصروفات العامة (الشركة والمشاريع)</h3>
        <p class="muted" style="margin:2px 0 0; font-size:12.5px;">توزيع ومقارنة المصروفات العامة للشركة والمصروفات المسجلة داخل المشاريع والإجمالي المجمع لكل بند</p>
      </div>
    </div>
    <div style="display:flex; gap:8px;">
      <a href="{{ route('general_expenses.index') }}" class="btn ghost sm" style="font-size:12px; padding:6px 12px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-clipboard"/></svg>
        سجل المصروفات العامة
      </a>
    </div>
  </div>

  {{-- 3 KPI Cards --}}
  <div class="grid cols-3" style="gap:16px; margin-bottom:20px;">
    {{-- Card 1: Company General Expenses --}}
    <div class="card stat" style="background:#eff6ff; border:1px solid #bfdbfe; padding:18px;">
      <div class="top">
        <span class="label" style="font-size:13px; font-weight:700; color:#1e40af;">مصروفات عامة للشركة (خارج المشاريع)</span>
        <span class="ic" style="background:#dbeafe; color:#1d4ed8;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-building"/></svg></span>
      </div>
      <div class="val tnum" style="color:#1d4ed8; font-size:24px; margin-top:8px;">{{ \App\Support\Money::format($totalCompanyExpenses ?? 0) }} <small style="font-size:13px;">ج.م</small></div>
      <div class="note" style="margin-top:6px; color:#3b82f6; font-weight:600;">عدد الحركات: {{ $countCompanyExpenses ?? 0 }} حركة</div>
    </div>

    {{-- Card 2: Project General Expenses --}}
    <div class="card stat" style="background:#fffbeb; border:1px solid #fde68a; padding:18px;">
      <div class="top">
        <span class="label" style="font-size:13px; font-weight:700; color:#92400e;">مصروفات عامة داخل المشاريع</span>
        <span class="ic" style="background:#fef3c7; color:#d97706;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-tool"/></svg></span>
      </div>
      <div class="val tnum" style="color:#b45309; font-size:24px; margin-top:8px;">{{ \App\Support\Money::format($totalProjectExpenses ?? 0) }} <small style="font-size:13px;">ج.م</small></div>
      <div class="note" style="margin-top:6px; color:#d97706; font-weight:600;">عدد الحركات: {{ $countProjectExpenses ?? 0 }} حركة</div>
    </div>

    {{-- Card 3: Combined Total --}}
    <div class="card stat" style="background:#fef2f2; border:1.5px solid #fca5a5; padding:18px;">
      <div class="top">
        <span class="label" style="font-size:13px; font-weight:800; color:#991b1b;">الإجمالي الكلي المجمّع (عامة + مشاريع)</span>
        <span class="ic" style="background:#fee2e2; color:#dc2626;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-receipt"/></svg></span>
      </div>
      <div class="val tnum" style="color:#dc2626; font-size:24px; margin-top:8px;">{{ \App\Support\Money::format($totalAllExpenses ?? 0) }} <small style="font-size:13px;">ج.م</small></div>
      <div class="note" style="margin-top:6px; color:#ef4444; font-weight:600;">إجمالي {{ $countAllExpenses ?? 0 }} حركة صرف في الفترة</div>
    </div>
  </div>

  {{-- Comprehensive Comparison Table: Combined by Item --}}
  <div class="table-card" style="padding:20px; margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
      <div>
        <h4 style="margin:0; font-size:15px; font-weight:800;">جدول تفصيلي: إجمالي الصرف على كل بند (المشاريع + العامة)</h4>
        <p class="muted" style="margin:2px 0 0; font-size:12px;">يوضح نصيب الشركة ونصيب المشاريع والإجمالي المجمع لكل نوع مصروف (فطار، إيجارات، انتقالات...)</p>
      </div>
      <div style="display:flex; gap:6px;" id="expViewBtns">
        <button type="button" class="btn sm active" onclick="switchExpView('all', this)" id="btn-exp-all" style="font-size:12px; padding:4px 10px;">الكل (مجمّع)</button>
        <button type="button" class="btn ghost sm" onclick="switchExpView('company', this)" id="btn-exp-company" style="font-size:12px; padding:4px 10px;">عامة للشركة فقط</button>
        <button type="button" class="btn ghost sm" onclick="switchExpView('project', this)" id="btn-exp-project" style="font-size:12px; padding:4px 10px;">مشاريع فقط</button>
      </div>
    </div>

    {{-- 1. Combined Table View --}}
    <div id="view-exp-all" class="exp-view-panel table-scroll">
      <table>
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th>البيان / نوع المصروف</th>
            <th class="num" style="color:#1d4ed8;">عامة للشركة</th>
            <th class="num" style="color:#b45309;">داخل المشاريع</th>
            <th class="num" style="color:#dc2626; font-size:14px;">الإجمالي الكلي (المجمّع)</th>
            <th class="num">النسبة من الإجمالي</th>
            <th style="min-width:140px;">نسبة (عامة / مشاريع)</th>
          </tr>
        </thead>
        <tbody>
          @forelse($combinedExpensesDistribution as $idx => $row)
            @php
              $compPct = $row->total_amount > 0 ? round(($row->company_amount / $row->total_amount) * 100) : 0;
              $projPct = $row->total_amount > 0 ? round(($row->project_amount / $row->total_amount) * 100) : 0;
            @endphp
            <tr>
              <td>
                <span style="width:22px;height:22px;border-radius:50%;background:#fee2e2;color:#dc2626;display:grid;place-items:center;font-size:10px;font-weight:700;">{{ $idx + 1 }}</span>
              </td>
              <td>
                <strong style="font-size:14px; color:var(--ink);">{{ $row->description }}</strong>
                <span class="muted" style="font-size:11px; margin-inline-start:4px;">({{ $row->count }} {{ $row->count === 1 ? 'حركة' : 'حركات' }})</span>
              </td>
              <td class="num" style="color:#1d4ed8; font-weight:600;">
                {{ $row->company_amount > 0 ? \App\Support\Money::format($row->company_amount) : '—' }}
              </td>
              <td class="num" style="color:#b45309; font-weight:600;">
                {{ $row->project_amount > 0 ? \App\Support\Money::format($row->project_amount) : '—' }}
              </td>
              <td class="num" style="color:#dc2626; font-weight:800; font-size:14px;">
                {{ \App\Support\Money::format($row->total_amount) }} ج.م
              </td>
              <td class="num">
                <span class="tag gray" style="font-weight:700;">{{ $row->pct }}%</span>
              </td>
              <td>
                <div style="display:flex; height:8px; border-radius:4px; overflow:hidden; background:var(--line); margin-bottom:4px;">
                  @if($compPct > 0)
                    <div style="width:{{ $compPct }}%; background:#3b82f6;" title="عامة: {{ $compPct }}%"></div>
                  @endif
                  @if($projPct > 0)
                    <div style="width:{{ $projPct }}%; background:#f59e0b;" title="مشاريع: {{ $projPct }}%"></div>
                  @endif
                </div>
                <div style="display:flex; justify-content:space-between; font-size:10px; color:var(--ink-3);">
                  <span style="color:#3b82f6;">عامة: {{ $compPct }}%</span>
                  <span style="color:#f59e0b;">مشاريع: {{ $projPct }}%</span>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="muted" style="text-align:center;padding:24px;">لا توجد مصروفات مسجلة في هذه الفترة</td></tr>
          @endforelse
        </tbody>
        @if($combinedExpensesDistribution->count() > 0)
          <tfoot>
            <tr style="background:rgba(239,68,68,0.05); font-weight:bold;">
              <td colspan="2" style="color:var(--neg);">الإجمالي الكلي لكافة المصروفات</td>
              <td class="num" style="color:#1d4ed8;">{{ \App\Support\Money::format($totalCompanyExpenses ?? 0) }} ج.م</td>
              <td class="num" style="color:#b45309;">{{ \App\Support\Money::format($totalProjectExpenses ?? 0) }} ج.م</td>
              <td class="num" style="color:#dc2626; font-size:15px;">{{ \App\Support\Money::format($totalAllExpenses ?? 0) }} ج.م</td>
              <td class="num" style="color:var(--neg);">100%</td>
              <td></td>
            </tr>
          </tfoot>
        @endif
      </table>
    </div>

    {{-- 2. Company Only View --}}
    <div id="view-exp-company" class="exp-view-panel table-scroll" style="display:none;">
      <table>
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th>البيان (مصروفات عامة للشركة)</th>
            <th class="num">عدد الحركات</th>
            <th class="num" style="color:#1d4ed8;">المبلغ</th>
            <th class="num">النسبة من المصروفات العامة</th>
          </tr>
        </thead>
        <tbody>
          @forelse($topCompanyExpenses as $idx => $row)
            <tr>
              <td><span style="width:22px;height:22px;border-radius:50%;background:#dbeafe;color:#1d4ed8;display:grid;place-items:center;font-size:10px;font-weight:700;">{{ $idx + 1 }}</span></td>
              <td><strong style="color:var(--ink);">{{ $row->description }}</strong></td>
              <td class="num">{{ $row->count }}</td>
              <td class="num" style="color:#1d4ed8; font-weight:700;">{{ \App\Support\Money::format($row->amount) }} ج.م</td>
              <td class="num"><span class="tag gray">{{ $row->pct }}%</span></td>
            </tr>
          @empty
            <tr><td colspan="5" class="muted" style="text-align:center;padding:20px;">لا توجد مصروفات عامة للشركة في هذه الفترة</td></tr>
          @endforelse
        </tbody>
        @if($topCompanyExpenses->count() > 0)
          <tfoot>
            <tr style="background:rgba(59,130,246,0.05); font-weight:bold;">
              <td colspan="3" style="color:#1d4ed8;">إجمالي المصروفات العامة للشركة</td>
              <td class="num" style="color:#1d4ed8; font-size:15px;">{{ \App\Support\Money::format($totalCompanyExpenses ?? 0) }} ج.م</td>
              <td class="num" style="color:#1d4ed8;">100%</td>
            </tr>
          </tfoot>
        @endif
      </table>
    </div>

    {{-- 3. Projects Only View --}}
    <div id="view-exp-project" class="exp-view-panel table-scroll" style="display:none;">
      <table>
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th>البيان (مصروفات داخل المشاريع)</th>
            <th class="num">عدد الحركات</th>
            <th class="num" style="color:#b45309;">المبلغ</th>
            <th class="num">النسبة من مصروفات المشاريع</th>
          </tr>
        </thead>
        <tbody>
          @forelse($topProjectExpenses as $idx => $row)
            <tr>
              <td><span style="width:22px;height:22px;border-radius:50%;background:#fef3c7;color:#b45309;display:grid;place-items:center;font-size:10px;font-weight:700;">{{ $idx + 1 }}</span></td>
              <td><strong style="color:var(--ink);">{{ $row->description }}</strong></td>
              <td class="num">{{ $row->count }}</td>
              <td class="num" style="color:#b45309; font-weight:700;">{{ \App\Support\Money::format($row->amount) }} ج.م</td>
              <td class="num"><span class="tag gray">{{ $row->pct }}%</span></td>
            </tr>
          @empty
            <tr><td colspan="5" class="muted" style="text-align:center;padding:20px;">لا توجد مصروفات مشاريع مسجلة في هذه الفترة</td></tr>
          @endforelse
        </tbody>
        @if($topProjectExpenses->count() > 0)
          <tfoot>
            <tr style="background:rgba(245,158,11,0.05); font-weight:bold;">
              <td colspan="3" style="color:#b45309;">إجمالي المصروفات العامة للمشاريع</td>
              <td class="num" style="color:#b45309; font-size:15px;">{{ \App\Support\Money::format($totalProjectExpenses ?? 0) }} ج.م</td>
              <td class="num" style="color:#b45309;">100%</td>
            </tr>
          </tfoot>
        @endif
      </table>
    </div>

  </div>
</div>

<script>
function switchExpView(viewName, btn) {
  document.querySelectorAll('.exp-view-panel').forEach(el => el.style.display = 'none');
  const target = document.getElementById('view-exp-' + viewName);
  if (target) target.style.display = '';
  
  document.querySelectorAll('#expViewBtns .btn').forEach(b => {
    b.classList.remove('active');
    b.classList.add('ghost');
  });
  btn.classList.add('active');
  btn.classList.remove('ghost');
}
</script>

{{-- Top bands --}}
<div class="grid cols-2" style="margin-bottom:24px">
  <div class="table-card">
    <div class="table-top"><h4>أكتر بند صرفت منه (مجمّع)</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>#</th><th>البند</th><th class="num">المرات</th><th class="num">المصروف</th></tr></thead>
        <tbody>
          @forelse($topBandNamesBySpend as $i => $row)
            <tr>
              <td><span style="width:22px;height:22px;border-radius:50%;background:{{ ['#ea580c','#f97316','#fb923c','#fdba74','#fed7aa'][$i % 5] }};color:#fff;display:grid;place-items:center;font-size:10px;font-weight:700">{{ $i+1 }}</span></td>
              <td>{{ $row->name }}</td><td class="num">{{ $row->count }}</td><td class="num" style="font-weight:700">{{ \App\Support\Money::format($row->spent) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="table-card">
    <div class="table-top"><h4>أكتر بند كسبت منه (مجمّع)</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>#</th><th>البند</th><th class="num">المرات</th><th class="num">الربح</th></tr></thead>
        <tbody>
          @forelse($topBandNamesByProfit as $i => $row)
            <tr>
              <td><span style="width:22px;height:22px;border-radius:50%;background:{{ ['#0f8a5f','#16b87e','#4ade80','#86efac','#bbf7d0'][$i % 5] }};color:#fff;display:grid;place-items:center;font-size:10px;font-weight:700">{{ $i+1 }}</span></td>
              <td>{{ $row->name }}</td><td class="num">{{ $row->count }}</td><td class="num" style="font-weight:700;color:{{ $row->profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($row->profit) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Top individual bands --}}
<div class="grid cols-2" style="margin-bottom:24px">
  <div class="table-card">
    <div class="table-top"><h4>أعلى بند فردي صرفًا</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>البند</th><th>المشروع</th><th class="num">المصروف</th></tr></thead>
        <tbody>
          @forelse($topBandInstancesBySpend as $row)
            <tr><td>{{ $row->name }}</td><td class="muted">{{ $row->project }}</td><td class="num">{{ \App\Support\Money::format($row->spent) }}</td></tr>
          @empty
            <tr><td colspan="3" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="table-card">
    <div class="table-top"><h4>أعلى بند فردي ربحًا</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>البند</th><th>المشروع</th><th class="num">الربح</th></tr></thead>
        <tbody>
          @forelse($topBandInstancesByProfit as $row)
            <tr><td>{{ $row->name }}</td><td class="muted">{{ $row->project }}</td><td class="num" style="color:{{ $row->profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($row->profit) }}</td></tr>
          @empty
            <tr><td colspan="3" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Top technicians --}}
<div class="table-card" style="margin-bottom:24px">
  <div class="table-top"><h4>أكتر فني اتعاملت معاه</h4></div>
  <div class="table-scroll">
    <table>
      <thead><tr><th>#</th><th>الفني</th><th class="num">عدد مرات العمل</th><th class="num">إجمالي المدفوع</th></tr></thead>
      <tbody>
        @forelse($technicians as $i => $t)
          <tr>
            <td><span style="width:22px;height:22px;border-radius:50%;background:{{ ['#b8842a','#d4a13d','#e8c06a','#f0d08e','#f8e8c0'][$i % 5] }};color:#fff;display:grid;place-items:center;font-size:10px;font-weight:700">{{ $i+1 }}</span></td>
            <td><strong>{{ $t->name }}</strong></td><td class="num">{{ $t->count }}</td><td class="num">{{ \App\Support\Money::format($t->total) }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>



@php
  $months = array_keys($cashFlow);
  $inData  = array_map(fn($m) => $cashFlow[$m]['in']  ?? 0, $months);
  $outData = array_map(fn($m) => $cashFlow[$m]['out'] ?? 0, $months);
  $topProjectLabels = $topProjectsByProfit->pluck('name');
  $topProjectValues = $topProjectsByProfit->pluck('profit');
  $topBandLabels    = $topBandNamesBySpend->take(6)->pluck('name');
  $topBandValues    = $topBandNamesBySpend->take(6)->pluck('spent');
  $purchasedLabels  = $topPurchasedMaterials->pluck('item')->values()->toArray();
  $purchasedValues  = $topPurchasedMaterials->pluck('total_cost')->values()->toArray();
  $returnedLabels   = $topReturnedMaterials->pluck('item')->values()->toArray();
  $returnedValues   = $topReturnedMaterials->pluck('returned_value')->values()->toArray();
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
Chart.defaults.font.family = "'IBM Plex Sans Arabic', system-ui, sans-serif";
Chart.defaults.font.size   = 11;
const fmt = v => Number(v).toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0});

// ── 1. Cash flow — Area chart (gradient fill) ─────────────────────────
(function(){
  const ctx = document.getElementById('cashFlowChart').getContext('2d');
  const gIn  = ctx.createLinearGradient(0,0,0,160);
  gIn.addColorStop(0,'rgba(15,138,95,.35)');
  gIn.addColorStop(1,'rgba(15,138,95,.02)');
  const gOut = ctx.createLinearGradient(0,0,0,160);
  gOut.addColorStop(0,'rgba(192,57,43,.30)');
  gOut.addColorStop(1,'rgba(192,57,43,.02)');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: @json($months),
      datasets: [
        { label:'وارد',  data:@json($inData),  borderColor:'#0f8a5f', backgroundColor:gIn,  fill:true, tension:.4, pointRadius:3, pointBackgroundColor:'#0f8a5f', borderWidth:2 },
        { label:'صادر',  data:@json($outData), borderColor:'#c0392b', backgroundColor:gOut, fill:true, tension:.4, pointRadius:3, pointBackgroundColor:'#c0392b', borderWidth:2 },
      ]
    },
    options:{
      responsive:true,
      plugins:{ legend:{ position:'bottom', labels:{ usePointStyle:true, boxWidth:8, padding:10, font:{size:10} } }, tooltip:{ callbacks:{ label: ctx => ' '+fmt(ctx.raw)+' ج.م' } } },
      scales:{
        x:{ grid:{display:false}, ticks:{font:{size:9}} },
        y:{ grid:{color:'rgba(0,0,0,.05)'}, ticks:{callback:v=>fmt(v), font:{size:9}} }
      }
    }
  });
})();

// ── 2. Doughnut — spend by band ───────────────────────────────────────
const bandLabels = @json($topBandLabels);
const bandVals   = @json($topBandValues);
const donutColors = ['#4f46e5','#0891b2','#0f8a5f','#b8842a','#c0392b','#7c3aed'];
new Chart(document.getElementById('spendDonut'), {
  type: 'doughnut',
  data: { labels:bandLabels, datasets:[{ data:bandVals, backgroundColor:donutColors, borderWidth:2, borderColor:'#fff', hoverOffset:6 }] },
  options:{
    cutout:'68%',
    plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: ctx=>' '+ctx.label+': '+fmt(ctx.raw)+' ج.م' } } }
  }
});
const legend = document.getElementById('donut-legend');
bandLabels.forEach((lbl,i)=>{
  const d=document.createElement('div');
  d.style.cssText='display:flex;align-items:center;gap:6px;font-size:10px';
  d.innerHTML=`<span style="width:8px;height:8px;border-radius:2px;background:${donutColors[i%donutColors.length]};flex-shrink:0"></span><span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${lbl}</span><strong>${fmt(bandVals[i])}</strong>`;
  legend.appendChild(d);
});

// ── 3. Polar Area — top bands ─────────────────────────────────────────
new Chart(document.getElementById('topBandsChart'), {
  type: 'polarArea',
  data: {
    labels: @json($topBandLabels),
    datasets:[{ data:@json($topBandValues),
      backgroundColor:['rgba(234,88,12,.75)','rgba(249,115,22,.7)','rgba(251,146,60,.65)','rgba(253,186,116,.6)','rgba(254,215,170,.55)','rgba(255,237,213,.5)'],
      borderWidth:0
    }]
  },
  options:{
    responsive:true,
    plugins:{ legend:{ position:'bottom', labels:{ font:{size:9}, padding:6, boxWidth:8 } }, tooltip:{ callbacks:{ label: ctx=>' '+ctx.label+': '+fmt(ctx.raw)+' ج.م' } } },
    scales:{ r:{ ticks:{display:false}, grid:{color:'rgba(0,0,0,.06)'} } }
  }
});

// ── 4. Horizontal bar — top projects profit ───────────────────────────
new Chart(document.getElementById('topProjectsChart'), {
  type: 'bar',
  data: {
    labels: @json($topProjectLabels),
    datasets:[{
      label:'ربح',
      data: @json($topProjectValues),
      backgroundColor:['rgba(15,138,95,.85)','rgba(22,184,126,.8)','rgba(74,222,128,.75)','rgba(134,239,172,.7)','rgba(187,247,208,.65)'],
      borderRadius:5, borderSkipped:false,
    }]
  },
  options:{
    indexAxis:'y', responsive:true,
    plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: ctx=>' '+fmt(ctx.raw)+' ج.م' } } },
    scales:{
      x:{ grid:{color:'rgba(0,0,0,.05)'}, ticks:{callback:v=>fmt(v), font:{size:9}} },
      y:{ grid:{display:false}, ticks:{font:{size:9}} }
    }
  }
});

// ── 5. Top purchased — horizontal bar with gradient colors ───────────
if (document.getElementById('topPurchasedChart')) {
  new Chart(document.getElementById('topPurchasedChart'), {
    type: 'bar',
    data: {
      labels: @json($purchasedLabels),
      datasets:[{
        label:'التكلفة',
        data: @json($purchasedValues),
        backgroundColor: (() => {
          const vals = @json($purchasedValues);
          if(!vals || vals.length === 0) return [];
          const max = Math.max(...vals);
          return vals.map(v => `rgba(79,70,229,${0.35 + 0.65*(max > 0 ? v/max : 1)})`);
        })(),
        borderRadius:5, borderSkipped:false,
      }]
    },
    options:{
      indexAxis:'y', responsive:true,
      plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: ctx=>' '+fmt(ctx.raw)+' ج.م' } } },
      scales:{
        x:{ grid:{color:'rgba(0,0,0,.05)'}, ticks:{callback:v=>fmt(v), font:{size:9}} },
        y:{ grid:{display:false}, ticks:{font:{size:9}} }
      }
    }
  });
}

// ── 6. Top returned — horizontal bar red gradient ────────────────────
if (document.getElementById('topReturnedChart')) {
  new Chart(document.getElementById('topReturnedChart'), {
    type: 'bar',
    data: {
      labels: @json($returnedLabels),
      datasets:[{
        label:'قيمة المرتجع',
        data: @json($returnedValues),
        backgroundColor: (() => {
          const vals = @json($returnedValues);
          const max  = Math.max(...vals, 1);
          return vals.map(v => `rgba(192,57,43,${0.35 + 0.65*(v/max)})`);
        })(),
        borderRadius:5, borderSkipped:false,
      }]
    },
    options:{
      indexAxis:'y', responsive:true,
      plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: ctx=>' '+fmt(ctx.raw)+' ج.م' } } },
      scales:{
        x:{ grid:{color:'rgba(0,0,0,.05)'}, ticks:{callback:v=>fmt(v), font:{size:9}} },
        y:{ grid:{display:false}, ticks:{font:{size:9}} }
      }
    }
  });
}
</script>

@endpush
@endsection
