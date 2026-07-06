<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Transaction;

// يكتب صف تدقيق ثابت (لا يُعدَّل ولا يُحذف أبداً) لكل إنشاء/تعديل/حذف لأي حركة
// في سy2_transactions — عشان "سجل الحركات" يفضل يعرض كل حاجة حصلت فعلاً، حتى
// الحركات اللي اتلغت (زي عكس دفعة) أو اتعدّلت بعد كده (زي تعديل مبلغ شراء).
// منفصل عمداً عن TransactionObserver (اللي بيحرّك المحفظة والمرآة الخارجية) —
// فشل هنا (لو حصل) ميأثرش على حركة الفلوس نفسها.
class TransactionAuditObserver
{
    public function created(Transaction $transaction): void
    {
        AuditLog::create($this->snapshot($transaction, 'created'));
    }

    public function updated(Transaction $transaction): void
    {
        $old = [];
        foreach (['direction', 'type', 'party', 'amount', 'project_id', 'band_id', 'account_id', 'description', 'date'] as $field) {
            if ($transaction->isDirty($field)) {
                $old[$field] = $transaction->getOriginal($field);
            }
        }

        AuditLog::create($this->snapshot($transaction, 'updated') + ['old_values' => $old ?: null]);
    }

    public function deleted(Transaction $transaction): void
    {
        AuditLog::create($this->snapshot($transaction, 'deleted'));
    }

    private function snapshot(Transaction $transaction, string $action): array
    {
        return [
            'action'         => $action,
            'transaction_id' => $transaction->id,
            'direction'      => $transaction->direction,
            'type'           => $transaction->type,
            'party'          => $transaction->party,
            'amount'         => $transaction->amount,
            'project_id'     => $transaction->project_id,
            'band_id'        => $transaction->band_id,
            'account_id'     => $transaction->account_id,
            'ref_type'       => $transaction->ref_type,
            'ref_id'         => $transaction->ref_id,
            'description'    => $transaction->description,
            'date'           => $transaction->date,
            'performed_by'   => auth()->check() ? auth()->id() : null,
            'happened_at'    => now(),
        ];
    }
}
