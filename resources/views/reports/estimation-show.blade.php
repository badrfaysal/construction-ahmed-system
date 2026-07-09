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
<div class="grid cols-5" style="margin-bottom:14px">
  <div class="vstat vstat-blue">
    <div class="top"><span class="label">إجمالي الخامات</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalMaterialCost) }} <small>ج.م</small></div>
    @if($grandPerSqm !== null)<div class="note">{{ number_format($area > 0 ? $totalMaterialCost / $area : 0, 1) }} ج.م/م²</div>@endif
  </div>
  <div class="vstat vstat-red">
    <div class="top"><span class="label">إجمالي النثريات</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalPettyCost) }} <small>ج.م</small></div>
    @if($grandPerSqm !== null)<div class="note">{{ number_format($area > 0 ? $totalPettyCost / $area : 0, 1) }} ج.م/م²</div>@endif
  </div>
  <div class="vstat vstat-amber">
    <div class="top"><span class="label">إجمالي المصنعية</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalLaborCost) }} <small>ج.م</small></div>
    @if($grandPerSqm !== null)<div class="note">{{ number_format($area > 0 ? $totalLaborCost / $area : 0, 1) }} ج.م/م²</div>@endif
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

{{-- شرح تكلفة المتر: المتر بياخد كام خامات + نثريات + مصنعية --}}
@if($grandPerSqm !== null)
<div class="card" style="padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:13px">
  <strong style="color:var(--accent-ink)">المتر المربع الواحد يتكلّف:</strong>
  <span class="tag blue">خامات {{ number_format($area > 0 ? $totalMaterialCost / $area : 0, 1) }} ج.م</span>
  <span style="color:var(--ink-3)">+</span>
  <span class="tag red">نثريات {{ number_format($area > 0 ? $totalPettyCost / $area : 0, 1) }} ج.م</span>
  <span style="color:var(--ink-3)">+</span>
  <span class="tag amber">مصنعية {{ number_format($area > 0 ? $totalLaborCost / $area : 0, 1) }} ج.م</span>
  <span style="color:var(--ink-3)">=</span>
  <span class="tag gray" style="font-weight:700">{{ number_format($grandPerSqm, 1) }} ج.م / م²</span>
</div>
@endif

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
      <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
        <div style="text-align:center">
          <div class="muted" style="font-size:11px">خامات</div>
          <div style="font-weight:600;color:var(--accent)">{{ \App\Support\Money::format($row->material_cost) }} <small class="muted">ج.م</small></div>
          @if($row->mat_per_sqm !== null)<div class="muted" style="font-size:10px">{{ number_format($row->mat_per_sqm, 1) }}/م²</div>@endif
        </div>
        <div style="text-align:center">
          <div class="muted" style="font-size:11px">نثريات</div>
          <div style="font-weight:600;color:var(--neg)">{{ \App\Support\Money::format($row->petty_cost) }} <small class="muted">ج.م</small></div>
          @if($row->petty_per_sqm !== null)<div class="muted" style="font-size:10px">{{ number_format($row->petty_per_sqm, 1) }}/م²</div>@endif
        </div>
        <div style="text-align:center">
          <div class="muted" style="font-size:11px">مصنعية</div>
          <div style="font-weight:600;color:var(--warn)">{{ \App\Support\Money::format($row->labor_cost) }} <small class="muted">ج.م</small></div>
          @if($row->labor_per_sqm !== null)<div class="muted" style="font-size:10px">{{ number_format($row->labor_per_sqm, 1) }}/م²</div>@endif
        </div>
        <div style="text-align:center;padding-inline-start:12px;border-inline-start:1px solid var(--line)">
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

    {{-- Petty (نثريات) table --}}
    @if($row->petty->count())
      <div style="padding:8px 18px 4px;font-size:12px;font-weight:600;color:var(--neg);border-top:2px solid var(--line);background:color-mix(in srgb, var(--neg) 4%, transparent)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;vertical-align:middle;margin-inline-end:4px"><use href="#i-receipt"/></svg>
        النثريات ({{ $row->petty->count() }} مصروف)
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>البيان</th>
              <th>التاريخ</th>
              <th class="num">التكلفة</th>
            </tr>
          </thead>
          <tbody>
            @foreach($row->petty as $p)
              <tr>
                <td><strong>{{ $p->item }}</strong></td>
                <td class="muted">{{ $p->date?->format('Y-m-d') ?? '—' }}</td>
                <td class="num">{{ \App\Support\Money::format($p->cost) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" style="color:var(--neg)">إجمالي النثريات</td>
              <td class="num" style="color:var(--neg)">{{ \App\Support\Money::format($row->petty_cost) }}</td>
            </tr>
          </tfoot>
        </table>
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

{{-- نثريات عامة على المشروع (مش مربوطة ببند) --}}
@if($generalPetty->count())
  <div class="table-card" style="margin-bottom:18px">
    <div class="table-top">
      <h4>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:middle;margin-inline-end:5px;color:var(--neg)"><use href="#i-receipt"/></svg>
        نثريات عامة على المشروع
      </h4>
      <div style="text-align:center">
        <div class="muted" style="font-size:11px">الإجمالي</div>
        <div style="font-weight:700;color:var(--neg)">{{ \App\Support\Money::format($generalPettyCost) }} <small class="muted">ج.م</small></div>
      </div>
    </div>
    <div class="table-scroll">
      <table>
        <thead>
          <tr><th>البيان</th><th>التاريخ</th><th class="num">التكلفة</th></tr>
        </thead>
        <tbody>
          @foreach($generalPetty as $p)
            <tr>
              <td><strong>{{ $p->item }}</strong></td>
              <td class="muted">{{ $p->date?->format('Y-m-d') ?? '—' }}</td>
              <td class="num">{{ \App\Support\Money::format($p->cost) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2" style="color:var(--neg)">إجمالي النثريات العامة</td>
            <td class="num" style="color:var(--neg)">{{ \App\Support\Money::format($generalPettyCost) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
@endif

@if($bands->isEmpty())
  <div class="empty-state">
    <h4>لا توجد بنود في هذا المشروع</h4>
  </div>
@endif

@endsection
