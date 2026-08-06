<?php

namespace App\Http\Controllers;

use App\Models\SystemActivityLog;
use Illuminate\Http\Request;

class SystemActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString();
        
        $groupedLogs = [];
        foreach ($logs as $log) {
            if ($log->batch_id) {
                if (!isset($groupedLogs[$log->batch_id])) {
                    $groupedLogs[$log->batch_id] = [];
                }
                $groupedLogs[$log->batch_id][] = $log;
            } else {
                $groupedLogs['no_batch_' . $log->id] = [$log];
            }
        }

        $users = \App\Models\User::all();

        return view('activity-logs.index', compact('logs', 'groupedLogs', 'users'));
    }
}
