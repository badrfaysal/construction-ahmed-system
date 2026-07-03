<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// This model points at an external, unprefixed table ("accounts") that
// belongs to a separate, unrelated business system sharing the same
// database. We only ever touch ONE specific row — the dedicated "المقاولات"
// wallet (id 37, category project_sector) — and never its schema or any
// other row/table in that system.
class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = ['balance'];

    public $timestamps = true;

    // Fixed on purpose — never resolved from user input, so this app can
    // never accidentally read/write a different account.
    const WALLET_ID = 37;

    // Read-only lookup for display (dashboard card, etc.) — no row lock.
    public static function walletBalance(): float
    {
        return (float) (static::query()->find(self::WALLET_ID)?->balance ?? 0);
    }

    // Locking lookup for use inside a DB::transaction() when the balance
    // is about to be mutated, so concurrent expense/income writes can't race.
    public static function lockedWallet(): self
    {
        return static::query()->lockForUpdate()->findOrFail(self::WALLET_ID);
    }
}
