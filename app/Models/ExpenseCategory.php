<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $table = 'sy2_expense_categories';

    protected $fillable = [
        'name',
        'description',
    ];
}
