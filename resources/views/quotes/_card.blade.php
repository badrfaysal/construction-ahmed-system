{{-- Single quote card — shared by quotes/index.blade.php and quotes/approved.blade.php --}}
<div class="qcard {{ $q->status === 'approved' ? 'approved' : '' }}">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px">
    <span class="tag {{ $q->statusTag() }}"><span class="dot"></span>{{ $q->statusAr() }}</span>
    <div class="qref">{{ $q->ref }} · {{ $q->date->format('Y-m-d') }} @if($q->area)· {{ rtrim(rtrim($q->area, '0'), '.') }} م²@endif</div>
  </div>

  <div class="qname" style="margin-top:11px">{{ $q->client_name }}</div>

  @if($q->address)
    <div class="qaddr">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pin"/></svg>
      {{ $q->address }}
    </div>
  @endif

  @if($q->note)
    <div class="qnote">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bell"/></svg>
      {{ $q->note }}
    </div>
  @endif

  @if($q->bands->count())
    <div class="qbands">
      @foreach($q->bands as $band)
        <div class="qband">
          <span class="n">{{ $band->name }}</span>
          <span class="v">{{ \App\Support\Money::format($band->price) }} ج.م</span>
        </div>
        @if($band->items->count())
          <div style="margin:2px 0 6px;font-size:11.5px;color:var(--ink-2)">
            @foreach($band->items as $item)
              <div>{{ $item->name }} — {{ rtrim(rtrim($item->qty, '0'), '.') }} × {{ \App\Support\Money::format($item->unit_price) }}</div>
            @endforeach
          </div>
        @endif
      @endforeach
    </div>
  @endif

  <div class="qtot">
    <span style="font-size:12.5px;color:var(--ink-2);font-weight:600">إجمالي عرض السعر</span>
    <span class="v">{{ \App\Support\Money::format($q->total()) }} ج.م</span>
  </div>

  <div class="qfooter">
    @if($q->status === 'approved')
      <span class="tag green" style="padding:8px 14px;font-size:13px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check-circle"/></svg>
        العرض المعتمدة
      </span>
      @if($q->project_id)
        <a href="{{ route('projects.show', $q->project_id) }}" class="btn ghost sm">عرض المشروع</a>
      @else
        <a href="{{ route('quotes.convert', $q) }}" class="btn pos sm">تحويل إلى مشروع</a>
      @endif
    @elseif($q->status === 'sent')
      <form method="POST" action="{{ route('quotes.destroy', $q) }}" onsubmit="return confirm('هل تريد رفض هذا العرض؟ سيتم حذفه نهائياً.')">
        @csrf @method('DELETE')
        <button class="btn danger sm">رفض</button>
      </form>
      <form method="POST" action="{{ route('quotes.status', $q) }}">
        @csrf
        <input type="hidden" name="status" value="approved">
        <button class="btn pos sm">موافقة</button>
      </form>
    @else
      <a href="{{ route('quotes.edit', $q) }}" class="btn ghost sm">تعديل</a>
      <form method="POST" action="{{ route('quotes.status', $q) }}">
        @csrf
        <input type="hidden" name="status" value="sent">
        <button class="btn sm">إرسال للعميل</button>
      </form>
    @endif
    @if($q->whatsappLink())
      <a href="{{ $q->whatsappLink() }}" target="_blank" class="btn ghost sm">إرسال واتساب</a>
    @endif
    <a href="{{ route('quotes.show', $q) }}" class="btn ghost sm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
      طباعه وعرض
    </a>
  </div>
</div>
