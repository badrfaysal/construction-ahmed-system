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

{{-- Summary --}}
<div class="grid cols-3" style="margin-bottom:20px">
  <div class="card stat">
    <div class="top"><span class="label">إجمالي الربح</span><span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg></span></div>
    <div class="val tnum" style="color:{{ $totalProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ number_format($totalProfit) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المصروف</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span></div>
    <div class="val tnum">{{ number_format($totalSpent) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المحصّل</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span></div>
    <div class="val tnum">{{ number_format($totalCollected) }} <small>ج.م</small></div>
  </div>
</div>

{{-- Charts --}}
<div class="grid cols-2" style="margin-bottom:24px">
  <div class="card card-pad">
    <div class="section-label" style="margin-top:0">الوارد والصادر الشهري</div>
    <canvas id="cashFlowChart" height="220"></canvas>
  </div>
  <div class="card card-pad">
    <div class="section-label" style="margin-top:0">أعلى 5 مشاريع ربحًا</div>
    <canvas id="topProjectsChart" height="220"></canvas>
  </div>
</div>

{{-- Top projects --}}
<div class="grid cols-2" style="margin-bottom:24px">
  <div class="table-card">
    <div class="table-top"><h4>أكتر مشروع صرفت فيه</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>المشروع</th><th class="num">المصروف</th></tr></thead>
        <tbody>
          @forelse($topProjectsBySpend as $row)
            <tr><td>{{ $row->name }}</td><td class="num">{{ number_format($row->spent) }}</td></tr>
          @empty
            <tr><td colspan="2" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="table-card">
    <div class="table-top"><h4>أكتر مشروع ربحت منه</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>المشروع</th><th class="num">الربح</th></tr></thead>
        <tbody>
          @forelse($topProjectsByProfit as $row)
            <tr><td>{{ $row->name }}</td><td class="num" style="color:{{ $row->profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ number_format($row->profit) }}</td></tr>
          @empty
            <tr><td colspan="2" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Top bands by name across all projects --}}
<div class="grid cols-2" style="margin-bottom:24px">
  <div class="table-card">
    <div class="table-top"><h4>أكتر بند صرفت منه (مجمّع بالاسم)</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>البند</th><th class="num">عدد المرات</th><th class="num">المصروف</th></tr></thead>
        <tbody>
          @forelse($topBandNamesBySpend as $row)
            <tr><td>{{ $row->name }}</td><td class="num">{{ $row->count }}</td><td class="num">{{ number_format($row->spent) }}</td></tr>
          @empty
            <tr><td colspan="3" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="table-card">
    <div class="table-top"><h4>أكتر بند كسبت منه (مجمّع بالاسم)</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>البند</th><th class="num">عدد المرات</th><th class="num">الربح</th></tr></thead>
        <tbody>
          @forelse($topBandNamesByProfit as $row)
            <tr><td>{{ $row->name }}</td><td class="num">{{ $row->count }}</td><td class="num" style="color:{{ $row->profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ number_format($row->profit) }}</td></tr>
          @empty
            <tr><td colspan="3" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Top individual bands (not grouped) --}}
<div class="grid cols-2" style="margin-bottom:24px">
  <div class="table-card">
    <div class="table-top"><h4>أعلى بند فردي صرفًا</h4></div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>البند</th><th>المشروع</th><th class="num">المصروف</th></tr></thead>
        <tbody>
          @forelse($topBandInstancesBySpend as $row)
            <tr><td>{{ $row->name }}</td><td class="muted">{{ $row->project }}</td><td class="num">{{ number_format($row->spent) }}</td></tr>
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
            <tr><td>{{ $row->name }}</td><td class="muted">{{ $row->project }}</td><td class="num" style="color:{{ $row->profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ number_format($row->profit) }}</td></tr>
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
      <thead><tr><th>الفني</th><th class="num">عدد مرات العمل</th><th class="num">إجمالي المدفوع</th></tr></thead>
      <tbody>
        @forelse($technicians as $t)
          <tr><td><strong>{{ $t->name }}</strong></td><td class="num">{{ $t->count }}</td><td class="num">{{ number_format($t->total) }}</td></tr>
        @empty
          <tr><td colspan="3" class="muted" style="text-align:center;padding:16px">لا توجد بيانات</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@php
  $months = array_keys($cashFlow);
  $inData = array_map(fn($m) => $cashFlow[$m]['in'] ?? 0, $months);
  $outData = array_map(fn($m) => $cashFlow[$m]['out'] ?? 0, $months);
  $topProjectLabels = $topProjectsByProfit->pluck('name');
  $topProjectValues = $topProjectsByProfit->pluck('profit');
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
new Chart(document.getElementById('cashFlowChart'), {
  type: 'bar',
  data: {
    labels: @json($months),
    datasets: [
      { label: 'وارد', data: @json($inData), backgroundColor: '#0f8a5f' },
      { label: 'صادر', data: @json($outData), backgroundColor: '#c0392b' },
    ]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('topProjectsChart'), {
  type: 'bar',
  data: {
    labels: @json($topProjectLabels),
    datasets: [{ label: 'الربح', data: @json($topProjectValues), backgroundColor: '#1f5fd6' }]
  },
  options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
});
</script>
@endpush
@endsection
