<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class Client extends Model
{
    use LogsActivity;

    protected $table = 'sy2_clients';

    protected $fillable = ['name', 'phone', 'email', 'address', 'notes'];

    // A client can have multiple projects
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id');
    }
}
