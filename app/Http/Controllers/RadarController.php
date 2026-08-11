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

        $query = AuditLog::with(['performedBy', 'project', 'band']);

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('happened_at')->orderBy('id');
        } elseif ($sort === 'amount_desc') {
            $query->orderByDesc('amount');
        } elseif ($sort === 'amount_asc') {
            $query->orderBy('amount');
        } else {
            $query->orderByDesc('happened_at')->orderByDesc('id');
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('performed_by', $request->user_id);
        }

        // Transaction filter (to jump to specific radar entry)
        if ($request->filled('tx')) {
            $query->where('transaction_id', $request->tx);
            $period = 'all'; // Override period to ensure we find it
        } else {
            // Date filter
            $period = $request->get('period', 'today');
            if ($period === 'today') {
                $query->whereBetween('happened_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()]);
            } elseif ($period === 'yesterday') {
                $query->whereBetween('happened_at', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()]);
            } elseif ($period === 'month') {
                $query->whereBetween('happened_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            } elseif ($period === 'custom') {
                if ($request->filled('date_from')) {
                    $query->where('happened_at', '>=', Carbon::parse($request->date_from)->startOfDay());
                }
                if ($request->filled('date_to')) {
                    $query->where('happened_at', '<=', Carbon::parse($request->date_to)->endOfDay());
                }
            }
        }

        // Project filter
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Text Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($b) use ($q) {
                $b->where('description', 'like', "%{$q}%")
                  ->orWhere('party', 'like', "%{$q}%")
                  ->orWhere('type', 'like', "%{$q}%")
                  ->orWhereHas('project', function($pq) use ($q) {
                      $pq->where('name', 'like', "%{$q}%");
                  })
                  ->orWhereHas('band', function($bq) use ($q) {
                      $bq->where('name', 'like', "%{$q}%");
                  })
                  ->orWhereHas('performedBy', function($uq) use ($q) {
                      $uq->where('name', 'like', "%{$q}%");
                  });
            });
        }

        // Flow type filter
        if ($request->filled('flow_type')) {
            $flow = $request->flow_type;
            if ($flow === 'in') {
                $query->where('direction', 'in')->where('ref_type', '!=', 'transfer');
            } elseif ($flow === 'out') {
                $query->where('direction', 'out')->where('ref_type', '!=', 'transfer');
            } elseif ($flow === 'transfer') {
                $query->where('ref_type', 'transfer');
            }
        }

        $logs = $query->paginate(30)->withQueryString();
        $users = User::all();
        $projects = \App\Models\Project::all();
        $wallets = \App\Models\Account::selectable();
        
        $transactionIds = $logs->pluck('transaction_id')->filter()->unique();
        $liveIds = \App\Models\Transaction::whereIn('id', $transactionIds)->pluck('id')->flip();

        // Calculate statistics for the top cards based on the selected period
        // We use Transaction model for live data (in, out, transfers) and AuditLog for canceled operations.
        $txQuery = \App\Models\Transaction::query();
        $canceledQuery = \App\Models\AuditLog::where('action', 'deleted');

        if ($period === 'today') {
            $txQuery->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()]);
            $canceledQuery->whereBetween('happened_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()]);
        } elseif ($period === 'yesterday') {
            $txQuery->whereBetween('created_at', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()]);
            $canceledQuery->whereBetween('happened_at', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()]);
        } elseif ($period === 'month') {
            $txQuery->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            $canceledQuery->whereBetween('happened_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($period === 'custom') {
            if ($request->filled('date_from')) {
                $txQuery->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
                $canceledQuery->where('happened_at', '>=', Carbon::parse($request->date_from)->startOfDay());
            }
            if ($request->filled('date_to')) {
                $txQuery->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
                $canceledQuery->where('happened_at', '<=', Carbon::parse($request->date_to)->endOfDay());
            }
        }

        $totalIn = (clone $txQuery)->where('direction', 'in')->where('ref_type', '!=', 'transfer')->sum('amount');
        $totalOut = (clone $txQuery)->where('direction', 'out')->where('ref_type', '!=', 'transfer')->sum('amount');
        $totalTransfers = (clone $txQuery)->where('ref_type', 'transfer')->where('direction', 'out')->sum('amount'); // Count one side only
        $canceledCount = $canceledQuery->whereIn('ref_type', ['manual', 'client_payment', 'transfer'])->count(); // mostly manual actions

        return view('radar.index', compact('logs', 'users', 'projects', 'period', 'liveIds', 'wallets', 'totalIn', 'totalOut', 'totalTransfers', 'canceledCount'));
    }

    public function revert(Request $request, AuditLog $log)
    {
        $request->validate(['current_password' => ['required', 'string']]);
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, auth()->user()->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'current_password' => 'كلمة مرور الأدمن غير صحيحة.',
            ]);
        }

        if ($log->action === 'deleted') {
            return back()->with('error', 'هذه الحركة محذوفة بالفعل.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($log) {
            if ($log->ref_type === 'asset') {
                $asset = \App\Models\Asset::find($log->ref_id);
                if ($asset) {
                    if ($log->type === 'إهلاك أصل') {
                        $asset->current_value += $log->amount;
                        $asset->status = 'active';
                        $asset->save();
                    } elseif ($log->type === 'شراء أصل (دفتري)') {
                        $asset->delete();
                    }
                }
            }

            // Mark log as deleted
            AuditLog::create([
                'action'       => 'deleted',
                'direction'    => $log->direction,
                'type'         => 'عكس ' . $log->type,
                'party'        => $log->party,
                'amount'       => $log->amount,
                'ref_type'     => $log->ref_type,
                'ref_id'       => $log->ref_id,
                'description'  => "تم عكس الحركة من الرادار: " . $log->description,
                'date'         => now()->toDateString(),
                'performed_by' => auth()->id(),
                'happened_at'  => now(),
            ]);

            // We can also mark the original log as deleted by updating it, but AuditLog is append-only.
            // Actually it's better to just update the action to 'deleted' to hide the delete button.
            $log->update(['action' => 'deleted']);
        });

        return back()->with('success', 'تم عكس الحركة بنجاح.');
    }
}
