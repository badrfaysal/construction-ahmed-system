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

<form method="GET" class="filter-bar">
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
  <div class="table-card" style="margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;padding:14px 18px;border-bottom:1px solid var(--line)">
      <div style="flex:1">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;flex-wrap:wrap">
          <h4 style="margin:0">{{ $c->name }}</h4>
          <div class="star-display">
            @for($i=1; $i<=5; $i++)
              <svg class="star-icon {{ $i <= $c->rating ? 'filled' : '' }}" viewBox="0 0 24 24" width="18" height="18">
                <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            @endfor
            @if($c->rating) <span class="muted" style="font-size:12px">({{ $c->rating }}/5)</span> @endif
          </div>
        </div>
        <div class="muted" style="font-size:13px;line-height:1.6">
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
            <div style="margin-top:4px;padding:6px;background:var(--bg-muted);border-radius:4px">
              <strong>ملاحظات التقييم:</strong> {{ $c->notes }}
            </div>
          @endif
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:12px;align-items:flex-end">
        <div style="display:flex;gap:20px;text-align:center">
          <div><div class="muted" style="font-size:12px">متعاقد</div><div class="tnum" style="font-weight:700">{{ \App\Support\Money::format($c->contracted) }}</div></div>
          <div><div class="muted" style="font-size:12px">مسوّى</div><div class="tnum" style="font-weight:700;color:var(--pos)">{{ \App\Support\Money::format($c->paid) }}</div></div>
          <div><div class="muted" style="font-size:12px">متبقي</div><div class="tnum" style="font-weight:700;color:{{ $c->remaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($c->remaining) }}</div></div>
          @if($c->owed_to_us > 0)
            <div><div class="muted" style="font-size:12px;color:var(--warn,#c9821a)">مستحق لينا</div><div class="tnum" style="font-weight:700;color:var(--warn,#c9821a)">{{ \App\Support\Money::format($c->owed_to_us) }}</div></div>
          @endif
        </div>
        <form action="{{ route('craftsmen.rate', $c->name) }}" method="POST" class="craftsman-rate-form">
          @csrf
          <div class="rate-stars">
            @for($i=1; $i<=5; $i++)
              <button type="button" class="rate-star-btn {{ $c->rating >= $i ? 'on' : '' }}"
                      onclick="setRating(this, {{ $i }})" title="{{ $i }} نجوم">
                <svg viewBox="0 0 24 24" width="22" height="22">
                  <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            @endfor
          </div>
          <input type="hidden" name="rating" class="rating-input" value="{{ $c->rating }}">
          <input type="text" name="notes" placeholder="ملاحظة..." value="{{ $c->notes }}" class="rate-notes-inp">
          <button type="submit" class="btn sm">حفظ</button>
        </form>
      </div>
    </div>
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>المشروع</th>
            <th>البند</th>
            <th>التعاقد</th>
            <th class="num">متعاقد</th>
            <th class="num">مدفوع</th>
            <th class="num">متبقي</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($c->assignments as $a)
            @php $paid = $a->paidTotal(); $remaining = $a->remaining(); @endphp
            <tr>
              <td>{{ $a->band?->project?->name ?? '—' }}</td>
              <td class="muted">{{ $a->band?->name ?? '—' }}</td>
              <td class="muted">
                {{ $a->contractTypeAr() }}
                @if(in_array($a->contract_type, ['per_meter','per_piece','daily']) && $a->contract_qty)
                  <span style="font-size:12px">({{ rtrim(rtrim(number_format($a->contract_qty, 2), '0'), '.') }} × {{ \App\Support\Money::format($a->contract_unit_rate) }})</span>
                @endif
              </td>
              <td class="num">{{ \App\Support\Money::format($a->amount) }}</td>
              <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($paid) }}</td>
              <td class="num" style="color:{{ $remaining > 0 ? 'var(--neg)' : 'var(--pos)' }}">{{ \App\Support\Money::format($remaining) }}</td>
              <td>
                <div style="display:flex; gap:4px">
                  <a href="{{ route('workers.payments', $a) }}" class="btn ghost sm">الدفعات</a>
                  <button type="button" class="btn ghost sm" style="color:var(--warn,#c9821a)" onclick="openDiscountModal({{ $a->id }}, '{{ htmlspecialchars($a->name, ENT_QUOTES) }}', {{ $remaining }})">خصم</button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@empty
  <div class="table-card">
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
      <h4>لا يوجد صنايعية مسجلين بعد</h4>
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

<style>
.rv-modal { position:fixed; inset:0; z-index:1060; display:none; align-items:center; justify-content:center; background:rgba(15,23,42,.55); }
.rv-modal.open { display:flex; }
</style>
@endpush
@endsection
