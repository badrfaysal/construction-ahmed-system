@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')

{{-- Summary stats row — كروت متدرّجة ملوّنة (بروح المركز المالي في نظام 1) --}}
<div class="grid cols-5" style="margin-bottom:20px">

  {{-- رأس مال مشروع المقاولات — السيولة + كل المستحقات (مباشر وعبر تقسيط) − الديون --}}
  <div class="vstat vstat-navy">
    <div class="top">
      <span class="label">رأس مال المقاولات</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($netCapital) }} <small>ج.م</small></div>
    <div class="cap-breakdown">
      <div><span>سيولة</span><b>{{ \App\Support\Money::format($walletBalance) }}</b></div>
      <div><span>ديون −</span><b>{{ \App\Support\Money::format($supplierDebtsRemaining) }}</b></div>
      <div><span>مستحق مباشر</span><b>{{ \App\Support\Money::format($directReceivables) }}</b></div>
      <div><span>مستحق تقسيط</span><b>{{ \App\Support\Money::format($installmentReceivables) }}</b></div>
    </div>
  </div>

  <div class="vstat vstat-blue">
    <div class="top">
      <span class="label">المشاريع الجارية</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      </span>
    </div>
    <div class="val">{{ $activeProjects->count() }}</div>
    <div class="note">{{ $activeProjects->pluck('name')->join(' · ') ?: 'لا توجد مشاريع جارية' }}</div>
  </div>

  <div class="vstat vstat-teal">
    <div class="top">
      <span class="label">إجمالي المحصّل</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalCollected) }} <small>ج.م</small></div>
    <div class="note">من إجمالي {{ \App\Support\Money::format($totalContract) }} ج.م</div>
  </div>

  @php $walletIsAdmin = auth()->user()->isAdmin(); @endphp
  <{{ $walletIsAdmin ? 'a' : 'div' }} class="vstat {{ $walletBalance >= 0 ? 'vstat-green' : 'vstat-red' }}" @if($walletIsAdmin) href="{{ route('wallet.index') }}" style="text-decoration:none" @endif>
    <div class="top">
      <span class="label">محفظة المقاولات</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ number_format($walletBalance, 2) }} <small>ج.م</small></div>
    <div class="note">{{ $walletIsAdmin ? 'دوس لإدارة المحفظة والحركات اليدوية' : 'الرصيد الفعلي — كل مصروف ودفعة بيتحدّث فيها تلقائيًا' }}</div>
  </{{ $walletIsAdmin ? 'a' : 'div' }}>

  <a class="vstat vstat-amber" href="{{ route('alerts.index') }}">
    <div class="top">
      <span class="label">أقساط مستحقة</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      </span>
    </div>
    <div class="val">{{ $overdueCount }}</div>
    <div class="note">{{ $overdueCount > 0 ? 'تحتاج متابعة عاجلة — اضغط للتفاصيل' : 'لا توجد أقساط متأخرة' }}</div>
  </a>

</div>

{{-- محتوى رئيسي (المشاريع الجارية) + عمود جانبي (روابط سريعة + آخر الحركات) --}}
<div class="dash-layout">

  {{-- ═══ العمود الرئيسي ═══ --}}
  <div>
    @if($activeProjects->count())
      <div class="section-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
        المشاريع الجارية
      </div>
      <div class="pcards">
        @foreach($activeProjects as $p)
          @php
            $prog   = $p->progressPct();
            $paid   = $p->totalCollected();
            $total  = $p->initialContractValue();
            $actual = $p->actualClientTotal();
            $due    = $total - $paid;
            $activeBand = $p->bands->where('status', 'active')->first();
            $paidWorkers = $p->bands->flatMap(fn($b) => $b->workers)->sum(fn($w) => $w->paidTotal());
          @endphp
          <a class="pcard" href="{{ route('projects.show', $p) }}">
            <div class="pc-band"></div>
            <div class="pc-body">
              <div class="pc-head">
                <div>
                  <div class="pc-name">{{ $p->name }}</div>
                  <div class="pc-client">{{ $p->client->name }}</div>
                </div>
                @if($activeBand)
                  <span class="tag blue"><span class="dot"></span>{{ $activeBand->name }}</span>
                @else
                  <span class="tag gray">قيد الإعداد</span>
                @endif
              </div>
              @if($p->address)
                <div class="pc-addr">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pin"/></svg>
                  {{ $p->address }}
                </div>
              @endif
              <div class="pc-prog">
                <span class="muted" style="font-size:11px">الإنجاز</span>
                <div class="bar-track"><div class="bar-fill" style="width:{{ $prog }}%"></div></div>
                <span class="pct">{{ $prog }}%</span>
              </div>
              @if($p->bands->count())
                <div class="pc-bands">
                  @foreach($p->bands as $band)
                    <span class="tag {{ $band->status === 'done' ? 'green' : ($band->status === 'active' ? 'blue' : 'gray') }} sm">
                      @if($band->status === 'done') ✓ @endif{{ $band->name }}
                    </span>
                  @endforeach
                </div>
              @endif
              <div class="pc-fin">
                <div>
                  <div class="l">قيمة التعاقد</div>
                  <div class="v">{{ \App\Support\Money::format($total) }}</div>
                  @include('partials._actual-vs-initial', ['initial' => $total, 'actual' => $actual])
                </div>
                <div>
                  <div class="l">محصّل</div>
                  <div class="v" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</div>
                </div>
                <div>
                  <div class="l">متبقي</div>
                  <div class="v" style="color:var(--warn)">{{ \App\Support\Money::format($due) }}</div>
                </div>
              </div>
              <div class="pc-pays">
                <div class="pc-pay in">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
                  <span class="l">دفعات العميل</span>
                  <span class="v">{{ \App\Support\Money::format($paid) }}</span>
                </div>
                <div class="pc-pay out">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
                  <span class="l">مدفوع للصنايعية</span>
                  <span class="v">{{ \App\Support\Money::format($paidWorkers) }}</span>
                </div>
              </div>
            </div>
          </a>
        @endforeach
      </div>
    @else
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
        <h4>لا توجد مشاريع جارية</h4>
        <p>ابدأ بإضافة مشروع جديد لمتابعته هنا</p>
      </div>
    @endif
  </div>

  {{-- ═══ العمود الجانبي ═══ --}}
  <div>

    {{-- روابط سريعة --}}
    <div class="card card-pad" style="margin-bottom:16px">
      <div class="section-label" style="margin:0 0 14px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-send"/></svg>
        روابط سريعة
      </div>
      <div class="qlinks">
        <a class="qlink" href="{{ route('projects.create') }}">
          <span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg></span>
          <span class="lbl">مشروع جديد</span>
        </a>
        <a class="qlink" href="{{ route('materials.create') }}">
          <span class="ic ic-teal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg></span>
          <span class="lbl">تسجيل خامة</span>
        </a>
        <a class="qlink" href="{{ route('receivables.index') }}">
          <span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span>
          <span class="lbl">تحصيل عميل</span>
        </a>
        <a class="qlink" href="{{ route('quotes.create') }}">
          <span class="ic ic-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg></span>
          <span class="lbl">عرض سعر جديد</span>
        </a>
        <a class="qlink" href="{{ route('installments.index') }}">
          <span class="ic ic-gold"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span>
          <span class="lbl">الأقساط</span>
        </a>
        @if($walletIsAdmin)
          <a class="qlink" href="{{ route('wallet.index') }}">
            <span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg></span>
            <span class="lbl">المحفظة</span>
          </a>
        @else
          <a class="qlink" href="{{ route('transactions.index') }}">
            <span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg></span>
            <span class="lbl">سجل الحركات</span>
          </a>
        @endif
      </div>
    </div>

    {{-- آخر الحركات --}}
    <div class="card" style="overflow:hidden">
      <div class="table-top">
        <h4>آخر الحركات</h4>
        <a href="{{ route('transactions.index') }}" class="btn ghost sm">عرض الكل</a>
      </div>
      @if($recentTransactions->count())
        <div class="feed feed-compact">
          @foreach($recentTransactions as $tx)
            <div class="tx {{ $tx->direction }}">
              <div class="tx-ic">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <use href="{{ $tx->direction === 'in' ? '#i-down' : '#i-chart' }}"/>
                </svg>
              </div>
              <div class="tx-main">
                <div class="t">{{ $tx->party }}</div>
                <div class="s">
                  <span>{{ $tx->type }}</span>
                  <span>{{ $tx->date->format('d/m/Y') }}</span>
                </div>
              </div>
              <div class="tx-amt">{{ $tx->direction === 'in' ? '+' : '−' }}{{ \App\Support\Money::format($tx->amount) }}</div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-state" style="padding:30px 20px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
          <h4 style="font-size:13.5px">لا توجد حركات بعد</h4>
        </div>
      @endif
    </div>

  </div>

</div>

@endsection
