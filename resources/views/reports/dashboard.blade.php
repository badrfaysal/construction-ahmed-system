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
<div class="grid cols-4" style="margin-bottom:20px">
  <div class="card stat">
    <div class="top"><span class="label">إجمالي الربح</span><span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg></span></div>
    <div class="val tnum" style="color:{{ $totalProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totalProfit) }} <small>ج.م</small></div>
    <div class="note" style="margin-top: 8px; font-size: 11.5px; border-top: 1px solid var(--border); padding-top: 8px;">
      <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
        <span style="color:#3b82f6">ربح تجاري:</span> 
        <strong style="color:{{ $totalTradeProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totalTradeProfit) }}</strong>
      </div>
      <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
        <span style="color:#ec4899">نسبة إشراف:</span> 
        <strong style="color:{{ $totalPercentageProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totalPercentageProfit) }}</strong>
      </div>
      <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
        <span style="color:#10b981">أرباح تقسيط:</span> 
        <strong style="color:{{ $totalInstallmentProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totalInstallmentProfit) }}</strong>
      </div>
      @if($totalDiscounts > 0)
      <div style="display:flex; justify-content:space-between; margin-bottom: 2px; padding-top: 4px; margin-top: 4px; border-top: 1px dashed var(--border);">
        <span style="color:var(--amber)">خصومات للعملاء (تُطرح):</span> 
        <strong style="color:var(--neg)">-{{ \App\Support\Money::format($totalDiscounts) }}</strong>
      </div>
      @endif
      @if(isset($totalMarketerCommissions) && $totalMarketerCommissions > 0)
      <div style="display:flex; justify-content:space-between;">
        <span style="color:#8b5cf6">عمولات مسوقين (تُطرح):</span> 
        <strong style="color:var(--neg)">-{{ \App\Support\Money::format($totalMarketerCommissions) }}</strong>
      </div>
      @endif
    </div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المصروف</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span></div>
    <div class="val tnum">{{ \App\Support\Money::format($totalSpent) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المحصّل</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span></div>
    <div class="val tnum">{{ \App\Support\Money::format($totalCollected) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">الخصومات الممنوحة</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-percent"/></svg></span></div>
    <div class="val tnum" style="color:var(--amber)">{{ \App\Support\Money::format($totalDiscounts) }} <small>ج.م</small></div>
    @if($topDiscountProject)
      <div class="note">أعلى خصم: <a href="{{ route('projects.show', $topDiscountProject->id) }}">{{ $topDiscountProject->name }}</a> ({{ \App\Support\Money::format($topDiscountProject->totalDiscount()) }})</div>
    @endif
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي عمولات المسوقين</span><span class="ic" style="color:#8b5cf6;background:#f3e8ff"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg></span></div>
    <div class="val tnum" style="color:#8b5cf6">{{ \App\Support\Money::format($totalMarketerCommissions ?? 0) }} <small>ج.م</small></div>
  </div>
</div>

{{-- ═══ Charts Grid: 4 compact cards ═══ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">

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
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">

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
