@extends('layouts.app')

@section('title', 'تعديل: ' . $project->name)
@section('page-title', 'تعديل المشروع')

@section('content')

<div class="page-head">
  <div><h3>تعديل: {{ $project->name }}</h3></div>
  <a href="{{ route('projects.show', $project) }}" class="btn ghost">رجوع</a>
</div>

<form method="POST" action="{{ route('projects.update', $project) }}">
  @csrf
  @method('PUT')
  <div class="form-card">

    <div class="row2">
      <div class="field">
        <label>العميل *</label>
        <select name="client_id" required>
          @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ $project->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>اسم المشروع *</label>
        <input type="text" name="name" value="{{ old('name', $project->name) }}" required>
      </div>
    </div>

    <div class="field">
      <label>العنوان</label>
      <input type="text" name="address" value="{{ old('address', $project->address) }}">
    </div>

    <div class="row3">
      <div class="field">
        <label>المساحة (م²)</label>
        <input type="number" name="area" value="{{ old('area', $project->area) }}" step="0.5" min="0">
      </div>
      <div class="field">
        <label>نسبة الإشراف الافتراضية % *</label>
        <input type="number" name="default_supervision_pct" value="{{ old('default_supervision_pct', $project->default_supervision_pct) }}" min="0" max="100" step="0.1" required>
      </div>
      <div class="field">
        <label>تاريخ البدء</label>
        <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label>موعد التسليم المخطط</label>
        <input type="date" name="deliver_date" value="{{ old('deliver_date', $project->deliver_date?->format('Y-m-d')) }}">
      </div>
    </div>

    <div class="row3">
      <div class="field">
        <label>الحالة</label>
        <select name="status">
          <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>جاري</option>
          <option value="done" {{ $project->status === 'done' ? 'selected' : '' }}>مكتمل</option>
        </select>
      </div>
      <div class="field">
        <label>تاريخ التسليم الفعلي</label>
        <input type="date" name="delivered_date" value="{{ old('delivered_date', $project->delivered_date?->format('Y-m-d')) }}">
      </div>
    </div>

    <div class="field">
      <label>ملاحظات</label>
      <textarea name="notes" rows="3">{{ old('notes', $project->notes) }}</textarea>
    </div>

    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>
        حفظ التعديلات
      </button>
      <a href="{{ route('projects.show', $project) }}" class="btn ghost">إلغاء</a>
    </div>

  </div>
</form>

@endsection
