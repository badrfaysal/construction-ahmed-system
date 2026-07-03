<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Validation\ValidationException;

// Keeps محفظة المقاولات (the "المقاولات" wallet, sy2's one designated row in
// the external accounts table) in sync with every transaction this system
// records — an "out" transaction debits it, an "in" transaction credits it.
// Every create/store call that leads here must run inside DB::transaction()
// so an insufficient-funds rejection rolls back the whole operation
// (e.g. the material/labor row that triggered it), not just the wallet.
class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->apply($transaction->direction, (float) $transaction->amount, block: true);
    }

    public function updated(Transaction $transaction): void
    {
        $oldDirection = $transaction->getOriginal('direction');
        $oldAmount    = (float) $transaction->getOriginal('amount');
        $newDirection = $transaction->direction;
        $newAmount    = (float) $transaction->amount;

        // Undo the previous effect first (reversing money already accounted
        // for is always allowed — it never gets blocked), then apply the new
        // one (only the new "out" side can be blocked for insufficient funds).
        $this->apply($oldDirection, $oldAmount, block: false, reverse: true);
        $this->apply($newDirection, $newAmount, block: true);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->apply($transaction->direction, (float) $transaction->amount, block: false, reverse: true);
    }

    private function apply(string $direction, float $amount, bool $block, bool $reverse = false): void
    {
        if ($amount == 0) {
            return;
        }

        // "out" normally debits the wallet, "in" normally credits it —
        // reversing a previous effect flips that.
        $isDebit = $reverse ? $direction === 'in' : $direction === 'out';

        $wallet = Account::lockedWallet();
        $newBalance = (float) $wallet->balance + ($isDebit ? -$amount : $amount);

        if ($block && $isDebit && $newBalance < 0) {
            throw ValidationException::withMessages([
                'wallet' => 'الرصيد في محفظة "المقاولات" غير كافٍ لإتمام هذه العملية (الرصيد الحالي: '
                    . number_format((float) $wallet->balance, 2) . ' ج.م).',
            ]);
        }

        $wallet->update(['balance' => $newBalance]);
    }
}
