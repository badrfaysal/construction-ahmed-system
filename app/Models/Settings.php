<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Single-row settings table — always use current() to get/create that one row.
class Settings extends Model
{
    protected $table = 'sy2_settings';

    protected $fillable = [
        'default_supervision_pct', 'company_name', 'company_tagline',
        'company_phone', 'company_registration', 'whatsapp_country_code',
    ];

    public static function current(): self
    {
        if ($settings = static::find(1)) {
            return $settings;
        }

        $settings = new static([
            'default_supervision_pct' => 0,
            'company_name'            => 'شركة الضبع للتجارة والتوريدات',
            'company_tagline'         => 'مقاولات وتشطيبات · القاهرة',
            'company_phone'           => '0100 000 0000',
            'company_registration'    => '12345',
            'whatsapp_country_code'   => '20',
        ]);
        $settings->id = 1;
        $settings->save();

        return $settings;
    }
}
