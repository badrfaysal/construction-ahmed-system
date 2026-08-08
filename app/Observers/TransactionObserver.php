<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

// Keeps the chosen wallet (any row in the sy2_accounts table — default
// المقاولات id 37) in sync with every transaction this system records: an "out"
// debits it, an "in" credits it, blocked if an "out" would overdraw it.
//
// Every create/store call that leads here must run inside DB::transaction() so
// an insufficient-funds rejection rolls back the whole operation (e.g. the
// material/labor row that triggered it), not just the wallet.
class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        if ($transaction->type !== 'client_discount') {
            $this->apply($transaction->direction, (float) $transaction->amount, $transaction->account_id, block: true);
        }
        $this->recalculateProjectTotals($transaction);
    }

    public function updated(Transaction $transaction): void
    {
        $oldDirection = $transaction->getOriginal('direction');
        $oldAmount    = (float) $transaction->getOriginal('amount');
        $oldAccount   = $transaction->getOriginal('account_id');

        // Undo the previous effect first (reversing money already accounted for
        // is always allowed — it never gets blocked), then apply the new one
        // (only the new "out" side can be blocked for insufficient funds). This
        // correctly moves money between wallets when account_id itself changed.
        if ($transaction->type !== 'client_discount') {
            $this->apply($oldDirection, $oldAmount, $oldAccount, block: false, reverse: true);
            $this->apply($transaction->direction, (float) $transaction->amount, $transaction->account_id, block: true);
        }
        $this->recalculateProjectTotals($transaction);
    }

    public function deleted(Transaction $transaction): void
    {
        if ($transaction->type !== 'client_discount') {
            $this->apply($transaction->direction, (float) $transaction->amount, $transaction->account_id, block: false, reverse: true);
        }
        $this->recalculateProjectTotals($transaction);
    }

    private function recalculateProjectTotals(Transaction $transaction): void
    {
        if (in_array($transaction->ref_type, ['client_payment', 'marketer_commission', 'project_expense']) && $transaction->project_id) {
            $transaction->project?->recalculateCachedTotals();
        }
    }

    // ── Wallet balance mutation ────────────────────────────────────────────
    private function apply(string $direction, float $amount, ?int $accountId, bool $block, bool $reverse = false): void
    {
        if ($amount == 0) {
            return;
        }

        // "out" normally debits the wallet, "in" normally credits it —
        // reversing a previous effect flips that.
        $isDebit = $reverse ? $direction === 'in' : $direction === 'out';

        $wallet     = Account::lockedById($accountId);
        $newBalance = (float) $wallet->balance + ($isDebit ? -$amount : $amount);

        if ($block && $isDebit && $newBalance < 0) {
            throw ValidationException::withMessages([
                'wallet' => 'الرصيد في محفظة "' . $wallet->name . '" غير كافٍ لإتمام هذه العملية (الرصيد الحالي: '
                    . number_format((float) $wallet->balance, 2) . ' ج.م).',
            ]);
        }

        $wallet->update(['balance' => $newBalance]);
    }
}
