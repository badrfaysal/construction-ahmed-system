<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client = \App\Models\Client::create(['name' => 'أحمد حسن', 'phone' => '01012345678']);

        $proj1 = \App\Models\Project::create([
            'client_id' => $client->id,
            'name' => 'فيلا التجمع الخامس',
            'status' => 'active',
            'start_date' => now()->subMonths(2),
        ]);

        $proj2 = \App\Models\Project::create([
            'client_id' => $client->id,
            'name' => 'شقة الرحاب',
            'status' => 'active',
            'start_date' => now()->subMonth(),
        ]);

        $bands = ['تأسيس كهرباء', 'تأسيس سباكة', 'محارة', 'جبس بورد', 'نقاشة'];

        $supplier = \App\Models\Supplier::create(['name' => 'شركة النور للأسمنت', 'phone' => '0123456789']);

        foreach ([$proj1, $proj2] as $index => $proj) {
            foreach ($bands as $i => $bandName) {
                $b = \App\Models\ProjectBand::create([
                    'project_id' => $proj->id,
                    'name' => $bandName,
                    'client_price' => rand(10000, 50000),
                    'status' => $i < 2 ? 'done' : 'active',
                ]);

                // Materials
                $materials = ['اسمنت', 'جبس', 'مواسير', 'سلك سويدي', 'مفاتيح كهرباء', 'رمل', 'زلط', 'معجون'];
                for ($m = 0; $m < 5; $m++) {
                    $itemName = $materials[array_rand($materials)];
                    $mat = \App\Models\Material::create([
                        'project_id' => $proj->id,
                        'band_id' => $b->id,
                        'item' => $itemName,
                        'supplier_id' => $supplier->id,
                        'unit' => 'كمية',
                        'qty' => rand(10, 100),
                        'unit_price' => rand(50, 500),
                        'payment_status' => 'deferred',
                        'date' => now()->subDays(rand(1, 60)),
                    ]);

                    // Random returns
                    if (rand(1, 10) > 7) {
                        \App\Models\MaterialReturn::create([
                            'material_id' => $mat->id,
                            'qty' => rand(1, 5),
                            'date' => now()->subDays(rand(1, 30)),
                        ]);
                    }
                }
            }
            $proj->recalculateCachedTotals();
        }
    }
}
