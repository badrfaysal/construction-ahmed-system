<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

// This model points to the construction-specific table "sy2_accounts".
use App\Traits\LogsActivity;

class Account extends Model
{
    use LogsActivity;

    protected $table = 'sy2_accounts';

    protected $fillable = ['name', 'category', 'initial_balance', 'balance', 'status'];

    public $timestamps = true;

    // The default wallet — used when no account is explicitly chosen
    const WALLET_ID = 37;

    // Read-only lookup for the DEFAULT wallet's balance
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

    // Locking lookup of any wallet by id (null → default) for use inside a DB::transaction()
    public static function lockedById(?int $id): self
    {
        return static::query()->lockForUpdate()->findOrFail($id ?: self::WALLET_ID);
    }

    // All active wallets, ordered with المقاولات first, for the expense/income wallet pickers.
    public static function selectable(): Collection
    {
        return static::query()
            ->where('status', 'active')
            ->orderByRaw('id = ? DESC', [self::WALLET_ID])
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'balance']);
    }

    // Human label for a wallet id (used in logs/statements). Cached per request.
    public static function nameOf(?int $id): string
    {
        static $cache = [];
        $id = $id ?: self::WALLET_ID;
        return $cache[$id] ??= (string) (static::query()->find($id)?->name ?? 'المقاولات');
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
