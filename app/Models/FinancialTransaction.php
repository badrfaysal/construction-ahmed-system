<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Points at the FIRST system's money-movement audit log (`financial_transactions`,
// unprefixed, shared DB). We only ever write/edit/delete OUR OWN rows — the ones
// tagged `ref_type = 'construction'` (mirrors of sy2_transactions) — so every
// construction wallet move is visible inside the first system's operations log
// with a distinctive marker. We never touch the first system's own rows, and we
// never derive balances from this table (accounts.balance is the shared truth,
// mutated directly — verified 2026-07-06 that the first system does the same).
class FinancialTransaction extends Model
{
    protected $table = 'financial_transactions';

    // The first system doesn't use Eloquent timestamps (updated_at is left null),
    // so we manage created_at by hand to blend into its log chronology.
    public $timestamps = false;

    protected $fillable = [
        'type', 'subtype', 'amount', 'from_account_id', 'to_account_id',
        'notes', 'status', 'person_name', 'ref_id', 'ref_type',
        'cancelled_at', 'cancel_reason', 'created_at',
    ];

    // Marker put in front of every mirrored note so an operator scanning the
    // first system's log instantly knows the entry came from the construction
    // system (not just from ref_type, which isn't always shown in their UI).
    const MARKER = '🏗️ [مقاولات]';

    // ref_type value that tags every row this system owns in the shared log.
    const REF_TYPE = 'construction';
}
