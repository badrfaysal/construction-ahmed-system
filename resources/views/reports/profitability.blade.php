@extends('layouts.app')
@section('title', 'ربحية المشاريع')
@section('page-title', 'ربحية المشاريع')

@section('content')
<div class="page-head">
  <div><h3>ربحية المشاريع</h3><p>ربح دفتري = سعر البيع + الإشراف − التكلفة | ربح محصل = المحصل فعلاً − التكلفة</p></div>
</div>

@push('styles')
<style>
.vstat { padding: 16px; min-height: 110px; }
.vstat:hover .ic { transform: scale(1.15) rotate(8deg); background: rgba(255, 255, 255, 0.3); }
.vstat .vstat-bg { position: absolute; left: -10px; bottom: -15px; width: 90px; height: 90px; color: rgba(255, 255, 255, 0.15); z-index: 0; transform: rotate(-15deg); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none; }
.vstat:hover .vstat-bg { transform: scale(1.2) rotate(5deg); color: rgba(255, 255, 255, 0.25); }
.vstat .val { font-size: 1.5rem; }
.vstat .sub { font-size: 0.75rem; color: rgba(255, 255, 255, 0.8); margin-top: 4px; }
</style>
@endpush

{{-- Summary KPIs --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px">
  
  <div class="vstat vstat-red">
    <div class="top">
      <span class="label">إجمالي التكلفة</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['total_spent']) }} <small>ج.م</small></div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg>
  </div>

  <div class="vstat {{ $totals['book_profit'] >= 0 ? 'vstat-green' : 'vstat-red' }}">
    <div class="top">
      <span class="label">ربح دفتري</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['book_profit']) }} <small>ج.م</small></div>
    <div class="sub">المفوتر: {{ \App\Support\Money::format($totals['total_billed']) }} ج.م</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg>
  </div>

  <div class="vstat {{ $totals['earned_profit'] >= 0 ? 'vstat-blue' : 'vstat-red' }}">
    <div class="top">
      <span class="label">ربح محصل</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['earned_profit']) }} <small>ج.م</small></div>
    <div class="sub">المحصّل: {{ \App\Support\Money::format($totals['total_collected']) }} ج.م</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg>
  </div>

  <div class="vstat vstat-amber">
    <div class="top">
      <span class="label">الربح التجاري</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['trade_profit']) }} <small>ج.م</small></div>
    <div class="sub">{{ number_format($totals['trade_profit_share'], 1) }}% من التكلفة</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
  </div>

  <div class="vstat vstat-teal">
    <div class="top">
      <span class="label">نسبة الإشراف</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-percent"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['percentage_profit']) }} <small>ج.م</small></div>
    <div class="sub">{{ number_format($totals['percentage_profit_share'], 1) }}% من التكلفة</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-percent"/></svg>
  </div>

  <div class="vstat" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color:#fff">
    <div class="top">
      <span class="label">أرباح التقسيط</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-clock"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totals['installment_profit']) }} <small>ج.م</small></div>
    <div class="sub">{{ number_format($totals['installment_profit_share'], 1) }}% من التكلفة</div>
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-clock"/></svg>
  </div>
</div>

<div class="table-card">
  @if($projects->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>المشروع</th>
            <th>العميل</th>
            <th>الحالة</th>
            <th class="num">التكلفة</th>
            <th class="num">المفوتر للعميل</th>
            <th class="num">الخصم</th>
            <th class="num">ربح دفتري</th>
            <th class="num">هامش %</th>
            <th class="num">ربح تجاري</th>
            <th class="num">نسبة إشراف</th>
            <th class="num">أرباح تقسيط</th>
            <th class="num">المحصّل</th>
            <th class="num">ربح محصل</th>
          </tr>
        </thead>
        <tbody>
          @foreach($projects as $project)
            <tr class="row-click" onclick="location.href='{{ route('projects.show', $project) }}'">
              <td><strong>{{ $project->name }}</strong></td>
              <td class="muted">{{ $project->client->name }}</td>
              <td>
                @if($project->status === 'done')
                  <span class="tag green">منتهي</span>
                @else
                  <span class="tag blue">نشط</span>
                @endif
              </td>
              <td class="num">{{ \App\Support\Money::format($project->total_spent) }}</td>
              <td class="num">{{ \App\Support\Money::format($project->total_billed) }}</td>
              <td class="num" style="color:var(--amber)">{{ \App\Support\Money::format($project->total_discount) }}</td>
              <td class="num" style="color:{{ $project->book_profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                {{ \App\Support\Money::format($project->book_profit) }}
              </td>
              <td class="num" style="color:{{ $project->book_margin >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                {{ number_format($project->book_margin, 1) }}%
              </td>
              <td class="num" style="color:{{ $project->trade_profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                {{ \App\Support\Money::format($project->trade_profit) }}
                <div class="muted" style="font-size:11px">{{ number_format($project->trade_profit_share, 1) }}%</div>
              </td>
              <td class="num" style="color:{{ $project->percentage_profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                {{ \App\Support\Money::format($project->percentage_profit) }}
                <div class="muted" style="font-size:11px">{{ number_format($project->percentage_profit_share, 1) }}%</div>
              </td>
              <td class="num" style="color:{{ $project->installment_profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                {{ \App\Support\Money::format($project->installment_profit) }}
                <div class="muted" style="font-size:11px">{{ number_format($project->installment_profit_share, 1) }}%</div>
              </td>
              <td class="num">{{ \App\Support\Money::format($project->total_collected) }}</td>
              <td class="num" style="color:{{ $project->earned_profit >= 0 ? 'var(--pos)' : 'var(--neg)' }}">
                {{ \App\Support\Money::format($project->earned_profit) }}
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3"><strong>الإجماليات</strong></td>
            <td class="num">{{ \App\Support\Money::format($totals['total_spent']) }}</td>
            <td class="num">{{ \App\Support\Money::format($totals['total_billed']) }}</td>
            <td class="num" style="color:var(--amber)">{{ \App\Support\Money::format($totals['total_discount']) }}</td>
            <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($totals['book_profit']) }}</td>
            <td></td>
            <td class="num" style="color:var(--pos)">
              {{ \App\Support\Money::format($totals['trade_profit']) }}
              <div class="muted" style="font-size:11px">{{ number_format($totals['trade_profit_share'], 1) }}%</div>
            </td>
            <td class="num" style="color:var(--pos)">
              {{ \App\Support\Money::format($totals['percentage_profit']) }}
              <div class="muted" style="font-size:11px">{{ number_format($totals['percentage_profit_share'], 1) }}%</div>
            </td>
            <td class="num" style="color:var(--pos)">
              {{ \App\Support\Money::format($totals['installment_profit']) }}
              <div class="muted" style="font-size:11px">{{ number_format($totals['installment_profit_share'], 1) }}%</div>
            </td>
            <td class="num">{{ \App\Support\Money::format($totals['total_collected']) }}</td>
            <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($totals['earned_profit']) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد مشاريع بعد</h4></div>
  @endif
</div>
@endsection
