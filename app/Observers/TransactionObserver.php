<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FinancialTransaction;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

// Keeps the chosen wallet (any row in the shared `accounts` table — default
// المقاولات id 37) in sync with every transaction this system records: an "out"
// debits it, an "in" credits it, blocked if an "out" would overdraw it.
//
// Since the 2026-07-06 merge it ALSO mirrors every construction money move into
// the first system's audit log (`financial_transactions`) as a distinctively
// marked row (ref_type = construction), so those moves are visible there with
// the action + reason. accounts.balance stays the single shared source of truth
// (mutated directly here); the mirror is a pure log entry, never a balance
// source, so there's no double-counting.
//
// Every create/store call that leads here must run inside DB::transaction() so
// an insufficient-funds rejection rolls back the whole operation (e.g. the
// material/labor row that triggered it), not just the wallet.
class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->apply($transaction->direction, (float) $transaction->amount, $transaction->account_id, block: true);
        $this->mirrorUpsert($transaction);
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
        $this->apply($oldDirection, $oldAmount, $oldAccount, block: false, reverse: true);
        $this->apply($transaction->direction, (float) $transaction->amount, $transaction->account_id, block: true);

        $this->mirrorUpsert($transaction);
        $this->recalculateProjectTotals($transaction);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->apply($transaction->direction, (float) $transaction->amount, $transaction->account_id, block: false, reverse: true);
        $this->mirrorDelete($transaction);
        $this->recalculateProjectTotals($transaction);
    }

    private function recalculateProjectTotals(Transaction $transaction): void
    {
        if ($transaction->ref_type === 'client_payment' && $transaction->project_id) {
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
                'wallet' => 'الرصيد في محفظة "' . $wallet->account_name . '" غير كافٍ لإتمام هذه العملية (الرصيد الحالي: '
                    . number_format((float) $wallet->balance, 2) . ' ج.م).',
            ]);
        }

        $wallet->update(['balance' => $newBalance]);
    }

    // ── Mirror into the first system's audit log ───────────────────────────
    private function mirrorUpsert(Transaction $transaction): void
    {
        $walletId = $transaction->account_id ?: Account::WALLET_ID;
        $isIn     = $transaction->direction === 'in';

        $attrs = [
            'type'            => $isIn ? 'income' : 'general_expense',
            'subtype'         => null,
            'amount'          => (float) $transaction->amount,
            'from_account_id' => $isIn ? null : $walletId,
            'to_account_id'   => $isIn ? $walletId : null,
            'notes'           => $this->mirrorNote($transaction),
            'status'          => 'active',
            'person_name'     => $transaction->party,
            'cancelled_at'    => null,
            'cancel_reason'   => null,
        ];

        $row = FinancialTransaction::where('ref_type', FinancialTransaction::REF_TYPE)
            ->where('ref_id', $transaction->id)
            ->first();

        if ($row) {
            $row->update($attrs);
            return;
        }

        FinancialTransaction::create($attrs + [
            'ref_type'   => FinancialTransaction::REF_TYPE,
            'ref_id'     => $transaction->id,
            // Business date + real clock time → sits at the right spot in their
            // chronological log while keeping same-day ordering stable.
            'created_at' => $transaction->date
                ? Carbon::parse($transaction->date)->setTimeFrom(now())
                : now(),
        ]);
    }

    private function mirrorDelete(Transaction $transaction): void
    {
        FinancialTransaction::where('ref_type', FinancialTransaction::REF_TYPE)
            ->where('ref_id', $transaction->id)
            ->delete();
    }

    // "🏗️ [مقاولات] تم صرف/تحصيل — <النوع>: <السبب> (<الجهة>)"
    private function mirrorNote(Transaction $transaction): string
    {
        $verb   = $transaction->direction === 'in' ? 'تحصيل' : 'صرف';
        $reason = trim((string) ($transaction->description ?? ''));

        $note = FinancialTransaction::MARKER . ' ' . $verb . ' — ' . ($transaction->type ?: 'حركة');
        if ($reason !== '') {
            $note .= ': ' . $reason;
        }
        if ($transaction->party) {
            $note .= ' (' . $transaction->party . ')';
        }

        return mb_substr($note, 0, 1000);
    }
}
