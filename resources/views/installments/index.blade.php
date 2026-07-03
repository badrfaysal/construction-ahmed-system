@extends('layouts.app')
@section('title', 'الأقساط والمدفوعات')
@section('page-title', 'الأقساط والمدفوعات')

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

/* ── Stat Cards ── */
.sy-stat{background:var(--surface);border:1px solid var(--border);border-radius:var(--rlg);
  padding:20px 22px;box-shadow:var(--sh-xs);position:relative;overflow:hidden;transition:.2s;}
.sy-stat::before{content:'';position:absolute;top:0;right:0;bottom:0;width:4px;}
.sy-stat.blue::before{background:var(--accent);}
.sy-stat.green::before{background:var(--success);}
.sy-stat.orange::before{background:var(--warn);}
.sy-stat.red::before{background:var(--danger);}
.sy-stat:hover{transform:translateY(-2px);box-shadow:var(--sh-md);}
.sy-stat .ic{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:12px;}
.sy-stat.blue .ic{background:var(--acbg);color:var(--accent);}
.sy-stat.green .ic{background:var(--sucbg);color:var(--success);}
.sy-stat.orange .ic{background:var(--warnbg);color:var(--warn);}
.sy-stat.red .ic{background:var(--danbg);color:var(--danger);}
.sy-stat h3{font-size:1.6rem;font-weight:700;margin:0;letter-spacing:-.02em;}
.sy-stat p{font-size:.78rem;font-weight:500;color:var(--muted);margin:0 0 4px;}

/* ── Table Box ── */
.tbox{background:var(--surface);border:1px solid var(--border);border-radius:var(--rmd);overflow:hidden;box-shadow:var(--sh-xs);}
.tbox-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
.tbox-head h2{font-size:1.05rem;font-weight:600;margin:0;}

/* ── Filter Bar ── */
.fbar{background:var(--surface);border:1px solid var(--border);border-radius:var(--rmd);padding:12px 16px;margin-bottom:18px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;box-shadow:var(--sh-xs);}
.fsearch{position:relative;flex:1 1 240px;}
.fsearch i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--soft);font-size:.85rem;}
.fsearch input{width:100%;padding:9px 36px 9px 12px;border:1px solid var(--border);border-radius:9px;background:var(--hover);font-size:.86rem;color:var(--text);outline:none;transition:.15s;}
.fsearch input:focus{border-color:var(--accent);background:var(--surface);box-shadow:0 0 0 3px var(--acbg);}
.spills{display:inline-flex;background:var(--hover);border:1px solid var(--border);border-radius:11px;padding:3px;gap:2px;}
.spill{border:none;background:transparent;color:var(--muted);font-weight:600;font-size:.8rem;padding:6px 14px;border-radius:8px;cursor:pointer;white-space:nowrap;transition:.15s;display:inline-flex;align-items:center;gap:5px;}
.spill:hover{color:var(--text);}
.spill.active{background:var(--surface);box-shadow:0 1px 4px rgba(0,0,0,.1);}
.spill.active.sp-all{color:var(--accent);}
.spill.active.sp-paid{color:var(--success);}
.spill.active.sp-due{color:var(--danger);}
.spill.active.sp-up{color:var(--muted);}

/* ── Main Table ── */
.ctable{width:100%;border-collapse:separate;border-spacing:0;}
.ctable th{padding:11px 14px;font-size:.72rem;font-weight:600;color:var(--muted);border-bottom:1px solid var(--border);background:var(--surface2);white-space:nowrap;text-align:center;}
.ctable td{padding:13px 14px;font-size:.88rem;border-bottom:1px solid var(--border);vertical-align:middle;text-align:center;}
.ctable tbody tr:last-child td{border-bottom:none;}
.ctable tbody tr{transition:background .15s;cursor:pointer;}
.ctable tbody tr:hover td{background:var(--hover);}
.av{width:38px;height:38px;border-radius:9px;background:var(--acbg);color:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.95rem;flex-shrink:0;border:1px solid rgba(79,70,229,.2);}

/* Progress */
.prog-bg{height:5px;background:var(--border);border-radius:99px;overflow:hidden;margin-top:4px;}
.prog-fill{height:100%;background:var(--success);border-radius:99px;transition:width .5s;}

/* Badges */
.sbadge{padding:4px 12px;border-radius:99px;font-size:.74rem;font-weight:700;display:inline-block;white-space:nowrap;}
.sb-paid{background:var(--sucbg);color:var(--success);}
.sb-due{background:var(--danbg);color:var(--danger);}
.sb-up{background:var(--warnbg);color:var(--warn);}
.sb-mix{background:var(--acbg);color:var(--accent);}

/* ── Modal style ── */
.modal-content{border-radius:var(--rlg);border:1px solid var(--border);overflow:hidden;box-shadow:var(--sh-lg);}
.modal-header{padding:18px 22px;border-bottom:1px solid var(--border);}
.modal-body{padding:22px;background:var(--surface2);max-height:75vh;overflow-y:auto;}
/* Paper install rows */
.inst-row{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;background:var(--surface);border:1px solid var(--border);margin-bottom:8px;transition:.15s;}
.inst-row:hover{border-color:var(--border2);box-shadow:var(--sh-sm);}
.inst-num{width:32px;height:32px;border-radius:8px;background:var(--acbg);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0;}
.inst-row.paid-row{background:var(--sucbg);border-color:rgba(5,150,105,.25);}
.inst-row.paid-row .inst-num{background:var(--success);color:#fff;}
.inst-row.due-row{background:var(--danbg);border-color:rgba(220,38,38,.25);}
.inst-row.due-row .inst-num{background:var(--danger);color:#fff;}

/* Summary row in modal */
.modal-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:18px;}
.ms-item{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center;}
.ms-label{font-size:.7rem;font-weight:600;color:var(--muted);margin-bottom:4px;}
.ms-val{font-size:1.1rem;font-weight:700;}

@media(max-width:768px){
  .modal-summary{grid-template-columns:repeat(2,1fr);}
  .sy-stat h3{font-size:1.2rem;}
}
</style>
@endpush

@section('content')

{{-- ── Page Head ── --}}
<div class="page-head">
  <div>
    <h3>الأقساط والمدفوعات</h3>
    <p>خطط السداد لكل مشروع — اضغط على أي صف لعرض تفاصيل الأقساط</p>
  </div>
  <div style="display:flex;gap:8px">
    <a href="{{ route('installments.plan.form') }}" class="btn ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><use href="#i-chart"/></svg>
      مولد خطة تقسيط
    </a>
    <a href="{{ route('installments.create') }}" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><use href="#i-plus"/></svg>
      قسط جديد
    </a>
  </div>
</div>

{{-- ── Stat Cards ── --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="sy-stat blue">
      <div class="ic"><i class="fa fa-building"></i></div>
      <p>مشاريع فيها أقساط</p>
      <h3>{{ $totals['projects'] }}</h3>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="sy-stat green">
      <div class="ic"><i class="fa fa-circle-check"></i></div>
      <p>إجمالي المحصول</p>
      <h3>{{ number_format($totals['paid']) }} <small style="font-size:.85rem;font-weight:400;color:var(--soft)">ج.م</small></h3>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="sy-stat orange">
      <div class="ic"><i class="fa fa-clock"></i></div>
      <p>المتبقي المرتقب</p>
      <h3>{{ number_format($totals['remaining']) }} <small style="font-size:.85rem;font-weight:400;color:var(--soft)">ج.م</small></h3>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="sy-stat red">
      <div class="ic"><i class="fa fa-triangle-exclamation"></i></div>
      <p>أقساط متأخرة</p>
      <h3>{{ $totals['overdue'] }} <small style="font-size:.85rem;font-weight:400;color:var(--soft)">قسط</small></h3>
    </div>
  </div>
</div>

{{-- ── Filter Bar ── --}}
<div class="fbar">
  <div class="fsearch">
    <i class="fa fa-search"></i>
    <input type="text" id="searchInput" placeholder="ابحث بالمشروع أو العميل..." oninput="filterRows(this.value)">
  </div>

  <div class="spills">
    <button class="spill sp-all {{ !$statusFilter ? 'active' : '' }}" onclick="location.href='{{ route('installments.index') }}'">
      <i class="fa fa-layer-group"></i> الكل
    </button>
    <button class="spill sp-paid {{ $statusFilter === 'paid' ? 'active' : '' }}" onclick="location.href='{{ route('installments.index', ['status'=>'paid']) }}'">
      <i class="fa fa-check-circle"></i> مدفوع
    </button>
    <button class="spill sp-due {{ $statusFilter === 'due' ? 'active' : '' }}" onclick="location.href='{{ route('installments.index', ['status'=>'due']) }}'">
      <i class="fa fa-circle-xmark"></i> متأخر
    </button>
    <button class="spill sp-up {{ $statusFilter === 'upcoming' ? 'active' : '' }}" onclick="location.href='{{ route('installments.index', ['status'=>'upcoming']) }}'">
      <i class="fa fa-calendar"></i> قادم
    </button>
  </div>

  @if($allProjects->count() > 1)
  <select onchange="location.href='{{ route('installments.index') }}?project_id='+this.value+({{ $statusFilter ? "'&status=$statusFilter'" : "''" }})" style="padding:8px 12px;border:1px solid var(--border);border-radius:9px;font-size:.84rem;color:var(--text);background:var(--surface);outline:none;">
    <option value="">كل المشاريع</option>
    @foreach($allProjects as $p)
      <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
    @endforeach
  </select>
  @endif
</div>

{{-- ── Main Table ── --}}
<div class="tbox">
  <div class="tbox-head">
    <h2>قائمة المشاريع</h2>
    <span style="font-size:.8rem;color:var(--muted)">{{ $projects->count() }} مشروع</span>
  </div>

  @if($projects->count())
  <div class="table-responsive">
    <table class="ctable">
      <thead>
        <tr>
          <th class="text-start">المشروع / العميل</th>
          <th>إجمالي الأقساط</th>
          <th>المقدم المحصول</th>
          <th>المتبقي</th>
          <th>نسبة التحصيل</th>
          <th>متأخرة</th>
          <th>الحالة</th>
        </tr>
      </thead>
      <tbody id="projectsTableBody">
        @foreach($projects as $idx => $project)
          @php
            $colors = ['#4f46e5','#ea580c','#059669','#7c3aed','#0284c7'];
            $color  = $colors[$idx % count($colors)];
            $isPaid = $project->inst_remaining <= 0;
          @endphp
          <tr data-name="{{ strtolower($project->name) }} {{ strtolower($project->client->name) }}"
              onclick="openProjectModal({{ $project->id }})">
            <td class="text-start">
              <div style="display:flex;align-items:center;gap:11px">
                <div class="av" style="background:{{ $color }}22;color:{{ $color }};border-color:{{ $color }}33">
                  {{ mb_substr($project->name, 0, 1, 'UTF-8') }}
                </div>
                <div>
                  <strong style="display:block;color:var(--text)">{{ $project->name }}</strong>
                  <small style="color:var(--muted)">{{ $project->client->name }}</small>
                </div>
              </div>
            </td>
            <td class="fw-bold">{{ number_format($project->inst_total) }} ج.م</td>
            <td style="color:var(--success);font-weight:700">{{ number_format($project->inst_paid) }} ج.م</td>
            <td style="color:{{ $isPaid ? 'var(--success)' : 'var(--danger)' }};font-weight:700">
              {{ number_format($project->inst_remaining) }} ج.م
            </td>
            <td style="min-width:100px">
              <div style="font-size:.8rem;font-weight:700;color:var(--success);margin-bottom:3px">{{ $project->inst_progress }}%</div>
              <div class="prog-bg"><div class="prog-fill" style="width:{{ $project->inst_progress }}%"></div></div>
            </td>
            <td>
              @if($project->inst_due_cnt > 0)
                <span class="sbadge sb-due"><i class="fa fa-exclamation-circle me-1"></i>{{ $project->inst_due_cnt }}</span>
              @else
                <span style="color:var(--soft)">—</span>
              @endif
            </td>
            <td>
              @if($isPaid)
                <span class="sbadge sb-paid">مكتمل</span>
              @elseif($project->inst_due_cnt > 0)
                <span class="sbadge sb-due">متأخر</span>
              @else
                <span class="sbadge sb-mix">جاري</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
    <div class="empty-state" style="padding:60px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="40" height="40"><use href="#i-receipt"/></svg>
      <h4>لا توجد أقساط</h4>
      <p>لم يتم تسجيل أي أقساط بعد</p>
      <a href="{{ route('installments.create') }}" class="btn">إضافة قسط</a>
    </div>
  @endif
</div>

{{-- ══════════════════════════════════════════════
     PROJECT DETAIL MODALS
══════════════════════════════════════════════ --}}
@foreach($projects as $idx => $project)
<div class="modal fade" id="projModal{{ $project->id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      {{-- Header --}}
      <div class="modal-header" style="background:linear-gradient(135deg,#0f172a,#4f46e5)">
        <div>
          <h5 class="modal-title text-white fw-bold" style="margin:0 0 4px">
            <i class="fa fa-building me-2"></i>{{ $project->name }}
          </h5>
          <div style="display:flex;gap:10px;align-items:center">
            <span class="badge bg-danger fs-6 fw-bold">متبقي: {{ number_format($project->inst_remaining) }} ج.م</span>
            <span class="badge bg-success fs-6 fw-bold">محصول: {{ number_format($project->inst_paid) }} ج.م</span>
            @if($project->inst_due_cnt > 0)
              <span class="badge bg-warning text-dark fs-6 fw-bold">{{ $project->inst_due_cnt }} قسط متأخر</span>
            @endif
          </div>
        </div>
        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
          <a href="{{ route('installments.statement', $project->id) }}" class="btn btn-sm fw-bold" style="background:#25d366;color:#fff" onclick="event.stopPropagation()">
            <i class="fa fa-file-contract me-1"></i> كشف حساب
          </a>
          <a href="{{ route('installments.create') }}?project_id={{ $project->id }}" class="btn btn-sm btn-light fw-bold" onclick="event.stopPropagation()">
            <i class="fa fa-plus me-1"></i> قسط جديد
          </a>
          <a href="{{ route('installments.plan.form') }}?project_id={{ $project->id }}" class="btn btn-sm btn-outline-light fw-bold" onclick="event.stopPropagation()">
            <i class="fa fa-calendar-plus me-1"></i> خطة تقسيط
          </a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>

      {{-- Modal Body --}}
      <div class="modal-body">

        {{-- Summary KPIs --}}
        <div class="modal-summary">
          <div class="ms-item">
            <div class="ms-label">إجمالي الأقساط المتعاقد عليها</div>
            <div class="ms-val">{{ number_format($project->inst_total) }} <small class="text-muted">ج.م</small></div>
          </div>
          <div class="ms-item">
            <div class="ms-label">المقدم والمدفوع حتى الآن</div>
            <div class="ms-val" style="color:var(--success)">{{ number_format($project->inst_paid) }} <small>ج.م</small></div>
          </div>
          <div class="ms-item">
            <div class="ms-label">المتبقي المستحق</div>
            <div class="ms-val" style="color:var(--danger)">{{ number_format($project->inst_remaining) }} <small>ج.م</small></div>
          </div>
          <div class="ms-item">
            <div class="ms-label">نسبة التحصيل</div>
            <div class="ms-val" style="color:var(--accent)">{{ $project->inst_progress }}%</div>
          </div>
        </div>

        {{-- Progress bar --}}
        <div style="margin-bottom:20px">
          <div style="display:flex;justify-content:space-between;font-size:.8rem;font-weight:600;color:var(--muted);margin-bottom:6px">
            <span>تقدم التحصيل</span>
            <span>{{ $project->inst_progress }}% من {{ number_format($project->inst_total) }} ج.م</span>
          </div>
          <div style="height:8px;background:var(--border);border-radius:99px;overflow:hidden">
            <div style="height:100%;width:{{ $project->inst_progress }}%;background:var(--success);border-radius:99px;transition:width .6s"></div>
          </div>
        </div>

        {{-- Installment rows --}}
        @php $instNum = 0; @endphp
        @forelse($project->installments as $inst)
          @php
            $instNum++;
            $rowClass = match($inst->status) {
              'paid'     => 'paid-row',
              'due'      => 'due-row',
              default    => '',
            };
          @endphp
          <div class="inst-row {{ $rowClass }}">
            {{-- Number badge --}}
            <div class="inst-num">{{ $instNum }}</div>

            {{-- Info --}}
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:.9rem;color:var(--text)">{{ $inst->label }}</div>
              <div style="font-size:.78rem;color:var(--muted);margin-top:2px">
                @if($inst->band)
                  <span style="background:var(--acbg);color:var(--accent);border-radius:6px;padding:2px 8px;margin-left:6px">{{ $inst->band->name }}</span>
                @endif
                <span><i class="fa fa-calendar-alt me-1"></i>{{ $inst->due_date->format('Y-m-d') }}</span>
                @if($inst->paid_date)
                  <span style="margin-right:8px"><i class="fa fa-check me-1 text-success"></i>دُفع: {{ $inst->paid_date->format('Y-m-d') }}</span>
                @endif
                @if($inst->payment_method)
                  <span style="margin-right:8px"><i class="fa fa-wallet me-1"></i>{{ $inst->payment_method }}</span>
                @endif
              </div>
            </div>

            {{-- Amount --}}
            <div style="text-align:center;min-width:110px">
              <div style="font-size:1.05rem;font-weight:700;color:{{ $inst->status==='paid' ? 'var(--success)' : ($inst->status==='due' ? 'var(--danger)' : 'var(--text)') }}">
                {{ number_format($inst->amount) }} <small>ج.م</small>
              </div>
              <span class="sbadge {{ $inst->status==='paid' ? 'sb-paid' : ($inst->status==='due' ? 'sb-due' : 'sb-up') }}">
                {{ $inst->statusAr() }}
              </span>
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:6px;flex-shrink:0">
              @if($inst->status !== 'paid')
                <form method="POST" action="{{ route('installments.markPaid', $inst) }}" onclick="event.stopPropagation()">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success fw-bold" style="white-space:nowrap">
                    <i class="fa fa-cash-register me-1"></i> تحصيل
                  </button>
                </form>
              @else
                <span class="badge bg-success py-2 px-3 fs-6"><i class="fa fa-check me-1"></i> مسدد</span>
              @endif
              <form method="POST" action="{{ route('installments.destroy', $inst) }}" onclick="event.stopPropagation()" onsubmit="return confirm('حذف هذا القسط؟')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">
                  <i class="fa fa-trash"></i>
                </button>
              </form>
            </div>
          </div>
        @empty
          <div style="text-align:center;padding:30px;color:var(--muted)">
            <i class="fa fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            لا توجد أقساط لهذا المشروع
          </div>
        @endforelse

        {{-- Totals row --}}
        @if($project->installments->count())
        <div style="background:var(--surface);border:1px solid var(--border2);border-radius:10px;padding:14px 18px;margin-top:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
          <span style="font-weight:600;font-size:.88rem">الإجماليات</span>
          <div style="display:flex;gap:24px;flex-wrap:wrap;text-align:center">
            <div>
              <div style="font-size:.72rem;color:var(--muted)">إجمالي</div>
              <div style="font-weight:700">{{ number_format($project->inst_total) }} ج.م</div>
            </div>
            <div>
              <div style="font-size:.72rem;color:var(--success)">محصول</div>
              <div style="font-weight:700;color:var(--success)">{{ number_format($project->inst_paid) }} ج.م</div>
            </div>
            <div>
              <div style="font-size:.72rem;color:var(--danger)">متبقي</div>
              <div style="font-weight:700;color:var(--danger)">{{ number_format($project->inst_remaining) }} ج.م</div>
            </div>
            <div>
              <div style="font-size:.72rem;color:var(--muted)">الأقساط</div>
              <div style="font-weight:700">{{ $project->inst_total_cnt }} قسط</div>
            </div>
          </div>
        </div>
        @endif

      </div>{{-- /modal-body --}}

    </div>
  </div>
</div>
@endforeach

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openProjectModal(id) {
  new bootstrap.Modal(document.getElementById('projModal' + id)).show();
}

function filterRows(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#projectsTableBody tr').forEach(tr => {
    tr.style.display = tr.dataset.name.includes(q) ? '' : 'none';
  });
}
</script>
@endpush
@endsection
