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

<style>
  .radar-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
  @media(max-width: 1024px) { .radar-stats-grid { grid-template-columns: repeat(2, 1fr); } }
  @media(max-width: 640px) { .radar-stats-grid { grid-template-columns: 1fr; } }
  
  .radar-header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
  @media(max-width: 640px) { .radar-header-actions { flex-direction: column; align-items: stretch; text-align: center; } .radar-header-actions > div { text-align: center !important; } .radar-header-actions .btn { width: 100%; justify-content: center; } }
</style>

{{-- Top Action Bar & Stats --}}
<div class="no-print" style="margin-bottom:20px;">
  <div class="radar-header-actions" style="background: #fff; border-radius: 16px; padding: 24px 28px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
    <div style="text-align:right;">
      <h3 style="margin:0; font-size:20px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:12px;">
        <div style="background: #eff6ff; color: #3b82f6; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="22" height="22"><use href="#i-zap"/></svg>
        </div>
        تنفيذ حركة يدوية
      </h3>
      <div style="font-size:13.5px; color:#64748b; margin-top:12px; font-weight:600; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
        <span style="color:#059669; background:#d1fae5; padding:4px 10px; border-radius:8px;">إيداع</span> يتسجل كدين عليك للمودع 
        <span style="color:#cbd5e1; margin:0 4px;">|</span>
        <span style="color:#e11d48; background:#ffe4e6; padding:4px 10px; border-radius:8px;">صرف</span> يتسجل كمستحق على المستلم 
        <span style="color:#cbd5e1; margin:0 4px;">|</span>
        <span style="color:#2563eb; background:#dbeafe; padding:4px 10px; border-radius:8px;">تحويل</span> بين المحافظ
      </div>
    </div>
    <button class="btn primary" onclick="openOperationModal()" style="font-size:16px; font-weight:bold; padding:14px 28px; border-radius:12px; background:#10b981; border-color:#10b981; color:#fff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
      <i class="fa fa-plus" style="margin-inline-end:8px;"></i> بدء العملية
    </button>
  </div>

  <div class="grid radar-stats-grid">
    {{-- Incoming --}}
    <div class="vstat radar-stat-green">
      <div class="top"><span class="label">التدفقات الداخلة</span>
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-down"/></svg></span>
      </div>
      <div class="val tnum">{{ \App\Support\Money::format($totalIn) }} <small>ج.م</small></div>
      <div class="note" style="line-height:1.4; opacity:0.85; margin-top:8px;">
        كل فلوس دخلت الحسابات: إيرادات فعلية + تسويات.
      </div>
      <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-down"/></svg>
    </div>

    {{-- Outgoing --}}
    <div class="vstat radar-stat-red">
      <div class="top"><span class="label">التدفقات الخارجة</span>
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-activity"/></svg></span>
      </div>
      <div class="val tnum">{{ \App\Support\Money::format($totalOut) }} <small>ج.م</small></div>
      <div class="note" style="line-height:1.4; opacity:0.85; margin-top:8px;">
        كل فلوس خرجت من الحسابات: مصروفات، رواتب، خصومات، وغيرها.
      </div>
      <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-activity"/></svg>
    </div>

    {{-- Transfers --}}
    <div class="vstat radar-stat-blue">
      <div class="top"><span class="label">إجمالي التحويلات</span>
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-activity"/></svg></span>
      </div>
      <div class="val tnum">{{ \App\Support\Money::format($totalTransfers) }} <small>ج.م</small></div>
      <div class="note" style="line-height:1.4; opacity:0.85; margin-top:8px;">
        فلوس انتقلت بين حسابات الشركة نفسها (مثلاً من خزنة لمحفظة).
      </div>
      <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-activity"/></svg>
    </div>

    {{-- Canceled --}}
    <div class="vstat radar-stat-gray">
      <div class="top"><span class="label">عمليات ملغاة</span>
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-pie-chart"/></svg></span>
      </div>
      <div class="val tnum">{{ $canceledCount }} <small>حركة</small></div>
      <div class="note" style="line-height:1.4; opacity:0.85; margin-top:8px;">
        عدد الحركات اليدوية التي تم إلغاؤها في نفس الفترة.
      </div>
      <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-pie-chart"/></svg>
    </div>
  </div>
</div>

<div class="form-card no-print" style="margin-bottom: 24px; padding: 20px; border-radius:16px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.02); background:#fff;">
  <form method="GET" action="{{ route('radar.index') }}" id="filter-form">
    
    <div style="border-bottom:1px solid #e2e8f0; padding-bottom:16px; margin-bottom:20px;">
      <h3 style="margin:0; font-size:18px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20" style="color:#3b82f6"><use href="#i-list"/></svg>
        سجل الحركات التفصيلي
      </h3>
    </div>
    
    <div style="display:flex; gap:16px; align-items:center; width:100%; flex-wrap:wrap;">
      
      <div class="field" style="margin: 0; flex: 1; min-width: 180px;">
        <select name="action" onchange="this.form.submit()" style="width:100%; height:44px; padding-top:0; padding-bottom:0; background-color:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; color:#0f172a; font-weight:600; font-size:13.5px;">
          <option value="">كل الإجراءات</option>
          <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>إنشاء (Created)</option>
          <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>تعديل (Updated)</option>
          <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>حذف / إلغاء (Deleted)</option>
        </select>
      </div>

      <div class="field" style="margin: 0; flex: 1; min-width: 180px;">
        <select name="user_id" onchange="this.form.submit()" style="width:100%; height:44px; padding-top:0; padding-bottom:0; background-color:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; color:#0f172a; font-weight:600; font-size:13.5px;">
          <option value="">كل المستخدمين</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="radar-ranges" style="display:flex; gap:6px; align-items:center; background:#f1f5f9; padding:6px; border-radius:12px; border: 1px solid #e2e8f0; flex: 2; min-width: 100%; flex-wrap: wrap;">
        @php $ranges = ['all' => 'الكل', 'today' => 'اليوم', 'yesterday' => 'أمس', 'month' => 'الشهر ده']; @endphp
        @foreach($ranges as $val => $label)
          <label style="margin:0; cursor:pointer; flex:1;">
            <input type="radio" name="period" value="{{ $val }}" style="display:none;" onchange="document.getElementById('filter-form').submit()" {{ $period === $val ? 'checked' : '' }}>
            <div style="text-align:center; padding:8px 0; border-radius:8px; font-size:13px; font-weight:bold; transition:all 0.2s; {{ $period === $val ? 'background:#3b82f6; color:#fff; box-shadow:0 2px 8px rgba(59,130,246,0.35);' : 'color:#475569;' }}">
              {{ $label }}
            </div>
          </label>
        @endforeach
        <label style="margin:0; cursor:pointer; flex:1;">
          <input type="radio" name="period" value="custom" style="display:none;" onchange="document.getElementById('filter-form').submit()" {{ $period === 'custom' ? 'checked' : '' }}>
          <div style="text-align:center; padding:8px 0; border-radius:8px; font-size:13px; font-weight:bold; transition:all 0.2s; {{ $period === 'custom' ? 'background:#3b82f6; color:#fff; box-shadow:0 2px 8px rgba(59,130,246,0.35);' : 'color:#475569;' }}">
            تصفية <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12" style="margin-right:2px; vertical-align:middle;"><use href="#i-filter"/></svg>
          </div>
        </label>
      </div>
      
      <div id="custom-dates" style="display: {{ $period === 'custom' ? 'flex' : 'none' }}; gap: 10px; flex: 1.5; min-width: 100%; flex-wrap: wrap;">
        <input type="date" name="date_to" value="{{ request('date_to') }}" style="flex:1; min-width:130px; height:44px; padding:0 12px; font-size:13.5px; border-radius:10px; border:1px solid #cbd5e1; background:#f8fafc; color:#0f172a;" placeholder="إلى تاريخ">
        <input type="date" name="date_from" value="{{ request('date_from') }}" style="flex:1; min-width:130px; height:44px; padding:0 12px; font-size:13.5px; border-radius:10px; border:1px solid #cbd5e1; background:#f8fafc; color:#0f172a;" placeholder="من تاريخ">
        <button type="submit" class="btn primary" style="height:44px; padding:0 24px; border-radius:10px; font-weight:bold; background:#3b82f6; border-color:#3b82f6; color:#fff; font-size:14px; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">تطبيق</button>
      </div>
    </div>
  </form>
</div>

<div class="table-wrap" style="box-shadow:none; border:none; overflow-x:auto;">
  <table class="table" style="min-width:1000px">
    <thead>
      <tr>
        <th style="padding-right:20px; width: 60px;">#</th>
        <th style="width: 150px;">نوع الحركة</th>
        <th class="num" style="width: 140px;">المبلغ</th>
        <th style="text-align:center; width: 160px;">الطرف الخارجي</th>
        <th style="text-align:center; width: 220px;">مسار الحركة (FLOW)</th>
        <th style="width: auto; min-width: 280px;">البيان والمشروع</th>
        <th style="width: 110px;">تاريخ التنفيذ</th>
        <th class="no-print" style="width: 90px;">إجراء</th>
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
            @if($log->amount > 0 && $log->discount > 0)
              <b style="color:{{ $isTransfer ? '#3b82f6' : ($log->direction === 'in' ? '#10b981' : ($log->direction === 'out' ? '#ef4444' : 'inherit')) }}; {{ $log->action === 'deleted' ? 'text-decoration:line-through' : '' }}">
                EGP {{ \App\Support\Money::format($log->amount) }} {{ $log->direction === 'in' ? '+' : ($log->direction === 'out' ? '-' : '') }}
              </b>
              <div class="muted" style="font-size:11px; color:#10b981; margin-top:2px; font-weight:bold;">
                 + تسوية: {{ \App\Support\Money::format($log->discount) }}
              </div>
            @elseif($log->amount > 0)
              <b style="color:{{ $isTransfer ? '#3b82f6' : ($log->direction === 'in' ? '#10b981' : ($log->direction === 'out' ? '#ef4444' : 'inherit')) }}; {{ $log->action === 'deleted' ? 'text-decoration:line-through' : '' }}">
                EGP {{ \App\Support\Money::format($log->amount) }} {{ $log->direction === 'in' ? '+' : ($log->direction === 'out' ? '-' : '') }}
              </b>
            @elseif($log->amount == 0 && $log->discount > 0)
              <b style="color:#10b981; {{ $log->action === 'deleted' ? 'text-decoration:line-through' : '' }}">
                EGP {{ \App\Support\Money::format($log->discount) }} (تسوية فقط)
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

          <td style="padding: 12px 16px;">
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
  <div class="modal-box" style="max-width:550px">
    <div class="modal-head">
      <h4 style="margin:0; display:flex; align-items:center; gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><use href="#i-zap"/></svg>
        تنفيذ حركة يدوية
      </h4>
      <button class="btn ghost sm" onclick="document.getElementById('operation-modal').classList.remove('open')">✕</button>
    </div>
    
    <div class="modal-body">
      <div style="font-weight:bold; margin-bottom:8px; font-size: 13px;">نوع الحركة:</div>
      <div style="display:flex; gap:10px; margin-bottom:12px;">
        <button type="button" class="op-type-btn btn" data-type="withdrawal" onclick="setOpType('withdrawal')" style="flex:1;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-down"/></svg>
          صرف
        </button>
        <button type="button" class="op-type-btn btn ghost" data-type="deposit" onclick="setOpType('deposit')" style="flex:1;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="transform:rotate(180deg)"><use href="#i-down"/></svg>
          إيداع
        </button>
        <button type="button" class="op-type-btn btn ghost" data-type="transfer" onclick="setOpType('transfer')" style="flex:1;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-activity"/></svg>
          تحويل
        </button>
      </div>
      
      <div id="op-hint" style="background:var(--ink-1); color:#fff; padding:10px 12px; border-radius:8px; font-size:12px; display:flex; gap:8px; margin-bottom:20px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;"><use href="#i-info"/></svg>
        <span>بيتخصم المبلغ من الخزنة + بيتسجل دين على المستلم في المستحقات.</span>
      </div>

      {{-- Form for Deposit & Withdrawal --}}
      <form id="dw-form" method="POST" action="{{ route('wallet.store') }}">
        @csrf
        <input type="hidden" name="kind" id="dw-kind" value="withdrawal">
        <input type="hidden" name="date" value="{{ today()->format('Y-m-d') }}">
        
        <div class="field">
          <label id="dw-amount-label">المبلغ (ج.م) *</label>
          <input type="number" name="amount" min="0.01" step="0.01" required>
        </div>
        
        <div class="field">
          <label id="dw-wallet-label">سحب من خزنة *</label>
          <select name="account_id" required>
            <option value="">اختر الخزنة...</option>
            @foreach($wallets as $w)
              <option value="{{ $w->id }}">{{ $w->name }} ({{ \App\Support\Money::format($w->balance) }})</option>
            @endforeach
          </select>
        </div>
        
        <div class="row2">
          <div class="field">
            <label id="dw-party-label">اسم المستلم (هيتسجل عليه مستحق) *</label>
            <input type="text" name="party" id="dw-party-input" required placeholder="الاسم ...">
          </div>
          <div class="field">
            <label>رقم الهاتف (اختياري)</label>
            <input type="text" name="phone" id="dw-phone-input" placeholder="الرقم (اختياري)...">
          </div>
        </div>
        
        <div class="field">
          <label>البيان / الملاحظات <span class="muted">(اختياري)</span></label>
          <input type="text" name="description" placeholder="سبب الحركة (اختياري)...">
        </div>
        
        <div class="btn-row" style="margin-top:20px;">
          <button type="submit" class="btn primary" id="dw-submit-btn" style="width:100%; justify-content:center; transition: all 0.2s">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-check"/></svg>
            تنفيذ العملية
          </button>
        </div>
      </form>

      {{-- Form for Transfer --}}
      <form id="tr-form" method="POST" action="{{ route('wallet.transfer') }}" style="display:none;">
        @csrf
        <input type="hidden" name="date" value="{{ today()->format('Y-m-d') }}">
        
        <div class="field">
          <label>المبلغ (ج.م) *</label>
          <input type="number" name="amount" min="0.01" step="0.01" required>
        </div>
        
        <div class="row2">
          <div class="field">
            <label>سحب من خزنة *</label>
            <select name="from_account_id" required>
              <option value="">اختر الخزنة...</option>
              @foreach($wallets as $w)
                <option value="{{ $w->id }}">{{ $w->name }} ({{ \App\Support\Money::format($w->balance) }})</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label>إيداع في خزنة *</label>
            <select name="to_account_id" required>
              <option value="">اختر الخزنة...</option>
              @foreach($wallets as $w)
                <option value="{{ $w->id }}">{{ $w->name }} ({{ \App\Support\Money::format($w->balance) }})</option>
              @endforeach
            </select>
          </div>
        </div>
        
        <div class="field">
          <label>البيان / الملاحظات <span class="muted">(اختياري)</span></label>
          <input type="text" name="description" placeholder="سبب التحويل (اختياري)...">
        </div>
        
        <div class="btn-row" style="margin-top:20px;">
          <button type="submit" class="btn primary" id="tr-submit-btn" style="width:100%; justify-content:center; transition: all 0.2s">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-check"/></svg>
            تنفيذ التحويل
          </button>
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
    b.classList.remove('primary');
    b.classList.add('ghost');
    b.style.backgroundColor = '';
    b.style.borderColor = '';
    b.style.color = '';
  });
  
  const activeBtn = document.querySelector(`.op-type-btn[data-type="${type}"]`);
  const dwSubmit = document.getElementById('dw-submit-btn');
  const trSubmit = document.getElementById('tr-submit-btn');
  
  if (activeBtn) {
    activeBtn.classList.remove('ghost');
    activeBtn.classList.add('primary');
    if (type === 'deposit') {
      activeBtn.style.background = '#059669'; // Green
      activeBtn.style.borderColor = '#059669';
      activeBtn.style.color = '#fff';
      if(dwSubmit) {
        dwSubmit.style.background = '#059669';
        dwSubmit.style.borderColor = '#059669';
      }
    } else if (type === 'withdrawal') {
      activeBtn.style.background = '#dc2626'; // Red
      activeBtn.style.borderColor = '#dc2626';
      activeBtn.style.color = '#fff';
      if(dwSubmit) {
        dwSubmit.style.background = '#dc2626';
        dwSubmit.style.borderColor = '#dc2626';
      }
    } else {
      activeBtn.style.background = '#1d4ed8'; // Blue
      activeBtn.style.borderColor = '#1d4ed8';
      activeBtn.style.color = '#fff';
      if(trSubmit) {
        trSubmit.style.background = '#1d4ed8';
        trSubmit.style.borderColor = '#1d4ed8';
      }
    }
  }
  
  const hint = document.getElementById('op-hint');
  const dwForm = document.getElementById('dw-form');
  const trForm = document.getElementById('tr-form');
  
  if (type === 'withdrawal') {
    hint.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;"><use href="#i-info"/></svg><span>بيتخصم المبلغ من الخزنة + بيتسجل دين على المستلم في المستحقات.</span>';
    
    document.getElementById('dw-kind').value = 'withdrawal';
    document.getElementById('dw-wallet-label').innerHTML = 'سحب من خزنة *';
    document.getElementById('dw-party-label').innerHTML = 'اسم المستلم (هيتسجل عليه مستحق) *';
    
    dwForm.style.display = 'block';
    trForm.style.display = 'none';
  } else if (type === 'deposit') {
    hint.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;"><use href="#i-info"/></svg><span>بيزيد المبلغ في الخزنة + بيتسجل دين عليك للمودع في المستحقات.</span>';
    
    document.getElementById('dw-kind').value = 'deposit';
    document.getElementById('dw-wallet-label').innerHTML = 'إيداع في خزنة *';
    document.getElementById('dw-party-label').innerHTML = 'اسم المودع (هيتسجل ليه دين) *';
    
    dwForm.style.display = 'block';
    trForm.style.display = 'none';
  } else {
    hint.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;"><use href="#i-info"/></svg><span>نقل رصيد بين الخزائن وحسابات البنوك التابعة للشركة.</span>';
    
    dwForm.style.display = 'none';
    trForm.style.display = 'block';
  }
}

// Modify dw-form onsubmit to handle phone number logic securely and prevent double submission
document.getElementById('dw-form').addEventListener('submit', function(e) {
  const phoneInput = document.getElementById('dw-phone-input');
  const partyInput = document.getElementById('dw-party-input');
  const phone = phoneInput ? phoneInput.value.trim() : '';
  
  if (phone && !partyInput.value.includes(phone)) {
    partyInput.value = partyInput.value.trim() + ' (' + phone + ')';
  }

  const btn = document.getElementById('dw-submit-btn');
  if (btn) {
    // Small timeout to allow form submission to proceed before disabling
    setTimeout(() => {
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> جاري التنفيذ...';
    }, 10);
  }
});

document.getElementById('tr-form').addEventListener('submit', function(e) {
  const btn = document.getElementById('tr-submit-btn');
  if (btn) {
    setTimeout(() => {
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> جاري التنفيذ...';
    }, 10);
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
  .radar-stats-grid {
    gap: 20px !important;
  }
  .radar-stats-grid .vstat {
    padding: 18px 20px;
    border-radius: 16px;
    min-height: 110px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid rgba(255,255,255,0.15);
    position: relative;
    overflow: hidden;
  }
  .radar-stats-grid .vstat .val {
    font-size: 24px;
    font-weight: 800;
    margin-top: 6px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  .radar-stats-grid .vstat .val small {
    font-size: 13px;
    opacity: 0.9;
  }
  .radar-stats-grid .vstat .label {
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.3px;
  }
  .radar-stats-grid .vstat .note {
    font-size: 11.5px;
    margin-top: 6px;
    line-height: 1.4 !important;
    opacity: 0.9;
    max-width: 90%;
  }
  .radar-stats-grid .vstat .ic {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(255,255,255,0.25);
  }
  .radar-stats-grid .vstat .ic svg {
    width: 18px;
    height: 18px;
  }
  
  /* Fix the huge inline SVG taking up space */
  .radar-stats-grid .vstat .vstat-bg {
    position: absolute;
    bottom: -15px;
    left: -15px;
    width: 100px;
    height: 100px;
    opacity: 0.15;
    pointer-events: none;
    z-index: 0;
    transform: rotate(-10deg);
  }
  
  .radar-stat-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .radar-stat-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
  .radar-stat-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
  .radar-stat-gray { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }

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
