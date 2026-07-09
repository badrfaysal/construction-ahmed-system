@extends('layouts.app')
@section('title', 'الفنيين والعمال')
@section('page-title', 'الفنيين والعمال')

@section('content')
<div class="page-head">
  <div><h3>الفنيين والعمال</h3><p>أجور ومستحقات العمالة لكل بند ومشروع</p></div>
  <a href="{{ route('labor.create') }}" class="btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    تسجيل عمل
  </a>
</div>

<form method="GET" action="{{ route('labor.index') }}" class="filter-bar">
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-building"/></svg>
      المشروع
    </label>
    <div class="f-select-wrap">
      <select name="project_id" class="f-select" onchange="this.form.submit()">
        <option value="">كل المشاريع</option>
        @foreach($projects as $p)
          <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
        @endforeach
      </select>
      <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-down"/></svg>
    </div>
  </div>
  @if(request('project_id'))
    <div class="f-actions">
      <a href="{{ route('labor.index') }}" class="btn ghost sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
        مسح الفلتر
      </a>
    </div>
  @endif
</form>

<div class="table-card">
  @if($bands->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>التاريخ</th>
            <th>الفني / الفريق</th>
            <th>البند</th>
            <th>الشقة</th>
            <th>نوع التعاقد</th>
            <th>الحالة</th>
            <th class="num">المستحق</th>
          </tr>
        </thead>
        <tbody>
          @foreach($bands as $band)
            <tr>
              <td class="muted">{{ $band->labor_date?->format('Y-m-d') ?? '—' }}</td>
              <td><strong>{{ $band->team_name ?: '—' }}</strong></td>
              <td>{{ $band->name }}</td>
              <td><a href="{{ route('projects.show', $band->project) }}">{{ $band->project->name }}</a></td>
              <td class="muted">{{ $band->contractTypeAr() }}</td>
              <td>
                @if($band->status === 'done')
                  <span class="tag green">منفذ</span>
                @elseif($band->status === 'active')
                  <span class="tag blue">جاري</span>
                @else
                  <span class="tag gray">لم يبدأ</span>
                @endif
              </td>
              <td class="num">{{ \App\Support\Money::format($band->labor_amount) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div style="padding:16px">{{ $bands->withQueryString()->links() }}</div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
      <h4>لا توجد أعمال مسجلة بعد</h4>
      <p><a href="{{ route('labor.create') }}">سجّل أول عمل</a></p>
    </div>
  @endif
</div>
@endsection
