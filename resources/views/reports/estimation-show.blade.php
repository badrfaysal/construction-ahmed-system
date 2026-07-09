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
    <div class="val tnum">{{ \App\Support\Money::format($totalMaterialCost) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-amber">
    <div class="top"><span class="label">إجمالي المصنعية</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalLaborCost) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-navy">
    <div class="top"><span class="label">التكلفة الإجمالية</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($grandTotal) }} <small>ج.م</small></div>
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
          <div class="muted" style="font-size:11px">خامات</div>
          <div style="font-weight:600;color:var(--accent)">{{ \App\Support\Money::format($row->material_cost) }} <small class="muted">ج.م</small></div>
        </div>
        <div style="text-align:center">
          <div class="muted" style="font-size:11px">مصنعية</div>
          <div style="font-weight:600;color:var(--warn)">{{ \App\Support\Money::format($row->labor_cost) }} <small class="muted">ج.م</small></div>
        </div>
        <div style="text-align:center">
          <div class="muted" style="font-size:11px">الإجمالي</div>
          <div style="font-weight:700">{{ \App\Support\Money::format($row->total_cost) }} <small class="muted">ج.م</small></div>
        </div>
        @if($row->per_sqm !== null)
          <div style="text-align:center">
            <div class="muted" style="font-size:11px">لكل م²</div>
            <div style="font-weight:700;color:var(--accent)">{{ number_format($row->per_sqm, 1) }} <small class="muted">ج.م</small></div>
          </div>
        @endif
      </div>
    </div>

    {{-- Materials table --}}
    @if($row->materials->count())
      <div style="padding:8px 18px 4px;font-size:12px;font-weight:600;color:var(--accent);border-top:1px solid var(--line);background:color-mix(in srgb, var(--accent) 4%, transparent)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;vertical-align:middle;margin-inline-end:4px"><use href="#i-box"/></svg>
        الخامات ({{ $row->materials->count() }} صنف)
      </div>
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
                <td class="num">{{ \App\Support\Money::format($m->cost) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="color:var(--accent)">إجمالي الخامات</td>
              <td class="num" style="color:var(--accent)">{{ \App\Support\Money::format($row->material_cost) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    @else
      <div style="padding:10px 18px;border-top:1px solid var(--line);color:var(--ink-3);font-size:13px">
        لا توجد خامات مسجّلة على هذا البند
      </div>
    @endif

    {{-- Workers table --}}
    @if($row->workers->count())
      <div style="padding:8px 18px 4px;font-size:12px;font-weight:600;color:var(--warn);border-top:2px solid var(--line);background:color-mix(in srgb, var(--warn) 4%, transparent)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;vertical-align:middle;margin-inline-end:4px"><use href="#i-hardhat"/></svg>
        الفنيين ({{ $row->workers->count() }} صنايعي)
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>الاسم</th>
              <th>نوع التعاقد</th>
              <th class="num">التفاصيل</th>
              <th class="num">التعاقد</th>
              <th class="num">المدفوع</th>
              <th class="num">المتبقي</th>
            </tr>
          </thead>
          <tbody>
            @foreach($row->workers as $w)
              <tr>
                <td><strong>{{ $w->name }}</strong></td>
                <td class="muted">{{ $w->contract_type }}</td>
                <td class="num muted" style="font-size:12px">
                  @if($w->qty !== null && $w->unit_rate !== null)
                    {{ rtrim(rtrim(number_format($w->qty, 2), '0'), '.') }} × {{ \App\Support\Money::format($w->unit_rate) }}
                  @else
                    —
                  @endif
                </td>
                <td class="num">{{ \App\Support\Money::format($w->amount) }}</td>
                <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($w->paid) }}</td>
                <td class="num" style="color:{{ $w->remaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">
                  {{ \App\Support\Money::format($w->remaining) }}
                  @if($w->remaining <= 0)<span class="tag green" style="font-size:10px;margin-inline-start:4px">مسدّد</span>@endif
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="color:var(--warn)">إجمالي المصنعية</td>
              <td class="num" style="color:var(--warn)">{{ \App\Support\Money::format($row->labor_cost) }}</td>
              <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($row->workers->sum('paid')) }}</td>
              <td class="num" style="color:{{ $row->workers->sum('remaining') > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($row->workers->sum('remaining')) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    @elseif($row->labor_cost > 0)
      <div style="padding:10px 18px;border-top:2px solid var(--line);color:var(--ink-3);font-size:13px">
        المصنعية: {{ \App\Support\Money::format($row->labor_cost) }} ج.م — لا يوجد فنيين مسجّلين بشكل تفصيلي
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
