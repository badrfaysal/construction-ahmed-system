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
    // The three hand-entered kinds → their fixed direction, label and default party
    private const KINDS = [
        'capital'       => ['direction' => 'in',  'type' => 'تغذية رأس مال', 'party' => 'رأس المال'],
        'withdrawal'    => ['direction' => 'out', 'type' => 'مسحوبات شخصية', 'party' => 'صاحب الشركة'],
        'admin_expense' => ['direction' => 'out', 'type' => 'مصروف إداري',   'party' => 'مصروف إداري'],
    ];

    public function index()
    {
        $balance = Account::walletBalance();
        $wallets = Account::selectable();

        // Only the hand-entered rows are listed/deletable here — the auto ones
        // live in سجل الحركات and must never be hand-edited.
        $manual = Transaction::with('account')
            ->where('ref_type', 'manual')
            ->orderByDesc('date')->orderByDesc('id')
            ->paginate(30);

        return view('wallet.index', compact('balance', 'wallets', 'manual'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kind'        => ['required', 'in:capital,withdrawal,admin_expense'],
            'account_id'  => ['required', 'integer', 'exists:accounts,id'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'date'        => ['required', 'date'],
            'party'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $kind = self::KINDS[$data['kind']];

        // Wrapped so a withdrawal/expense that would overdraw the chosen wallet is
        // rejected by TransactionObserver and rolls back cleanly.
        DB::transaction(fn () => Transaction::create([
            'project_id'  => null,
            'band_id'     => null,
            'account_id'  => $data['account_id'] ?? null,
            'direction'   => $kind['direction'],
            'type'        => $kind['type'],
            'party'       => $data['party'] ?: $kind['party'],
            'amount'      => $data['amount'],
            'date'        => $data['date'],
            'description' => $data['description'] ?? null,
            'ref_type'    => 'manual',
            'ref_id'      => null,
        ]));

        return back()->with('success', 'تم تسجيل الحركة في المحفظة.');
    }

    public function destroy(Transaction $transaction)
    {
        // Guard: only hand-entered rows can be removed here. Auto rows belong to
        // a material/band/return/payment and must be reversed at their source.
        if ($transaction->ref_type !== 'manual') {
            return back()->with('error', 'دي حركة تلقائية مش بتتحذف من هنا.');
        }

        DB::transaction(fn () => $transaction->delete());

        return back()->with('success', 'تم حذف الحركة.');
    }
}
