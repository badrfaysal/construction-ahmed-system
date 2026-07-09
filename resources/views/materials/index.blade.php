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

{{-- أكتر خامة/بند — بتحترم فلتر المشروع الحالي --}}
<div class="grid cols-3" style="margin-bottom:20px">
  <div class="vstat vstat-blue">
    <div class="top">
      <span class="label">أكتر خامة اشتريتها</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg></span>
    </div>
    @if($insights['topMaterial'])
      <div class="val">{{ $insights['topMaterial']->item }}</div>
      <div class="note">{{ number_format($insights['topMaterial']->total_qty, 1) }} {{ $insights['topMaterial']->unit }} — بتكلفة {{ \App\Support\Money::format($insights['topMaterial']->total_cost) }} ج.م ({{ $insights['topMaterial']->purchase_count }} عملية شراء)</div>
    @else
      <div class="val">—</div>
      <div class="note">لا توجد بيانات كافية بعد</div>
    @endif
  </div>
  <div class="vstat vstat-red">
    <div class="top">
      <span class="label">أكتر خامة عملت لها مرتجع</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg></span>
    </div>
    @if($insights['topReturned'])
      <div class="val">{{ $insights['topReturned']->item }}</div>
      <div class="note">{{ number_format($insights['topReturned']->total_qty, 1) }} {{ $insights['topReturned']->unit }} مرتجعة — بقيمة {{ \App\Support\Money::format($insights['topReturned']->total_value) }} ج.م ({{ $insights['topReturned']->return_count }} مرتجع)</div>
    @else
      <div class="val">—</div>
      <div class="note">لا توجد مرتجعات مسجّلة بعد</div>
    @endif
  </div>
  <div class="vstat vstat-teal">
    <div class="top">
      <span class="label">أكتر بند اشتريت له خامات</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg></span>
    </div>
    @if($insights['topBand'])
      <div class="val">{{ $insights['topBand']->band_name }}</div>
      <div class="note">{{ $insights['topBand']->project_name }} — بتكلفة {{ \App\Support\Money::format($insights['topBand']->total_cost) }} ج.م</div>
    @else
      <div class="val">—</div>
      <div class="note">لا توجد بيانات كافية بعد</div>
    @endif
  </div>
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
  @include('partials._sort-select', ['options' => [
    'newest'    => 'الأحدث',
    'oldest'    => 'الأقدم',
    'cost_desc' => 'الأعلى تكلفة',
    'cost_asc'  => 'الأقل تكلفة',
  ]])
  @if(request()->hasAny(['project_id','sort']))
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
              <td class="num">{{ \App\Support\Money::format($m->unit_price) }}</td>
              <td class="num {{ $m->returnedQty() > 0 ? '' : 'muted' }}">{{ \App\Support\Money::format($m->returnedQty(), 1) }}</td>
              <td class="num">{{ \App\Support\Money::format($m->netCost()) }}</td>
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
