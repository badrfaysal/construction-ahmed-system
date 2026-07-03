@extends('layouts.app')
@section('title', 'ضمان جديد')
@section('page-title', 'بدء ضمان جديد')

@section('content')
<div class="page-head"><div><h3>بدء ضمان جديد</h3></div><a href="{{ route('warranties.index') }}" class="btn ghost">رجوع</a></div>
<form method="POST" action="{{ route('warranties.store') }}">
  @csrf
  <div class="form-card">
    <div class="field">
      <label>المشروع *</label>
      <select name="project_id" required>
        <option value="">— اختر مشروعاً منتهياً —</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="row2">
      <div class="field">
        <label>تاريخ بداية الضمان *</label>
        <input type="date" name="start_date" value="{{ old('start_date', today()->format('Y-m-d')) }}" required>
      </div>
      <div class="field">
        <label>مدة الضمان (أشهر) *</label>
        <input type="number" name="months" value="{{ old('months', 12) }}" min="1" max="60" required>
      </div>
    </div>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ</button>
      <a href="{{ route('warranties.index') }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>
@endsection
