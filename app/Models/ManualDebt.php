<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class ManualDebt extends Model
{
    use LogsActivity;

    protected $table = 'sy2_manual_debts';

    protected $fillable = [
        'type', 'party', 'description', 'total_amount', 'paid_amount', 'status', 'date'
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function remaining(): float
    {
        return (float) $this->total_amount - (float) $this->paid_amount;
    }

    public function statusAr(): string
    {
        return match ($this->status) {
            'pending' => 'معلق',
            'partial' => 'جزئي',
            'paid'    => 'مسدد',
            default   => $this->status,
        };
    }

    public function statusTag(): string
    {
        return match ($this->status) {
            'paid'    => 'green',
            'partial' => 'amber',
            default   => 'red',
        };
    }
}
