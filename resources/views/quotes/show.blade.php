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
    <button onclick="window.print()" class="btn ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
      طباعة
    </button>
    <a href="{{ route('quotes.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

{{-- Printable quote document — same branded layout as the client statement --}}
<div class="statement">
  <div class="st-head">
    <div class="co">
      <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg></div>
      <h2>{{ $settings->company_name }}</h2>
      <p>{{ $settings->company_tagline }} @if($settings->company_phone)· هاتف {{ $settings->company_phone }}@endif @if($settings->company_registration)· سجل تجاري {{ $settings->company_registration }}@endif</p>
    </div>
    <div class="meta">
      <b>عرض سعر</b><br>
      رقم: {{ $quote->ref }}<br>
      التاريخ: {{ $quote->date->format('d/m/Y') }}
    </div>
  </div>

  <div class="st-body">
    <div class="st-client">
      <div><div class="l">العميل</div><div class="b">{{ $quote->client_name }}</div></div>
      @if($quote->phone)<div><div class="l">الهاتف</div><div class="b">{{ $quote->phone }}</div></div>@endif
      @if($quote->area)<div><div class="l">المساحة</div><div class="b">{{ rtrim(rtrim($quote->area, '0'), '.') }} م²</div></div>@endif
      @if($quote->address)<div><div class="l">العنوان</div><div class="b" style="font-size:13px;font-weight:600">{{ $quote->address }}</div></div>@endif
    </div>

    @if($quote->note)
      <div class="qnote" style="margin-bottom:18px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bell"/></svg>
        {{ $quote->note }}
      </div>
    @endif

    <div class="st-sec">البنود والأصناف بالتفصيل</div>
    <table class="st-table">
      <thead><tr><th>الصنف</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead>
      <tbody>
        @foreach($quote->bands as $band)
          <tr class="grp">
            <td colspan="4">بند: {{ $band->name }} <span class="bt">إجمالي البند: {{ number_format($band->price) }} ج.م</span></td>
          </tr>
          @forelse($band->items as $item)
            <tr>
              <td>{{ $item->name }}</td>
              <td>{{ rtrim(rtrim($item->qty, '0'), '.') }}</td>
              <td>{{ number_format($item->unit_price) }}</td>
              <td><b>{{ number_format($item->total()) }}</b></td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="muted">سعر مقطوع — بدون تفصيل أصناف</td>
              <td><b>{{ number_format($band->price) }}</b></td>
            </tr>
          @endforelse
          <tr class="sub">
            <td colspan="3" style="text-align:left">إجمالي بند {{ $band->name }}</td>
            <td>{{ number_format($band->price) }} ج.م</td>
          </tr>
        @endforeach
        <tr class="sub" style="background:var(--accent-soft)">
          <td colspan="3" style="text-align:left;color:var(--accent-ink)">إجمالي عرض السعر</td>
          <td style="color:var(--accent-ink)">{{ number_format($quote->total()) }} ج.م</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="st-foot">
    <span>عرض سعر مبدئي — قابل للتعديل حتى الموافقة النهائية.</span>
    <span>توقيع الشركة: ____________</span>
  </div>
</div>

<div class="btn-row no-print" style="margin-top:16px">
  <form method="POST" action="{{ route('quotes.destroy', $quote) }}" onsubmit="return confirm('حذف هذا العرض؟')">
    @csrf @method('DELETE')
    <button class="btn danger">حذف العرض</button>
  </form>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.getElementById('share-image-btn')?.addEventListener('click', async function () {
  const btn = this;
  const original = btn.innerHTML;
  btn.disabled = true;
  btn.textContent = 'جاري تجهيز الصورة...';

  try {
    const canvas = await html2canvas(document.querySelector('.statement'), { scale: 2, backgroundColor: '#ffffff' });

    canvas.toBlob(async (blob) => {
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
    }, 'image/png');
  } catch (e) {
    alert('حصل خطأ أثناء تجهيز الصورة.');
    btn.disabled = false;
    btn.innerHTML = original;
  }
});
</script>
@endpush
@endsection
