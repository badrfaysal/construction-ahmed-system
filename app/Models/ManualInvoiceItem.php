<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualInvoiceItem extends Model
{
    protected $table = 'sy2_manual_invoice_items';

    protected $fillable = [
        'manual_invoice_id', 'date', 'description', 'qty', 'unit', 'unit_price', 'total',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ManualInvoice::class, 'manual_invoice_id');
    }
}
