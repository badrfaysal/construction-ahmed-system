@extends('layouts.app')
@section('title', 'ضمان — ' . $project->name)
@section('page-title', 'ضمان: ' . $project->name)

@section('content')
@php $warranty = $project->warranty; @endphp
<div class="page-head">
  <div>
    <h3>{{ $project->name }}</h3>
    <p>{{ $project->client->name }} — يبدأ {{ $warranty->start_date->format('Y-m-d') }} وينتهي {{ $warranty->expiresAt()->format('Y-m-d') }}</p>
  </div>
  <div class="btn-row">
    <span class="tag {{ $warranty->isActive() ? 'green' : 'gray' }}" style="font-size:13px;padding:6px 14px">{{ $warranty->isActive() ? 'ساري' : 'منتهي' }}</span>
    <a href="{{ route('warranties.index') }}" class="btn ghost">رجوع</a>
  </div>
</div>

{{-- Add a new complaint --}}
<div class="form-card" style="max-width:680px;margin-bottom:24px">
  <div class="section-label" style="margin-top:0">تسجيل شكوى جديدة</div>
  <form method="POST" action="{{ route('warranties.complaints.store', $warranty) }}">
    @csrf
    <div class="row2">
      <div class="field">
        <label>تاريخ الشكوى *</label>
        <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
      </div>
    </div>
    <div class="field">
      <label>الوصف *</label>
      <textarea name="description" rows="2" required placeholder="وصف المشكلة المبلغ عنها...">{{ old('description') }}</textarea>
    </div>
    <div class="btn-row">
      <button type="submit" class="btn sm">تسجيل الشكوى</button>
    </div>
  </form>
</div>

{{-- Complaints list --}}
<div class="section-label">سجل الشكاوى</div>
<div class="table-card">
  @if($warranty->complaints->count())
    <div class="table-scroll">
      <table>
        <thead><tr><th>التاريخ</th><th>الوصف</th><th>الحالة</th><th></th></tr></thead>
        <tbody>
          @foreach($warranty->complaints as $c)
            <tr>
              <td class="muted">{{ $c->date->format('Y-m-d') }}</td>
              <td>{{ $c->description }}</td>
              <td>
                @if($c->status === 'resolved')
                  <span class="tag green">تم الحل</span>
                @else
                  <span class="tag amber">قيد المتابعة</span>
                @endif
              </td>
              <td>
                @if($c->status !== 'resolved')
                  <form method="POST" action="{{ route('complaints.resolve', $c) }}">
                    @csrf
                    <button class="btn pos sm">إغلاق الشكوى</button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state"><h4>لا توجد شكاوى مسجلة</h4></div>
  @endif
</div>
@endsection
