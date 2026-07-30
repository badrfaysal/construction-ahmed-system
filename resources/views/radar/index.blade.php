@extends('layouts.app')
@section('title', 'العمليات المالية ومراقبة النظام')
@section('page-title', 'العمليات المالية')

@section('content')
<div class="page-head no-print">
  <div>
    <h3>العمليات المالية ومراقبة النظام</h3>
    <p>لوحة تحكم بنكية لمراقبة تدفقات الأموال، ورادار تسجيل كافة الأنشطة</p>
  </div>
  <button onclick="window.print()" class="btn ghost">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><use href="#i-printer"/></svg>
    طباعة ملخص اليوم
  </button>
</div>

{{-- Top Action Bar & Stats --}}
<div class="no-print" style="margin-bottom:20px;">
  <div style="background:var(--ink-1); color:#fff; border-radius:12px; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; box-shadow:var(--shadow-md);">
    <button class="btn primary" onclick="openOperationModal()" style="font-size:16px; font-weight:bold; padding:10px 24px; border-radius:30px; background:#10b981; border-color:#10b981; color:#fff;">
      + بدء العملية
    </button>
    <div style="text-align:left; direction:ltr;">
      <h3 style="margin:0; font-size:20px; display:flex; align-items:center; justify-content:flex-end; gap:8px;">
        تنفيذ حركة يدوية
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><use href="#i-zap"/></svg>
      </h3>
      <div style="font-size:13px; color:var(--ink-4); margin-top:4px; direction:rtl;">
        <span style="color:#10b981; font-weight:bold; background:rgba(16,185,129,0.1); padding:2px 4px; border-radius:4px;">إيداع</span> يتسجل كـ دين عليك للمودع | 
        <span style="color:#ef4444; font-weight:bold; background:rgba(239,68,68,0.1); padding:2px 4px; border-radius:4px;">صرف</span> يتسجل كـ مستحق على المستلم | 
        <span style="color:#3b82f6; font-weight:bold; background:rgba(59,130,246,0.1); padding:2px 4px; border-radius:4px;">تحويل</span> بين المحافظ
      </div>
    </div>
  </div>

  <div style="display:grid; grid-template-columns:1fr 1.5fr 1.5fr 1.5fr; gap:16px;">
    {{-- Canceled --}}
    <div style="background:#475569; color:#fff; border-radius:10px; padding:16px; position:relative; overflow:hidden;">
      <div style="position:absolute; left:-20px; bottom:-20px; opacity:0.1; transform:scale(2);">
        <svg viewBox="0 0 24 24" fill="currentColor" width="100" height="100"><use href="#i-pie-chart"/></svg>
      </div>
      <div style="font-size:14px; font-weight:bold; text-align:left; margin-bottom:8px;">عمليات ملغاة</div>
      <div style="font-size:24px; font-weight:bold; text-align:left; margin-bottom:12px;">{{ $canceledCount }}</div>
      <div style="font-size:10px; opacity:0.8; text-align:right; border-top:1px dashed rgba(255,255,255,0.2); padding-top:8px;">
        عدد الحركات اليدوية اللي اتعمل لها إلغاء في نفس الفترة
      </div>
    </div>
    
    {{-- Transfers --}}
    <div style="background:#2563eb; color:#fff; border-radius:10px; padding:16px; position:relative; overflow:hidden;">
      <div style="position:absolute; left:-20px; bottom:-20px; opacity:0.1; transform:scale(2);">
        <svg viewBox="0 0 24 24" fill="currentColor" width="100" height="100"><use href="#i-pie-chart"/></svg>
      </div>
      <div style="font-size:14px; font-weight:bold; text-align:left; margin-bottom:8px;">إجمالي التحويلات</div>
      <div style="font-size:24px; font-weight:bold; text-align:left; margin-bottom:12px;">{{ \App\Support\Money::format($totalTransfers) }} ج</div>
      <div style="font-size:10px; opacity:0.8; text-align:right; border-top:1px dashed rgba(255,255,255,0.2); padding-top:8px;">
        فلوس انتقلت بين حسابات الشركة نفسها (من خزنة لمحفظة مثلاً) — مش دخلت ولا خرجت فعلياً
      </div>
    </div>

    {{-- Outgoing --}}
    <div style="background:#16a34a; color:#fff; border-radius:10px; padding:16px; position:relative; overflow:hidden;">
      <div style="position:absolute; left:-20px; bottom:-20px; opacity:0.1; transform:scale(2);">
        <svg viewBox="0 0 24 24" fill="currentColor" width="100" height="100"><use href="#i-pie-chart"/></svg>
      </div>
      <div style="font-size:14px; font-weight:bold; text-align:left; margin-bottom:8px;">حجم التدفقات الخارجة</div>
      <div style="font-size:24px; font-weight:bold; text-align:left; margin-bottom:12px;">{{ \App\Support\Money::format($totalOut) }} ج</div>
      <div style="font-size:10px; opacity:0.8; text-align:right; border-top:1px dashed rgba(255,255,255,0.2); padding-top:8px;">
        كل فلوس خرجت من الحسابات: مصروفات + رواتب + خصومات + عهد موظفين + إعدامات ديون وأي صرف تاني. أشمل من "إجمالي المصروفات" في شاشة التقارير لإنها بتستبعد بعض البنود دي كأرقام منفصلة
      </div>
    </div>

    {{-- Incoming --}}
    <div style="background:#dc2626; color:#fff; border-radius:10px; padding:16px; position:relative; overflow:hidden;">
      <div style="position:absolute; left:-20px; bottom:-20px; opacity:0.1; transform:scale(2);">
        <svg viewBox="0 0 24 24" fill="currentColor" width="100" height="100"><use href="#i-pie-chart"/></svg>
      </div>
      <div style="font-size:14px; font-weight:bold; text-align:left; margin-bottom:8px;">حجم التدفقات الداخلة</div>
      <div style="font-size:24px; font-weight:bold; text-align:left; margin-bottom:12px;">{{ \App\Support\Money::format($totalIn) }} ج</div>
      <div style="font-size:10px; opacity:0.8; text-align:right; border-top:1px dashed rgba(255,255,255,0.2); padding-top:8px;">
        كل فلوس دخلت الحسابات: إيرادات فعلية + تسويات (إيداعات مسجلة كدين على الشركة). أشمل من "إجمالي الإيرادات" في شاشة التقارير لإنها بتفصل التسويات
      </div>
    </div>
  </div>
</div>

<div class="form-card no-print" style="margin-bottom: 20px;">
  <form method="GET" action="{{ route('radar.index') }}" id="filter-form">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--line); padding-bottom:16px; margin-bottom:16px;">
      <h3 style="margin:0; font-size:16px; display:flex; align-items:center; gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" style="color:var(--brand)"><use href="#i-list"/></svg>
        سجل الحركات التفصيلي
      </h3>
      
      <div style="display:flex; gap:12px; align-items:center;">
        <div style="display:flex; gap:8px; align-items:center; background:#f1f5f9; padding:4px; border-radius:8px;">
          @php $ranges = ['all' => 'الكل', 'today' => 'اليوم', 'yesterday' => 'أمس', 'month' => 'الشهر ده']; @endphp
          @foreach($ranges as $val => $label)
            <label style="margin:0; cursor:pointer;">
              <input type="radio" name="period" value="{{ $val }}" style="display:none;" onchange="document.getElementById('filter-form').submit()" {{ $period === $val ? 'checked' : '' }}>
              <div style="padding:6px 16px; border-radius:6px; font-size:13px; font-weight:600; {{ $period === $val ? 'background:var(--ink-1); color:#fff;' : 'color:var(--ink-2);' }}">
                {{ $label }}
              </div>
            </label>
          @endforeach
          <label style="margin:0; cursor:pointer;">
            <input type="radio" name="period" value="custom" style="display:none;" onchange="document.getElementById('filter-form').submit()" {{ $period === 'custom' ? 'checked' : '' }}>
            <div style="padding:6px 16px; border-radius:6px; font-size:13px; font-weight:600; {{ $period === 'custom' ? 'background:var(--ink-1); color:#fff;' : 'color:var(--ink-2);' }}">
              تصفية <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><use href="#i-filter"/></svg>
            </div>
          </label>
        </div>
        
        <div id="custom-dates" style="display: {{ $period === 'custom' ? 'flex' : 'none' }}; gap: 8px;">
          <input type="date" name="date_to" value="{{ request('date_to') }}" style="height:34px; padding:0 8px; font-size:13px;" placeholder="إلى تاريخ">
          <input type="date" name="date_from" value="{{ request('date_from') }}" style="height:34px; padding:0 8px; font-size:13px;" placeholder="من تاريخ">
          <button type="submit" class="btn sm" style="height:34px;">تطبيق</button>
        </div>
      </div>
    </div>
    
    <div class="row2">
      <div class="field" style="margin: 0;">
        <select name="action" onchange="this.form.submit()">
          <option value="">كل الإجراءات</option>
          <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>إنشاء</option>
          <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>تعديل</option>
          <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>حذف / إلغاء</option>
        </select>
      </div>

      <div class="field" style="margin: 0;">
        <select name="user_id" onchange="this.form.submit()">
          <option value="">كل المستخدمين</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </form>
</div>

<div class="table-wrap" style="box-shadow:none; border:none;">
  <table class="table" style="min-width:1000px">
    <thead>
      <tr>
        <th style="padding-right:20px;">#</th>
        <th>نوع الحركة</th>
        <th class="num">المبلغ</th>
        <th style="text-align:center">الطرف الخارجي</th>
        <th style="text-align:center">مسار الحركة (FLOW)</th>
        <th>البيان والمشروع</th>
        <th>تاريخ التنفيذ</th>
        <th class="no-print">إجراء</th>
      </tr>
    </thead>
    <tbody>
      @forelse($logs as $log)
        @php
          $meta = $log->refMeta();
          $isLive = $log->transaction_id && isset($liveIds[$log->transaction_id]) && $log->action !== 'deleted';
          $isSafe = in_array($log->ref_type, ['manual', 'client_payment', null], true);
          $accountName = \App\Models\Account::nameOf($log->account_id);
          $partyName = $log->party ?: 'خارجي';
          
          $isTransfer = $log->ref_type === 'transfer';
          if ($isTransfer) {
            $isTransferOut = $log->direction === 'out';
            $meta['label'] = $isTransferOut ? 'تحويل صادر' : 'تحويل وارد';
          }
        @endphp
        <tr style="{{ $log->action === 'deleted' ? 'background:repeating-linear-gradient(45deg, #fffafa, #fffafa 10px, #fff5f5 10px, #fff5f5 20px); opacity: 0.8;' : '' }}">
          <td class="muted" style="padding-right:20px;">{{ $log->transaction_id ?? $log->id }}</td>
          
          <td>
            <div style="display:flex;align-items:center;gap:12px">
              <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:{{ $isTransfer ? '#3b82f6' : ($log->direction === 'in' ? '#10b981' : ($log->direction === 'out' ? '#ef4444' : '#64748b')) }};color:#fff;flex-shrink:0;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" style="{{ $log->direction === 'out' && !$isTransfer ? 'transform: rotate(180deg);' : '' }}">
                  @if($isTransfer)
                    <use href="#i-activity"/>
                  @elseif($log->direction === 'in' || $log->direction === 'out')
                    <use href="#i-down"/>
                  @else
                    <use href="#i-activity"/>
                  @endif
                </svg>
              </div>
              <div>
                <div style="font-weight:bold;font-size:13px">{{ $meta['label'] }}</div>
                <div style="font-size:11px;color:#64748b">{{ $isTransfer ? 'تحويل' : ($log->direction === 'in' ? 'تدفق داخل' : ($log->direction === 'out' ? 'تدفق خارج' : '—')) }}</div>
              </div>
            </div>
          </td>

          <td class="num">
            @if($log->amount > 0)
              <b style="color:{{ $isTransfer ? '#3b82f6' : ($log->direction === 'in' ? '#10b981' : ($log->direction === 'out' ? '#ef4444' : 'inherit')) }}; {{ $log->action === 'deleted' ? 'text-decoration:line-through' : '' }}">
                EGP {{ \App\Support\Money::format($log->amount) }} {{ $log->direction === 'in' ? '+' : ($log->direction === 'out' ? '-' : '') }}
              </b>
            @elseif($log->amount == 0 && $log->ref_type === 'material' && $log->ref_id && $log->action !== 'deleted')
              @php $deferredMat = \App\Models\Material::find($log->ref_id); @endphp
              @if($deferredMat && $deferredMat->grossCost() > 0)
                <b style="color:var(--amber)">EGP {{ \App\Support\Money::format($deferredMat->grossCost()) }}</b>
                <div class="muted" style="font-size:10px">آجل بالكامل</div>
              @else
                <span class="muted">—</span>
              @endif
            @elseif($log->amount == 0 && $log->ref_type === 'material_invoice' && $log->ref_id && $log->action !== 'deleted')
              @php $deferredInv = \App\Models\MaterialInvoice::find($log->ref_id); @endphp
              @if($deferredInv && $deferredInv->total_amount > 0)
                <b style="color:var(--amber)">EGP {{ \App\Support\Money::format($deferredInv->total_amount) }}</b>
                <div class="muted" style="font-size:10px">آجل بالكامل</div>
              @else
                <span class="muted">—</span>
              @endif
            @else
              <span class="muted">—</span>
            @endif
          </td>

          <td style="text-align:center;font-weight:600;font-size:13px;color:#475569">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="vertical-align:middle; margin-left:4px;"><use href="#i-users"/></svg>
            {{ $isTransfer ? 'تحويل' : $partyName }}
          </td>

          <td>
            <div style="display:flex;align-items:center;gap:6px;justify-content:center;white-space:nowrap;direction:rtl">
              @if($isTransfer)
                @if($log->direction === 'out')
                  <div style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><use href="#i-grid"/></svg>
                    {{ $accountName }}
                  </div>
                  <div style="color:#94a3b8; font-size:10px; display:flex; align-items:center;">
                    <span style="letter-spacing:-2px;">---</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" style="transform:rotate(180deg)"><use href="#i-arrow"/></svg>
                  </div>
                  <div style="background:#eff6ff;color:#3b82f6;border:1px solid #bfdbfe;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px">
                    {{ str_replace('إلى: ', '', $partyName) }}
                  </div>
                @else
                  <div style="background:#eff6ff;color:#3b82f6;border:1px solid #bfdbfe;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px">
                    {{ str_replace('من: ', '', $partyName) }}
                  </div>
                  <div style="color:#94a3b8; font-size:10px; display:flex; align-items:center;">
                    <span style="letter-spacing:-2px;">---</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" style="transform:rotate(180deg)"><use href="#i-arrow"/></svg>
                  </div>
                  <div style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><use href="#i-grid"/></svg>
                    {{ $accountName }}
                  </div>
                @endif
              @elseif($log->direction === 'in')
                <div style="background:#d1fae5;color:#059669;border:1px solid #a7f3d0;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px">
                  {{ $partyName }}
                </div>
                <div style="color:#94a3b8; font-size:10px; display:flex; align-items:center;">
                  <span style="letter-spacing:-2px;">---</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" style="transform:rotate(180deg)"><use href="#i-arrow"/></svg>
                </div>
                <div style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><use href="#i-grid"/></svg>
                  {{ $accountName }}
                </div>
              @elseif($log->direction === 'out')
                <div style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><use href="#i-grid"/></svg>
                  {{ $accountName }}
                </div>
                <div style="color:#94a3b8; font-size:10px; display:flex; align-items:center;">
                  <span style="letter-spacing:-2px;">---</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" style="transform:rotate(180deg)"><use href="#i-arrow"/></svg>
                </div>
                <div style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px">
                  {{ $partyName }}
                </div>
              @else
                <span class="muted">—</span>
              @endif
            </div>
          </td>

          <td style="max-width:250px">
            <div style="display:flex; gap:8px;">
              <span style="color:#94a3b8;">—</span>
              <div>
                @if($log->ref_type === 'material_invoice' && $log->ref_id)
                  <a href="{{ route('material_invoices.show', $log->ref_id) }}" class="truncate fw-bold" style="text-decoration:none; display:block; color:var(--ink-1)" title="{{ $log->description }}">{{ $log->description ?: '—' }}</a>
                @else
                  <div class="truncate fw-bold" style="color:var(--ink-1)" title="{{ $log->description }}">{{ $log->description ?: '—' }}</div>
                @endif
                @if($log->project)
                  <div style="font-size:11px;color:#64748b;margin-top:2px">
                    <i class="fa fa-folder-open me-1"></i> <a href="{{ route('projects.show', $log->project) }}" class="lk" style="color:inherit">{{ $log->project->name }}</a>
                    @if($log->band) <span class="ms-1">(بند: {{ $log->band->name }})</span> @endif
                  </div>
                @endif
              </div>
            </div>
          </td>

          <td>
            <div style="display:flex;flex-direction:column;gap:2px">
              <div style="font-weight:bold;color:#334155; font-size:12px;">{{ $log->happened_at->format('Y/m/d') }}</div>
              <div class="muted" style="font-size:11px;">{{ $log->happened_at->format('H:i') }}</div>
            </div>
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

{{-- Operation Modal (Deposit / Withdrawal / Transfer) --}}
<div class="modal-overlay" id="operation-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:550px; padding:0; overflow:hidden;">
    <div style="background:var(--ink-1); color:#fff; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
      <h4 style="margin:0; display:flex; align-items:center; gap:8px;">
        <div style="background:rgba(255,255,255,0.1); width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><use href="#i-plus"/></svg>
        </div>
        تنفيذ حركة يدوية (إيداع/صرف/تحويل)
      </h4>
      <button class="btn ghost sm" style="color:#fff;" onclick="document.getElementById('operation-modal').classList.remove('open')">✕</button>
    </div>
    
    <div style="padding:20px;">
      <div style="font-weight:bold; margin-bottom:8px;">نوع الحركة المطلوبة:</div>
      <div style="display:flex; gap:10px; margin-bottom:12px;">
        <button type="button" class="op-type-btn" data-type="withdrawal" onclick="setOpType('withdrawal')" style="flex:1; padding:8px; border:2px solid #ef4444; background:#fef2f2; color:#ef4444; border-radius:8px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-down"/></svg>
          صرف
        </button>
        <button type="button" class="op-type-btn" data-type="deposit" onclick="setOpType('deposit')" style="flex:1; padding:8px; border:2px solid #10b981; background:#fff; color:#10b981; border-radius:8px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="transform:rotate(180deg)"><use href="#i-down"/></svg>
          إيداع
        </button>
        <button type="button" class="op-type-btn" data-type="transfer" onclick="setOpType('transfer')" style="flex:1; padding:8px; border:2px solid #3b82f6; background:#fff; color:#3b82f6; border-radius:8px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-activity"/></svg>
          تحويل
        </button>
      </div>
      
      <div id="op-hint" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; padding:10px 12px; border-radius:8px; font-size:12px; display:flex; gap:8px; margin-bottom:20px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;"><use href="#i-info"/></svg>
        <span>بيتخصم المبلغ من الخزنة + بيتسجل دين على المستلم في المستحقات.</span>
      </div>

      {{-- Form for Deposit & Withdrawal --}}
      <form id="dw-form" method="POST" action="{{ route('wallet.store') }}">
        @csrf
        <input type="hidden" name="kind" id="dw-kind" value="withdrawal">
        <input type="hidden" name="date" value="{{ today()->format('Y-m-d') }}">
        
        <div class="field">
          <label style="color:#ef4444; font-weight:bold;" id="dw-amount-label">المبلغ (ج.م) *</label>
          <input type="number" name="amount" min="0.01" step="0.01" required style="border:1px solid #ef4444;">
        </div>
        
        <div class="field">
          <label style="color:#ef4444; font-weight:bold;" id="dw-wallet-label">سحب من خزنة *</label>
          <select name="account_id" required style="border:1px solid #ef4444;">
            <option value="">اختر الخزنة...</option>
            @foreach($wallets as $w)
              <option value="{{ $w->id }}">{{ $w->name }} ({{ \App\Support\Money::format($w->balance) }})</option>
            @endforeach
          </select>
        </div>
        
        <div class="row2">
          <div class="field">
            <label style="color:#3b82f6; font-weight:bold;" id="dw-party-label">اسم المستلم (هيتسجل عليه مستحق) *</label>
            <input type="text" name="party" id="dw-party-input" required placeholder="الاسم ...">
          </div>
          <div class="field">
            <label style="color:#3b82f6; font-weight:bold;">رقم الهاتف (اختياري)</label>
            <input type="text" name="phone" id="dw-phone-input" placeholder="الرقم (اختياري)...">
          </div>
        </div>
        
        <div class="field">
          <label style="font-weight:bold;">البيان / الملاحظات <span class="muted">(اختياري)</span></label>
          <input type="text" name="description" placeholder="سبب الحركة (اختياري)...">
        </div>
        
        <div class="btn-row" style="margin-top:20px;">
          <button type="submit" class="btn primary" style="background:#10b981; border-color:#10b981; width:100%; justify-content:center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-check"/></svg>
            تنفيذ وإتمام العملية
          </button>
          <button type="button" class="btn ghost" onclick="document.getElementById('operation-modal').classList.remove('open')" style="width:100%; margin-top:8px; justify-content:center;">✕ إلغاء</button>
        </div>
      </form>

      {{-- Form for Transfer --}}
      <form id="tr-form" method="POST" action="{{ route('wallet.transfer') }}" style="display:none;">
        @csrf
        <input type="hidden" name="date" value="{{ today()->format('Y-m-d') }}">
        
        <div class="field">
          <label style="color:#3b82f6; font-weight:bold;">المبلغ (ج.م) *</label>
          <input type="number" name="amount" min="0.01" step="0.01" required style="border:1px solid #3b82f6;">
        </div>
        
        <div class="row2">
          <div class="field">
            <label style="color:#ef4444; font-weight:bold;">سحب من خزنة *</label>
            <select name="from_account_id" required style="border:1px solid #ef4444;">
              <option value="">اختر الخزنة...</option>
              @foreach($wallets as $w)
                <option value="{{ $w->id }}">{{ $w->name }} ({{ \App\Support\Money::format($w->balance) }})</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label style="color:#10b981; font-weight:bold;">إيداع في خزنة *</label>
            <select name="to_account_id" required style="border:1px solid #10b981;">
              <option value="">اختر الخزنة...</option>
              @foreach($wallets as $w)
                <option value="{{ $w->id }}">{{ $w->name }} ({{ \App\Support\Money::format($w->balance) }})</option>
              @endforeach
            </select>
          </div>
        </div>
        
        <div class="field">
          <label style="font-weight:bold;">البيان / الملاحظات <span class="muted">(اختياري)</span></label>
          <input type="text" name="description" placeholder="سبب التحويل (اختياري)...">
        </div>
        
        <div class="btn-row" style="margin-top:20px;">
          <button type="submit" class="btn primary" style="background:#10b981; border-color:#10b981; width:100%; justify-content:center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-check"/></svg>
            تنفيذ وإتمام التحويل
          </button>
          <button type="button" class="btn ghost" onclick="document.getElementById('operation-modal').classList.remove('open')" style="width:100%; margin-top:8px; justify-content:center;">✕ إلغاء</button>
        </div>
      </form>
    </div>
  </div>
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
function openOperationModal() {
  document.getElementById('operation-modal').classList.add('open');
  setOpType('withdrawal'); // Default
}

function setOpType(type) {
  const btns = document.querySelectorAll('.op-type-btn');
  btns.forEach(b => {
    b.style.background = '#fff';
    // Reset borders depending on original color
    if (b.dataset.type === 'withdrawal') { b.style.borderColor = '#ef4444'; b.style.color = '#ef4444'; }
    if (b.dataset.type === 'deposit') { b.style.borderColor = '#10b981'; b.style.color = '#10b981'; }
    if (b.dataset.type === 'transfer') { b.style.borderColor = '#3b82f6'; b.style.color = '#3b82f6'; }
  });
  
  const activeBtn = document.querySelector(`.op-type-btn[data-type="${type}"]`);
  
  const hint = document.getElementById('op-hint');
  const dwForm = document.getElementById('dw-form');
  const trForm = document.getElementById('tr-form');
  
  if (type === 'withdrawal') {
    activeBtn.style.background = '#fef2f2';
    hint.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;"><use href="#i-info"/></svg><span>بيتخصم المبلغ من الخزنة + بيتسجل دين على المستلم في المستحقات.</span>';
    
    document.getElementById('dw-kind').value = 'withdrawal';
    document.getElementById('dw-amount-label').style.color = '#ef4444';
    document.querySelector('#dw-form input[name="amount"]').style.borderColor = '#ef4444';
    
    document.getElementById('dw-wallet-label').innerHTML = 'سحب من خزنة *';
    document.getElementById('dw-wallet-label').style.color = '#ef4444';
    document.querySelector('#dw-form select[name="account_id"]').style.borderColor = '#ef4444';
    
    document.getElementById('dw-party-label').innerHTML = 'اسم المستلم (هيتسجل عليه مستحق) *';
    document.getElementById('dw-party-label').style.color = '#3b82f6';
    
    dwForm.style.display = 'block';
    trForm.style.display = 'none';
  } else if (type === 'deposit') {
    activeBtn.style.background = '#ecfdf5';
    hint.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;"><use href="#i-info"/></svg><span>بيزيد المبلغ في الخزنة + بيتسجل دين عليك للمودع في المستحقات.</span>';
    
    document.getElementById('dw-kind').value = 'deposit';
    document.getElementById('dw-amount-label').style.color = '#10b981';
    document.querySelector('#dw-form input[name="amount"]').style.borderColor = '#10b981';
    
    document.getElementById('dw-wallet-label').innerHTML = 'إيداع في خزنة *';
    document.getElementById('dw-wallet-label').style.color = '#10b981';
    document.querySelector('#dw-form select[name="account_id"]').style.borderColor = '#10b981';
    
    document.getElementById('dw-party-label').innerHTML = 'اسم المودع (هيتسجل ليه دين) *';
    document.getElementById('dw-party-label').style.color = '#3b82f6';
    
    dwForm.style.display = 'block';
    trForm.style.display = 'none';
  } else {
    activeBtn.style.background = '#eff6ff';
    hint.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;"><use href="#i-info"/></svg><span>نقل رصيد بين الخزائن وحسابات البنوك التابعة للشركة.</span>';
    
    dwForm.style.display = 'none';
    trForm.style.display = 'block';
  }
}

// Modify dw-form onsubmit to handle phone number logic securely
document.getElementById('dw-form').addEventListener('submit', function(e) {
  const phoneInput = document.getElementById('dw-phone-input');
  const partyInput = document.getElementById('dw-party-input');
  const phone = phoneInput ? phoneInput.value.trim() : '';
  
  if (phone && !partyInput.value.includes(phone)) {
    partyInput.value = partyInput.value.trim() + ' (' + phone + ')';
  }
});

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
