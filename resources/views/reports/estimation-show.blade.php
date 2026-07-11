@extends('layouts.app')
@section('title', 'تقدير تكلفة — ' . $project->name)
@section('page-title', 'تقدير تكلفة مشروع جديد')

@section('content')
<div class="page-head no-print">
  <div>
    <h3>{{ $project->name }}</h3>
    <p>
      {{ $project->client->name ?? '—' }}
      @if($area) — مساحة مرجعية {{ rtrim(rtrim($project->area, '0'), '.') }} م² @endif
      — استخدمه كمرجع لتقدير أي مشروع جديد
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

{{-- ═══ أداة: قدّر مشروع جديد بمساحة مختلفة ═══ --}}
@if($grandPerSqm !== null)
<div class="est-tool no-print">
  <div class="est-tool-head">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-clipboard"/></svg>
    <div>
      <div class="est-tool-title">قدّر شقة جديدة</div>
      <div class="est-tool-sub">اكتب مساحة الشقة الجديدة، والنظام يحسب المتوقع لكل بند بناءً على هذا المشروع</div>
    </div>
    <div class="est-tool-input">
      <label>مساحة الشقة الجديدة (م²)</label>
      <input type="number" id="new-area" min="0" step="1" value="{{ rtrim(rtrim($area, '0'), '.') }}" oninput="recalcEstimate()">
    </div>
    <div class="est-tool-result">
      <div class="l">التكلفة المتوقعة</div>
      <div class="v tnum" id="est-total">{{ \App\Support\Money::format($grandTotal) }}</div>
      <div class="l">ج.م</div>
    </div>
  </div>
  <details class="est-proj-details" style="margin-top:16px; border-top:1px solid #eee; padding-top:12px">
    <summary style="cursor:pointer; font-weight:700; color:var(--accent); display:flex; align-items:center; gap:8px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px"><path d="M6 9l6 6 6-6"/></svg>
      عرض التفاصيل والكميات المتوقعة للمشروع الجديد
    </summary>
    <div id="proj-breakdown" style="margin-top:16px"></div>
  </details>
</div>
@endif

{{-- ═══ إجماليات المشروع المرجعي ═══ --}}
<div class="grid cols-5" style="margin-bottom:14px">
  <div class="vstat vstat-blue">
    <div class="top"><span class="label">إجمالي الخامات</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalMaterialCost) }} <small>ج.م</small></div>
    @if($grandPerSqm !== null)<div class="note">{{ number_format($area > 0 ? $totalMaterialCost / $area : 0, 1) }} ج.م/م²</div>@endif
  </div>
  <div class="vstat vstat-red">
    <div class="top"><span class="label">نثريات ومصروفات</span>
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

{{-- شرح تكلفة المتر --}}
@if($grandPerSqm !== null)
<div class="card" style="padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:13px">
  <strong style="color:var(--accent-ink)">المتر المربع الواحد يتكلّف:</strong>
  <span class="tag blue">خامات {{ number_format($area > 0 ? $totalMaterialCost / $area : 0, 1) }} ج.م</span>
  <span style="color:var(--ink-3)">+</span>
  <span class="tag red">نثريات ومصروفات {{ number_format($area > 0 ? $totalPettyCost / $area : 0, 1) }} ج.م</span>
  <span style="color:var(--ink-3)">+</span>
  <span class="tag amber">مصنعية {{ number_format($area > 0 ? $totalLaborCost / $area : 0, 1) }} ج.م</span>
  <span style="color:var(--ink-3)">=</span>
  <span class="tag gray" style="font-weight:700">{{ number_format($grandPerSqm, 1) }} ج.م / م²</span>
</div>
@endif

{{-- ═══ تفاصيل كل بند ═══ --}}
@foreach($bands as $row)
  <div class="table-card est-band"
       data-persqm="{{ $row->per_sqm ?? 0 }}"
       data-density="{{ $row->density_100 ?? 0 }}"
       data-costperunit="{{ $row->cost_per_unit ?? 0 }}"
       data-unitlabel="{{ $row->unit_label ?? '' }}"
       style="margin-bottom:18px">
    <div class="est-band-head">
      <div class="est-band-title">
        {{ $row->band->name }}
        <span class="tag {{ $row->band->status === 'done' ? 'green' : ($row->band->status === 'active' ? 'blue' : 'gray') }}">
          {{ $row->band->status === 'done' ? 'مكتمل' : ($row->band->status === 'active' ? 'جاري' : 'معلق') }}
        </span>
      </div>
      <div class="est-band-costs">
        <div class="ebc"><span class="l">خامات</span><span class="v" style="color:var(--accent)">{{ \App\Support\Money::format($row->material_cost) }}</span></div>
        <div class="ebc"><span class="l">نثريات ومصروفات</span><span class="v" style="color:var(--neg)">{{ \App\Support\Money::format($row->petty_cost) }}</span></div>
        <div class="ebc"><span class="l">مصنعية</span><span class="v" style="color:var(--warn)">{{ \App\Support\Money::format($row->labor_cost) }}</span></div>
        <div class="ebc ebc-total"><span class="l">الإجمالي</span><span class="v">{{ \App\Support\Money::format($row->total_cost) }}</span></div>
        @if($row->per_sqm !== null)
          <div class="ebc"><span class="l">لكل م²</span><span class="v" style="color:var(--accent)">{{ number_format($row->per_sqm, 1) }}</span></div>
        @endif
      </div>
    </div>

    {{-- تحليل وحدة العمل (متوسط الوحدة + الكثافة) --}}
    @if($row->unit_label)
      <div class="est-unit-bar">
        <div class="euc">
          <div class="euc-lbl">وحدة العمل</div>
          <div class="euc-val">{{ $row->unit_label }}</div>
        </div>
        <div class="euc">
          <div class="euc-lbl">إجمالي الكمية المنفّذة</div>
          <div class="euc-val">{{ rtrim(rtrim(number_format($row->unit_qty, 2), '0'), '.') }} {{ $row->unit_label }}</div>
        </div>
        <div class="euc euc-accent">
          <div class="euc-lbl">متوسط تكلفة الـ{{ $row->unit_label }}</div>
          <div class="euc-val">{{ $row->cost_per_unit !== null ? \App\Support\Money::format($row->cost_per_unit) : '—' }} <small>ج.م</small></div>
        </div>
        @if($row->density_100 !== null)
          <div class="euc euc-accent">
            <div class="euc-lbl">الكثافة (لكل 100م²)</div>
            <div class="euc-val">{{ number_format($row->density_100, 1) }} {{ $row->unit_label }}</div>
          </div>
          <div class="euc euc-proj no-print">
            <div class="euc-lbl">المتوقع للشقة الجديدة</div>
            <div class="euc-val"><span class="est-proj-units">—</span> {{ $row->unit_label }}</div>
          </div>
        @endif
      </div>
    @endif

    {{-- الخامات --}}
    @if($row->materials->count())
      <div class="est-sec-lbl" style="color:var(--accent);background:color-mix(in srgb, var(--accent) 4%, transparent)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
        الخامات ({{ $row->materials->count() }} صنف)
      </div>
      <div class="table-scroll">
        <table>
          <thead><tr><th>الخامة</th><th class="num">الكمية</th><th>الوحدة</th><th class="num">التكلفة</th></tr></thead>
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
          <tfoot><tr><td colspan="3" style="color:var(--accent)">إجمالي الخامات</td><td class="num" style="color:var(--accent)">{{ \App\Support\Money::format($row->material_cost) }}</td></tr></tfoot>
        </table>
      </div>
    @endif

    {{-- نثريات ومصروفات --}}
    @if($row->petty->count())
      <div class="est-sec-lbl" style="color:var(--neg);background:color-mix(in srgb, var(--neg) 4%, transparent)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
        نثريات ومصروفات ({{ $row->petty->count() }} مصروف)
      </div>
      <div class="table-scroll">
        <table>
          <thead><tr><th>البيان</th><th>التاريخ</th><th class="num">التكلفة</th></tr></thead>
          <tbody>
            @foreach($row->petty as $p)
              <tr><td><strong>{{ $p->item }}</strong></td><td class="muted">{{ $p->date?->format('Y-m-d') ?? '—' }}</td><td class="num">{{ \App\Support\Money::format($p->cost) }}</td></tr>
            @endforeach
          </tbody>
          <tfoot><tr><td colspan="2" style="color:var(--neg)">إجمالي النثريات والمصروفات</td><td class="num" style="color:var(--neg)">{{ \App\Support\Money::format($row->petty_cost) }}</td></tr></tfoot>
        </table>
      </div>
    @endif

    {{-- الفنيين --}}
    @if($row->workers->count())
      <div class="est-sec-lbl" style="color:var(--warn);background:color-mix(in srgb, var(--warn) 4%, transparent)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
        الفنيين ({{ $row->workers->count() }} صنايعي)
      </div>
      <div class="table-scroll">
        <table>
          <thead><tr><th>الاسم</th><th>نوع التعاقد</th><th class="num">التفاصيل</th><th class="num">التعاقد</th><th class="num">المدفوع</th><th class="num">المتبقي</th></tr></thead>
          <tbody>
            @foreach($row->workers as $w)
              <tr>
                <td><strong>{{ $w->name }}</strong></td>
                <td class="muted">{{ $w->contract_type }}</td>
                <td class="num muted" style="font-size:12px">
                  @if($w->qty !== null && $w->unit_rate !== null)
                    {{ rtrim(rtrim(number_format($w->qty, 2), '0'), '.') }} × {{ \App\Support\Money::format($w->unit_rate) }}
                  @else — @endif
                </td>
                <td class="num">{{ \App\Support\Money::format($w->amount) }}</td>
                <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($w->paid) }}</td>
                <td class="num" style="color:{{ $w->remaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($w->remaining) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot><tr><td colspan="3" style="color:var(--warn)">إجمالي المصنعية</td><td class="num" style="color:var(--warn)">{{ \App\Support\Money::format($row->labor_cost) }}</td><td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($row->workers->sum('paid')) }}</td><td class="num">{{ \App\Support\Money::format($row->workers->sum('remaining')) }}</td></tr></tfoot>
        </table>
      </div>
    @elseif($row->labor_cost > 0)
      <div style="padding:10px 18px;border-top:2px solid var(--line);color:var(--ink-3);font-size:13px">
        المصنعية: {{ \App\Support\Money::format($row->labor_cost) }} ج.م — لا يوجد فنيين مسجّلين تفصيليًا
      </div>
    @endif
  </div>
@endforeach

{{-- نثريات ومصروفات عامة على المشروع --}}
@if($generalPetty->count())
  <div class="table-card" style="margin-bottom:18px">
    <div class="table-top">
      <h4>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:middle;margin-inline-end:5px;color:var(--neg)"><use href="#i-receipt"/></svg>
        نثريات ومصروفات عامة على المشروع
      </h4>
      <div style="text-align:center">
        <div class="muted" style="font-size:11px">الإجمالي</div>
        <div style="font-weight:700;color:var(--neg)">{{ \App\Support\Money::format($generalPettyCost) }} <small class="muted">ج.م</small></div>
      </div>
    </div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>البيان</th><th>التاريخ</th><th class="num">التكلفة</th></tr></thead>
        <tbody>
          @foreach($generalPetty as $p)
            <tr><td><strong>{{ $p->item }}</strong></td><td class="muted">{{ $p->date?->format('Y-m-d') ?? '—' }}</td><td class="num">{{ \App\Support\Money::format($p->cost) }}</td></tr>
          @endforeach
        </tbody>
        <tfoot><tr><td colspan="2" style="color:var(--neg)">إجمالي النثريات والمصروفات العامة</td><td class="num" style="color:var(--neg)">{{ \App\Support\Money::format($generalPettyCost) }}</td></tr></tfoot>
      </table>
    </div>
  </div>
@endif

@if($bands->isEmpty())
  <div class="empty-state"><h4>لا توجد بنود في هذا المشروع</h4></div>
@endif

@push('scripts')
<script>
const REF_AREA = {{ $area ?: 0 }};
const GRAND_PER_SQM = {{ $grandPerSqm ?? 0 }};
const BANDS_DATA = {!! json_encode($bands) !!};

function recalcEstimate() {
  const input = document.getElementById('new-area');
  if (!input) return;
  const newArea = parseFloat(input.value) || 0;
  const ratio = REF_AREA > 0 ? newArea / REF_AREA : 1;

  const totalEl = document.getElementById('est-total');
  if (totalEl) totalEl.textContent = fmt(GRAND_PER_SQM * newArea);

  let html = '';
  BANDS_DATA.forEach(row => {
    let bandHtml = `<div style="margin-bottom:20px; border:1px solid #eee; border-radius:8px; padding:12px; background:#fafafa">
      <h4 style="margin:0 0 10px; color:var(--ink); font-size:1.05rem; border-bottom:1px solid #ddd; padding-bottom:6px">${row.band.name}</h4>`;
    
    // Materials
    if (row.materials && row.materials.length > 0) {
      bandHtml += `<div style="font-weight:bold; color:var(--accent); margin-bottom:6px; font-size:0.9rem">الخامات المتوقعة</div>
      <table style="width:100%; font-size:0.85rem; margin-bottom:12px">
        <thead><tr style="text-align:right; color:var(--muted)"><th>الخامة</th><th>الكمية</th><th>التكلفة (ج.م)</th></tr></thead>
        <tbody>`;
      let matTotal = 0;
      row.materials.forEach(m => {
        let pQty = m.qty * ratio;
        let pCost = m.cost * ratio;
        matTotal += pCost;
        bandHtml += `<tr><td>${m.item}</td><td>${trimNum(pQty.toFixed(1))} ${m.unit}</td><td style="font-weight:bold">${fmt(pCost)}</td></tr>`;
      });
      bandHtml += `</tbody>
        <tfoot><tr style="color:var(--accent); font-weight:bold"><td colspan="2">إجمالي الخامات</td><td>${fmt(matTotal)}</td></tr></tfoot>
      </table>`;
    }

    // Labor
    if (row.labor_cost > 0) {
      let pLabor = row.labor_cost * ratio;
      bandHtml += `<div style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; padding:6px 0; border-top:1px solid #eee">
        <span style="font-weight:bold; color:var(--warn)">المصنعية المتوقعة (للفنيين)</span>
        <span style="font-weight:bold">${fmt(pLabor)} ج.م</span>
      </div>`;
    }

    // Petty
    if (row.petty_cost > 0) {
      let pPetty = row.petty_cost * ratio;
      bandHtml += `<div style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; padding:6px 0; border-top:1px solid #eee">
        <span style="font-weight:bold; color:var(--neg)">المصروفات المتوقعة</span>
        <span style="font-weight:bold">${fmt(pPetty)} ج.م</span>
      </div>`;
    }

    let pTotal = row.total_cost * ratio;
    bandHtml += `<div style="display:flex; justify-content:space-between; align-items:center; font-size:0.95rem; margin-top:8px; padding-top:8px; border-top:2px solid #ddd; font-weight:bold">
      <span>إجمالي البند المتوقع</span>
      <span>${fmt(pTotal)} ج.م</span>
    </div>`;

    bandHtml += `</div>`;
    html += bandHtml;
  });

  const breakdownEl = document.getElementById('proj-breakdown');
  if (breakdownEl) {
    if (html === '') {
      breakdownEl.innerHTML = '<div class="muted">لا توجد بيانات تفصيلية.</div>';
    } else {
      breakdownEl.innerHTML = html;
    }
  }

  // Also update original bands' projected units (keeping that)
  document.querySelectorAll('.est-band').forEach(card => {
    const density = parseFloat(card.dataset.density) || 0;
    const projUnitsEl = card.querySelector('.est-proj-units');
    if (projUnitsEl) {
      const units = density * (newArea / 100);
      projUnitsEl.textContent = units > 0 ? trimNum(units.toFixed(1)) : '—';
    }
  });
}
function fmt(n){ return (Math.round(n*100)/100).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function trimNum(s){ return String(s).replace(/\.0$/,''); }
recalcEstimate();
</script>
@endpush
@endsection
