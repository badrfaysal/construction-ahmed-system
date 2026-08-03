@extends('layouts.app')
@section('title', 'المصروفات العامة')
@section('page-title', 'المصروفات العامة')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ═══ تصميم المصروفات ═══ */
.exp * { box-sizing:border-box; }
.exp { --ink:#1e293b; --mut:#64748b; --soft:#94a3b8; --ln:#e2e8f0; --bg2:#f8fafc;
      --ok:#059669; --bad:#dc2626; --brand:#4f46e5; --brand-light:#e0e7ff; }

.exp-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:24px; }
.exp-stat { background:linear-gradient(145deg, #ffffff, #f8fafc); border:1px solid var(--ln); border-radius:16px; padding:24px; 
  display:flex; flex-direction:column; gap:10px; position:relative; overflow:hidden; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
.exp-stat:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.12); }
.exp-stat::before { display: none; }
.exp-stat.st-red { background: linear-gradient(135deg, #ef4444, #b91c1c); border: none; color: #fff; }
.exp-stat.st-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none; color: #fff; }
.exp-stat.st-green { background: linear-gradient(135deg, #10b981, #047857); border: none; color: #fff; }
.exp-stat .top { display:flex; justify-content:space-between; align-items:center; font-size:1rem; font-weight:700; color: #fff; }
.exp-stat .top svg, .exp-stat .top i { background: rgba(255,255,255,0.2); color: #fff; padding: 8px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.exp-stat .val { font-size:2.2rem; font-weight:900; color:#fff; line-height:1; margin-top: 8px; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
.exp-stat .sub { font-size:1rem; color:rgba(255,255,255,0.9); font-weight: 600; }

.exp-layout { display:grid; grid-template-columns:360px 1fr; gap:24px; align-items:start; }
@media(max-width:900px) { .exp-layout { grid-template-columns:1fr; } }

.exp-box { background:#fff; border:1px solid var(--ln); border-radius:16px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: all 0.3s ease; }
.exp-box:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.05); }
.exp-bhead { background:linear-gradient(to left, var(--brand-light), #fff); padding:18px 24px; border-bottom:1px solid var(--ln); display:flex; align-items:center; gap:12px; }
.exp-bhead svg, .exp-bhead i { color: var(--brand); background: #fff; border-radius: 10px; padding: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.exp-bhead h3 { font-size:1.15rem; font-weight:800; color:var(--brand); margin:0; }

.exp-form { padding:24px; display:flex; flex-direction:column; gap:20px; background: #fafafa; }
.exp-form label { display:flex; align-items: center; gap: 8px; font-size:.9rem; font-weight:700; color:var(--ink); margin-bottom:8px; }
.exp-form label i { color: var(--brand); font-size: 1rem; }
.exp-form .req { color:var(--bad); }
.exp-form input, .exp-form select, .exp-form textarea { width:100%; padding:12px 16px; border:1px solid #cbd5e1; 
  border-radius:10px; font-size:.95rem; background:#fff; transition:all .3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
.exp-form input:focus, .exp-form select:focus, .exp-form textarea:focus { border-color:var(--brand); outline:none; box-shadow:0 0 0 4px rgba(79, 70, 229, 0.15), inset 0 2px 4px rgba(0,0,0,0.01); }
.exp-form .hint { font-size:.8rem; color:var(--mut); margin-top:6px; display: flex; align-items: center; gap: 6px; }
.exp-form .btn { width:100%; padding:14px; font-size:1.05rem; justify-content:center; border-radius: 10px; font-weight: 700; background: linear-gradient(145deg, var(--brand), #4338ca); color: #fff; border: none; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; cursor: pointer; }
.exp-form .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(79, 70, 229, 0.4); background: linear-gradient(145deg, #4338ca, #3730a3); }

.exp-filters { padding:20px 24px; background:linear-gradient(to bottom, #fff, var(--bg2)); border-bottom:1px solid var(--ln); 
  display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end; }
.exp-filters .f-group { flex:1; min-width:180px; display:flex; flex-direction:column; gap:8px; }
.exp-filters label { font-size:.85rem; font-weight:700; color:var(--ink); display: flex; align-items: center; gap: 6px; }
.exp-filters label i { color: var(--mut); }
.exp-filters select, .exp-filters input { padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:.95rem; transition: all 0.2s; box-shadow: inset 0 1px 3px rgba(0,0,0,0.02); }
.exp-filters select:focus, .exp-filters input:focus { border-color: var(--brand); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.exp-filters .btn.secondary { background: #fff; border: 1px solid #cbd5e1; color: var(--ink); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.exp-filters .btn.secondary:hover { background: var(--bg2); border-color: var(--mut); }

.exp-dist { padding:24px; display:flex; gap:30px; border-bottom:1px solid var(--ln); align-items:center; flex-wrap:wrap; }
.exp-dist-item { display:flex; flex-direction:column; gap:8px; }
.exp-dist-top { display:flex; justify-content:space-between; font-size:.95rem; }
.exp-dist-top .name { font-weight:700; color:var(--ink); }
.exp-dist-top .amt { color:var(--mut); font-weight:600; }
.exp-dist-bar { height:8px; background:#eef2f7; border-radius:99px; overflow:hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
.exp-dist-fill { height:100%; background:var(--brand); border-radius:99px; position: relative; }
.exp-dist-fill::after { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%); animation: shimmer 2s infinite; }
@keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

table.exp-tbl { width:100%; border-collapse:collapse; }
.exp-tbl th { padding:16px 20px; text-align:right; font-size:.85rem; font-weight:700; color:var(--mut); border-bottom:1px solid var(--ln); background: var(--bg2); }
.exp-tbl td { padding:18px 20px; font-size:.95rem; border-bottom:1px solid var(--ln); color:var(--ink); vertical-align:middle; transition: background 0.2s; }
.exp-tbl tr:last-child td { border-bottom:none; }
.exp-tbl tr:hover td { background:var(--brand-light); }
.exp-tbl .amt { font-weight:800; color:var(--bad); font-size:1.05rem; }
.exp-tbl .desc { font-weight:700; font-size:.95rem; color: var(--brand); }
.exp-tbl .date { color:var(--mut); font-size:.95rem; font-weight: 600; }
.exp-tbl .act { text-align:left; width:70px; }
.exp-tbl .btn.ghost { background: transparent; border: none; cursor: pointer; transition: all 0.2s; border-radius: 8px; }
.exp-tbl .btn.ghost:hover { background: #fee2e2; transform: scale(1.05); }
</style>
@endpush

@section('content')
<div class="exp">
  
  {{-- Header --}}
  <div class="page-head">
    <div>
      <h2>سجل المصروفات العامة</h2>
      <p>المصروفات النثرية والتشغيلية المنفصلة عن حسابات المشاريع</p>
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
          <label><i class="fa-solid fa-money-bill-wave"></i> المبلغ (ج.م) <span class="req">*</span></label>
          <input type="number" name="amount" step="0.01" min="0.01" required style="font-weight:900; color:var(--bad); font-size:1.4rem;">
        </div>

        <div>
          <label><i class="fa-solid fa-tags"></i> البيان / نوع المصروف <span class="req">*</span></label>
          <input type="text" name="description" required list="expense-descriptions" placeholder="مثال: إيجار، بوفيه، نقل...">
          <datalist id="expense-descriptions">
            @foreach($uniqueDescriptions as $desc)
              <option value="{{ $desc }}"></option>
            @endforeach
          </datalist>
          <div class="hint"><i class="fa-solid fa-circle-info"></i> استخدم نفس المسميات دائماً لضمان دقة التوزيع والإحصائيات.</div>
        </div>

        <div>
          <label><i class="fa-solid fa-wallet"></i> دفع من خزنة / محفظة <span class="req">*</span></label>
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
          <label><i class="fa-regular fa-calendar"></i> التاريخ <span class="req">*</span></label>
          <input type="date" name="date" required value="{{ date('Y-m-d') }}">
        </div>

        <div>
          <label><i class="fa-solid fa-note-sticky"></i> ملاحظات إضافية</label>
          <textarea name="notes" rows="2" placeholder="أي تفاصيل أخرى..."></textarea>
        </div>

        <button type="submit" class="btn primary">
          <i class="fa-solid fa-circle-check"></i>
          حفظ وتسجيل المصروف
        </button>
      </form>
    </div>

    {{-- Main Content --}}
    <div class="exp-box">
      
      {{-- Filters --}}
      <form method="GET" action="{{ route('general_expenses.index') }}" class="exp-filters" id="filterForm">
        <div class="f-group" style="flex:2;">
          <label><i class="fa-regular fa-calendar-days"></i> عرض حسب الفترة</label>
          <select name="range" onchange="document.getElementById('customRange').style.display = this.value === 'custom' ? 'flex' : 'none'; if(this.value !== 'custom') this.form.submit();">
            <option value="all" {{ $range == 'all' ? 'selected' : '' }}>كل الفترات</option>
            <option value="today" {{ $range == 'today' ? 'selected' : '' }}>اليوم</option>
            <option value="yesterday" {{ $range == 'yesterday' ? 'selected' : '' }}>أمس</option>
            <option value="week" {{ $range == 'week' ? 'selected' : '' }}>آخر 7 أيام</option>
            <option value="month" {{ $range == 'month' ? 'selected' : '' }}>الشهر الحالي</option>
            <option value="custom" {{ $range == 'custom' ? 'selected' : '' }}>فترة مخصصة...</option>
          </select>
        </div>
        
        <div class="f-group" style="flex:2;">
          <label><i class="fa-solid fa-list"></i> البند / البيان</label>
          <select name="description_filter" onchange="this.form.submit();">
            <option value="">جميع البنود</option>
            @foreach($uniqueDescriptions as $desc)
              <option value="{{ $desc }}" {{ request('description_filter') == $desc ? 'selected' : '' }}>{{ $desc }}</option>
            @endforeach
          </select>
        </div>
        
        <div id="customRange" style="display:{{ $range == 'custom' ? 'flex' : 'none' }}; gap:16px; flex:3;">
          <div class="f-group">
            <label><i class="fa-solid fa-hourglass-start"></i> من</label>
            <input type="date" name="from" value="{{ request('from') }}">
          </div>
          <div class="f-group">
            <label><i class="fa-solid fa-hourglass-end"></i> إلى</label>
            <input type="date" name="to" value="{{ request('to') }}">
          </div>
          <button type="submit" class="btn secondary" style="padding:10px 20px;"><i class="fa-solid fa-magnifying-glass"></i> بحث</button>
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
