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
    <div class="section-label">التسعير</div>
    <div class="field">
      <label>نسبة الإشراف الافتراضية % *</label>
      <input type="number" name="default_supervision_pct" value="{{ old('default_supervision_pct', $settings->default_supervision_pct) }}" min="0" max="100" step="0.1" required>
      <p class="muted" style="margin-top:6px">القيمة اللي بتتملى تلقائياً لما تسجل خامة أو مصنعية جديدة — مش بتغيّر أي سجل موجود بالفعل.</p>
    </div>
  </div>

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
@endsection
