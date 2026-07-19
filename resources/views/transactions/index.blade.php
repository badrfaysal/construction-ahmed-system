@extends('layouts.app')
@section('title', 'سجل الحركات')
@section('page-title', 'سجل الحركات المالية')

@section('content')

<div class="page-head">
  <div><h3>سجل الحركات المالية</h3><p>كل حركة حصلت فعلاً في النظام — إنشاء وتعديل وحذف — مسجّلة تلقائياً ولا تُمحى أبداً</p></div>
</div>

{{-- Totals strip --}}
<div class="grid cols-2" style="margin-bottom:20px">
  <div class="vstat vstat-green">
    <div class="top">
      <span class="label">إجمالي الوارد (حي)</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trending-up"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalIn) }} <small>ج.م</small></div>
  </div>
  <div class="vstat vstat-red">
    <div class="top">
      <span class="label">إجمالي الصادر (حي)</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bar-chart"/></svg></span>
    </div>
    <div class="val tnum">{{ \App\Support\Money::format($totalOut) }} <small>ج.م</small></div>
  </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar" id="txn-filter-form">
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      المشروع
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
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
      نوع الحركة
    </label>
    <div class="tabs" style="margin-bottom:0">
      <a href="{{ request()->fullUrlWithQuery(['direction' => null]) }}" class="tab {{ !request('direction') ? 'active' : '' }}">الكل</a>
      <a href="{{ request()->fullUrlWithQuery(['direction' => 'in']) }}" class="tab {{ request('direction') === 'in' ? 'active' : '' }}">وارد</a>
      <a href="{{ request()->fullUrlWithQuery(['direction' => 'out']) }}" class="tab {{ request('direction') === 'out' ? 'active' : '' }}">صادر</a>
    </div>
  </div>
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-doc"/></svg>
      الإجراء
    </label>
    <div class="tabs" style="margin-bottom:0">
      <a href="{{ request()->fullUrlWithQuery(['action' => null]) }}" class="tab {{ !request('action') ? 'active' : '' }}">الكل</a>
      <a href="{{ request()->fullUrlWithQuery(['action' => 'created']) }}" class="tab {{ request('action') === 'created' ? 'active' : '' }}">إنشاء</a>
      <a href="{{ request()->fullUrlWithQuery(['action' => 'updated']) }}" class="tab {{ request('action') === 'updated' ? 'active' : '' }}">تعديل</a>
      <a href="{{ request()->fullUrlWithQuery(['action' => 'deleted']) }}" class="tab {{ request('action') === 'deleted' ? 'active' : '' }}">حذف/إلغاء</a>
    </div>
  </div>
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-calendar"/></svg>
      التاريخ
    </label>
    <div class="tabs" style="margin-bottom:0">
      <a href="{{ request()->fullUrlWithQuery(['range' => null, 'from' => null, 'to' => null]) }}" class="tab {{ !request('range') ? 'active' : '' }}">الكل</a>
      <a href="{{ request()->fullUrlWithQuery(['range' => 'today', 'from' => null, 'to' => null]) }}" class="tab {{ request('range') === 'today' ? 'active' : '' }}">اليوم</a>
      <a href="{{ request()->fullUrlWithQuery(['range' => 'yesterday', 'from' => null, 'to' => null]) }}" class="tab {{ request('range') === 'yesterday' ? 'active' : '' }}">أمس</a>
      <a href="{{ request()->fullUrlWithQuery(['range' => 'week', 'from' => null, 'to' => null]) }}" class="tab {{ request('range') === 'week' ? 'active' : '' }}">الأسبوع</a>
      <a href="#" onclick="event.preventDefault();document.getElementById('custom-range-row').style.display='flex';document.querySelector('input[name=range][value=custom]').checked=true;" class="tab {{ request('range') === 'custom' ? 'active' : '' }}">رنج مخصص</a>
    </div>
  </div>
  <div id="custom-range-row" style="display:{{ request('range') === 'custom' ? 'flex' : 'none' }};gap:8px;align-items:flex-end">
    <input type="hidden" name="range" value="custom">
    <div class="f-field">
      <label>من</label>
      <input type="date" name="from" value="{{ request('from') }}" class="f-select" style="height:38px">
    </div>
    <div class="f-field">
      <label>إلى</label>
      <input type="date" name="to" value="{{ request('to') }}" class="f-select" style="height:38px">
    </div>
    <button type="submit" class="btn sm">تطبيق</button>
  </div>
  @include('partials._sort-select', ['options' => [
    'newest'      => 'الأحدث',
    'oldest'      => 'الأقدم',
    'amount_desc' => 'الأعلى مبلغًا',
    'amount_asc'  => 'الأقل مبلغًا',
  ]])
  @if(request()->hasAny(['project_id','direction','action','sort','range','from','to']))
    <div class="f-actions">
      <a href="{{ route('transactions.index') }}" class="btn ghost sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
        مسح الفلتر
      </a>
    </div>
  @endif
</form>

<div class="txn-page-wrap">
  {{-- خلفية أيقونات مالية زخرفية — تدي الصفحة طابع "بنكي" مميز --}}
  <div class="txn-bg-icons" aria-hidden="true">
    <svg viewBox="0 0 24 24"><use href="#i-wallet"/></svg>
    <svg viewBox="0 0 24 24"><use href="#i-coins"/></svg>
    <svg viewBox="0 0 24 24"><use href="#i-credit-card"/></svg>
    <svg viewBox="0 0 24 24"><use href="#i-bar-chart"/></svg>
    <svg viewBox="0 0 24 24"><use href="#i-trending-up"/></svg>
    <svg viewBox="0 0 24 24"><use href="#i-receipt"/></svg>
    <svg viewBox="0 0 24 24"><use href="#i-building"/></svg>
    <svg viewBox="0 0 24 24"><use href="#i-cash"/></svg>
  </div>

  <div class="table-card" style="position:relative;z-index:1">
    @if($logs->count())
      <div class="feed">
        @foreach($logs as $log)
          @php
            $meta   = $log->refMeta();
            $isLive = $log->transaction_id && isset($liveIds[$log->transaction_id]) && $log->action !== 'deleted';
            $isSafe = in_array($log->ref_type, ['manual', 'client_payment', null], true);
          @endphp
          <div class="tx txn-row" style="{{ $log->action === 'deleted' ? 'opacity:.6' : '' }};--txn-color:{{ $meta['color'] }}">
            <div class="tx-ic" style="background:{{ $meta['color'] }}1a;color:{{ $meta['color'] }}">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                @if($log->action === 'deleted')
                  <use href="#i-x"/>
                @else
                  <use href="#{{ $meta['icon'] }}"/>
                @endif
              </svg>
            </div>
            <div class="tx-main">
              <div class="t">
                {{ $log->party ?: '—' }}
                <span class="tag sm" style="background:{{ $meta['color'] }}1a;color:{{ $meta['color'] }};margin-inline-start:6px">{{ $meta['label'] }}</span>
                <span class="tag {{ $log->action === 'created' ? 'green' : ($log->action === 'deleted' ? 'red' : 'amber') }} sm">
                  {{ $log->actionAr() }}
                </span>
              </div>
              <div class="s">
                @if($log->direction)
                  <span class="tag {{ $log->direction === 'in' ? 'green' : 'red' }} sm">{{ $log->directionAr() }}</span>
                @endif
                <span>{{ $log->type }}</span>
                @if($log->project)
                  <span class="tag gray sm">{{ $log->project->name }}</span>
                @endif
                @if($log->band)
                  <span class="tag gray sm">{{ $log->band->name }}</span>
                @endif
                @if($log->date)
                  <span>{{ $log->date->format('d/m/Y') }}</span>
                @endif
                <span class="muted" title="{{ $log->happened_at }}">سُجّل: {{ $log->happened_at->format('d/m/Y H:i') }}</span>
                @if($log->performedBy)
                  <span class="muted">— {{ $log->performedBy->name }}</span>
                @endif
                @if($log->description)
                  @if($log->ref_type === 'material_invoice' && $log->ref_id)
                    <a href="{{ route('material_invoices.show', $log->ref_id) }}" class="muted" style="text-decoration:underline">{{ $log->description }}</a>
                  @else
                    <span class="muted">{{ $log->description }}</span>
                  @endif
                @endif
              </div>
              @if($log->action === 'updated' && $log->old_values)
                <div class="s" style="margin-top:4px;color:#b7791f">
                  قبل التعديل:
                  @foreach($log->old_values as $field => $val)
                    <span style="margin-inline-end:8px">{{ $field }}: <strong>{{ $val }}</strong></span>
                  @endforeach
                </div>
              @endif
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
              <div class="tx-amt" style="{{ $log->action === 'deleted' ? 'text-decoration:line-through' : '' }};color:{{ $meta['color'] }}">
                @if($log->amount > 0)
                  {{ $log->direction === 'in' ? '+ ' : '− ' }}{{ \App\Support\Money::format($log->amount) }} ج.م
                @elseif($log->amount == 0 && $log->ref_type === 'material' && $log->ref_id && $log->action !== 'deleted')
                  @php $txDeferredMat = \App\Models\Material::find($log->ref_id); @endphp
                  @if($txDeferredMat && $txDeferredMat->grossCost() > 0)
                    <span style="color:var(--amber)">{{ \App\Support\Money::format($txDeferredMat->grossCost()) }} ج.م</span>
                    <span class="muted" style="font-size:10px;margin-right:4px">(آجل)</span>
                  @else
                    0.00 ج.م
                  @endif
                @elseif($log->amount == 0 && $log->ref_type === 'material_invoice' && $log->ref_id && $log->action !== 'deleted')
                  @php $txDeferredInv = \App\Models\MaterialInvoice::find($log->ref_id); @endphp
                  @if($txDeferredInv && $txDeferredInv->total_amount > 0)
                    <span style="color:var(--amber)">{{ \App\Support\Money::format($txDeferredInv->total_amount) }} ج.م</span>
                    <span class="muted" style="font-size:10px;margin-right:4px">(آجل)</span>
                  @else
                    0.00 ج.م
                  @endif
                @else
                  {{ $log->direction === 'in' ? '+ ' : '− ' }}0.00 ج.م
                @endif
              </div>
              @if($isLive && auth()->user()->isAdmin())
                <div style="display:flex;gap:4px">
                  @if($isSafe)
                    <button type="button" class="btn ghost sm txn-icon-btn" title="تعديل"
                      onclick="openEditModal({{ $log->transaction_id }}, {{ $log->amount }}, {{ $log->account_id ?? 'null' }}, '{{ $log->date?->format('Y-m-d') }}', '{{ addslashes($log->description ?? '') }}')">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-tool"/></svg>
                    </button>
                  @endif
                  <button type="button" class="btn ghost sm txn-icon-btn danger" title="حذف"
                    onclick="openDeleteModal({{ $log->transaction_id }}, '{{ addslashes($meta['label']) }}', '{{ \App\Support\Money::format($log->amount) }}', {{ $isSafe ? 'false' : 'true' }})">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
                  </button>
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
      <div style="padding:14px 18px;border-top:1px solid var(--line)">
        {{ $logs->withQueryString()->links() }}
      </div>
    @else
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
        <h4>لا توجد حركات</h4>
      </div>
    @endif
  </div>
</div>

@if(auth()->user()->isAdmin())
{{-- Edit Modal --}}
<div class="modal-overlay" id="edit-tx-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-head">
      <h4 style="margin:0">تعديل الحركة</h4>
      <button class="btn ghost sm" onclick="document.getElementById('edit-tx-modal').classList.remove('open')">✕</button>
    </div>
    <form id="edit-tx-form" method="POST" onsubmit="return submitTxForm(event, 'edit')">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="field">
          <label>المبلغ (ج.م) *</label>
          <input type="number" name="amount" id="edit-amount" min="0.01" step="0.01" required>
        </div>
        @include('partials._wallet-select', ['wallets' => $wallets, 'name' => 'account_id', 'required' => true, 'bare' => false])
        <div class="field">
          <label>التاريخ *</label>
          <input type="date" name="date" id="edit-date" required>
        </div>
        <div class="field">
          <label>الوصف</label>
          <input type="text" name="description" id="edit-description">
        </div>
        <div class="field" style="border-top:1px dashed var(--line);padding-top:14px;margin-top:6px">
          <label style="color:var(--neg)">كلمة مرور الأدمن للتأكيد *</label>
          <input type="password" name="current_password" id="edit-password" required autocomplete="current-password">
          <div id="edit-tx-error" class="txn-pw-error" style="display:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
            <span></span>
          </div>
        </div>
      </div>
      <div class="btn-row" style="padding:0 20px 20px">
        <button type="submit" class="btn" id="edit-tx-submit">حفظ التعديل</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('edit-tx-modal').classList.remove('open')">إلغاء</button>
      </div>
    </form>
  </div>
</div>

{{-- Delete Modal --}}
<div class="modal-overlay" id="delete-tx-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:440px">
    <div class="modal-head">
      <h4 style="margin:0;color:var(--neg)">حذف الحركة</h4>
      <button class="btn ghost sm" onclick="document.getElementById('delete-tx-modal').classList.remove('open')">✕</button>
    </div>
    <form id="delete-tx-form" method="POST" onsubmit="return submitTxForm(event, 'delete')">
      @csrf @method('DELETE')
      <div class="modal-body">
        <p id="delete-summary" style="margin:0 0 14px;line-height:1.7"></p>
        <p id="delete-owned-note" style="display:none;margin:0 0 14px;padding:10px 12px;background:var(--warn-soft,#fef3e2);border:1px dashed var(--warn,#c9821a);border-radius:8px;font-size:12.5px;color:var(--warn,#c9821a)">
          الحركة دي مرتبطة بسجل تاني (خامة/دفعة/دين...) — الحذف هيمسح السجل الأصلي بالكامل مش الحركة بس، وأي تنظيف مرتبط (زي متبقي عقد أو رصيد دين) هيتظبط تلقائيًا.
        </p>
        <div class="field">
          <label style="color:var(--neg)">كلمة مرور الأدمن للتأكيد *</label>
          <input type="password" name="current_password" id="delete-password" required autocomplete="current-password">
          <div id="delete-tx-error" class="txn-pw-error" style="display:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
            <span></span>
          </div>
        </div>
      </div>
      <div class="btn-row" style="padding:0 20px 20px">
        <button type="submit" class="btn danger" id="delete-tx-submit">تأكيد الحذف</button>
        <button type="button" class="btn ghost" onclick="document.getElementById('delete-tx-modal').classList.remove('open')">إلغاء</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openEditModal(txId, amount, accountId, date, description) {
  document.getElementById('edit-tx-form').action = '/transactions/' + txId;
  document.getElementById('edit-amount').value = amount;
  const walletSel = document.querySelector('#edit-tx-form select[name="account_id"]');
  if (walletSel) walletSel.value = accountId || '';
  document.getElementById('edit-date').value = date;
  document.getElementById('edit-description').value = description || '';
  document.getElementById('edit-password').value = '';
  document.getElementById('edit-tx-error').style.display = 'none';
  document.getElementById('edit-tx-modal').classList.add('open');
}
function openDeleteModal(txId, label, amount, isOwned) {
  document.getElementById('delete-tx-form').action = '/transactions/' + txId;
  document.getElementById('delete-summary').innerHTML = 'هيتحذف: <strong>' + label + '</strong> — <strong>' + amount + ' ج.م</strong> وهيرجع أثرها بالكامل من المحفظة.';
  document.getElementById('delete-owned-note').style.display = isOwned ? 'block' : 'none';
  document.getElementById('delete-password').value = '';
  document.getElementById('delete-tx-error').style.display = 'none';
  document.getElementById('delete-tx-modal').classList.add('open');
}

// نغمة إنذار قصيرة (نغمتين هابطتين) — من غير أي ملف صوت خارجي
function playAlarmSound() {
  try {
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (! Ctx) return;
    const ctx = new Ctx();
    const now = ctx.currentTime;
    [[880, 0], [660, 0.16]].forEach(([freq, offset]) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'square';
      osc.frequency.setValueAtTime(freq, now + offset);
      gain.gain.setValueAtTime(0.16, now + offset);
      gain.gain.exponentialRampToValueAtTime(0.001, now + offset + 0.15);
      osc.connect(gain).connect(ctx.destination);
      osc.start(now + offset);
      osc.stop(now + offset + 0.16);
    });
  } catch (e) { /* الصوت اختياري — لو المتصفح رافض، الرسالة والاهتزاز كفاية */ }
}

function shakeModal(box) {
  box.classList.remove('shake-error');
  void box.offsetWidth; // إعادة تشغيل الأنيميشن لو كانت شغالة بالفعل
  box.classList.add('shake-error');
}

// تحقق الباسورد بالـ AJAX من غير ما الصفحة تعمل reload — لو غلط، رسالة واضحة
// + اهتزاز + صوت إنذار فورًا بدل ما ينتظر redirect كامل
async function submitTxForm(evt, prefix) {
  evt.preventDefault();
  const form = evt.target;
  const submitBtn = document.getElementById(prefix + '-tx-submit');
  const errorBox = document.getElementById(prefix + '-tx-error');
  const errorSpan = errorBox.querySelector('span');
  const passwordInput = document.getElementById(prefix + '-password');

  errorBox.style.display = 'none';
  submitBtn.disabled = true;

  try {
    const res = await fetch(form.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: new FormData(form),
    });

    if (res.ok) {
      window.location.reload();
      return false;
    }

    const data = await res.json().catch(() => ({}));
    const msg = data?.errors?.current_password?.[0]
      || data?.message
      || 'حصل خطأ — راجع البيانات وحاول تاني.';

    errorSpan.textContent = msg;
    errorBox.style.display = 'flex';
    playAlarmSound();
    shakeModal(form.closest('.modal-box'));
    passwordInput.value = '';
    passwordInput.focus();
  } catch (e) {
    errorSpan.textContent = 'حصل خطأ في الاتصال — حاول تاني.';
    errorBox.style.display = 'flex';
  } finally {
    submitBtn.disabled = false;
  }

  return false;
}
</script>
@endpush
@endif

@endsection
