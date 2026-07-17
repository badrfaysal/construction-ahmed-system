@extends('layouts.app')
@section('title', 'عميل جديد')
@section('page-title', 'عميل جديد')

@section('content')
<div class="page-head">
  <div><h3>عميل جديد</h3></div>
  <a href="{{ route('clients.index') }}" class="btn ghost">رجوع</a>
</div>
<form method="POST" action="{{ route('clients.store') }}">
  @csrf
  <div class="form-card">
    <div class="row2">
      <div class="field custom-autocomplete"><label>الموبايل</label><input type="tel" name="phone" value="{{ old('phone') }}" placeholder="0100 000 0000" autocomplete="off" oninput="autocompleteContactByPhone(this)" onfocus="autocompleteContactByPhone(this)"></div>
      <div class="field custom-autocomplete"><label>الاسم *</label><input type="text" name="name" value="{{ old('name') }}" required autocomplete="off" oninput="autocompleteContactByName(this)" onfocus="autocompleteContactByName(this)"></div>
    </div>
    <div class="row2">

      <div class="field"><label>العنوان</label><input type="text" name="address" value="{{ old('address') }}" placeholder="الحي — الشارع — الدور"></div>
    </div>
    <div class="field"><label>ملاحظات</label><textarea name="notes" rows="3">{{ old('notes') }}</textarea></div>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('clients.index') }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

@include('partials.contact-autocomplete')

@endsection
