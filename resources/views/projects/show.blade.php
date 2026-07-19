@extends('layouts.app')

@section('title', $project->name)
@section('page-title', $project->name)

@section('content')

@if($errors->any())
  <div class="flash error" style="margin-bottom:16px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

@php
  $isOwner = auth()->user()->canSeeFinancials();
  $totalProfit = $project->profit();
  $actualValue  = $project->grossClientTotal();
  // "قيمة المشروع" فوق = الفاتورة الفعلية الحية (خامات + مصنعية بسعرهم الحقيقي
  // وقت التسجيل). "سعر العميل" في جدول البنود تحت = السعر المتفق عليه وقت
  // إنشاء/تعديل البند (من عرض السعر أو اتكتب يدوي)، وده بيفضل زي ما هو حتى
  // لو خامات جديدة اتسجلت على البند بعد كده — عشان كده بيختلفوا مع الوقت.
  $bandsAgreedTotal = $project->bands->sum('client_price');
  $valueGap = $actualValue - $bandsAgreedTotal;
  $owedWorkers  = $project->bands->flatMap(fn ($b) => $b->workers->map(fn ($w) => (object)[
    'name'      => $w->name,
    'band'      => $b->name,
    'remaining' => $w->remaining(),
  ]))->filter(fn ($w) => $w->remaining > 0)->values();
@endphp

<div class="page-head">
  <div>
    <h3>{{ $project->name }}</h3>
    <p>{{ $project->client->name }} @if($project->address)— {{ $project->address }}@endif</p>
  </div>
  <div class="btn-row">
    @if($owedWorkers->isNotEmpty())
      <button type="button" class="bell-btn" onclick="document.getElementById('owed-modal').classList.add('open')" title="{{ $owedWorkers->count() }} صنايعية مستحقين فلوس في المشروع ده">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bell"/></svg>
        <span class="bell-badge">{{ $owedWorkers->count() }}</span>
      </button>
    @endif
    <a href="{{ route('reports.statement', $project) }}" class="btn ghost">كشف حساب العميل</a>
    <a href="{{ route('reports.statement.summary', $project) }}" class="btn ghost">كشف حساب مختصر</a>
    @if(auth()->user()->canSeeFinancials())
      <a href="{{ route('reports.company', $project) }}" class="btn ghost">كشف حساب الشركة</a>
    @endif
    @if($project->warranty)
      <a href="{{ route('warranties.show', $project) }}" class="btn ghost">الضمان</a>
    @endif
    <div style="display:flex; flex-direction:column; gap:4px; align-items:stretch">
      @if($project->status !== 'active')
        <button type="button" class="btn ghost" style="color:var(--accent); padding:4px 8px; min-height:auto; font-size:11px; height:auto" onclick="changeProjectStatus('active')">تنشيط المشروع</button>
      @endif
      @if($project->status !== 'done')
        <button type="button" class="btn ghost" style="color:var(--good); padding:4px 8px; min-height:auto; font-size:11px; height:auto" onclick="changeProjectStatus('done')">إنهاء المشروع</button>
      @endif
      @if($project->status !== 'suspended')
        <button type="button" class="btn ghost" style="color:var(--amber); padding:4px 8px; min-height:auto; font-size:11px; height:auto" onclick="changeProjectStatus('suspended')">تعليق المشروع</button>
      @endif
      @if($project->status !== 'canceled')
        <button type="button" class="btn ghost" style="color:var(--bad); padding:4px 8px; min-height:auto; font-size:11px; height:auto" onclick="changeProjectStatus('canceled')">إلغاء المشروع</button>
      @endif
    </div>
    <a href="{{ route('projects.edit', $project) }}" class="btn ghost">تعديل</a>
    <a href="{{ route('projects.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

@if($owedWorkers->isNotEmpty())
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

<div class="grid {{ $isOwner ? 'cols-5' : 'cols-4' }}" style="margin-bottom:20px">
  <div class="card stat">
    <div class="top"><span class="label">قيمة المشروع</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span></div>
    <div class="val tnum price-sell">{{ \App\Support\Money::format($actualValue) }} <small>ج.م</small></div>
    <div class="note" style="display:flex;justify-content:space-between;align-items:center;margin-top:6px">
      <span>سعر الشراء (تكلفة)</span>
      <strong class="price-cost">{{ \App\Support\Money::format($project->totalSpent()) }} ج.م</strong>
    </div>
    @if(abs($valueGap) > 0.5)
      <div class="note" style="margin-top:4px;font-size:11px;color:var(--ink-3)">
        الفرق عن سعر البنود المتفق عليه ({{ \App\Support\Money::format($bandsAgreedTotal) }} ج.م):
        {{ $valueGap > 0 ? '+' : '' }}{{ \App\Support\Money::format($valueGap) }} ج.م
        — خامات/مصنعية اتسجلت فعليًا بعد تحديد سعر البند
      </div>
    @endif
  </div>
  <div class="card stat">
    <div class="top"><span class="label">الخصم الممنوح</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-percent"/></svg></span></div>
    <div class="val tnum" style="color:var(--amber)">{{ \App\Support\Money::format($project->totalDiscount()) }} <small>ج.م</small></div>
    <div class="note">إجمالي ما تم خصمه للعميل</div>
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
      <div class="note">بعد طرح الخصم — للتفاصيل اضغط هنا</div>
    </div>
  @endif
</div>

{{-- Section tabs — each part of the project lives in its own tab --}}
<div class="tabs" id="project-tabs">
  <button type="button" class="tab active" data-tab="bands" onclick="switchProjectTab('bands')">
    البنود والمراحل <span class="cnt">{{ $project->bands->count() }}</span>
  </button>
  <button type="button" class="tab" data-tab="installments" onclick="switchProjectTab('installments')">
    الماليات
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
  @if($isOwner)
    <button type="button" class="tab" data-tab="reports" onclick="switchProjectTab('reports')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-inline-end:4px;vertical-align:-2px"><use href="#i-pie-chart"/></svg>
      تقارير المشروع
    </button>
  @endif
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
            <th>الفنيين</th>
            <th class="num" title="السعر اللي اتحدد وقت إنشاء/تعديل البند — بيفضل زي ما هو حتى لو خامات جديدة اتسجلت بعد كده">السعر المتفق عليه</th>
            <th class="num" title="خامات + مصنعية بسعر بيعهم الحقيقي للعميل + نسبة الإشراف — ده اللي بيتحسب منه الربح فعليًا">الفاتورة الفعلية</th>
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
              $isDone  = $band->status === 'done';
              $bandActual = $band->actualClientTotal();
            @endphp
            <tr class="{{ $isDone ? 'band-row-done' : '' }}">
              <td>
                @if($isDone)
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--pos)" stroke-width="2.5" style="vertical-align:-2px;margin-inline-end:4px"><use href="#i-check-circle"/></svg>
                @endif
                <strong>{{ $band->name }}</strong>
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
              <td class="num muted">{{ \App\Support\Money::format($band->client_price) }}</td>
              <td class="num"><strong>{{ \App\Support\Money::format($bandActual) }}</strong></td>
              <td class="num">{{ \App\Support\Money::format($band->labor_amount) }}</td>
              <td class="num">{{ \App\Support\Money::format($matCost) }}</td>
              <td class="num" style="color:{{ $profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                {{ \App\Support\Money::format($profit) }}
              </td>
              <td style="display:flex;gap:6px;align-items:center">
                @if($isOwner)
                  <a href="{{ route('bands.statement', $band) }}" class="btn ghost sm">كشف حساب</a>
                @endif
                <a href="{{ route('bands.edit', $band) }}" class="btn ghost sm">تعديل</a>
                @if(auth()->user()->canManage() && ! $isDone)
                  <button type="button" class="btn ghost sm" style="color:var(--pos);border-color:var(--pos)"
                          onclick="openFinishBand({{ $band->id }}, '{{ addslashes($band->name) }}')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
                    إنهاء البند
                  </button>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2"><strong>الإجماليات للبنود</strong></td>
            <td class="num muted"><strong>{{ \App\Support\Money::format($project->bands->sum('client_price')) }}</strong><div class="muted" style="font-size:11px">متفق عليه</div></td>
            <td class="num"><strong>{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->actualClientTotal())) }}</strong><div class="muted" style="font-size:11px">فاتورة فعلية</div></td>
            <td class="num"><strong>{{ \App\Support\Money::format($project->bands->sum('labor_amount')) }}</strong><div class="muted" style="font-size:11px">مصنعية</div></td>
            <td class="num"><strong>{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->materialCost())) }}</strong><div class="muted" style="font-size:11px">مواد</div></td>
            <td class="num" style="color:{{ $project->bands->sum(fn($b) => $b->profit()) >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
              <strong>{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->profit())) }}</strong>
              <div class="muted" style="font-size:11px">الربح قبل الخصم</div>
            </td>
            <td></td>
          </tr>
          @if($project->totalDiscount() > 0)
            <tr>
              <td colspan="6" style="text-align: left; color: var(--amber)"><strong>الخصم الممنوح للعميل على المشروع</strong></td>
              <td class="num" style="color: var(--amber)"><strong>-{{ \App\Support\Money::format($project->totalDiscount()) }}</strong></td>
              <td></td>
            </tr>
            <tr>
              <td colspan="6" style="text-align: left;"><strong>صافي ربح البنود</strong></td>
              <td class="num" style="color:{{ ($project->bands->sum(fn($b) => $b->profit()) - $project->totalDiscount()) >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                <strong>{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->profit()) - $project->totalDiscount()) }}</strong>
              </td>
              <td></td>
            </tr>
          @endif
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

{{-- ═══ Financials Dashboard ═══ --}}
<div class="grid cols-3" style="margin-bottom:24px;gap:16px;align-items:start">
  
  {{-- Chart 1: Collection --}}
  <div class="card card-pad" style="padding:16px;height:100%">
    <div style="font-weight:700;font-size:14px;margin-bottom:4px">موقف تحصيل العميل</div>
    <div style="font-size:11px;color:var(--ink-3);margin-bottom:16px">إجمالي المحصّل مقابل المتبقي كمديونية</div>
    <div style="position:relative;width:100%;height:180px;display:flex;align-items:center;justify-content:center">
      <canvas id="collectionChart"></canvas>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:12px;font-size:12px">
      <div style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;border-radius:2px;background:#10b981"></span>محصّل</div>
      <div style="display:flex;align-items:center;gap:6px"><span style="width:10px;height:10px;border-radius:2px;background:#f43f5e"></span>متبقي</div>
    </div>
  </div>

  {{-- Chart 2: Cost Breakdown --}}
  <div class="card card-pad" style="padding:16px;height:100%">
    <div style="font-weight:700;font-size:14px;margin-bottom:4px">توزيع تكلفة المشروع</div>
    <div style="font-size:11px;color:var(--ink-3);margin-bottom:16px">المصروفات مقسمة حسب النوع</div>
    <div style="position:relative;width:100%;height:180px">
      <canvas id="costBreakdownChart"></canvas>
    </div>
  </div>

  {{-- Marketers Block --}}
  <div class="card card-pad" style="padding:16px;height:100%">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <div>
        <div style="font-weight:700;font-size:14px">عمولات المسوقين</div>
        <div style="font-size:11px;color:var(--ink-3)">المسوقين المشاركين في المشروع</div>
      </div>
      <div style="width:32px;height:32px;border-radius:8px;background:#f3e8ff;color:#8b5cf6;display:grid;place-items:center">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-users"/></svg>
      </div>
    </div>
    
    @if($projectMarketers->count())
      <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px">
        @foreach($projectMarketers as $pm)
          <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--line);padding-bottom:8px">
            <span style="font-weight:600;font-size:13px">{{ $pm->name }}</span>
            <span class="num" style="color:var(--pos);font-weight:700">{{ \App\Support\Money::format($pm->total) }} ج.م</span>
          </div>
        @endforeach
      </div>
      <div style="background:var(--surface);border:1px solid var(--line);border-radius:6px;padding:12px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
          <span style="font-size:12px;color:var(--ink-2)">إجمالي العمولات</span>
          <strong class="num" style="color:#8b5cf6">{{ \App\Support\Money::format($marketersCost) }} ج.م</strong>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:12px;color:var(--ink-2)">نسبة العموله ل تكلفة المشروع</span>
          @php
             $costPct = $totalCost > 0 ? round(($marketersCost / $totalCost) * 100, 1) : 0;
          @endphp
          <strong class="num" style="color:var(--ink)">{{ $costPct }}%</strong>
        </div>
      </div>
    @else
      <div style="height:140px;display:flex;align-items:center;justify-content:center;color:var(--ink-3);font-size:13px;background:var(--surface);border-radius:8px">
        لا يوجد مسوقين في هذا المشروع
      </div>
    @endif
  </div>

</div>

<div class="section-label" style="margin-top:0">إجراءات مالية وتحصيل</div>

{{-- اختيار طريقة الدفع: تحصيل جزء دلوقتي، أو عمل تقسيط بجدول سداد --}}
<div class="pay-choice">
  <button type="button" class="pay-opt" style="cursor:pointer;text-align:start;background:var(--surface);border:1px solid var(--line);width:100%" onclick="document.getElementById('quick-pay-modal').classList.add('open')">
    <div class="pay-opt-ic in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></div>
    <div class="pay-opt-body">
      <div class="pay-opt-t">العميل يدفع دفعة / جزء</div>
      <div class="pay-opt-s">سجّل تحصيل مبلغ من العميل دلوقتي من هنا مباشرة</div>
    </div>
    <svg class="pay-opt-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow"/></svg>
  </button>
  <a href="{{ route('installments.index') }}" class="pay-opt">
    <div class="pay-opt-ic amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></div>
    <div class="pay-opt-body">
      <div class="pay-opt-t">تقسيط بجدول سداد</div>
      <div class="pay-opt-s">اعمل عقد تقسيط بمقدم وأقساط شهرية من شاشة الأقساط</div>
    </div>
    <svg class="pay-opt-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow"/></svg>
  </a>
  <button type="button" class="pay-opt" style="cursor:pointer;text-align:start;background:var(--surface);border:1px solid var(--line);width:100%" onclick="document.getElementById('discount-modal').classList.add('open')">
    <div class="pay-opt-ic" style="color:#e11d48;background:#ffe4e6"><i class="fa fa-percent"></i></div>
    <div class="pay-opt-body">
      <div class="pay-opt-t">إضافة خصم للعميل</div>
      <div class="pay-opt-s">تقليل المديونية بمنح خصم جديد على المشروع</div>
    </div>
    <svg class="pay-opt-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow"/></svg>
  </button>
  <button type="button" class="pay-opt" style="cursor:pointer;text-align:start;background:var(--surface);border:1px solid var(--line);width:100%" onclick="document.getElementById('commission-modal').classList.add('open')">
    <div class="pay-opt-ic" style="color:#8b5cf6;background:#f3e8ff"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg></div>
    <div class="pay-opt-body">
      <div class="pay-opt-t">دفع عمولة لمسوق</div>
      <div class="pay-opt-s">دفع عمولة على المشروع وتسجيلها كمصروف</div>
    </div>
    <svg class="pay-opt-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow"/></svg>
  </button>
</div>

{{-- مودال تحصيل دفعة سريعة من العميل — من غير ما تسيب صفحة المشروع --}}
<div class="modal-overlay" id="quick-pay-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:460px">
    <div class="modal-head">
      <h4 style="margin:0">تحصيل دفعة من العميل</h4>
      <button class="btn ghost sm" onclick="document.getElementById('quick-pay-modal').classList.remove('open')">✕</button>
    </div>
    <form method="POST" action="{{ route('receivables.pay', $project) }}">
      @csrf
      <div class="modal-body">
          <div class="field">
            <label>المبلغ (ج.م) *</label>
            <input type="number" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" required autofocus>
          </div>
        <div class="row2">
          <div class="field">
            <label>التاريخ *</label>
            <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
          </div>
          <div class="field">
            <label>البند (اختياري)</label>
            <select name="band_id">
              <option value="">— دفعة عامة للمشروع —</option>
              @foreach($project->bands as $b)
                <option value="{{ $b->id }}" {{ old('band_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true])
        <div class="field">
          <label>ملاحظات</label>
          <input type="text" name="notes" value="{{ old('notes') }}" placeholder="اختياري">
        </div>
      </div>
      <div class="btn-row" style="padding:0 20px 20px">
        <button type="submit" class="btn pos">تسجيل التحصيل</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('quick-pay-modal').classList.remove('open')">إلغاء</button>
      </div>
    </form>
  </div>
</div>

{{-- مودال دفع عمولة لمسوق --}}
<div class="modal-overlay" id="commission-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:460px">
    <div class="modal-head">
      <h4 style="margin:0">دفع عمولة لمسوق</h4>
      <button class="btn ghost sm" onclick="document.getElementById('commission-modal').classList.remove('open')">✕</button>
    </div>
    <form method="POST" action="{{ route('projects.payCommission', $project) }}">
      @csrf
      <div class="modal-body">
          <div class="field">
            <label>المسوق *</label>
            <select name="marketer_id" required>
              <option value="">— اختر المسوق —</option>
              @foreach($marketers as $marketer)
                <option value="{{ $marketer->id }}">{{ $marketer->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label>المبلغ (ج.م) *</label>
            <input type="number" name="amount" min="0.01" step="0.01" required>
          </div>
        <div class="row2">
          <div class="field">
            <label>التاريخ *</label>
            <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required>
          </div>
        </div>
        @include('partials._wallet-select', ['wallets' => $wallets, 'required' => true])
        <div class="field">
          <label>ملاحظات</label>
          <input type="text" name="notes" placeholder="اختياري">
        </div>
      </div>
      <div class="btn-row" style="padding:0 20px 20px">
        <button type="submit" class="btn pos">تسجيل العمولة</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('commission-modal').classList.remove('open')">إلغاء</button>
      </div>
    </form>
  </div>
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

@php
  // كل الفلوس اللي العميل دفعها فعلًا — تحصيلات مباشرة + مقدم العقد (لو
  // موجود) + كل قسط اتحصّل — مرتبة الأحدث أولًا، بشكل "شيك" واحد بيبان فيه
  // نوع كل دفعة وتاريخها ومبلغها
  $directPays = $project->transactions->where('ref_type', 'client_payment')->map(fn ($t) => (object) [
    'date'   => $t->date,
    'amount' => (float) $t->amount + (float) $t->discount,
    'type'   => 'تحصيل مباشر',
    'note'   => $t->description,
  ]);
  $contractPays = collect();
  foreach ($project->contracts as $c) {
    foreach ($c->payments as $p) {
      $contractPays->push((object) [
        'date'   => $p->payment_date,
        'amount' => (float) $p->amount_paid + (float) $p->discount_applied,
        'type'   => 'قسط شهري',
        'note'   => $p->notes,
      ]);
    }
  }
  $allClientPays = $directPays->concat($contractPays)->sortByDesc(fn ($p) => $p->date)->values();
@endphp
@if($project->discounts->count())
  <div class="section-label" style="margin-top:0;color:var(--ink)">
    الخصومات الممنوحة للعميل
  </div>
  <div class="table-card" style="margin-bottom:24px;overflow:hidden">
    <div class="feed">
      @foreach($project->discounts->sortByDesc('date') as $disc)
        <div class="tx in" style="background:#fef2f2;border-color:#fecaca">
          <div class="tx-ic" style="color:var(--bad)"><i class="fa fa-percent"></i></div>
          <div class="tx-main">
            <div class="t" style="color:var(--bad)">خصم على إجمالي المشروع</div>
            <div class="s">
              <span>{{ $disc->date->format('Y-m-d') }}</span>
              @if($disc->notes)<span>— {{ $disc->notes }}</span>@endif
            </div>
          </div>
          <div class="tx-amt" style="color:var(--bad)">{{ \App\Support\Money::format($disc->amount) }} ج.م</div>
        </div>
      @endforeach
    </div>
    <div style="padding:12px 18px;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;background:var(--bg)">
      <strong>إجمالي الخصومات</strong>
      <strong class="tnum" style="color:var(--bad)">{{ \App\Support\Money::format($project->discounts->sum('amount')) }} ج.م</strong>
    </div>
  </div>
@endif

<div class="section-label" style="margin-top:0;">
  <span>سجل دفعات العميل بالتفصيل</span>
</div>
<div class="table-card" style="margin-bottom:24px;overflow:hidden">
  @if($allClientPays->isNotEmpty())
    <div class="feed">
      @foreach($allClientPays as $pay)
        <div class="tx in">
          <div class="tx-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></div>
          <div class="tx-main">
            <div class="t">{{ $pay->type }}</div>
            <div class="s">
              <span>{{ \Illuminate\Support\Carbon::parse($pay->date)->format('Y-m-d') }}</span>
              @if($pay->note)<span>— {{ $pay->note }}</span>@endif
            </div>
          </div>
          <div class="tx-amt">+{{ \App\Support\Money::format($pay->amount) }}</div>
        </div>
      @endforeach
    </div>
    <div style="padding:12px 18px;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;background:var(--bg)">
      <strong>إجمالي المحصّل</strong>
      <strong class="tnum" style="color:var(--pos)">{{ \App\Support\Money::format($allClientPays->sum('amount')) }} ج.م</strong>
    </div>
  @else
    <div class="empty-state"><h4>لسه مفيش دفعات مسجلة من العميل</h4></div>
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
      نثريات ومصروفات
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
  $bandPalette = [
    '#eff6ff', // أزرق فاتح
    '#f0fdf4', // أخضر فاتح
    '#fefce8', // أصفر فاتح
    '#fdf4ff', // بنفسجي فاتح
    '#fff7ed', // برتقالي فاتح
    '#f0fdfa', // زمردي فاتح
  ];
  $bandColorMap = [];
  $ci = 0;
  foreach ($project->materials->pluck('band_id')->unique() as $bId) {
      $bandColorMap[$bId ?? 'null'] = $bandPalette[$ci % count($bandPalette)];
      $ci++;
  }
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
@if($project->materials->count())
@php
  $matsByBand = $project->materials->sortByDesc('date')->groupBy('band_id');
  $totalNetCost = $project->materials->sum(fn($m) => $m->netCost());
@endphp
@foreach($matsByBand as $bId => $bandMats)
  @php
    $bObj = $bandMats->first()->band;
    $bColor = $bandColorMap[$bId === null ? 'null' : $bId] ?? '#f8fafc';
    $bNetCost = $bandMats->sum(fn($m) => $m->netCost());
  @endphp
  <div class="table-card mat-band-section" style="margin-bottom:14px">
    <div class="mat-band-header" style="background:{{ $bColor }}">
      <div style="display:flex;align-items:center;gap:10px">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-hardhat"/></svg>
        <strong>{{ $bObj?->name ?? 'خامات بدون بند' }}</strong>
        <span class="tag gray sm">{{ $bandMats->count() }} صنف</span>
      </div>
      <div class="tnum" style="font-weight:700">إجمالي: {{ \App\Support\Money::format($bNetCost) }} ج.م</div>
    </div>
    <div class="table-scroll">
      <table>
        <thead>
          <tr style="background:{{ $bColor }}">
            <th>الصنف</th>
            <th>المورد</th>
            <th class="num">الكمية</th>
            <th class="num"><span class="price-cost">سعر الشراء (تكلفة)</span></th>
            <th class="num"><span class="price-sell">سعر البيع</span></th>
            <th class="num">المرتجع</th>
            <th class="num">الإجمالي الصافي</th>
            <th>التاريخ</th>
            <th class="no-print"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($bandMats as $m)
            <tr class="mat-row" data-band="{{ $m->band_id }}">
              <td>
                <strong>{{ $m->item }}</strong>
                @if($m->isMisc())<span class="tag gray sm" style="margin-right:6px">نثريات ومصروفات</span>@endif
              </td>
              <td class="muted">{{ $m->supplier?->name ?? '—' }}</td>
              <td class="num">{{ number_format($m->qty, 1) }}</td>
              <td class="num price-cost">{{ \App\Support\Money::format($m->unit_price) }}</td>
              <td class="num price-sell">{{ \App\Support\Money::format($m->clientUnitPrice()) }}</td>
              <td class="num {{ $m->returnedQty() > 0 ? '' : 'muted' }}">{{ \App\Support\Money::format($m->returnedQty(), 1) }}</td>
              <td class="num"><strong>{{ \App\Support\Money::format($m->netCost()) }}</strong></td>
              <td class="muted">
                {{ $m->date->format('Y-m-d') }}
                @if($m->invoice_id)
                  <a href="{{ route('material_invoices.show', $m->invoice_id) }}" class="tag sm" style="margin-right:4px">فاتورة</a>
                @endif
              </td>
              <td class="no-print">
                @if(auth()->user()->isAdmin())
                  <button type="button" class="btn ghost danger sm" title="عكس الحركة" onclick="openItemDeleteModal('{{ route('materials.destroy', $m->id) }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
                  </button>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endforeach
{{-- إجمالي كل الخامات --}}
<div class="mat-total-strip">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-coins"/></svg>
  إجمالي تكلفة الخامات الصافية:
  <strong class="tnum">{{ \App\Support\Money::format($totalNetCost) }} ج.م</strong>
</div>
@else
  <div class="table-card" style="margin-bottom:24px">
    <div class="empty-state"><h4>لا توجد خامات مسجلة</h4></div>
  </div>
@endif
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
            <th class="num"><span class="price-cost">سعر الشراء</span></th>
            <th class="num">سعر المرتجع</th>
            <th class="num">الخسارة</th>
            <th>من عملية شراء بتاريخ</th>
            <th>ملاحظات</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($allReturns as $r)
            @php $rLoss = $r->loss(); @endphp
            <tr>
              <td class="muted">{{ $r->date->format('Y-m-d') }}</td>
              <td><strong>{{ $r->material->item }}</strong></td>
              <td class="num">{{ number_format($r->qty, 1) }}</td>
              <td class="num price-cost">{{ \App\Support\Money::format($r->material->unit_price) }}</td>
              <td class="num">{{ \App\Support\Money::format($r->effectivePrice()) }}</td>
              <td class="num" style="{{ $rLoss > 0 ? 'color:var(--neg);font-weight:700' : '' }}">{{ $rLoss > 0 ? \App\Support\Money::format($rLoss) : '—' }}</td>
              <td class="muted">{{ $r->material->date->format('Y-m-d') }} @if($r->material->supplier) — {{ $r->material->supplier->name }} @endif</td>
              <td class="muted">{{ $r->notes ?: '—' }}</td>
              <td>
                @if(auth()->user()->isAdmin())
                  <button type="button" class="btn ghost sm" style="color:var(--neg)" onclick="openItemDeleteModal('{{ route('returns.destroy', $r->id) }}')">عكس الحركة</button>
                @endif
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

@if(auth()->user()->isAdmin())
<div class="modal-overlay" id="item-delete-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-head">
      <h4 style="margin:0;color:var(--neg)">عكس الحركة</h4>
      <button class="btn ghost sm" onclick="document.getElementById('item-delete-modal').classList.remove('open')">✕</button>
    </div>
    <form id="item-delete-form" method="POST" onsubmit="return submitItemDelete(event)">
      @csrf @method('DELETE')
      <div class="modal-body">
        <p style="margin:0 0 14px">سيتم عكس أثر هذه الحركة بالكامل من التكاليف، المحفظة، والمديونيات.</p>
        <div class="field">
          <label style="color:var(--neg)">كلمة مرور المشرف للتأكيد *</label>
          <input type="password" name="current_password" id="item-delete-password" required autocomplete="current-password">
          <div id="item-delete-error" class="txn-pw-error" style="display:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
            <span></span>
          </div>
        </div>
      </div>
      <div class="btn-row" style="padding:0 20px 20px">
        <button type="submit" class="btn danger" id="item-delete-submit">تأكيد عكس الحركة</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('item-delete-modal').classList.remove('open')">إلغاء</button>
      </div>
    </form>
  </div>
</div>
</div>
@endif

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
                  <div style="font-size:12px">{{ rtrim(rtrim(number_format($row->worker->contract_qty, 2), '0'), '.') }} × {{ \App\Support\Money::format((float)$row->worker->contract_unit_rate) }}</div>
                @endif
              </td>
              <td class="num">{{ \App\Support\Money::format($row->worker->amount) }}</td>
              <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</td>
              <td class="num" style="color:{{ $remaining > 0 ? 'var(--amber, #b45309)' : 'var(--pos)' }}">{{ \App\Support\Money::format($remaining) }}</td>
              <td>
                <div style="display:flex; gap:4px">
                  <a href="{{ route('workers.payments', $row->worker) }}" class="btn ghost sm">الدفعات</a>
                  <button type="button" class="btn ghost sm" style="color:var(--warn,#c9821a)" onclick="openDiscountModal({{ $row->worker->id }}, '{{ htmlspecialchars($row->worker->name, ENT_QUOTES) }}', {{ $remaining }})">خصم</button>
                </div>
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
<div class="tab-panel" data-panel="reports" style="display:none">
@php
  $rptMatCost = $project->materials->sum(fn($m) => $m->netCost());
  $rptLaborCost = (float) $project->bands->sum('labor_amount');
  $rptTotalCost = $totalCost; // Uses $totalSpent() which includes marketers
  $rptClientTotal = $actualValue; 
  $rptProfit = $totalProfit; // Matches top card
  
  // Calculate percentage based on Net Client Total (Actual minus payment discounts)
  $netClientTotal = $project->actualClientTotal() - $project->paymentDiscounts();
  $rptProfitPct = $netClientTotal > 0 ? round($rptProfit / $netClientTotal * 100, 1) : 0;
  
  $rptCollected = $collected; // From controller
  $rptDue = $remaining; // From controller
  $rptCollectedPct = $netClientTotal > 0 ? min(100, round($rptCollected / $netClientTotal * 100)) : 0;

  // دونات: توزيع خامات/مصنعية/عمولات/ربح كنسبة من المشروع
  $donutSlices = collect([
    ['label' => 'خامات', 'value' => $rptMatCost, 'color' => '#f59e0b'],
    ['label' => 'مصنعية', 'value' => $rptLaborCost, 'color' => '#8b5cf6'],
    ['label' => 'عمولات', 'value' => $marketersCost, 'color' => '#ec4899'],
    ['label' => 'ربح', 'value' => max(0, $rptProfit), 'color' => '#16a34a'],
  ])->filter(fn ($s) => $s['value'] > 0);
  $donutTotal = $donutSlices->sum('value') ?: 1;
  $circumference = 2 * M_PI * 40;
  $donutOffset = 0;
  $donutSlices = $donutSlices->map(function ($s) use ($donutTotal, $circumference, &$donutOffset) {
    $frac = $s['value'] / $donutTotal;
    $len  = $frac * $circumference;
    $s['dash'] = round($len, 2) . ' ' . round($circumference - $len, 2);
    $s['dashoffset'] = round(-$donutOffset, 2);
    $donutOffset += $len;
    $s['pct'] = round($frac * 100, 1);
    return $s;
  });

  // دونات: توزيع أرباح المشروع (تجاري / إشراف)
  $rptTradeProfit = max(0, $project->tradeProfit());
  $rptPercentageProfit = max(0, $project->percentageProfit());
  $profitDonutSlices = collect([
    ['label' => 'ربح تجاري', 'value' => $rptTradeProfit, 'color' => '#3b82f6'],
    ['label' => 'ربح إشراف', 'value' => $rptPercentageProfit, 'color' => '#ec4899'],
  ])->filter(fn ($s) => $s['value'] > 0);
  $profitDonutTotal = $profitDonutSlices->sum('value') ?: 1;
  $profitDonutOffset = 0;
  $profitDonutSlices = $profitDonutSlices->map(function ($s) use ($profitDonutTotal, $circumference, &$profitDonutOffset) {
    $frac = $s['value'] / $profitDonutTotal;
    $len  = $frac * $circumference;
    $s['dash'] = round($len, 2) . ' ' . round($circumference - $len, 2);
    $s['dashoffset'] = round(-$profitDonutOffset, 2);
    $profitDonutOffset += $len;
    $s['pct'] = round($frac * 100, 1);
    return $s;
  });

  // مقارنة البنود: سعر العميل/التكلفة/الربح لكل بند، الأعلى قيمة أولًا
  $rptBandRows = $project->bands->map(fn ($b) => (object) [
    'name'   => $b->name,
    'client' => $b->actualClientTotal(),
    'cost'   => $b->totalCost(),
    'profit' => $b->profit(),
  ])->sortByDesc('client')->values();
  $maxBandClient = $rptBandRows->max('client') ?: 1;

  // الصنايعية: كل فني ونسبة تحصيله، الأعلى تعاقدًا أولًا
  $rptWorkers = $project->bands->flatMap(fn ($b) => $b->workers)->sortByDesc('amount')->values();

  // ── تحليل الخامات: أعلى الأصناف بالتكلفة (خامات حقيقية فقط، بدون نثريات) ──
  $realMaterials = $project->materials->where('category', '!=', 'misc');
  $rptTopMaterials = $realMaterials
    ->groupBy('item')
    ->map(fn ($g) => (object) [
      'item' => $g->first()->item,
      'qty'  => $g->sum(fn ($m) => $m->netQty()),
      'cost' => $g->sum(fn ($m) => $m->netCost()),
    ])
    ->sortByDesc('cost')->take(8)->values();
  $rptMaxMatCost = $rptTopMaterials->max('cost') ?: 1;

  // ── تحليل الموردين: توزيع المشتريات (الصافي) على الموردين ──
  $rptBySupplier = $project->materials
    ->groupBy(fn ($m) => $m->supplier?->name ?? 'بدون مورد')
    ->map(fn ($g, $name) => (object) [
      'name'  => $name,
      'count' => $g->count(),
      'cost'  => $g->sum(fn ($m) => $m->netCost()),
    ])
    ->sortByDesc('cost')->values();
  $rptMaxSupplierCost = $rptBySupplier->max('cost') ?: 1;

  // ── تحليل المرتجعات: الكمية والقيمة والخسائر ──
  $rptReturns = $project->materials->flatMap->returns;
  $rptReturnsCount = $rptReturns->count();
  $rptReturnsValue = $rptReturns->sum(fn ($r) => (float) $r->qty * $r->effectivePrice());
  $rptReturnsLoss  = $rptReturns->sum(fn ($r) => $r->loss());
  $rptTopReturns = $project->materials
    ->filter(fn ($m) => $m->returns->isNotEmpty())
    ->map(fn ($m) => (object) [
      'item' => $m->item,
      'qty'  => $m->returnedQty(),
      'value'=> $m->returns->sum(fn ($r) => (float) $r->qty * $r->effectivePrice()),
      'loss' => $m->returns->sum(fn ($r) => $r->loss()),
    ])
    ->sortByDesc('value')->take(6)->values();
@endphp

<div class="section-label" style="margin-top:0">نظرة عامة</div>
<div class="grid cols-4" style="margin-bottom:24px">
  <div class="vstat vstat-blue">
    <div class="top"><span class="label">إجمالي قيمة المشروع</span></div>
    <div class="val tnum">{{ \App\Support\Money::format($rptClientTotal) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-amber">
    <div class="top"><span class="label">إجمالي التكلفة</span></div>
    <div class="val tnum">{{ \App\Support\Money::format($rptTotalCost) }} <small>ج.م</small></div>
  </div>
  <div class="vstat {{ $rptProfit >= 0 ? 'vstat-green' : 'vstat-red' }}">
    <div class="top"><span class="label">الربح</span></div>
    <div class="val tnum">{{ \App\Support\Money::format($rptProfit) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-navy">
    <div class="top"><span class="label">نسبة الربح</span></div>
    <div class="val tnum">{{ $rptProfitPct }}%</div>
  </div>
</div>

<div class="grid cols-3" style="margin-bottom:24px;align-items:start">
  <div class="table-card" style="padding:20px">
    <div class="section-label" style="margin-top:0">توزيع تكلفة المشروع</div>
    @if($donutSlices->isNotEmpty())
      <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;justify-content:center">
        <svg width="140" height="140" viewBox="0 0 100 100" style="transform:rotate(-90deg);flex-shrink:0">
          <circle cx="50" cy="50" r="40" fill="none" stroke="var(--line)" stroke-width="16"/>
          @foreach($donutSlices as $slice)
            <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $slice['color'] }}" stroke-width="16"
              stroke-dasharray="{{ $slice['dash'] }}" stroke-dashoffset="{{ $slice['dashoffset'] }}" stroke-linecap="butt"/>
          @endforeach
        </svg>
        <div style="display:flex;flex-direction:column;gap:8px;min-width:130px">
          @foreach($donutSlices as $slice)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px">
              <span style="width:10px;height:10px;border-radius:3px;background:{{ $slice['color'] }};flex-shrink:0"></span>
              <span>{{ $slice['label'] }}</span>
              <strong style="margin-inline-start:auto">{{ $slice['pct'] }}%</strong>
            </div>
          @endforeach
        </div>
      </div>
    @else
      <p class="muted">لا توجد بيانات كافية بعد</p>
    @endif
  </div>

  <div class="table-card" style="padding:20px">
    <div class="section-label" style="margin-top:0">توزيع أرباح المشروع</div>
    @if($profitDonutSlices->isNotEmpty())
      <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;justify-content:center">
        <svg width="140" height="140" viewBox="0 0 100 100" style="transform:rotate(-90deg);flex-shrink:0">
          <circle cx="50" cy="50" r="40" fill="none" stroke="var(--line)" stroke-width="16"/>
          @foreach($profitDonutSlices as $slice)
            <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $slice['color'] }}" stroke-width="16"
              stroke-dasharray="{{ $slice['dash'] }}" stroke-dashoffset="{{ $slice['dashoffset'] }}" stroke-linecap="butt"/>
          @endforeach
        </svg>
        <div style="display:flex;flex-direction:column;gap:8px;min-width:130px">
          @foreach($profitDonutSlices as $slice)
            <div style="display:flex;align-items:center;gap:8px;font-size:13px">
              <span style="width:10px;height:10px;border-radius:3px;background:{{ $slice['color'] }};flex-shrink:0"></span>
              <span>{{ $slice['label'] }}</span>
              <strong style="margin-inline-start:auto">{{ $slice['pct'] }}%</strong>
            </div>
          @endforeach
        </div>
      </div>
    @else
      <p class="muted">لا توجد أرباح مسجلة بعد</p>
    @endif
  </div>

  <div class="table-card" style="padding:20px">
    <div class="section-label" style="margin-top:0">نسبة التحصيل من العميل</div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px">
      <span class="muted">محصّل: <strong style="color:var(--pos)">{{ \App\Support\Money::format($rptCollected) }}</strong></span>
      <span class="muted">متبقي: <strong style="color:var(--warn)">{{ \App\Support\Money::format($rptDue) }}</strong></span>
    </div>
    <div class="bar-track" style="height:14px;border-radius:8px">
      <div class="bar-fill {{ $rptCollectedPct >= 100 ? 'full' : '' }}" style="width:{{ $rptCollectedPct }}%;border-radius:8px"></div>
    </div>
    <div style="text-align:center;margin-top:10px;font-weight:800;font-size:22px;color:var(--accent)">{{ $rptCollectedPct }}%</div>
  </div>
</div>

<div class="table-card" style="padding:20px;margin-bottom:24px">
  <div class="section-label" style="margin-top:0">مقارنة البنود (سعر العميل مقابل التكلفة)</div>
  @forelse($rptBandRows as $row)
    <div style="margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;flex-wrap:wrap;gap:4px">
        <strong>{{ $row->name }}</strong>
        <span class="muted">سعر العميل: {{ \App\Support\Money::format($row->client) }} · تكلفة: {{ \App\Support\Money::format($row->cost) }} · ربح:
          <span style="color:{{ $row->profit >= 0 ? 'var(--pos)' : 'var(--neg)' }};font-weight:700">{{ \App\Support\Money::format($row->profit) }}</span>
        </span>
      </div>
      <div class="bar-track">
        <div class="bar-fill" style="width:{{ $maxBandClient > 0 ? round($row->client / $maxBandClient * 100) : 0 }}%;background:var(--accent)"></div>
      </div>
    </div>
  @empty
    <p class="muted">لا توجد بنود بعد</p>
  @endforelse
</div>

<div class="table-card" style="padding:20px">
  <div class="section-label" style="margin-top:0">حالة تحصيل الصنايعية</div>
  @forelse($rptWorkers as $w)
    @php $wPaid = $w->paidTotal(); $wRem = $w->remaining(); @endphp
    <div style="margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;flex-wrap:wrap;gap:4px">
        <strong>{{ $w->name }}</strong>
        <span class="muted">متعاقد: {{ \App\Support\Money::format($w->amount) }} · مدفوع:
          <span style="color:var(--pos);font-weight:700">{{ \App\Support\Money::format($wPaid) }}</span> · متبقي:
          <span style="color:{{ $wRem > 0 ? 'var(--warn)' : 'var(--pos)' }};font-weight:700">{{ \App\Support\Money::format($wRem) }}</span>
        </span>
      </div>
      <div class="bar-track">
        <div class="bar-fill {{ $wRem <= 0 ? 'full' : '' }}" style="width:{{ (float) $w->amount > 0 ? round($wPaid / (float) $w->amount * 100) : 0 }}%;background:var(--pos)"></div>
      </div>
    </div>
  @empty
    <p class="muted">لا يوجد صنايعية بعد</p>
  @endforelse
</div>

{{-- ═══ تحليل الخامات + الموردين ═══ --}}
<div class="grid cols-2" style="margin-top:24px;align-items:start">
  <div class="table-card" style="padding:20px">
    <div class="section-label" style="margin-top:0">أعلى الخامات تكلفة</div>
    @forelse($rptTopMaterials as $m)
      <div style="margin-bottom:12px">
        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;flex-wrap:wrap;gap:4px">
          <strong>{{ $m->item }}</strong>
          <span class="muted">{{ number_format($m->qty, 1) }} · <span class="price-cost" style="font-weight:700">{{ \App\Support\Money::format($m->cost) }} ج.م</span></span>
        </div>
        <div class="bar-track">
          <div class="bar-fill" style="width:{{ round($m->cost / $rptMaxMatCost * 100) }}%;background:var(--warn)"></div>
        </div>
      </div>
    @empty
      <p class="muted">لا توجد خامات مسجّلة</p>
    @endforelse
  </div>

  <div class="table-card" style="padding:20px">
    <div class="section-label" style="margin-top:0">توزيع المشتريات على الموردين</div>
    @forelse($rptBySupplier as $s)
      <div style="margin-bottom:12px">
        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;flex-wrap:wrap;gap:4px">
          <strong>{{ $s->name }}</strong>
          <span class="muted">{{ $s->count }} {{ $s->count === 1 ? 'صنف' : 'أصناف' }} · <span style="font-weight:700;color:var(--accent-ink)">{{ \App\Support\Money::format($s->cost) }} ج.م</span></span>
        </div>
        <div class="bar-track">
          <div class="bar-fill" style="width:{{ round($s->cost / $rptMaxSupplierCost * 100) }}%;background:var(--accent)"></div>
        </div>
      </div>
    @empty
      <p class="muted">لا يوجد موردون مسجّلون</p>
    @endforelse
  </div>
</div>

{{-- ═══ تحليل المرتجعات ═══ --}}
<div class="table-card" style="padding:20px;margin-top:24px">
  <div class="section-label" style="margin-top:0">تحليل المرتجعات</div>
  @if($rptReturnsCount > 0)
    <div class="grid cols-3" style="margin-bottom:16px">
      <div class="card stat"><div class="top"><span class="label">عدد المرتجعات</span></div><div class="val tnum">{{ $rptReturnsCount }}</div></div>
      <div class="card stat"><div class="top"><span class="label">قيمة المرتجعات</span></div><div class="val tnum">{{ \App\Support\Money::format($rptReturnsValue) }} <small>ج.م</small></div></div>
      <div class="card stat" style="{{ $rptReturnsLoss > 0 ? 'border:1.5px solid var(--neg)' : '' }}"><div class="top"><span class="label">خسائر المرتجعات</span></div><div class="val tnum" style="color:{{ $rptReturnsLoss > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($rptReturnsLoss) }} <small>ج.م</small></div></div>
    </div>
    <div class="table-scroll">
      <table>
        <thead><tr><th>الصنف</th><th class="num">الكمية المرتجعة</th><th class="num">القيمة</th><th class="num">الخسارة</th></tr></thead>
        <tbody>
          @foreach($rptTopReturns as $r)
            <tr>
              <td><strong>{{ $r->item }}</strong></td>
              <td class="num">{{ number_format($r->qty, 1) }}</td>
              <td class="num">{{ \App\Support\Money::format($r->value) }}</td>
              <td class="num" style="{{ $r->loss > 0 ? 'color:var(--neg);font-weight:700' : '' }}">{{ $r->loss > 0 ? \App\Support\Money::format($r->loss) : '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <p class="muted">لا توجد مرتجعات على هذا المشروع</p>
  @endif
</div>
</div>{{-- /tab-panel: reports --}}
@endif

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
                <td>مجموع البنود</td>
                <td class="num">{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->actualClientTotal())) }}</td>
                <td class="num">{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->totalCost())) }}</td>
                <td class="num" style="color:{{ $project->bands->sum(fn($b) => $b->profit()) >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($project->bands->sum(fn($b) => $b->profit())) }}</td>
              </tr>
              @php
                $genMatCount = $project->generalMaterials()->count();
                $genMatClientTotal = $project->generalMaterials()->sum(fn($m) => $m->netClientCost());
                $genMatCost = $project->generalMaterials()->sum(fn($m) => $m->netCost());
                $genMatProfit = $project->generalMaterials()->sum(fn($m) => $m->percentageProfit() + $m->tradeProfit());
              @endphp
              @if($genMatCount > 0)
                <tr>
                  <td>نثريات وخامات عامة</td>
                  <td class="num">{{ \App\Support\Money::format($genMatClientTotal) }}</td>
                  <td class="num">{{ \App\Support\Money::format($genMatCost) }}</td>
                  <td class="num" style="color:{{ $genMatProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($genMatProfit) }}</td>
                </tr>
              @endif
              @php $installmentInterest = $project->contracts()->get()->sum(fn($c) => $c->interestAmount()); @endphp
              @if($installmentInterest > 0)
                <tr>
                  <td>فائدة التقسيط</td>
                  <td class="num">{{ \App\Support\Money::format($installmentInterest) }}</td>
                  <td class="num">0.00</td>
                  <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($installmentInterest) }}</td>
                </tr>
              @endif
              @if($project->totalDiscount() > 0)
                <tr>
                  <td colspan="3" style="text-align:left; color: var(--amber)">الخصومات الممنوحة للعميل</td>
                  <td class="num" style="color: var(--amber)">-{{ \App\Support\Money::format($project->totalDiscount()) }}</td>
                </tr>
              @endif
              <tr style="border-top: 2px solid #ddd;">
                <td><strong>الصافي الكلي للمشروع</strong></td>
                <td class="num"><strong>{{ \App\Support\Money::format($project->actualClientTotal() + $project->totalDiscount()) }}</strong></td>
                <td class="num"><strong>{{ \App\Support\Money::format($project->totalSpent()) }}</strong></td>
                <td class="num" style="color:{{ $totalProfit >= 0 ? 'var(--pos)' : 'var(--neg)' }}"><strong>{{ \App\Support\Money::format($totalProfit) }}</strong></td>
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

{{-- Modal: إنهاء البند --}}
@if(auth()->user()->canManage())
<div class="modal-overlay" id="finish-band-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-head">
      <h4 style="margin:0">إنهاء البند</h4>
      <button class="btn ghost sm" onclick="document.getElementById('finish-band-modal').classList.remove('open')">✕</button>
    </div>
    <div class="modal-body">
      <p style="margin:0 0 8px">هل أنت متأكد من إنهاء البند <strong>"<span id="finish-band-name"></span>"</strong> بالكامل؟</p>
      <p class="muted" style="font-size:13px;margin:0 0 20px">بعد الإنهاء يظهر البند باللون الأخضر ويُعتبر منتهيًا.</p>
      <form id="finish-band-form" method="POST">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="done">
        <div class="btn-row">
          <button type="submit" class="btn pos">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
            نعم، أنهِ البند
          </button>
          <button type="button" class="btn ghost" onclick="document.getElementById('finish-band-modal').classList.remove('open')">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@push('scripts')
<script>
function openFinishBand(bandId, bandName) {
  document.getElementById('finish-band-name').textContent = bandName;
  document.getElementById('finish-band-form').action = '/bands/' + bandId + '/status';
  document.getElementById('finish-band-modal').classList.add('open');
}

function switchProjectTab(name) {
  document.querySelectorAll('#project-tabs .tab').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = p.dataset.panel === name ? '' : 'none');
  
  if (name === 'installments' && typeof initFinancialCharts === 'function') {
    initFinancialCharts();
  }
}

@if($errors->any() && old('amount') !== null)
  switchProjectTab('installments');
  document.getElementById('quick-pay-modal').classList.add('open');
@endif

function openItemDeleteModal(actionUrl) {
  document.getElementById('item-delete-form').action = actionUrl;
  document.getElementById('item-delete-password').value = '';
  document.getElementById('item-delete-error').style.display = 'none';
  document.getElementById('item-delete-modal').classList.add('open');
}

function playAlarmSound() {
  try {
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (! Ctx) return;
    const ctx = new Ctx();
    const now = ctx.currentTime;
    [[880, 0], [660, 0.16]].forEach(([freq, offset]) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'square';
      osc.frequency.setValueAtTime(freq, now + offset);
      gain.gain.setValueAtTime(0.16, now + offset);
      gain.gain.exponentialRampToValueAtTime(0.001, now + offset + 0.15);
      osc.connect(gain).connect(ctx.destination);
      osc.start(now + offset);
      osc.stop(now + offset + 0.16);
    });
  } catch (e) {}
}

function shakeModal(box) {
  box.classList.remove('shake-error');
  void box.offsetWidth;
  box.classList.add('shake-error');
}

async  function confirmBandDeletion(id) {
    if(confirm('متأكد إنك عايز تحذف البند ده؟ كل الخامات ودفعات الصنايعية اللي فيه هتحذف!')) {
      document.getElementById('delete-band-' + id).submit();
    }
  }

  // --- Modal for direct discount ---
  function openDiscountModal(workerId, workerName, remaining) {
    document.getElementById('discModalWorkerName').textContent = workerName;
    document.getElementById('discModalRemaining').textContent = remaining + ' ج.م';
    // Update form action dynamically
    document.getElementById('discountForm').action = "/workers/" + workerId + "/payments";
    document.getElementById('discountModal').classList.add('open');
  }
  function closeDiscountModal() {
    document.getElementById('discountModal').classList.remove('open');
  }

async function submitItemDelete(evt) {
  evt.preventDefault();
  const form = evt.target;
  const submitBtn = document.getElementById('item-delete-submit');
  const errorBox = document.getElementById('item-delete-error');
  const errorSpan = errorBox.querySelector('span');
  const passwordInput = document.getElementById('item-delete-password');

  errorBox.style.display = 'none';
  submitBtn.disabled = true;

  try {
    const res = await fetch(form.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: new FormData(form),
    });

    if (res.ok) {
      window.location.reload();
      return false;
    }

    const data = await res.json().catch(() => ({}));
    const msg = data?.errors?.current_password?.[0] || data?.message || 'حصل خطأ — حاول تاني.';
    errorSpan.textContent = msg;
    errorBox.style.display = 'flex';
    playAlarmSound();
    shakeModal(form.closest('.modal-box'));
    passwordInput.value = '';
    passwordInput.focus();
  } catch (e) {
    errorSpan.textContent = 'حصل خطأ في الاتصال — حاول تاني.';
    errorBox.style.display = 'flex';
  } finally {
    submitBtn.disabled = false;
  }
  return false;
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

{{-- Modal for Project Discount --}}
<div class="modal-overlay" id="discount-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-head">
      <h4 style="margin:0">منح خصم للعميل</h4>
      <button class="btn ghost sm" onclick="document.getElementById('discount-modal').classList.remove('open')">✕</button>
    </div>
    <form method="POST" action="{{ route('projects.discount', $project) }}">
      @csrf
      <div class="modal-body">
        <p style="margin:0 0 14px;color:var(--mut);font-size:.85rem">الخصم هيتسجل في حساب العميل ويقلل المتبقي عليه على طول.</p>
        <div class="field">
          <label>مبلغ الخصم (ج.م) *</label>
          <input type="number" step="0.01" min="0.01" name="amount" required>
        </div>
        <div class="field">
          <label>تاريخ الخصم *</label>
          <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required>
        </div>
        <div class="field">
          <label>ملاحظات</label>
          <input type="text" name="notes">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn ghost" onclick="document.getElementById('discount-modal').classList.remove('open')">إلغاء</button>
        <button type="submit" class="btn">إضافة الخصم</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal for direct discount --}}
<div class="rv-modal" id="discountModal" onclick="if(event.target===this) closeDiscountModal()">
  <div class="rv-card" style="max-width:400px;margin:20px;background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.1);padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #eee;padding-bottom:12px">
      <h3 style="margin:0;font-size:1.1rem">تسجيل خصم للصنايعي</h3>
      <button type="button" class="btn ghost sm" onclick="closeDiscountModal()" style="padding:4px 8px"><i class="fa fa-times"></i></button>
    </div>
    <form method="POST" action="" id="discountForm">
      @csrf
      <input type="hidden" name="amount" value="0">
      <div style="margin-bottom:12px; font-size:13px">
        <strong>الصنايعي:</strong> <span id="discModalWorkerName"></span><br>
        <strong>المتبقي عليه:</strong> <span id="discModalRemaining" style="color:var(--warn,#c9821a); font-weight:bold"></span>
      </div>
      <div class="field" style="margin-bottom:12px">
        <label>قيمة الخصم (ج.م) *</label>
        <input type="number" name="discount" step="0.01" min="0.01" required placeholder="مثال: 500" style="width:100%">
      </div>
      <div class="field" style="margin-bottom:12px">
        <label>سبب الخصم *</label>
        <input type="text" name="discount_reason" required placeholder="مثال: غياب / تأخير / خطأ في الشغل" style="width:100%">
      </div>
      <div class="field" style="margin-bottom:16px">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required style="width:100%">
      </div>
      <div style="text-align:left">
        <button type="button" class="btn ghost" onclick="closeDiscountModal()">إلغاء</button>
        <button type="submit" class="btn" style="background:var(--warn,#c9821a); border-color:var(--warn,#c9821a); color:#fff">تسجيل الخصم</button>
      </div>
    </form>
  </div>
</div>

<style>
.rv-modal { position:fixed; inset:0; z-index:1060; display:none; align-items:center; justify-content:center; background:rgba(15,23,42,.55); }
.rv-modal.open { display:flex; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
let chartsInitialized = false;

function initFinancialCharts() {
  if (chartsInitialized) return;
  chartsInitialized = true;

  Chart.defaults.font.family = "'IBM Plex Sans Arabic', system-ui, sans-serif";
  const fmt = v => Number(v).toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0});

  // Collection Chart (Doughnut)
  const collectionCtx = document.getElementById('collectionChart');
  if (collectionCtx) {
    const collected = {{ $collected ?: 0 }};
    const remaining = {{ $remaining ?: 0 }};
    const hasData = collected > 0 || remaining > 0;
    
    new Chart(collectionCtx, {
      type: 'doughnut',
      data: {
        labels: hasData ? ['محصّل', 'متبقي'] : ['لا توجد مبالغ'],
        datasets: [{
          data: hasData ? [collected, remaining] : [1],
          backgroundColor: hasData ? ['#10b981', '#f43f5e'] : ['#e2e8f0'],
          borderWidth: 2,
          borderColor: '#fff',
          hoverOffset: hasData ? 4 : 0
        }]
      },
      options: {
        cutout: '65%',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: { 
              label: ctx => {
                if (!hasData) return ' صفر ج.م';
                return ' ' + ctx.label + ': ' + fmt(ctx.raw) + ' ج.م';
              }
            }
          }
        }
      }
    });
  }

  // Cost Breakdown Chart (Bar)
  const costCtx = document.getElementById('costBreakdownChart');
  if (costCtx) {
    new Chart(costCtx, {
      type: 'bar',
      data: {
        labels: ['خامات', 'مصنعيات', 'نثريات', 'عمولات'],
        datasets: [{
          label: 'التكلفة',
          data: [{{ $bandMaterialsCost ?: 0 }}, {{ $laborCost ?: 0 }}, {{ $generalMaterialsCost ?: 0 }}, {{ $marketersCost ?: 0 }}],
          backgroundColor: ['#4f46e5', '#f59e0b', '#06b6d4', '#8b5cf6'],
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: { label: ctx => ' ' + fmt(ctx.raw) + ' ج.م' }
          }
        },
        scales: {
          x: { grid: { display: false }, ticks: { font: { size: 10 } } },
          y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 }, callback: v => fmt(v) } }
        }
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', function() {
  // If the tab is active on load, initialize immediately
  const activeTab = document.querySelector('#project-tabs .tab.active');
  if (activeTab && activeTab.dataset.tab === 'installments') {
    initFinancialCharts();
  }
});
</script>

<form id="change-status-form" method="POST" action="{{ route('projects.changeStatus', $project) }}" style="display:none;">
  @csrf
  <input type="hidden" name="status" id="change-status-input">
</form>

<script>
  function changeProjectStatus(status) {
    let msg = '';
    if(status === 'active') msg = 'هل أنت متأكد من إعادة تنشيط المشروع؟';
    if(status === 'done') msg = 'هل أنت متأكد من إنهاء المشروع؟';
    if(status === 'suspended') msg = 'هل أنت متأكد من تعليق المشروع؟';
    if(status === 'canceled') msg = 'هل أنت متأكد من إلغاء المشروع؟';
    
    if(confirm(msg)) {
      document.getElementById('change-status-input').value = status;
      document.getElementById('change-status-form').submit();
    }
  }
</script>

@endpush
@endsection
