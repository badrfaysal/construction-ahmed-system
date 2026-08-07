@extends('layouts.app')
@section('title', 'فاتورة — ' . $invoice->invoice_number)
@section('page-title', 'معاينة فاتورة يدوية')

@section('content')
<div class="page-head no-print">
  <div>
    <h3>فاتورة — {{ $invoice->invoice_number }}</h3>
    <p>{{ $invoice->client_name }} · {{ $invoice->date->format('Y-m-d') }}</p>
  </div>
  <div class="btn-row">
    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-inline-end:10px;font-size:13px;font-weight:600">
      <input type="checkbox" id="toggle-tax-invoice" onchange="document.body.classList.toggle('tax-invoice-mode', this.checked)" {{ $invoice->tax_pct > 0 ? 'checked' : '' }}>
      فاتورة ضريبية
    </label>
    
    @if($invoice->status !== 'draft')
    @php
      $waText = urlencode("فاتورة رقم: {$invoice->invoice_number}\nالإجمالي: " . \App\Support\Money::format($invoice->total) . " ج.م");
      if ($invoice->client_phone) {
          $countryCode = $settings->whatsapp_country_code ?? '20';
          $waPhone = ltrim($invoice->client_phone, '0+');
          $waLink = "https://wa.me/{$countryCode}{$waPhone}?text={$waText}";
      } else {
          $waLink = "https://wa.me/?text={$waText}";
      }
    @endphp
    <button onclick="shareInvoiceImage('{{ $waLink }}')" class="btn" id="waBtn" style="background:#25d366;color:#fff;border-color:#25d366;">
      <i class="fab fa-whatsapp" style="font-size:16px;"></i>
      إرسال واتساب (صورة)
    </button>
    @endif

    <a href="{{ route('manual_invoices.create', ['copy_from_manual' => $invoice->id]) }}" class="btn ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-clipboard"/></svg>
      نسخ
    </a>
    <a href="{{ route('manual_invoices.edit', $invoice) }}" class="btn ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px"><use href="#i-tool"/></svg>
      تعديل
    </a>
    <button onclick="window.print()" class="btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      طباعة / PDF
    </button>
    <a href="{{ route('manual_invoices.index') }}" class="btn ghost">رجوع للسجل</a>
  </div>
</div>

<style>
  .tax-invoice-label { display: none; }
  body.tax-invoice-mode .tax-invoice-label { display: inline; }
  body.tax-invoice-mode .standard-label { display: none; }

  .tax-invoice-row { display: none; }
  body.tax-invoice-mode .tax-invoice-row { display: table-row; }
  body.tax-invoice-mode .standard-total-row { display: none; }
</style>

<div class="statement">
  {{-- Company header --}}
  <div class="st-head">
    <div class="co">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></div>
      <h2>{{ $settings->company_name }}</h2>
      <p>{{ $settings->company_tagline }} @if($settings->company_phone)· هاتف {{ $settings->company_phone }}@endif</p>
    </div>
    <div class="meta">
      <b>
        <span class="standard-label">فاتورة</span>
        <span class="tax-invoice-label">فاتورة ضريبية</span>
      </b><br>
      رقم: {{ $invoice->invoice_number }}<br>
      التاريخ: {{ $invoice->date->format('d/m/Y') }}
      @if($invoice->tax_number)
        <span class="tax-invoice-label"><br>الرقم الضريبي: {{ $invoice->tax_number }}</span>
      @endif
      @if($invoice->commercial_register)
        <span class="tax-invoice-label"><br>السجل التجاري: {{ $invoice->commercial_register }}</span>
      @endif
    </div>
  </div>

  <div class="st-body">
    {{-- Client info --}}
    <div class="st-client">
      <div><div class="l">العميل</div><div class="b">{{ $invoice->client_name }}</div></div>
      @if($invoice->client_phone)
        <div><div class="l">التلفون</div><div class="b">{{ $invoice->client_phone }}</div></div>
      @endif
      @if($invoice->client_address)
        <div><div class="l">العنوان</div><div class="b" style="font-size:13px;font-weight:600">{{ $invoice->client_address }}</div></div>
      @endif
    </div>

    {{-- Summary boxes --}}
    @php
      $grandTotal = $invoice->grandTotal();
      $remaining = $invoice->remaining();
    @endphp
    <div class="st-summary">
      <div class="st-box tot">
        <div class="l">إجمالي المستحق</div>
        <div class="v">
          <span class="standard-label">{{ \App\Support\Money::format($invoice->total) }} ج.م</span>
          <span class="tax-invoice-label">{{ \App\Support\Money::format($grandTotal) }} EGP</span>
        </div>
      </div>
      @if($invoice->discount > 0)
      <div class="st-box" style="background: #fee2e2; border-color: #fca5a5"><div class="l" style="color:#b91c1c">الخصم</div><div class="v" style="color:#b91c1c">{{ \App\Support\Money::format($invoice->discount) }} ج.م</div></div>
      @endif
      <div class="st-box paid"><div class="l">المدفوع</div><div class="v">{{ \App\Support\Money::format($invoice->paid_amount) }} ج.م</div></div>
      <div class="st-box due">
        <div class="l">المتبقي</div>
        <div class="v">
          <span class="standard-label">{{ \App\Support\Money::format($remaining) }} ج.م</span>
          <span class="tax-invoice-label">{{ \App\Support\Money::format($remaining) }} EGP</span>
        </div>
      </div>
    </div>

    {{-- Items table --}}
    <div class="st-sec">تفاصيل الأصناف</div>
    <table class="st-table">
      <thead>
        <tr>
          <th style="width:40px;">#</th>
          <th>التاريخ</th>
          <th>البيان</th>
          <th>الكمية</th>
          <th>الوحدة</th>
          <th>سعر الوحدة</th>
          <th>الإجمالي</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoice->items as $idx => $item)
          <tr>
            <td style="font-weight:600;color:var(--text-muted);text-align:center;">{{ $idx + 1 }}</td>
            <td>{{ $item->date ? $item->date->format('Y-m-d') : '—' }}</td>
            <td style="font-weight:600;">{{ $item->description }}</td>
            <td>{{ \App\Support\Money::format($item->qty, 1) }}</td>
            <td>{{ $item->unit ?: '—' }}</td>
            <td>{{ \App\Support\Money::format($item->unit_price) }}</td>
            <td><b>{{ \App\Support\Money::format($item->total) }}</b></td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Final summary --}}
    <div class="st-final">
      <div class="st-final-blocks">
        <table>
          {{-- Standard mode --}}
          <tr class="standard-total-row">
            <td class="muted" style="text-align:right">إجمالي الأصناف</td>
            <td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($invoice->subtotal) }} ج.م</td>
          </tr>
          @if($invoice->discount > 0)
          <tr class="standard-total-row">
            <td class="muted" style="text-align:right">الخصم</td>
            <td style="text-align:left;font-weight:700;color:#b91c1c">-{{ \App\Support\Money::format($invoice->discount) }} ج.م</td>
          </tr>
          <tr class="standard-total-row">
            <td class="muted" style="text-align:right">إجمالي المستحق</td>
            <td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($invoice->total) }} ج.م</td>
          </tr>
          @else
          <tr class="standard-total-row">
            <td class="muted" style="text-align:right">إجمالي المستحق</td>
            <td style="text-align:left;font-weight:700">{{ \App\Support\Money::format($invoice->total) }} ج.م</td>
          </tr>
          @endif

          {{-- Tax invoice mode --}}
          <tr class="tax-invoice-row">
            <td class="muted" style="text-align:right">المجموع</td>
            <td style="text-align:left;font-weight:700;color:var(--ink-2)">{{ \App\Support\Money::format($invoice->subtotal) }} EGP</td>
          </tr>
          @if($invoice->discount > 0)
          <tr class="tax-invoice-row">
            <td class="muted" style="text-align:right">الخصم</td>
            <td style="text-align:left;font-weight:700;color:#b91c1c">-{{ \App\Support\Money::format($invoice->discount) }} EGP</td>
          </tr>
          <tr class="tax-invoice-row">
            <td class="muted" style="text-align:right">المجموع بعد الخصم</td>
            <td style="text-align:left;font-weight:700;color:var(--ink-2)">{{ \App\Support\Money::format($invoice->total) }} EGP</td>
          </tr>
          @endif
          <tr class="tax-invoice-row">
            <td class="muted" style="text-align:right">الضريبة ({{ (float) $invoice->tax_pct }}%)</td>
            <td style="text-align:left;font-weight:700;color:var(--pos)">+{{ \App\Support\Money::format($invoice->tax_amount) }} EGP</td>
          </tr>
          <tr class="tax-invoice-row" style="background:#005c97;color:#fff">
            <td style="font-weight:700;color:#fff;font-size:14.5px">الإجمالي النهائي</td>
            <td style="text-align:left;font-weight:700;color:#fff;font-size:14.5px">{{ \App\Support\Money::format($grandTotal) }} EGP</td>
          </tr>
        </table>

        <table>
          <tr>
            <td class="muted" style="text-align:right">المدفوع</td>
            <td style="text-align:left;font-weight:700;color:var(--pos)">{{ \App\Support\Money::format($invoice->paid_amount) }} ج.م</td>
          </tr>
          <tr class="big">
            <td style="text-align:right;font-size:16px;font-weight:700">المتبقي المطلوب</td>
            <td style="text-align:left;font-size:20px;font-weight:800">
              <span class="standard-label">{{ \App\Support\Money::format($remaining) }} ج.م</span>
              <span class="tax-invoice-label">{{ \App\Support\Money::format($remaining) }} EGP</span>
            </td>
          </tr>
        </table>
      </div>
    </div>

    {{-- Notes --}}
    @if($invoice->notes)
    <div style="margin-top:20px;padding:16px;background:var(--bg);border-radius:10px;border:1px solid var(--border);">
      <div style="font-weight:700;font-size:13px;color:var(--text-muted);margin-bottom:6px;">الشروط والأحكام / ملاحظات الفاتورة</div>
      <div style="font-size:14px;font-weight:600;white-space:pre-wrap;">{{ $invoice->notes }}</div>
    </div>
    @endif
  </div>

  <div class="st-foot">
    <span>
      <span class="standard-label">فاتورة يدوية</span>
      <span class="tax-invoice-label">فاتورة ضريبية</span>
      — {{ $invoice->invoice_number }} · {{ $invoice->date->format('d/m/Y') }}
    </span>
    <span>توقيع الشركة: ____________</span>
  </div>
</div>

@if($invoice->status === 'draft')
<div class="no-print" style="margin-top:20px;text-align:center;">
  <div style="background:#fef3c7;border:1px solid #fbbf24;padding:12px 20px;border-radius:10px;display:inline-block;">
    <span style="font-weight:700;color:#92400e;">⚠️ هذه الفاتورة مسودة — لم يتم اعتمادها بعد</span>
  </div>
</div>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
async function shareInvoiceImage(waLink) {
  var btn = document.getElementById('waBtn');
  var originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:16px;"></i> جاري التجهيز...';
  btn.disabled = true;

  try {
    var el = document.querySelector('.statement');
    var originalMargin = el.style.margin;
    var originalMaxWidth = el.style.maxWidth;
    el.style.margin = '0';
    el.style.maxWidth = '1000px';

    await new Promise(function(r) { setTimeout(r, 100); });

    var canvas = await html2canvas(el, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
      logging: false
    });

    el.style.margin = originalMargin;
    el.style.maxWidth = originalMaxWidth;

    var blob = await new Promise(function(resolve) {
      canvas.toBlob(function(b) { resolve(b); }, 'image/png');
    });

    if (!blob) {
      alert('فشل إنشاء الصورة.');
      btn.innerHTML = originalText;
      btn.disabled = false;
      return;
    }

    var file = new File([blob], 'invoice.png', { type: 'image/png' });

    // 1) Mobile: Native Web Share
    if (navigator.canShare && navigator.canShare({ files: [file] })) {
      try {
        await navigator.share({ files: [file], title: 'فاتورة', text: 'مرفق صورة الفاتورة' });
        btn.innerHTML = originalText;
        btn.disabled = false;
        return;
      } catch (shareErr) {
        console.log('Native share cancelled or failed', shareErr);
      }
    }

    // 2) Desktop HTTPS: Clipboard
    if (typeof window.ClipboardItem !== 'undefined') {
      try {
        var item = new ClipboardItem({ 'image/png': blob });
        await navigator.clipboard.write([item]);
        alert('✅ تم نسخ الصورة!\n\nاضغط Ctrl+V (لصق) داخل محادثة الواتساب لإرسالها.');
        window.open(waLink, '_blank');
        btn.innerHTML = originalText;
        btn.disabled = false;
        return;
      } catch (clipErr) {
        console.log('Clipboard failed', clipErr);
      }
    }

    // 3) Fallback: Download file
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'فاتورة_{{ $invoice->invoice_number }}.png';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    alert('تم تحميل صورة الفاتورة. يمكنك إرسالها من الملفات المحملة.');
    window.open(waLink, '_blank');
    btn.innerHTML = originalText;
    btn.disabled = false;

  } catch (error) {
    console.error('shareInvoiceImage error:', error);
    alert('حدث خطأ أثناء تجهيز الصورة: ' + error.message);
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}
</script>
<script>
  // Auto-enable tax invoice mode if there is tax applied
  document.addEventListener("DOMContentLoaded", function() {
    @if($invoice->tax_pct > 0)
      const checkbox = document.getElementById('toggle-tax-invoice');
      if (checkbox) {
        checkbox.checked = true;
        document.body.classList.add('tax-invoice-mode');
      }
    @endif
  });
</script>
@endsection
