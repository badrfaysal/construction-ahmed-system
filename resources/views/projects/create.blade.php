@extends('layouts.app')

@section('title', 'مشروع جديد')
@section('page-title', 'مشروع جديد')

@section('content')

<div class="page-head">
  <div>
    <h3>مشروع جديد</h3>
    <p>أدخل بيانات المشروع الجديد</p>
  </div>
  <a href="{{ route('projects.index') }}" class="btn ghost">رجوع</a>
</div>

<form method="POST" action="{{ route('projects.store') }}">
  @csrf
  <div class="form-card">

    <div class="row2">
      <div class="field">
        <label>العميل *</label>
        <select name="client_id" required>
          <option value="">— اختر العميل —</option>
          @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>اسم المشروع *</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="شقة الزمالك" required>
      </div>
    </div>

    <div class="field">
      <label>العنوان</label>
      <input type="text" name="address" value="{{ old('address') }}" placeholder="الحي — الشارع — الدور">
    </div>

    <div class="row3">
      <div class="field">
        <label>المساحة (م²)</label>
        <input type="number" name="area" value="{{ old('area') }}" placeholder="150" step="0.5" min="0">
      </div>
      <div class="field">
        <label>نسبة الإشراف الافتراضية % *</label>
        <input type="number" name="default_supervision_pct" value="{{ old('default_supervision_pct', 0) }}" min="0" max="100" step="0.1" required>
        <p class="muted" style="margin-top:4px;font-size:11px">تتطبق تلقائيًا على كل بند وخامة وفني في المشروع (وتقدر تعدّلها في أي حتة).</p>
      </div>
      <div class="field">
        <label>تاريخ البدء</label>
        <input type="date" name="start_date" value="{{ old('start_date') }}">
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label>موعد التسليم المخطط</label>
        <input type="date" name="deliver_date" value="{{ old('deliver_date') }}">
      </div>
    </div>

    <div class="field">
      <label>ملاحظات</label>
      <textarea name="notes" rows="3" placeholder="أي تفاصيل إضافية...">{{ old('notes') }}</textarea>
    </div>

    <hr style="margin:20px 0; border:0; border-top:1px solid var(--border)">

    <div class="field" style="margin-bottom:16px">
      <label class="check-box" style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:600">
        <input type="checkbox" id="copy-toggle" onchange="document.getElementById('copy-section').style.display = this.checked ? 'block' : 'none'">
        <span>نسخ بنود من مشروع سابق؟</span>
      </label>
      <p class="muted" style="margin-top:4px;font-size:11px">لو علمت هنا، تقدر تختار مشروع قديم وهينسخ كل بنوده وتفاصيل مصنعياتها للمشروع الجديد ده، عشان يسهل عليك وماتدخلهاش من تاني.</p>
    </div>

    <div id="copy-section" style="display:none;background:var(--bg-soft);padding:16px;border-radius:8px;margin-bottom:20px;border:1px solid var(--border)">
      <div class="field" style="margin-bottom:0">
        <label>اختر المشروع اللي عايز تنسخ منه</label>
        <select name="copy_project_id">
          <option value="">— اختر المشروع —</option>
          @foreach($existingProjects as $ep)
            <option value="{{ $ep->id }}">{{ $ep->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>
        حفظ المشروع
      </button>
      <a href="{{ route('projects.index') }}" class="btn ghost">إلغاء</a>
    </div>

  </div>
</form>

@endsection
