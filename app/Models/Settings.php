<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Single-row settings table — always use current() to get/create that one row.
use App\Traits\LogsActivity;

class Settings extends Model
{
    use LogsActivity;

    protected $table = 'sy2_settings';

    protected $fillable = [
        'company_name', 'company_tagline',
        'company_phone', 'whatsapp_country_code',
        'expense_categories',
    ];

    protected function casts(): array
    {
        return [
            'expense_categories' => 'array',
        ];
    }

    public static function current(): self
    {
        if ($settings = static::find(1)) {
            return $settings;
        }

        $settings = new static([
            'company_name'            => 'شركة الضبع للتجارة والتوريدات',
            'company_tagline'         => 'مقاولات وتشطيبات · القاهرة',
            'company_phone'           => '0100 000 0000',
            'whatsapp_country_code'   => '20',
            'expense_categories'      => ['إيجار', 'كهرباء ومياه', 'بوفيه', 'نقل ومواصلات', 'صيانة', 'أدوات مكتبية', 'مرتبات', 'إكراميات'],
        ]);
        $settings->id = 1;
        $settings->save();

        return $settings;
    }
}
