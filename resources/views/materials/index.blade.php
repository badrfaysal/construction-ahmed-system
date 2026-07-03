@extends('layouts.app')
@section('title', 'الخامات')
@section('page-title', 'الخامات والمرتجعات')

@section('content')

<div class="page-head">
  <div><h3>الخامات والمرتجعات</h3><p>كل ما تم شراؤه ومرتجعاته</p></div>
  <a href="{{ route('materials.create') }}" class="btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-plus"/></svg>
    خامة جديدة
  </a>
</div>

<form method="GET" class="filter-bar">
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
      <a href="{{ route('materials.index') }}" class="btn ghost sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
        مسح الفلتر
      </a>
    </div>
  @endif
</form>

<div class="table-card">
  @if($materials->count())
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>الصنف</th>
            <th>المشروع</th>
            <th>البند</th>
            <th>المورد</th>
            <th class="num">الكمية</th>
            <th>الوحدة</th>
            <th class="num">سعر الوحدة</th>
            <th class="num">المرتجع</th>
            <th class="num">الإجمالي الصافي</th>
            <th>التاريخ</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($materials as $m)
            <tr>
              <td><strong>{{ $m->item }}</strong></td>
              <td><span class="tag gray">{{ $m->project?->name ?? '—' }}</span></td>
              <td class="muted">{{ $m->band?->name ?? '—' }}</td>
              <td class="muted">{{ $m->supplier?->name ?? '—' }}</td>
              <td class="num">{{ number_format($m->qty, 1) }}</td>
              <td class="muted">{{ $m->unit }}</td>
              <td class="num">{{ number_format($m->unit_price) }}</td>
              <td class="num {{ $m->returnedQty() > 0 ? '' : 'muted' }}">{{ number_format($m->returnedQty(), 1) }}</td>
              <td class="num">{{ number_format($m->netCost()) }}</td>
              <td class="muted">{{ $m->date->format('d/m/Y') }}</td>
              <td>
                <form method="POST" action="{{ route('materials.destroy', $m) }}" onsubmit="return confirm('حذف؟')">
                  @csrf @method('DELETE')
                  <button class="btn ghost sm" style="color:var(--neg)">حذف</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div style="padding:14px 18px;border-top:1px solid var(--line)">
      {{ $materials->withQueryString()->links() }}
    </div>
  @else
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
      <h4>لا توجد خامات مسجلة</h4>
    </div>
  @endif
</div>

@endsection
