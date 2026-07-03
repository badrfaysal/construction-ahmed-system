<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'نظام الإدارة') — {{ $settings->company_name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/sy2.css') }}">
@stack('styles')
</head>
<body>

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
</defs></svg>

<aside class="sidebar">
  <div class="brand">
    <div class="logo">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
    </div>
    <div>
      <h1>{{ $settings->company_name }}</h1>
      <p>نظام إدارة المشاريع</p>
    </div>
  </div>

  <nav class="nav">
    {{-- Each nav-item gets the "active" class when the current route matches --}}

    <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-grid"/></svg>
      لوحة التحكم
    </a>

    <div class="nav-label">المشاريع</div>
    <a class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      الشقق والمشاريع
    </a>
    <a class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
      العملاء
    </a>

    <div class="nav-label">الحسابات</div>
    <a class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
      سجل الحركات
    </a>
    <a class="nav-item {{ request()->routeIs('installments.*') ? 'active' : '' }}" href="{{ route('installments.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      المدفوعات والأقساط
    </a>
    <a class="nav-item {{ request()->routeIs('receivables.*') ? 'active' : '' }}" href="{{ route('receivables.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg>
      المستحقات
    </a>
    <a class="nav-item {{ request()->routeIs('debts.*') ? 'active' : '' }}" href="{{ route('debts.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
      الديون
    </a>
    <a class="nav-item {{ request()->routeIs('materials.*') ? 'active' : '' }}" href="{{ route('materials.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
      الخامات والمرتجعات
    </a>
    <a class="nav-item {{ request()->routeIs('labor.*') ? 'active' : '' }}" href="{{ route('labor.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
      الفنيين والعمال
    </a>

    <div class="nav-label">التقارير</div>
    <a class="nav-item {{ request()->routeIs('reports.statement*') ? 'active' : '' }}" href="{{ route('reports.statement.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      كشف حساب العميل
    </a>
    <a class="nav-item {{ request()->routeIs('reports.profitability') ? 'active' : '' }}" href="{{ route('reports.profitability') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg>
      ربحية المشاريع
    </a>
    <a class="nav-item {{ request()->routeIs('reports.dashboard') ? 'active' : '' }}" href="{{ route('reports.dashboard') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bar-chart"/></svg>
      التقارير
    </a>

    <div class="nav-label">المتابعة</div>
    <a class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-truck"/></svg>
      الموردون
    </a>
    <a class="nav-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}" href="{{ route('alerts.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bell"/></svg>
      المتابعة والتنبيهات
    </a>

    <div class="nav-label">عروض الأسعار</div>
    <a class="nav-item {{ request()->routeIs('quotes.index') ? 'active' : '' }}" href="{{ route('quotes.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      عروض الأسعار
    </a>
    <a class="nav-item {{ request()->routeIs('quotes.approved') ? 'active' : '' }}" href="{{ route('quotes.approved') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check-circle"/></svg>
      العروض المعتمدة
    </a>

    <div class="nav-label">تحليلات</div>
    <a class="nav-item {{ request()->routeIs('analytics.index') ? 'active' : '' }}" href="{{ route('analytics.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bar-chart"/></svg>
      لوحة التحليلات
    </a>
    <a class="nav-item {{ request()->routeIs('analytics.technicians') ? 'active' : '' }}" href="{{ route('analytics.technicians') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
      تقرير الفنيين
    </a>
    <a class="nav-item {{ request()->routeIs('warranties.*') ? 'active' : '' }}" href="{{ route('warranties.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-shield"/></svg>
      متابعة الضمانات
    </a>
    <a class="nav-item {{ request()->routeIs('price-history.*') ? 'active' : '' }}" href="{{ route('price-history.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg>
      متابعة الأسعار
    </a>
    <a class="nav-item {{ request()->routeIs('suppliers.compare') ? 'active' : '' }}" href="{{ route('suppliers.compare') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
      مقارنة الموردين
    </a>

    @if(auth()->user()->isAdmin())
      <div class="nav-label">النظام</div>
      <a class="nav-item {{ request()->routeIs('settings.edit') ? 'active' : '' }}" href="{{ route('settings.edit') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-grid"/></svg>
        الإعدادات
      </a>
    @endif
  </nav>

  <div class="sb-foot">
    نسخة 1.0 · {{ now()->format('Y') }}
  </div>
</aside>

<div class="main">
  {{-- Sticky top bar with page title and user info --}}
  <div class="topbar">
    <h2>@yield('page-title', 'لوحة التحكم')</h2>
    <form method="GET" action="{{ route('search.index') }}" class="topbar-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-search"/></svg>
      <input type="search" name="q" placeholder="ابحث عن صنف، مشروع، أو مورد..." value="{{ request('q') }}">
    </form>
    <div class="right">
      {{-- Logout button --}}
      <form method="POST" action="{{ route('logout') }}" style="margin:0">
        @csrf
        <button type="submit" class="btn ghost sm">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
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

  {{-- Page content --}}
  <div class="page-wrap">

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

@stack('scripts')
</body>
</html>
