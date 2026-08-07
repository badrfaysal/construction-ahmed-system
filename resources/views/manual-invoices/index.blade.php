@extends('layouts.app')
@section('title', 'سجل الفواتير اليدوية')
@section('page-title', 'الفواتير اليدوية')

@section('content')

<!-- Colorful Random Shapes Background -->
<div class="mesh-bg">
  <div class="shape s1"></div>
  <div class="shape s2"></div>
  <div class="shape s3"></div>
  <div class="shape s4"></div>
  <svg class="geo g1" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#bae6fd" d="M45.7,-76.4C58.9,-69.3,69.1,-55.3,77.7,-40.8C86.3,-26.3,93.4,-11.2,91.8,3.2C90.2,17.7,80,31.5,70.1,43.9C60.2,56.3,50.7,67.4,38.4,74.5C26.1,81.6,11,84.7,-4.3,86.6C-19.6,88.5,-35.3,89.2,-48.5,82.4C-61.7,75.6,-72.4,61.3,-79.8,45.8C-87.2,30.3,-91.3,13.6,-88.7,-1.8C-86,-17.2,-76.5,-31.2,-65.4,-42.6C-54.3,-54,-41.6,-62.8,-28.1,-69.7C-14.6,-76.6,0.3,-81.6,15.6,-79.3C30.9,-77,46.2,-67.4,45.7,-76.4Z" transform="translate(100 100)" /></svg>
  <svg class="geo g2" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#a5f3fc" d="M37.8,-62C47.8,-52.1,53.8,-37.9,59.3,-23.4C64.8,-8.9,69.8,5.9,67.3,19.3C64.8,32.7,54.8,44.7,42.5,52.4C30.2,60.1,15.1,63.5,0.2,63.2C-14.7,62.9,-29.4,58.9,-41.8,51.1C-54.2,43.3,-64.3,31.7,-68.8,18.3C-73.3,4.9,-72.2,-10.3,-66,-23.4C-59.8,-36.5,-48.5,-47.5,-36,-56.3C-23.5,-65.1,-9.8,-71.7,2.8,-75.7C15.4,-79.7,30.8,-81.1,37.8,-62Z" transform="translate(100 100)" /></svg>
  <svg class="geo g3" viewBox="0 0 24 24" fill="none" stroke="#7dd3fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 22 20 2 20"/></svg>
  <svg class="geo g4" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
</div>

<style>
  .mesh-bg { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; overflow: hidden; pointer-events: none; }
  .mesh-bg .shape { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.5; animation: floatBg 25s ease-in-out infinite alternate; }
  .mesh-bg .s1 { width: 400px; height: 400px; background: #bfdbfe; top: -10%; left: -5%; animation-delay: 0s; }
  .mesh-bg .s2 { width: 500px; height: 500px; background: #a5f3fc; bottom: -10%; right: -10%; animation-delay: -5s; }
  .mesh-bg .s3 { width: 350px; height: 350px; background: #c7d2fe; top: 40%; left: 40%; animation-delay: -12s; }
  .mesh-bg .s4 { width: 450px; height: 450px; background: #bae6fd; top: -20%; right: 20%; animation-delay: -18s; }
  
  .mesh-bg .geo { position: absolute; opacity: 0.5; animation: floatShapes 18s linear infinite; }
  .mesh-bg .g1 { width: 300px; height: 300px; bottom: 5%; left: 15%; animation-delay: -2s; }
  .mesh-bg .g2 { width: 250px; height: 250px; top: 15%; right: 5%; animation-delay: -8s; }
  .mesh-bg .g3 { width: 120px; height: 120px; top: 50%; right: 30%; animation-delay: -5s; stroke-dasharray: 4 4; fill: rgba(125,211,252,0.2); }
  .mesh-bg .g4 { width: 180px; height: 180px; bottom: 25%; left: 45%; animation-delay: -11s; opacity: 0.15; }

  @keyframes floatBg {
    0% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(40px, -60px) scale(1.1); }
    66% { transform: translate(-30px, 30px) scale(0.9); }
    100% { transform: translate(50px, 50px) scale(1.05); }
  }
  @keyframes floatShapes {
    0% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(20px, -40px) rotate(180deg); }
    100% { transform: translate(0, 0) rotate(360deg); }
  }
  
  /* Make cards slightly translucent so background shows through */
  .page-head, .card, .mi-stat-card {
    background: rgba(255, 255, 255, 0.75) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
  }
  .page-head { background: linear-gradient(135deg, rgba(240,249,255,0.85), rgba(224,242,254,0.85)) !important; }
  .mi-stat-card:nth-child(1) { background: linear-gradient(135deg, rgba(239,246,255,0.85), rgba(219,234,254,0.85)) !important; }
  .mi-stat-card:nth-child(2) { background: linear-gradient(135deg, rgba(236,254,255,0.85), rgba(207,250,254,0.85)) !important; }
  .mi-stat-card:nth-child(3) { background: linear-gradient(135deg, rgba(238,242,255,0.85), rgba(224,231,255,0.85)) !important; }
  .mi-stat-card:nth-child(4) { background: linear-gradient(135deg, rgba(248,250,252,0.85), rgba(241,245,249,0.85)) !important; }
  .mi-items-table thead { background: rgba(248,250,252,0.6) !important; }
</style>

<div class="page-head no-print" style="position: relative; overflow: hidden; padding: 24px; border-radius: 16px; margin-bottom: 24px; border: 1px solid #bae6fd; box-shadow: 0 8px 32px rgba(0,0,0,0.05);">
  
  <!-- Decorative Background Icons -->
  <svg class="decor-icon" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; width: 120px; height: 120px; right: -20px; top: -30px; opacity: 0.03; transform: rotate(-15deg); animation: float1 8s ease-in-out infinite;"><use href="#i-doc"/></svg>
  <svg class="decor-icon" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; width: 80px; height: 80px; left: 30%; bottom: -20px; opacity: 0.03; transform: rotate(20deg); animation: float2 10s ease-in-out infinite;"><use href="#i-receipt"/></svg>
  <svg class="decor-icon" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; width: 100px; height: 100px; left: 10%; top: -10px; opacity: 0.03; transform: rotate(-10deg); animation: float1 7s ease-in-out infinite alternate;"><use href="#i-clipboard"/></svg>

  <div style="position: relative; z-index: 2;">
    <h3 style="color: #1e40af; font-weight: 900; font-size: 26px; margin: 0; line-height: 1.4;">سجل الفواتير اليدوية</h3>
    <p style="color: #0284c7; font-weight: 600; margin-top: 4px;">جميع الفواتير اليدوية المحفوظة في النظام</p>
  </div>
  <div class="btn-row" style="position: relative; z-index: 2;">
    <a href="{{ route('manual_invoices.create') }}" class="btn" style="background: linear-gradient(45deg, #0284c7, #2563eb); border: none; font-weight: 800; padding: 12px 20px; box-shadow: 0 4px 12px rgba(37,99,235,0.3); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(37,99,235,0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(37,99,235,0.3)';">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><use href="#i-plus-circle"/></svg>
      إنشاء فاتورة جديدة
    </a>
  </div>
</div>

<style>
  .mi-layout { display: grid; grid-template-columns: 1fr 300px; gap: 20px; align-items: start; }
  .mi-stat-card { position: relative; overflow: hidden; background: #fff; padding: 24px 20px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid var(--line); margin-bottom: 16px; text-align: center; z-index: 1; }
  .mi-stat-title { font-size: 14px; font-weight: 800; margin-bottom: 8px; z-index: 2; position: relative; }
  .mi-stat-value { font-size: 26px; font-weight: 900; z-index: 2; position: relative; }
  .mi-stat-value { font-size: 26px; font-weight: 900; z-index: 2; position: relative; }
  
  @keyframes floatIcon {
    0% { transform: translateY(0) rotate(15deg); }
    50% { transform: translateY(-8px) rotate(22deg); }
    100% { transform: translateY(0) rotate(15deg); }
  }
  @keyframes float1 {
    0% { transform: translateY(0) rotate(-15deg); }
    50% { transform: translateY(-15px) rotate(-5deg); }
    100% { transform: translateY(0) rotate(-15deg); }
  }
  @keyframes float2 {
    0% { transform: translateY(0) rotate(20deg); }
    50% { transform: translateY(-10px) rotate(30deg); }
    100% { transform: translateY(0) rotate(20deg); }
  }

  .mi-stat-bg-icon {
    position: absolute;
    left: -15px;
    bottom: -10px;
    font-size: 100px;
    opacity: 0.04;
    z-index: 0;
    transform: rotate(15deg);
    pointer-events: none;
    animation: floatIcon 6s ease-in-out infinite;
  }
  @media (max-width: 900px) {
    .mi-layout { grid-template-columns: 1fr; }
    .mi-layout > .mi-sidebar { grid-row: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .mi-layout > .mi-sidebar .mi-stat-card { margin-bottom: 0; }
  }
  @media (max-width: 600px) {
    .mi-layout > .mi-sidebar { grid-template-columns: 1fr; }
  }
</style>

<div class="mi-layout">
  <div class="mi-main">


{{-- Filters --}}
<div class="card" style="margin-bottom:20px; background: linear-gradient(120deg, #f8fafc, #f1f5f9); border: 1px solid #e2e8f0; box-shadow: inset 0 2px 4px rgba(255,255,255,0.7), 0 2px 6px rgba(0,0,0,0.02);">
  <div class="card-pad">
    <form method="GET" action="{{ route('manual_invoices.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
      <div class="field" style="flex:1;min-width:180px;">
        <label style="font-size:12px;font-weight:700;margin-bottom:4px;display:block;">اسم العميل</label>
        <input type="text" name="client" class="inp" value="{{ request('client') }}" placeholder="بحث بالاسم...">
      </div>
      <div class="field" style="min-width:140px;">
        <label style="font-size:12px;font-weight:700;margin-bottom:4px;display:block;">الحالة</label>
        <select name="status" class="inp">
          <option value="">الكل</option>
          <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
          <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>نهائية</option>
        </select>
      </div>
      <div class="field" style="min-width:150px;">
        <label style="font-size:12px;font-weight:700;margin-bottom:4px;display:block;">من تاريخ</label>
        <input type="text" name="from" class="inp flatpickr-date" value="{{ request('from') }}" placeholder="من">
      </div>
      <div class="field" style="min-width:150px;">
        <label style="font-size:12px;font-weight:700;margin-bottom:4px;display:block;">إلى تاريخ</label>
        <input type="text" name="to" class="inp flatpickr-date" value="{{ request('to') }}" placeholder="إلى">
      </div>
      <button type="submit" class="btn sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-search"/></svg>
        بحث
      </button>
      @if(request()->hasAny(['client','status','from','to']))
        <a href="{{ route('manual_invoices.index') }}" class="btn ghost sm">مسح الفلتر</a>
      @endif
    </form>
  </div>
</div>

{{-- Invoices Table --}}
<div class="card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
  <div style="overflow-x:auto;">
    <table class="tbl" style="margin:0; width:100%;">
      <thead style="background: linear-gradient(90deg, #f8fafc, #eff6ff); border-bottom: 2px solid #bfdbfe;">
        <tr>
          <th style="color: #1e40af; font-weight: 800; padding: 14px 16px;">رقم الفاتورة</th>
          <th>التاريخ</th>
          <th>العميل</th>
          <th>عدد الأصناف</th>
          <th>الإجمالي</th>
          <th>المدفوع</th>
          <th>المتبقي</th>
          <th>الحالة</th>
          <th style="width:140px;">إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr style="transition: all 0.2s ease; border-bottom: 1px solid #f1f5f9;">
            <td style="font-weight:800; color:#0284c7;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;opacity:0.5;margin-inline-end:2px;"><use href="#i-doc"/></svg> {{ $inv->invoice_number }}</td>
            <td style="color:#475569; font-weight:500;">{{ $inv->date->format('Y-m-d') }}</td>
            <td style="font-weight:700; color:#1e293b;"><svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;margin-inline-end:4px;vertical-align:-3px;"><use href="#i-users"/></svg> {{ $inv->client_name }}</td>
            <td><span style="background:#f1f5f9; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:700; color:#475569;">{{ $inv->items_count }} صنف</span></td>
            <td style="font-weight:700;">{{ \App\Support\Money::format($inv->grandTotal()) }}</td>
            <td style="color:var(--pos);font-weight:600;">{{ \App\Support\Money::format($inv->paid_amount) }}</td>
            <td style="color:{{ $inv->remaining() > 0 ? '#b91c1c' : 'var(--pos)' }};font-weight:700;">
              {{ \App\Support\Money::format($inv->remaining()) }}
            </td>
            <td><span class="tag {{ $inv->statusTag() }}">{{ $inv->statusAr() }}</span></td>
            <td>
              <div style="display:flex;gap:6px;">
                <a href="{{ route('manual_invoices.show', $inv) }}" class="btn ghost sm" title="عرض">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-doc"/></svg>
                </a>
                <a href="{{ route('manual_invoices.edit', $inv) }}" class="btn ghost sm" title="تعديل">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-tool"/></svg>
                </a>
                <form method="POST" action="{{ route('manual_invoices.destroy', $inv) }}" style="margin:0;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn ghost sm" style="color:#ef4444;" title="حذف">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-trash"/></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="muted" style="text-align:center;padding:40px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:40px;height:40px;margin-bottom:8px;opacity:.4"><use href="#i-receipt"/></svg>
              <br>لا توجد فواتير يدوية بعد
              <br><a href="{{ route('manual_invoices.create') }}" style="color:var(--accent);font-weight:700;margin-top:8px;display:inline-block;">إنشاء فاتورة جديدة</a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($invoices->hasPages())
  <div style="margin-top:20px;display:flex;justify-content:center;">
    {{ $invoices->links() }}
  </div>
@endif
  </div>

  <div class="mi-sidebar">
    <div class="mi-stat-card" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #bfdbfe; color: #1e40af;">
      <svg class="mi-stat-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-clipboard"/></svg>
      <div class="mi-stat-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:-3px;"><use href="#i-clipboard"/></svg> إجمالي الفواتير</div>
      <div class="mi-stat-value">{{ $totals['count'] }} <span style="font-size:14px;font-weight:700;opacity:0.8">فاتورة</span></div>
    </div>
    
    <div class="mi-stat-card" style="background: linear-gradient(135deg, #ecfeff, #cffafe); border-color: #a5f3fc; color: #0e7490;">
      <svg class="mi-stat-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
      <div class="mi-stat-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:-3px;"><use href="#i-receipt"/></svg> فواتير ضريبية</div>
      <div class="mi-stat-value">{{ $stats['tax_invoices'] }}</div>
    </div>
    
    @if($stats['last_client'])
    <div class="mi-stat-card" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe; color: #4338ca;">
      <svg class="mi-stat-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      <div class="mi-stat-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:-3px;"><use href="#i-doc"/></svg> آخر فاتورة لعميل</div>
      <div class="mi-stat-value" style="font-size:20px;">{{ $stats['last_client'] }}</div>
    </div>
    @endif
    
    @if($stats['top_client'])
    <div class="mi-stat-card" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-color: #e2e8f0; color: #334155;">
      <svg class="mi-stat-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>
      <div class="mi-stat-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:-3px;"><use href="#i-users"/></svg> أكثر عميل اتعمله فواتير</div>
      <div class="mi-stat-value" style="font-size:20px;">{{ $stats['top_client'] }}</div>
      <div style="font-size:13px;font-weight:700;margin-top:8px; opacity:0.9;">
        بإجمالي: {{ \App\Support\Money::format($stats['top_client_total']) }} ج.م <br>
        ({{ $stats['top_client_count'] }} فاتورة)
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
