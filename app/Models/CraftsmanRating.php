<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class CraftsmanRating extends Model
{
    use LogsActivity;

    protected $fillable = ['craftsman_name', 'rating', 'notes'];
}
