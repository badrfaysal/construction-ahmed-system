<?php

namespace App\Http\Controllers;

use App\Models\SystemActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemActivityLog::with('user')->latest();

        // Stats queries
        $today = Carbon::today();
        $totalRecords = SystemActivityLog::count();
        $todayRecords = SystemActivityLog::whereDate('created_at', $today)->count();
        $criticalOperations = SystemActivityLog::whereIn('action', ['deleted'])->count();
        $warnings = SystemActivityLog::where('action', 'updated')->count();

        // Filters
        if ($request->filled('period')) {
            $period = $request->period;
            if ($period === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($period === 'yesterday') {
                $query->whereDate('created_at', Carbon::yesterday());
            } elseif ($period === 'week') {
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($period === 'month') {
                $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            }
        }

        // Exclude CapitalSnapshot
        $query->where('model_type', '!=', 'App\\Models\\CapitalSnapshot');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('importance')) {
            if ($request->importance === 'critical') {
                $query->whereIn('action', ['deleted']);
            } elseif ($request->importance === 'warning') {
                $query->where('action', 'updated');
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('action', 'like', "%{$search}%")
                ->orWhere('model_type', 'like', "%{$search}%")
                ->orWhere('old_values', 'like', "%{$search}%")
                ->orWhere('new_values', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();
        $users = User::all();

        return view('audit.index', compact('logs', 'totalRecords', 'todayRecords', 'criticalOperations', 'warnings', 'users'));
    }
}
