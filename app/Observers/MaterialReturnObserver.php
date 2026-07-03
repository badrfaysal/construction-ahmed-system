<?php

namespace App\Observers;

use App\Models\MaterialReturn;
use App\Models\Transaction;

// A return changes its parent purchase's net cost, which must flow through to
// سجل الحركات (and محفظة المقاولات via TransactionObserver). We re-sync the
// material's transaction here — going through the Transaction model instance so
// its observer fires — because Material::touch() alone does NOT fire the
// material's own "updated" event.
class MaterialReturnObserver
{
    // Adding a return lowers net cost → credits the wallet back
    public function created(MaterialReturn $return): void
    {
        $this->resync($return);
    }

    // Removing a return raises net cost back up → re-debits the wallet
    // (blocked by TransactionObserver if the balance can no longer cover it)
    public function deleted(MaterialReturn $return): void
    {
        $this->resync($return);
    }

    private function resync(MaterialReturn $return): void
    {
        $material = $return->material()->with('returns')->first();
        if (! $material) {
            return;
        }

        $tx = Transaction::where('ref_type', 'material')->where('ref_id', $material->id)->first();
        $tx?->update([
            'amount'      => $material->netCost(),
            'description' => $material->item . ' — ' . number_format($material->netQty(), 1) . ' ' . $material->unit,
        ]);
    }
}
