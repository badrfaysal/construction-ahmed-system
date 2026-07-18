<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

use Illuminate\Support\Facades\DB;

class UniquePhone implements ValidationRule
{
    protected $ignoreTable;
    protected $ignoreId;
    protected $workerName;

    public function __construct($ignoreTable = null, $ignoreId = null, $workerName = null)
    {
        $this->ignoreTable = $ignoreTable;
        $this->ignoreId = $ignoreId;
        $this->workerName = $workerName;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) return;

        if ($this->ignoreTable === 'sy2_clients') {
            $clientQuery = DB::table('sy2_clients')->where('phone', $value);
            if ($this->ignoreId) {
                $clientQuery->where('id', '!=', $this->ignoreId);
            }
            if ($clientQuery->exists()) {
                $fail('رقم الموبايل مسجل مسبقاً في قائمة العملاء.');
            }
        }

        if ($this->ignoreTable === 'sy2_suppliers') {
            $supplierQuery = DB::table('sy2_suppliers')->where('phone', $value);
            if ($this->ignoreId) {
                $supplierQuery->where('id', '!=', $this->ignoreId);
            }
            if ($supplierQuery->exists()) {
                $fail('رقم الموبايل مسجل مسبقاً في قائمة الموردين.');
            }
        }

        // For workers, we allow the same technician to be added multiple times across projects
        // as long as the name matches.
        if ($this->ignoreTable === 'sy2_band_workers') {
            $workerQuery = DB::table('sy2_band_workers')->where('phone', $value);
            if ($this->ignoreId) {
                $workerQuery->where('id', '!=', $this->ignoreId);
            }
            if ($this->workerName) {
                // If the user uses the same phone for a DIFFERENT name, we warn them?
                // Actually, let's just let them reuse phones freely for workers, as it's common to use dummy phones.
                // Or we can keep the logic: fail if the phone exists for a *different* worker name.
                $workerQuery->where('name', '!=', $this->workerName);
                if ($workerQuery->exists()) {
                    $fail('رقم الموبايل مسجل مسبقاً لفني آخر. يرجى التأكد من الرقم أو تغيير الاسم ليتطابق مع المسجل.');
                }
            }
        }
    }
}
