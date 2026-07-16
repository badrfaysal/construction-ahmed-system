@extends('layouts.app')

@section('title', 'فواتير الشراء')

@section('content')
<div class="page-header">
  <div style="display:flex; align-items:center; gap:12px;">
    <div class="header-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
    </div>
    <div>
      <h1 class="page-title">فواتير الشراء</h1>
      <p class="page-desc">سجل كامل بجميع فواتير شراء الخامات</p>
    </div>
  </div>
</div>

<div class="filter-bar no-print">
  <form method="GET" action="{{ route('material_invoices.index') }}" style="display:flex;gap:16px;flex-wrap:wrap">
    <div class="f-field">
      <label>فلترة بالمشروع</label>
      <div class="f-select-wrap">
        <select name="project_id" class="f-select" onchange="this.form.submit()">
          <option value="">كل المشاريع</option>
          @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
          @endforeach
        </select>
        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chevron-down"/></svg>
      </div>
    </div>
    
    <div class="f-field">
      <label>فلترة بالمورد</label>
      <div class="f-select-wrap">
        <select name="supplier_id" class="f-select" onchange="this.form.submit()">
          <option value="">كل الموردين</option>
          @foreach($suppliers as $s)
            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
          @endforeach
        </select>
        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chevron-down"/></svg>
      </div>
    </div>
    
    @if(request()->hasAny(['project_id', 'supplier_id']))
      <div class="f-field" style="display:flex;align-items:flex-end">
        <a href="{{ route('material_invoices.index') }}" class="btn ghost danger">إلغاء الفلاتر</a>
      </div>
    @endif
  </form>
</div>

<div class="table-card">
  <div class="table-scroll">
    <table>
      <thead>
        <tr>
          <th>رقم الفاتورة</th>
          <th>المشروع</th>
          <th>المورد</th>
          <th>التاريخ</th>
          <th class="num">الإجمالي</th>
          <th class="num">المدفوع</th>
          <th class="num">المتبقي (آجل)</th>
          <th class="no-print" style="width:50px"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          @php
            $remaining = $inv->total_amount - $inv->paid_amount;
            $statusColor = $remaining <= 0 ? 'var(--pos)' : ($inv->paid_amount == 0 ? 'var(--neg)' : 'var(--warn)');
          @endphp
          <tr>
            <td>
              <a href="{{ route('material_invoices.show', $inv->id) }}" style="font-weight:bold;text-decoration:none">
                #{{ str_pad($inv->id, 4, '0', STR_PAD_LEFT) }}
              </a>
              @if($inv->name)
                <div class="muted sm">{{ $inv->name }}</div>
              @endif
            </td>
            <td>
              <a href="{{ route('projects.show', $inv->project_id) }}" class="tag" style="text-decoration:none">
                {{ $inv->project->name }}
              </a>
            </td>
            <td class="muted">{{ $inv->supplier?->name ?? '—' }}</td>
            <td class="muted">{{ $inv->date->format('Y-m-d') }}</td>
            <td class="num"><strong>{{ \App\Support\Money::format($inv->total_amount) }}</strong></td>
            <td class="num" style="color:var(--pos)">{{ \App\Support\Money::format($inv->paid_amount) }}</td>
            <td class="num" style="color:{{ $statusColor }}">
              {{ \App\Support\Money::format($remaining) }}
            </td>
            <td class="no-print">
              <a href="{{ route('material_invoices.show', $inv->id) }}" class="btn ghost sm" title="عرض التفاصيل">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-eye"/></svg>
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align:center;padding:40px;color:var(--mut)">لا توجد فواتير مطابقة للبحث</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($invoices->hasPages())
<div class="pagination-wrapper" style="margin-top:20px;display:flex;justify-content:center">
  {{ $invoices->links() }}
</div>
@endif

@endsection
