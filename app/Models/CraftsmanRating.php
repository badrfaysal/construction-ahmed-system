<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CraftsmanRating extends Model
{
    protected $fillable = ['craftsman_name', 'rating', 'notes'];
}
