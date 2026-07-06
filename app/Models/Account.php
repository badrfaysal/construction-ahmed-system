<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

// This model points at an external, unprefixed table ("accounts") that is
// SHARED with the first (fuel/factory/financial) system. Historically the
// construction system only ever touched ONE row (المقاولات, id 37). As of the
// 2026-07-06 merge, the user can now direct any construction money movement at
// ANY wallet in this table — so we read the full list for the wallet pickers
// and lock a chosen row when mutating its balance. We still never touch the
// table's schema; only the `balance` column of the row the user selected.
class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = ['balance'];

    public $timestamps = true;

    // The default wallet — used when no account is explicitly chosen, and for
    // read-only displays (dashboard card) that predate multi-wallet support.
    const WALLET_ID = 37;

    // Read-only lookup for the DEFAULT wallet's balance (dashboard card, etc.).
    public static function walletBalance(): float
    {
        return static::balanceOf(self::WALLET_ID);
    }

    // Read-only balance of any wallet by id (falls back to the default wallet).
    public static function balanceOf(?int $id): float
    {
        return (float) (static::query()->find($id ?: self::WALLET_ID)?->balance ?? 0);
    }

    // Locking lookup of the DEFAULT wallet for use inside a DB::transaction().
    public static function lockedWallet(): self
    {
        return static::lockedById(self::WALLET_ID);
    }

    // Locking lookup of any wallet by id (null → default) for use inside a
    // DB::transaction() when its balance is about to be mutated, so concurrent
    // expense/income writes can't race.
    public static function lockedById(?int $id): self
    {
        return static::query()->lockForUpdate()->findOrFail($id ?: self::WALLET_ID);
    }

    // All active wallets, ordered with المقاولات first, for the expense/income
    // wallet pickers. Returns lightweight objects: id, name, category, balance.
    public static function selectable(): Collection
    {
        return static::query()
            ->where('status', 'active')
            ->orderByRaw('id = ? DESC', [self::WALLET_ID])
            ->orderBy('account_name')
            ->get(['id', 'account_name', 'category', 'balance']);
    }

    // Human label for a wallet id (used in logs/statements). Cached per request.
    public static function nameOf(?int $id): string
    {
        static $cache = [];
        $id = $id ?: self::WALLET_ID;
        return $cache[$id] ??= (string) (static::query()->find($id)?->account_name ?? 'المقاولات');
    }

    // Arabic label for the account category (for grouping in the picker).
    public function categoryAr(): string
    {
        return match ($this->category) {
            'bank_wallet'    => 'بنوك ومحافظ',
            'safe_cash'      => 'خزائن نقدية',
            'project_sector' => 'قطاعات ومشاريع',
            default          => 'أخرى',
        };
    }
}
