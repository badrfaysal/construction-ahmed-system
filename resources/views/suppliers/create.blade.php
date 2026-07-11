@extends('layouts.app')
@section('title', 'مورد جديد')
@section('page-title', 'مورد جديد')
@section('content')
<div class="page-head"><div><h3>مورد جديد</h3></div><a href="{{ route('suppliers.index') }}" class="btn ghost">رجوع</a></div>
<form method="POST" action="{{ route('suppliers.store') }}">
  @csrf
  <div class="form-card">
    <div class="row2">
      <div class="field"><label>اسم المورد *</label><input type="text" name="name" value="{{ old('name') }}" required></div>
      <div class="field"><label>النشاط</label><input type="text" name="activity" value="{{ old('activity') }}" placeholder="كهربائي / بتاع بويات / سيراميك..." list="supplier-activities"></div>
    </div>
    <div class="row2">
      <div class="field"><label>الهاتف</label><input type="tel" name="phone" value="{{ old('phone') }}"></div>
      <div class="field"><label>البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email') }}"></div>
    </div>
    <div class="field"><label>العنوان</label><input type="text" name="address" value="{{ old('address') }}"></div>
    <div class="field"><label>ملاحظات</label><textarea name="notes" rows="3">{{ old('notes') }}</textarea></div>
    <datalist id="supplier-activities">
      @foreach($activities ?? [] as $act)
        <option value="{{ $act }}">
      @endforeach
    </datalist>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('suppliers.index') }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>
@endsection
