@extends('layouts.app')
@section('title', 'الإعدادات')
@section('page-title', 'الإعدادات العامة')

@section('content')
<div class="page-head">
  <div><h3>الإعدادات العامة</h3><p>القيم دي بتتطبق على كل النظام — عروض الأسعار، تسجيل الخامات والمصنعية، والمستندات المطبوعة</p></div>
</div>

<form method="POST" action="{{ route('settings.update') }}" style="max-width:640px">
  @csrf
  @method('PUT')

  <div class="form-card">
    <div class="section-label">بيانات الشركة (تظهر في عروض الأسعار وكشوف الحساب المطبوعة)</div>
    <div class="field">
      <label>اسم الشركة *</label>
      <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required>
    </div>
    <div class="field">
      <label>الوصف / التخصص</label>
      <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings->company_tagline) }}" placeholder="مقاولات وتشطيبات · القاهرة">
    </div>
    <div class="row2">
      <div class="field">
        <label>الهاتف</label>
        <input type="text" name="company_phone" value="{{ old('company_phone', $settings->company_phone) }}">
      </div>
      <div class="field">
        <label>رقم السجل التجاري</label>
        <input type="text" name="company_registration" value="{{ old('company_registration', $settings->company_registration) }}">
      </div>
    </div>
  </div>

  <div class="form-card">
    <div class="section-label">واتساب</div>
    <div class="field">
      <label>كود الدولة (بدون +) *</label>
      <input type="text" name="whatsapp_country_code" value="{{ old('whatsapp_country_code', $settings->whatsapp_country_code) }}" maxlength="5" required style="max-width:120px">
      <p class="muted" style="margin-top:6px">مصر = 20. بيتضاف تلقائياً قبل رقم العميل عند إنشاء رابط واتساب من عرض السعر.</p>
    </div>
  </div>

  <div class="btn-row" style="margin-top:8px">
    <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ الإعدادات</button>
  </div>
</form>

<div style="max-width:640px; margin-top:20px;">
  <div class="form-card">
    <div class="section-label">قاعدة البيانات</div>
    <div class="field">
      <p class="muted" style="margin-bottom:12px">يمكنك تحميل نسخة احتياطية من قاعدة البيانات (.sql) لضمان أمان البيانات. يرجى حفظ الملف في مكان آمن.</p>
      <form method="POST" action="{{ route('settings.export_db') }}" style="display:inline">
        @csrf
        <button type="submit" class="btn ghost" style="color:var(--brand); border-color:var(--line);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" style="margin-inline-end:6px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> تصدير قاعدة البيانات</button>
      </form>
    </div>
  </div>
</div>
@endsection
