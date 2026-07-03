@extends('layouts.app')
@section('title', 'المستحقات — ما للعملاء علينا')
@section('page-title', 'المستحقات')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{
  --bg:#f4f7fb;--surface:#fff;--surface2:#fafbfd;
  --text:#0f172a;--muted:#64748b;--soft:#94a3b8;
  --border:#e6ebf3;--border2:#d4dbe6;--hover:#f1f4f9;
  --accent:#4f46e5;--acbg:#eef2ff;
  --success:#059669;--sucbg:#ecfdf5;
  --danger:#dc2626;--danbg:#fef2f2;
  --warn:#d97706;--warnbg:#fffbeb;
  --rsm:8px;--rmd:12px;--rlg:16px;
  --sh-xs:0 1px 2px rgba(15,23,42,.05);
  --sh-sm:0 2px 6px rgba(15,23,42,.07);
  --sh-md:0 4px 14px rgba(15,23,42,.08);
  --sh-lg:0 12px 32px rgba(15,23,42,.1);
}
body{font-family:'IBM Plex Sans Arabic','Cairo',sans-serif;background:var(--bg);color:var(--text);}

.sy-stat{background:var(--surface);border:1px solid var(--border);border-radius:var(--rlg);
  padding:20px 22px;box-shadow:var(--sh-xs);position:relative;overflow:hidden;transition:.2s;}
.sy-stat::before{content:'';position:absolute;top:0;right:0;bottom:0;width:4px;}
.sy-stat.blue::before{background:var(--accent);}
.sy-stat.green::before{background:var(--success);}
.sy-stat.orange::before{background:var(--warn);}
.sy-stat.red::before{background:var(--danger);}
.sy-stat.purple::before{background:#7c3aed;}
.sy-stat:hover{transform:translateY(-2px);box-shadow:var(--sh-md);}
.sy-stat .ic{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:12px;}
.sy-stat.blue .ic{background:var(--acbg);color:var(--accent);}
.sy-stat.green .ic{background:var(--sucbg);color:var(--success);}
.sy-stat.orange .ic{background:var(--warnbg);color:var(--warn);}
.sy-stat.red .ic{background:var(--danbg);color:var(--danger);}
.sy-stat.purple .ic{background:#f5f3ff;color:#7c3aed;}
.sy-stat h3{font-size:1.5rem;font-weight:700;margin:0;letter-spacing:-.02em;}
.sy-stat p{font-size:.78rem;font-weight:500;color:var(--muted);margin:0 0 4px;}

.tbox{background:var(--surface);border:1px solid var(--border);border-radius:var(--rmd);overflow:hidden;box-shadow:var(--sh-xs);margin-bottom:24px;}
.tbox-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
.tbox-head h2{font-size:1.05rem;font-weight:600;margin:0;}
.ctable{width:100%;border-collapse:separate;border-spacing:0;}
.ctable th{padding:11px 14px;font-size:.72rem;font-weight:600;color:var(--muted);border-bottom:1px solid var(--border);background:var(--surface2);white-space:nowrap;text-align:center;}
.ctable td{padding:13px 14px;font-size:.88rem;border-bottom:1px solid var(--border);vertical-align:middle;text-align:center;}
.ctable tbody tr:last-child td{border-bottom:none;}
.ctable tbody tr{cursor:pointer;transition:background .15s;}
.ctable tbody tr:hover td{background:var(--hover);}

.av{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.95rem;flex-shrink:0;}

.prog-bg{height:5px;background:var(--border);border-radius:99px;overflow:hidden;margin-top:4px;}
.prog-fill{height:100%;border-radius:99px;}

.sbadge{padding:4px 12px;border-radius:99px;font-size:.74rem;font-weight:700;display:inline-block;white-space:nowrap;}
.sb-positive{background:var(--sucbg);color:var(--success);}
.sb-negative{background:var(--danbg);color:var(--danger);}
.sb-neutral{background:var(--acbg);color:var(--accent);}

.modal-content{border-radius:var(--rlg);border:1px solid var(--border);overflow:hidden;box-shadow:var(--sh-lg);}
.modal-header{padding:18px 22px;border-bottom:1px solid var(--border);}
.modal-body{padding:22px;background:var(--surface2);max-height:78vh;overflow-y:auto;}

.paper-table{width:100%;border-collapse:collapse;font-size:.87rem;}
.paper-table th{background:#f8fafc;border:1px solid var(--border);padding:9px 13px;font-weight:600;color:var(--muted);font-size:.74rem;text-align:center;}
.paper-table td{border:1px solid var(--border);padding:9px 13px;text-align:center;}
.paper-table tr:nth-child(even) td{background:#fafbfd;}

.kpi-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:20px;}
.kpi-item{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center;}
.kpi-label{font-size:.7rem;font-weight:600;color:var(--muted);margin-bottom:4px;}
.kpi-val{font-size:1.1rem;font-weight:700;}

.inst-chip{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;background:var(--surface);border:1px solid var(--border);margin-bottom:7px;}
.inst-chip.overdue{background:var(--danbg);border-color:rgba(220,38,38,.2);}
.inst-chip.upcoming{background:var(--warnbg);border-color:rgba(217,119,6,.2);}

@media(max-width:768px){
  .kpi-strip{grid-template-columns:repeat(2,1fr);}
  .sy-stat h3{font-size:1.2rem;}
}
</style>
@endpush

@section('content')

<div class="page-head">
  <div>
    <h3>المستحقات</h3>
    <p>ما يستحقه العملاء تجاه مشاريعهم — المفوتر والمحصول والمتبقي</p>
  </div>
  <a href="{{ route('installments.index') }}" class="btn ghost">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-receipt"/></svg>
    الأقساط
  </a>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md" style="min-width:150px">
    <div class="sy-stat blue">
      <div class="ic"><i class="fa fa-file-invoice-dollar"></i></div>
      <p>إجمالي المفوتر</p>
      <h3>{{ number_format($totals['total_billed']) }} <small style="font-size:.8rem;color:var(--soft);font-weight:400">ج.م</small></h3>
    </div>
  </div>
  <div class="col-6 col-md" style="min-width:150px">
    <div class="sy-stat green">
      <div class="ic"><i class="fa fa-money-bill-wave"></i></div>
      <p>المحصول من العملاء</p>
      <h3>{{ number_format($totals['total_collected']) }} <small style="font-size:.8rem;color:var(--soft);font-weight:400">ج.م</small></h3>
    </div>
  </div>
  <div class="col-6 col-md" style="min-width:150px">
    <div class="sy-stat red">
      <div class="ic"><i class="fa fa-clock-rotate-left"></i></div>
      <p>المتبقي على العملاء</p>
      <h3>{{ number_format($totals['total_remaining']) }} <small style="font-size:.8rem;color:var(--soft);font-weight:400">ج.م</small></h3>
    </div>
  </div>
  <div class="col-6 col-md" style="min-width:150px">
    <div class="sy-stat orange">
      <div class="ic"><i class="fa fa-chart-line"></i></div>
      <p>الربح الدفتري</p>
      <h3>{{ number_format($totals['book_profit']) }} <small style="font-size:.8rem;color:var(--soft);font-weight:400">ج.م</small></h3>
    </div>
  </div>
  <div class="col-6 col-md" style="min-width:150px">
    <div class="sy-stat purple">
      <div class="ic"><i class="fa fa-sack-dollar"></i></div>
      <p>الربح المحصل فعلياً</p>
      <h3>{{ number_format($totals['earned_profit']) }} <small style="font-size:.8rem;color:var(--soft);font-weight:400">ج.م</small></h3>
    </div>
  </div>
</div>

{{-- Main Table --}}
<div class="tbox">
  <div class="tbox-head">
    <h2><i class="fa fa-table-list me-2 text-primary"></i>مستحقات المشاريع</h2>
    <span style="font-size:.8rem;color:var(--muted)">{{ $rows->count() }} مشروع — اضغط على أي صف لعرض التفاصيل</span>
  </div>

  @if($rows->count())
  <div class="table-responsive">
    <table class="ctable">
      <thead>
        <tr>
          <th class="text-start">المشروع / العميل</th>
          <th>المفوتر</th>
          <th>المحصول</th>
          <th>المتبقي</th>
          <th>نسبة التحصيل</th>
          <th>ربح دفتري</th>
          <th>ربح محصل</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rows as $idx => $row)
          @php
            $colors = ['#4f46e5','#ea580c','#059669','#7c3aed','#0284c7','#d97706'];
            $color  = $colors[$idx % count($colors)];
            $collectPct = $row->billed > 0 ? round($row->collected / $row->billed * 100) : 0;
          @endphp
          <tr onclick="openModal({{ $row->project->id }})">
            <td class="text-start">
              <div style="display:flex;align-items:center;gap:11px">
                <div class="av" style="background:{{ $color }}22;color:{{ $color }};border:1px solid {{ $color }}33">
                  {{ mb_substr($row->project->name, 0, 1, 'UTF-8') }}
                </div>
                <div>
                  <strong style="display:block">{{ $row->project->name }}</strong>
                  <small style="color:var(--muted)">{{ $row->project->client->name }}</small>
                </div>
              </div>
            </td>
            <td class="fw-bold">{{ number_format($row->billed) }} ج.م</td>
            <td style="color:var(--success);font-weight:700">{{ number_format($row->collected) }} ج.م</td>
            <td style="color:{{ $row->remaining > 0 ? 'var(--danger)' : 'var(--success)' }};font-weight:700">
              {{ number_format($row->remaining) }} ج.م
            </td>
            <td style="min-width:100px">
              <div style="font-size:.8rem;font-weight:700;color:{{ $collectPct >= 100 ? 'var(--success)' : 'var(--accent)' }};margin-bottom:3px">
                {{ $collectPct }}%
              </div>
              <div class="prog-bg">
                <div class="prog-fill" style="width:{{ min($collectPct, 100) }}%;background:{{ $collectPct >= 100 ? 'var(--success)' : 'var(--accent)' }}"></div>
              </div>
            </td>
            <td>
              <span class="sbadge {{ $row->book_profit >= 0 ? 'sb-positive' : 'sb-negative' }}">
                {{ number_format($row->book_profit) }} ج.م
              </span>
            </td>
            <td>
              <span class="sbadge {{ $row->earned_profit >= 0 ? 'sb-positive' : 'sb-negative' }}">
                {{ number_format($row->earned_profit) }} ج.م
              </span>
            </td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background:var(--surface2);font-weight:700">
          <td class="text-start" style="padding:12px 14px">الإجمالي</td>
          <td>{{ number_format($totals['total_billed']) }} ج.م</td>
          <td style="color:var(--success)">{{ number_format($totals['total_collected']) }} ج.م</td>
          <td style="color:var(--danger)">{{ number_format($totals['total_remaining']) }} ج.م</td>
          <td>
            @php $overallPct = $totals['total_billed'] > 0 ? round($totals['total_collected'] / $totals['total_billed'] * 100) : 0 @endphp
            {{ $overallPct }}%
          </td>
          <td style="color:{{ $totals['book_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
            {{ number_format($totals['book_profit']) }} ج.م
          </td>
          <td style="color:{{ $totals['earned_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
            {{ number_format($totals['earned_profit']) }} ج.م
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
  @else
    <div class="empty-state" style="padding:60px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="40" height="40"><use href="#i-chart"/></svg>
      <h4>لا توجد مشاريع مُفوترة</h4>
      <p>أضف بنوداً ومواد للمشاريع لتظهر هنا المستحقات</p>
    </div>
  @endif
</div>

{{-- Overdue Installments --}}
@if($overdueInstallments->count())
<div class="tbox">
  <div class="tbox-head" style="background:var(--danbg)">
    <h2 style="color:var(--danger)"><i class="fa fa-triangle-exclamation me-2"></i>أقساط متأخرة ({{ $overdueInstallments->count() }})</h2>
    <span style="font-size:.8rem;color:var(--danger);font-weight:600">
      إجمالي: {{ number_format($overdueInstallments->sum('amount')) }} ج.م
    </span>
  </div>
  <div style="padding:16px">
    @foreach($overdueInstallments as $inst)
      <div class="inst-chip overdue">
        <div style="width:36px;height:36px;border-radius:8px;background:var(--danger);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fa fa-exclamation"></i>
        </div>
        <div style="flex:1">
          <div style="font-weight:600;font-size:.9rem">{{ $inst->label }}</div>
          <div style="font-size:.78rem;color:var(--muted)">
            {{ $inst->project->name }} — {{ $inst->project->client->name }}
            @if($inst->band)<span style="margin-right:6px">| {{ $inst->band->name }}</span>@endif
          </div>
        </div>
        <div style="text-align:center;min-width:110px">
          <div style="font-weight:700;color:var(--danger)">{{ number_format($inst->amount) }} ج.م</div>
          <div style="font-size:.72rem;color:var(--muted)">استحق: {{ $inst->due_date->format('Y-m-d') }}</div>
        </div>
        <form method="POST" action="{{ route('installments.markPaid', $inst) }}" onclick="event.stopPropagation()">
          @csrf
          <button type="submit" class="btn btn-sm btn-danger fw-bold"><i class="fa fa-cash-register me-1"></i>تحصيل</button>
        </form>
      </div>
    @endforeach
  </div>
</div>
@endif

{{-- Upcoming installments (60 days) --}}
@if($upcomingInstallments->count())
<div class="tbox">
  <div class="tbox-head" style="background:var(--warnbg)">
    <h2 style="color:var(--warn)"><i class="fa fa-calendar-days me-2"></i>أقساط قادمة — خلال 60 يوماً ({{ $upcomingInstallments->count() }})</h2>
    <span style="font-size:.8rem;color:var(--warn);font-weight:600">
      إجمالي: {{ number_format($upcomingInstallments->sum('amount')) }} ج.م
    </span>
  </div>
  <div style="padding:16px">
    @foreach($upcomingInstallments as $inst)
      @php $daysLeft = (int) now()->diffInDays($inst->due_date, false); @endphp
      <div class="inst-chip upcoming">
        <div style="width:36px;height:36px;border-radius:8px;background:var(--warn);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.78rem;font-weight:700;line-height:1.1;text-align:center">
          {{ $daysLeft }}<br><span style="font-size:.6rem">يوم</span>
        </div>
        <div style="flex:1">
          <div style="font-weight:600;font-size:.9rem">{{ $inst->label }}</div>
          <div style="font-size:.78rem;color:var(--muted)">
            {{ $inst->project->name }} — {{ $inst->project->client->name }}
            @if($inst->band)<span style="margin-right:6px">| {{ $inst->band->name }}</span>@endif
          </div>
        </div>
        <div style="text-align:center;min-width:110px">
          <div style="font-weight:700;color:var(--warn)">{{ number_format($inst->amount) }} ج.م</div>
          <div style="font-size:.72rem;color:var(--muted)">يستحق: {{ $inst->due_date->format('Y-m-d') }}</div>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endif

{{-- Per-project modals --}}
@foreach($rows as $row)
  @php
    $proj = $row->project;
    $collectPct = $row->billed > 0 ? round($row->collected / $row->billed * 100) : 0;
    $spent = $row->billed - $row->book_profit;
  @endphp
  <div class="modal fade" id="modal{{ $proj->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header" style="background:linear-gradient(135deg,#0f172a,#4f46e5)">
          <div>
            <h5 class="modal-title text-white fw-bold" style="margin:0 0 4px">
              <i class="fa fa-building me-2"></i>{{ $proj->name }}
              <small class="text-white-50 fs-6 fw-normal">— {{ $proj->client->name }}</small>
            </h5>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <span class="badge bg-success fs-6">محصول: {{ number_format($row->collected) }} ج.م</span>
              @if($row->remaining > 0)
                <span class="badge bg-danger fs-6">متبقي: {{ number_format($row->remaining) }} ج.م</span>
              @else
                <span class="badge bg-success fs-6">مسدد بالكامل ✓</span>
              @endif
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="kpi-strip">
            <div class="kpi-item">
              <div class="kpi-label">المفوتر</div>
              <div class="kpi-val">{{ number_format($row->billed) }} <small class="text-muted">ج.م</small></div>
            </div>
            <div class="kpi-item">
              <div class="kpi-label">التكلفة الفعلية</div>
              <div class="kpi-val">{{ number_format($spent) }} <small class="text-muted">ج.م</small></div>
            </div>
            <div class="kpi-item">
              <div class="kpi-label">ربح دفتري</div>
              <div class="kpi-val" style="color:{{ $row->book_profit >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                {{ number_format($row->book_profit) }} <small>ج.م</small>
              </div>
            </div>
            <div class="kpi-item">
              <div class="kpi-label">ربح محصل</div>
              <div class="kpi-val" style="color:{{ $row->earned_profit >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                {{ number_format($row->earned_profit) }} <small>ج.م</small>
              </div>
            </div>
          </div>

          <div style="margin-bottom:20px">
            <div style="display:flex;justify-content:space-between;font-size:.8rem;font-weight:600;color:var(--muted);margin-bottom:6px">
              <span>نسبة التحصيل</span>
              <span>{{ $collectPct }}% من {{ number_format($row->billed) }} ج.م مفوتر</span>
            </div>
            <div style="height:8px;background:var(--border);border-radius:99px;overflow:hidden">
              <div style="height:100%;width:{{ min($collectPct, 100) }}%;background:{{ $collectPct >= 100 ? 'var(--success)' : 'var(--accent)' }};border-radius:99px"></div>
            </div>
          </div>

          @if($proj->installments->count())
            <h6 style="font-weight:700;margin-bottom:12px;color:var(--muted)">جدول الأقساط</h6>
            <div class="table-responsive">
              <table class="paper-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th class="text-start">البيان</th>
                    <th>البند</th>
                    <th>الاستحقاق</th>
                    <th>المبلغ</th>
                    <th>تاريخ السداد</th>
                    <th>الحالة</th>
                    <th>إجراء</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($proj->installments->sortBy('sort_order') as $n => $inst)
                    <tr>
                      <td>{{ $n + 1 }}</td>
                      <td class="text-start">{{ $inst->label }}</td>
                      <td>{{ $inst->band?->name ?? '—' }}</td>
                      <td>{{ $inst->due_date->format('Y-m-d') }}</td>
                      <td class="fw-bold">{{ number_format($inst->amount) }} ج.م</td>
                      <td>{{ $inst->paid_date ? $inst->paid_date->format('Y-m-d') : '—' }}</td>
                      <td>
                        <span class="sbadge {{ $inst->status === 'paid' ? 'sb-positive' : ($inst->status === 'due' ? 'sb-negative' : 'sb-neutral') }}">
                          {{ $inst->statusAr() }}
                        </span>
                      </td>
                      <td>
                        @if($inst->status !== 'paid')
                          <form method="POST" action="{{ route('installments.markPaid', $inst) }}" onclick="event.stopPropagation()">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success fw-bold px-3">تحصيل</button>
                          </form>
                        @else
                          <span class="text-success"><i class="fa fa-check"></i></span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr style="font-weight:700;background:#f8fafc">
                    <td colspan="4" class="text-start" style="padding:10px 14px">الإجمالي</td>
                    <td>{{ number_format($proj->installments->sum('amount')) }} ج.م</td>
                    <td colspan="3">
                      محصول: <span style="color:var(--success)">{{ number_format($proj->installments->where('status','paid')->sum('amount')) }} ج.م</span>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          @else
            <div style="text-align:center;padding:20px;color:var(--muted);font-size:.88rem">
              <i class="fa fa-inbox opacity-50 d-block mb-2 fa-2x"></i>
              لا توجد أقساط مسجلة لهذا المشروع
            </div>
          @endif
        </div>

      </div>
    </div>
  </div>
@endforeach

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(id) {
  new bootstrap.Modal(document.getElementById('modal' + id)).show();
}
</script>
@endpush
@endsection
