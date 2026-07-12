<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'نظام الإدارة') — {{ $settings->company_name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/sy2.css?v=1.2') }}">
{{-- flatpickr — يخلّي كل حقول التاريخ تتعرض يوم/شهر/سنة (dd/mm/yyyy) وتتبعت Y-m-d --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@stack('styles')
</head>
<body>

{{-- خلفية متحركة: أيقونات بناء ومقاولات بتعوم بهدوء ورا المحتوى — لمسة حيوية
     خفيفة جدًا (شفافة) مش بتأثر على القراءة. بتتعطّل تلقائيًا لو المستخدم مفضّل
     تقليل الحركة (prefers-reduced-motion). --}}
<div class="bg-icons" aria-hidden="true">
  <svg style="top:8%;inset-inline-start:6%;width:46px;height:46px;animation-duration:13s;animation-delay:0s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
  <svg style="top:22%;inset-inline-start:38%;width:64px;height:64px;animation-duration:18s;animation-delay:1.5s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
  <svg style="top:14%;inset-inline-start:78%;width:40px;height:40px;animation-duration:15s;animation-delay:3s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
  <svg style="top:44%;inset-inline-start:14%;width:52px;height:52px;animation-duration:20s;animation-delay:.8s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-tool"/></svg>
  <svg style="top:52%;inset-inline-start:62%;width:58px;height:58px;animation-duration:16s;animation-delay:2.2s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-truck"/></svg>
  <svg style="top:70%;inset-inline-start:30%;width:44px;height:44px;animation-duration:14s;animation-delay:4s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
  <svg style="top:80%;inset-inline-start:82%;width:50px;height:50px;animation-duration:19s;animation-delay:1s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-clipboard"/></svg>
  <svg style="top:34%;inset-inline-start:90%;width:38px;height:38px;animation-duration:17s;animation-delay:2.8s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-coins"/></svg>
  <svg style="top:88%;inset-inline-start:52%;width:42px;height:42px;animation-duration:15s;animation-delay:3.6s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
  <svg style="top:60%;inset-inline-start:46%;width:36px;height:36px;animation-duration:21s;animation-delay:.4s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bar-chart"/></svg>
</div>

{{-- شاشة تحميل — بتظهر تلقائيًا مع أي إرسال فورم أو تنقّل بين الصفحات (انظر
     initConstructionLoader أسفل الصفحة)، بتصميم كرين وبناء بيتشيّد. --}}
<div class="constr-loader" id="constrLoader" aria-hidden="true">
  <div class="cl-box">
    <svg width="118" height="92" viewBox="0 0 140 110" fill="none">
      <line x1="8" y1="98" x2="132" y2="98" stroke="var(--line)" stroke-width="3" stroke-linecap="round"/>
      <g stroke="var(--ink-3)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none">
        <line x1="104" y1="98" x2="104" y2="14"/>
        <line x1="104" y1="14" x2="134" y2="14"/>
        <line x1="104" y1="14" x2="90" y2="24"/>
        <line x1="96" y1="98" x2="112" y2="98"/>
      </g>
      <g class="cl-hook">
        <line x1="123" y1="14" x2="123" y2="32" stroke="var(--ink-3)" stroke-width="2.5"/>
        <rect x="114" y="32" width="18" height="15" rx="2" fill="var(--brand)"/>
      </g>
      <g>
        <rect class="cl-bar cl-bar1" x="20" y="68" width="22" height="30" rx="2" fill="var(--accent)"/>
        <rect class="cl-bar cl-bar2" x="47" y="52" width="22" height="46" rx="2" fill="var(--brand)"/>
        <rect class="cl-bar cl-bar3" x="74" y="38" width="22" height="60" rx="2" fill="var(--accent)"/>
      </g>
    </svg>
    <div class="cl-text">جاري التحميل<span class="cl-dots"></span></div>
  </div>
</div>

{{-- Hidden SVG icon library — referenced via <use href="#i-name"> throughout all views --}}
<svg width="0" height="0" style="position:absolute"><defs>
  <g id="i-grid"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></g>
  <g id="i-building"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9h.01M9 13h.01M9 17h.01"/></g>
  <g id="i-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></g>
  <g id="i-cash"><path d="M2 7h20v10H2zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6M6 10v.01M18 14v.01"/></g>
  <g id="i-box"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16M3.27 6.96 12 12l8.73-5.04M12 22V12"/></g>
  <g id="i-receipt"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1V2l-2 1-2-1-2 1-2-1-2 1-2-1zM8 7h8M8 11h8M8 15h5"/></g>
  <g id="i-chart"><path d="M3 3v18h18M7 14l4-4 3 3 5-6"/></g>
  <g id="i-plus"><path d="M12 5v14M5 12h14"/></g>
  <g id="i-check"><path d="M20 6 9 17l-5-5"/></g>
  <g id="i-chevron"><path d="M15 18l-6-6 6-6"/></g>
  <g id="i-x"><path d="M18 6 6 18M6 6l12 12"/></g>
  <g id="i-arrow"><path d="M19 12H5M12 19l7-7-7-7"/></g>
  <g id="i-search"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></g>
  <g id="i-truck"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8M5.5 18.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM18.5 18.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/></g>
  <g id="i-wallet"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4M3 5v14a2 2 0 0 0 2 2h16v-5M18 12a2 2 0 0 0 0 4h4v-4h-4z"/></g>
  <g id="i-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></g>
  <g id="i-activity"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></g>
  <g id="i-filter"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></g>
  <g id="i-down"><path d="M12 5v14M19 12l-7 7-7-7"/></g>
  <g id="i-send"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></g>
  <g id="i-doc"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></g>
  <g id="i-shield"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></g>
  <g id="i-bar-chart"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></g>
  <g id="i-trending-up"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></g>
  <g id="i-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></g>
  <g id="i-hardhat"><path d="M4 18a8 8 0 0 1 16 0"/><path d="M2 18h20"/><path d="M12 10V4"/><path d="M4 14V11a8 8 0 0 1 16 0v3"/></g>
  <g id="i-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></g>
  <g id="i-print"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></g>
  <g id="i-tool"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></g>
  <g id="i-settings"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></g>
  <g id="i-logout"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></g>
  <g id="i-trash"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></g>
  <g id="i-coins"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></g>
  <g id="i-credit-card"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></g>
  <g id="i-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></g>
  <g id="i-percent"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></g>
  <g id="i-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></g>
  <g id="i-scale"><path d="M12 3v18"/><path d="M5 7h14"/><path d="M6.5 7 3 14a3.5 3.5 0 0 0 7 0z"/><path d="M17.5 7 14 14a3.5 3.5 0 0 0 7 0z"/><path d="M8 21h8"/></g>
  <g id="i-clipboard"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><line x1="8" y1="11" x2="16" y2="11"/><line x1="8" y1="15" x2="14" y2="15"/></g>
  <g id="i-chevron-down"><polyline points="6 9 12 15 18 9"/></g>
</defs></svg>

<aside class="sidebar">
  <div class="brand" style="position:relative">
    <div class="logo">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
    </div>
    <div>
      <h1>{{ $settings->company_name }}</h1>
      <p>نظام إدارة المشاريع</p>
    </div>
  </div>
  <button type="button" id="sidebar-toggle" style="position:absolute; inset-inline-end:-14px; top:39px; transform:translateY(-50%); width:28px; height:28px; background:var(--accent); color:#fff; border-radius:50%; border:3px solid var(--bg); display:grid; place-items:center; cursor:pointer; z-index:50; padding:0; transition: transform 0.2s;">
    <svg class="toggle-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;transition:transform 0.2s"><path d="M15 18l-6-6 6-6"/></svg>
  </button>

  <nav class="nav">
    {{-- Each nav-item gets the "active" class when the current route matches --}}
    {{-- Order follows the real workflow: dashboard → add people (clients/suppliers/workers) → projects & quotes → money → reports/analytics → follow-up → system --}}

    <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" style="--ic:#3b82f6">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-grid"/></svg>
      <span>لوحة التحكم</span>
    </a>

    <div class="nav-label">العملاء والموردون</div>
    <a class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}" style="--ic:#06b6d4">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
      <span>العملاء</span>
    </a>
    <a class="nav-item {{ request()->routeIs('suppliers.*') && !request()->routeIs('suppliers.compare') ? 'active' : '' }}" href="{{ route('suppliers.index') }}" style="--ic:#f97316">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-truck"/></svg>
      <span>الموردون</span>
    </a>
    <a class="nav-item {{ request()->routeIs('craftsmen.*') ? 'active' : '' }}" href="{{ route('craftsmen.index') }}" style="--ic:#ca8a04">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-tool"/></svg>
      <span>الصنايعية ومستحقاتهم</span>
    </a>

    <div class="nav-label">المشاريع</div>
    <a class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}" style="--ic:#6366f1">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      <span>الشقق والمشاريع</span>
    </a>
    <a class="nav-item {{ request()->routeIs('quotes.index') ? 'active' : '' }}" href="{{ route('quotes.index') }}" style="--ic:#8b5cf6">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      <span>عروض الأسعار</span>
    </a>
    <a class="nav-item {{ request()->routeIs('quotes.approved') ? 'active' : '' }}" href="{{ route('quotes.approved') }}" style="--ic:#10b981">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check-circle"/></svg>
      <span>العروض المعتمدة</span>
    </a>

    <div class="nav-label">الحسابات</div>
    <a class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}" style="--ic:#64748b">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
      <span>سجل الحركات</span>
    </a>
    @if(auth()->user()->isAdmin())
    <a class="nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}" href="{{ route('wallet.index') }}" style="--ic:#d4a13d">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
      <span>المحفظة</span>
    </a>
    @endif
    <a class="nav-item {{ request()->routeIs('installments.*') ? 'active' : '' }}" href="{{ route('installments.index') }}" style="--ic:#14b8a6">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-calendar"/></svg>
      <span>المدفوعات والأقساط</span>
    </a>
    <a class="nav-item {{ request()->routeIs('receivables.*') ? 'active' : '' }}" href="{{ route('receivables.index') }}" style="--ic:#22b583">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-coins"/></svg>
      <span>المستحقات</span>
    </a>
    <a class="nav-item {{ request()->routeIs('debts.*') ? 'active' : '' }}" href="{{ route('debts.index') }}" style="--ic:#ef5a4a">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-credit-card"/></svg>
      <span>الديون</span>
    </a>
    <a class="nav-item {{ request()->routeIs('materials.*') ? 'active' : '' }}" href="{{ route('materials.index') }}" style="--ic:#f97316">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
      <span>الخامات والمرتجعات</span>
    </a>

    <div class="nav-label">التقارير والتحليلات</div>
    <a class="nav-item {{ request()->routeIs('reports.dashboard') ? 'active' : '' }}" href="{{ route('reports.dashboard') }}" style="--ic:#8b5cf6">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bar-chart"/></svg>
      <span>التقارير</span>
    </a>
    <a class="nav-item {{ request()->routeIs('reports.profitability') ? 'active' : '' }}" href="{{ route('reports.profitability') }}" style="--ic:#10b981">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-percent"/></svg>
      <span>ربحية المشاريع</span>
    </a>
    <a class="nav-item {{ request()->routeIs('reports.statement*') ? 'active' : '' }}" href="{{ route('reports.statement.index') }}" style="--ic:#06b6d4">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      <span>كشف حساب العميل</span>
    </a>
    @if(auth()->user()->canSeeFinancials())
    <a class="nav-item {{ request()->routeIs('reports.estimation.*') ? 'active' : '' }}" href="{{ route('reports.estimation.index') }}" style="--ic:#f59e0b">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-clipboard"/></svg>
      <span>تقدير تكلفة مشروع</span>
    </a>
    @endif
    {{-- <a class="nav-item {{ request()->routeIs('analytics.index') ? 'active' : '' }}" href="{{ route('analytics.index') }}" style="--ic:#3b82f6">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pie-chart"/></svg>
      <span>لوحة التحليلات</span>
    </a> --}}
    <a class="nav-item {{ request()->routeIs('price-history.*') ? 'active' : '' }}" href="{{ route('price-history.index') }}" style="--ic:#0ea5e9">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg>
      <span>متابعة الأسعار</span>
    </a>
    <a class="nav-item {{ request()->routeIs('suppliers.compare') ? 'active' : '' }}" href="{{ route('suppliers.compare') }}" style="--ic:#8b5cf6">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-scale"/></svg>
      <span>مقارنة الموردين</span>
    </a>
{{-- 
    <div class="nav-label">المتابعة</div>
    <a class="nav-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}" href="{{ route('alerts.index') }}" style="--ic:#ef4444">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bell"/></svg>
      <span>المتابعة والتنبيهات</span>
    </a> --}}
    {{-- <a class="nav-item {{ request()->routeIs('warranties.*') ? 'active' : '' }}" href="{{ route('warranties.index') }}" style="--ic:#10b981">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-shield"/></svg>
      <span>متابعة الضمانات</span>
    </a> --}}

    @if(auth()->user()->isAdmin())
      <div class="nav-label">النظام</div>
      <a class="nav-item {{ request()->routeIs('settings.edit') ? 'active' : '' }}" href="{{ route('settings.edit') }}" style="--ic:#94a3b8">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-settings"/></svg>
        <span>الإعدادات</span>
      </a>
    @endif
  </nav>

  <div class="sb-foot">
    نسخة 1.0 · {{ now()->format('Y') }}
  </div>
</aside>

@php
  // هوية لونية + أيقونة مميّزة لكل شاشة — بتتحدد من اسم الراوت. كل شاشة بتاخد
  // لونها الخاص اللي بيتطبّق تلقائيًا على أزرارها وعناوينها وتاباتها ورأس
  // جداولها (عن طريق متغيّرات CSS على .page-wrap)، وأيقونة جنب اسم الصفحة.
  // الصيغة: [accent, accent-2 (فاتح للتدرّج), accent-soft (خلفية خفيفة), accent-ink (غامق للنص), icon]
  $routeSeg = explode('.', request()->route()?->getName() ?? '')[0];
  $pageThemes = [
    'dashboard'     => ['#2563eb','#3b82f6','#e5edff','#1d4ed8','i-grid'],
    'clients'       => ['#0891b2','#06b6d4','#e0f7fa','#0e7490','i-users'],
    'suppliers'     => ['#ea7317','#f97316','#fff0e0','#c2560f','i-truck'],
    'labor'         => ['#b45309','#d97706','#fdf1dd','#92400e','i-hardhat'],
    'craftsmen'     => ['#a16207','#ca8a04','#fef7e0','#854d0e','i-tool'],
    'projects'      => ['#4f46e5','#6366f1','#ececfe','#4338ca','i-building'],
    'quotes'        => ['#7c3aed','#8b5cf6','#f2ecfe','#5b21b6','i-doc'],
    'transactions'  => ['#475569','#64748b','#eef2f7','#334155','i-activity'],
    'wallet'        => ['#b8842a','#d4a13d','#fbf1de','#8a631d','i-wallet'],
    'installments'  => ['#0d9488','#14b8a6','#dcf7f2','#0f766e','i-calendar'],
    'receivables'   => ['#12936a','#22b583','#e3f6ee','#0b6b49','i-coins'],
    'debts'         => ['#d63b2c','#ef5a4a','#fdeae7','#b02419','i-credit-card'],
    'materials'     => ['#ea580c','#f97316','#ffedd5','#c2410c','i-box'],
    'expenses'      => ['#ea580c','#f97316','#ffedd5','#c2410c','i-box'],
    'returns'       => ['#ea580c','#f97316','#ffedd5','#c2410c','i-box'],
    'reports'       => ['#7c3aed','#8b5cf6','#f2ecfe','#5b21b6','i-bar-chart'],
    'analytics'     => ['#4f46e5','#6366f1','#ececfe','#4338ca','i-pie-chart'],
    'price-history' => ['#0891b2','#06b6d4','#e0f7fa','#0e7490','i-trending-up'],
    'alerts'        => ['#dc2626','#ef4444','#fdeae7','#b91c1c','i-bell'],
    'warranties'    => ['#059669','#10b981','#e3f6ee','#047857','i-shield'],
    'settings'      => ['#475569','#64748b','#eef2f7','#334155','i-settings'],
    'search'        => ['#2563eb','#3b82f6','#e5edff','#1d4ed8','i-search'],
    'bands'         => ['#4f46e5','#6366f1','#ececfe','#4338ca','i-building'],
    'workers'       => ['#a16207','#ca8a04','#fef7e0','#854d0e','i-tool'],
  ];
  $T = $pageThemes[$routeSeg] ?? $pageThemes['dashboard'];
@endphp

<div class="main">
  {{-- Sticky top bar with page title and user info --}}
  <div class="topbar">
    <div class="page-ic" style="background:{{ $T[2] }};color:{{ $T[0] }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#{{ $T[4] }}"/></svg>
    </div>
    <h2>@yield('page-title', 'لوحة التحكم')</h2>
    <form method="GET" action="{{ route('search.index') }}" class="topbar-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-search"/></svg>
      <input type="search" name="q" placeholder="ابحث عن مشروع، عميل، مورد، صنف، مرتجع، أو دين..." value="{{ request('q') }}">
    </form>
    <div class="right">
      @if(auth()->user()->isAdmin())
      {{-- تصفير قاعدة البيانات (للتجارب فقط) — يمسح كل بيانات المقاولات --}}
      <form method="POST" action="{{ route('maintenance.reset') }}" style="margin:0"
            onsubmit="return confirm('⚠️ تحذير: هيتم مسح كل بيانات المقاولات (المشاريع، الخامات، الحركات، العروض...) وتصفير المحفظة نهائيًا.\n\nالخطوة دي للتيست فقط ومش ممكن التراجع عنها.\n\nمتأكد إنك عايز تكمل؟');">
        @csrf
        <button type="submit" class="btn danger sm" title="تصفير كل بيانات المقاولات — للتيست فقط">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
          تصفير الداتا
        </button>
      </form>
      @endif
      {{-- Logout button --}}
      <form method="POST" action="{{ route('logout') }}" style="margin:0">
        @csrf
        <button type="submit" class="btn ghost sm">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-logout"/></svg>
          تسجيل الخروج
        </button>
      </form>
      <div class="user">
        {{-- First letter of user's name as avatar --}}
        <div class="av">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
        <div>
          <div class="nm">{{ auth()->user()->name }}</div>
          <div class="rl">{{ ['admin' => 'مدير', 'employee' => 'موظف', 'viewer' => 'مطالع'][auth()->user()->role] ?? 'مستخدم' }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Page content — كل صفحة بتاخد لونها الخاص عن طريق المتغيّرات دي --}}
  <div class="page-wrap" style="--accent:{{ $T[0] }};--accent-2:{{ $T[1] }};--accent-soft:{{ $T[2] }};--accent-ink:{{ $T[3] }}">

    {{-- Flash messages (success / error) from session --}}
    @if(session('success'))
      <div class="flash success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
        {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="flash error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
        {{ session('error') }}
      </div>
    @endif

    @yield('content')
  </div>
</div>

{{-- flatpickr: كل حقول <input type="date"> تتعرض dd/mm/yyyy (يوم/شهر/سنة) للمستخدم
     بينما القيمة اللي بتتبعت للسيرفر تفضل Y-m-d. بيشتغل كمان على الحقول اللي بتتضاف
     ديناميكيًا (فورمات الخامات) أو اللي بتتحمّل عبر AJAX (كشف حساب الأقساط) عن طريق
     MutationObserver. --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://npmcdn.com/flatpickr@4.6.13/dist/l10n/ar.js"></script>
<script>
(function () {
  const OPTS = {
    dateFormat: 'Y-m-d',   // القيمة المُرسَلة
    altInput: true,
    altFormat: 'd/m/Y',    // اللي بيشوفه المستخدم: يوم/شهر/سنة
    allowInput: true,
    locale: (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.ar) ? 'ar' : 'default',
  };
  function initDatePickers(root) {
    (root || document).querySelectorAll('input[type="date"]:not([data-fp])').forEach(el => {
      el.dataset.fp = '1';
      try { flatpickr(el, OPTS); } catch (e) {}
    });
  }
  window.initDatePickers = initDatePickers;
  document.addEventListener('DOMContentLoaded', () => initDatePickers(document));
  // امسك أي حقل تاريخ يتضاف بعد تحميل الصفحة (فورمات ديناميكية / محتوى AJAX)
  new MutationObserver(muts => {
    for (const m of muts) {
      for (const n of m.addedNodes) {
        if (n.nodeType !== 1) continue;
        if (n.matches && n.matches('input[type="date"]')) initDatePickers(n.parentNode || document);
        else if (n.querySelectorAll) initDatePickers(n);
      }
    }
  }).observe(document.body, { childList: true, subtree: true });
})();
</script>

{{-- شاشة التحميل: بتظهر تلقائيًا مع أي إرسال فورم أو ضغط على لينك داخلي —
     مفيش داعي أي صفحة تستدعيها بنفسها. بتقفل الزرار وقت الإرسال كمان عشان
     تمنع ضغط مزدوج (دفعة اتسجلت مرتين بالغلط لو المستخدم ضغط تاني وهو مستني). --}}
<script>
(function () {
  const overlay = document.getElementById('constrLoader');
  if (!overlay) return;
  let shown = false;
  function show() { if (shown) return; shown = true; overlay.classList.add('show'); }
  function hide() { shown = false; overlay.classList.remove('show'); }
  window.showConstructionLoader = show;
  window.hideConstructionLoader = hide;

  // مهم: من غير capture عشان أي onsubmit="return confirm(...)" على الفورم نفسه
  // (زي حذف/تصفير) يتنفّذ الأول — لو المستخدم دوس "إلغاء" في الـ confirm،
  // الفورم بيعمل preventDefault وإحنا هنشوف e.defaultPrevented ونتجاهل الحدث
  // بدل ما نفضل الشاشة والزرار المقفول عالقين من غير ما حاجة تتبعت فعلاً.
  document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.hasAttribute('data-no-loading')) return;
    show();
    form.querySelectorAll('button[type="submit"], button:not([type])').forEach(function (btn) {
      if (!btn.disabled) btn.disabled = true;
    });
  });

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented) return;
    const a = e.target.closest('a[href]');
    if (!a || a.hasAttribute('data-no-loading') || a.target === '_blank' || a.hasAttribute('download')) return;
    const href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
    if (a.origin !== window.location.origin) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
    show();
  });

  // رجوع/تقدّم من الكاش (bfcache) بيسيب الصفحة زي ما كانت وقت الخروج منها —
  // لازم نقفل الشاشة تاني وإلا هتفضل عالقة لو المستخدم رجع بزرار المتصفح
  window.addEventListener('pageshow', function () { hide(); });
})();
</script>

<script>
  // Sidebar toggle logic
  const toggleBtn = document.getElementById('sidebar-toggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('sidebar-collapsed');
      localStorage.setItem('sy2-sidebar', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
    });
  }
  // Restore state on load (add this script directly to prevent FOUC, but doing it here is fine since body is already parsing)
  if (localStorage.getItem('sy2-sidebar') === '1') {
    document.body.classList.add('sidebar-collapsed');
  }
</script>

<style>
  body.sidebar-collapsed #sidebar-toggle .toggle-ic {
    transform: rotate(180deg);
  }
</style>

@stack('scripts')
</body>
</html>
