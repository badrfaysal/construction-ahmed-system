@extends('layouts.app')
@section('title', 'الأصول الثابتة')
@section('page-title', 'الأصول الثابتة')

@section('content')
<div class="page-head">
  <div>
    <h3>الأصول الثابتة</h3>
    <p>إدارة ممتلكات الشركة، سجل العمليات، تحديث القيمة وتسييلها</p>
  </div>
  <button class="btn" onclick="openModal('add-asset-modal')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-plus"/></svg>
    تسجيل أصل جديد
  </button>
</div>

@if($errors->any())
  <div class="flash error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg>
    <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
  </div>
@endif

@if(session('success'))
  <div class="flash success">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
    <div>{{ session('success') }}</div>
  </div>
@endif

@php
  $totalPurchaseActive = $activeAssets->sum('purchase_cost');
  $totalCurrentActive = $activeAssets->sum('current_value');
@endphp

<div class="grid cols-2" style="margin-bottom:20px; gap: 20px;">
  <div class="vstat" style="background: #e66b27; color: white; border: none;">
    <div class="top" style="color: rgba(255,255,255,0.8)">
      <span class="label">إجمالي القيمة الحالية (للأصول النشطة)</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></span>
    </div>
    <div class="val tnum" style="color: white;">{{ \App\Support\Money::format($totalCurrentActive) }} <small>ج.م</small></div>
  </div>

  <div class="vstat" style="background: #0d9488; color: white; border: none;">
    <div class="top" style="color: rgba(255,255,255,0.8)">
      <span class="label">إجمالي القيمة الشرائية (للأصول النشطة)</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-wallet"/></svg></span>
    </div>
    <div class="val tnum" style="color: white;">{{ \App\Support\Money::format($totalPurchaseActive) }} <small>ج.م</small></div>
  </div>
</div>

<div class="form-card no-print" style="margin-bottom: 24px; padding: 20px; border-radius:16px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.02); background:#fff;">
  <form method="GET" action="{{ route('assets.index') }}" id="filter-form">
    
    <div style="border-bottom:1px solid #e2e8f0; padding-bottom:16px; margin-bottom:20px;">
      <h3 style="margin:0; font-size:18px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20" style="color:#3b82f6"><use href="#i-filter"/></svg>
        تصفية وترتيب الأصول
      </h3>
    </div>
    
    <div style="display:flex; gap:16px; align-items:center; width:100%; flex-wrap:wrap;">
      
      <div class="field" style="margin: 0; flex: 1; min-width: 180px;">
        <select name="sort" onchange="this.form.submit()" style="width:100%; height:44px; padding-top:0; padding-bottom:0; background-color:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; color:#0f172a; font-weight:600; font-size:13.5px;">
          <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>الأحدث أولاً (تاريخ الشراء)</option>
          <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>الأقدم أولاً</option>
          <option value="value_desc" {{ ($sort ?? '') === 'value_desc' ? 'selected' : '' }}>القيمة الحالية (الأعلى)</option>
          <option value="value_asc" {{ ($sort ?? '') === 'value_asc' ? 'selected' : '' }}>القيمة الحالية (الأقل)</option>
        </select>
      </div>

      <div style="display: {{ request('date_from') || request('date_to') ? 'flex' : 'flex' }}; gap: 10px; flex: 1.5; min-width: 250px; flex-wrap: wrap;">
        <input type="date" name="date_to" value="{{ request('date_to') }}" style="flex:1; min-width:120px; height:44px; padding:0 12px; font-size:13.5px; border-radius:10px; border:1px solid #cbd5e1; background:#f8fafc; color:#0f172a;" placeholder="إلى تاريخ">
        <input type="date" name="date_from" value="{{ request('date_from') }}" style="flex:1; min-width:120px; height:44px; padding:0 12px; font-size:13.5px; border-radius:10px; border:1px solid #cbd5e1; background:#f8fafc; color:#0f172a;" placeholder="من تاريخ">
      </div>

      <button type="submit" class="btn primary" style="height:44px; padding:0 24px; border-radius:10px; min-width:120px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><use href="#i-check"/></svg>
        تطبيق
      </button>
      @if(request()->hasAny(['sort', 'date_from', 'date_to']))
        <a href="{{ route('assets.index') }}" class="btn ghost danger" style="height:44px; border-radius:10px; padding:0 16px;">إلغاء</a>
      @endif
    </div>
  </form>
</div>

<div class="table-card" style="margin-bottom:20px">
  <div class="tabs" style="border-bottom: 1px solid var(--line); padding: 0 18px; display: flex; gap: 20px; margin-bottom: 0;">
    <button class="tab-btn active" onclick="switchTab('active')" id="tab-btn-active" style="padding: 14px 0; border: none; border-bottom: 3px solid var(--brand); background: none; font-weight: 700; color: var(--brand); cursor: pointer; display: flex; align-items: center; gap: 6px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg> الأصول النشطة <span style="background: var(--brand); color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px;">{{ $activeAssets->count() }}</span>
    </button>
    <button class="tab-btn" onclick="switchTab('sold')" id="tab-btn-sold" style="padding: 14px 0; border: none; border-bottom: 3px solid transparent; background: none; font-weight: 600; color: var(--ink-2); cursor: pointer; display: flex; align-items: center; gap: 6px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> أصول مباعة <span style="background: #f59e0b; color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px;">{{ $soldAssets->count() }}</span>
    </button>
    <button class="tab-btn" onclick="switchTab('depreciated')" id="tab-btn-depreciated" style="padding: 14px 0; border: none; border-bottom: 3px solid transparent; background: none; font-weight: 600; color: var(--ink-2); cursor: pointer; display: flex; align-items: center; gap: 6px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg> مهلكة بالكامل <span style="background: var(--ink-3); color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px;">{{ $depreciatedAssets->count() }}</span>
    </button>
  </div>

  <div style="background: #f0f9ff; color: #0284c7; padding: 8px 18px; font-size: 13px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--line);">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
    اضغط على أي صف لعرض السجل الزمني الكامل للأصل في نافذة منبثقة.
  </div>

  <div class="table-scroll" id="tab-active">
    <table class="assets-table">
      <thead>
        <tr>
          <th style="width: 40px">#</th>
          <th>اسم الأصل</th>
          <th class="num">التكلفة الشرائية</th>
          <th class="num">القيمة الحالية</th>
          <th class="num">مقدار الإهلاك</th>
          <th>ملاحظات</th>
          <th style="width: 140px; text-align: center;">إجراءات سريعة</th>
        </tr>
      </thead>
      <tbody>
        @forelse($activeAssets as $asset)
          @include('assets._row', ['asset' => $asset, 'index' => $loop->iteration])
        @empty
          <tr><td colspan="7" class="muted text-center" style="padding: 30px 0;">لا توجد أصول نشطة.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="table-scroll" id="tab-sold" style="display: none;">
    <table class="assets-table">
      <thead>
        <tr>
          <th style="width: 40px">#</th>
          <th>اسم الأصل</th>
          <th class="num">التكلفة الشرائية</th>
          <th class="num">القيمة الحالية</th>
          <th class="num">مقدار الإهلاك</th>
          <th>ملاحظات</th>
        </tr>
      </thead>
      <tbody>
        @forelse($soldAssets as $asset)
          @include('assets._row', ['asset' => $asset, 'index' => $loop->iteration, 'hideActions' => true])
        @empty
          <tr><td colspan="6" class="muted text-center" style="padding: 30px 0;">لا توجد أصول مباعة.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="table-scroll" id="tab-depreciated" style="display: none;">
    <table class="assets-table">
      <thead>
        <tr>
          <th style="width: 40px">#</th>
          <th>اسم الأصل</th>
          <th class="num">التكلفة الشرائية</th>
          <th class="num">القيمة الحالية</th>
          <th class="num">مقدار الإهلاك</th>
          <th>ملاحظات</th>
        </tr>
      </thead>
      <tbody>
        @forelse($depreciatedAssets as $asset)
          @include('assets._row', ['asset' => $asset, 'index' => $loop->iteration, 'hideActions' => true])
        @empty
          <tr><td colspan="6" class="muted text-center" style="padding: 30px 0;">لا توجد أصول مهلكة.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Add Asset Modal -->
<div class="modal-overlay" id="add-asset-modal" style="display:none" onclick="if(event.target===this) closeModal('add-asset-modal')">
  <div class="modal" style="max-width: 550px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);">
    <div class="modal-head" style="background: linear-gradient(135deg, var(--brand), #0d9488); color: white; padding: 20px 24px;">
      <h3 style="margin: 0; display: flex; align-items: center; gap: 8px; font-weight: 700;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-plus-circle"/></svg>
        تسجيل أصل جديد
      </h3>
      <button type="button" class="close" onclick="closeModal('add-asset-modal')" style="color: white; opacity: 0.8; transition: opacity 0.2s;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg></button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #fafafa;">
      <form method="POST" action="{{ route('assets.store') }}">
        @csrf
        
        <div style="background: white; border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
          <div class="field" style="margin-bottom: 16px;">
            <label style="font-weight: 700; color: var(--ink-1);">اسم الأصل *</label>
            <input type="text" name="name" required placeholder="مثال: لابتوب ديل، ماكينة حفر، سيارة شيفروليه..." style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px;">
          </div>
          
          <div class="row2" style="gap: 16px; margin-bottom: 0;">
            <div class="field" style="margin-bottom: 0;">
              <label style="font-weight: 700; color: var(--ink-1);">التكلفة الشرائية (ج.م) *</label>
              <div style="position: relative;">
                <input type="number" name="purchase_cost" min="0" step="0.01" required style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px; font-weight: 700; color: var(--brand);">
              </div>
            </div>
            <div class="field" style="margin-bottom: 0;">
              <label style="font-weight: 700; color: var(--ink-1);">تاريخ الشراء *</label>
              <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px;">
            </div>
          </div>
        </div>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
          <h4 style="margin: 0 0 12px 0; color: #166534; font-size: 14px; display: flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-wallet"/></svg>
            التأثير المالي وتحديث الحسابات
          </h4>
          <div class="field" style="margin-bottom: 0;">
            <label style="color: #15803d; font-weight: 600;">مصدر الدفع (اختياري)</label>
            <select name="account_id" style="background: white; border-color: #bbf7d0; color: #166534; font-weight: 600; border-radius: 8px;">
              <option value="">-- تسجيل دفتري فقط (بدون سحب مالي) --</option>
              @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">سحب من: {{ $acc->name }} (الرصيد: {{ \App\Support\Money::format($acc->balance) }})</option>
              @endforeach
            </select>
            <p style="margin: 6px 0 0; font-size: 12px; color: #15803d; opacity: 0.8;">إذا اخترت حساباً، سيتم تسجيل عملية سحب تلقائية بقيمة الأصل من هذا الحساب.</p>
          </div>
        </div>

        <div class="field">
          <label style="font-weight: 700; color: var(--ink-1);">ملاحظات إضافية</label>
          <textarea name="notes" placeholder="ملاحظات حول الأصل (اختياري)..." rows="2" style="background: white; border-radius: 8px; border: 1px solid var(--line); font-size: 14px; padding: 10px 14px; resize: vertical;"></textarea>
        </div>
        
        <div class="btn-row" style="margin-top:24px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 12px;">
          <button type="button" class="btn ghost" onclick="closeModal('add-asset-modal')" style="background: white; border: 1px solid var(--line); color: var(--ink-2); font-weight: 600; border-radius: 8px;">إلغاء</button>
          <button type="submit" class="btn" style="background: linear-gradient(135deg, var(--brand), #0d9488); border: none; border-radius: 8px; font-weight: 700; padding: 0 24px; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
            تأكيد وحفظ الأصل
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="edit-asset-modal" style="display:none" onclick="if(event.target===this) closeModal('edit-asset-modal')">
  <div class="modal" style="max-width: 500px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);">
    <div class="modal-head" style="background: linear-gradient(135deg, var(--ink-2), var(--ink-1)); color: white; padding: 20px 24px;">
      <h3 style="margin: 0; display: flex; align-items: center; gap: 8px; font-weight: 700;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-tool"/></svg>
        تعديل بيانات الأصل
      </h3>
      <button type="button" class="close" onclick="closeModal('edit-asset-modal')" style="color: white; opacity: 0.8; transition: opacity 0.2s;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg></button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #fafafa;">
      <form method="POST" id="edit-asset-form">
        @csrf @method('PUT')
        
        <div style="background: #fff8f1; border-right: 4px solid #f97316; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; color: #9a3412; font-size: 13.5px; display: flex; gap: 12px; align-items: flex-start; box-shadow: var(--shadow-sm);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <div style="line-height: 1.6;">
            <strong>ملاحظة هامة:</strong> تعديل التكلفة الشرائية هنا <strong>دفتري فقط ولا يؤثر مالياً</strong> على أي حساب أو خزنة. <br>
            إذا أردت تعديل الأصل مع التأثير المالي على المحافظ، يرجى <strong>حذف الأصل</strong> وإعادة تسجيله من جديد.
          </div>
        </div>

        <div style="background: white; border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
          <div class="field" style="margin-bottom: 16px;">
            <label style="font-weight: 700; color: var(--ink-1);">اسم الأصل *</label>
            <input type="text" name="name" id="edit-name" required style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px;">
          </div>
          <div class="row2" style="gap: 16px; margin-bottom: 0;">
            <div class="field" style="margin-bottom: 0;">
              <label style="font-weight: 700; color: var(--ink-1);">التكلفة الشرائية (ج.م) *</label>
              <input type="number" name="purchase_cost" id="edit-purchase-cost" min="0" step="0.01" required style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px; font-weight: 700; color: #3b82f6;">
            </div>
            <div class="field" style="margin-bottom: 0;">
              <label style="font-weight: 700; color: var(--ink-1);">القيمة الحالية (ج.م) *</label>
              <input type="number" name="current_value" id="edit-value" min="0" step="0.01" required style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px; font-weight: 700; color: #16a34a;">
            </div>
          </div>
        </div>

        <div class="btn-row" style="margin-top:24px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 12px;">
          <button type="button" class="btn ghost" onclick="closeModal('edit-asset-modal')" style="background: white; border: 1px solid var(--line); color: var(--ink-2); font-weight: 600; border-radius: 8px;">إلغاء</button>
          <button type="submit" class="btn" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none; border-radius: 8px; font-weight: 700; padding: 0 24px; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
            حفظ التعديلات
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Depreciate Modal -->
<div class="modal-overlay" id="depreciate-asset-modal" style="display:none" onclick="if(event.target===this) closeModal('depreciate-asset-modal')">
  <div class="modal" style="max-width: 450px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);">
    <div class="modal-head" style="background: linear-gradient(135deg, #ec4899, #be185d); color: white; padding: 20px 24px;">
      <h3 style="margin: 0; display: flex; align-items: center; gap: 8px; font-weight: 700;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
        إهلاك أصل
      </h3>
      <button type="button" class="close" onclick="closeModal('depreciate-asset-modal')" style="color: white; opacity: 0.8; transition: opacity 0.2s;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg></button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #fafafa;">
      <form method="POST" id="depreciate-asset-form">
        @csrf
        
        <div style="background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 12px; padding: 16px; margin-bottom: 20px; text-align: center;">
          <div style="color: #9d174d; font-size: 13px; font-weight: 600; margin-bottom: 4px;">الأصل المستهدف</div>
          <div id="depreciate-name" style="font-size: 18px; font-weight: 800; color: #831843; margin-bottom: 12px;"></div>
          
          <div style="display: inline-flex; align-items: center; gap: 6px; background: white; padding: 6px 12px; border-radius: 20px; border: 1px solid #fbcfe8; color: #be185d; font-weight: 700; font-size: 14px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-coins"/></svg>
            القيمة الحالية: <span id="depreciate-current"></span> ج.م
          </div>
        </div>

        <div style="background: white; border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
          <div class="field" style="margin-bottom: 0;">
            <label style="font-weight: 700; color: var(--ink-1);">مقدار الإهلاك المراد خصمه (ج.م) *</label>
            <input type="number" name="amount" id="depreciate-amount-input" min="0.01" step="0.01" required style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px; font-weight: 700; color: #ec4899;">
          </div>
        </div>

        <div class="btn-row" style="margin-top:24px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 12px;">
          <button type="button" class="btn ghost" onclick="closeModal('depreciate-asset-modal')" style="background: white; border: 1px solid var(--line); color: var(--ink-2); font-weight: 600; border-radius: 8px;">إلغاء</button>
          <button type="submit" class="btn" style="background: linear-gradient(135deg, #ec4899, #be185d); border: none; border-radius: 8px; font-weight: 700; padding: 0 24px; box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
            تطبيق الإهلاك
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Sell Modal -->
<div class="modal-overlay" id="sell-asset-modal" style="display:none" onclick="if(event.target===this) closeModal('sell-asset-modal')">
  <div class="modal" style="max-width: 550px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);">
    <div class="modal-head" style="background: linear-gradient(135deg, #f59e0b, #b45309); color: white; padding: 20px 24px;">
      <h3 style="margin: 0; display: flex; align-items: center; gap: 8px; font-weight: 700;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        بيع / تسييل الأصل
      </h3>
      <button type="button" class="close" onclick="closeModal('sell-asset-modal')" style="color: white; opacity: 0.8; transition: opacity 0.2s;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg></button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #fafafa;">
      <form method="POST" id="sell-asset-form">
        @csrf
        
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="color: #b45309; font-size: 12px; font-weight: 600; margin-bottom: 2px;">الأصل المستهدف بالبيع</div>
            <div id="sell-name" style="font-size: 16px; font-weight: 800; color: #92400e;"></div>
          </div>
          <div style="text-align: left;">
            <div style="color: #b45309; font-size: 12px; font-weight: 600; margin-bottom: 2px;">القيمة الدفترية الحالية</div>
            <div style="font-size: 15px; font-weight: 700; color: #b45309;"><span id="sell-current"></span> ج.م</div>
          </div>
        </div>

        <div style="background: white; border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
          <div class="row2" style="gap: 16px; margin-bottom: 0;">
            <div class="field" style="margin-bottom: 0;">
              <label style="font-weight: 700; color: var(--ink-1);">مبلغ البيع (التحصيل) ج.م *</label>
              <input type="number" name="sell_amount" min="0" step="0.01" required style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px; font-weight: 700; color: #f59e0b;">
            </div>
            <div class="field" style="margin-bottom: 0;">
              <label style="font-weight: 700; color: var(--ink-1);">تاريخ البيع *</label>
              <input type="date" name="date" value="{{ date('Y-m-d') }}" required style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 15px; padding: 12px 14px;">
            </div>
          </div>
        </div>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
          <h4 style="margin: 0 0 12px 0; color: #166534; font-size: 14px; display: flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-wallet"/></svg>
            إيداع قيمة البيع
          </h4>
          <div class="field" style="margin-bottom: 0;">
            <label style="color: #15803d; font-weight: 600;">الحساب البنكي *</label>
            <select name="account_id" required style="background: white; border-color: #bbf7d0; color: #166534; font-weight: 600; border-radius: 8px;">
              <option value="">-- اختر حساب الإيداع --</option>
              @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">إيداع في: {{ $acc->name }}</option>
              @endforeach
            </select>
            <p style="margin: 6px 0 0; font-size: 12px; color: #15803d; opacity: 0.8;">إجباري تحديد حساب لضمان تسجيل المبالغ المباعة بشكل صحيح في المحفظة المالية.</p>
          </div>
        </div>

        <div class="btn-row" style="margin-top:24px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 12px;">
          <button type="button" class="btn ghost" onclick="closeModal('sell-asset-modal')" style="background: white; border: 1px solid var(--line); color: var(--ink-2); font-weight: 600; border-radius: 8px;">إلغاء</button>
          <button type="submit" class="btn" style="background: linear-gradient(135deg, #f59e0b, #b45309); border: none; border-radius: 8px; font-weight: 700; padding: 0 24px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-check"/></svg>
            تأكيد البيع والتسييل
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Asset Details Modal -->
<div class="modal-overlay" id="asset-details-modal" style="display:none" onclick="if(event.target===this) closeModal('asset-details-modal')">
  <div class="modal" style="max-width: 600px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);">
    <div class="modal-head" style="background: linear-gradient(135deg, var(--ink-1), var(--ink-2)); color: white; padding: 20px 24px;">
      <h3 style="margin: 0; display: flex; align-items: center; gap: 8px; font-weight: 700;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-doc"/></svg>
        السجل الزمني وتفاصيل الأصل
      </h3>
      <button type="button" class="close" onclick="closeModal('asset-details-modal')" style="color: white; opacity: 0.8; transition: opacity 0.2s;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg></button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #fafafa; max-height: 70vh; overflow-y: auto;">
      
      <div style="background: white; border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 18px; font-weight: 800; color: var(--ink-1); margin-bottom: 12px;" id="asset-details-name"></div>
        <div class="grid cols-2" style="gap: 12px; font-size: 14px;">
          <div><span style="color:var(--ink-2); font-weight:600;">الحالة:</span> <span id="asset-details-status" style="font-weight:700; color: var(--brand);"></span></div>
          <div><span style="color:var(--ink-2); font-weight:600;">تاريخ الشراء:</span> <span id="asset-details-date" style="font-weight:700;"></span></div>
          <div><span style="color:var(--ink-2); font-weight:600;">التكلفة الشرائية:</span> <span id="asset-details-purchase" style="font-weight:700; color: #16a34a;"></span> ج.م</div>
          <div><span style="color:var(--ink-2); font-weight:600;">القيمة الحالية:</span> <span id="asset-details-current" style="font-weight:700; color: #3b82f6;"></span> ج.م</div>
          <div><span style="color:var(--ink-2); font-weight:600;">الخصم/الإهلاك:</span> <span id="asset-details-depreciated" style="font-weight:700; color: #ef4444;"></span> ج.م</div>
          <div><span style="color:var(--ink-2); font-weight:600;">الحساب/الخزنة:</span> <span id="asset-details-account" style="font-weight:700;"></span></div>
        </div>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed var(--line); font-size: 13px; color: var(--ink-2);">
          <strong>ملاحظات:</strong> <span id="asset-details-notes"></span>
        </div>
      </div>

      <h4 style="margin: 0 0 12px 0; color: var(--ink-1); font-size: 15px; display: flex; align-items: center; gap: 6px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        العمليات التي تمت على الأصل
      </h4>
      
      <div id="asset-details-logs" style="background: white; border: 1px solid var(--line); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
        <!-- Logs injected via JS -->
      </div>
      
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="delete-asset-modal" style="display:none" onclick="if(event.target===this) closeModal('delete-asset-modal')">
  <div class="modal" style="max-width: 450px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);">
    <div class="modal-head" style="background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; padding: 20px 24px;">
      <h3 style="margin: 0; display: flex; align-items: center; gap: 8px; font-weight: 700;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
        تأكيد حذف الأصل
      </h3>
      <button type="button" class="close" onclick="closeModal('delete-asset-modal')" style="color: white; opacity: 0.8; transition: opacity 0.2s;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-x"/></svg></button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #fafafa;">
      <form method="POST" id="delete-asset-form">
        @csrf @method('DELETE')
        
        <div style="background: #fef2f2; border-right: 4px solid #ef4444; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; color: #991b1b; font-size: 13.5px; display: flex; gap: 12px; align-items: flex-start; box-shadow: var(--shadow-sm);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="flex-shrink: 0; margin-top: 2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
          <div style="line-height: 1.6;">
            <strong>تنبيه مالي مهم:</strong> حذف هذا الأصل سيؤدي إلى <strong>تأثير مالي عكسي</strong>. <br>
            إذا كان تم سحب مبلغ لشرائه من حساب أو خزنة سيتم استرداد وإرجاع هذا المبلغ للحساب مرة أخرى.
          </div>
        </div>

        <p style="margin: 0 0 20px 0; font-size: 15px; color: var(--ink-1);">
          هل أنت متأكد من حذف الأصل: <br>
          <strong id="delete-asset-name" style="color: #ef4444; font-size: 18px; display: inline-block; margin-top: 8px;"></strong>
        </p>

        <div class="field" style="margin-bottom: 20px;">
          <label style="font-weight: 700; color: var(--ink-1); display: block; margin-bottom: 8px;">تأكيد كلمة المرور للمدير *</label>
          <input type="password" name="admin_password" required placeholder="أدخل كلمة المرور الخاصة بك لتأكيد الحذف" style="width: 100%; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; padding: 12px 14px; outline: none; transition: border 0.2s;">
        </div>
        
        <div class="actions" style="display: flex; gap: 12px; margin-top: 24px;">
          <button type="button" class="btn ghost" onclick="closeModal('delete-asset-modal')" style="flex: 1;">إلغاء</button>
          <button type="submit" class="btn primary" style="flex: 1; background: #ef4444; border: none;">نعم، متأكد من الحذف</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
function openDeleteModal(id, name) {
  document.getElementById('delete-asset-form').action = '/assets/' + id;
  document.getElementById('delete-asset-name').textContent = name;
  openModal('delete-asset-modal');
}
function openAssetDetails(rowEl) {
  const asset = JSON.parse(rowEl.getAttribute('data-asset'));
  
  document.getElementById('asset-details-name').textContent = asset.name;
  document.getElementById('asset-details-status').textContent = asset.status;
  document.getElementById('asset-details-date').textContent = asset.date;
  document.getElementById('asset-details-purchase').textContent = asset.purchase_cost;
  document.getElementById('asset-details-current').textContent = asset.current_value;
  document.getElementById('asset-details-depreciated').textContent = asset.depreciated;
  document.getElementById('asset-details-account').textContent = asset.account;
  document.getElementById('asset-details-notes').textContent = asset.notes;
  
  let logsHtml = '';
  if (asset.logs && asset.logs.length > 0) {
    logsHtml = '<table style="width: 100%; border-collapse: collapse; font-size: 13.5px;"><tbody>';
    asset.logs.forEach(log => {
      let tag = '';
      if (log.action === 'purchase') tag = '<span class="tag blue" style="background:#eff6ff;color:#3b82f6;padding:4px 8px;border-radius:6px;font-weight:bold;font-size:12px;">شراء</span>';
      else if (log.action === 'depreciation') tag = '<span class="tag red" style="background:#fef2f2;color:#ef4444;padding:4px 8px;border-radius:6px;font-weight:bold;font-size:12px;">إهلاك</span>';
      else if (log.action === 'edit') tag = '<span class="tag gray" style="background:#f1f5f9;color:#64748b;padding:4px 8px;border-radius:6px;font-weight:bold;font-size:12px;">تعديل</span>';
      else if (log.action === 'sale') tag = '<span class="tag green" style="background:#ecfdf5;color:#10b981;padding:4px 8px;border-radius:6px;font-weight:bold;font-size:12px;">بيع</span>';
      
      logsHtml += `<tr style="border-bottom: 1px solid #f1f5f9;">
        <td style="padding: 12px 16px; width: 140px; color: #64748b; font-weight:600;">${log.date}</td>
        <td style="padding: 12px 16px; width: 90px;">${tag}</td>
        <td style="padding: 12px 16px; color: #0f172a; font-weight:600; line-height: 1.5;">${log.notes}</td>
      </tr>`;
    });
    logsHtml += '</tbody></table>';
  } else {
    logsHtml = '<div style="padding: 20px; text-align: center; color: #94a3b8; font-weight: 600;">لا توجد حركات مسجلة.</div>';
  }
  document.getElementById('asset-details-logs').innerHTML = logsHtml;
  
  openModal('asset-details-modal');
}
function switchTab(tabId) {
  // Hide all tbodies/divs
  document.getElementById('tab-active').style.display = 'none';
  document.getElementById('tab-sold').style.display = 'none';
  document.getElementById('tab-depreciated').style.display = 'none';
  
  // Reset buttons
  document.getElementById('tab-btn-active').className = 'tab-btn';
  document.getElementById('tab-btn-sold').className = 'tab-btn';
  document.getElementById('tab-btn-depreciated').className = 'tab-btn';
  
  document.getElementById('tab-btn-active').style.borderBottomColor = 'transparent';
  document.getElementById('tab-btn-active').style.color = 'var(--ink-2)';
  document.getElementById('tab-btn-sold').style.borderBottomColor = 'transparent';
  document.getElementById('tab-btn-sold').style.color = 'var(--ink-2)';
  document.getElementById('tab-btn-depreciated').style.borderBottomColor = 'transparent';
  document.getElementById('tab-btn-depreciated').style.color = 'var(--ink-2)';
  
  // Show active tab
  document.getElementById('tab-' + tabId).style.display = 'block';
  const btn = document.getElementById('tab-btn-' + tabId);
  btn.className = 'tab-btn active';
  btn.style.borderBottomColor = (tabId === 'active') ? 'var(--brand)' : ((tabId === 'sold') ? '#f59e0b' : 'var(--ink-3)');
  btn.style.color = (tabId === 'active') ? 'var(--brand)' : ((tabId === 'sold') ? '#b45309' : 'var(--ink-1)');
}

function toggleLogs(id) {
  const tr = document.getElementById('logs-' + id);
  if(tr.style.display === 'none') {
    tr.style.display = 'table-row';
  } else {
    tr.style.display = 'none';
  }
}

  function openEditModal(id, name, val, cost) {
    document.getElementById('edit-asset-form').action = '/assets/' + id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-value').value = val;
    document.getElementById('edit-purchase-cost').value = cost;
    openModal('edit-asset-modal');
  }

function openDepreciateModal(id, name, value) {
  const form = document.getElementById('depreciate-asset-form');
  form.action = `/assets/${id}/depreciate`;
  document.getElementById('depreciate-name').textContent = name;
  document.getElementById('depreciate-current').textContent = new Intl.NumberFormat('ar-EG').format(value);
  document.getElementById('depreciate-amount-input').max = value;
  openModal('depreciate-asset-modal');
}

function openSellModal(id, name, value) {
  const form = document.getElementById('sell-asset-form');
  form.action = `/assets/${id}/sell`;
  document.getElementById('sell-name').textContent = name;
  document.getElementById('sell-current').textContent = new Intl.NumberFormat('ar-EG').format(value);
  openModal('sell-asset-modal');
}

function openModal(id) {
  document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}
</script>

<style>
  .assets-table tr.main-row { cursor: pointer; transition: background 0.2s; }
  .assets-table tr.main-row:hover { background: #f8fafc; }
  .assets-table td { vertical-align: middle; }
  
  .btn-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 6px; color: white; border: none; cursor: pointer;
    margin: 0 2px; transition: opacity 0.2s;
  }
  .btn-icon:hover { opacity: 0.8; }
  .btn-edit { background: #3b82f6; }
  .btn-sell { background: #f59e0b; }
  .btn-depreciate { background: #ec4899; }
  .btn-delete { background: #ef4444; }
</style>
@endpush
@endsection
