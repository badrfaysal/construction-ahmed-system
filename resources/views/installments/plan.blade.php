@extends('layouts.app')
@section('title', 'مولد خطة التقسيط')
@section('page-title', 'خطة التقسيط')

@section('content')
<div class="page-head">
  <div><h3>مولد خطة التقسيط</h3><p>حدد المبلغ الإجمالي والمقدم وعدد الأشهر — سيتم إنشاء الأقساط تلقائياً</p></div>
  <a href="{{ route('installments.index') }}" class="btn ghost">رجوع</a>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  </div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start">

<form method="POST" action="{{ route('installments.plan.store') }}" id="plan-form">
  @csrf
  <div class="form-card">
    <div class="field">
      <label>المشروع *</label>
      <select name="project_id" id="plan-project" required onchange="loadPlanBands(this.value)">
        <option value="">— اختر المشروع —</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" {{ ($selectedProjectId == $p->id) ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="field">
      <label>البند (اختياري — لربط الأقساط ببند معين)</label>
      <select name="band_id" id="plan-band" onchange="loadPlanData()">
        <option value="">— المشروع كاملاً (بدون بند محدد) —</option>
        @foreach($bands as $b)
          <option value="{{ $b->id }}">{{ $b->name }}</option>
        @endforeach
      </select>
      <small class="muted" id="plan-band-hint"></small>
    </div>

    <div class="row2">
      <div class="field">
        <label>
          المبلغ الإجمالي للعقد (ج.م) *
          <span id="auto-fill-badge" style="display:none;font-size:.72rem;background:#ecfdf5;color:#059669;padding:2px 8px;border-radius:8px;font-weight:600">✓ تلقائي</span>
        </label>
        <input type="number" name="total_amount" id="inp-total" min="0" step="0.01" value="{{ old('total_amount', $project?->initialContractValue() ?? '') }}" required oninput="calcPreview()">
        <small class="muted">المبلغ المطلوب من العميل في هذا البند/المشروع</small>
      </div>
      <div class="field">
        <label>
          الدفعة المقدمة (ج.م) *
          <span id="down-fill-badge" style="display:none;font-size:.72rem;background:#ecfdf5;color:#059669;padding:2px 8px;border-radius:8px;font-weight:600">✓ من دفعاته المسبقة</span>
        </label>
        <input type="number" name="down_payment" id="inp-down" min="0" step="0.01" value="{{ old('down_payment', 0) }}" required oninput="calcPreview()">
        <small class="muted">يُحسب تلقائياً من المدفوعات السابقة للبند/المشروع</small>
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label>عدد الأشهر (الأقساط الشهرية) *</label>
        <input type="number" name="months" id="inp-months" min="1" max="120" value="{{ old('months', 12) }}" required oninput="calcPreview()">
      </div>
      <div class="field">
        <label>نسبة الفائدة على المبلغ المتبقي %</label>
        <input type="number" name="interest_rate" id="inp-interest" min="0" max="100" step="0.01" value="{{ old('interest_rate', 0) }}" oninput="calcPreview()">
        <small class="muted">0 = بدون فائدة</small>
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label>تاريخ بدء الأقساط *</label>
        <input type="date" name="start_date" value="{{ old('start_date', today()->toDateString()) }}" required>
      </div>
      <div class="field">
        <label>طريقة الدفع</label>
        <input type="text" name="payment_method" value="{{ old('payment_method') }}" placeholder="كاش / تحويل بنكي">
      </div>
    </div>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>
        إنشاء خطة الأقساط
      </button>
    </div>
  </div>
</form>

{{-- Live preview --}}
<div class="form-card" id="preview-card">
  <h4 style="margin:0 0 16px">معاينة الخطة</h4>
  <div class="grid cols-2" style="gap:12px;margin-bottom:16px">
    <div style="background:var(--bg);border-radius:8px;padding:12px">
      <div class="muted" style="font-size:.8rem">المقدم</div>
      <div class="tnum" id="prev-down" style="font-size:1.1rem;font-weight:600">—</div>
    </div>
    <div style="background:var(--bg);border-radius:8px;padding:12px">
      <div class="muted" style="font-size:.8rem">القسط الشهري</div>
      <div class="tnum" id="prev-monthly" style="font-size:1.1rem;font-weight:600">—</div>
    </div>
    <div style="background:var(--bg);border-radius:8px;padding:12px">
      <div class="muted" style="font-size:.8rem">إجمالي الفوائد</div>
      <div class="tnum" id="prev-interest" style="font-size:1.1rem;font-weight:600;color:var(--warn)">—</div>
    </div>
    <div style="background:var(--bg);border-radius:8px;padding:12px">
      <div class="muted" style="font-size:.8rem">إجمالي مع الفوائد</div>
      <div class="tnum" id="prev-total-with" style="font-size:1.1rem;font-weight:600">—</div>
    </div>
  </div>
  <div id="preview-rows" style="max-height:340px;overflow-y:auto">
    <p class="muted" style="text-align:center;padding:20px">أدخل البيانات لعرض الجدول</p>
  </div>
</div>

</div>

@push('scripts')
<script>
function fmt(n) { return n.toLocaleString('ar-EG', {minimumFractionDigits:0, maximumFractionDigits:0}); }

function calcPreview() {
  const total    = parseFloat(document.getElementById('inp-total').value) || 0;
  const down     = parseFloat(document.getElementById('inp-down').value)  || 0;
  const months   = parseInt(document.getElementById('inp-months').value)  || 0;
  const interest = parseFloat(document.getElementById('inp-interest').value) || 0;

  if (total <= 0 || months <= 0) {
    document.getElementById('preview-rows').innerHTML = '<p class="muted" style="text-align:center;padding:20px">أدخل البيانات لعرض الجدول</p>';
    return;
  }

  const remaining    = total - down;
  const withInterest = remaining * (1 + interest / 100);
  const monthly      = withInterest / months;
  const interestAmt  = withInterest - remaining;

  document.getElementById('prev-down').textContent    = fmt(down) + ' ج.م';
  document.getElementById('prev-monthly').textContent  = fmt(monthly) + ' ج.م';
  document.getElementById('prev-interest').textContent = fmt(interestAmt) + ' ج.م';
  document.getElementById('prev-total-with').textContent = fmt(down + withInterest) + ' ج.م';

  // Build schedule table
  const startInput = document.querySelector('[name=start_date]').value;
  if (!startInput) return;

  let rows = '<table style="width:100%;font-size:.85rem"><thead><tr><th style="text-align:right;padding:6px 8px">القسط</th><th style="text-align:right;padding:6px 8px">التاريخ</th><th style="text-align:left;padding:6px 8px">المبلغ</th></tr></thead><tbody>';
  const start = new Date(startInput);

  if (down > 0) {
    rows += `<tr><td style="padding:5px 8px">دفعة مقدم</td><td style="padding:5px 8px">${start.toISOString().slice(0,10)}</td><td style="padding:5px 8px;text-align:left;font-weight:600">${fmt(down)} ج.م</td></tr>`;
  }

  for (let i = 1; i <= months; i++) {
    const d = new Date(start);
    d.setMonth(d.getMonth() + i);
    rows += `<tr><td style="padding:5px 8px">القسط ${i} من ${months}</td><td style="padding:5px 8px">${d.toISOString().slice(0,10)}</td><td style="padding:5px 8px;text-align:left">${fmt(monthly)} ج.م</td></tr>`;
  }
  rows += '</tbody></table>';
  document.getElementById('preview-rows').innerHTML = rows;
}

function loadPlanBands(projectId) {
  const sel = document.getElementById('plan-band');
  sel.innerHTML = '<option value="">— المشروع كاملاً (بدون بند محدد) —</option>';
  if (!projectId) return;
  fetch('/api/projects/' + projectId + '/bands')
    .then(r => r.json())
    .then(bands => {
      bands.forEach(b => {
        sel.innerHTML += `<option value="${b.id}">${b.name}</option>`;
      });
      // Auto-fetch project-level data (no band) after bands load
      loadPlanData();
    });
}

// Fetch billed + paid amounts and auto-fill the fields
function loadPlanData() {
  const projectId = document.getElementById('plan-project').value;
  if (!projectId) return;

  const bandId = document.getElementById('plan-band').value;
  const url = '/api/projects/' + projectId + '/plan-data' + (bandId ? '?band_id=' + bandId : '');

  fetch(url)
    .then(r => r.json())
    .then(data => {
      // Fill total_amount with billed
      if (data.billed > 0) {
        document.getElementById('inp-total').value = data.billed.toFixed(2);
        document.getElementById('auto-fill-badge').style.display = 'inline';
      } else {
        document.getElementById('auto-fill-badge').style.display = 'none';
      }
      // Fill down_payment with already-paid installments
      if (data.paid > 0) {
        document.getElementById('inp-down').value = data.paid.toFixed(2);
        document.getElementById('down-fill-badge').style.display = 'inline';
      } else {
        document.getElementById('inp-down').value = '0';
        document.getElementById('down-fill-badge').style.display = 'none';
      }
      // Show hint
      const hint = document.getElementById('plan-band-hint');
      if (data.billed > 0) {
        hint.textContent = 'مبلغ البند/المشروع: ' + data.billed.toLocaleString('ar-EG') + ' ج.م — مدفوع مسبقاً: ' + data.paid.toLocaleString('ar-EG') + ' ج.م';
      } else {
        hint.textContent = 'لا توجد خامات مسجلة بعد في هذا البند';
      }
      calcPreview();
    })
    .catch(() => { /* silent if no data */ });
}

// Run on load to populate preview if old() values exist
calcPreview();
document.querySelector('[name=start_date]').addEventListener('change', calcPreview);
</script>
@endpush
@endsection
