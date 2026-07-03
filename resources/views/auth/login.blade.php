<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تسجيل الدخول — شركة الضبع</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/sy2.css') }}">
</head>
<body>

<svg width="0" height="0" style="position:absolute"><defs>
  <g id="i-building"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></g>
  <g id="i-check"><path d="M20 6 9 17l-5-5"/></g>
  <g id="i-x"><path d="M18 6 6 18M6 6l12 12"/></g>
</defs></svg>

<div class="login-wrap">
  <div class="login-card">
    {{-- Company logo --}}
    <div class="login-logo">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
    </div>
    <h2>شركة الضبع للتجارة</h2>
    <p class="sub">نظام إدارة المشاريع — تسجيل الدخول</p>

    {{-- Show validation errors --}}
    @if($errors->any())
      <div class="flash error" style="margin-bottom:20px">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="field">
        <label for="email">البريد الإلكتروني</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               placeholder="ahmed@aldabaa.com" autocomplete="email" required>
      </div>
      <div class="field">
        <label for="password">كلمة المرور</label>
        <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px">
        <input type="checkbox" id="remember" name="remember" style="width:auto">
        <label for="remember" style="font-size:13px;color:var(--ink-2);margin-bottom:0;font-weight:500">تذكرني</label>
      </div>
      <button type="submit" class="btn" style="width:100%;justify-content:center;padding:12px">
        دخول
      </button>
    </form>
  </div>
</div>

</body>
</html>
