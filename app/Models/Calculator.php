<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calculator extends Model
{
    use HasFactory;

    protected $table = 'sy2_calculators';

    protected $fillable = [
        'title',
        'work_type',
        'global_height',
        'spaces',
        'total_paints',
        'total_floor_ceramics',
        'total_wall_ceramics',
        'total_deductions',
    ];

    protected $casts = [
        'spaces' => 'array',
        'global_height' => 'float',
        'total_paints' => 'float',
        'total_floor_ceramics' => 'float',
        'total_wall_ceramics' => 'float',
        'total_deductions' => 'float',
    ];
}
