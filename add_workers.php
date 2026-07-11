<?php

$projects = \App\Models\Project::all();

foreach ($projects as $proj) {
    // Add Client Payments (وارد)
    \Illuminate\Support\Facades\DB::table('sy2_transactions')->insert([
        'account_id' => 37,
        'project_id' => $proj->id,
        'type' => 'in',
        'amount' => rand(20000, 50000),
        'ref_type' => 'client_payment',
        'party' => 'العميل',
        'date' => now()->subDays(rand(10, 40)),
        'description' => 'دفعة مقدمة من العميل',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach($proj->bands as $band) {
        // Add Workers
        $workerNames = ['محمد السيد', 'محمود علي', 'سيد إبراهيم', 'حسن مصطفى', 'أسامة ربيع'];
        for ($w = 0; $w < 2; $w++) {
            $workerId = \Illuminate\Support\Facades\DB::table('sy2_band_workers')->insertGetId([
                'project_band_id' => $band->id,
                'name' => $workerNames[array_rand($workerNames)],
                'phone' => '01' . rand(100000000, 999999999),
                'specialty' => 'صنايعي ' . $band->name,
                'contract_type' => 'lump_sum',
                'amount' => rand(2000, 8000),
                'start_date' => now()->subDays(rand(5, 50)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add Payments to workers (صادر)
            if (rand(1, 10) > 2) {
                \Illuminate\Support\Facades\DB::table('sy2_worker_payments')->insert([
                    'band_worker_id' => $workerId,
                    'project_id' => $proj->id,
                    'project_band_id' => $band->id,
                    'account_id' => 37,
                    'amount' => rand(500, 1500),
                    'discount' => rand(0, 1) ? rand(100, 300) : 0,
                    'discount_reason' => 'خصم تأخير',
                    'date' => now()->subDays(rand(1, 20)),
                    'method' => 'cash',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    $proj->recalculateCachedTotals();
}

echo 'Added workers and payments successfully.';
