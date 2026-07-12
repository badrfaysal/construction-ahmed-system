<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectBand;
use App\Models\Supplier;
use App\Models\Material;
use App\Models\BandWorker;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Clients
        $client1 = Client::create([
            'name' => 'محمد عبدالله',
            'phone' => '01012345678',
            'email' => 'mohamed@example.com',
            'notes' => 'عميل شقة التجمع'
        ]);

        $client2 = Client::create([
            'name' => 'أحمد محمود',
            'phone' => '01123456789',
            'email' => 'ahmed@example.com',
            'notes' => 'عميل فيلا الشروق'
        ]);

        // 2. Create Suppliers
        $supplier1 = Supplier::create(['name' => 'المتحدة لمواد البناء', 'phone' => '01234567890', 'address' => 'التجمع الخامس', 'activity' => 'أسمنت وحديد']);
        $supplier2 = Supplier::create(['name' => 'الرواد للكهرباء', 'phone' => '01098765432', 'address' => 'مدينة نصر', 'activity' => 'أدوات كهربائية']);
        $supplier3 = Supplier::create(['name' => 'مكاوي للأسمنت', 'phone' => '01511111111', 'address' => 'العبور', 'activity' => 'أسمنت']);
        $supplier4 = Supplier::create(['name' => 'الأهرام للدهانات', 'phone' => '01122222222', 'address' => 'المعادي', 'activity' => 'دهانات']);

        // 3. Create Projects
        $project1 = Project::create([
            'client_id' => $client1->id,
            'name' => 'تشطيب شقة التجمع',
            'address' => 'التجمع الخامس، حي النرجس',
            'area' => 120,
            'deliver_date' => Carbon::now()->addMonths(3),
            'status' => 'active',
            'notes' => 'تشطيب كامل سوبر لوكس'
        ]);

        $project2 = Project::create([
            'client_id' => $client2->id,
            'name' => 'تشطيب فيلا الشروق',
            'address' => 'مدينة الشروق، المنطقة الأولى',
            'area' => 350,
            'deliver_date' => Carbon::now()->addMonths(6),
            'status' => 'active',
            'notes' => 'تشطيب فيلا 3 أدوار'
        ]);

        // 4. Create Bands for Project 1
        $p1_band1 = ProjectBand::create(['project_id' => $project1->id, 'name' => 'تأسيس الكهرباء', 'status' => 'active', 'sort_order' => 1]);
        $p1_band2 = ProjectBand::create(['project_id' => $project1->id, 'name' => 'تكسير ومباني', 'status' => 'active', 'sort_order' => 2]);
        $p1_band3 = ProjectBand::create(['project_id' => $project1->id, 'name' => 'محارة', 'status' => 'pending', 'sort_order' => 3]);
        $p1_band4 = ProjectBand::create(['project_id' => $project1->id, 'name' => 'دهانات', 'status' => 'pending', 'sort_order' => 4]);

        // 5. Create Bands for Project 2
        $p2_band1 = ProjectBand::create(['project_id' => $project2->id, 'name' => 'تكسير ومباني', 'status' => 'done', 'sort_order' => 1]);
        $p2_band2 = ProjectBand::create(['project_id' => $project2->id, 'name' => 'تأسيس السباكة', 'status' => 'active', 'sort_order' => 2]);
        $p2_band3 = ProjectBand::create(['project_id' => $project2->id, 'name' => 'تأسيس الكهرباء', 'status' => 'active', 'sort_order' => 3]);
        $p2_band4 = ProjectBand::create(['project_id' => $project2->id, 'name' => 'دهانات', 'status' => 'pending', 'sort_order' => 4]);

        // 6. Create Materials
        // Buying the same item (أسمنت) from two different suppliers at different prices
        Material::create([
            'project_id' => $project1->id, 'band_id' => $p1_band2->id, 'supplier_id' => $supplier1->id,
            'item' => 'أسمنت', 'qty' => 10, 'unit' => 'طن', 'unit_price' => 2500, 'sell_price' => 2800,
            'supervision_pct' => 15, 'date' => Carbon::now()->subDays(10), 'payment_status' => 'deferred', 'paid_amount' => 0,
        ]);

        Material::create([
            'project_id' => $project2->id, 'band_id' => $p2_band1->id, 'supplier_id' => $supplier3->id,
            'item' => 'أسمنت', 'qty' => 5, 'unit' => 'طن', 'unit_price' => 2600, 'sell_price' => 2900,
            'supervision_pct' => 15, 'date' => Carbon::now()->subDays(25), 'payment_status' => 'deferred', 'paid_amount' => 0,
        ]);

        // More materials
        Material::create([
            'project_id' => $project1->id, 'band_id' => $p1_band1->id, 'supplier_id' => $supplier2->id,
            'item' => 'أسلاك سويدي 2 ملم', 'qty' => 10, 'unit' => 'لفة', 'unit_price' => 1500, 'sell_price' => 1700,
            'supervision_pct' => 10, 'date' => Carbon::now()->subDays(9), 'payment_status' => 'deferred', 'paid_amount' => 0,
        ]);

        Material::create([
            'project_id' => $project1->id, 'band_id' => $p1_band1->id, 'supplier_id' => $supplier2->id,
            'item' => 'خراطيم كهرباء', 'qty' => 20, 'unit' => 'لفة', 'unit_price' => 200, 'sell_price' => 250,
            'supervision_pct' => 10, 'date' => Carbon::now()->subDays(8), 'payment_status' => 'deferred', 'paid_amount' => 0,
        ]);

        Material::create([
            'project_id' => $project2->id, 'band_id' => $p2_band3->id, 'supplier_id' => $supplier2->id,
            'item' => 'علب ماجيك', 'qty' => 150, 'unit' => 'قطعة', 'unit_price' => 10, 'sell_price' => 12,
            'supervision_pct' => 10, 'date' => Carbon::now()->subDays(2), 'payment_status' => 'deferred', 'paid_amount' => 0,
        ]);

        Material::create([
            'project_id' => $project1->id, 'band_id' => $p1_band4->id, 'supplier_id' => $supplier4->id,
            'item' => 'بستلة معجون', 'qty' => 15, 'unit' => 'بستلة', 'unit_price' => 300, 'sell_price' => 350,
            'supervision_pct' => 10, 'date' => Carbon::now()->subDays(1), 'payment_status' => 'deferred', 'paid_amount' => 0,
        ]);

        Material::create([
            'project_id' => $project2->id, 'band_id' => $p2_band4->id, 'supplier_id' => $supplier4->id,
            'item' => 'دهانات بلاستيك', 'qty' => 8, 'unit' => 'بستلة', 'unit_price' => 450, 'sell_price' => 500,
            'supervision_pct' => 10, 'date' => Carbon::now()->subDays(1), 'payment_status' => 'deferred', 'paid_amount' => 0,
        ]);

        // 7. Create Band Workers (Craftsmen)
        BandWorker::create([
            'project_band_id' => $p1_band1->id, 'name' => 'محمود الكهربائي', 'phone' => '01011112222', 'specialty' => 'كهربائي',
            'contract_type' => 'lump_sum', 'amount' => 5000, 'sell_amount' => 6000, 'supervision_pct' => 10, 'start_date' => Carbon::now()->subDays(8),
        ]);

        BandWorker::create([
            'project_band_id' => $p2_band1->id, 'name' => 'سيد البنا', 'phone' => '01222223333', 'specialty' => 'بنا',
            'contract_type' => 'per_meter', 'contract_qty' => 100, 'contract_unit_rate' => 50, 'sell_rate' => 60,
            'amount' => 5000, 'sell_amount' => 6000, 'supervision_pct' => 10, 'start_date' => Carbon::now()->subDays(25),
        ]);

        BandWorker::create([
            'project_band_id' => $p1_band3->id, 'name' => 'خالد المبيض', 'phone' => '01133334444', 'specialty' => 'مبيض محارة',
            'contract_type' => 'per_meter', 'contract_qty' => 300, 'contract_unit_rate' => 40, 'sell_rate' => 50,
            'amount' => 12000, 'sell_amount' => 15000, 'supervision_pct' => 10, 'start_date' => Carbon::now()->subDays(2),
        ]);
        
        BandWorker::create([
            'project_band_id' => $p2_band2->id, 'name' => 'عادل السباك', 'phone' => '01055556666', 'specialty' => 'سباك',
            'contract_type' => 'lump_sum', 'amount' => 7000, 'sell_amount' => 8500, 'supervision_pct' => 10, 'start_date' => Carbon::now()->subDays(5),
        ]);
    }
}
