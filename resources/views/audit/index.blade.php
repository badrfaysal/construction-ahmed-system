@extends('layouts.app')
@section('title', 'سجل التدقيق')
@section('page-title', 'سجل التدقيق')

@section('content')
<div style="background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; border: 1px solid #10b981; border-top-width: 4px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="text-align: right;">
            <h2 style="margin: 0; font-size: 24px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                سجل التدقيق
            </h2>
            <p style="margin: 8px 0 0 0; color: #64748b; font-size: 15px;">كل تغيير حصل في النظام: مين، إمتى، وإيه اللي اتغير</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left; border-right: 4px solid #1e293b;">
        <div style="color: #64748b; font-size: 14px; margin-bottom: 8px; text-align: right;">إجمالي السجلات (الكلي)</div>
        <div style="font-size: 28px; font-weight: 700; color: #0f172a;">{{ number_format($totalRecords) }}</div>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left; border-right: 4px solid #ef4444;">
        <div style="color: #64748b; font-size: 14px; margin-bottom: 8px; text-align: right;">عمليات حرجة</div>
        <div style="font-size: 28px; font-weight: 700; color: #ef4444;">{{ number_format($criticalOperations) }}</div>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left; border-right: 4px solid #f59e0b;">
        <div style="color: #64748b; font-size: 14px; margin-bottom: 8px; text-align: right;">تحذيرات</div>
        <div style="font-size: 28px; font-weight: 700; color: #f59e0b;">{{ number_format($warnings) }}</div>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left; border-right: 4px solid #3b82f6;">
        <div style="color: #64748b; font-size: 14px; margin-bottom: 8px; text-align: right;">سجلات اليوم</div>
        <div style="font-size: 28px; font-weight: 700; color: #3b82f6;">{{ number_format($todayRecords) }}</div>
    </div>
</div>

<div style="background: #fff; padding: 16px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <form method="GET" action="{{ route('audit.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin: 0;">
        
        <div>
            <label style="display: block; font-size: 13px; color: #475569; margin-bottom: 6px; font-weight: 600;">الفترة</label>
            <select name="period" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #f8fafc; outline: none;">
                <option value="">كل الفترات</option>
                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>الأمس</option>
                <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>هذا الأسبوع</option>
                <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>هذا الشهر</option>
            </select>
        </div>



        <div>
            <label style="display: block; font-size: 13px; color: #475569; margin-bottom: 6px; font-weight: 600;">العملية</label>
            <select name="action" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #f8fafc; outline: none;">
                <option value="">كل العمليات</option>
                <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>إنشاء</option>
                <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>تعديل</option>
                <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>حذف</option>
                <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>تسجيل الدخول</option>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 13px; color: #475569; margin-bottom: 6px; font-weight: 600;">الأهمية</label>
            <select name="importance" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #f8fafc; outline: none;">
                <option value="">الكل</option>
                <option value="critical" {{ request('importance') == 'critical' ? 'selected' : '' }}>حرجة</option>
                <option value="warning" {{ request('importance') == 'warning' ? 'selected' : '' }}>تحذير</option>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 13px; color: #475569; margin-bottom: 6px; font-weight: 600;">الموظف</label>
            <select name="user_id" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #f8fafc; outline: none;">
                <option value="">الكل</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 13px; color: #475569; margin-bottom: 6px; font-weight: 600;">بحث</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث في الوصف..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #f8fafc; outline: none;" onkeydown="if(event.key === 'Enter') this.form.submit()">
        </div>

        @if(request()->anyFilled(['period', 'module', 'action', 'importance', 'user_id', 'search']))
        <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end;">
            <a href="{{ route('audit.index') }}" style="color: #ef4444; font-size: 14px; font-weight: 600; text-decoration: none;">مسح الفلاتر</a>
        </div>
        @endif
    </form>
</div>

<style>
    .audit-card {
        background: #fff;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 12px;
        border-left: 4px solid #3b82f6;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .audit-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .audit-card.action-deleted { border-left-color: #ef4444; }
    .audit-card.action-created { border-left-color: #10b981; }
    .audit-card.action-login { border-left-color: #8b5cf6; }

    .audit-tags {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }
    .audit-tag {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
    }
    .audit-tag.user { background: #dbeafe; color: #1e3a8a; }
    .audit-tag.action { background: #f3f4f6; color: #374151; }
    .audit-tag.module { background: #e0e7ff; color: #3730a3; }
    
    .audit-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .audit-ip {
        font-size: 12px;
        color: #94a3b8;
        font-family: monospace;
    }
    .audit-time {
        font-size: 13px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }
    
    .log-modal-content {
        padding: 24px;
        background: #fff;
        border-radius: 12px;
        text-align: right;
        direction: rtl;
        width: 100%;
        margin: auto;
    }
    .log-modal-content pre {
        background: #0f172a;
        color: #f8fafc;
        padding: 16px;
        border-radius: 8px;
        font-size: 14px;
        direction: ltr;
        text-align: left;
        overflow-x: auto;
        white-space: pre-wrap;
    }
    .diff-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 768px) {
        .diff-container { grid-template-columns: 1fr; }
    }
</style>

@forelse($logs as $log)
<div class="audit-card action-{{ $log->action }}" 
     data-action="{{ $log->action }}"
     data-old="{{ json_encode($log->old_values) }}"
     data-new="{{ json_encode($log->new_values) }}"
     onclick="showLogDetailsFromElement(this)">
    <div class="audit-card-right" style="flex: 1;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            
            <div style="text-align: right;">
                <div class="audit-tags" style="justify-content: flex-start;">
                    <span class="audit-tag user">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 2px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        {{ $log->user->name ?? 'النظام' }}
                    </span>
                    <span class="audit-tag action">
                        @if($log->action === 'created')
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 2px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        @elseif($log->action === 'updated')
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 2px;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        @elseif($log->action === 'deleted')
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 2px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        @else
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 2px;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        @endif
                        {{ $log->action }}
                    </span>
                    <span class="audit-tag module">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 2px;"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        {{ class_basename($log->model_type) }}
                    </span>
                </div>
                
                <div class="audit-title">
                    {{ $log->getTelegramDescription() }}
                </div>
                
                <div class="audit-ip" style="margin-top: 6px;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-left: 2px;"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                    IP: <span dir="ltr">{{ $log->ip_address ?? 'غير مسجل' }}</span>
                </div>
            </div>

            <div class="audit-time">
                <span dir="ltr">{{ $log->created_at->format('Y/m/d h:i:s A') }}</span>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            
        </div>
    </div>
</div>
@empty
<div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
    <h4>لا يوجد سجلات مطابقة</h4>
    <p>لم يتم العثور على أي حركات.</p>
</div>
@endforelse

@if($logs->hasPages())
<div style="margin-top: 20px; display: flex; justify-content: center;" class="pagination-wrapper">
    {{ $logs->links() }}
</div>
@endif

<script>
function showLogDetailsFromElement(el) {
    showLogDetails(
        el.getAttribute('data-action'), 
        el.getAttribute('data-old'), 
        el.getAttribute('data-new')
    );
}

const keyMap = {
    'name': 'الاسم', 'title': 'العنوان', 'amount': 'المبلغ', 'total': 'الإجمالي', 
    'price': 'السعر', 'balance': 'الرصيد', 'status': 'الحالة', 'description': 'الوصف', 
    'date': 'التاريخ', 'quantity': 'الكمية', 'type': 'النوع', 'direction': 'الاتجاه', 
    'party': 'الطرف', 'project_id': 'رقم المشروع', 'client_id': 'رقم العميل', 
    'account_id': 'رقم الحساب', 'band_id': 'رقم البند', 'discount': 'الخصم', 
    'created_at': 'وقت الإنشاء', 'updated_at': 'وقت التعديل', 'id': 'المعرف', 
    'user_id': 'رقم المستخدم', 'ref_type': 'نوع المرجع', 'ref_id': 'رقم المرجع',
    'phone': 'رقم الهاتف', 'address': 'العنوان', 'notes': 'ملاحظات', 'email': 'البريد الإلكتروني',
    'password': 'كلمة المرور', 'role': 'الصلاحية', 'net_capital': 'صافي رأس المال',
    'unit_price': 'سعر الوحدة', 'sell_price': 'سعر البيع', 'supervision_pct': 'نسبة الإشراف',
    'specialty': 'التخصص', 'contract_type': 'نوع التعاقد', 'start_date': 'تاريخ البدء', 'sell_amount': 'مبلغ البيع'
};

const hiddenFields = ['remember_token', 'updated_at', 'created_at', 'deleted_at', 'details'];

function formatValue(key, val) {
    if (key === 'password') return '******** (مشفرة)';
    if (val === null || val === undefined || val === '') return '<span style="color:#94a3b8">فارغ</span>';
    if (typeof val === 'boolean') return val ? 'نعم' : 'لا';
    if (typeof val === 'object') return '<span style="color:#94a3b8">بيانات مركبة</span>';
    return val;
}

function renderTable(data, title, color) {
    if (!data || Object.keys(data).length === 0) return `<div style="padding:15px; background:#f8fafc; border-radius:8px; color:#64748b; text-align:center;">لا يوجد بيانات للعرض</div>`;
    
    let html = `<div style="margin-bottom: 20px;">
        <h4 style="color:${color}; margin-bottom:12px; font-weight:700; display:flex; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            ${title}
        </h4>
        <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <tbody>`;
            
    let hasRows = false;
    for (let key in data) {
        if (hiddenFields.includes(key)) continue;
        let keyAr = keyMap[key] || key;
        html += `
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:12px 16px; background:#f8fafc; color:#475569; font-weight:600; width:40%; border-left:1px solid #f1f5f9;">${keyAr}</td>
                <td style="padding:12px 16px; color:#1e293b; font-weight:500;">${formatValue(key, data[key])}</td>
            </tr>`;
        hasRows = true;
    }
    
    if (!hasRows) return `<div style="padding:15px; background:#f8fafc; border-radius:8px; color:#64748b; text-align:center;">لم تتغير بيانات مرئية</div>`;
    
    html += `</tbody></table></div>`;
    return html;
}

function showLogDetails(action, oldVal, newVal) {
    let contentHtml = '';
    
    let parsedOld = null, parsedNew = null;
    try { parsedOld = oldVal ? JSON.parse(oldVal) : null; } catch(e) {}
    try { parsedNew = newVal ? JSON.parse(newVal) : null; } catch(e) {}
    
    if (action === 'updated') {
        let diffOld = {};
        let diffNew = {};
        
        if (parsedOld && parsedNew) {
            let hasDiff = false;
            for (let k in parsedNew) {
                if (hiddenFields.includes(k)) continue;
                
                let oldValStr = (typeof parsedOld[k] === 'object' && parsedOld[k] !== null) ? JSON.stringify(parsedOld[k]) : String(parsedOld[k]);
                let newValStr = (typeof parsedNew[k] === 'object' && parsedNew[k] !== null) ? JSON.stringify(parsedNew[k]) : String(parsedNew[k]);
                
                if (oldValStr !== newValStr) {
                    diffOld[k] = parsedOld[k];
                    diffNew[k] = parsedNew[k];
                    hasDiff = true;
                }
            }
            if (!hasDiff) {
                // Remove hidden fields before showing the fallback full object
                for (let k in parsedOld) { if (!hiddenFields.includes(k)) diffOld[k] = parsedOld[k]; }
                for (let k in parsedNew) { if (!hiddenFields.includes(k)) diffNew[k] = parsedNew[k]; }
            }
        } else if (parsedNew) {
            for (let k in parsedNew) { if (!hiddenFields.includes(k)) diffNew[k] = parsedNew[k]; }
        } else if (parsedOld) {
            for (let k in parsedOld) { if (!hiddenFields.includes(k)) diffOld[k] = parsedOld[k]; }
        }
        
        contentHtml = `
            <div class="diff-container">
                <div>${renderTable(diffOld, 'البيانات قبل التعديل', '#ef4444')}</div>
                <div>${renderTable(diffNew, 'البيانات بعد التعديل', '#10b981')}</div>
            </div>
        `;
    } else {
        let data = (action === 'deleted') ? (parsedOld || parsedNew) : (parsedNew || parsedOld);
        if(action === 'login') {
            contentHtml = `<div style="padding:20px; text-align:center; color:#3b82f6; font-size:16px; font-weight:600;"><svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom:10px; display:block; margin:0 auto 10px;"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>تسجيل الدخول تم بنجاح</div>`;
        } else {
            let cleanData = {};
            if (data) {
                for (let k in data) { if (!hiddenFields.includes(k)) cleanData[k] = data[k]; }
            }
            contentHtml = renderTable(cleanData, 'تفاصيل العنصر', '#64748b');
        }
    }

    Swal.fire({
        title: 'تفاصيل الحركة',
        html: `<div class="log-modal-content" style="padding:0; background:transparent;">${contentHtml}</div>`,
        showConfirmButton: true,
        confirmButtonText: 'إغلاق',
        width: '900px',
        customClass: {
            container: 'my-swal-container',
            popup: 'my-swal-popup',
            confirmButton: 'btn'
        }
    });
}
</script>
@endsection
