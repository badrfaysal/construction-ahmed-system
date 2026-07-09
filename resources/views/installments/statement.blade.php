@extends('layouts.app')
@section('title', 'كشف حساب — ' . $project->name)
@section('page-title', 'كشف حساب التقسيط')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{
  --bg:#f4f7fb;--surface:#fff;--text:#0f172a;--muted:#64748b;
  --border:#e6ebf3;--hover:#f1f4f9;
  --accent:#4f46e5;--acbg:#eef2ff;
  --success:#059669;--sucbg:#ecfdf5;
  --danger:#dc2626;--danbg:#fef2f2;
  --warn:#d97706;--warnbg:#fffbeb;
}
body{font-family:'IBM Plex Sans Arabic','Cairo',sans-serif;background:var(--bg);color:var(--text);}

/* ── Statement Card ── */
.stmt-wrap{max-width:720px;margin:0 auto;}

.stmt-card{background:var(--surface);border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(15,23,42,.1);}

/* Header */
.stmt-head{background:linear-gradient(135deg,#0f172a 0%,#4f46e5 100%);padding:20px 24px;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;}
.stmt-head h2{font-size:1.2rem;font-weight:700;margin:0;}
.stmt-head p{margin:4px 0 0;font-size:.82rem;opacity:.75;}

/* Count badge */
.count-badge{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:5px 14px;font-size:.8rem;font-weight:700;white-space:nowrap;}

/* Action strip */
.stmt-actions{background:#1e2d40;padding:10px 20px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.sa-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;font-size:.82rem;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:.15s;}
.sa-btn.green{background:#059669;color:#fff;}
.sa-btn.blue{background:#4f46e5;color:#fff;}
.sa-btn.amber{background:#d97706;color:#fff;}
.sa-btn.red{background:#dc2626;color:#fff;}
.sa-btn.slate{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2);}
.sa-btn:hover{opacity:.88;color:#fff;}

/* Info table */
.info-grid{display:grid;grid-template-columns:1fr 1fr;border-bottom:3px solid #4f46e5;}
.ig-head{padding:10px 18px;font-size:.78rem;font-weight:700;color:#fff;text-align:center;}
.ig-head.right{background:#4f46e5;}
.ig-head.left{background:#0f172a;}
.info-row{display:contents;}
.info-row .lbl{background:#f0f2f8;padding:10px 18px;font-size:.84rem;font-weight:600;color:var(--muted);border-bottom:1px solid var(--border);}
.info-row .val{background:var(--surface);padding:10px 18px;font-size:.9rem;font-weight:700;color:var(--text);border-bottom:1px solid var(--border);text-align:center;}
.info-row.alt .lbl{background:#f8faff;}
.info-row.alt .val{background:#fafbff;}

/* Schedule table */
.sched-head{background:#1e2d40;color:#fff;padding:10px 20px;font-size:.84rem;font-weight:600;}
.sched-empty{background:#fff9eb;padding:12px 20px;text-align:center;font-size:.84rem;color:var(--warn);font-weight:600;}

.inst-table{width:100%;border-collapse:collapse;}
.inst-table th{background:#f0f2f8;padding:9px 14px;font-size:.73rem;font-weight:700;color:var(--muted);text-align:center;border-bottom:2px solid var(--border);}
.inst-table td{padding:9px 14px;font-size:.86rem;text-align:center;border-bottom:1px solid var(--border);}
.inst-table tr:last-child td{border-bottom:none;}
.inst-table tr.paid-row td{background:var(--sucbg);}
.inst-table tr.due-row td{background:var(--danbg);}
.inst-table tr.upcoming-row td{background:#fff;}

/* Status pill */
.spill{padding:3px 10px;border-radius:99px;font-size:.72rem;font-weight:700;}
.sp-paid{background:var(--sucbg);color:var(--success);}
.sp-due{background:var(--danbg);color:var(--danger);}
.sp-up{background:#f1f5f9;color:var(--muted);}

/* Footer */
.stmt-footer{background:#f8fafc;padding:14px 20px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:center;border-top:1px solid var(--border);}

/* ── Print CSS — يضغط الكشف في صفحة واحدة على قد الإمكان ── */
@media print {
  @page{size:A4 portrait;margin:8mm;}
  html,body{background:#fff !important;}
  .page-head,.sidebar,.topbar,.stmt-actions,.stmt-footer,.no-print{display:none !important;}
  .stmt-wrap{max-width:100%;margin:0;}
  .stmt-card{box-shadow:none;border-radius:0;}
  .page-wrap{padding:0 !important;margin:0 !important;}
  .inst-table tr.paid-row td{background:#e6f7f0 !important;-webkit-print-color-adjust:exact;}
  .inst-table tr.due-row td{background:#fde8e8 !important;-webkit-print-color-adjust:exact;}
  .stmt-head,.ig-head,.sched-head,.info-row .lbl,.info-row .val{-webkit-print-color-adjust:exact;print-color-adjust:exact;}

  /* تصغير الهوامش والخطوط عشان كل حاجة تدخل في صفحة واحدة */
  .stmt-head{padding:8px 16px;}
  .stmt-head h2{font-size:.95rem;}
  .stmt-head p{font-size:.7rem;margin-top:2px;}
  .info-grid{border-bottom-width:2px;}
  .ig-head{padding:4px 12px;font-size:.68rem;}
  .info-row .lbl,.info-row .val{padding:4px 12px;font-size:.7rem;border-bottom-width:1px;}
  .sched-head{padding:5px 14px;font-size:.72rem;}
  .inst-table th{padding:3px 8px;font-size:.6rem;}
  .inst-table td{padding:3px 8px;font-size:.66rem;}
  .inst-table td span{width:20px !important;height:20px !important;font-size:.62rem !important;}
  .spill{padding:1px 7px;font-size:.6rem;}

  /* منع قطع الصف بين صفحتين، والحفاظ على ترابط رأس الجدول */
  .inst-table tr{break-inside:avoid;}
  thead{display:table-header-group;}
}
</style>
@endpush

@section('content')

<div class="stmt-wrap">

  {{-- Back / print bar (hidden on print) --}}
  <div class="no-print" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
    <a href="{{ route('installments.index', ['project_id' => $project->id]) }}" class="btn ghost">
      <i class="fa fa-arrow-right me-1"></i> رجوع للأقساط
    </a>
    <button onclick="window.print()" class="btn" style="background:#4f46e5;color:#fff">
      <i class="fa fa-print me-1"></i> طباعة الكشف
    </button>
    @if($project->client->phone)
      @php
        $phone = preg_replace('/\D/', '', $project->client->phone);
        if (str_starts_with($phone, '0')) { $phone = '2' . $phone; } // Egypt prefix

        // Build WhatsApp message
        $insts = $installments;
        $lines = [];
        $lines[] = '*كشف حساب التقسيط*';
        $lines[] = 'المشروع: ' . $project->name;
        $lines[] = 'العميل: ' . $project->client->name;
        $lines[] = 'قيمة الأقساط الإجمالية: ' . \App\Support\Money::format($totalWithInst) . ' ج.م';
        $lines[] = 'المدفوع حتى الآن: ' . \App\Support\Money::format($downPaid) . ' ج.م';
        $lines[] = 'المتبقي: ' . \App\Support\Money::format($remaining) . ' ج.م';
        if ($monthCount > 0) {
          $lines[] = 'عدد الأقساط: ' . $monthCount . ' قسط';
          $lines[] = 'القسط الشهري: ' . \App\Support\Money::format($avgMonthly) . ' ج.م';
        }
        $lines[] = '';
        $lines[] = '*جدول الأقساط:*';
        foreach ($installments as $n => $inst) {
          $status = match($inst->status) { 'paid' => '✅', 'due' => '❗', default => '⏳' };
          $lines[] = ($n+1) . '- ' . $inst->due_date->format('Y/m/d') . ' — ' . \App\Support\Money::format($inst->amount) . ' ج.م ' . $status;
        }
        $waText = urlencode(implode("\n", $lines));
        $waUrl = 'https://wa.me/' . $phone . '?text=' . $waText;
      @endphp
      <a href="{{ $waUrl }}" target="_blank" class="btn" style="background:#25d366;color:#fff">
        <i class="fa-brands fa-whatsapp me-1"></i> إرسال على واتساب
      </a>
    @endif
  </div>

  <div class="stmt-card">

    {{-- ── Header ── --}}
    <div class="stmt-head">
      <div>
        <h2><i class="fa fa-file-contract me-2"></i>كشف حساب — {{ $settings->company_name }}</h2>
        <p>{{ $settings->company_phone }}</p>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <span class="count-badge">
          <i class="fa fa-list-ol me-1"></i>عدد {{ $installments->count() }} أقساط
        </span>
        @if($downPaid > 0)
          <span class="count-badge" style="background:rgba(5,150,105,.25);border-color:rgba(5,150,105,.4)">
            <i class="fa fa-circle-check me-1"></i>محصول {{ \App\Support\Money::format($downPaid) }} ج.م
          </span>
        @endif
      </div>
    </div>

    {{-- ── Action strip (no print) ── --}}
    <div class="stmt-actions no-print">
      <a href="{{ route('installments.create') }}?project_id={{ $project->id }}" class="sa-btn green">
        <i class="fa fa-cash-register"></i> تسجيل دفعة
      </a>
      <a href="{{ route('installments.plan.form') }}?project_id={{ $project->id }}" class="sa-btn blue">
        <i class="fa fa-calendar-plus"></i> خطة جديدة
      </a>
      @if($project->client->phone)
        <a href="{{ $waUrl }}" target="_blank" class="sa-btn" style="background:#25d366">
          <i class="fa-brands fa-whatsapp"></i> واتساب
        </a>
      @endif
      <span style="flex:1"></span>
      <button onclick="window.print()" class="sa-btn slate">
        <i class="fa fa-print"></i> طباعة
      </button>
    </div>

    {{-- ── Info Grid ── --}}
    <div class="info-grid">
      <div class="ig-head right">اسم العميل / البيان</div>
      <div class="ig-head left">الشركة / القيمة</div>

      <div class="info-row alt">
        <div class="lbl">اسم العميل</div>
        <div class="val">{{ $project->client->name }}</div>
      </div>
      <div class="info-row">
        <div class="lbl">المشروع</div>
        <div class="val">{{ $project->name }}</div>
      </div>
      <div class="info-row alt">
        <div class="lbl">تاريخ التعاقد</div>
        <div class="val">{{ $project->created_at->format('Y-m-d') }}</div>
      </div>
      <div class="info-row">
        <div class="lbl">إجمالي قيمة الأقساط</div>
        <div class="val" style="color:#0f172a">{{ \App\Support\Money::format($totalWithInst) }} ج.م</div>
      </div>
      <div class="info-row alt">
        <div class="lbl">المقدم المدفوع حتى الآن</div>
        <div class="val" style="color:var(--success)">{{ \App\Support\Money::format($downPaid) }} ج.م</div>
      </div>
      <div class="info-row">
        <div class="lbl">المتبقي (قبل الفوائد)</div>
        <div class="val" style="color:var(--danger)">{{ \App\Support\Money::format($totalWithInst - $downPaid) }} ج.م</div>
      </div>
      <div class="info-row alt">
        <div class="lbl">عدد الأقساط الشهرية</div>
        <div class="val">{{ $monthCount }} قسط</div>
      </div>
      @if($avgMonthly > 0)
      <div class="info-row">
        <div class="lbl">القسط الشهري</div>
        <div class="val" style="color:var(--accent);font-size:1.05rem">{{ \App\Support\Money::format($avgMonthly) }} ج.م</div>
      </div>
      @endif
      @if($payDay)
      <div class="info-row alt">
        <div class="lbl">موعد سداد القسط</div>
        <div class="val">كل {{ $payDay }} من الشهر</div>
      </div>
      @endif
      @if($project->client->phone)
      <div class="info-row">
        <div class="lbl">رقم الموبايل</div>
        <div class="val" dir="ltr">{{ $project->client->phone }}</div>
      </div>
      @endif
    </div>

    {{-- ── Schedule ── --}}
    <div class="sched-head">
      <i class="fa fa-table-list me-2"></i>جدول الأقساط
      @if($downPaid <= 0)
        <span style="font-size:.78rem;font-weight:400;opacity:.7;margin-right:10px">لم يتم سداد أي دفعات حتى الآن</span>
      @endif
    </div>

    <div class="table-responsive">
      <table class="inst-table">
        <thead>
          <tr>
            <th>#</th>
            <th class="text-start">البيان</th>
            <th>البند</th>
            <th>تاريخ الاستحقاق</th>
            <th>المبلغ</th>
            <th>تاريخ السداد</th>
            <th>الحالة</th>
          </tr>
        </thead>
        <tbody>
          @forelse($installments as $n => $inst)
            <tr class="{{ $inst->status }}-row">
              <td>
                <span style="width:28px;height:28px;border-radius:7px;background:{{ $inst->status==='paid' ? 'var(--success)' : ($inst->status==='due' ? 'var(--danger)' : '#94a3b8') }};color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700">
                  {{ $n + 1 }}
                </span>
              </td>
              <td class="text-start" style="font-weight:600">{{ $inst->label }}</td>
              <td><small>{{ $inst->band?->name ?? '—' }}</small></td>
              <td>{{ $inst->due_date->format('Y/m/d') }}</td>
              <td style="font-weight:700;font-size:.95rem">{{ \App\Support\Money::format($inst->amount) }} ج.م</td>
              <td>
                @if($inst->paid_date)
                  <span style="color:var(--success)">{{ $inst->paid_date->format('Y/m/d') }}</span>
                @else
                  <span style="color:#cbd5e1">—</span>
                @endif
              </td>
              <td>
                <span class="spill sp-{{ $inst->status === 'upcoming' ? 'up' : $inst->status }}">
                  {{ $inst->statusAr() }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">
                <i class="fa fa-inbox fa-2x opacity-50 d-block mb-2"></i>
                لا توجد أقساط مسجلة
              </td>
            </tr>
          @endforelse
        </tbody>
        @if($installments->count())
        <tfoot>
          <tr style="background:#f0f2f8;font-weight:700">
            <td colspan="4" style="text-align:start;padding:10px 14px">الإجمالي</td>
            <td>{{ \App\Support\Money::format($totalWithInst) }} ج.م</td>
            <td colspan="2">
              محصول: <span style="color:var(--success)">{{ \App\Support\Money::format($downPaid) }} ج.م</span>
              &nbsp;|&nbsp;
              متبقي: <span style="color:var(--danger)">{{ \App\Support\Money::format($remaining) }} ج.م</span>
            </td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>

    {{-- ── Footer ── --}}
    <div class="stmt-footer no-print">
      <button onclick="window.print()" class="sa-btn" style="background:#4f46e5;color:#fff">
        <i class="fa fa-print me-1"></i> طباعة الكشف
      </button>
      @if($project->client->phone ?? false)
        <a href="{{ $waUrl }}" target="_blank" class="sa-btn" style="background:#25d366;color:#fff">
          <i class="fa-brands fa-whatsapp me-1"></i> إرسال العقد على واتساب
        </a>
      @endif
      <a href="{{ route('installments.index', ['project_id' => $project->id]) }}" class="sa-btn slate">
        <i class="fa fa-arrow-right me-1"></i> رجوع
      </a>
    </div>

  </div>{{-- /stmt-card --}}

</div>{{-- /stmt-wrap --}}

@endsection
