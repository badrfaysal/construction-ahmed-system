<?php

namespace App\Observers;

use App\Models\ProjectBand;
use App\Models\Transaction;

// Keeps سجل الحركات in sync with labor/wage payments automatically —
// a band only has an "out" transaction while it actually has a labor_amount set
class ProjectBandObserver
{
    public function created(ProjectBand $band): void
    {
        if ($band->labor_amount > 0) {
            $this->createTransaction($band);
        }
    }

    // Sync the transaction when labor_amount, team, or date changes
    public function updated(ProjectBand $band): void
    {
        $existing = Transaction::where('ref_type', 'band')->where('ref_id', $band->id)->first();

        if ($band->labor_amount > 0) {
            if ($existing) {
                $existing->update([
                    'amount' => $band->labor_amount,
                    'date'   => $band->labor_date ?? today(),
                    'party'  => $band->team_name ?: $band->name,
                ]);
            } else {
                $this->createTransaction($band);
            }
        } elseif ($existing) {
            // Labor amount was cleared — no wage was actually paid
            $existing->delete();
        }
    }

    public function deleted(ProjectBand $band): void
    {
        // Model instance (not query-builder) so TransactionObserver fires and
        // credits the wallet back for this band's labor.
        Transaction::where('ref_type', 'band')->where('ref_id', $band->id)->first()?->delete();
    }

    private function createTransaction(ProjectBand $band): void
    {
        Transaction::create([
            'project_id'  => $band->project_id,
            'band_id'     => $band->id,
            'direction'   => 'out',
            'type'        => 'أجور فنيين',
            'party'       => $band->team_name ?: $band->name,
            'amount'      => $band->labor_amount,
            'date'        => $band->labor_date ?? today(),
            'description' => 'أجر بند ' . $band->name,
            'ref_type'    => 'band',
            'ref_id'      => $band->id,
        ]);
    }
}
