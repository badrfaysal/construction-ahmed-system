<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class RadarController extends Controller
{
    public function index(Request $request)
    {
        // Must be admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'غير مصرح لك بالدخول لهذه الصفحة');
        }

        $query = AuditLog::with(['performedBy', 'project', 'band'])->orderByDesc('happened_at');

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('performed_by', $request->user_id);
        }

        // Date filter
        $period = $request->get('period', 'today');
        if ($period === 'today') {
            $query->whereBetween('happened_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()]);
        } elseif ($period === 'yesterday') {
            $query->whereBetween('happened_at', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()]);
        } elseif ($period === 'custom') {
            if ($request->filled('date_from')) {
                $query->where('happened_at', '>=', Carbon::parse($request->date_from)->startOfDay());
            }
            if ($request->filled('date_to')) {
                $query->where('happened_at', '<=', Carbon::parse($request->date_to)->endOfDay());
            }
        }

        $logs = $query->paginate(30)->withQueryString();
        $users = User::all();
        $wallets = \App\Models\Account::selectable();
        
        $transactionIds = $logs->pluck('transaction_id')->filter()->unique();
        $liveIds = \App\Models\Transaction::whereIn('id', $transactionIds)->pluck('id')->flip();

        return view('radar.index', compact('logs', 'users', 'period', 'liveIds', 'wallets'));
    }
}
