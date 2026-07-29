@extends('layouts.app')
@section('title', 'الخامات')
@section('page-title', 'الخامات والمرتجعات')

@section('content')

@push('styles')
<style>
.vstat:hover .ic { transform: scale(1.15) rotate(8deg); background: rgba(255, 255, 255, 0.3); }
.vstat .vstat-bg { position: absolute; left: -10px; bottom: -15px; width: 90px; height: 90px; color: rgba(255, 255, 255, 0.15); z-index: 0; transform: rotate(-15deg); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none; }
.vstat:hover .vstat-bg { transform: scale(1.2) rotate(5deg); color: rgba(255, 255, 255, 0.25); }
</style>
@endpush

<div class="page-head">
  <div><h3>الخامات والمرتجعات</h3><p>كل ما تم شراؤه ومرتجعاته — التعديل والحذف بقوا من <a href="{{ route('transactions.index') }}">سجل الحركات</a> فقط</p></div>
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
      <div class="note">{{ number_format($insights['topMaterial']->total_qty, 1) }} — بتكلفة {{ \App\Support\Money::format($insights['topMaterial']->total_cost) }} ج.م ({{ $insights['topMaterial']->purchase_count }} عملية شراء)</div>
    @else
      <div class="val">—</div>
      <div class="note">لا توجد بيانات كافية بعد</div>
    @endif
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>
  </div>
  <div class="vstat vstat-red">
    <div class="top">
      <span class="label">أكتر خامة عملت لها مرتجع</span>
      <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg></span>
    </div>
    @if($insights['topReturned'])
      <div class="val">{{ $insights['topReturned']->item }}</div>
      <div class="note">{{ number_format($insights['topReturned']->total_qty, 1) }} مرتجعة — بقيمة {{ \App\Support\Money::format($insights['topReturned']->total_value) }} ج.م ({{ $insights['topReturned']->return_count }} مرتجع)</div>
    @else
      <div class="val">—</div>
      <div class="note">لا توجد مرتجعات مسجّلة بعد</div>
    @endif
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-x"/></svg>
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
    <svg class="vstat-bg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-hardhat"/></svg>
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
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-calendar"/></svg>
      من تاريخ
    </label>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="f-select" onchange="this.form.submit()">
  </div>
  <div class="f-field">
    <label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-calendar"/></svg>
      إلى تاريخ
    </label>
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="f-select" onchange="this.form.submit()">
  </div>
  @include('partials._sort-select', ['options' => [
    'newest'    => 'الأحدث',
    'oldest'    => 'الأقدم',
    'cost_desc' => 'الأعلى تكلفة',
    'cost_asc'  => 'الأقل تكلفة',
  ]])
  @if(request()->hasAny(['project_id','sort','date_from','date_to']))
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
            <th class="num"><span class="price-cost">سعر الشراء (تكلفة)</span></th>
            <th class="num"><span class="price-sell">سعر البيع للعميل</span></th>
            <th class="num">المرتجع</th>
            <th class="num">الإجمالي الصافي</th>
            <th>التاريخ</th>
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
              <td class="num price-cost">{{ \App\Support\Money::format($m->unit_price) }}</td>
              <td class="num price-sell">{{ \App\Support\Money::format($m->clientUnitPrice()) }}</td>
              <td class="num {{ $m->returnedQty() > 0 ? '' : 'muted' }}">{{ \App\Support\Money::format($m->returnedQty(), 1) }}</td>
              <td class="num">{{ \App\Support\Money::format($m->netCost()) }}</td>
              <td class="muted">{{ $m->date->format('d/m/Y') }}</td>
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
