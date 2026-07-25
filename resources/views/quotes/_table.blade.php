<div class="table-scroll">
  <table class="table">
    <thead>
      <tr>
        <th>رقم العرض / التاريخ</th>
        <th>العميل</th>
        <th>الإجمالي</th>
        <th>الحالة</th>
        <th style="text-align:left">الإجراءات</th>
      </tr>
    </thead>
    <tbody>
      @foreach($quotes as $q)
      <tr class="{{ $q->status === 'approved' ? 'approved' : '' }}">
        <td>
          <div style="font-weight:700; color:var(--ink)">{{ $q->ref }}</div>
          <div style="font-size:12px; color:var(--ink-3); margin-top:4px">{{ $q->date->format('Y-m-d') }}</div>
        </td>
        <td>
          <div style="font-weight:700; color:var(--ink)">{{ $q->client_name }}</div>
          <div style="font-size:12px; color:var(--ink-3); margin-top:4px">
            @if($q->area) {{ rtrim(rtrim($q->area, '0'), '.') }} م² @endif
            @if($q->phone) · {{ $q->phone }} @endif
          </div>
        </td>
        <td>
          <div style="font-weight:700; color:var(--ink)">{{ \App\Support\Money::format($q->total()) }} ج.م</div>
        </td>
        <td>
          <span class="tag {{ $q->statusTag() }}"><span class="dot"></span>{{ $q->statusAr() }}</span>
        </td>
        <td>
          <div style="display:flex; gap:8px; justify-content:flex-end; align-items:center;">
            @if($q->status === 'approved')
              @if($q->project_id)
                <a href="{{ route('projects.show', $q->project_id) }}" class="btn ghost sm">عرض المشروع</a>
              @else
                <a href="{{ route('quotes.convert', $q) }}" class="btn pos sm">تحويل إلى مشروع</a>
              @endif
            @elseif($q->status === 'sent')
              <form method="POST" action="{{ route('quotes.status', $q) }}">
                @csrf
                <input type="hidden" name="status" value="approved">
                <button class="btn pos sm">موافقة</button>
              </form>
              <form method="POST" action="{{ route('quotes.destroy', $q) }}" onsubmit="return confirm('هل تريد رفض هذا العرض؟ سيتم حذفه نهائياً.')">
                @csrf @method('DELETE')
                <button class="btn danger sm">رفض</button>
              </form>
            @else
              <form method="POST" action="{{ route('quotes.status', $q) }}">
                @csrf
                <input type="hidden" name="status" value="sent">
                <button class="btn pos sm">إرسال للعميل</button>
              </form>
              <a href="{{ route('quotes.edit', $q) }}" class="btn ghost sm">تعديل</a>
            @endif
            
            <a href="{{ route('quotes.show', $q) }}" class="btn sm" style="font-weight:700">طباعة</a>
            @if($q->whatsappLink())
              <a href="{{ $q->whatsappLink() }}" target="_blank" class="btn ghost sm" style="color:#25D366;border-color:#25D366">واتساب</a>
            @endif
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
