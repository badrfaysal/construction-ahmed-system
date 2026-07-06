<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// دفعة تحصيل واحدة على عقد تقسيط — بتخصم من متبقّي العقد وتغذّي المحفظة
// عبر InstallmentPaymentObserver (حركة "in" في sy2_transactions).
class InstallmentPayment extends Model
{
    protected $table = 'sy2_installment_payments';

    protected $fillable = [
        'contract_id', 'project_id', 'account_id', 'amount_paid', 'discount_applied',
        'payment_date', 'method', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date'     => 'date',
            'amount_paid'      => 'decimal:2',
            'discount_applied' => 'decimal:2',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(InstallmentContract::class, 'contract_id');
    }
}
