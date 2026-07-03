<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;

// Read-only — transactions are generated automatically by MaterialObserver,
// InstallmentObserver, and ProjectBandObserver whenever real activity happens
// in the system. No manual entry, no manual delete.
class TransactionController extends Controller
{
    // List all transactions, newest first, with optional project/direction filters
    public function index(Request $request)
    {
        $query = Transaction::with('project')->orderByDesc('date')->orderByDesc('id');

        if ($pid = $request->get('project_id')) {
            $query->where('project_id', $pid);
        }

        if ($dir = $request->get('direction')) {
            $query->where('direction', $dir);
        }

        $transactions = $query->paginate(50);
        $projects     = Project::orderBy('name')->get(['id', 'name']);

        $totalIn  = (clone $query->getQuery())->where('direction', 'in')->sum('amount');
        $totalOut = (clone $query->getQuery())->where('direction', 'out')->sum('amount');

        return view('transactions.index', compact('transactions', 'projects', 'totalIn', 'totalOut'));
    }
}
