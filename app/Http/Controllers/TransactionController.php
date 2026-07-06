<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;

// سجل الحركات: القائمة نفسها بتتقرا من sy2_audit_logs (سجل تدقيق ثابت لا
// يُعدَّل ولا يُحذف) عشان تعرض كل حاجة حصلت فعلاً — إنشاء/تعديل/حذف — مش بس
// الحالة الحية النهائية اللي في sy2_transactions. الإجماليات (وارد/صادر) لسه
// بتتحسب من الحركات الحية (Transaction) عشان تفضل تعكس الرصيد الفعلي دلوقتي.
class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['project', 'band'])
            ->orderByDesc('happened_at')
            ->orderByDesc('id');

        if ($pid = $request->get('project_id')) {
            $query->where('project_id', $pid);
        }

        if ($dir = $request->get('direction')) {
            $query->where('direction', $dir);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        $logs     = $query->paginate(50);
        $projects = Project::orderBy('name')->get(['id', 'name']);

        // الإجماليات من الحركات الحيّة فقط (مش من كل أحداث السجل) عشان تعكس
        // الرصيد الفعلي، مع تطبيق نفس فلتر المشروع لو موجود
        $liveQuery = Transaction::query();
        if ($pid) {
            $liveQuery->where('project_id', $pid);
        }
        $totalIn  = (clone $liveQuery)->where('direction', 'in')->sum('amount');
        $totalOut = (clone $liveQuery)->where('direction', 'out')->sum('amount');

        return view('transactions.index', compact('logs', 'projects', 'totalIn', 'totalOut'));
    }
}
