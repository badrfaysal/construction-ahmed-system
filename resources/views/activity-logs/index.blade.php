@extends('layouts.app')
@section('title', 'سجل العمليات')
@section('page-title', 'سجل العمليات')

@section('content')
<div class="page-head">
    <div>
        <h3>سجل العمليات الشامل</h3>
        <p>يحتوي هذا السجل على كافة الحركات التي تمت في النظام.</p>
    </div>
</div>

<div style="margin: 20px 0; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; background: #fff; padding: 12px 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <form method="GET" action="{{ route('activity-logs.index') }}" style="display: flex; gap: 12px; align-items: center; width: 100%; flex-wrap: wrap; margin: 0;">
        <div style="flex: 1; min-width: 200px;">
            <select name="action" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none; background: #fff; cursor: pointer;">
                <option value="">كل أنواع الحركات</option>
                <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>إنشاء</option>
                <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>تعديل</option>
                <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>حذف</option>
            </select>
        </div>
        
        <div style="flex: 1; min-width: 200px;">
            <select name="user_id" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none; background: #fff; cursor: pointer;">
                <option value="">كل المستخدمين</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 4px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px;">
                <span style="font-size: 12px; color: #64748b;">من:</span>
                <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" style="border: none; background: transparent; font-size: 13.5px; outline: none; color: #475569;">
                <span style="font-size: 12px; color: #64748b; border-right: 1px solid #e2e8f0; padding-right: 8px; margin-right: 4px;">إلى:</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" style="border: none; background: transparent; font-size: 13.5px; outline: none; color: #475569;">
            </div>
            
            @if(request('action') || request('user_id') || request('date_from') || request('date_to'))
                <a href="{{ route('activity-logs.index') }}" style="color: #ef4444; font-size: 13.5px; font-weight: 600; text-decoration: none; white-space: nowrap;">إلغاء</a>
            @endif
        </div>
    </form>
</div>

@if($logs->count())
<style>
    .compact-table { border-collapse: separate !important; border-spacing: 0 12px !important; width: 100%; margin-top: 4px; }
    .compact-table th { padding: 12px 16px !important; border: none !important; color: #475569; text-align: right; font-size: 14.5px; font-weight: 700; white-space: nowrap; }
    .compact-table th .th-flex { display: flex; align-items: center; justify-content: flex-start; gap: 6px; }
    .compact-table th svg { opacity: 0.9; width: 18px; height: 18px; flex-shrink: 0; }
    .compact-table td { 
        padding: 14px 16px !important; 
        line-height: 1.4; 
        border-top: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        background-color: #fff;
        font-size: 13.5px; 
    }
    .compact-table td:first-child { border-right: 1px solid #e2e8f0 !important; border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
    .compact-table td:last-child { border-left: 1px solid #e2e8f0 !important; border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .compact-table tbody tr { box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s ease; }
    .compact-table tbody tr:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .compact-table tbody tr:hover td { background-color: #f8fafc !important; border-color: #cbd5e1 !important; }
    
    .log-modal-content {
        padding: 20px;
        background: #fff;
        border-radius: 12px;
        text-align: right;
        direction: rtl;
        max-width: 800px;
        width: 100%;
        margin: auto;
    }
    .log-modal-content pre {
        background: #1e293b;
        color: #f8fafc;
        padding: 15px;
        border-radius: 8px;
        font-size: 13px;
        direction: ltr;
        text-align: left;
        overflow-x: auto;
    }
    .diff-container { display: flex; gap: 15px; flex-wrap: wrap; }
    .diff-box { flex: 1; min-width: 300px; }
</style>

<div class="table-card" style="background: transparent; box-shadow: none; padding: 0; border: none;">
    <div class="table-scroll" style="padding-bottom: 12px;">
        <table class="compact-table" style="white-space: nowrap;">
            <thead>
                <tr>
                    <th><div class="th-flex"><svg style="color: #64748b;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-calendar"/></svg>الوقت</div></th>
                    <th><div class="th-flex"><svg style="color: #3b82f6;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-users"/></svg>المستخدم</div></th>
                    <th><div class="th-flex"><svg style="color: #eab308;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>نوع الحركة</div></th>
                    <th><div class="th-flex"><svg style="color: #a855f7;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-box"/></svg>العنصر المتأثر</div></th>
                    <th><div class="th-flex"><svg style="color: #10b981;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-search"/></svg>تفاصيل التعديل</div></th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupedLogs as $batchId => $batch)
                @php
                    // Sort batch by ID ascending so the first inserted model (usually the main one) is at the top
                    usort($batch, fn($a, $b) => $a->id <=> $b->id);
                    $mainLog = $batch[0];
                    $subLogs = array_slice($batch, 1);
                @endphp
                <tr>
                    <td dir="ltr" style="text-align: right; color: #64748b; font-weight: 500;">
                        {{ $mainLog->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td style="font-weight: 700; color: #1e293b;">
                        {{ $mainLog->user->name ?? 'النظام' }}
                    </td>
                    <td>
                        @if($mainLog->action == 'created')
                            <span class="tag green sm"><span class="dot"></span>{{ $mainLog->actionAr() }}</span>
                        @elseif($mainLog->action == 'updated')
                            <span class="tag blue sm"><span class="dot"></span>{{ $mainLog->actionAr() }}</span>
                        @elseif($mainLog->action == 'deleted')
                            <span class="tag red sm"><span class="dot"></span>{{ $mainLog->actionAr() }}</span>
                        @else
                            <span class="tag gray sm">{{ $mainLog->actionAr() }}</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight: 600; color: #4338ca;">{{ $mainLog->modelTypeAr() }}</span>
                        <span style="color: #94a3b8; font-size: 12px; margin-right: 4px;">#{{ $mainLog->model_id }}</span>
                        
                        @if(count($subLogs) > 0)
                            <button type="button" class="btn outline sm" style="margin-right: 8px; font-size: 11px; padding: 2px 6px;" onclick="document.getElementById('sub-{{ $mainLog->id }}').style.display = document.getElementById('sub-{{ $mainLog->id }}').style.display === 'none' ? 'table-row' : 'none'">
                                + {{ count($subLogs) }} حركات مرتبطة
                            </button>
                        @endif
                    </td>
                    <td>
                        @php
                            $jsonOld = json_encode($mainLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                            $jsonNew = json_encode($mainLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        @endphp
                        
                        <button type="button" class="btn outline sm" onclick="showLogDetails('{{ $mainLog->action }}', `{{ htmlspecialchars($jsonOld) }}`, `{{ htmlspecialchars($jsonNew) }}`)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-search"/></svg>
                            التفاصيل
                        </button>
                    </td>
                </tr>
                
                @if(count($subLogs) > 0)
                <tr id="sub-{{ $mainLog->id }}" style="display: none; background-color: #f8fafc;">
                    <td colspan="5" style="padding: 10px 30px !important;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                            @foreach($subLogs as $sub)
                            <tr style="border-bottom: 1px dashed #cbd5e1;">
                                <td style="padding: 8px 4px; width: 100px; color: #64748b;">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 4px;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    تأثر تلقائي:
                                </td>
                                <td style="padding: 8px 4px; width: 120px;">
                                    <span class="tag gray sm">{{ $sub->actionAr() }}</span>
                                </td>
                                <td style="padding: 8px 4px;">
                                    <span style="font-weight: 600; color: #475569;">{{ $sub->modelTypeAr() }}</span>
                                    <span style="color: #94a3b8; font-size: 11px;">#{{ $sub->model_id }}</span>
                                </td>
                                <td style="padding: 8px 4px; text-align: left;">
                                    @php
                                        $subJsonOld = json_encode($sub->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                        $subJsonNew = json_encode($sub->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                    @endphp
                                    <button type="button" style="background: none; border: none; color: #3b82f6; cursor: pointer; text-decoration: underline;" onclick="showLogDetails('{{ $sub->action }}', `{{ htmlspecialchars($subJsonOld) }}`, `{{ htmlspecialchars($subJsonNew) }}`)">
                                        عرض التفاصيل
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                @endif
                
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($logs->hasPages())
<div style="margin-top: 20px; display: flex; justify-content: center;" class="pagination-wrapper">
    {{ $logs->links() }}
</div>
@endif

@else
<div class="empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-activity"/></svg>
    <h4>لا توجد حركات مسجلة</h4>
    <p>لم يتم العثور على أي حركات تطابق معايير البحث الحالية.</p>
</div>
@endif

<script>
function showLogDetails(action, oldVal, newVal) {
    let contentHtml = '';
    
    if (action === 'updated') {
        contentHtml = `
            <div class="diff-container">
                <div class="diff-box">
                    <h4 style="color:#ef4444; margin-bottom:10px; font-weight:bold;">القيم القديمة (قبل التعديل)</h4>
                    <pre>${oldVal}</pre>
                </div>
                <div class="diff-box">
                    <h4 style="color:#10b981; margin-bottom:10px; font-weight:bold;">القيم الجديدة (بعد التعديل)</h4>
                    <pre>${newVal}</pre>
                </div>
            </div>
        `;
    } else {
        let val = action === 'deleted' ? oldVal : newVal;
        contentHtml = `
            <h4 style="color:#64748b; margin-bottom:10px; font-weight:bold;">بيانات العنصر</h4>
            <pre>${val}</pre>
        `;
    }

    Swal.fire({
        title: 'تفاصيل الحركة',
        html: `<div class="log-modal-content">${contentHtml}</div>`,
        showConfirmButton: true,
        confirmButtonText: 'إغلاق',
        width: '800px',
        customClass: {
            container: 'my-swal-container',
            popup: 'my-swal-popup',
            confirmButton: 'btn'
        }
    });
}
</script>

@endsection
