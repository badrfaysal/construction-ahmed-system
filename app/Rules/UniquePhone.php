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

        // Check clients
        $clientQuery = DB::table('sy2_clients')->where('phone', $value);
        if ($this->ignoreTable === 'sy2_clients' && $this->ignoreId) {
            $clientQuery->where('id', '!=', $this->ignoreId);
        }
        if ($clientQuery->exists()) {
            $fail('رقم الموبايل مسجل مسبقاً في قائمة العملاء.');
            return;
        }

        // Check suppliers
        $supplierQuery = DB::table('sy2_suppliers')->where('phone', $value);
        if ($this->ignoreTable === 'sy2_suppliers' && $this->ignoreId) {
            $supplierQuery->where('id', '!=', $this->ignoreId);
        }
        if ($supplierQuery->exists()) {
            $fail('رقم الموبايل مسجل مسبقاً في قائمة الموردين.');
            return;
        }

        // Check workers
        $workerQuery = DB::table('sy2_band_workers')->where('phone', $value);
        if ($this->ignoreTable === 'sy2_band_workers') {
            if ($this->ignoreId) {
                $workerQuery->where('id', '!=', $this->ignoreId);
            }
            if ($this->workerName) {
                $workerQuery->where('name', '!=', $this->workerName);
            }
        }
        if ($workerQuery->exists()) {
            $fail('رقم الموبايل مسجل مسبقاً في قائمة الفنيين/الصنايعية.');
            return;
        }
    }
}
