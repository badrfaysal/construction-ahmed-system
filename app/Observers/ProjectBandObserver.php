<?php

namespace App\Observers;

use App\Models\ProjectBand;
use App\Models\Transaction;

// Labor is NO LONGER auto-debited in full when a band is saved. Craftsmen are
// paid in installments (دفعات) as the work progresses, and only those actual
// payments touch محفظة المقاولات (see WorkerPaymentObserver). The band's
// labor_amount is just the agreed commitment — it drives cost/profit figures,
// not the wallet.
//
// This observer now only exists to clean up the legacy per-band "أجور فنيين"
// transaction that older bands may still carry from before the دفعات model:
// re-saving or deleting such a band reverses that stale full debit so the
// wallet isn't double-counting it against the new payment entries.
class ProjectBandObserver
{
    public function updated(ProjectBand $band): void
    {
        $this->removeLegacyLaborTransaction($band);
    }

    public function deleted(ProjectBand $band): void
    {
        $this->removeLegacyLaborTransaction($band);
    }

    private function removeLegacyLaborTransaction(ProjectBand $band): void
    {
        // Model instance (not query-builder) so TransactionObserver fires and
        // credits the wallet back for the reversed legacy labor debit.
        Transaction::where('ref_type', 'band')->where('ref_id', $band->id)->first()?->delete();
    }
}
