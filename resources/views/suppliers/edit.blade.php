@extends('layouts.app')
@section('title', 'تعديل: ' . $supplier->name)
@section('page-title', 'تعديل المورد')
@section('content')
<div class="page-head"><div><h3>تعديل: {{ $supplier->name }}</h3></div><a href="{{ route('suppliers.show', $supplier) }}" class="btn ghost">رجوع</a></div>
<form method="POST" action="{{ route('suppliers.update', $supplier) }}">
  @csrf @method('PUT')
  <div class="form-card">
    <div class="row2">
      <div class="field custom-autocomplete"><label>الموبايل</label><input type="tel" name="phone" value="{{ old('phone', $supplier->phone) }}" autocomplete="off" oninput="autocompleteContactByPhone(this)" onfocus="autocompleteContactByPhone(this)"></div>
      <div class="field custom-autocomplete"><label>اسم المورد *</label><input type="text" name="name" value="{{ old('name', $supplier->name) }}" required autocomplete="off" oninput="autocompleteContactByName(this)" onfocus="autocompleteContactByName(this)"></div>
    </div>
    <div class="row2">
      <div class="field"><label>النشاط</label><input type="text" name="activity" value="{{ old('activity', $supplier->activity) }}" placeholder="كهربائي / بائع أسمنت / سيراميك..." list="supplier-activities"></div>
    </div>
    <div class="field"><label>العنوان</label><input type="text" name="address" value="{{ old('address', $supplier->address) }}"></div>
    <div class="field"><label>ملاحظات</label><textarea name="notes" rows="3">{{ old('notes', $supplier->notes) }}</textarea></div>
    <datalist id="supplier-activities">
      @foreach($activities ?? [] as $act)
        <option value="{{ $act }}">
      @endforeach
    </datalist>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('suppliers.show', $supplier) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

@include('partials.contact-autocomplete')

@endsection
