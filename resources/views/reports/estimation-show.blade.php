@extends('layouts.app')
@section('title', 'تقدير تكلفة — ' . $project->name)
@section('page-title', 'تقدير تكلفة مشروع جديد')

@section('content')
<div class="page-head no-print">
  <div>
    <h3>{{ $project->name }}</h3>
    <p>
      {{ $project->client->name ?? '—' }}
      @if($area) — {{ rtrim(rtrim($project->area, '0'), '.') }} م² @endif
      — مرجع تقدير لأي مشروع جديد بنفس المساحة
    </p>
  </div>
  <div style="display:flex;gap:8px">
    <button onclick="window.print()" class="btn ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
      طباعة
    </button>
    <a href="{{ route('reports.estimation.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

{{-- Grand totals --}}
<div class="grid cols-4" style="margin-bottom:20px">
  <div class="vstat vstat-blue">
    <div class="top"><span class="label">إجمالي تكلفة الخامات</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg></span>
    </div>
    <div class="val tnum">{{ number_format($totalMaterialCost) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-amber">
    <div class="top"><span class="label">إجمالي المصنعية</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg></span>
    </div>
    <div class="val tnum">{{ number_format($totalLaborCost) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-navy">
    <div class="top"><span class="label">التكلفة الإجمالية</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg></span>
    </div>
    <div class="val tnum">{{ number_format($grandTotal) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-teal">
    <div class="top"><span class="label">تكلفة المتر الواحد</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span>
    </div>
    <div class="val tnum">{{ $grandPerSqm !== null ? number_format($grandPerSqm, 1) : '—' }} <small>{{ $grandPerSqm !== null ? 'ج.م/م²' : '' }}</small></div>
    @if($grandPerSqm === null)<div class="note">حدّد مساحة المشروع لحساب التكلفة بالمتر</div>@endif
  </div>
</div>

{{-- Per band breakdown --}}
@foreach($bands as $row)
  <div class="table-card" style="margin-bottom:18px">
    <div class="table-top">
      <h4>
        {{ $row->band->name }}
        <span class="tag {{ $row->band->status === 'done' ? 'green' : ($row->band->status === 'active' ? 'blue' : 'gray') }}" style="margin-inline-start:8px">
          {{ $row->band->status === 'done' ? 'مكتمل' : ($row->band->status === 'active' ? 'جاري' : 'معلق') }}
        </span>
      </h4>
      <div style="display:flex;gap:18px;align-items:center">
        <div style="text-align:center">
          <div class="muted" style="font-size:11px">تكلفة البند</div>
          <div style="font-weight:700">{{ number_format($row->total_cost) }} <small class="muted">ج.م</small></div>
        </div>
        @if($row->per_sqm !== null)
          <div style="text-align:center">
            <div class="muted" style="font-size:11px">لكل م²</div>
            <div style="font-weight:700;color:var(--accent)">{{ number_format($row->per_sqm, 1) }} <small class="muted">ج.م</small></div>
          </div>
        @endif
      </div>
    </div>

    @if($row->materials->count())
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>الخامة</th>
              <th class="num">الكمية</th>
              <th>الوحدة</th>
              <th class="num">التكلفة</th>
            </tr>
          </thead>
          <tbody>
            @foreach($row->materials as $m)
              <tr>
                <td><strong>{{ $m->item }}</strong></td>
                <td class="num">{{ number_format($m->qty, 1) }}</td>
                <td class="muted">{{ $m->unit }}</td>
                <td class="num">{{ number_format($m->cost) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3">إجمالي تكلفة الخامات</td>
              <td class="num">{{ number_format($row->material_cost) }}</td>
            </tr>
            <tr>
              <td colspan="3">المصنعية</td>
              <td class="num">{{ number_format($row->labor_cost) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    @else
      <div class="empty-state" style="padding:24px">
        <p class="muted">لا توجد خامات مسجّلة على هذا البند — المصنعية فقط: {{ number_format($row->labor_cost) }} ج.م</p>
      </div>
    @endif
  </div>
@endforeach

@if($bands->isEmpty())
  <div class="empty-state">
    <h4>لا توجد بنود في هذا المشروع</h4>
  </div>
@endif

@endsection
