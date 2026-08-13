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

<div class="form-card no-print" style="max-width: 100%; margin-bottom: 24px; padding: 20px; border-radius:16px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.02); background:#fff;">
  <form method="GET" action="{{ route('radar.index') }}" id="filter-form">
    
    <div style="border-bottom:1px solid #e2e8f0; padding-bottom:16px; margin-bottom:20px;">
      <h3 style="margin:0; font-size:18px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20" style="color:#3b82f6"><use href="#i-list"/></svg>
        سجل الحركات التفصيلي
      </h3>
    </div>
    
    <div style="display:flex; gap:16px; align-items:center; width:100%; flex-wrap:wrap;">
      
      <div class="field" style="margin: 0; flex: 1; min-width: 250px;">
        <input type="text" name="q" value="{{ request('q') }}" class="radar-filter-input" placeholder="بحث عام (بالاسم، البيان، الخ)..." oninput="clearTimeout(window.searchTimeout); window.searchTimeout = setTimeout(() => this.form.submit(), 600)">
      </div>

      <div class="field" style="margin: 0; flex: 1; min-width: 180px;">
        <select name="action" class="radar-filter-input" onchange="this.form.submit()">
          <option value="">كل الإجراءات</option>
          <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>إنشاء (Created)</option>
          <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>تعديل (Updated)</option>
          <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>حذف / إلغاء (Deleted)</option>
        </select>
      </div>

      <div class="field" style="margin: 0; flex: 1; min-width: 180px;">
        <select name="flow_type" class="select2-filter" onchange="this.form.submit()">
          <option value="">كل التدفقات</option>
          <option value="in" {{ request('flow_type') === 'in' ? 'selected' : '' }}>وارد</option>
          <option value="out" {{ request('flow_type') === 'out' ? 'selected' : '' }}>صادر</option>
          <option value="transfer" {{ request('flow_type') === 'transfer' ? 'selected' : '' }}>تحويل</option>
        </select>
      </div>

      <div class="field" style="margin: 0; flex: 1; min-width: 180px;">
        <select name="project_id" class="select2-filter" onchange="this.form.submit()">
          <option value="">كل المشاريع</option>
          @foreach($projects as $project)
            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="field" style="margin: 0; flex: 1; min-width: 180px;">
        <select name="user_id" class="select2-filter" onchange="this.form.submit()">
          <option value="">كل المستخدمين</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="field" style="margin: 0; flex: 1; min-width: 180px;">
        <select name="sort" class="radar-filter-input" onchange="this.form.submit()">
          <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>الأحدث أولاً (الافتراضي)</option>
          <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>الأقدم أولاً</option>
          <option value="amount_desc" {{ request('sort') === 'amount_desc' ? 'selected' : '' }}>المبلغ (الأعلى للأقل)</option>
          <option value="amount_asc" {{ request('sort') === 'amount_asc' ? 'selected' : '' }}>المبلغ (الأقل للأعلى)</option>
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
          <div style="display:flex; align-items:center; justify-content:center; gap:4px; padding:8px 0; border-radius:8px; font-size:13px; font-weight:bold; transition:all 0.2s; {{ $period === 'custom' ? 'background:#3b82f6; color:#fff; box-shadow:0 2px 8px rgba(59,130,246,0.35);' : 'color:#475569;' }}">
            تاريخ محدد
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><use href="#i-filter"/></svg>
          </div>
        </label>
      </div>
      
      <div id="custom-dates" style="display: {{ $period === 'custom' ? 'flex' : 'none' }}; gap: 12px; margin-top: 10px; flex: 1.5; min-width: 100%; flex-wrap: wrap;">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="radar-filter-input" style="flex:1; min-width:130px; margin:0;" placeholder="من تاريخ" onchange="if(this.value) this.form.submit()">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="radar-filter-input" style="flex:1; min-width:130px; margin:0;" placeholder="إلى تاريخ" onchange="if(this.value) this.form.submit()">
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
          
          $matGrossCost = null;
          if (in_array($log->ref_type, ['material', 'material_invoice', 'worker_payment'])) {
            $ovGross = is_string($log->old_values) ? json_decode($log->old_values, true) : $log->old_values;
            
            if (is_array($ovGross) && isset($ovGross['gross_amount'])) {
              $matGrossCost = (float) $ovGross['gross_amount'];
            } elseif ($log->ref_id && $log->action !== 'deleted') {
              if ($log->ref_type === 'material') {
                $txMat = \App\Models\Material::find($log->ref_id);
                if ($txMat) $matGrossCost = $txMat->grossCost();
              } elseif ($log->ref_type === 'material_invoice') {
                $txInv = \App\Models\MaterialInvoice::find($log->ref_id);
                if ($txInv) $matGrossCost = (float) $txInv->total_amount;
              } elseif ($log->ref_type === 'worker_payment') {
                // For worker payment, the amount IS the cash paid.
                // But wait, there is no total invoice for a worker payment!
                // Maybe the user wants it to say (آجل) if amount is 0?
                // For worker payment, we don't have a gross_cost, just the discount if any.
                // If amount is 0, we can just say "آجل" without a gross cost, or we can use the discount as gross cost?
              }
            }
            
            if ($matGrossCost === null && $log->transaction_id) {
                // Fallback: look for gross_amount in another log for the same transaction (e.g. if the model is deleted)
                $relatedLog = \App\Models\AuditLog::where('transaction_id', $log->transaction_id)
                    ->whereNotNull('old_values')
                    ->where('old_values', 'like', '%"gross_amount"%')
                    ->first();
                if ($relatedLog) {
                    $relatedOv = is_string($relatedLog->old_values) ? json_decode($relatedLog->old_values, true) : $relatedLog->old_values;
                    if (is_array($relatedOv) && isset($relatedOv['gross_amount'])) {
                        $matGrossCost = (float) $relatedOv['gross_amount'];
                    }
                }
            }
          }
          
          $deletionEffects = [];
          if ($log->action === 'deleted') {
              if ($log->amount > 0) {
                  $formattedAmt = \App\Support\Money::format($log->amount);
                  $acc = \App\Models\Account::nameOf($log->account_id);
                  if ($log->direction === 'out') {
                      $deletionEffects[] = "تم استرداد مبلغ {$formattedAmt} ج.م وإعادته إلى ({$acc}).";
                  } elseif ($log->direction === 'in') {
                      $deletionEffects[] = "تم سحب مبلغ {$formattedAmt} ج.م من ({$acc}) لعكس الحركة.";
                  }
              }
              
              if (in_array($log->ref_type, ['material', 'material_invoice'])) {
                  $gross = $matGrossCost ?? 0;
                  $formattedGross = \App\Support\Money::format($gross);
                  $debtCancelled = $gross - $log->amount;
                  
                  if ($matGrossCost === null && $log->amount == 0) {
                      $deletionEffects[] = "تم إلغاء فاتورة شراء آجلة بالكامل.";
                  } elseif ($matGrossCost === null) {
                      $deletionEffects[] = "تم إلغاء فاتورة الشراء بالكامل.";
                  } elseif ($debtCancelled > 0.01 && $log->amount > 0) {
                      $formattedDebt = \App\Support\Money::format($debtCancelled);
                      $deletionEffects[] = "تم إلغاء الفاتورة (إجمالي {$formattedGross} ج.م)، وسقوط المديونية المتبقية بقيمة {$formattedDebt} ج.م.";
                  } elseif ($debtCancelled > 0.01 && $log->amount == 0) {
                      $deletionEffects[] = "تم إلغاء فاتورة آجلة بالكامل، وسقوط المديونية بقيمة {$formattedGross} ج.م.";
                  } elseif ($gross > 0) {
                      $deletionEffects[] = "تم إلغاء الفاتورة بالكامل (بقيمة {$formattedGross} ج.م).";
                  }
              } elseif ($log->ref_type === 'debt') {
                  $debtReinstated = $log->amount + $log->discount;
                  $formattedDebt = \App\Support\Money::format($debtReinstated);
                  $deletionEffects[] = "تم التراجع عن سداد المديونية، وعودة دين بقيمة {$formattedDebt} ج.م على الشركة.";
              } elseif ($log->ref_type === 'worker_payment') {
                  if ($log->amount > 0) {
                      $formattedAmt = \App\Support\Money::format($log->amount);
                      $deletionEffects[] = "تم التراجع عن دفعة الصنايعي، وعودة مستحقاته بقيمة {$formattedAmt} ج.م.";
                  }
              }
              
              if ($log->discount > 0) {
                  $formattedDisc = \App\Support\Money::format($log->discount);
                  $deletionEffects[] = "تم إلغاء تسوية/خصم كان بقيمة {$formattedDisc} ج.م.";
              }
          }
        @endphp
        @php
          $logData = [
             'id' => $log->transaction_id ?? $log->id,
             'action' => $meta['label'],
             'type' => $isTransfer ? 'تحويل' : ($log->direction === 'in' ? 'تدفق داخل' : ($log->direction === 'out' ? 'تدفق خارج' : '—')),
             'amount' => \App\Support\Money::format($log->amount),
             'discount' => $log->discount > 0 ? \App\Support\Money::format($log->discount) : 0,
             'direction' => $log->direction,
             'party' => $isTransfer ? 'تحويل' : $partyName,
             'project' => $log->project ? $log->project->name : 'عام / غير محدد',
             'band' => $log->band ? $log->band->name : '',
             'description' => $log->description ?: '—',
             'date' => $log->happened_at->format('Y-m-d H:i A'),
             'user' => $log->performedBy ? $log->performedBy->name : 'النظام',
             'account' => $accountName,
             'color' => $isTransfer ? '#3b82f6' : ($log->direction === 'in' ? '#10b981' : ($log->direction === 'out' ? '#ef4444' : '#64748b')),
             'action_type' => $log->action,
             'gross_amount' => $matGrossCost !== null && round($matGrossCost, 2) > round($log->amount, 2) ? \App\Support\Money::format($matGrossCost) : null,
             'deletion_effects' => $deletionEffects,
          ];
          $ovRaw = is_string($log->old_values) ? json_decode($log->old_values, true) : $log->old_values;
          $ov = [];
          $nv = [];
          if (is_array($ovRaw)) {
              foreach ($ovRaw as $k => $v) {
                  if ($k === 'account_id') {
                      $ov['account'] = \App\Models\Account::nameOf($v);
                      $nv['account'] = \App\Models\Account::nameOf($log->account_id);
                  }
                  elseif ($k === 'project_id') {
                      $ov['project'] = $v ? \App\Models\Project::find($v)?->name : 'عام / غير محدد';
                      $nv['project'] = $log->project_id ? \App\Models\Project::find($log->project_id)?->name : 'عام / غير محدد';
                  }
                  elseif ($k === 'band_id') {
                      $ov['band'] = $v ? \App\Models\Band::find($v)?->name : '—';
                      $nv['band'] = $log->band_id ? \App\Models\Band::find($log->band_id)?->name : '—';
                  }
                  elseif ($k === 'amount') {
                      $ov['amount'] = \App\Support\Money::format($v);
                      $nv['amount'] = \App\Support\Money::format($log->amount);
                  }
                  elseif ($k === 'discount') {
                      $ov['discount'] = \App\Support\Money::format($v);
                      $nv['discount'] = \App\Support\Money::format($log->discount);
                  }
                  elseif ($k === 'happened_at' || $k === 'date') {
                      $ov['date'] = \Carbon\Carbon::parse($v)->format('Y-m-d H:i A');
                      $nv['date'] = \Carbon\Carbon::parse($log->happened_at)->format('Y-m-d H:i A');
                  }
                  elseif ($k === 'description') {
                      $ov['description'] = $v;
                      $nv['description'] = $log->description;
                  }
                  elseif ($k === 'party') {
                      $ov['party'] = $v;
                      $nv['party'] = $log->party;
                  }
                  elseif ($k === 'type') {
                      $ov['type'] = $v;
                      $nv['type'] = $log->type;
                  }
                  elseif ($k === 'direction') {
                      $ov['direction'] = $v === 'in' ? 'تدفق داخل' : ($v === 'out' ? 'تدفق خارج' : $v);
                      $nv['direction'] = $log->direction === 'in' ? 'تدفق داخل' : ($log->direction === 'out' ? 'تدفق خارج' : $log->direction);
                  }
              }
          }
          $logData['old_values'] = $ov;
          $logData['new_values'] = $nv;
        @endphp
        <tr style="cursor: pointer; {{ $log->action === 'deleted' ? 'background:repeating-linear-gradient(45deg, #fffafa, #fffafa 10px, #fff5f5 10px, #fff5f5 20px); opacity: 0.8;' : 'transition: background 0.2s;' }}" 
            onmouseover="this.style.background='#f8fafc'" 
            onmouseout="this.style.background='{{ $log->action === 'deleted' ? 'repeating-linear-gradient(45deg, #fffafa, #fffafa 10px, #fff5f5 10px, #fff5f5 20px)' : 'transparent' }}'"
            onclick="if(!event.target.closest('button') && !event.target.closest('a')) openLogDetails(this)" data-log="{{ json_encode($logData) }}">
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
                <div style="font-size:11px;color:#64748b;margin-bottom:4px;">{{ $isTransfer ? 'تحويل' : ($log->direction === 'in' ? 'تدفق داخل' : ($log->direction === 'out' ? 'تدفق خارج' : '—')) }}</div>
                <span class="tag {{ $log->action === 'created' ? 'green' : ($log->action === 'deleted' ? 'red' : 'amber') }} sm" style="font-size: 10px; padding: 2px 6px;">{{ $log->actionAr() }}</span>
              </div>
            </div>
          </td>

          <td class="num">

            @if($matGrossCost !== null && round($matGrossCost, 2) > round($log->amount, 2))
              <b style="color:var(--amber); {{ $log->action === 'deleted' ? 'text-decoration:line-through; color:red !important;' : '' }}">EGP {{ \App\Support\Money::format($matGrossCost) }}</b>
              <div class="muted" style="font-size:10px; margin-top:2px; font-weight:bold;">
                @if($log->amount == 0)
                  (آجل بالكامل)
                @else
                  (آجل، تم دفع {{ \App\Support\Money::format($log->amount) }})
                @endif
              </div>
            @elseif($log->amount > 0 && $log->discount > 0)
              <b style="color:{{ $isTransfer ? '#3b82f6' : ($log->direction === 'in' ? '#10b981' : ($log->direction === 'out' ? '#ef4444' : 'inherit')) }}; {{ $log->action === 'deleted' ? 'text-decoration:line-through; color:red !important;' : '' }}">
                EGP {{ \App\Support\Money::format($log->amount) }} {{ $log->direction === 'in' ? '+' : ($log->direction === 'out' ? '-' : '') }}
              </b>
              <div class="muted" style="font-size:11px; color:#10b981; margin-top:2px; font-weight:bold;">
                 + تسوية: {{ \App\Support\Money::format($log->discount) }}
              </div>
            @elseif($log->amount > 0)
              <b style="color:{{ $isTransfer ? '#3b82f6' : ($log->direction === 'in' ? '#10b981' : ($log->direction === 'out' ? '#ef4444' : 'inherit')) }}; {{ $log->action === 'deleted' ? 'text-decoration:line-through; color:red !important;' : '' }}">
                EGP {{ \App\Support\Money::format($log->amount) }} {{ $log->direction === 'in' ? '+' : ($log->direction === 'out' ? '-' : '') }}
              </b>
            @elseif($log->amount == 0 && $log->discount > 0)
              <b style="color:#10b981; {{ $log->action === 'deleted' ? 'text-decoration:line-through; color:red !important;' : '' }}">
                EGP {{ \App\Support\Money::format($log->discount) }} (تسوية فقط)
              </b>
            @else
              <span class="muted">—</span>
            @endif
          </td>

          <td>
            <div style="display:flex;align-items:center;justify-content:center;gap:6px;font-weight:600;font-size:13px;color:#475569;white-space:nowrap;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><use href="#i-users"/></svg>
              <span>{{ $isTransfer ? 'تحويل' : $partyName }}</span>
            </div>
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
                  onclick="openDeleteModal('/transactions/{{ $log->transaction_id }}', '{{ addslashes($meta['label']) }}', '{{ \App\Support\Money::format($log->amount) }}', {{ $isSafe ? 'false' : 'true' }})">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
                </button>
              </div>
            @elseif($log->transaction_id === null && $log->ref_type === 'asset' && $log->action !== 'deleted' && in_array($log->type, ['إهلاك أصل', 'شراء أصل (دفتري)']))
              <div style="display:flex;gap:4px">
                <button type="button" class="btn ghost sm danger" title="عكس إجراء الأصل"
                  onclick="openDeleteModal('/radar/{{ $log->id }}/revert', '{{ addslashes($meta['label'] . ' - ' . $log->type) }}', '{{ \App\Support\Money::format($log->amount) }}', true)">
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

{{-- Log Details Modal --}}
<div class="modal-overlay" id="log-details-modal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:500px; padding: 0; overflow: hidden; border-radius: 16px;">
    <div class="modal-head" id="log-details-head" style="padding: 20px; border-bottom: 1px solid rgba(0,0,0,0.05); color: white;">
      <h4 style="margin:0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><use href="#i-doc"/></svg>
        <span id="log-details-title">تفاصيل الحركة</span>
      </h4>
      <button class="btn ghost sm" onclick="document.getElementById('log-details-modal').classList.remove('open')" style="color: inherit; opacity: 0.8; background: rgba(255,255,255,0.2);">✕</button>
    </div>
    <div class="modal-body" style="padding: 20px; background: #f8fafc; max-height: 75vh; overflow-y: auto;">
      
      <div style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;">
        <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;">المبلغ</div>
        <div id="log-details-amount" style="font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;"></div>
        <div id="log-details-discount" style="font-size: 12px; color: #10b981; font-weight: bold; margin-top: 4px; display: none;"></div>
      </div>

      <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="display: flex; border-bottom: 1px solid #f1f5f9; padding: 12px 16px;">
          <div style="width: 120px; font-weight: 600; color: #64748b; font-size: 13px;">النوع:</div>
          <div id="log-details-type" style="font-weight: 700; color: #0f172a; font-size: 14px;"></div>
        </div>
        <div style="display: flex; border-bottom: 1px solid #f1f5f9; padding: 12px 16px;">
          <div style="width: 120px; font-weight: 600; color: #64748b; font-size: 13px;">التاريخ:</div>
          <div id="log-details-date" style="font-weight: 700; color: #0f172a; font-size: 14px; direction: ltr; text-align: right;"></div>
        </div>
        <div style="display: flex; border-bottom: 1px solid #f1f5f9; padding: 12px 16px;">
          <div style="width: 120px; font-weight: 600; color: #64748b; font-size: 13px;">المشروع / البند:</div>
          <div id="log-details-project" style="font-weight: 700; color: #0f172a; font-size: 14px;"></div>
        </div>
        <div style="display: flex; border-bottom: 1px solid #f1f5f9; padding: 12px 16px;">
          <div style="width: 120px; font-weight: 600; color: #64748b; font-size: 13px;">الطرف:</div>
          <div id="log-details-party" style="font-weight: 700; color: #0f172a; font-size: 14px;"></div>
        </div>
        <div style="display: flex; border-bottom: 1px solid #f1f5f9; padding: 12px 16px;">
          <div style="width: 120px; font-weight: 600; color: #64748b; font-size: 13px;">الخزنة / الحساب:</div>
          <div id="log-details-account" style="font-weight: 700; color: #0f172a; font-size: 14px;"></div>
        </div>
        <div style="display: flex; border-bottom: 1px solid #f1f5f9; padding: 12px 16px;">
          <div style="width: 120px; font-weight: 600; color: #64748b; font-size: 13px;">بواسطة:</div>
          <div id="log-details-user" style="font-weight: 700; color: #0f172a; font-size: 14px;"></div>
        </div>
        <div style="display: flex; padding: 12px 16px; flex-direction: column;">
          <div style="font-weight: 600; color: #64748b; font-size: 13px; margin-bottom: 6px;">البيان والملاحظات:</div>
          <div id="log-details-description" style="font-weight: 600; color: #334155; font-size: 14px; line-height: 1.6; background: #f8fafc; padding: 10px; border-radius: 8px;"></div>
        </div>

        <div id="log-details-changes-container" style="display: none; padding: 12px 16px; flex-direction: column;">
          <div style="font-weight: 600; color: #c2410c; font-size: 13px; margin-bottom: 6px; display:flex; align-items:center; gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-tool"/></svg>
            مقارنة التعديلات التي تمت:
          </div>
          <div id="log-details-changes" style="font-weight: 600; color: #78350f; font-size: 13.5px; line-height: 1.7; background: #fffbeb; border: 1px dashed #fde68a; padding: 12px; border-radius: 8px;"></div>
        </div>

        <div id="log-details-deletion-effects-container" style="display: none; padding: 12px 16px; flex-direction: column;">
          <div style="font-weight: 600; color: #dc2626; font-size: 13px; margin-bottom: 6px; display:flex; align-items:center; gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-x"/></svg>
            تأثيرات الحذف التي تمت:
          </div>
          <div id="log-details-deletion-effects" style="font-weight: 600; color: #991b1b; font-size: 13.5px; line-height: 1.7; background: #fef2f2; border: 1px dashed #fca5a5; padding: 12px; border-radius: 8px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .radar-filter-input,
  .select2-container .select2-selection--single {
    width: 100% !important;
    height: 46px !important;
    padding: 0 16px !important;
    border-radius: 12px !important;
    border: 1px solid #cbd5e1 !important;
    background: linear-gradient(145deg, #f8fafc, #f1f5f9) !important;
    color: #0f172a !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02) !important;
    transition: all 0.3s ease !important;
    display: flex;
    align-items: center;
  }
  .radar-filter-input:focus,
  .radar-filter-input:hover,
  .select2-container--open .select2-selection--single,
  .select2-container .select2-selection--single:hover {
    border-color: #3b82f6 !important;
    background: #eff6ff !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), inset 0 2px 4px rgba(0,0,0,0.01) !important;
    outline: none !important;
    color: #1d4ed8 !important;
  }
  
  /* Fix Select2 text alignment and arrow */
  .select2-container .select2-selection--single .select2-selection__rendered {
    padding-right: 0 !important;
    padding-left: 20px !important;
    line-height: 44px !important;
    color: inherit !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    top: 10px !important;
    right: auto !important;
    left: 12px !important;
  }
  .select2-dropdown {
    border-radius: 12px !important;
    border: 1px solid #bfdbfe !important;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1) !important;
    overflow: hidden;
    padding: 4px;
  }
  .select2-results__option {
    border-radius: 8px;
    margin-bottom: 2px;
    font-weight: 600;
    font-size: 13.5px;
    color: #334155;
    padding: 8px 12px;
  }
  .select2-results__option--highlighted[aria-selected] {
    background-color: #eff6ff !important;
    color: #2563eb !important;
  }
  .select2-search__field {
    border-radius: 8px !important;
    border: 1px solid #cbd5e1 !important;
    padding: 8px 12px !important;
  }
  .select2-search__field:focus {
    border-color: #3b82f6 !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
  }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('.select2-filter').select2({
      dir: "rtl",
      width: '100%',
      language: {
        noResults: function () {
          return "لا توجد نتائج";
        }
      }
    });
  });
</script>
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

function openDeleteModal(actionUrl, label, amount, isOwned) {
  document.getElementById('delete-tx-form').action = actionUrl;
  document.getElementById('delete-summary').innerHTML = 'هيتم التراجع وعكس: <strong>' + label + '</strong> — <strong>' + amount + ' ج.م</strong> وهيرجع أثرها بالكامل.';
  document.getElementById('delete-owned-note').style.display = isOwned ? 'block' : 'none';
  document.getElementById('delete-password').value = '';
  document.getElementById('delete-tx-error').style.display = 'none';
  document.getElementById('delete-tx-modal').classList.add('open');
}

function openLogDetails(rowEl) {
  const data = JSON.parse(rowEl.getAttribute('data-log'));
  
  let headerColor = data.color;
  if (data.action_type === 'deleted') {
      headerColor = '#475569'; // Slate 600
  } else if (data.action_type === 'updated') {
      headerColor = '#f59e0b'; // Amber 500
  } else if (data.action_type === 'created') {
      headerColor = '#10b981'; // Emerald 500
  }
  
  document.getElementById('log-details-head').style.background = headerColor;
  document.getElementById('log-details-title').textContent = data.action;
  
  const sign = data.direction === 'in' ? '+' : (data.direction === 'out' ? '-' : '');
  if (data.gross_amount) {
    if (data.amount === '0.00' || data.amount === '0') {
      document.getElementById('log-details-amount').innerHTML = `<span style="text-decoration:line-through; opacity:0.6; font-size: 18px;">${sign}${data.amount} ج.م</span> <br> <span style="color:#f59e0b">${sign}${data.gross_amount} ج.م <small>(آجل بالكامل)</small></span>`;
    } else {
      document.getElementById('log-details-amount').innerHTML = `<span style="font-size: 18px;">${sign}${data.amount} ج.م (تم دفعه)</span> <br> <span style="color:#f59e0b">${sign}${data.gross_amount} ج.م <small>(آجل جزئياً)</small></span>`;
    }
    document.getElementById('log-details-amount').style.color = '#0f172a';
  } else {
    document.getElementById('log-details-amount').textContent = sign + data.amount + ' ج.م';
    document.getElementById('log-details-amount').style.color = data.color;
  }
  
  if (data.discount !== 0) {
    document.getElementById('log-details-discount').textContent = '+ تسوية: ' + data.discount + ' ج.م';
    document.getElementById('log-details-discount').style.display = 'block';
  } else {
    document.getElementById('log-details-discount').style.display = 'none';
  }

  document.getElementById('log-details-type').textContent = data.type;
  document.getElementById('log-details-date').textContent = data.date;
  document.getElementById('log-details-project').textContent = data.project + (data.band ? ' (بند: ' + data.band + ')' : '');
  document.getElementById('log-details-party').textContent = data.party;
  document.getElementById('log-details-account').textContent = data.account || '—';
  document.getElementById('log-details-user').textContent = data.user;
  document.getElementById('log-details-description').textContent = data.description;
  
  const changesContainer = document.getElementById('log-details-changes-container');
  if (data.action_type === 'updated' && data.old_values && Object.keys(data.old_values).length > 0) {
    let html = '';
    const labels = {
      'amount': 'المبلغ',
      'discount': 'التسوية',
      'type': 'النوع',
      'party': 'الطرف',
      'description': 'البيان',
      'date': 'التاريخ',
      'project': 'المشروع',
      'band': 'البند',
      'account': 'الخزنة',
      'direction': 'اتجاه الحركة'
    };
    
    for (const [key, val] of Object.entries(data.old_values)) {
      if(key === 'gross_amount') continue;
      const label = labels[key] || key;
      let oldVal = val !== null && val !== '' ? val : '—';
      let newVal = data.new_values && data.new_values[key] !== undefined && data.new_values[key] !== null && data.new_values[key] !== '' ? data.new_values[key] : '—';
      
      if (key === 'amount' && data.gross_amount) {
          const isOldZero = oldVal === '0' || oldVal === '0.00' || oldVal.includes('0.00');
          if (isOldZero) {
              oldVal = `${data.gross_amount} <span style="color:#d97706; font-size:11px;">(آجل بالكامل)</span>`;
          } else if (oldVal !== '—') {
              oldVal = `${oldVal} <span style="color:#d97706; font-size:11px;">(آجل، الإجمالي: ${data.gross_amount})</span>`;
          }
          
          const isNewZero = newVal === '0' || newVal === '0.00' || newVal.includes('0.00');
          if (isNewZero) {
              newVal = `${data.gross_amount} <span style="color:#d97706; font-size:12px;">(آجل بالكامل)</span>`;
          } else if (newVal !== '—') {
              newVal = `${newVal} <span style="color:#d97706; font-size:12px;">(آجل، الإجمالي: ${data.gross_amount})</span>`;
          }
      }
      
      html += `<div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #fde68a; padding-bottom:8px; margin-bottom:8px;">
                 <span style="color:#b45309; width: 80px; flex-shrink: 0;">${label}:</span>
                 <div style="flex:1; display:flex; flex-direction:column; gap:4px; text-align:left;">
                   <div style="font-size:12px; color:#92400e; text-decoration:line-through; opacity: 0.8;">${oldVal}</div>
                   <div style="font-size:13.5px; font-weight:bold; color:#166534;">${newVal}</div>
                 </div>
               </div>`;
    }
    
    if (html !== '') {
      document.getElementById('log-details-changes').innerHTML = html;
      changesContainer.style.display = 'flex';
    } else {
      changesContainer.style.display = 'none';
    }
  } else {
    changesContainer.style.display = 'none';
  }
  
  const deletionEffectsContainer = document.getElementById('log-details-deletion-effects-container');
  if (data.action_type === 'deleted' && data.deletion_effects && data.deletion_effects.length > 0) {
    let effectsHtml = '<ul style="margin:0; padding-right:18px;">';
    data.deletion_effects.forEach(effect => {
      effectsHtml += `<li style="margin-bottom:6px;">✅ ${effect}</li>`;
    });
    effectsHtml += '</ul>';
    document.getElementById('log-details-deletion-effects').innerHTML = effectsHtml;
    deletionEffectsContainer.style.display = 'flex';
  } else {
    deletionEffectsContainer.style.display = 'none';
  }

  document.getElementById('log-details-modal').classList.add('open');
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
