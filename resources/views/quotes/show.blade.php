@extends('layouts.app')
@section('title', $quote->ref)
@section('page-title', 'عرض سعر: ' . $quote->ref)

@section('content')
<div class="page-head no-print">
  <div>
    <h3>{{ $quote->ref }} — {{ $quote->client_name }}</h3>
    <p>{{ $quote->date->format('d/m/Y') }} @if($quote->address)— {{ $quote->address }}@endif</p>
  </div>
  <div class="btn-row">
    <span class="tag {{ $quote->statusTag() }}" style="font-size:13px;padding:6px 14px">{{ $quote->statusAr() }}</span>
    @if($quote->status === 'draft')
      <form method="POST" action="{{ route('quotes.status', $quote) }}">
        @csrf
        <input type="hidden" name="status" value="sent">
        <button class="btn">إرسال للعميل</button>
      </form>
    @elseif($quote->status === 'sent')
      <form method="POST" action="{{ route('quotes.status', $quote) }}">
        @csrf
        <input type="hidden" name="status" value="approved">
        <button class="btn pos">موافقة</button>
      </form>
    @endif
    @if($quote->status === 'draft')
      <a href="{{ route('quotes.edit', $quote) }}" class="btn ghost">تعديل</a>
    @endif
    <button type="button" id="share-image-btn" class="btn ghost" data-ref="{{ $quote->ref }}" data-text="عرض سعر رقم {{ $quote->ref }} — {{ $quote->client_name }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-send"/></svg>
      مشاركة كصورة
    </button>
    @if($quote->status === 'approved')
      @if($quote->project_id)
        <a href="{{ route('projects.show', $quote->project_id) }}" class="btn ghost">عرض المشروع</a>
      @else
        <a href="{{ route('quotes.convert', $quote) }}" class="btn pos">تحويل إلى مشروع</a>
      @endif
    @endif
    <button onclick="window.print()" class="btn" style="font-weight:700">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
      طباعة عرض السعر
    </button>
    <a href="{{ route('quotes.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

<style>
/* Modern Elegant Quote Styles */
.quote-doc {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.08);
  margin: 30px auto;
  max-width: 900px;
  overflow: hidden;
  font-family: 'Tajawal', system-ui, sans-serif;
  color: #222;
  position: relative;
}
.quote-doc-inner {
  padding: 40px;
}
.quote-top-bar {
  height: 10px;
  background: var(--brand, #059669);
  width: 100%;
}
.quote-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
  border-bottom: 2px solid #f1f5f9;
  padding-bottom: 20px;
}
.quote-title {
  color: var(--brand, #059669);
  font-size: 34px;
  font-weight: 800;
  margin: 0 0 5px;
  letter-spacing: -0.5px;
}
.quote-ref {
  color: #64748b;
  font-size: 15px;
  letter-spacing: 1px;
  font-weight: 600;
}
.quote-logo {
  text-align: left;
}
.quote-logo h2 {
  margin: 0;
  font-size: 26px;
  color: #0f172a;
  font-weight: 800;
}
.quote-logo p {
  margin: 4px 0 0;
  font-size: 13px;
  color: #64748b;
}
.quote-meta-grid {
  display: flex;
  justify-content: space-between;
  margin-bottom: 40px;
  gap: 30px;
}
.quote-meta-col {
  flex: 1;
}
.quote-meta-title {
  font-size: 12px;
  color: #94a3b8;
  text-transform: uppercase;
  margin-bottom: 10px;
  font-weight: 700;
  letter-spacing: 0.5px;
}
.quote-client-box {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-top: 4px;
}
.quote-client-name {
  font-size: 19px;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 6px;
}
.quote-client-details {
  font-size: 13.5px;
  color: #475569;
  line-height: 1.6;
}
.quote-qr {
  width: 86px;
  height: 86px;
  background: #fff;
  padding: 6px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
  flex-shrink: 0;
}
.quote-dates {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 10px;
  font-size: 14px;
}
.quote-dates span.lbl { color: #64748b; display: block; font-size: 12px; margin-bottom: 4px;}
.quote-dates span.val { font-weight: 700; color: #1e293b; }

.quote-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-bottom: 30px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}
.quote-table th {
  background: #0f172a;
  color: #fff;
  padding: 14px 16px;
  font-size: 13px;
  text-align: right;
  font-weight: 600;
}
.quote-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
}
.quote-table tr.band-row td {
  background: #f8fafc;
  font-weight: 800;
  color: #0f172a;
  border-bottom: 2px solid #e2e8f0;
}
.quote-table tr:last-child td { border-bottom: none; }
.quote-table .num { text-align: left; }

.quote-footer-grid {
  display: flex;
  gap: 30px;
  margin-bottom: 40px;
}
.quote-terms {
  flex: 1.5;
  font-size: 12.5px;
  color: #475569;
  line-height: 1.8;
}
.quote-terms h4 { margin: 0 0 12px; color: #0f172a; font-size: 15px; font-weight: 700; }
.quote-summary {
  flex: 1;
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}
.quote-summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  font-size: 14px;
  border-bottom: 1px solid #f1f5f9;
  font-weight: 600;
  color: #475569;
}
.quote-summary-row span {
  white-space: nowrap;
}
.quote-summary-row:last-child { border-bottom: none; }
.quote-summary-total {
  background: #0284c7; /* Modern blue */
  color: #fff;
  font-size: 18px;
  font-weight: 800;
  padding: 18px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.quote-summary-total span {
  white-space: nowrap;
}

.quote-barcode-wrap {
  text-align: center;
  margin-bottom: 30px;
}
.barcode-text {
  font-size: 13px;
  color: #64748b;
  letter-spacing: 3px;
  margin-top: 6px;
  font-weight: 700;
}

.quote-footer-icons {
  display: flex;
  justify-content: center;
  gap: 24px;
  border-top: 2px solid #f1f5f9;
  padding-top: 24px;
  font-size: 12px;
  color: #64748b;
  font-weight: 600;
}
.quote-footer-icons div {
  display: flex;
  align-items: center;
  gap: 8px;
}
.quote-footer-icons svg {
  width: 16px; height: 16px; color: #0284c7;
}

@media print {
  body { background: #fff !important; }
  .quote-doc { box-shadow: none !important; margin: 0; padding: 0; max-width: 100%; border-radius: 0; }
  .quote-doc-inner { padding: 0; }
  .btn-row, .page-head { display: none !important; }
  .quote-table th { background: #0f172a !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .quote-table tr.band-row td { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .quote-summary-total { background: #0284c7 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .quote-top-bar { background: var(--brand, #059669) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .quote-client-box { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="quote-doc" id="printable-quote">
  <div class="quote-top-bar"></div>
  <div class="quote-doc-inner">
    
    <div class="quote-header">
      <div>
        <h1 class="quote-title">عرض سعر</h1>
        <div class="quote-ref">{{ $quote->ref }}</div>
        @if($quote->area)
          <div style="margin-top: 12px; font-size: 12px; color: #8b5cf6; background: #f5f3ff; padding: 6px 12px; border-radius: 20px; display: inline-block; font-weight: 700;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="vertical-align:-3px; margin-inline-end: 4px;"><use href="#i-grid"/></svg>
            مساحة المشروع: {{ rtrim(rtrim($quote->area, '0'), '.') }} م²
          </div>
        @endif
      </div>
      <div class="quote-logo">
        <h2>{{ $settings->company_name }}</h2>
        <p>{{ $settings->company_tagline }}</p>
      </div>
    </div>

    <div class="quote-meta-grid">
      <div class="quote-meta-col">
        <div class="quote-meta-title">مقدم إلى</div>
        <div class="quote-client-box">
          <div class="quote-qr">
            <!-- QR code linked to the quote view itself so client can view/download it -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('quotes.show', $quote)) }}&margin=0" style="width:100%; height:100%;" alt="QR Code">
          </div>
          <div style="flex: 1; margin-inline-start: 16px;">
            <div class="quote-client-name">{{ $quote->client_name }}</div>
            <div class="quote-client-details">
              @if($quote->phone)
                <span dir="ltr">{{ $quote->phone }}</span> <br>
              @endif
              @if($quote->address)
                {{ $quote->address }}
              @endif
            </div>
          </div>
        </div>
      </div>
      
      <div class="quote-meta-col" style="flex: 0.8; padding-inline-start: 30px;">
        <div class="quote-meta-title">تفاصيل العرض</div>
        <div class="quote-dates">
          <div><span class="lbl">تاريخ الإصدار</span><span class="val">{{ $quote->date->format('d/m/Y') }}</span></div>
          <div><span class="lbl">العملة</span><span class="val">جنيه مصري (EGP)</span></div>
        </div>
      </div>
    </div>

    @if($quote->note)
      <div style="background: #fffbeb; border: 1px solid #fef08a; padding: 14px 18px; border-radius: 8px; margin-bottom: 30px; font-size: 14px; color: #92400e; font-weight: 600;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="vertical-align:-3px; margin-inline-end: 4px;"><use href="#i-bell"/></svg>
        {{ $quote->note }}
      </div>
    @endif

    <table class="quote-table">
      <thead>
        <tr>
          <th style="width:40px; text-align:center;">#</th>
          <th>البيان / الوصف</th>
          <th style="text-align:center;">الكمية</th>
          <th class="num">سعر الوحدة</th>
          <th class="num">الإجمالي</th>
        </tr>
      </thead>
      <tbody>
        @php $counter = 1; @endphp
        @foreach($quote->bands as $band)
          <tr class="band-row">
            <td colspan="5">بند: {{ $band->name }}</td>
          </tr>
          @forelse($band->items as $item)
            <tr>
              <td style="text-align:center; color:#94a3b8; font-weight: 600;">{{ $counter++ }}</td>
              <td style="font-weight: 700; color: #334155;">{{ $item->name }}</td>
              <td style="text-align:center; font-weight: 600;">{{ rtrim(rtrim($item->qty, '0'), '.') }}</td>
              <td class="num">{{ \App\Support\Money::format($item->unit_price) }}</td>
              <td class="num" style="font-weight:800; color: #0f172a;">{{ \App\Support\Money::format($item->total()) }}</td>
            </tr>
          @empty
            @if($band->workers->isEmpty())
              <tr>
                <td style="text-align:center; color:#94a3b8; font-weight: 600;">{{ $counter++ }}</td>
                <td style="color:#64748b;">سعر مقطوع — بدون تفصيل أصناف</td>
                <td style="text-align:center; font-weight: 600;">1</td>
                <td class="num">{{ \App\Support\Money::format($band->price) }}</td>
                <td class="num" style="font-weight:800; color: #0f172a;">{{ \App\Support\Money::format($band->price) }}</td>
              </tr>
            @endif
          @endforelse
          
          @foreach($band->workers as $worker)
            <tr>
              <td style="text-align:center; color:#94a3b8; font-weight: 600;">{{ $counter++ }}</td>
              <td style="font-weight: 700; color: #334155;">{{ $worker->name }} <span style="color:#94a3b8; font-size:12.5px; font-weight: 600;">(مصنعية@if($worker->specialty) — {{ $worker->specialty }}@endif)</span></td>
              <td style="text-align:center; font-weight: 600;">{{ $worker->contract_qty ? rtrim(rtrim($worker->contract_qty, '0'), '.') : '1' }}</td>
              <td class="num">{{ $worker->contract_unit_rate ? \App\Support\Money::format($worker->contract_unit_rate) : \App\Support\Money::format($worker->clientPrice()) }}</td>
              <td class="num" style="font-weight:800; color: #0f172a;">{{ \App\Support\Money::format($worker->clientPrice()) }}</td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>

    <div class="quote-footer-grid">
      <div class="quote-terms">
        <h4>الشروط والأحكام</h4>
        @if($quote->terms)
          <div style="white-space: pre-wrap; margin: 0; line-height: 1.8;">{{ $quote->terms }}</div>
        @else
          <ul style="padding-inline-start: 18px; margin: 0;">
            <li>هذا العرض ساري المفعول لمدة 15 يوماً من تاريخ إصداره.</li>
            <li>الأسعار لا تشمل ضريبة القيمة المضافة ما لم يُذكر خلاف ذلك.</li>
            <li>التنفيذ يبدأ خلال 3 إلى 5 أيام عمل من تاريخ تأكيد الطلب واستلام الدفعة.</li>
            <li>شروط الدفع: دفعة مقدمة 50%، و50% عند الاستلام (أو حسب الاتفاق المعلق).</li>
          </ul>
        @endif
      </div>
      <div class="quote-summary">
        <div class="quote-summary-row">
          <span>المجموع الفرعي</span>
          <span class="num">{{ \App\Support\Money::format($quote->total()) }}</span>
        </div>
        <div class="quote-summary-row">
          <span>الخصم (0%)</span>
          <span class="num" style="color: #ef4444; direction: ltr; display: inline-block;">- 0.00</span>
        </div>
        <div class="quote-summary-row">
          <span>الضريبة (0%)</span>
          <span class="num" style="color: #10b981; direction: ltr; display: inline-block;">+ 0.00</span>
        </div>
        <div class="quote-summary-total">
          <span>الإجمالي النهائي</span>
          <span>{{ \App\Support\Money::format($quote->total()) }} <small style="font-size: 13px; font-weight: normal; opacity: 0.9;">EGP</small></span>
        </div>
      </div>
    </div>

    <div class="quote-barcode-wrap">
      <div style="display:inline-block;">
        <svg viewBox="0 0 300 45" width="300" height="45" style="margin-bottom: 2px;">
          <!-- Simulated wider barcode bars -->
          <rect x="0" y="0" width="6" height="45" fill="#0f172a"/><rect x="9" y="0" width="3" height="45" fill="#0f172a"/>
          <rect x="18" y="0" width="9" height="45" fill="#0f172a"/><rect x="33" y="0" width="3" height="45" fill="#0f172a"/>
          <rect x="42" y="0" width="6" height="45" fill="#0f172a"/><rect x="54" y="0" width="9" height="45" fill="#0f172a"/>
          <rect x="69" y="0" width="3" height="45" fill="#0f172a"/><rect x="78" y="0" width="12" height="45" fill="#0f172a"/>
          <rect x="96" y="0" width="3" height="45" fill="#0f172a"/><rect x="105" y="0" width="6" height="45" fill="#0f172a"/>
          <rect x="117" y="0" width="3" height="45" fill="#0f172a"/><rect x="126" y="0" width="9" height="45" fill="#0f172a"/>
          <rect x="141" y="0" width="6" height="45" fill="#0f172a"/><rect x="153" y="0" width="3" height="45" fill="#0f172a"/>
          <rect x="162" y="0" width="12" height="45" fill="#0f172a"/><rect x="180" y="0" width="3" height="45" fill="#0f172a"/>
          <rect x="189" y="0" width="6" height="45" fill="#0f172a"/><rect x="201" y="0" width="9" height="45" fill="#0f172a"/>
          <rect x="216" y="0" width="3" height="45" fill="#0f172a"/><rect x="225" y="0" width="6" height="45" fill="#0f172a"/>
          <rect x="237" y="0" width="12" height="45" fill="#0f172a"/><rect x="255" y="0" width="3" height="45" fill="#0f172a"/>
          <rect x="264" y="0" width="6" height="45" fill="#0f172a"/><rect x="276" y="0" width="9" height="45" fill="#0f172a"/>
          <rect x="291" y="0" width="9" height="45" fill="#0f172a"/>
        </svg>
        <div class="barcode-text">{{ $quote->ref }}</div>
      </div>
    </div>

    <div class="quote-footer-icons">
      @if($settings->company_phone)
        <div><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg> {{ $settings->company_phone }}</div>
      @endif
      <div><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg> شركة {{ $settings->company_name }}</div>
    </div>

  </div>
</div>

<div class="btn-row no-print" style="margin-top:16px">
  <form method="POST" action="{{ route('quotes.destroy', $quote) }}" onsubmit="return confirm('حذف هذا العرض؟')">
    @csrf @method('DELETE')
    <button class="btn danger">حذف العرض</button>
  </form>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
<script>
document.getElementById('share-image-btn')?.addEventListener('click', async function () {
  const btn = this;
  const original = btn.innerHTML;
  btn.disabled = true;
  btn.textContent = 'جاري تجهيز الصورة...';

  try {
    // حل مشكلة قص الصورة في العربي (RTL bug in html-to-image)
    const originalNode = document.querySelector('.quote-doc');
    const node = originalNode.cloneNode(true);
    document.body.appendChild(node);
    node.style.position = 'absolute';
    node.style.left = '0';
    node.style.top = '0';
    node.style.margin = '0';
    node.style.zIndex = '-9999';

    const blob = await htmlToImage.toBlob(node, { pixelRatio: 2, backgroundColor: '#ffffff' });
    node.remove();

    const file = new File([blob], 'عرض-سعر-' + btn.dataset.ref + '.png', { type: 'image/png' });

    if (navigator.canShare && navigator.canShare({ files: [file] })) {
      try {
        await navigator.share({ files: [file], title: btn.dataset.text, text: btn.dataset.text });
      } catch (e) {
        // المستخدم لغى المشاركة — مش خطأ
      }
    } else {
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = file.name;
      a.click();
      URL.revokeObjectURL(url);
      alert('المتصفح ده مايدعمش المشاركة المباشرة — اتنزلت الصورة، افتح واتساب وارفقها يدوي.');
    }

    btn.disabled = false;
    btn.innerHTML = original;
  } catch (e) {
    alert('حصل خطأ أثناء تجهيز الصورة.');
    btn.disabled = false;
    btn.innerHTML = original;
  }
});
</script>
@endpush
@endsection
