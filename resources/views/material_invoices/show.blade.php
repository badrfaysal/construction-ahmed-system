@extends('layouts.app')
@section('title', 'تفاصيل الفاتورة')
@section('page-title', 'تفاصيل الفاتورة')

@section('content')
<div class="page-head no-print">
    <div>
        <h3>فاتورة مشتريات: {{ $invoice->name ?: 'بدون اسم' }}</h3>
        <p>مشروع: {{ $invoice->project->name }} | المورد: {{ $invoice->supplier?->name ?? 'بدون مورد' }}</p>
    </div>
    <div class="btn-row">
        <button type="button" class="btn sm ghost" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
            طباعة الفاتورة
        </button>
        <a href="{{ route('projects.show', $invoice->project_id) }}" class="btn ghost sm">رجوع للمشروع</a>
    </div>
</div>

<div class="invoice-wrapper" id="print-area">
    <div class="invoice-header">
        <div class="invoice-brand">
            <!-- مساحة اللوجو -->
            <div class="logo-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <div class="brand-info">
                <h2>نظام إدارة المقاولات</h2>
                <p>سند إدخال مشتريات / فاتورة مورد</p>
            </div>
        </div>
        <div class="invoice-meta">
            <div class="meta-item">
                <span class="meta-label">رقم الفاتورة:</span>
                <span class="meta-val">#INV-{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">تاريخ الفاتورة:</span>
                <span class="meta-val">{{ $invoice->date?->format('Y-m-d') ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div class="invoice-details row2">
        <div class="detail-box">
            <div class="box-title">بيانات المشروع</div>
            <div class="box-content">
                <strong>{{ $invoice->project->name }}</strong><br>
                <span class="muted">الخزنة/المحفظة: {{ $invoice->account?->name ?? 'غير محدد' }}</span>
            </div>
        </div>
        <div class="detail-box">
            <div class="box-title">بيانات المورد</div>
            <div class="box-content">
                <strong>{{ $invoice->supplier?->name ?? 'مورد عام / نثريات' }}</strong><br>
                @if($invoice->supplier?->phone)
                <span class="muted">رقم الهاتف: <span dir="ltr">{{ $invoice->supplier->phone }}</span></span>
                @endif
            </div>
        </div>
    </div>

    <div class="table-card mt-3" style="box-shadow:none; border:1px solid var(--line); border-radius:8px">
        <table class="table" style="margin:0">
            <thead style="background:#f8fafc">
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>البند المرتبط</th>
                    <th class="num">الكمية</th>
                    <th class="num">سعر الوحدة</th>
                    <th class="num">الإجمالي (تكلفة)</th>
                    <th class="num no-print">سعر البيع للعميل</th>
                    <th class="no-print"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->materials as $idx => $mat)
                <tr>
                    <td class="muted">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $mat->item }}</strong>
                        @if($mat->returnedQty() > 0)
                        <div style="margin-top:4px"><span class="tag sm amber">مرتجع: {{ number_format($mat->returnedQty(), 1) }} {{ $mat->unit }}</span></div>
                        @endif
                    </td>
                    <td class="muted">{{ $mat->band?->name ?? '—' }}</td>
                    <td class="num">{{ number_format($mat->qty, 1) }} {{ $mat->unit }}</td>
                    <td class="num">{{ \App\Support\Money::format($mat->unit_price) }}</td>
                    <td class="num" style="background:#f8fafc"><strong>{{ \App\Support\Money::format($mat->qty * $mat->unit_price) }}</strong></td>
                    <td class="num no-print">{{ \App\Support\Money::format($mat->clientUnitPrice()) }}</td>
                    <td class="no-print" style="width:40px; text-align:center">
                        @if(auth()->user()->isAdmin())
                            @if($mat->returnedQty() > 0)
                                <span class="muted" style="font-size:11px; display:inline-block; margin-top:6px" title="لا يمكن الحذف لوجود مرتجع مرتبط بها">محمية</span>
                            @else
                                <form method="POST" action="{{ route('materials.destroy', $mat->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الصنف والتراجع عن تكاليفه؟')" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn ghost danger sm" title="حذف الصنف" style="padding:4px">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#i-trash"/></svg>
                                    </button>
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="invoice-footer">
        <div class="notes-section">
            @if($invoice->notes)
            <div class="box-title">ملاحظات الفاتورة</div>
            <p>{{ $invoice->notes }}</p>
            @endif
        </div>
        <div class="totals-section">
            <div class="total-row">
                <span>إجمالي الفاتورة:</span>
                <strong>{{ \App\Support\Money::format($invoice->total_amount) }} ج.م</strong>
            </div>
            <div class="total-row">
                <span>المدفوع:</span>
                <strong>{{ \App\Support\Money::format($invoice->paid_amount) }} ج.م</strong>
            </div>
            <div class="total-row grand-total {{ $invoice->remainingBalance() > 0 ? 'debt' : 'paid' }}">
                <span>المتبقي (المديونية):</span>
                <strong dir="ltr">{{ \App\Support\Money::format($invoice->remainingBalance()) }} ج.م</strong>
            </div>
        </div>
    </div>
</div>

<div class="no-print" style="margin-top:24px; text-align:left;">
    <form method="POST" action="{{ route('material_invoices.destroy', $invoice->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة بالكامل والتراجع عن التكاليف والديون المتعلقة بها؟')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn danger outline">حذف الفاتورة بالكامل</button>
    </form>
</div>

@push('scripts')
<style>
.invoice-wrapper {
    background: #fff;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    max-width: 900px;
    margin: 0 auto;
    color: #333;
}
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 24px;
    margin-bottom: 24px;
}
.invoice-brand {
    display: flex;
    align-items: center;
    gap: 16px;
}
.logo-placeholder {
    width: 64px;
    height: 64px;
    background: var(--primary);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.logo-placeholder svg { width: 32px; height: 32px; }
.brand-info h2 { margin: 0 0 4px; font-size: 1.4rem; color: #111; }
.brand-info p { margin: 0; color: #666; font-size: 0.95rem; }

.invoice-meta {
    text-align: left;
}
.meta-item {
    margin-bottom: 8px;
    font-size: 0.95rem;
}
.meta-label { color: #666; display: inline-block; width: 100px; text-align:right; margin-left:8px; }
.meta-val { font-weight: 600; color: #111; display: inline-block; width: 120px; text-align: left; }

.detail-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
}
.box-title {
    font-size: 0.85rem;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.box-content strong { font-size: 1.1rem; color: #0f172a; }

.invoice-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 2px solid #f0f0f0;
}
.notes-section { flex: 1; padding-left: 32px; color: #555; }
.totals-section {
    width: 300px;
    background: #f8fafc;
    border-radius: 8px;
    padding: 16px;
    border: 1px solid #e2e8f0;
}
.total-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 1.05rem;
}
.total-row:not(:last-child) {
    border-bottom: 1px dashed #cbd5e1;
}
.total-row.grand-total {
    font-size: 1.25rem;
    font-weight: 700;
    border-bottom: none;
    padding-top: 12px;
    margin-top: 4px;
}
.grand-total.debt { color: #b91c1c; }
.grand-total.paid { color: #15803d; }

@media print {
    @page { margin: 1cm; }
    body { background: #fff !important; }
    body * { visibility: hidden; }
    .invoice-wrapper, .invoice-wrapper * { visibility: visible; }
    .invoice-wrapper {
        position: absolute;
        left: 0; top: 0;
        width: 100%;
        margin: 0; padding: 0;
        box-shadow: none;
    }
    .no-print { display: none !important; }
    .table-card { border: 1px solid #000 !important; }
    .table th, .table td { border-bottom: 1px solid #ccc !important; }
}
</style>
@endpush
@endsection
