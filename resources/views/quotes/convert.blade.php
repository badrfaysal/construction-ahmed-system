@extends('layouts.app')
@section('title', 'تحويل عرض لمشروع')
@section('page-title', 'تحويل عرض ' . $quote->ref . ' لمشروع')

@section('content')
<div class="page-head">
  <div>
    <h3>تحويل العرض إلى مشروع</h3>
    <p>{{ $quote->client_name }} — قيمة التعاقد المبدئية {{ \App\Support\Money::format($quote->total()) }} ج.م</p>
  </div>
  <a href="{{ route('quotes.show', $quote) }}" class="btn ghost">رجوع</a>
</div>

@include('partials._errors')

<style>
  .mat-layout {
    --accent: #2563eb; --accent-2: #3b82f6; --accent-soft: #eff6ff; --accent-ink: #1e3a8a;
    display:flex; gap:32px; align-items:flex-start;
  }
  .mat-layout .mat-form{flex:1;min-width:0}
  
  .mat-totals {
    position:sticky;top:24px;width:250px;flex-shrink:0;
    background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.8); border-radius: 24px; padding: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.04), inset 0 2px 4px rgba(255,255,255,0.5);
  }
  .mat-totals .section-label{margin:0 0 20px; font-size: 1.3rem; color: var(--ink); font-weight: 800; text-align: center;}
  .mat-totals .card.stat {
    margin:0 0 16px; background: linear-gradient(145deg, #ffffff, #f8fafc); border: none;
    box-shadow: 0 8px 20px rgba(0,0,0,0.03); padding: 16px 20px; border-radius: 20px; transition: transform 0.3s;
  }
  .mat-totals .card.stat:hover { transform: translateY(-3px); }
  .mat-totals .card.stat:last-child{margin-bottom:0}
  @media (max-width:1100px) {
    .mat-layout{flex-direction:column}
    .mat-totals{position:static;width:100%}
  }
  
  /* تصغير حجم الخانات في الجدول عشان تظهر كلها بدون شريط تمرير */
  .conv-row input[type="number"], .conv-row input[type="date"], .conv-row select {
    padding: 4px 6px !important;
    font-size: 0.85rem !important;
    height: auto !important;
  }
</style>

<div class="mat-layout">
<div class="mat-form">

<div class="flash" style="background:var(--card);border:1px solid var(--line);margin-bottom:16px">
  علّم الأصناف اللي <strong>تم شراؤها فعلاً</strong> — هتتسجل كمشتريات حقيقية في المشروع وتتخصم من محفظة المقاولات. الأصناف اللي متتعلمش، المشروع هيتعمل من غير ما تسجّلها (تقدر تسجّلها بعدين).
</div>

<form method="POST" action="{{ route('quotes.convert.store', $quote) }}">
  @csrf

  {{-- نسبة الإشراف الافتراضية — تتطبّق على كل أصناف العرض دلوقتي، وتبقى القيمة
       الافتراضية للمشروع ولأي خامة/مصنعية تتسجّل بعد كده --}}
  <div class="form-card" style="max-width:none;margin-bottom:16px;background:#fffbeb;border:1px solid #fcd34d">
    <div class="section-label" style="margin-top:0">نسبة الإشراف الافتراضية</div>
    <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap">
      <div class="field" style="margin:0;max-width:220px">
        <label style="font-weight:700">النسبة % <span style="color:#dc2626">*</span></label>
        <input type="number" name="default_supervision_pct" id="conv_sup" min="0" max="100" step="0.1"
               value="{{ old('default_supervision_pct', $settings->default_supervision_pct) }}"
               oninput="applyConvSupervision(this.value)" required>
      </div>
      <button type="button" class="btn ghost sm" onclick="applyConvSupervision(document.getElementById('conv_sup').value)">
        طبّق على كل الأصناف
      </button>
      <div class="field" style="margin:0;min-width:240px">
        @include('partials._wallet-select', ['wallets' => $wallets, 'label' => 'محفظة الصرف للمشتريات', 'selectStyle' => 'width:100%'])
      </div>
    </div>
    <p class="muted" style="margin:8px 0 0;font-size:12px">هتتطبّق على كل أصناف العرض دلوقتي، وتبقى النسبة الافتراضية للمشروع ولأي خامة/مصنعية جديدة بعد كده. الأصناف المشتراة هتتخصم من المحفظة المختارة.</p>
  </div>

  @php $idx = 0; $wIdx = 0; @endphp

  @foreach($quote->bands as $band)
    <div class="section-label" style="margin-top:18px">{{ $band->name }} <span class="muted">— {{ \App\Support\Money::format($band->price) }} ج.م</span></div>
    <div class="table-card" style="margin-bottom:14px">
      @if($band->items->count())
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th style="width:50px">تم شراؤه؟</th>
                <th>الصنف</th>
                <th class="num">الكمية</th>
                <th class="num">سعر الشراء</th>
                <th class="num">سعر البيع</th>
                <th class="num">إشراف %</th>
                <th>المورد</th>
                <th>التاريخ</th>
                <th>طريقة الدفع</th>
              </tr>
            </thead>
            <tbody>
              @foreach($band->items as $item)
                <tr id="conv-row-{{ $idx }}" class="conv-row">
                  <td style="text-align:center">
                    <input type="hidden" name="items[{{ $idx }}][name]" value="{{ $item->name }}">
                    <input type="hidden" name="items[{{ $idx }}][quote_band_id]" value="{{ $band->id }}">
                    <input type="checkbox" name="items[{{ $idx }}][purchased]" value="1" {{ old('items.' . $idx . '.purchased') ? 'checked' : '' }} style="width:18px;height:18px">
                  </td>
                  <td>
                    <strong>{{ $item->name }}</strong>
                    <input type="hidden" name="items[{{ $idx }}][unit]" value="وحدة">
                  </td>
                  <td class="num"><input type="number" name="items[{{ $idx }}][qty]" value="{{ old('items.' . $idx . '.qty', rtrim(rtrim($item->qty, '0'), '.')) }}" min="0" step="0.01" style="width:60px"></td>
                  <td class="num"><input type="number" name="items[{{ $idx }}][unit_price]" value="{{ old('items.' . $idx . '.unit_price', $item->unit_price) }}" min="0" step="0.01" style="width:80px"></td>
                  <td class="num"><input type="number" name="items[{{ $idx }}][sell_price]" value="{{ $item->unit_price }}" min="0" step="0.01" style="width:80px; background-color:#f1f5f9; cursor:not-allowed;" readonly title="سعر البيع محدد مسبقاً في عرض السعر"></td>
                  <td class="num"><input type="number" name="items[{{ $idx }}][supervision_pct]" value="{{ old('items.' . $idx . '.supervision_pct', $item->supervision_pct) }}" min="0" max="100" step="0.1" style="width:55px"></td>
                  <td>
                    <select name="items[{{ $idx }}][supplier_id]" style="width:100%; max-width:110px;">
                      <option value="">— بدون —</option>
                      @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('items.' . $idx . '.supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="date" name="items[{{ $idx }}][date]" value="{{ old('items.' . $idx . '.date', today()->format('Y-m-d')) }}"></td>
                  <td>
                    {{-- Payment type --}}
                    <div style="display:flex;flex-direction:column;gap:4px">
                      <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem">
                        <input type="radio" name="items[{{ $idx }}][payment_status]" value="paid" {{ old('items.' . $idx . '.payment_status', 'paid') === 'paid' ? 'checked' : '' }} onchange="toggleConvPaid({{ $idx }},this.value)">
                        <span style="color:#059669;font-weight:600">كاش</span>
                      </label>
                      <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem">
                        <input type="radio" name="items[{{ $idx }}][payment_status]" value="partial" {{ old('items.' . $idx . '.payment_status') === 'partial' ? 'checked' : '' }} onchange="toggleConvPaid({{ $idx }},this.value)">
                        <span style="color:#d97706;font-weight:600">جزئي</span>
                      </label>
                      <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem">
                        <input type="radio" name="items[{{ $idx }}][payment_status]" value="deferred" {{ old('items.' . $idx . '.payment_status') === 'deferred' ? 'checked' : '' }} onchange="toggleConvPaid({{ $idx }},this.value)">
                        <span style="color:#dc2626;font-weight:600">أجل كامل</span>
                      </label>
                      <div id="conv-paid-{{ $idx }}" style="display:{{ old('items.' . $idx . '.payment_status') === 'partial' ? 'block' : 'none' }};margin-top:4px">
                        <input type="number" name="items[{{ $idx }}][paid_amount]" value="{{ old('items.' . $idx . '.paid_amount') }}" placeholder="المبلغ المدفوع" min="0" step="0.01" style="width:100px;border-color:#d97706">
                        <small style="color:#d97706;display:block;font-size:.7rem">أدخل المبلغ المدفوع</small>
                      </div>
                    </div>
                  </td>
                </tr>
                @php $idx++; @endphp
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="empty-state" style="padding:16px"><p class="muted">لا توجد أصناف مفصّلة في هذا البند.</p></div>
      @endif
      
      @if($band->workers->count())
        <div class="table-scroll" style="margin-top: 16px; border-top: 2px dashed var(--line); padding-top: 16px;">
          <h4 style="margin:0 0 12px; font-size: 1.1rem; color: #1e293b;">الصنايعية والفنيين</h4>
          <table>
            <thead>
              <tr>
                <th style="min-width: 140px;">اسم الفني</th>
                <th>نوع التعاقد</th>
                <th class="num">الكمية</th>
                <th class="num">سعر البيع</th>
                <th class="num">تكلفة الوحدة</th>
                <th class="num" style="min-width: 110px;">إجمالي التعاقد</th>
                <th style="min-width: 160px;">دفعة أولية (للفني)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($band->workers as $worker)
                <tr class="conv-worker-row" style="border-bottom: 1px solid var(--line);">
                  <td>
                    <strong>{{ $worker->name }}</strong>
                    <input type="hidden" name="workers[{{ $wIdx }}][id]" value="{{ $worker->id }}">
                    <input type="hidden" name="workers[{{ $wIdx }}][name]" value="{{ $worker->name }}">
                    <input type="hidden" name="workers[{{ $wIdx }}][quote_band_id]" value="{{ $band->id }}">
                  </td>
                  <td>
                    <span style="display:inline-block; padding: 4px 8px; background: #f1f5f9; border-radius: 4px; font-size: 12px; font-weight: bold;">
                      {{ $worker->contract_type == 'lump_sum' ? 'مقطوعية' : ($worker->contract_type == 'per_meter' ? 'بالمتر' : 'بالقطعة') }}
                    </span>
                    <input type="hidden" name="workers[{{ $wIdx }}][contract_type]" value="{{ $worker->contract_type }}">
                  </td>
                  <td class="num">
                    @if(in_array($worker->contract_type, ['per_meter', 'per_piece']))
                      <input type="number" name="workers[{{ $wIdx }}][contract_qty]" id="cw-qty-{{ $wIdx }}" value="{{ old('workers.' . $wIdx . '.contract_qty', $worker->contract_qty) }}" min="0" step="0.01" style="width:60px; background-color:#f1f5f9; cursor:not-allowed;" readonly>
                    @else
                      <span class="muted">—</span>
                      <input type="hidden" name="workers[{{ $wIdx }}][contract_qty]" id="cw-qty-{{ $wIdx }}" value="0">
                    @endif
                  </td>
                  <td class="num">
                    <span style="color:var(--pos); font-weight:bold;">{{ \App\Support\Money::format($worker->sell_amount) }}</span>
                  </td>
                  <td class="num">
                    @if(in_array($worker->contract_type, ['per_meter', 'per_piece']))
                      <input type="number" name="workers[{{ $wIdx }}][contract_unit_rate]" id="cw-rate-{{ $wIdx }}" value="{{ old('workers.' . $wIdx . '.contract_unit_rate', $worker->contract_unit_rate) }}" min="0" step="0.01" style="width:80px;" oninput="recalcConvWorker({{ $wIdx }})">
                    @else
                      <span class="muted">—</span>
                      <input type="hidden" name="workers[{{ $wIdx }}][contract_unit_rate]" id="cw-rate-{{ $wIdx }}" value="0">
                    @endif
                  </td>
                  <td class="num">
                    <input type="number" name="workers[{{ $wIdx }}][amount]" id="cw-amt-{{ $wIdx }}" value="{{ old('workers.' . $wIdx . '.amount', $worker->amount) }}" min="0" step="0.01" style="width:100%; max-width: 110px; font-weight: bold; background: #fffbeb" required oninput="this.dataset.touched='1'">
                  </td>
                  <td>
                    <div style="display:flex;flex-direction:column;gap:4px">
                      <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem">
                        <input type="radio" name="workers[{{ $wIdx }}][payment_status]" value="paid" {{ old('workers.' . $wIdx . '.payment_status') === 'paid' ? 'checked' : '' }} onchange="toggleConvWorkerPaid({{ $wIdx }},this.value)">
                        <span style="color:#059669;font-weight:600">دفعة كاش</span>
                      </label>
                      <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem">
                        <input type="radio" name="workers[{{ $wIdx }}][payment_status]" value="deferred" {{ old('workers.' . $wIdx . '.payment_status', 'deferred') === 'deferred' ? 'checked' : '' }} onchange="toggleConvWorkerPaid({{ $wIdx }},this.value)">
                        <span style="color:#dc2626;font-weight:600">أجل كامل</span>
                      </label>
                      <div id="conv-worker-paid-{{ $wIdx }}" style="display:{{ old('workers.' . $wIdx . '.payment_status') === 'paid' ? 'block' : 'none' }};margin-top:4px">
                        <input type="number" name="workers[{{ $wIdx }}][paid_amount]" value="{{ old('workers.' . $wIdx . '.paid_amount') }}" placeholder="المبلغ المدفوع" min="0" step="0.01" style="width:100%; max-width: 110px; border-color:#059669;">
                      </div>
                    </div>
                  </td>
                </tr>
                @php $wIdx++; @endphp
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  @endforeach

  <div class="btn-row" style="margin-top:16px">
    <button type="submit" class="btn pos">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>
      إنشاء المشروع
    </button>
    <a href="{{ route('quotes.show', $quote) }}" class="btn ghost">إلغاء</a>
  </div>
</form>
</div>

<aside class="mat-totals">
  <div class="section-label">الإجماليات (للمشتريات)</div>
  <div class="card stat">
    <div class="top"><span class="label">عدد الأصناف</span></div>
    <div class="val tnum"><span id="tot-items-count">0</span></div>
  </div>
  <div class="card stat" style="background: #fffbeb; border: 1px solid #fde68a;">
    <div class="top"><span class="label" style="color: #b45309; font-weight: 600;">إجمالي الشراء (تكلفة)</span></div>
    <div class="val tnum" style="color: #92400e;"><span id="tot-purchase">0</span> <small style="color: #d97706;">ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي البيع</span></div>
    <div class="val tnum"><span id="tot-sell">0</span> <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">الإجمالي للعميل (بعد الإشراف)</span></div>
    <div class="val tnum"><span id="tot-client">0</span> <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي الربح</span></div>
    <div class="val tnum" style="color:var(--pos)"><span id="tot-profit">0</span> <small>ج.م</small></div>
    <div class="note">فرق السعر: <span id="tot-pricediff">0</span> · إشراف: <span id="tot-sup">0</span></div>
  </div>
</aside>
</div>
@push('scripts')
<script>
  function toggleConvPaid(idx, val) {
    document.getElementById('conv-paid-'+idx).style.display = (val === 'partial') ? 'block' : 'none';
  }
  function toggleConvWorkerPaid(idx, val) {
    document.getElementById('conv-worker-paid-'+idx).style.display = (val === 'paid') ? 'block' : 'none';
  }
  function toggleConvWorkerQty(idx, type) {
    const show = (type === 'per_meter' || type === 'per_piece');
    document.getElementById('cw-qty-'+idx).style.display = show ? 'inline-block' : 'none';
    document.getElementById('cw-rate-'+idx).style.display = show ? 'inline-block' : 'none';
  }
  function recalcConvWorker(idx) {
    const amtInput = document.getElementById('cw-amt-'+idx);
    if (amtInput.dataset.touched === '1') return;
    const q = parseFloat(document.getElementById('cw-qty-'+idx).value) || 0;
    const r = parseFloat(document.getElementById('cw-rate-'+idx).value) || 0;
    amtInput.value = (q * r).toFixed(2);
  }
// تطبيق نسبة الإشراف الافتراضية على كل خانات إشراف الأصناف
function applyConvSupervision(v) {
  document.querySelectorAll('input[name^="items"][name$="[supervision_pct]"]').forEach(i => { i.value = v; });
  recalcConvTotals();
}

function recalcConvTotals() {
  let purchase = 0, sell = 0, client = 0, itemsCount = 0;
  document.querySelectorAll('.conv-row').forEach(row => {
    const isChecked = row.querySelector('input[type="checkbox"]').checked;
    if (!isChecked) return;
    
    itemsCount++;
    const qty = parseFloat(row.querySelector('input[name$="[qty]"]').value) || 0;
    const cost = parseFloat(row.querySelector('input[name$="[unit_price]"]').value) || 0;
    const sp = parseFloat(row.querySelector('input[name$="[sell_price]"]').value) || 0;
    const pct = parseFloat(row.querySelector('input[name$="[supervision_pct]"]').value) || 0;
    
    purchase += qty * cost;
    sell += qty * sp;
    client += qty * (sp + cost * (pct / 100));
  });
  
  const fmt = n => Math.round(n).toLocaleString('en-US');
  document.getElementById('tot-items-count').textContent = itemsCount;
  document.getElementById('tot-purchase').textContent = fmt(purchase);
  document.getElementById('tot-sell').textContent = fmt(sell);
  document.getElementById('tot-client').textContent = fmt(client);
  document.getElementById('tot-profit').textContent = fmt(client - purchase);
  document.getElementById('tot-pricediff').textContent = fmt(sell - purchase);
  document.getElementById('tot-sup').textContent = fmt(client - sell);
}

document.addEventListener('change', function(e) {
  if (e.target.closest('.conv-row') || e.target.id === 'conv_sup') recalcConvTotals();
});
document.addEventListener('input', function(e) {
  if (e.target.closest('.conv-row') || e.target.id === 'conv_sup') recalcConvTotals();
});
document.addEventListener('DOMContentLoaded', recalcConvTotals);
</script>
@endpush
@endsection
