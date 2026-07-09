@extends('layouts.app')

@section('title', $project->name)
@section('page-title', $project->name)

@section('content')

<div class="page-head">
  <div>
    <h3>{{ $project->name }}</h3>
    <p>{{ $project->client->name }} @if($project->address)— {{ $project->address }}@endif</p>
  </div>
  <div class="btn-row">
    <a href="{{ route('reports.statement', $project) }}" class="btn ghost">كشف حساب العميل</a>
    <a href="{{ route('reports.statement.summary', $project) }}" class="btn ghost">كشف حساب مختصر</a>
    @if(auth()->user()->canSeeFinancials())
      <a href="{{ route('reports.company', $project) }}" class="btn ghost">كشف حساب الشركة</a>
    @endif
    @if($project->warranty)
      <a href="{{ route('warranties.show', $project) }}" class="btn ghost">الضمان</a>
    @endif
    <a href="{{ route('projects.edit', $project) }}" class="btn ghost">تعديل</a>
    <a href="{{ route('projects.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

@php
  $isOwner = auth()->user()->canSeeFinancials();
  $totalProfit = $project->bands->sum(fn ($b) => $b->profit());
  $initialValue = $project->initialContractValue();
  $actualValue  = $project->actualClientTotal();
  $owedWorkers  = $project->bands->flatMap(fn ($b) => $b->workers->map(fn ($w) => (object)[
    'name'      => $w->name,
    'band'      => $b->name,
    'remaining' => $w->remaining(),
  ]))->filter(fn ($w) => $w->remaining > 0)->values();
@endphp

@if($owedWorkers->isNotEmpty())
<div class="flash warning" style="cursor:pointer;margin-bottom:16px" onclick="document.getElementById('owed-modal').classList.add('open')">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;flex-shrink:0"><use href="#i-hardhat"/></svg>
  فيه <strong>{{ $owedWorkers->count() }}</strong> {{ $owedWorkers->count() === 1 ? 'صنايعي مستحق' : 'صنايعية مستحقين' }} فلوس في المشروع ده — اضغط للتفاصيل
</div>

<div class="modal-overlay" id="owed-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:520px">
    <div class="modal-head">
      <h4 style="margin:0">الصنايعية المستحقين فلوس</h4>
      <button class="btn ghost sm" onclick="document.getElementById('owed-modal').classList.remove('open')">✕</button>
    </div>
    <div class="table-scroll" style="margin:0">
      <table>
        <thead><tr><th>الاسم</th><th>البند</th><th class="num">المتبقي</th></tr></thead>
        <tbody>
          @foreach($owedWorkers as $w)
            <tr>
              <td><strong>{{ $w->name }}</strong></td>
              <td class="muted">{{ $w->band }}</td>
              <td class="num" style="color:var(--neg);font-weight:600">{{ \App\Support\Money::format($w->remaining) }} ج.م</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2"><strong>الإجمالي</strong></td>
            <td class="num" style="color:var(--neg);font-weight:700">{{ \App\Support\Money::format($owedWorkers->sum('remaining')) }} ج.م</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endif

<div class="grid {{ $isOwner ? 'cols-4' : 'cols-3' }}" style="margin-bottom:20px">
  <div class="card stat">
    <div class="top"><span class="label">قيمة التعاقد</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span></div>
    <div class="val tnum">{{ \App\Support\Money::format($initialValue) }} <small>ج.م</small></div>
    @include('partials._actual-vs-initial', ['initial' => $initialValue, 'actual' => $actualValue])
  </div>
  <div class="card stat">
    <div class="top"><span class="label">محصّل من العميل</span><span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span></div>
    <div class="val tnum" style="color:var(--pos)">{{ \App\Support\Money::format($project->totalCollected()) }} <small>ج.م</small></div>
    <div class="note">الباقي عليه: {{ \App\Support\Money::format(max($project->amountDue(), 0)) }} ج.م</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المصروف</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span></div>
    <div class="val tnum">{{ \App\Support\Money::format($project->totalSpent()) }} <small>ج.م</small></div>
  </div>
  @if($isOwner)
    <div class="card stat row-click" onclick="document.getElementById('profit-modal').classList.add('open')">
      <div class="top"><span class="label">الربح المتحقق</span><span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg></span></div>
      <div class="val tnum" style="color:{{ $totalProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totalProfit) }} <small>ج.م</small></div>
      <div class="note">دوس هنا لتفاصيل الربح</div>
    </div>
  @endif
</div>

{{-- Section tabs — each part of the project lives in its own tab --}}
<div class="tabs" id="project-tabs">
  <button type="button" class="tab active" data-tab="bands" onclick="switchProjectTab('bands')">
    البنود والمراحل <span class="cnt">{{ $project->bands->count() }}</span>
  </button>
  <button type="button" class="tab" data-tab="installments" onclick="switchProjectTab('installments')">
    الأقساط <span class="cnt">{{ $project->installments->count() }}</span>
  </button>
  <button type="button" class="tab" data-tab="materials" onclick="switchProjectTab('materials')">
    الخامات المشتراة <span class="cnt">{{ $project->materials->count() }}</span>
  </button>
  <button type="button" class="tab" data-tab="returns" onclick="switchProjectTab('returns')">
    المرتجعات <span class="cnt">{{ $project->materials->sum(fn($m) => $m->returns->count()) }}</span>
  </button>
  <button type="button" class="tab" data-tab="workers" onclick="switchProjectTab('workers')">
    الفنيين <span class="cnt">{{ $project->bands->sum(fn($b) => $b->workers->count()) }}</span>
  </button>
</div>

<div class="tab-panel" data-panel="bands">
<div class="section-label" style="display:flex;justify-content:space-between;align-items:center;margin-top:0">
  <span>البنود والمراحل</span>
  <a href="{{ route('projects.bands.create', $project) }}" class="btn sm">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    بند جديد
  </a>
</div>
<div class="table-card" style="margin-bottom:24px">
  @if($project->bands->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>البند</th>
            <th>الحالة</th>
            <th>الفنيين</th>
            <th class="num">سعر العميل</th>
            <th class="num">أجر المصنعية</th>
            <th class="num">المواد</th>
            <th class="num">الربح</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($project->bands as $band)
            @php
              $matCost = $band->materialCost();
              $profit  = $band->profit();
            @endphp
            <tr>
              <td><strong>{{ $band->name }}</strong></td>
              <td>
                @if(auth()->user()->canManage())
                  <form method="POST" action="{{ route('bands.updateStatus', $band) }}" class="status-quick-form">
                    @csrf @method('PATCH')
                    <select name="status" class="status-quick-select tag-{{ $band->status }}" onchange="this.form.submit()">
                      <option value="pending" {{ $band->status === 'pending' ? 'selected' : '' }}>لم يبدأ</option>
                      <option value="active" {{ $band->status === 'active' ? 'selected' : '' }}>جاري</option>
                      <option value="done" {{ $band->status === 'done' ? 'selected' : '' }}>منفذ</option>
                    </select>
                  </form>
                @else
                  @if($band->status === 'done')
                    <span class="tag green"><span class="dot"></span>منفذ</span>
                  @elseif($band->status === 'active')
                    <span class="tag blue"><span class="dot"></span>جاري</span>
                  @else
                    <span class="tag gray">لم يبدأ</span>
                  @endif
                @endif
              </td>
              <td class="muted">
                @if($band->workers->count())
                  {{ $band->workers->pluck('name')->join('، ') }}
                @elseif($band->team_name)
                  {{ $band->team_name }}
                @else
                  —
                @endif
              </td>
              <td class="num">{{ \App\Support\Money::format($band->client_price) }}</td>
              <td class="num">{{ \App\Support\Money::format($band->labor_amount) }}</td>
              <td class="num">{{ \App\Support\Money::format($matCost) }}</td>
              <td class="num" style="color:{{ $profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                {{ \App\Support\Money::format($profit) }}
              </td>
              <td style="display:flex;gap:6px">
                @if($isOwner)
                  <a href="{{ route('bands.statement', $band) }}" class="btn ghost sm">كشف حساب</a>
                @endif
                <a href="{{ route('bands.edit', $band) }}" class="btn ghost sm">تعديل</a>
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4">الإجماليات</td>
            <td class="num">{{ \App\Support\Money::format($project->bands->sum('client_price')) }}</td>
            <td class="num">{{ \App\Support\Money::format($project->bands->sum('labor_amount')) }}</td>
            <td class="num">{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->materialCost())) }}</td>
            <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->profit())) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
      <h4>لا توجد بنود بعد</h4>
    </div>
  @endif
</div>
</div>{{-- /tab-panel: bands --}}

<div class="tab-panel" data-panel="installments" style="display:none">
<div class="section-label" style="display:flex;justify-content:space-between;align-items:center;margin-top:0">
  <span>عقد التقسيط</span>
  <a href="{{ route('installments.index') }}" class="btn sm">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
    إدارة العقود والأقساط
  </a>
</div>
<div class="table-card" style="margin-bottom:24px">
  @php $contract = $project->contracts->first(); @endphp
  @if($contract)
    @php
      $collected = (float) $contract->down_payment + (float) $contract->payments->sum('amount_paid');
    @endphp
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th class="num">إجمالي العقد</th>
            <th class="num">المقدم</th>
            <th class="num">القسط الشهري</th>
            <th class="num">عدد الأقساط</th>
            <th class="num">المحصّل</th>
            <th class="num">المتبقي</th>
            <th>الحالة</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="num">{{ \App\Support\Money::format($contract->total_after_interest) }}</td>
            <td class="num">{{ \App\Support\Money::format($contract->down_payment) }}</td>
            <td class="num">{{ \App\Support\Money::format($contract->monthly_installment) }}</td>
            <td class="num">{{ $contract->installment_months }}</td>
            <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($collected) }}</td>
            <td class="num" style="color:{{ $contract->remaining_balance > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($contract->remaining_balance) }}</td>
            <td><span class="tag {{ $contract->remaining_balance > 0 ? 'amber' : 'green' }}">{{ $contract->remaining_balance > 0 ? 'نشط' : 'مسدد بالكامل' }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state">
      <h4>لا يوجد عقد تقسيط لهذا المشروع</h4>
      <a href="{{ route('installments.index') }}" class="btn sm" style="margin-top:10px">إنشاء عقد من صفحة الأقساط</a>
    </div>
  @endif
</div>
</div>{{-- /tab-panel: installments --}}

<div class="tab-panel" data-panel="materials" style="display:none">
<div class="section-label no-print" style="display:flex;justify-content:space-between;align-items:center;margin-top:0">
  <span>الخامات المشتراة</span>
  <div class="btn-row">
    <button type="button" class="btn ghost sm" onclick="printMaterials()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
      طباعة
    </button>
    <a href="{{ route('expenses.create', $project) }}" class="btn ghost sm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
      مصروف نثري
    </a>
    <a href="{{ route('materials.create', ['project_id' => $project->id]) }}" class="btn sm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
      خامة جديدة
    </a>
  </div>
</div>

@if($project->materials->count())
@php
  $matBands = $project->materials->map(fn($m) => $m->band)->filter()->unique('id');
@endphp
<div class="filter-bar no-print" style="margin-bottom:16px;padding:12px 16px;">
  <div class="f-field">
    <label>فلترة بالبند</label>
    <div class="f-select-wrap">
      <select id="materials-band-filter" class="f-select" style="min-width:200px">
        <option value="">كل البنود</option>
        @foreach($matBands as $band)
          <option value="{{ $band->id }}">{{ $band->name }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chevron-down"/></svg>
    </div>
  </div>
</div>
@endif
<div class="table-card" style="margin-bottom:24px">
  @if($project->materials->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>الصنف</th>
            <th>البند</th>
            <th>المورد</th>
            <th class="num">الكمية</th>
            <th>الوحدة</th>
            <th class="num">سعر الوحدة</th>
            <th class="num">المرتجع</th>
            <th class="num">الإجمالي</th>
            <th>التاريخ</th>
          </tr>
        </thead>
        <tbody>
          @foreach($project->materials->sortByDesc('date') as $m)
            <tr class="mat-row" data-band="{{ $m->band_id }}">
              <td><strong>{{ $m->item }}</strong>@if($m->isMisc())<span class="tag gray sm" style="margin-right:6px">نثري</span>@endif</td>
              <td class="muted">{{ $m->band?->name ?? '—' }}</td>
              <td class="muted">{{ $m->supplier?->name ?? '—' }}</td>
              <td class="num">{{ number_format($m->qty, 1) }}</td>
              <td class="muted">{{ $m->unit }}</td>
              <td class="num">{{ \App\Support\Money::format($m->unit_price) }}</td>
              <td class="num {{ $m->returnedQty() > 0 ? '' : 'muted' }}">{{ \App\Support\Money::format($m->returnedQty(), 1) }}</td>
              <td class="num">{{ \App\Support\Money::format($m->netCost()) }}</td>
              <td class="muted">{{ $m->date->format('Y-m-d') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد خامات مسجلة</h4></div>
  @endif
</div>
</div>{{-- /tab-panel: materials --}}

<div class="tab-panel" data-panel="returns" style="display:none">
<div class="section-label" style="display:flex;justify-content:space-between;align-items:center;margin-top:0">
  <span>المرتجعات</span>
  <a href="{{ route('returns.create', $project) }}" class="btn sm">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    إضافة مرتجع
  </a>
</div>
@php $allReturns = $project->materials->flatMap->returns->sortByDesc('date'); @endphp
<div class="table-card" style="margin-bottom:24px">
  @if($allReturns->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>تاريخ الإرجاع</th>
            <th>الصنف</th>
            <th class="num">الكمية المرتجعة</th>
            <th>من عملية شراء بتاريخ</th>
            <th>ملاحظات</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($allReturns as $r)
            <tr>
              <td class="muted">{{ $r->date->format('Y-m-d') }}</td>
              <td><strong>{{ $r->material->item }}</strong></td>
              <td class="num">{{ number_format($r->qty, 1) }} {{ $r->material->unit }}</td>
              <td class="muted">{{ $r->material->date->format('Y-m-d') }} @if($r->material->supplier) — {{ $r->material->supplier->name }} @endif</td>
              <td class="muted">{{ $r->notes ?: '—' }}</td>
              <td>
                <form method="POST" action="{{ route('returns.destroy', $r) }}" onsubmit="return confirm('حذف هذا المرتجع؟')">
                  @csrf @method('DELETE')
                  <button class="btn ghost sm" style="color:var(--neg)">حذف</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
      <h4>لا توجد مرتجعات مسجلة</h4>
    </div>
  @endif
</div>
</div>{{-- /tab-panel: returns --}}

<div class="tab-panel" data-panel="workers" style="display:none">
<div class="section-label" style="margin-top:0">الصنايعية الشغالين في المشروع</div>
<p class="muted" style="margin:-6px 0 12px">
  الصنايعي بياخد فلوسه على دفعات لغاية ما الشغل يخلص — كل دفعة بتتخصم من المحفظة وقتها بس، والباقي يفضل مستحق (مش دين علينا).
  لو صنايعي عمل جزء من الشغل ومشي، سيبه بأمتاره اللي اتعملت وضيف صنايعي تاني في نفس البند يكمّل الباقي (من تعديل البند).
</p>
@php $allWorkers = $project->bands->flatMap(fn($b) => $b->workers->map(fn($w) => (object) ['worker' => $w, 'band' => $b])); @endphp
<div class="table-card" style="margin-bottom:24px">
  @if($allWorkers->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>الاسم</th>
            <th>البند</th>
            <th>التعاقد</th>
            <th class="num">المتعاقد عليه</th>
            <th class="num">المدفوع</th>
            <th class="num">المتبقي</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($allWorkers as $row)
            @php $paid = $row->worker->paidTotal(); $remaining = $row->worker->remaining(); @endphp
            <tr>
              <td>
                <strong>{{ $row->worker->name }}</strong>
                @if($row->worker->specialty)<div class="muted" style="font-size:12px">{{ $row->worker->specialty }}</div>@endif
              </td>
              <td class="muted">{{ $row->band->name }}</td>
              <td class="muted">
                {{ $row->worker->contractTypeAr() }}
                @if(in_array($row->worker->contract_type, ['per_meter','per_piece','daily']) && $row->worker->contract_qty)
                  <div style="font-size:12px">{{ rtrim(rtrim(number_format($row->worker->contract_qty, 2), '0'), '.') }} × {{ \App\Support\Money::format($row->worker->contract_unit_rate) }}</div>
                @endif
              </td>
              <td class="num">{{ \App\Support\Money::format($row->worker->amount) }}</td>
              <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</td>
              <td class="num" style="color:{{ $remaining > 0 ? 'var(--amber, #b45309)' : 'var(--pos)' }}">{{ \App\Support\Money::format($remaining) }}</td>
              <td>
                <a href="{{ route('workers.payments', $row->worker) }}" class="btn ghost sm">الدفعات</a>
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3">الإجمالي</td>
            <td class="num">{{ \App\Support\Money::format($allWorkers->sum(fn($r) => $r->worker->amount)) }}</td>
            <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($allWorkers->sum(fn($r) => $r->worker->paidTotal())) }}</td>
            <td class="num">{{ \App\Support\Money::format($allWorkers->sum(fn($r) => $r->worker->remaining())) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
      <h4>لا يوجد صنايعية مسجلين بعد</h4>
    </div>
  @endif
</div>
</div>{{-- /tab-panel: workers --}}

@if($isOwner)
  {{-- Profit breakdown modal — owner-only, explains exactly where the profit figure comes from --}}
  <div class="modal-overlay" id="profit-modal">
    <div class="modal-box">
      <div class="modal-head">
        <h4>تفاصيل الربح المتحقق — {{ $project->name }}</h4>
        <button type="button" class="btn ghost sm no-print" onclick="document.getElementById('profit-modal').classList.remove('open')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
          إغلاق
        </button>
      </div>
      <div class="modal-body">
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>البند</th>
                <th class="num">إجمالي العميل</th>
                <th class="num">التكلفة</th>
                <th class="num">الربح</th>
              </tr>
            </thead>
            <tbody>
              @foreach($project->bands as $band)
                @php $bProfit = $band->profit(); @endphp
                <tr>
                  <td>{{ $band->name }}</td>
                  <td class="num">{{ \App\Support\Money::format($band->actualClientTotal()) }}</td>
                  <td class="num">{{ \App\Support\Money::format($band->totalCost()) }}</td>
                  <td class="num" style="color:{{ $bProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($bProfit) }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td>الإجمالي</td>
                <td class="num">{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->actualClientTotal())) }}</td>
                <td class="num">{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->totalCost())) }}</td>
                <td class="num" style="color:{{ $totalProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totalProfit) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="btn-row no-print" style="margin-top:16px">
          <button type="button" class="btn" onclick="printProfitModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
            طباعة
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
  <script>
    function printProfitModal() {
      document.body.classList.add('printing-profit');
      window.print();
    }
    function printMaterials() {
      document.body.classList.add('printing-materials');
      window.print();
    }
    window.addEventListener('afterprint', () => {
      document.body.classList.remove('printing-profit');
      document.body.classList.remove('printing-materials');
    });
  </script>
  @endpush
@endif

@push('scripts')
<script>
function switchProjectTab(name) {
  document.querySelectorAll('#project-tabs .tab').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = p.dataset.panel === name ? '' : 'none');
}

document.getElementById('materials-band-filter')?.addEventListener('change', function() {
  const val = this.value;
  const rows = document.querySelectorAll('.mat-row');
  rows.forEach(row => {
    if(val === '' || row.dataset.band === val) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});
</script>
@endpush
@endsection
