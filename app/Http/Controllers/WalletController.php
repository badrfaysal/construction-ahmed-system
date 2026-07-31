<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// محفظة المقاولات control screen — the balance plus the ONE place money can be
// moved by hand (everything else in the system books itself automatically):
//   • تغذية رأس مال      → cash put into the business  (in)
//   • مسحوبات شخصية      → owner drawing profits out   (out)
//   • مصروف إداري عام    → office/overhead not tied to a project (out)
// Each is a normal sy2_transactions row (ref_type = manual) so it flows through
// TransactionObserver and moves the wallet like any other entry.
class WalletController extends Controller
{
    private const KINDS = [
        'capital'       => ['direction' => 'in',  'type' => 'تغذية رأس مال'],
        'withdrawal'    => ['direction' => 'out', 'type' => 'مسحوبات شخصية'],
        'admin_expense' => ['direction' => 'out', 'type' => 'مصروف إداري عام'],
    ];

    public function index()
    {
        $wallets = Account::selectable();
        
        $manual = Transaction::with('account')
            ->where('ref_type', 'manual')
            ->orWhere('ref_type', 'transfer')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15);
            
        $balance = $wallets->firstWhere('id', Account::WALLET_ID)?->balance ?? 0;

        return view('wallet.index', compact('wallets', 'manual', 'balance'));
    }

    public function store(Request $request)
    {
        $messages = [
            'kind.required'       => 'يرجى اختيار نوع الحركة.',
            'account_id.required' => 'يرجى اختيار المحفظة.',
            'amount.required'     => 'يرجى إدخال المبلغ.',
            'date.required'       => 'يرجى تحديد التاريخ.',
            'party.required'      => 'يرجى كتابة الجهة / المصدر لهذه الحركة.',
        ];

        $data = $request->validate([
            'kind'        => ['required', 'in:capital,withdrawal,admin_expense'],
            'account_id'  => ['required', 'integer', 'exists:sy2_accounts,id'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'date'        => ['required', 'date'],
            'party'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], $messages);

        $kind = self::KINDS[$data['kind']];

        // Wrapped so a withdrawal/expense that would overdraw the chosen wallet is
        // rejected by TransactionObserver and rolls back cleanly.
        DB::transaction(function () use ($data, $kind) {
            $transaction = Transaction::create([
                'project_id'  => null,
                'band_id'     => null,
                'account_id'  => $data['account_id'],
                'direction'   => $kind['direction'],
                'type'        => $kind['type'],
                'party'       => $data['party'],
                'amount'      => $data['amount'],
                'date'        => $data['date'],
                'description' => $data['description'] ?? null,
                'ref_type'    => 'manual',
                'ref_id'      => null,
            ]);

            if ($data['kind'] !== 'capital') {
                \App\Models\ManualDebt::create([
                    'type'         => 'receivable',
                    'party'        => $data['party'],
                    'description'  => $data['description'] ?? null,
                    'total_amount' => $data['amount'],
                    'paid_amount'  => 0,
                    'status'       => 'pending',
                    'date'         => $data['date'],
                ]);
            }
        });

        return back()->with('success', 'تم تسجيل الحركة بنجاح.');
    }

    public function statement(Account $account)
    {
        $transactions = Transaction::with(['project', 'band', 'account'])
            ->where('account_id', $account->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(30);

        $wallets = Account::selectable()->where('id', '!=', $account->id);

        return view('wallet.statement', compact('account', 'transactions', 'wallets'));
    }

    public function transfer(Request $request)
    {
        $messages = [
            'from_account_id.required' => 'يرجى اختيار المحفظة المحول منها.',
            'to_account_id.required'   => 'يرجى اختيار المحفظة المحول إليها.',
            'to_account_id.different'  => 'لا يمكن التحويل لنفس المحفظة.',
            'amount.required'          => 'يرجى إدخال المبلغ.',
            'date.required'            => 'يرجى تحديد التاريخ.',
        ];

        $data = $request->validate([
            'from_account_id' => ['required', 'integer', 'exists:sy2_accounts,id'],
            'to_account_id'   => ['required', 'integer', 'exists:sy2_accounts,id', 'different:from_account_id'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'date'            => ['required', 'date'],
            'description'     => ['nullable', 'string', 'max:1000'],
        ], $messages);

        $from = Account::findOrFail($data['from_account_id']);
        $to = Account::findOrFail($data['to_account_id']);

        DB::transaction(function () use ($data, $from, $to) {
            Transaction::create([
                'project_id'  => null,
                'band_id'     => null,
                'account_id'  => $from->id,
                'direction'   => 'out',
                'type'        => 'تحويل صادر',
                'party'       => 'إلى: ' . $to->name,
                'amount'      => $data['amount'],
                'date'        => $data['date'],
                'description' => $data['description'] ?? null,
                'ref_type'    => 'transfer',
                'ref_id'      => null,
            ]);

            Transaction::create([
                'project_id'  => null,
                'band_id'     => null,
                'account_id'  => $to->id,
                'direction'   => 'in',
                'type'        => 'تحويل وارد',
                'party'       => 'من: ' . $from->name,
                'amount'      => $data['amount'],
                'date'        => $data['date'],
                'description' => $data['description'] ?? null,
                'ref_type'    => 'transfer',
                'ref_id'      => null,
            ]);
        });

        return back()->with('success', 'تم تحويل المبلغ بنجاح.');
    }
}
