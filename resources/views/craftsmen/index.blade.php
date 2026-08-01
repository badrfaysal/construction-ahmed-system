@extends('layouts.app')
@section('title', 'الصنايعية ومستحقاتهم')
@section('page-title', 'الصنايعية ومستحقاتهم')

@section('content')
<div class="page-head">
  <div><h3>الصنايعية ومستحقاتهم</h3><p>كل صنايعي مجمّع عبر كل المشاريع — المتعاقد عليه، المدفوع، والمتبقي المستحق دلوقتي</p></div>
</div>

<div class="grid cols-3" style="margin-bottom:20px">
  <div class="card stat">
    <div class="top"><span class="label">عدد الصنايعية</span><span class="ic ic-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg></span></div>
    <div class="val tnum">{{ $craftsmen->count() }}</div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المدفوع</span><span class="ic ic-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cash"/></svg></span></div>
    <div class="val tnum" style="color:var(--pos)">{{ \App\Support\Money::format($totalPaid) }} <small>ج.م</small></div>
  </div>
  <div class="card stat">
    <div class="top"><span class="label">إجمالي المستحق للصنايعية</span><span class="ic ic-amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg></span></div>
    <div class="val tnum" style="color:{{ $totalRemaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($totalRemaining) }} <small>ج.م</small></div>
  </div>
</div>
<div class="tabs" style="margin-bottom: 20px; display: flex; gap: 10px; border-bottom: 1px solid var(--line); padding-bottom: 10px;">
  <a href="{{ request()->fullUrlWithQuery(['status' => 'due']) }}" class="btn {{ request('status', 'due') == 'due' ? 'primary' : 'ghost' }}" style="border-radius: 20px; padding: 6px 16px;">المستحق (عليه باقي)</a>
  <a href="{{ request()->fullUrlWithQuery(['status' => 'paid']) }}" class="btn {{ request('status') == 'paid' ? 'primary' : 'ghost' }}" style="border-radius: 20px; padding: 6px 16px;">المسدد (خالص)</a>
</div>

<form method="GET" class="filter-bar">
  <input type="hidden" name="status" value="{{ request('status', 'due') }}">
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      الشقة / المشروع
    </label>
    <div class="f-select-wrap">
      <select name="project_id" class="f-select" onchange="this.form.submit()">
        <option value="">كل المشاريع</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
    </div>
  </div>
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-tool"/></svg>
      التخصص
    </label>
    <div class="f-select-wrap">
      <select name="specialty" class="f-select" onchange="this.form.submit()">
        <option value="">كل التخصصات</option>
        @foreach($specialties as $sp)
          <option value="{{ $sp }}" {{ request('specialty') === $sp ? 'selected' : '' }}>{{ $sp }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
    </div>
  </div>
  @include('partials._sort-select', ['options' => [
    'newest'          => 'الأحدث إضافة',
    'remaining_desc'  => 'الأعلى مستحقًا',
    'paid_desc'       => 'الأعلى مدفوعًا',
    'contracted_desc' => 'الأعلى تعاقدًا',
    'projects_desc'   => 'الأكثر مشاريع',
    'rating_desc'     => 'الأعلى تقييمًا',
    'rating_asc'      => 'الأقل تقييمًا',
    'name'            => 'أبجديًا',
  ]])
  @if(request()->hasAny(['project_id','specialty']))
    <div class="f-actions">
      <a href="{{ route('craftsmen.index') }}" class="btn ghost sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
        مسح الفلتر
      </a>
    </div>
  @endif
</form>

@forelse($craftsmen as $c)
  @php $modalId = 'modal-craftsman-' . md5($c->name); @endphp
  <div class="table-card craftsman-row" style="margin-bottom:12px; cursor:pointer; transition:transform 0.2s, box-shadow 0.2s; border: 1px solid var(--line);" onclick="document.getElementById('{{ $modalId }}').classList.add('open')" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow=''; this.style.transform='';">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;padding:16px 20px;">
      
      <div style="flex:1; display:flex; align-items:center; gap:16px; min-width:250px;">
        <div style="width:48px;height:48px;border-radius:50%;background:var(--bg-main);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:var(--brand);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><use href="#i-users"/></svg>
        </div>
        <div>
          <h4 style="margin:0; font-size:1.1rem; color:var(--ink);">{{ $c->name }}</h4>
          <div class="muted" style="font-size:13px; margin-top:4px; display:flex; gap:12px;">
            <span><i class="fa fa-phone" style="font-size:10px"></i> @if($c->phones->count()){{ $c->phones->first() }}@else — @endif</span>
            <span><i class="fa fa-briefcase" style="font-size:10px"></i> @if($c->specialties->count()){{ $c->specialties->join('، ') }}@else عام @endif</span>
            <span><i class="fa fa-building" style="font-size:10px"></i> {{ $c->projects }} مشروع</span>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:30px;align-items:center;">
        <div style="text-align:center;">
          <div class="muted" style="font-size:11px;">إجمالي المستحق (ليه كام)</div>
          <div class="tnum" style="font-weight:700;">{{ \App\Support\Money::format($c->contracted) }}</div>
        </div>
        <div style="text-align:center;">
          <div class="muted" style="font-size:11px;">المدفوع (دفعنا كام)</div>
          <div class="tnum" style="font-weight:700;color:var(--pos);">{{ \App\Support\Money::format($c->paid) }}</div>
        </div>
        <div style="text-align:center; background:var(--bg2); padding:6px 12px; border-radius:8px;">
          <div class="muted" style="font-size:11px;">المتبقي</div>
          <div class="tnum" style="font-size:1.1rem; font-weight:800; color:{{ $c->remaining > 0 ? 'var(--neg)' : 'var(--pos)' }};">{{ \App\Support\Money::format($c->remaining) }}</div>
        </div>
      </div>

      <div style="display:flex;gap:8px;align-items:center; margin-right:30px;" onclick="event.stopPropagation()">
        @if($c->remaining > 0)
          <button type="button" class="btn primary sm" style="background:#10b981; border-color:#10b981; font-weight:bold" onclick="openBulkPaymentModal('{{ addslashes($c->name) }}', {{ $c->remaining }}, false)">سداد مجمع</button>
          <button type="button" class="btn ghost sm" style="font-weight:bold; color:#10b981; border:1px solid #10b981;" onclick="openBulkPaymentModal('{{ addslashes($c->name) }}', {{ $c->remaining }}, true)">سداد جزئي</button>
        @endif
        <a href="{{ route('craftsmen.statement', $c->name) }}" class="btn ghost sm" style="border:1px solid var(--line); font-weight:bold"><i class="fa fa-file-invoice" style="margin-left:4px"></i> كشف حساب</a>
      </div>

    </div>
  </div>

  {{-- Detailed Modal for Craftsman --}}
  <div class="rv-modal" id="{{ $modalId }}" onclick="if(event.target===this) this.classList.remove('open')">
    <div class="rv-card" style="width:min(1000px, 95vw); max-height:90vh; overflow-y:auto; padding:0; border-radius:12px; background:var(--surface); box-shadow:0 15px 40px rgba(0,0,0,0.15);">
      <div style="padding:16px 24px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:var(--surface); z-index:10;">
        <h3 style="margin:0;font-size:1.3rem; display:flex; align-items:center; gap:8px;">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-users"/></svg>
          تفاصيل شغل الصنايعي: {{ $c->name }}
        </h3>
        <button type="button" class="btn ghost sm" onclick="document.getElementById('{{ $modalId }}').classList.remove('open')" style="font-size:1.2rem; padding:4px 10px;">&times;</button>
      </div>
      <div style="padding:24px;">
        
        {{-- Craftsman Info Header (Moved from old main view) --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:20px; background:var(--bg-main); padding:16px; border-radius:10px;">
          <div style="flex:1">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;flex-wrap:wrap">
              <div class="star-display">
                @for($i=1; $i<=5; $i++)
                  <svg class="star-icon {{ $i <= $c->rating ? 'filled' : '' }}" viewBox="0 0 24 24" width="18" height="18">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                @endfor
                @if($c->rating) <span class="muted" style="font-size:12px">({{ $c->rating }}/5)</span> @endif
              </div>
            </div>
            <div class="muted" style="font-size:13px;line-height:1.8">
              <div>
                <strong>تاريخ البداية:</strong> {{ $c->start_date ? $c->start_date->format('Y-m-d') : '—' }} ·
                <strong>الهاتف:</strong> @if($c->phones->count()){{ $c->phones->join(' / ') }}@else — @endif
              </div>
              <div>
                <strong>البنود التي عمل بها:</strong> @if($c->bands_worked->count()){{ $c->bands_worked->join('، ') }}@else — @endif ·
                <strong>التخصصات:</strong> @if($c->specialties->count()){{ $c->specialties->join('، ') }}@else — @endif
              </div>
              <div>
                <strong>المشاريع:</strong> {{ $c->projects }} مشروع ·
                <strong>الدفعات:</strong> {{ $c->payments_count }} دفعة مستلمة ·
                <strong>المهام:</strong> {{ $c->assignments->count() }} بند
              </div>
              @if($c->notes)
                <div style="margin-top:8px;padding:8px;background:#fff;border:1px dashed var(--line);border-radius:6px;">
                  <strong>ملاحظات التقييم:</strong> {{ $c->notes }}
                </div>
              @endif
            </div>
          </div>
          <div style="display:flex;flex-direction:column;gap:12px;align-items:flex-end">
            <form action="{{ route('craftsmen.rate', $c->name) }}" method="POST" class="craftsman-rate-form" style="background:#fff; padding:12px; border-radius:8px; border:1px solid var(--line);">
              @csrf
              <div class="rate-stars" style="margin-bottom:8px;">
                @for($i=1; $i<=5; $i++)
                  <button type="button" class="rate-star-btn {{ $c->rating >= $i ? 'on' : '' }}" onclick="setRating(this, {{ $i }})" title="{{ $i }} نجوم">
                    <svg viewBox="0 0 24 24" width="22" height="22">
                      <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                @endfor
              </div>
              <input type="hidden" name="rating" class="rating-input" value="{{ $c->rating }}">
              <div style="display:flex; gap:6px;">
                <input type="text" name="notes" placeholder="ملاحظة التقييم..." value="{{ $c->notes }}" class="rate-notes-inp" style="flex:1; border:1px solid var(--line); border-radius:4px; padding:4px 8px;">
                <button type="submit" class="btn primary sm">حفظ</button>
              </div>
            </form>
          </div>
        </div>

        <h4 style="margin-bottom:12px; font-size:1.1rem; color:var(--ink); border-bottom:2px solid var(--brand); display:inline-block; padding-bottom:4px;">سجل المهام والمشاريع</h4>
        
        <div class="table-scroll">
          <table>
            <thead style="background:var(--bg2)">
              <tr>
                <th>المشروع</th>
                <th>البند</th>
                <th>التعاقد</th>
                <th class="num">متعاقد</th>
                <th class="num">مدفوع</th>
                <th class="num">متبقي</th>
                <th>إجراءات الدفع للبنود</th>
              </tr>
            </thead>
            <tbody>
              @foreach($c->assignments as $a)
                @php $paid = $a->paidTotal(); $remaining = $a->remaining(); @endphp
                <tr>
                  <td><strong>{{ $a->band?->project?->name ?? '—' }}</strong></td>
                  <td class="muted">{{ $a->band?->name ?? '—' }}</td>
                  <td class="muted">
                    {{ $a->contractTypeAr() }}
                    @if(in_array($a->contract_type, ['per_meter','per_piece','daily']) && $a->contract_qty)
                      <span style="font-size:12px; display:block; margin-top:2px;">({{ rtrim(rtrim(number_format($a->contract_qty, 2), '0'), '.') }} × {{ \App\Support\Money::format($a->contract_unit_rate) }})</span>
                    @endif
                  </td>
                  <td class="num">{{ \App\Support\Money::format($a->totalEntitlement()) }}</td>
                  <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</td>
                  <td class="num" style="color:{{ $remaining > 0 ? 'var(--neg)' : 'var(--pos)' }}"><strong>{{ \App\Support\Money::format($remaining) }}</strong></td>
                  <td>
                    <div style="display:flex; gap:4px">
                      @if($remaining > 0)
                        <button type="button" class="btn primary sm" style="background:#10b981; border-color:#10b981;" onclick="openPaymentModal({{ $a->id }}, '{{ htmlspecialchars($a->name, ENT_QUOTES) }}', {{ $remaining }})">سداد للبند</button>
                      @endif
                      <a href="{{ route('workers.payments', $a) }}" class="btn ghost sm" style="background:var(--bg2);">سجل الدفعات</a>
                      @if($remaining > 0)
                        <button type="button" class="btn ghost sm" style="color:var(--warn,#c9821a); border:1px solid var(--warn);" onclick="openDiscountModal({{ $a->id }}, '{{ htmlspecialchars($a->name, ENT_QUOTES) }}', {{ $remaining }})">خصم</button>
                      @endif
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

@empty
  <div class="table-card" style="padding:40px; text-align:center;">
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="48" height="48" style="color:var(--mut); margin-bottom:12px;"><use href="#i-users"/></svg>
      <h4 style="color:var(--mut)">لا يوجد صنايعية مسجلين بعد</h4>
    </div>
  </div>
@endforelse

@push('scripts')
<script>
function setRating(clickedBtn, val) {
  const form = clickedBtn.closest('.craftsman-rate-form');
  form.querySelector('.rating-input').value = val;
  const btns = form.querySelectorAll('.rate-star-btn');
  btns.forEach((b, i) => b.classList.toggle('on', i < val));
}

// --- Modal for direct discount ---
function openDiscountModal(workerId, workerName, remaining) {
  document.getElementById('discModalWorkerName').textContent = workerName;
  document.getElementById('discModalRemaining').textContent = remaining + ' ج.م';
  document.getElementById('discountForm').action = "/workers/" + workerId + "/payments";
  document.getElementById('discountModal').classList.add('open');
}
function closeDiscountModal() {
  document.getElementById('discountModal').classList.remove('open');
}

// --- Modal for direct payment (full/partial) ---
function openPaymentModal(workerId, workerName, remaining) {
  document.getElementById('payModalWorkerName').textContent = workerName;
  document.getElementById('payModalRemaining').textContent = remaining + ' ج.م';
  document.getElementById('payModalAmount').value = remaining > 0 ? remaining : 0;
  document.getElementById('paymentForm').action = "/workers/" + workerId + "/payments";
  document.getElementById('paymentModal').classList.add('open');
}
function closePaymentModal() {
  document.getElementById('paymentModal').classList.remove('open');
}
// --- Modal for Bulk Payment ---
function openBulkPaymentModal(workerName, remaining, isPartial = false) {
  document.getElementById('bulkModalWorkerName').textContent = workerName;
  document.getElementById('bulkModalWorkerNameInput').value = workerName;
  document.getElementById('bulkModalRemaining').textContent = remaining + ' ج.م';
  if (isPartial) {
      document.getElementById('bulkModalAmount').value = '';
  } else {
      document.getElementById('bulkModalAmount').value = remaining > 0 ? remaining : 0;
  }
  document.getElementById('bulkPaymentModal').classList.add('open');
}
function closeBulkPaymentModal() {
  document.getElementById('bulkPaymentModal').classList.remove('open');
}
</script>

<div class="rv-modal" id="discountModal" onclick="if(event.target===this) closeDiscountModal()">
  <div class="rv-card" style="max-width:400px;margin:20px;background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.1);padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #eee;padding-bottom:12px">
      <h3 style="margin:0;font-size:1.1rem">تسجيل خصم للصنايعي</h3>
      <button type="button" class="btn ghost sm" onclick="closeDiscountModal()" style="padding:4px 8px"><i class="fa fa-times"></i></button>
    </div>
    <form method="POST" action="" id="discountForm">
      @csrf
      <input type="hidden" name="amount" value="0">
      <div style="margin-bottom:12px; font-size:13px">
        <strong>الصنايعي:</strong> <span id="discModalWorkerName"></span><br>
        <strong>المتبقي عليه:</strong> <span id="discModalRemaining" style="color:var(--warn,#c9821a); font-weight:bold"></span>
      </div>
      <div class="field" style="margin-bottom:12px">
        <label>قيمة الخصم (ج.م) *</label>
        <input type="number" name="discount" step="0.01" min="0.01" required placeholder="مثال: 500" style="width:100%">
      </div>
      <div class="field" style="margin-bottom:12px">
        <label>سبب الخصم *</label>
        <input type="text" name="discount_reason" required placeholder="مثال: غياب / تأخير / خطأ في الشغل" style="width:100%">
      </div>
      <div class="field" style="margin-bottom:16px">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required style="width:100%">
      </div>
      <div style="text-align:left">
        <button type="button" class="btn ghost" onclick="closeDiscountModal()">إلغاء</button>
        <button type="submit" class="btn" style="background:var(--warn,#c9821a); border-color:var(--warn,#c9821a); color:#fff">تسجيل الخصم</button>
      </div>
    </form>
  </div>
</div>

{{-- Payment Modal --}}
<div class="rv-modal" id="paymentModal" onclick="if(event.target===this) closePaymentModal()">
  <div class="rv-card" style="max-width:400px;margin:20px;background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.1);padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #eee;padding-bottom:12px">
      <h3 style="margin:0;font-size:1.1rem">تسجيل دفعة سداد للصنايعي</h3>
      <button type="button" class="btn ghost sm" onclick="closePaymentModal()" style="padding:4px 8px"><i class="fa fa-times"></i></button>
    </div>
    <form method="POST" action="" id="paymentForm">
      @csrf
      <div style="margin-bottom:12px; font-size:13px">
        <strong>الصنايعي:</strong> <span id="payModalWorkerName"></span><br>
        <strong>المتبقي عليه:</strong> <span id="payModalRemaining" style="color:var(--pos,#10b981); font-weight:bold"></span>
      </div>
      
      <div class="field" style="margin-bottom:12px">
        <label>المبلغ (ج.م) *</label>
        <input type="number" name="amount" id="payModalAmount" step="0.01" min="0.01" required style="width:100%">
        <small class="muted">بادر بتغيير المبلغ إذا كان السداد جزئياً.</small>
      </div>

      <div class="field" style="margin-bottom:12px">
        <label>الخزنة / المحفظة *</label>
        <select name="account_id" required style="width:100%">
          <option value="">اختر الخزنة...</option>
          @foreach($wallets ?? [] as $w)
            <option value="{{ $w->id }}">{{ $w->name }} ({{ \App\Support\Money::format($w->balance) }})</option>
          @endforeach
        </select>
      </div>
      
      <div class="field" style="margin-bottom:12px">
        <label>البيان / ملاحظات <span class="muted">(اختياري)</span></label>
        <input type="text" name="notes" placeholder="تفاصيل الدفعة..." style="width:100%">
      </div>

      <div class="field" style="margin-bottom:16px">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required style="width:100%">
      </div>
      
      <div style="text-align:left">
        <button type="button" class="btn ghost" onclick="closePaymentModal()">إلغاء</button>
        <button type="submit" class="btn" style="background:var(--pos,#10b981); border-color:var(--pos,#10b981); color:#fff">تسجيل السداد</button>
      </div>
    </form>
  </div>
</div>

{{-- Bulk Payment Modal --}}
<div class="rv-modal" id="bulkPaymentModal" onclick="if(event.target===this) closeBulkPaymentModal()">
  <div class="rv-card" style="max-width:400px;margin:20px;background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.1);padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #eee;padding-bottom:12px">
      <h3 style="margin:0;font-size:1.1rem">تسجيل سداد مجمع للصنايعي</h3>
      <button type="button" class="btn ghost sm" onclick="closeBulkPaymentModal()" style="padding:4px 8px"><i class="fa fa-times"></i></button>
    </div>
    <form method="POST" action="{{ route('workers.pay_bulk') }}" id="bulkPaymentForm">
      @csrf
      <input type="hidden" name="worker_name" id="bulkModalWorkerNameInput">
      
      <div style="margin-bottom:12px; font-size:13px">
        <strong>الصنايعي:</strong> <span id="bulkModalWorkerName"></span><br>
        <strong>إجمالي المتبقي له:</strong> <span id="bulkModalRemaining" style="color:var(--pos,#10b981); font-weight:bold"></span>
      </div>
      
      <div class="field" style="margin-bottom:12px">
        <label>المبلغ المراد سداده (ج.م) *</label>
        <input type="number" name="amount" id="bulkModalAmount" step="0.01" min="0.01" required style="width:100%">
        <small class="muted">سيتم توزيع هذا المبلغ تلقائياً على بنود هذا الصنايعي.</small>
      </div>

      <div class="field" style="margin-bottom:12px">
        <label>الخزنة / المحفظة *</label>
        <select name="account_id" required style="width:100%">
          <option value="">اختر الخزنة...</option>
          @foreach($wallets ?? [] as $w)
            <option value="{{ $w->id }}">{{ $w->name }} ({{ \App\Support\Money::format($w->balance) }})</option>
          @endforeach
        </select>
      </div>
      
      <div class="field" style="margin-bottom:12px">
        <label>البيان / ملاحظات <span class="muted">(اختياري)</span></label>
        <input type="text" name="notes" placeholder="تفاصيل الدفعة المجمعة..." style="width:100%">
      </div>

      <div class="field" style="margin-bottom:16px">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required style="width:100%">
      </div>
      
      <div style="text-align:left">
        <button type="button" class="btn ghost" onclick="closeBulkPaymentModal()">إلغاء</button>
        <button type="submit" class="btn" style="background:var(--pos,#10b981); border-color:var(--pos,#10b981); color:#fff; font-weight:bold">تأكيد السداد المجمع</button>
      </div>
    </form>
  </div>
</div>

<style>
.rv-modal { position:fixed; inset:0; z-index:1060; display:none; align-items:center; justify-content:center; background:rgba(15,23,42,.55); }
.rv-modal.open { display:flex; }
</style>
@endpush
@endsection
