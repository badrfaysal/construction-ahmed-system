<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One return against a specific material purchase — a purchase can have
// several of these added over time, instead of a single fixed returned_qty
class MaterialReturn extends Model
{
    protected $table = 'sy2_material_returns';

    protected $fillable = ['material_id', 'qty', 'return_price', 'date', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    // السعر الفعلي اللي اترجعت بيه الوحدة — لو مش متحدد، بيفترض إنه نفس
    // سعر الشراء الأصلي (مرتجع عادي بدون خسارة)
    public function effectivePrice(): float
    {
        return (float) ($this->return_price ?? $this->material->unit_price);
    }

    // خسارة الإرجاع = لو رجّعنا بسعر أقل من اللي اشترينا بيه، الفرق ده
    // خسارة محقّقة على الكمية المرتجعة كلها (المدفوع منها والآجل)
    public function loss(): float
    {
        $orig = (float) $this->material->unit_price;
        $ret  = $this->effectivePrice();
        return $ret < $orig ? round((float) $this->qty * ($orig - $ret), 2) : 0.0;
    }
}
