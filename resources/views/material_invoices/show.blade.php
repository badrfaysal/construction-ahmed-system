@extends('layouts.app')
@section('title', 'تفاصيل الفاتورة')
@section('page-title', 'تفاصيل الفاتورة')

@section('content')
<div class="page-head">
    <div>
        <h3>فاتورة: {{ $invoice->name ?: 'بدون اسم' }}</h3>
        <p>مشروع: {{ $invoice->project->name }} | المورد: {{ $invoice->supplier?->name ?? 'بدون مورد' }}</p>
    </div>
    <a href="{{ route('projects.show', $invoice->project_id) }}" class="btn ghost">رجوع للمشروع</a>
</div>

<div class="row" style="margin-bottom: 24px;">
    <div class="card stat">
        <div class="top"><span class="label">إجمالي الفاتورة</span></div>
        <div class="val tnum">{{ number_format($invoice->total_amount, 2) }} ج.م</div>
    </div>
    <div class="card stat">
        <div class="top"><span class="label">المدفوع</span></div>
        <div class="val tnum">{{ number_format($invoice->paid_amount, 2) }} ج.م</div>
    </div>
    <div class="card stat">
        <div class="top"><span class="label">المتبقي (دين)</span></div>
        <div class="val tnum" style="color:var(--neg)">{{ number_format($invoice->remainingBalance(), 2) }} ج.م</div>
    </div>
</div>

<div class="card" style="padding: 24px;">
    <div class="section-label" style="display:flex; justify-content:space-between; align-items:center;">
        <span>الأصناف التابعة للفاتورة</span>
        <form method="POST" action="{{ route('material_invoices.destroy', $invoice->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة بالكامل والتراجع عن التكاليف والديون المتعلقة بها؟')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn ghost danger sm">حذف الفاتورة بالكامل</button>
        </form>
    </div>

    <div class="table-card mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>البند</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>الإجمالي (تكلفة)</th>
                    <th>سعر البيع للعميل</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->materials as $mat)
                <tr>
                    <td><strong>{{ $mat->item }}</strong></td>
                    <td class="muted">{{ $mat->band?->name ?? '—' }}</td>
                    <td class="num">{{ $mat->qty }} {{ $mat->unit }}</td>
                    <td class="num">{{ number_format($mat->unit_price, 2) }}</td>
                    <td class="num"><strong>{{ number_format($mat->qty * $mat->unit_price, 2) }}</strong></td>
                    <td class="num">{{ number_format($mat->qty * $mat->sell_price, 2) }}</td>
                    <td>
                        <form method="POST" action="{{ route('materials.destroy', $mat->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الصنف والتراجع عن تكاليفه؟')" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn ghost danger sm" title="حذف الصنف">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
