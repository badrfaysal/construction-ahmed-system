<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Login and permissions are shared with the first (fuel/factory) system,
    // which lives in the same unified database — so we authenticate against
    // its unprefixed "users" table, NOT our old sy2_users. We only ever read
    // this table for auth (plus Laravel's normal remember_token write); we
    // never alter its schema or touch rows on behalf of the other system.
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'hide_financials',
        'is_active',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hide_financials' => 'boolean',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
        ];
    }

    // ---- Role helpers — the shared users table uses admin/employee/viewer ----

    // Full access: settings, profit/margins, cost statements
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Read-only account — blocked from every write action (see BlockViewerWrites)
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    // Anyone who isn't a viewer can create/edit/delete
    public function canManage(): bool
    {
        return ! $this->isViewer();
    }

    // Financial visibility (profit, margins, real cost, company cost statement)
    // is admin-only, and even an admin can have it switched off per-account
    // via the first system's hide_financials flag.
    public function canSeeFinancials(): bool
    {
        return $this->isAdmin() && ! $this->hide_financials;
    }
}
