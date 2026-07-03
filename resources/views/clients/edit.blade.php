@extends('layouts.app')
@section('title', 'تعديل: ' . $client->name)
@section('page-title', 'تعديل العميل')
@section('content')
<div class="page-head">
  <div><h3>تعديل: {{ $client->name }}</h3></div>
  <a href="{{ route('clients.show', $client) }}" class="btn ghost">رجوع</a>
</div>
<form method="POST" action="{{ route('clients.update', $client) }}">
  @csrf @method('PUT')
  <div class="form-card">
    <div class="row2">
      <div class="field"><label>الاسم *</label><input type="text" name="name" value="{{ old('name', $client->name) }}" required></div>
      <div class="field"><label>الهاتف</label><input type="tel" name="phone" value="{{ old('phone', $client->phone) }}"></div>
    </div>
    <div class="row2">
      <div class="field"><label>البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email', $client->email) }}"></div>
      <div class="field"><label>العنوان</label><input type="text" name="address" value="{{ old('address', $client->address) }}"></div>
    </div>
    <div class="field"><label>ملاحظات</label><textarea name="notes" rows="3">{{ old('notes', $client->notes) }}</textarea></div>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('clients.show', $client) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>
@endsection
