<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $table = 'sy2_quotes';

    protected $fillable = [
        'ref', 'client_id', 'client_name', 'phone', 'address', 'area',
        'date', 'status', 'note', 'project_id', 'terms',
        'tax_pct', 'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'tax_pct' => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function bands(): HasMany
    {
        return $this->hasMany(QuoteBand::class, 'quote_id')->orderBy('sort_order');
    }

    // If the quote was approved it may link to the resulting project
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Total price = sum of all band prices
    public function total(): float
    {
        return (float) $this->bands->sum('price');
    }

    // الإجمالي بعد الضريبة (الضريبة فقط هي اللي بتأثر — الخصم شكلي)
    public function totalWithTax(): float
    {
        $subtotal = $this->total();
        $tax = $subtotal * (float)($this->tax_pct ?? 0) / 100;
        return $subtotal + $tax;
    }

    public function taxAmount(): float
    {
        return $this->total() * (float)($this->tax_pct ?? 0) / 100;
    }

    // الخصم شكلي فقط — بيظهر في العرض بس مش بيأثر على الإجمالي
    public function discountAmount(): float
    {
        return (float)($this->discount_amount ?? 0);
    }

    public function statusAr(): string
    {
        return match ($this->status) {
            'draft'    => 'قيد المراجعة',
            'sent'     => 'تم الإرسال للعميل',
            'approved' => 'تم الموافقة',
            default    => $this->status,
        };
    }

    public function statusTag(): string
    {
        return match ($this->status) {
            'approved' => 'green',
            'sent'     => 'amber',
            default    => 'gray',
        };
    }

    // wa.me link pre-filled with a summary of the quote, ready to send as-is.
    // Normalizes local Egyptian numbers (01xxxxxxxxx) to international (20...).
    public function whatsappLink(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $countryCode = Settings::current()->whatsapp_country_code;
        $digits = preg_replace('/\D/', '', $this->phone);

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode . substr($digits, 1);
        } elseif (! str_starts_with($digits, $countryCode)) {
            $digits = $countryCode . $digits;
        }

        $lines = $this->bands->map(fn ($band) => "- {$band->name}: " . number_format($band->price) . ' ج.م')->implode("\n");

        $text = "عرض سعر رقم {$this->ref}\n"
            . "العميل: {$this->client_name}\n"
            . ($this->address ? "العنوان: {$this->address}\n" : '')
            . "\nالبنود:\n{$lines}"
            . "\n\nالإجمالي: " . number_format($this->total()) . ' ج.م';

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($text);
    }
}
