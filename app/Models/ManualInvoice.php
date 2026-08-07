<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class ManualInvoice extends Model
{
    use LogsActivity;

    protected $table = 'sy2_manual_invoices';

    protected $fillable = [
        'invoice_number', 'client_name', 'client_phone', 'client_address',
        'date', 'subtotal', 'discount', 'tax_pct', 'tax_amount',
        'tax_number', 'commercial_register',
        'total', 'paid_amount', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_pct' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManualInvoiceItem::class);
    }

    public function remaining(): float
    {
        return (float) $this->total + (float) $this->tax_amount - (float) $this->paid_amount;
    }

    public function grandTotal(): float
    {
        return (float) $this->total + (float) $this->tax_amount;
    }

    public function statusAr(): string
    {
        return match ($this->status) {
            'draft' => 'مسودة',
            'final' => 'نهائية',
            default => $this->status,
        };
    }

    public function statusTag(): string
    {
        return match ($this->status) {
            'final' => 'green',
            default => 'amber',
        };
    }

    // Generate next invoice number
    public static function nextNumber(): string
    {
        $last = static::max('id') ?? 0;
        return 'MI-' . ($last + 1001);
    }
}
