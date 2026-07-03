@extends('layouts.app')
@section('title', 'متابعة الضمانات')
@section('page-title', 'متابعة الضمانات')

@section('content')
<div class="page-head">
  <div><h3>متابعة الضمانات</h3><p>كل المشاريع التي بدأ ضمانها</p></div>
  <a href="{{ route('warranties.create') }}" class="btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    بدء ضمان جديد
  </a>
</div>

<div class="table-card">
  @if($warranties->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>المشروع</th>
            <th>العميل</th>
            <th>بداية الضمان</th>
            <th>تاريخ الانتهاء</th>
            <th>الحالة</th>
            <th class="num">شكاوى مفتوحة</th>
          </tr>
        </thead>
        <tbody>
          @foreach($warranties as $w)
            <tr class="row-click" onclick="location.href='{{ route('warranties.show', $w->project) }}'">
              <td><strong>{{ $w->project->name }}</strong></td>
              <td class="muted">{{ $w->project->client->name }}</td>
              <td class="muted">{{ $w->start_date->format('Y-m-d') }}</td>
              <td class="muted">{{ $w->expiresAt()->format('Y-m-d') }}</td>
              <td>
                @if($w->isActive())
                  <span class="tag green">ساري</span>
                @else
                  <span class="tag gray">منتهي</span>
                @endif
              </td>
              <td class="num">{{ $w->complaints->where('status', '!=', 'resolved')->count() }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-shield"/></svg>
      <h4>لا توجد ضمانات بعد</h4>
      <p><a href="{{ route('warranties.create') }}">ابدأ ضماناً جديداً</a></p>
    </div>
  @endif
</div>
@endsection
