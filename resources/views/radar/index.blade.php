@extends('layouts.app')
@section('title', 'الرادار - سجل الحركات')
@section('page-title', 'الرادار')

@section('content')
<div class="page-head no-print">
  <div>
    <h3>الرادار (سجل الحركات)</h3>
    <p>مراقبة وتتبع جميع الأنشطة والعمليات التي تمت في النظام</p>
  </div>
  <button onclick="window.print()" class="btn ghost">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><use href="#i-printer"/></svg>
    طباعة السجل
  </button>
</div>

<div class="form-card no-print" style="margin-bottom: 20px;">
  <form method="GET" action="{{ route('radar.index') }}" class="row2" style="align-items: flex-end;">
    <div class="field" style="margin: 0;">
      <label>الفترة</label>
      <select name="period" onchange="document.getElementById('custom-dates').style.display = this.value === 'custom' ? 'flex' : 'none'">
        <option value="today" {{ $period === 'today' ? 'selected' : '' }}>اليوم</option>
        <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>الأمس</option>
        <option value="all" {{ $period === 'all' ? 'selected' : '' }}>كل الوقت</option>
        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>تاريخ مخصص...</option>
      </select>
    </div>
    
    <div id="custom-dates" style="display: {{ $period === 'custom' ? 'flex' : 'none' }}; gap: 10px; flex: 2;">
      <div class="field" style="margin: 0; flex: 1;"><label>من</label><input type="date" name="date_from" value="{{ request('date_from') }}"></div>
      <div class="field" style="margin: 0; flex: 1;"><label>إلى</label><input type="date" name="date_to" value="{{ request('date_to') }}"></div>
    </div>

    <div class="field" style="margin: 0;">
      <label>نوع الإجراء</label>
      <select name="action">
        <option value="">الكل</option>
        <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>إنشاء</option>
        <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>تعديل</option>
        <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>حذف / إلغاء</option>
      </select>
    </div>

    <div class="field" style="margin: 0;">
      <label>المستخدم</label>
      <select name="user_id">
        <option value="">الكل</option>
        @foreach($users as $user)
          <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
        @endforeach
      </select>
    </div>

    <button type="submit" class="btn" style="height: 42px;">تصفية</button>
    <a href="{{ route('radar.index') }}" class="btn ghost" style="height: 42px;">إعادة ضبط</a>
  </form>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>الوقت</th>
        <th>المستخدم</th>
        <th>الإجراء</th>
        <th>التصنيف</th>
        <th>المشروع / البند</th>
        <th>البيان</th>
        <th class="num">القيمة</th>
        <th class="no-print" style="width: 100px;">إجراء</th>
      </tr>
    </thead>
    <tbody>
      @forelse($logs as $log)
        @php
          $meta = $log->refMeta();
          $isLive = $log->transaction_id && isset($liveIds[$log->transaction_id]) && $log->action !== 'deleted';
          $isSafe = in_array($log->ref_type, ['manual', 'client_payment', null], true);
        @endphp
        <tr style="{{ $log->action === 'deleted' ? 'opacity:.6' : '' }}">
          <td class="muted" style="white-space:nowrap" title="{{ $log->happened_at->format('Y-m-d H:i:s') }}">
            {{ $log->happened_at->format('h:i A') }}<br>
            <small>{{ $log->happened_at->format('Y-m-d') }}</small>
          </td>
          <td>
            <div style="display:flex;align-items:center;gap:6px">
              <div class="avatar sm" style="background:#f1f5f9;color:#475569;width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600">
                {{ mb_substr($log->performedBy?->name ?? '?', 0, 1) }}
              </div>
              <span>{{ $log->performedBy?->name ?? 'نظام' }}</span>
            </div>
          </td>
          <td>
            @if($log->action === 'created')
              <span class="tag green">إنشاء</span>
            @elseif($log->action === 'updated')
              <span class="tag amber">تعديل</span>
            @elseif($log->action === 'deleted')
              <span class="tag red">حذف</span>
            @else
              <span class="tag gray">{{ $log->action }}</span>
            @endif
          </td>
          <td>
            <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border-radius:6px;background:{{ $meta['color'] }}15;color:{{ $meta['color'] }};font-size:12px;font-weight:600">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><use href="#{{ $meta['icon'] }}"/></svg>
              {{ $meta['label'] }}
            </div>
          </td>
          <td>
            @if($log->project)
              <a href="{{ route('projects.show', $log->project) }}" class="lk">{{ $log->project->name }}</a>
            @else
              <span class="muted">—</span>
            @endif
            @if($log->band)
              <br><small class="muted">بند: {{ $log->band->name }}</small>
            @endif
          </td>
          <td style="max-width:300px">
            @if($log->ref_type === 'material_invoice' && $log->ref_id)
              <a href="{{ route('material_invoices.show', $log->ref_id) }}" class="truncate muted" style="text-decoration:underline; display:block" title="{{ $log->description }}">{{ $log->description ?: '—' }}</a>
            @else
              <div class="truncate" title="{{ $log->description }}">{{ $log->description ?: '—' }}</div>
            @endif
          </td>
          <td class="num">
            @if($log->amount > 0)
              <b style="color:{{ $log->direction === 'in' ? 'var(--pos)' : ($log->direction === 'out' ? 'var(--neg)' : 'inherit') }}; {{ $log->action === 'deleted' ? 'text-decoration:line-through' : '' }}">
                {{ $log->direction === 'in' ? '+' : ($log->direction === 'out' ? '-' : '') }}{{ \App\Support\Money::format($log->amount) }}
              </b>
            @elseif($log->amount == 0 && $log->ref_type === 'material' && $log->ref_id && $log->action !== 'deleted')
              @php $deferredMat = \App\Models\Material::find($log->ref_id); @endphp
              @if($deferredMat && $deferredMat->grossCost() > 0)
                <b style="color:var(--amber)">{{ \App\Support\Money::format($deferredMat->grossCost()) }}</b>
                <div class="muted" style="font-size:10px">آجل بالكامل</div>
              @else
                <span class="muted">—</span>
              @endif
            @elseif($log->amount == 0 && $log->ref_type === 'material_invoice' && $log->ref_id && $log->action !== 'deleted')
              @php $deferredInv = \App\Models\MaterialInvoice::find($log->ref_id); @endphp
              @if($deferredInv && $deferredInv->total_amount > 0)
                <b style="color:var(--amber)">{{ \App\Support\Money::format($deferredInv->total_amount) }}</b>
                <div class="muted" style="font-size:10px">آجل بالكامل</div>
              @else
                <span class="muted">—</span>
              @endif
            @else
              <span class="muted">—</span>
            @endif
          </td>
          <td class="no-print">
            @if($isLive)
              <div style="display:flex;gap:4px">
                @if($isSafe)
                  <button type="button" class="btn ghost sm" title="تعديل"
                    onclick="openEditModal({{ $log->transaction_id }}, {{ $log->amount }}, {{ $log->account_id ?? 'null' }}, '{{ $log->date?->format('Y-m-d') }}', '{{ addslashes($log->description ?? '') }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-tool"/></svg>
                  </button>
                @endif
                <button type="button" class="btn ghost sm danger" title="حذف وعكس الحركة"
                  onclick="openDeleteModal({{ $log->transaction_id }}, '{{ addslashes($meta['label']) }}', '{{ \App\Support\Money::format($log->amount) }}', {{ $isSafe ? 'false' : 'true' }})">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
                </button>
              </div>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="empty" style="text-align:center;padding:30px">لا توجد حركات مسجلة في هذه الفترة</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="no-print" style="margin-top: 15px;">
  {{ $logs->links() }}
</div>

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
          <div id="edit-tx-error" class="txn-pw-error" style="display:none;color:var(--neg);background:#fef2f2;padding:6px 10px;border-radius:4px;margin-top:8px;font-size:12px;align-items:center;gap:6px">
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
      <h4 style="margin:0;color:var(--neg)">تأكيد عكس الحركة</h4>
      <button class="btn ghost sm" onclick="document.getElementById('delete-tx-modal').classList.remove('open')">✕</button>
    </div>
    <form id="delete-tx-form" method="POST" onsubmit="return submitTxForm(event, 'delete')">
      @csrf @method('DELETE')
      <div class="modal-body">
        <p id="delete-summary" style="margin:0 0 14px;line-height:1.7"></p>
        <p id="delete-owned-note" style="display:none;margin:0 0 14px;padding:10px 12px;background:var(--warn-soft,#fef3e2);border:1px dashed var(--warn,#c9821a);border-radius:8px;font-size:12.5px;color:var(--warn,#c9821a)">
          الحركة دي مرتبطة بسجل تاني (خامة/دفعة/دين...) — العكس هيلغي السجل الأصلي بالكامل وكأنه لم يكن، وهتتظبط كل الإجماليات والديون الخاصة بيه.
        </p>
        <div class="field">
          <label style="color:var(--neg)">كلمة مرور الأدمن للتأكيد *</label>
          <input type="password" name="current_password" id="delete-password" required autocomplete="current-password">
          <div id="delete-tx-error" class="txn-pw-error" style="display:none;color:var(--neg);background:#fef2f2;padding:6px 10px;border-radius:4px;margin-top:8px;font-size:12px;align-items:center;gap:6px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
            <span></span>
          </div>
        </div>
      </div>
      <div class="btn-row" style="padding:0 20px 20px">
        <button type="submit" class="btn danger" id="delete-tx-submit">تأكيد عكس الحركة</button>
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
  document.getElementById('delete-summary').innerHTML = 'هيتم التراجع وعكس: <strong>' + label + '</strong> — <strong>' + amount + ' ج.م</strong> وهيرجع أثرها بالكامل.';
  document.getElementById('delete-owned-note').style.display = isOwned ? 'block' : 'none';
  document.getElementById('delete-password').value = '';
  document.getElementById('delete-tx-error').style.display = 'none';
  document.getElementById('delete-tx-modal').classList.add('open');
}

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
  } catch (e) {}
}

function shakeModal(box) {
  box.classList.remove('shake-error');
  void box.offsetWidth;
  box.classList.add('shake-error');
}

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
    const msg = data?.errors?.current_password?.[0] || data?.message || 'حصل خطأ — راجع البيانات وحاول تاني.';

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
<style>
  @media print {
    body { background: #fff !important; }
    .table-wrap { box-shadow: none !important; border: 1px solid #ddd; }
    .table th { background: #f8f9fa !important; color: #000 !important; }
    .table td { border-bottom: 1px solid #ddd !important; }
    .tag { border: 1px solid #ddd; background: transparent !important; color: #000 !important; }
  }
</style>
@endpush
@endsection
