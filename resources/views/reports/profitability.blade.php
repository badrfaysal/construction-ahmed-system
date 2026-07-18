@extends('layouts.app')
@section('title', 'ربحية المشاريع')
@section('page-title', 'ربحية المشاريع')

@section('content')
<div class="page-head">
  <div><h3>ربحية المشاريع</h3><p>ربح دفتري = سعر البيع + الإشراف − التكلفة | ربح محصل = المحصل فعلاً − التكلفة</p></div>
</div>

{{-- Summary KPIs --}}
<div class="grid cols-3" style="margin-bottom:24px">
  <div class="card stat">
    <div class="top"><span class="label">إجمالي التكلفة الفعلية</span></div>
    <div class="val tnum" style="color:var(--warn)">{{ \App\Support\Money::format($totals['total_spent']) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">ربح دفتري (على الورق)</span></div>
    <div class="val tnum" style="color:{{ $totals['book_profit'] >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totals['book_profit']) }} <small>ج.م</small></div>
    <div class="sub">إجمالي المفوتر: {{ \App\Support\Money::format($totals['total_billed']) }} ج.م</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">ربح محصل (قُبض فعلاً)</span></div>
    <div class="val tnum" style="color:{{ $totals['earned_profit'] >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totals['earned_profit']) }} <small>ج.م</small></div>
    <div class="sub">إجمالي المحصّل: {{ \App\Support\Money::format($totals['total_collected']) }} ج.م</div>
  </div>
</div>

{{-- تفصيل الربح الدفتري لمصدرين: تجاري (فرق شراء/بيع) و نسبة (إشراف) --}}
<div class="grid cols-2" style="margin-bottom:24px">
  <div class="card stat">
    <div class="top"><span class="label">الربح التجاري (فرق الشراء من البيع)</span></div>
    <div class="val tnum" style="color:{{ $totals['trade_profit'] >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totals['trade_profit']) }} <small>ج.م</small></div>
    <div class="sub">{{ number_format($totals['trade_profit_share'], 1) }}% من إجمالي الربح الدفتري</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">ربح النسبة (الإشراف)</span></div>
    <div class="val tnum" style="color:{{ $totals['percentage_profit'] >= 0 ? 'var(--pos)' : 'var(--neg)' }}">{{ \App\Support\Money::format($totals['percentage_profit']) }} <small>ج.م</small></div>
    <div class="sub">{{ number_format($totals['percentage_profit_share'], 1) }}% من إجمالي الربح الدفتري</div>
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
            <th class="num">ربح نسبة</th>
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
