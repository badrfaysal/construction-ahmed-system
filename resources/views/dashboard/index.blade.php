@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')

<style>
/* تحسين شكل كروت الإحصائيات (Premium CSS) */
.vstat {
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    background-size: 200% auto;
}
.vstat:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    background-position: right center;
}
.vstat .ic {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: transform 0.3s ease;
}
.vstat:hover .ic {
    transform: scale(1.1) rotate(5deg);
}
.vstat-navy { background-image: linear-gradient(135deg, #1e293b 0%, #0f172a 51%, #1e293b 100%); }
.vstat-blue { background-image: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 51%, #3b82f6 100%); }
.vstat-teal { background-image: linear-gradient(135deg, #14b8a6 0%, #0f766e 51%, #14b8a6 100%); }
.vstat-green{ background-image: linear-gradient(135deg, #10b981 0%, #047857 51%, #10b981 100%); }
.vstat-red  { background-image: linear-gradient(135deg, #ef4444 0%, #b91c1c 51%, #ef4444 100%); }
.vstat-amber{ background-image: linear-gradient(135deg, #f59e0b 0%, #b45309 51%, #f59e0b 100%); }
.vstat-gold { background-image: linear-gradient(135deg, #d97706 0%, #92400e 51%, #d97706 100%); }

.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 12px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.filter-bar h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; }
.filter-form { display: flex; gap: 10px; align-items: center; }
.filter-form input[type="month"] {
    padding: 6px 12px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-family: inherit;
    outline: none;
}
.filter-form input[type="month"]:focus { border-color: #3b82f6; }

/* دعم شبكة 5 أعمدة للكروت في الشاشات الكبيرة */
.cols-5 { grid-template-columns: repeat(5, 1fr); }
@media (max-width: 1400px) {
    .cols-5 { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .cols-5 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .cols-5 { grid-template-columns: 1fr; }
}
</style>

{{-- شريط الفلتر الزمني --}}
<div class="filter-bar">
    <h2>نظرة عامة {{ $isFiltered ? '- شهر ' . \Carbon\Carbon::parse($monthFilter)->translatedFormat('F Y') : '- كل الأوقات' }}</h2>
    <form class="filter-form" method="GET" action="{{ route('dashboard') }}">
        <input type="month" name="month" value="{{ $monthFilter === 'all' ? '' : $monthFilter }}" onchange="this.form.submit()">
        <a href="{{ route('dashboard', ['month' => 'all']) }}" class="btn ghost sm {{ $monthFilter === 'all' ? 'active' : '' }}" style="margin: 0">الكل (بدون فلتر)</a>
    </form>
</div>

{{-- Summary stats row --}}
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
      <div><span>صافي حركات المقاولات</span><b>{{ \App\Support\Money::format($constructionNetCash) }}</b></div>
      <div><span>مستحق مباشر</span><b>{{ \App\Support\Money::format($directReceivables) }}</b></div>
      <div><span>مستحق تقسيط</span><b>{{ \App\Support\Money::format($installmentReceivables) }}</b></div>
      <div><span>ديون موردين −</span><b>{{ \App\Support\Money::format($supplierDebtsRemaining) }}</b></div>
      <div style="grid-column: 1 / -1; border-top: 1px dashed rgba(255,255,255,0.15); margin-top: 2px; padding-top: 4px;"><span>مصنعيات فنيين −</span><b>{{ \App\Support\Money::format($unpaidLabor) }}</b></div>
    </div>
  </div>

  <div class="vstat vstat-blue">
      <div class="top">
        <span class="label">{{ $isFiltered ? 'مشاريع الشهر' : 'المشاريع الجارية' }}</span>
        <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      </span>
    </div>
    <div class="val">{{ $activeProjects->count() }}</div>
    <div class="note">{{ $activeProjects->pluck('name')->join(' · ') ?: 'لا توجد مشاريع جارية' }}</div>
  </div>

  <div class="vstat vstat-teal">
      <div class="top">
        <span class="label">المحصّل {{ $isFiltered ? 'بالشهر' : 'الإجمالي' }}</span>
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

  <a class="vstat vstat-gold" href="{{ route('installments.index') }}">
    <div class="top">
      <span class="label">مستحق منظومة الأقساط</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($installmentContractsDue) }} <small>ج.م</small></div>
    <div class="note">المتبقي من عقود تقسيط المقاولات</div>
  </a>

</div>

{{-- محتوى رئيسي (المشاريع الجارية) + عمود جانبي (روابط سريعة + آخر الحركات) --}}
<div class="dash-layout">

  {{-- ═══ العمود الرئيسي ═══ --}}
  <div>
    @php
      $dashTabs = [
        'active' => ['title' => $isFiltered ? 'مشاريع الشهر' : 'المشاريع الجارية', 'items' => $activeProjects],
        'done' => ['title' => 'المشاريع المكتملة', 'items' => $doneProjects],
        'suspended' => ['title' => 'المشاريع المعلقة', 'items' => $suspendedProjects],
        'canceled' => ['title' => 'المشاريع الملغية', 'items' => $canceledProjects],
      ];
    @endphp

    <div class="tabs" style="margin-bottom:16px;">
      @foreach($dashTabs as $k => $t)
        <a class="tab {{ $loop->first ? 'active' : '' }}" onclick="showDashTab('{{ $k }}', this)" style="cursor:pointer;">
          {{ $t['title'] }} <span class="cnt">{{ $t['items']->count() }}</span>
        </a>
      @endforeach
    </div>

    @foreach($dashTabs as $k => $t)
      <div id="dash-tab-{{ $k }}" class="dash-tab-content" style="display: {{ $loop->first ? 'block' : 'none' }}">
        @if($t['items']->count())
          <div class="pcards">
            @foreach($t['items'] as $p)
              @php
                $prog   = $p->progressPct();
                $paid   = $p->cached_collected;
                $actual = $p->cached_actual_total;
                $gross  = $p->grossClientTotal();
                $due    = max($actual - $paid, 0);
                $activeBand = $p->bands->where('status', 'active')->first();
                $paidWorkers = (float) $p->total_worker_paid;
              @endphp
              <a class="pcard {{ $p->status === 'done' ? 'is-done' : '' }}" href="{{ route('projects.show', $p) }}">
                <div class="pc-band"></div>
                <div class="pc-body">
                  <div class="pc-head">
                    <div>
                      <div class="pc-name">{{ $p->name }}</div>
                      <div class="pc-client">{{ $p->client->name }}</div>
                    </div>
                    @if($p->status === 'done')
                      <span class="tag green"><span class="dot"></span>مكتمل ومسلّم</span>
                    @elseif($p->status === 'suspended')
                      <span class="tag amber"><span class="dot"></span>معلق</span>
                    @elseif($p->status === 'canceled')
                      <span class="tag red"><span class="dot"></span>ملغي</span>
                    @elseif($activeBand)
                      <span class="tag blue"><span class="dot"></span>{{ $activeBand->name }}</span>
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
                      <div class="l" style="display:flex;align-items:center;gap:4px">قيمة المشروع <small class="muted" style="font-size:9px" title="قبل الخصم">(إجمالي)</small></div>
                      <div class="v">{{ \App\Support\Money::format($gross) }}</div>
                    </div>
                    <div>
                      <div class="l">المدفوع</div>
                      <div class="v" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</div>
                    </div>
                    <div>
                      <div class="l">المتبقي</div>
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
            <h4>لا توجد {{ $t['title'] }}</h4>
          </div>
        @endif
      </div>
    @endforeach

    <script>
      function showDashTab(tabId, btn) {
        document.querySelectorAll('.dash-tab-content').forEach(el => el.style.display = 'none');
        document.getElementById('dash-tab-' + tabId).style.display = 'block';
        
        let parent = btn.closest('.tabs');
        parent.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
      }
    </script>
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
