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

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

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

  @php $idx = 0; @endphp

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
                <th>الوحدة</th>
                <th class="num">سعر الشراء</th>
                <th class="num">سعر البيع</th>
                <th class="num">إشراف %</th>
                <th>المورد</th>
                <th>التاريخ</th>
                <th style="min-width:200px">طريقة الدفع</th>
              </tr>
            </thead>
            <tbody>
              @foreach($band->items as $item)
                <tr id="conv-row-{{ $idx }}">
                  <td style="text-align:center">
                    <input type="hidden" name="items[{{ $idx }}][name]" value="{{ $item->name }}">
                    <input type="hidden" name="items[{{ $idx }}][quote_band_id]" value="{{ $band->id }}">
                    <input type="checkbox" name="items[{{ $idx }}][purchased]" value="1" style="width:18px;height:18px">
                  </td>
                  <td><strong>{{ $item->name }}</strong></td>
                  <td class="num"><input type="number" name="items[{{ $idx }}][qty]" value="{{ rtrim(rtrim($item->qty, '0'), '.') }}" min="0" step="0.01" style="width:80px"></td>
                  <td><input type="text" name="items[{{ $idx }}][unit]" value="وحدة" style="width:80px"></td>
                  <td class="num"><input type="number" name="items[{{ $idx }}][unit_price]" value="{{ $item->unit_price }}" min="0" step="0.01" style="width:100px"></td>
                  <td class="num"><input type="number" name="items[{{ $idx }}][sell_price]" value="{{ $item->unit_price }}" min="0" step="0.01" style="width:100px"></td>
                  <td class="num"><input type="number" name="items[{{ $idx }}][supervision_pct]" value="{{ $item->supervision_pct }}" min="0" max="100" step="0.1" style="width:70px"></td>
                  <td>
                    <select name="items[{{ $idx }}][supplier_id]" style="min-width:120px">
                      <option value="">— بدون —</option>
                      @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="date" name="items[{{ $idx }}][date]" value="{{ today()->format('Y-m-d') }}"></td>
                  <td>
                    {{-- Payment type --}}
                    <div style="display:flex;flex-direction:column;gap:4px">
                      <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem">
                        <input type="radio" name="items[{{ $idx }}][payment_status]" value="paid" checked onchange="toggleConvPaid({{ $idx }},this.value)">
                        <span style="color:#059669;font-weight:600">كاش</span>
                      </label>
                      <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem">
                        <input type="radio" name="items[{{ $idx }}][payment_status]" value="partial" onchange="toggleConvPaid({{ $idx }},this.value)">
                        <span style="color:#d97706;font-weight:600">جزئي</span>
                      </label>
                      <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem">
                        <input type="radio" name="items[{{ $idx }}][payment_status]" value="deferred" onchange="toggleConvPaid({{ $idx }},this.value)">
                        <span style="color:#dc2626;font-weight:600">أجل كامل</span>
                      </label>
                      <div id="conv-paid-{{ $idx }}" style="display:none;margin-top:4px">
                        <input type="number" name="items[{{ $idx }}][paid_amount]" placeholder="المبلغ المدفوع" min="0" step="0.01" style="width:120px;border-color:#d97706">
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
@push('scripts')
<script>
function toggleConvPaid(idx, val) {
  const el = document.getElementById('conv-paid-' + idx);
  el.style.display = (val === 'partial') ? 'block' : 'none';
}
// تطبيق نسبة الإشراف الافتراضية على كل خانات إشراف الأصناف
function applyConvSupervision(v) {
  document.querySelectorAll('input[name^="items"][name$="[supervision_pct]"]').forEach(i => { i.value = v; });
}
</script>
@endpush
@endsection
