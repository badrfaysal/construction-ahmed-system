@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')

<style>
/* تحسين شكل كروت الإحصائيات (Premium CSS) */
.vstat {
    padding: 10px 14px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
    background-size: 200% auto;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
    z-index: 1;
}
.vstat .val { font-size: 20px; margin-top: -4px; }
.vstat .ic { width: 28px; height: 28px; }
.vstat .ic svg { width: 16px; height: 16px; }
.vstat .top { margin-bottom: 2px; }
.vstat .note { font-size: 11px; margin-top: 0px; opacity: 0.9; }
.vstat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(180deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    z-index: -1;
}
.vstat:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.25);
    background-position: right center;
    border-color: rgba(255, 255, 255, 0.3);
}
.vstat .ic {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.vstat:hover .ic {
    transform: scale(1.15) rotate(8deg);
    background: rgba(255, 255, 255, 0.3);
}
.vstat .vstat-bg {
    position: absolute;
    left: -10px;
    bottom: -15px;
    width: 90px;
    height: 90px;
    color: rgba(255, 255, 255, 0.15);
    z-index: 0;
    transform: rotate(-15deg);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}
.vstat:hover .vstat-bg {
    transform: scale(1.2) rotate(5deg);
    color: rgba(255, 255, 255, 0.25);
}

.vstat-navy { background-image: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
.vstat-blue { background-image: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); }
.vstat-teal { background-image: linear-gradient(135deg, #047857 0%, #10b981 100%); }
.vstat-green{ background-image: linear-gradient(135deg, #4338ca 0%, #8b5cf6 100%); }
.vstat-red  { background-image: linear-gradient(135deg, #be123c 0%, #a64555 100%); }
.vstat-amber{ background-image: linear-gradient(135deg, #b45309 0%, #f59e0b 100%); }
.vstat-gold { background-image: linear-gradient(135deg, #334155 0%, #64748b 100%); }
.vstat-light-red { background-image: linear-gradient(135deg, #f10101 0%, #e51d1d 100%); }

/* Account Cards */
.acc-card {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
}
.acc-card::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 100px;
    background: radial-gradient(circle, rgba(59,130,246,0.05) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    transform: translate(30%, -30%);
    transition: transform 0.4s ease;
}
.acc-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
}
.acc-card:hover::after {
    transform: translate(20%, -20%) scale(1.5);
}
.acc-card .acc-bg {
    position: absolute;
    left: -15px;
    bottom: -15px;
    width: 110px;
    height: 110px;
    z-index: 1;
    transform: rotate(-10deg);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}
.acc-card:hover .acc-bg {
    transform: scale(1.15) rotate(0deg);
    filter: brightness(0.85); /* Make the color slightly darker/more visible on hover */
}

.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    padding: 16px 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px -2px rgba(0,0,0,0.04);
    border: 1px solid rgba(226, 232, 240, 0.8);
}
.filter-bar h2 { margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; }
.filter-form { display: flex; gap: 12px; align-items: center; }
.filter-form input[type="month"] {
    padding: 8px 16px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    font-family: inherit;
    font-weight: 500;
    color: #334155;
    outline: none;
    transition: all 0.2s;
    background: #f8fafc;
}
.filter-form input[type="month"]:focus { 
    border-color: #3b82f6; 
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    background: #ffffff;
}

/* دعم شبكة الكروت في الشاشات الكبيرة */
.cols-top { grid-template-columns: repeat(4, 1fr); gap: 20px; display: grid; }
.cols-acc { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; display: grid; }

.qlink:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
    border-color: #cbd5e1 !important;
}
.qlink:hover .ic {
    transform: scale(1.1);
}

/* Profit Section CSS */
.profit-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}
.profit-card {
    border-radius: 12px;
    padding: 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
}
.profit-card-blue {
    background: #0f6c9c; /* Matches screenshot blue */
}
.profit-card-green {
    background: #0d8159; /* Matches screenshot green */
}
.profit-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
    position: relative;
    z-index: 2;
}
.profit-card .card-title {
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.profit-card .card-value {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 4px;
    direction: ltr;
    text-align: right;
    position: relative;
    z-index: 2;
}
.profit-card .card-subtitle {
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 20px;
    position: relative;
    z-index: 2;
}
.profit-list {
    list-style: none;
    padding: 0;
    margin: 0;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 16px;
    position: relative;
    z-index: 2;
}
.profit-list li {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.profit-list li:last-child {
    border-bottom: none;
}
.profit-list li .val {
    font-weight: 700;
}
.profit-list li.total-row {
    font-size: 16px;
    font-weight: 700;
    color: #ffdf00; /* Yellow */
    border-bottom: none;
    padding-top: 12px;
}

@media (max-width: 900px) {
    .profit-section {
        grid-template-columns: 1fr;
    }
}
</style>

{{-- شريط الفلتر الزمني --}}
<div class="filter-bar" style="background: linear-gradient(to left, #ffffff, #f8fafc); border-right: 4px solid #3b82f6; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05); padding: 20px 24px;">
    <h2 style="display: flex; align-items: center; gap: 12px; font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">
        <div style="width: 40px; height: 40px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.1);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
        نظرة عامة
        @if($isFiltered)
            <span class="tag blue" style="font-size: 14px; font-weight: 700; padding: 6px 14px; border-radius: 20px; display: flex; align-items: center; gap: 6px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; margin-inline-start: 4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                شهر {{ \Carbon\Carbon::parse($monthFilter)->translatedFormat('F Y') }}
            </span>
        @else
            <span class="tag gray" style="font-size: 14px; font-weight: 700; padding: 6px 14px; border-radius: 20px; display: flex; align-items: center; gap: 6px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; margin-inline-start: 4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                كل الأوقات
            </span>
        @endif
    </h2>
    <form class="filter-form" method="GET" action="{{ route('dashboard') }}" style="background: #f8fafc; padding: 6px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
        <div style="position: relative; display: flex; align-items: center;">
            <svg style="position: absolute; right: 12px; color: #64748b; pointer-events: none; width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <input type="month" name="month" value="{{ $monthFilter === 'all' ? '' : $monthFilter }}" onchange="this.form.submit()" style="padding: 10px 16px 10px 36px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #1e293b; font-weight: 600; font-size: 14px; outline: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: border-color 0.2s; font-family: inherit;">
        </div>
        <a href="{{ route('dashboard', ['month' => 'all']) }}" class="btn {{ $monthFilter === 'all' ? 'primary' : 'ghost' }} sm" style="margin: 0; border-radius: 8px; padding: 10px 16px; font-weight: 700; display: flex; align-items: center; gap: 6px; {{ $monthFilter === 'all' ? 'background: #3b82f6; color: #fff; border:none;' : 'background: transparent; color: #475569;' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
            الكل
        </a>
    </form>
</div>



{{-- Summary stats row --}}
<div class="cols-top" style="margin-bottom:30px;">

  {{-- 1. رأس مال المقاولات --}}
  <div class="vstat vstat-navy">
    <div class="top">
      <span class="label">رأس مال المقاولات</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($netCapital) }} <small>ج.م</small></div>
    <div class="note">(السيولة + المستحقات − الديون)</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
  </div>

  {{-- 2. مشاريع الشهر / المشاريع الجارية --}}
  <div class="vstat vstat-blue">
      <div class="top">
        <span class="label">{{ $isFiltered ? 'مشاريع الشهر' : 'المشاريع الجارية' }}</span>
        <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      </span>
    </div>
    <div class="val">{{ $activeProjects->count() }}</div>
    <div class="note">{{ $activeProjects->pluck('name')->join(' · ') ?: 'لا توجد مشاريع جارية' }}</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
  </div>

  {{-- 3. أقساط المقاولات --}}
  <a class="vstat vstat-gold" href="{{ route('installments.index') }}">
    <div class="top">
      <span class="label">أقساط المقاولات</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($installmentContractsDue) }} <small>ج.م</small></div>
    <div class="note">المتبقي من عقود تقسيط المقاولات</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
  </a>

  {{-- 4. المستحقات لنا --}}
  <a class="vstat vstat-teal" href="{{ route('receivables.index') }}">
      <div class="top">
        <span class="label">المستحقات لنا</span>
        <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($directReceivables) }} <small>ج.م</small></div>
    <div class="note">مستحقات مشاريع مباشرة</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
  </a>

  {{-- 5. الديون --}}
  <a class="vstat vstat-red" href="{{ route('debts.index') }}">
      <div class="top">
        <span class="label">ديون الموردين</span>
        <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($supplierDebtsRemaining) }} <small>ج.م</small></div>
    <div class="note">متبقي الديون المستحقة للموردين</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
  </a>

  {{-- 6. مصنعيات الفنيين --}}
  <a class="vstat vstat-amber" href="{{ route('craftsmen.index') }}">
      <div class="top">
        <span class="label">مصنعيات الفنيين</span>
        <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($unpaidLabor) }} <small>ج.م</small></div>
    <div class="note">مصنعيات فنيين متبقية (غير مسددة)</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
  </a>

  {{-- 7. سيولة المقاولات --}}
  <a class="vstat vstat-green" href="{{ route('wallet.index') }}">
      <div class="top">
        <span class="label">سيولة المقاولات</span>
        <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($accountsBalance) }} <small>ج.م</small></div>
    <div class="note">إجمالي السيولة في الخزائن والبنوك</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
  </a>

  {{-- 8. المصروفات العامة --}}
  <a class="vstat vstat-light-red" href="#">
    <div class="top">
      <span class="label">المصروفات العامة</span>
      <span class="ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      </span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalGeneralExpenses ?? 0) }} <small>ج.م</small></div>
    <div class="note">إجمالي المصروفات الإدارية للمؤسسة</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
  </a>

</div>

        {{-- روابط سريعة (Full Width) --}}
        <div class="card" style="display: flex; flex-direction: column; padding: 24px; margin-top: 40px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 16px; box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05);">
          <div class="section-label" style="margin:0 0 20px; font-size: 16px; font-weight: 800; color: #0369a1; display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><use href="#i-send"/></svg>
            إجراءات سريعة
          </div>
          <div class="qlinks" style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px;">
            <a class="qlink" href="{{ route('projects.create') }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center; transition: all 0.2s;">
              <span class="ic ic-blue" style="margin: 0 auto 8px; width: 32px; height: 32px; font-size: 16px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg></span>
              <span class="lbl" style="font-weight: 600; color: #334155; font-size: 13px;">مشروع</span>
            </a>
            <a class="qlink" href="{{ route('materials.create') }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center; transition: all 0.2s;">
              <span class="ic ic-teal" style="margin: 0 auto 8px; width: 32px; height: 32px; font-size: 16px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg></span>
              <span class="lbl" style="font-weight: 600; color: #334155; font-size: 13px;">خامة</span>
            </a>
            <a class="qlink" href="{{ route('receivables.index') }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center; transition: all 0.2s;">
              <span class="ic ic-green" style="margin: 0 auto 8px; width: 32px; height: 32px; font-size: 16px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span>
              <span class="lbl" style="font-weight: 600; color: #334155; font-size: 13px;">تحصيل</span>
            </a>
            <a class="qlink" href="{{ route('quotes.create') }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center; transition: all 0.2s;">
              <span class="ic ic-purple" style="margin: 0 auto 8px; width: 32px; height: 32px; font-size: 16px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg></span>
              <span class="lbl" style="font-weight: 600; color: #334155; font-size: 13px;">عرض سعر</span>
            </a>
            <a class="qlink" href="{{ route('installments.index') }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center; transition: all 0.2s;">
              <span class="ic ic-gold" style="margin: 0 auto 8px; width: 32px; height: 32px; font-size: 16px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span>
              <span class="lbl" style="font-weight: 600; color: #334155; font-size: 13px;">أقساط</span>
            </a>
            <a class="qlink" href="{{ route('radar.index') }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center; transition: all 0.2s;">
              <span class="ic ic-amber" style="margin: 0 auto 8px; width: 32px; height: 32px; font-size: 16px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg></span>
              <span class="lbl" style="font-weight: 600; color: #334155; font-size: 13px;">الرادار (السجل)</span>
            </a>
          </div>
        </div>

<div style="margin-top: 40px;">

    {{-- Main Area: Accounts --}}
    <div style="margin-bottom: 40px;">
        <h3 style="margin-top:0; margin-bottom:15px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
            السيولة النقدية والمحافظ البنكية
        </h3>

        <div class="cols-acc">
            @foreach($accounts as $acc)
            <div class="acc-card" style="display:flex; flex-direction:column; justify-content:space-between; padding:24px;">
                <svg class="acc-bg" style="color: {{ $acc->category === 'bank_wallet' ? 'rgba(59,130,246,0.18)' : ($acc->category === 'safe_cash' ? 'rgba(16,185,129,0.18)' : 'rgba(245,158,11,0.18)') }};" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="{{ $acc->category === 'bank_wallet' ? '#i-building' : ($acc->category === 'safe_cash' ? '#i-wallet' : '#i-cash') }}"/>
                </svg>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; position:relative; z-index:2;">
                    <div style="font-weight:700; font-size:17px; color:#0f172a; letter-spacing:-0.01em;">{{ $acc->name }}</div>
                    @if($acc->category === 'bank_wallet')
                        <span class="tag blue sm" style="margin:0; box-shadow: 0 2px 4px rgba(59,130,246,0.15)"><span class="dot"></span>بنكي</span>
                    @elseif($acc->category === 'safe_cash')
                        <span class="tag green sm" style="margin:0; box-shadow: 0 2px 4px rgba(16,185,129,0.15)"><span class="dot"></span>خزينة</span>
                    @else
                        <span class="tag gray sm" style="margin:0; box-shadow: 0 2px 4px rgba(100,116,139,0.15)"><span class="dot"></span>أخرى</span>
                    @endif
                </div>
                <div style="position:relative; z-index:2;">
                    <div class="muted" style="font-size:12px; margin-bottom:6px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#64748b;">الرصيد الحالي</div>
                    <div style="font-size:28px; font-weight:800; color:#0f172a; display:flex; align-items:baseline; gap:4px; letter-spacing:-0.02em;">
                        {{ \App\Support\Money::format($acc->balance) }} <span style="font-size:14px; font-weight:600; color:#94a3b8;">ج.م</span>
                    </div>
                </div>
                
                <div style="position:relative; z-index:2; margin-top:16px; border-top:1px solid rgba(0,0,0,0.05); padding-top:12px;">
                    <a href="{{ route('accounts.statement', $acc->id) }}" class="btn ghost sm" style="width:100%; justify-content:center; color:#3b82f6;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" style="margin-inline-end:6px"><use href="#i-doc"/></svg>
                        كشف حساب المحفظة
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Profit & Recent Transactions Grid --}}
    <style>
    .profit-tx-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        align-items: start;
        margin-bottom: 40px;
    }
    @media (max-width: 1300px) {
        .profit-tx-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <div class="profit-tx-grid">
        
        {{-- Profit Details --}}
        <div style="display: flex; flex-direction: column;">
            <h3 style="margin-top:0; margin-bottom:15px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg>
                تفاصيل الأرباح
            </h3>
            
            <div class="profit-section" style="flex: 1; margin-bottom: 0;">
                {{-- الربح الدفتري (على الورق) - Right Card --}}
            <div class="profit-card profit-card-blue">
                <div class="card-header">
                    <div class="card-title">
                        الربح الدفتري (على الورق)
                    </div>
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-doc"/></svg>
                </div>
                <div class="card-value">{{ \App\Support\Money::format($bookProfit) }} ج</div>
                <div class="card-subtitle">إجمالي الإيرادات - إجمالي الخصومات ({{ \App\Support\Money::format($totalDiscount) }} ج)</div>
                
                <ul class="profit-list">
                    <li>
                        <span class="lbl" style="font-weight: 700; display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg> 
                            مصادر الإيرادات
                        </span>
                    </li>
                    <li>
                        <span class="lbl">أرباح التجاري (فرق الشراء والبيع)</span>
                        <span class="val">{{ \App\Support\Money::format($totalTradeProfit) }} ج</span>
                    </li>
                    <li>
                        <span class="lbl">أرباح نسبة الإشراف</span>
                        <span class="val">{{ \App\Support\Money::format($totalPercentageProfit) }} ج</span>
                    </li>
                    <li>
                        <span class="lbl">أرباح منظومة التقسيط (الفوائد)</span>
                        <span class="val">{{ \App\Support\Money::format($totalInstallmentProfit) }} ج</span>
                    </li>
                    <li class="total-row">
                        <span class="lbl">إجمالي الإيرادات</span>
                        <span class="val">{{ \App\Support\Money::format($totalRevenuesForView) }} ج</span>
                    </li>
                </ul>
                <svg style="position: absolute; left: -20px; bottom: -20px; width: 180px; height: 180px; color: rgba(255,255,255,0.06); transform: rotate(-15deg); pointer-events:none; z-index: 1;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-doc"/></svg>
            </div>

            {{-- الربح الحقيقي (المحصل) - Left Card --}}
            <div class="profit-card profit-card-green">
                <div class="card-header">
                    <div class="card-title">
                        الربح الحقيقي (المحصل)
                    </div>
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-cash"/></svg>
                </div>
                <div class="card-value">{{ \App\Support\Money::format($realProfit) }} ج</div>
                <div class="card-subtitle">المبلغ الآمن للتوزيع على الشركاء</div>
                
                <ul class="profit-list">
                    <li>
                        <span class="lbl" style="font-weight: 700;">= مقارنة مع الدفتري</span>
                    </li>
                    <li>
                        <span class="lbl">الربح الدفتري</span>
                        <span class="val">{{ \App\Support\Money::format($bookProfit) }} ج</span>
                    </li>
                    <li>
                        <span class="lbl">الربح الحقيقي</span>
                        <span class="val">{{ \App\Support\Money::format($realProfit) }} ج</span>
                    </li>
                    <li class="total-row">
                        <span class="lbl">الفرق (أرباح لم تُحصل بعد)</span>
                        <span class="val">{{ \App\Support\Money::format($uncollectedProfit) }} ج</span>
                    </li>
                </ul>
                <svg style="position: absolute; left: -20px; bottom: -20px; width: 180px; height: 180px; color: rgba(255,255,255,0.06); transform: rotate(-15deg); pointer-events:none; z-index: 1;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-cash"/></svg>
            </div>
        </div>

        {{-- آخر الحركات --}}
        <div style="display: flex; flex-direction: column;">
            <div class="card" style="height: 100%; min-height: 400px; max-height: 480px; overflow:hidden; display: flex; flex-direction: column; background: #ffffff; border-color: #e2e8f0; box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05); border-radius: 16px; position: relative;">
              <div class="table-top" style="padding: 20px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; background: #f8fafc;">
                <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                  <span style="background: #e0f2fe; color: #0284c7; padding: 6px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
                  </span>
                  آخر الحركات المالية
                </h4>
                <a href="{{ route('transactions.index') }}" class="btn ghost sm" style="font-size: 13px; background: #e0f2fe; color: #0284c7; border: none; font-weight: 700; border-radius: 8px;">عرض الكل</a>
              </div>
              <style>
                .tx-premium {
                    position: relative;
                    padding: 16px;
                    margin: 12px 20px;
                    border-radius: 14px;
                    background: #ffffff;
                    border: 1px solid #f1f5f9;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
                    transition: all 0.3s ease;
                    overflow: hidden;
                    display: flex;
                    align-items: center;
                    gap: 16px;
                }
                .tx-premium:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 16px rgba(0,0,0,0.06);
                    border-color: #e2e8f0;
                }
                .tx-in .tx-ic-box { background: #ecfdf5; color: #10b981; }
                .tx-out .tx-ic-box { background: #fef2f2; color: #ef4444; }
                .tx-ic-box {
                    width: 44px;
                    height: 44px;
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                }
                .tx-bg-icon { display: none; }
              </style>
              @if($recentTransactions->count())
                <div class="feed feed-compact" style="flex: 1; overflow-y: auto; padding-bottom: 20px; padding-top: 4px;">
                  @foreach($recentTransactions as $tx)
                    <div class="tx-premium tx-{{ $tx->direction }}">
                      <div class="tx-ic-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <use href="{{ $tx->direction === 'in' ? '#i-down' : '#i-chart' }}"/>
                        </svg>
                      </div>
                      <div class="tx-main-info" style="flex: 1;">
                        <div class="t" style="font-size: 14px; font-weight: 800; color: #1e293b; margin-bottom: 6px;">{{ \Illuminate\Support\Str::limit($tx->party, 25) }}</div>
                        <div class="s" style="font-size: 13px; color: #64748b; display: flex; gap: 10px; align-items: center;">
                          <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 6px; font-weight: 600; color: #475569;">{{ $tx->type }}</span>
                          <span style="font-weight: 500;">{{ $tx->date->format('d/m') }}</span>
                        </div>
                      </div>
                      <div style="font-size: 17px; font-weight: 800; color: {{ $tx->direction === 'in' ? '#10b981' : '#ef4444' }}; direction: ltr;">
                        {{ $tx->direction === 'in' ? '+' : '-' }}{{ \App\Support\Money::format($tx->amount) }}
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="empty-state" style="padding:40px 20px; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8;">
                  <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.4;"><use href="#i-activity"/></svg>
                  <h4 style="font-size:15px; margin: 0; color: #64748b; font-weight: 700;">لا توجد حركات مالية بعد</h4>
                </div>
              @endif
            </div>
        </div>

    </div>
        
    {{-- Discounts and Losses Section --}}
    <div class="card discounts-card" style="margin-bottom:30px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.05); border-right: 4px solid #ef4444; overflow: hidden; position: relative;">
        <svg style="position: absolute; left: -20px; bottom: -20px; width: 150px; height: 150px; color: rgba(239, 68, 68, 0.05); transform: rotate(-15deg); pointer-events:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-chart"/></svg>
            <div style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #b91c1c;">تفاصيل الخصومات والخسائر</h3>
                </div>
                
                <div class="disc-list" style="display: flex; flex-direction: column;">
                    
                    {{-- Row 1 (Client Discounts) --}}
                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="text-align: left; font-weight: 700; color: #ef4444;">{{ \App\Support\Money::format($totalDiscount) }} ج</div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #1e293b;">خصومات للعملاء</div>
                            <div style="font-size: 12px; color: #94a3b8;">تخفيضات على المبيعات</div>
                        </div>
                    </div>

                    {{-- Row 2 (Marketer Commissions) --}}
                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="text-align: left; font-weight: 700; color: #ef4444;">{{ \App\Support\Money::format($totalMarketerCommissions) }} ج</div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #1e293b;">عمولات المسوقين</div>
                            <div style="font-size: 12px; color: #94a3b8;">عمولات البيع والتسويق</div>
                        </div>
                    </div>

                    {{-- Row 3 (Return Losses) --}}
                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="text-align: left; font-weight: 700; color: #ef4444;">{{ \App\Support\Money::format($totalReturnLosses) }} ج</div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #1e293b;">خسائر المرتجعات</div>
                            <div style="font-size: 12px; color: #94a3b8;">فرق التكلفة في المرتجعات</div>
                        </div>
                    </div>

                    {{-- Total Row --}}
                    <div style="display: flex; justify-content: space-between; padding-top: 16px; margin-top: 8px;">
                        <div style="text-align: left; font-size: 18px; font-weight: 800; color: #dc2626;">{{ \App\Support\Money::format($totalDiscountsAndLosses) }} ج</div>
                        <div style="text-align: right; font-size: 18px; font-weight: 800; color: #1e293b;">إجمالي الخصومات</div>
                    </div>

    </div>

</div> <!-- closes padding 24px -->
</div> <!-- closes discounts-card -->
</div> <!-- closes margin-top 40px wrapper -->

{{-- الرسم البياني لنمو رأس المال --}}
<div class="card" style="margin-top: 40px; margin-bottom: 40px; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; position: relative;">
    <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; background: #fafafa;">
        
        {{-- Right Side: Title --}}
        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            نمو رأس المال (لقطات)
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
        </h3>
        
        {{-- Left Side: Badges --}}
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 12px; font-size: 13px; font-weight: 600; color: #334155; display: flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                آخر 3 شهور (افتراضي)
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            <div style="background: #1d4ed8; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 700; display: flex; align-items: center;">
                {{ $capitalSnapshots->count() }} لقطة
            </div>
        </div>
    </div>
    
    <div style="padding: 16px 24px 24px 24px;">
        <div id="capital-chart" style="min-height: 320px;"></div>
    </div>
    
    @if($capitalSnapshots->isEmpty())
        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(255,255,255,0.7); z-index: 10; backdrop-filter: blur(8px);">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#4338ca" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 8 12 12 14 14"></polyline></svg>
            <div style="color: #1e3a8a; font-weight: 800; font-size: 16px; margin-bottom: 8px;">لا توجد لقطات مسجلة حتى الآن</div>
            <div style="color: #4f46e5; font-size: 14px; font-weight: 600;">يقوم النظام بتسجيل لقطة يومياً بشكل تلقائي</div>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var snapshots = @json($capitalSnapshots);
    
    if (snapshots.length > 0) {
        // إذا كان هناك لقطة واحدة فقط، نضيف لقطة وهمية سابقة لتكوين خط مستقيم
        if (snapshots.length === 1) {
            let single = snapshots[0];
            let previousDate = new Date(single.snapshot_date);
            previousDate.setDate(previousDate.getDate() - 1);
            let prevDateStr = previousDate.toISOString().split('T')[0];
            
            snapshots.unshift({
                snapshot_date: prevDateStr,
                net_capital: single.net_capital,
                details: single.details
            });
        }
        
        var dates = snapshots.map(item => {
            // تنسيق التاريخ إلى يوم وشهر
            var d = new Date(item.snapshot_date);
            return d.getDate() + '/' + (d.getMonth() + 1);
        });
        
        var values = snapshots.map(item => parseFloat(item.net_capital));
        
        var options = {
            series: [{
                name: 'رأس المال',
                data: values
            }],
            chart: {
                type: 'area',
                height: 300,
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                }
            },
            colors: ['#3b82f6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 100]
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            markers: {
                size: 5,
                colors: ['#ffffff'],
                strokeColors: '#3b82f6',
                strokeWidth: 2,
                hover: {
                    size: 7
                }
            },
            xaxis: {
                categories: dates,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontSize: '11px',
                        fontWeight: 500,
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return (value / 1000).toFixed(0) + 'k ج';
                    },
                    style: {
                        colors: '#94a3b8',
                        fontSize: '11px',
                        fontWeight: 500,
                    }
                }
            },
            grid: {
                borderColor: 'rgba(0,0,0,0.04)',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } },
            },
            tooltip: {
                theme: 'light',
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    var val = series[seriesIndex][dataPointIndex];
                    var item = snapshots[dataPointIndex];
                    
                    var tooltipHtml = '<div style="padding: 12px; font-family: inherit; min-width: 220px; direction: rtl; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">';
                    tooltipHtml += '<div style="font-weight: 700; margin-bottom: 8px; font-size: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; color: #1e293b;">رأس المال: ' + new Intl.NumberFormat('ar-EG').format(val) + ' ج.م</div>';
                    
                    if (item.details && item.details.accounts_breakdown) {
                        tooltipHtml += '<div style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px;">تفاصيل الخزائن:</div>';
                        item.details.accounts_breakdown.forEach(function(acc) {
                            tooltipHtml += '<div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px; align-items: center;">';
                            tooltipHtml += '<span style="color: #475569;">' + acc.name + '</span>';
                            tooltipHtml += '<span style="font-weight: 700; color: #0f172a;">' + new Intl.NumberFormat('ar-EG').format(acc.balance) + '</span>';
                            tooltipHtml += '</div>';
                        });
                    }
                    
                    tooltipHtml += '</div>';
                    return tooltipHtml;
                }
            },
            markers: {
                size: 4,
                colors: ['#fff'],
                strokeColors: '#3b82f6',
                strokeWidth: 2,
                hover: {
                    size: 6
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#capital-chart"), options);
        chart.render();
    }
});
</script>
@endpush
