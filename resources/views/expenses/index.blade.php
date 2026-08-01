@extends('layouts.app')
@section('title', 'المصروفات العامة')
@section('page-title', 'المصروفات العامة')

@push('styles')
<style>
/* ═══ تصميم المصروفات ═══ */
.exp * { box-sizing:border-box; }
.exp { --ink:#1e293b; --mut:#64748b; --soft:#94a3b8; --ln:#e2e8f0; --bg2:#f8fafc;
      --ok:#047857; --bad:#b91c1c; --brand:#3b82f6; --brand-light:#eff6ff; }

.exp-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:24px; }
.exp-stat { background:#fff; border:1px solid var(--ln); border-radius:12px; padding:20px; 
  display:flex; flex-direction:column; gap:8px; position:relative; overflow:hidden; }
.exp-stat::before { content:''; position:absolute; top:0; right:0; width:4px; height:100%; background:var(--c, var(--mut)); }
.exp-stat.st-red { --c:var(--bad); }
.exp-stat.st-blue { --c:var(--brand); }
.exp-stat.st-green { --c:var(--ok); }
.exp-stat .top { display:flex; justify-content:space-between; align-items:center; color:var(--mut); font-size:.85rem; font-weight:600; }
.exp-stat .top svg { color:var(--soft); }
.exp-stat .val { font-size:1.8rem; font-weight:800; color:var(--ink); line-height:1; }
.exp-stat .sub { font-size:.85rem; color:var(--mut); }

.exp-layout { display:grid; grid-template-columns:330px 1fr; gap:20px; align-items:start; }
@media(max-width:900px) { .exp-layout { grid-template-columns:1fr; } }

.exp-box { background:#fff; border:1px solid var(--ln); border-radius:12px; overflow:hidden; }
.exp-bhead { background:var(--bg2); padding:16px 20px; border-bottom:1px solid var(--ln); display:flex; align-items:center; gap:8px; }
.exp-bhead h3 { font-size:1.05rem; font-weight:700; color:var(--ink); margin:0; }

.exp-form { padding:20px; display:flex; flex-direction:column; gap:16px; }
.exp-form label { display:block; font-size:.85rem; font-weight:700; color:var(--ink); margin-bottom:6px; }
.exp-form .req { color:var(--bad); }
.exp-form input, .exp-form select, .exp-form textarea { width:100%; padding:10px 14px; border:1px solid var(--ln); 
  border-radius:8px; font-size:.95rem; background:#fff; transition:.2s; }
.exp-form input:focus, .exp-form select:focus, .exp-form textarea:focus { border-color:var(--brand); outline:none; box-shadow:0 0 0 3px var(--brand-light); }
.exp-form .hint { font-size:.75rem; color:var(--mut); margin-top:4px; }
.exp-form .btn { width:100%; padding:12px; font-size:1rem; justify-content:center; }

.exp-filters { padding:14px 20px; background:var(--bg2); border-bottom:1px solid var(--ln); 
  display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end; }
.exp-filters .f-group { flex:1; min-width:180px; display:flex; flex-direction:column; gap:4px; }
.exp-filters label { font-size:.8rem; font-weight:600; color:var(--mut); }
.exp-filters select, .exp-filters input { padding:8px 12px; border:1px solid var(--ln); border-radius:6px; font-size:.9rem; }

.exp-dist { padding:20px; display:flex; gap:24px; border-bottom:1px solid var(--ln); align-items:center; flex-wrap:wrap; }
.exp-dist-item { display:flex; flex-direction:column; gap:6px; }
.exp-dist-top { display:flex; justify-content:space-between; font-size:.9rem; }
.exp-dist-top .name { font-weight:700; color:var(--ink); }
.exp-dist-top .amt { color:var(--mut); font-weight:600; }
.exp-dist-bar { height:6px; background:#eef2f7; border-radius:99px; overflow:hidden; }
.exp-dist-fill { height:100%; background:var(--brand); border-radius:99px; }

table.exp-tbl { width:100%; border-collapse:collapse; }
.exp-tbl th { padding:14px 16px; text-align:right; font-size:.8rem; font-weight:700; color:var(--mut); border-bottom:1px solid var(--ln); }
.exp-tbl td { padding:16px; font-size:.9rem; border-bottom:1px solid var(--ln); color:var(--ink); vertical-align:middle; }
.exp-tbl tr:last-child td { border-bottom:none; }
.exp-tbl tr:hover td { background:var(--bg2); }
.exp-tbl .amt { font-weight:700; color:var(--bad); font-size:1rem; }
.exp-tbl .desc { font-weight:700; font-size:.95rem; }
.exp-tbl .date { color:var(--soft); font-size:.95rem; }
.exp-tbl .act { text-align:left; width:60px; }
</style>
@endpush

@section('content')
<div class="exp">
  
  {{-- Header --}}
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
      <h2 style="margin:0; font-size:1.6rem; color:var(--ink);">سجل المصروفات العامة</h2>
      <p style="margin:6px 0 0; color:var(--mut); font-size:1rem;">المصروفات النثرية والتشغيلية المنفصلة عن حسابات المشاريع</p>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="exp-stats">
    <div class="exp-stat st-red">
      <div class="top">
        <span>إجمالي المصروفات للفترة المحددة</span>
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-cash"/></svg>
      </div>
      <div class="val tnum">{{ \App\Support\Money::format($totalSpent) }}</div>
    </div>
    
    <div class="exp-stat st-blue">
      <div class="top">
        <span>أعلى بند صرف</span>
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trending-up"/></svg>
      </div>
      <div class="val" style="font-size:1.6rem; margin-top:6px;">{{ $mostSpentCat ?? 'لا يوجد' }}</div>
      <div class="sub tnum">{{ $mostSpentAmount ? \App\Support\Money::format($mostSpentAmount) . ' ج.م' : '' }}</div>
    </div>

    <div class="exp-stat st-green">
      <div class="top">
        <span>أقل بند صرف</span>
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-arrow-down"/></svg>
      </div>
      <div class="val" style="font-size:1.6rem; margin-top:6px;">{{ $leastSpentCat ?? 'لا يوجد' }}</div>
      <div class="sub tnum">{{ $leastSpentAmount ? \App\Support\Money::format($leastSpentAmount) . ' ج.م' : '' }}</div>
    </div>
  </div>

  <div class="exp-layout">
    
    {{-- Form Sidebar --}}
    <div class="exp-box" style="position:sticky; top:20px;">
      <div class="exp-bhead">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-plus"/></svg>
        <h3>إضافة مصروف جديد</h3>
      </div>
      <form action="{{ route('general_expenses.store') }}" method="POST" class="exp-form">
        @csrf
        <div>
          <label>المبلغ (ج.م) <span class="req">*</span></label>
          <input type="number" name="amount" step="0.01" min="0.01" required style="font-weight:bold; color:var(--bad); font-size:1.3rem;">
        </div>

        <div>
          <label>البيان / نوع المصروف <span class="req">*</span></label>
          <input type="text" name="description" required list="expense-descriptions" placeholder="مثال: إيجار، بوفيه، نقل...">
          <datalist id="expense-descriptions">
            @foreach($uniqueDescriptions as $desc)
              <option value="{{ $desc }}"></option>
            @endforeach
          </datalist>
          <div class="hint">استخدم نفس المسميات دائماً لضمان دقة التوزيع والإحصائيات.</div>
        </div>

        <div>
          <label>دفع من خزنة / محفظة <span class="req">*</span></label>
          <select name="account_id" required style="direction: rtl; text-align: right;">
            @foreach($wallets as $i => $w)
              @php 
                $optBg = $i % 2 == 0 ? '#f8fafc' : '#ffffff';
                $optColor = '#1e293b';
              @endphp
              <option value="{{ $w->id }}" style="direction: rtl; text-align: right; background-color: {{ $optBg }}; color: {{ $optColor }}; padding: 8px;">{{ $w->name }} &nbsp; — &nbsp; الرصيد: {{ number_format($w->balance, 0) }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label>التاريخ <span class="req">*</span></label>
          <input type="date" name="date" required value="{{ date('Y-m-d') }}">
        </div>

        <div>
          <label>ملاحظات إضافية</label>
          <textarea name="notes" rows="2" placeholder="أي تفاصيل أخرى..."></textarea>
        </div>

        <button type="submit" class="btn primary">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
          حفظ وتسجيل المصروف
        </button>
      </form>
    </div>

    {{-- Main Content --}}
    <div class="exp-box">
      
      {{-- Filters --}}
      <form method="GET" action="{{ route('general_expenses.index') }}" class="exp-filters" id="filterForm">
        <div class="f-group" style="flex:2;">
          <label>عرض حسب الفترة</label>
          <select name="range" onchange="document.getElementById('customRange').style.display = this.value === 'custom' ? 'flex' : 'none'; if(this.value !== 'custom') this.form.submit();">
            <option value="all" {{ $range == 'all' ? 'selected' : '' }}>كل الفترات</option>
            <option value="today" {{ $range == 'today' ? 'selected' : '' }}>اليوم</option>
            <option value="yesterday" {{ $range == 'yesterday' ? 'selected' : '' }}>أمس</option>
            <option value="week" {{ $range == 'week' ? 'selected' : '' }}>آخر 7 أيام</option>
            <option value="month" {{ $range == 'month' ? 'selected' : '' }}>الشهر الحالي</option>
            <option value="custom" {{ $range == 'custom' ? 'selected' : '' }}>فترة مخصصة...</option>
          </select>
        </div>
        
        <div id="customRange" style="display:{{ $range == 'custom' ? 'flex' : 'none' }}; gap:16px; flex:3;">
          <div class="f-group">
            <label>من</label>
            <input type="date" name="from" value="{{ request('from') }}">
          </div>
          <div class="f-group">
            <label>إلى</label>
            <input type="date" name="to" value="{{ request('to') }}">
          </div>
          <button type="submit" class="btn secondary" style="padding:10px 20px;">بحث</button>
        </div>
      </form>

      {{-- Distribution Chart & Bars --}}
      @if($distribution->count() > 0)
        <div class="exp-dist">
          <div style="flex:1; min-width:300px;">
            <div style="font-size:1.15rem; font-weight:700; color:var(--ink); margin-bottom:20px;">
              توزيع المصروفات
            </div>
            <div style="display:flex; flex-direction:column; gap:18px;">
              @foreach($distribution->take(6) as $cat => $amount)
                @php 
                  $percent = $totalSpent > 0 ? ($amount / $totalSpent) * 100 : 0; 
                  $colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#64748b'];
                  $color = $colors[$loop->index % count($colors)];
                @endphp
                <div class="exp-dist-item">
                  <div class="exp-dist-top">
                    <span class="name">{{ $cat }}</span>
                    <span class="amt tnum">{{ number_format($percent, 1) }}% - {{ \App\Support\Money::format($amount) }}</span>
                  </div>
                  <div class="exp-dist-bar">
                    <div class="exp-dist-fill" style="width:{{ $percent }}%; background:{{ $color }};"></div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
          <div style="width:240px; height:240px; position:relative; flex-shrink:0; margin:0 auto;">
            <canvas id="expensesChart"></canvas>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none">
              <div style="font-size:13px;color:var(--mut);font-weight:600;">الإجمالي</div>
              <div class="tnum" style="font-size:1.4rem;font-weight:800;color:var(--ink);">{{ \App\Support\Money::format($totalSpent) }}</div>
            </div>
          </div>
        </div>
      @endif

      {{-- Table --}}
      <div style="overflow-x:auto;">
        <table class="exp-tbl">
          <thead>
            <tr>
              <th>التاريخ</th>
              <th>البيان</th>
              <th>المبلغ</th>
              <th>الخزنة</th>
              <th>ملاحظات</th>
              <th class="act"></th>
            </tr>
          </thead>
          <tbody>
            @forelse($expenses as $ex)
              <tr>
                <td class="date tnum">{{ $ex->date->format('Y-m-d') }}</td>
                <td class="desc">{{ $ex->description }}</td>
                <td class="amt tnum">{{ \App\Support\Money::format($ex->amount) }}</td>
                <td style="color:var(--mut); font-size:.9rem;">
                  <div style="display:flex; align-items:center; gap:6px;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-wallet"/></svg>
                    {{ $ex->account->name ?? '—' }}
                  </div>
                </td>
                <td style="color:var(--mut); font-size:.9rem; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $ex->notes }}">
                  {{ $ex->notes ?? '—' }}
                </td>
                <td class="act">
                  <form action="{{ route('general_expenses.destroy', $ex) }}" method="POST" onsubmit="return confirm('تأكيد الحذف واسترجاع المبلغ؟');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn ghost sm" style="color:var(--bad); padding:8px;" title="حذف واسترجاع القيمة">
                      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" style="text-align:center; padding:40px 20px; color:var(--soft);">
                  <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" style="margin-bottom:12px; opacity:0.5;"><use href="#i-receipt"/></svg>
                  <div style="font-size:1.1rem;">لا يوجد مصروفات مسجلة في هذه الفترة</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      @if($expenses->hasPages())
        <div style="padding:16px; border-top:1px solid var(--ln);">
          {{ $expenses->appends(request()->query())->links() }}
        </div>
      @endif
      
    </div>
  </div>
</div>

<svg width="0" height="0" style="position:absolute"><defs><g id="i-arrow-down"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></g></defs></svg>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const ctx = document.getElementById('expensesChart');
  if(ctx) {
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($distribution->keys()->take(6)) !!},
        datasets: [{
          data: {!! json_encode($distribution->values()->take(6)) !!},
          backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#64748b'],
          borderWidth: 0,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.parsed !== null) {
                  label += new Intl.NumberFormat('en-EG').format(context.parsed) + ' ج.م';
                }
                return label;
              }
            }
          }
        }
      }
    });
  }
});
</script>
@endpush
