@extends('layouts.app')
@section('title', 'تعديل ' . ($material->category === 'misc' ? 'بند فرعي' : 'خامة'))
@section('page-title', 'تعديل ' . ($material->category === 'misc' ? 'بند فرعي' : 'خامة') . ' — ' . $material->project->name)

@section('content')
<div class="page-head">
  <div><h3>تعديل {{ $material->category === 'misc' ? 'بند فرعي' : 'خامة' }}</h3><p>{{ $material->project->name }}</p></div>
  <a href="{{ route('projects.show', $material->project) }}" class="btn ghost">رجوع</a>
</div>

@include('partials._errors')

<form method="POST" action="{{ route('materials.update', $material) }}" style="max-width:720px">
  @csrf
  @method('PUT')
  <div class="form-card">
    
    <div class="field">
      <label>اسم الصنف *</label>
      <input type="text" name="item" value="{{ old('item', $material->item) }}" placeholder="اسم الخامة أو المصروف..." required list="item-names-list">
    </div>

    @if($material->category === 'misc')
      <div class="row2">
        <div class="field">
          <label>اسم الجهة (المورد أو الصنايعي)</label>
          <input type="text" name="supplier_name" value="{{ old('supplier_name', $material->band_worker_id ? $material->worker->name ?? '' : $material->supplier_name) }}" list="suppliers-list">
        </div>
        <div class="field">
          <label>طريقة المحاسبة</label>
          <select name="contract_type">
            <option value="" {{ old('contract_type', $material->contract_type) === null ? 'selected' : '' }}>لا يوجد (فاتورة)</option>
            <option value="lump_sum" {{ old('contract_type', $material->contract_type) === 'lump_sum' ? 'selected' : '' }}>مبلغ مقطوع</option>
            <option value="per_meter" {{ old('contract_type', $material->contract_type) === 'per_meter' ? 'selected' : '' }}>بالمتر</option>
            <option value="per_piece" {{ old('contract_type', $material->contract_type) === 'per_piece' ? 'selected' : '' }}>بالقطعة</option>
          </select>
        </div>
      </div>
    @endif

    <div class="row2">
      <div class="field">
        <label>الوحدة {{ $material->category === 'misc' ? '(اختياري)' : '*' }}</label>
        <input type="text" name="unit" value="{{ old('unit', $material->unit) }}" {{ $material->category !== 'misc' ? 'required' : '' }} list="unit-names-list">
      </div>
      <div class="field">
        <label>الكمية *</label>
        <input type="number" name="qty" value="{{ old('qty', (float)$material->qty) }}" min="0" step="0.01" required>
      </div>
    </div>

    <div class="row3">
      <div class="field">
        <label>سعر الوحدة (شراء / تكلفة) *</label>
        <input type="number" name="unit_price" value="{{ old('unit_price', (float)$material->unit_price) }}" min="0" step="0.01" required>
      </div>
      <div class="field">
        <label>سعر البيع للعميل *</label>
        <input type="number" name="sell_price" value="{{ old('sell_price', (float)$material->sell_price) }}" min="0" step="0.01" required>
      </div>
      <div class="field">
        <label>نسبة الإشراف %</label>
        <input type="number" name="supervision_pct" value="{{ old('supervision_pct', (float)$material->supervision_pct) }}" min="0" max="100" step="0.1">
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label>التاريخ *</label>
        <input type="date" name="date" value="{{ old('date', $material->date->format('Y-m-d')) }}" required>
      </div>
      @if($material->category !== 'misc')
        <div class="field">
          <label>المورد (اختياري)</label>
          <select name="supplier_id">
            <option value="">— اختر المورد —</option>
            @foreach($suppliers as $sup)
              <option value="{{ $sup->id }}" {{ old('supplier_id', $material->supplier_id) == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
            @endforeach
          </select>
        </div>
      @endif
    </div>

    <div class="field" style="margin-bottom:14px">
      <label>ملاحظات</label>
      <input type="text" name="notes" value="{{ old('notes', $material->notes) }}" placeholder="أي تفاصيل إضافية...">
    </div>
    <div class="btn-row" style="margin-top:8px">
      <button type="submit" class="btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>حفظ التعديلات</button>
      <a href="{{ route('projects.show', $material->project) }}" class="btn ghost">إلغاء</a>
    </div>
  </div>
</form>

<datalist id="item-names-list">
  @foreach($itemNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>
<datalist id="unit-names-list">
  @foreach($unitNames as $name)
    <option value="{{ $name }}">
  @endforeach
</datalist>
@if($material->category === 'misc')
<datalist id="suppliers-list">
  @foreach($supplierNames as $sup)
    <option value="{{ $sup }}">
  @endforeach
  @foreach($craftsmenNames as $crf)
    <option value="{{ $crf }}">
  @endforeach
</datalist>
@endif

@endsection
