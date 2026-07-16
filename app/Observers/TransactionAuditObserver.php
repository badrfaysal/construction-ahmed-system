<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Transaction;
use App\Jobs\SendTelegramNotification;

// يكتب صف تدقيق ثابت (لا يُعدَّل ولا يُحذف أبداً) لكل إنشاء/تعديل/حذف لأي حركة
// في سy2_transactions — عشان "سجل الحركات" يفضل يعرض كل حاجة حصلت فعلاً، حتى
// الحركات اللي اتلغت (زي عكس دفعة) أو اتعدّلت بعد كده (زي تعديل مبلغ شراء).
// منفصل عمداً عن TransactionObserver (اللي بيحرّك المحفظة والمرآة الخارجية) —
// فشل هنا (لو حصل) ميأثرش على حركة الفلوس نفسها.
class TransactionAuditObserver
{
    public function created(Transaction $transaction): void
    {
        $log = AuditLog::create($this->snapshot($transaction, 'created'));
        $this->dispatchTelegram($log);
    }

    public function updated(Transaction $transaction): void
    {
        $old = [];
        foreach (['direction', 'type', 'party', 'amount', 'project_id', 'band_id', 'account_id', 'description', 'date'] as $field) {
            if ($transaction->isDirty($field)) {
                $old[$field] = $transaction->getOriginal($field);
            }
        }

        $log = AuditLog::create($this->snapshot($transaction, 'updated') + ['old_values' => $old ?: null]);
        $this->dispatchTelegram($log);
    }

    public function deleted(Transaction $transaction): void
    {
        $log = AuditLog::create($this->snapshot($transaction, 'deleted'));
        $this->dispatchTelegram($log);
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

    private function dispatchTelegram(AuditLog $log): void
    {
        $actionName = match ($log->action) {
            'created' => '✨ إضافة جديدة',
            'updated' => '📝 تعديل حركة',
            'deleted' => '❌ عكس/إلغاء حركة',
            default => $log->action,
        };

        $project = $log->project ? $log->project->name : 'عام / غير محدد';
        $user = $log->performedBy ? $log->performedBy->name : 'النظام';
        $amountStr = number_format($log->amount, 2) . ' ج.م';

        if ($log->ref_type === 'material_invoice') {
            $invoice = \App\Models\MaterialInvoice::find($log->ref_id);
            if ($invoice && $invoice->total_amount > 0) {
                if ($log->amount == 0) {
                    $amountStr = number_format($invoice->total_amount, 2) . " ج.م (آجل بالكامل)";
                } elseif ($log->amount < $invoice->total_amount) {
                    $amountStr = number_format($log->amount, 2) . " ج.م (من أصل " . number_format($invoice->total_amount, 2) . ")";
                }
            }
        } elseif ($log->ref_type === 'material') {
            $material = \App\Models\Material::find($log->ref_id);
            if ($material) {
                $total = $material->qty * $material->unit_price;
                if ($total > 0) {
                    if ($log->amount == 0) {
                        $amountStr = number_format($total, 2) . " ج.م (آجل بالكامل)";
                    } elseif ($log->amount < $total) {
                        $amountStr = number_format($log->amount, 2) . " ج.م (من أصل " . number_format($total, 2) . ")";
                    }
                }
            }
        }

        $text = "<b>🏢 مـقـاولات 🏢</b>\n\n";
        $text .= "<b>العملية:</b> {$actionName}\n";
        $text .= "<b>النوع:</b> {$log->type}\n";
        $text .= "<b>المبلغ:</b> {$amountStr}\n";
        if ($log->party) {
            $text .= "<b>الطرف:</b> {$log->party}\n";
        }
        $text .= "<b>المشروع:</b> {$project}\n";
        if ($log->description) {
            $text .= "<b>الوصف:</b> {$log->description}\n";
        }
        $text .= "<b>بواسطة:</b> {$user}\n";

        SendTelegramNotification::dispatchSync($text);
    }
}
