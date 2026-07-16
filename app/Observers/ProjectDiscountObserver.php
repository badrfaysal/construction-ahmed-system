<?php

namespace App\Observers;

use App\Models\ProjectDiscount;
use App\Models\Transaction;

class ProjectDiscountObserver
{
    /**
     * Handle the ProjectDiscount "created" event.
     */
    public function created(ProjectDiscount $projectDiscount): void
    {
        // Add a "virtual" transaction so it appears in the ledger/radar
        Transaction::create([
            'project_id'  => $projectDiscount->project_id,
            'direction'   => 'in',
            'type'        => 'client_discount',
            'party'       => $projectDiscount->project->client->name,
            'amount'      => $projectDiscount->amount,
            'date'        => $projectDiscount->date,
            'description' => 'منح خصم: ' . $projectDiscount->notes,
            'ref_type'    => 'discount',
            'ref_id'      => $projectDiscount->id,
        ]);
    }

    /**
     * Handle the ProjectDiscount "updated" event.
     */
    public function updated(ProjectDiscount $projectDiscount): void
    {
        $tx = Transaction::where('ref_type', 'discount')->where('ref_id', $projectDiscount->id)->first();
        if ($tx) {
            $tx->update([
                'amount'      => $projectDiscount->amount,
                'date'        => $projectDiscount->date,
                'description' => 'منح خصم: ' . $projectDiscount->notes,
            ]);
        }
    }

    /**
     * Handle the ProjectDiscount "deleting" event.
     */
    public function deleting(ProjectDiscount $projectDiscount): void
    {
        Transaction::where('ref_type', 'discount')->where('ref_id', $projectDiscount->id)->delete();
    }
}
