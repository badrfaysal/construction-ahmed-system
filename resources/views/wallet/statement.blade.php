@extends('layouts.app')
@section('title', 'كشف حساب محفظة')
@section('page-title', 'كشف حساب: ' . $account->name)

@push('styles')
<style>
    .statement-wrapper {
        padding: 24px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .action-group {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .btn-return {
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    .btn-return:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .btn-action {
        color: #fff;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.1s, filter 0.2s;
    }
    .btn-action:hover {
        filter: brightness(1.1);
        transform: translateY(-1px);
    }
    .btn-income { background: #10b981; }
    .btn-expense { background: #ef4444; }
    .btn-transfer { background: #0f4a8a; }
    .btn-print {
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #334155;
        padding: 8px 12px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        cursor: pointer;
    }
    .btn-print:hover { background: #f8fafc; }

    .account-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        padding: 24px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 5px solid #0f4a8a;
        margin-bottom: 32px;
    }
    .acc-details h2 {
        margin: 0 0 4px 0;
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
    }
    .acc-details p {
        margin: 0;
        color: #94a3b8;
        font-size: 14px;
        font-weight: 500;
    }
    .acc-balance {
        text-align: left;
    }
    .acc-balance .label {
        font-size: 13px;
        color: #94a3b8;
        margin-bottom: 2px;
        display: block;
        font-weight: 600;
    }
    .acc-balance .amount {
        font-size: 32px;
        font-weight: 800;
        color: #10b981;
        line-height: 1.1;
    }
    .acc-balance.negative .amount {
        color: #ef4444;
    }

    .statement-table-wrapper {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .statement-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }
    .statement-table th {
        background: #f8fafc;
        padding: 16px 20px;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
    }
    .statement-table td {
        padding: 16px 20px;
        font-size: 14px;
        font-weight: 600;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .statement-table tr:last-child td {
        border-bottom: none;
    }
    .statement-table tr:hover td {
        background: #f8fafc;
    }
    .tag-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }
    .tag-badge.green { background: #d1fae5; color: #059669; }
    .tag-badge.red { background: #fee2e2; color: #dc2626; }
    .tag-badge.blue { background: #dbeafe; color: #1d4ed8; }
    
    .amt-in { color: #10b981; font-weight: 800; font-size: 15px; }
    .amt-out { color: #ef4444; font-weight: 800; font-size: 15px; }
    
    .empty-state {
        padding: 48px 24px;
        text-align: center;
        color: #94a3b8;
    }
    .empty-state svg {
        width: 48px;
        height: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
</style>
@endpush

@section('content')

<div class="statement-wrapper">

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('dashboard') }}" class="btn-return">
            كل الحسابات
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <div class="action-group">
            <button class="btn-print" onclick="window.print()">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-print"/></svg>
            </button>
            <button class="btn-action btn-transfer" onclick="document.getElementById('transferModal').style.display='flex'">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3l4 4-4 4M3 17l4-4 4 4M7 7h14M17 17H3"/></svg>
                تحويل للخارج
            </button>
        </div>
    </div>

    {{-- Account Details Card --}}
    <div class="account-card">
        <div class="acc-details">
            <h2>{{ $account->name }}</h2>
            <p>كشف الحساب — EGP</p>
        </div>
        <div class="acc-balance {{ $account->balance < 0 ? 'negative' : '' }}">
            <span class="label">الرصيد الحالي</span>
            <div class="amount">{{ \App\Support\Money::format($account->balance) }}</div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="statement-table-wrapper">
        @if($transactions->count())
            <div style="overflow-x: auto;">
                <table class="statement-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>المرجع</th>
                            <th>النوع</th>
                            <th>التفاصيل</th>
                            <th>المشروع / الجهة</th>
                            <th style="text-align: left;">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $tx)
                            <tr>
                                <td style="color:#64748b; font-weight:500;">{{ $tx->date->format('Y-m-d') }}</td>
                                <td style="color:#64748b; font-size:12px; font-family:monospace; letter-spacing:0.5px;">
                                    TX-{{ $tx->date->format('Y') }}-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td>
                                    <span class="tag-badge {{ $tx->ref_type === 'transfer' ? 'blue' : ($tx->direction === 'in' ? 'green' : 'red') }}">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td>
                                    @if($tx->description)
                                        {{ $tx->description }}
                                    @else
                                        <span style="color:#94a3b8; font-weight:400;">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tx->project)
                                        {{ $tx->project->name }} 
                                        @if($tx->band)<span style="color:#94a3b8; font-size:12px;">({{ $tx->band->name }})</span>@endif
                                    @else
                                        {{ $tx->party ?: '--' }}
                                    @endif
                                </td>
                                <td style="text-align: left;" class="{{ $tx->direction === 'in' ? 'amt-in' : 'amt-out' }}">
                                    {{ $tx->direction === 'in' ? '+' : '-' }}{{ \App\Support\Money::format($tx->amount) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px 20px; border-top: 1px solid #f1f5f9;">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg>
                <h3>لا توجد حركات مسجلة</h3>
                <p>لم يتم تسجيل أي عمليات مالية على هذا الحساب حتى الآن.</p>
            </div>
        @endif
    </div>

</div>

{{-- Transfer Modal --}}
<div class="modal-overlay" id="transferModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:100; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:400px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid #f1f5f9;">
            <h3 style="margin:0; font-size:16px; font-weight:800; color:#0f172a;">تحويل رصيد لخزينة/محفظة أخرى</h3>
            <button onclick="document.getElementById('transferModal').style.display='none'" style="background:none; border:none; cursor:pointer; color:#94a3b8; padding:0;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('wallet.transfer') }}" method="POST">
            @csrf
            <input type="hidden" name="from_account_id" value="{{ $account->id }}">
            <div style="padding:24px; display:flex; flex-direction:column; gap:16px;">
                <div class="field">
                    <label style="font-size:13px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">تحويل إلى *</label>
                    <select name="to_account_id" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; outline:none;">
                        <option value="">-- اختر المحفظة أو الخزينة --</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} (الرصيد: {{ \App\Support\Money::format($w->balance) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label style="font-size:13px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">المبلغ (ج.م) *</label>
                    <input type="number" name="amount" min="0.01" step="0.01" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; outline:none;">
                </div>
                <div class="field">
                    <label style="font-size:13px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">تاريخ التحويل *</label>
                    <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; outline:none;">
                </div>
                <div class="field">
                    <label style="font-size:13px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">ملاحظات (اختياري)</label>
                    <input type="text" name="description" placeholder="تفاصيل سبب التحويل" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; outline:none;">
                </div>
            </div>
            <div style="padding:16px 24px; background:#f8fafc; border-top:1px solid #f1f5f9; display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('transferModal').style.display='none'" style="padding:8px 16px; background:#fff; border:1px solid #cbd5e1; border-radius:8px; color:#475569; font-weight:600; cursor:pointer;">إلغاء</button>
                <button type="submit" style="padding:8px 16px; background:#0f4a8a; border:none; border-radius:8px; color:#fff; font-weight:600; cursor:pointer; box-shadow:0 2px 4px rgba(15,74,138,0.2);">تأكيد التحويل</button>
            </div>
        </form>
    </div>
</div>

@endsection
