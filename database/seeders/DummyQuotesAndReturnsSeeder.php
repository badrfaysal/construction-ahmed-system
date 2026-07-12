<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quote;
use App\Models\QuoteBand;
use App\Models\QuoteBandItem;
use App\Models\QuoteBandWorker;
use App\Models\Material;
use App\Models\MaterialReturn;
use Carbon\Carbon;

class DummyQuotesAndReturnsSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Quotes
        $quote1 = Quote::create([
            'ref' => 'Q-1001',
            'client_name' => 'ياسر محمد',
            'phone' => '01011110000',
            'address' => 'زايد، الحي الثامن',
            'area' => 200,
            'date' => Carbon::now()->subDays(5),
            'status' => 'draft',
            'note' => 'عرض سعر لتشطيب فيلا هاي سوبر لوكس'
        ]);

        $quote2 = Quote::create([
            'ref' => 'Q-1002',
            'client_name' => 'عمر خالد',
            'phone' => '01222220000',
            'address' => 'التجمع الخامس، البنفسج',
            'area' => 150,
            'date' => Carbon::now()->subDays(2),
            'status' => 'sent',
            'note' => 'عرض سعر تجديد شقة'
        ]);

        // 2. Add Bands to Quote 1
        $q1_band1 = QuoteBand::create(['quote_id' => $quote1->id, 'name' => 'تأسيس السباكة', 'price' => 0, 'sort_order' => 1]);
        $q1_band2 = QuoteBand::create(['quote_id' => $quote1->id, 'name' => 'تأسيس الكهرباء', 'price' => 0, 'sort_order' => 2]);
        $q1_band3 = QuoteBand::create(['quote_id' => $quote1->id, 'name' => 'المحارة', 'price' => 0, 'sort_order' => 3]);
        $q1_band4 = QuoteBand::create(['quote_id' => $quote1->id, 'name' => 'الدهانات', 'price' => 0, 'sort_order' => 4]);

        // Items for Q1 Band 1
        QuoteBandItem::create(['quote_band_id' => $q1_band1->id, 'name' => 'مواسير وتجهيزات', 'qty' => 50, 'unit_price' => 200, 'supervision_pct' => 10, 'sort_order' => 1]);
        QuoteBandWorker::create(['quote_band_id' => $q1_band1->id, 'name' => 'مصنعية السباك', 'contract_type' => 'lump_sum', 'sell_amount' => 10000, 'supervision_pct' => 10, 'sort_order' => 1]);

        // Items for Q1 Band 2
        QuoteBandItem::create(['quote_band_id' => $q1_band2->id, 'name' => 'أسلاك كهرباء وخراطيم', 'qty' => 40, 'unit_price' => 300, 'supervision_pct' => 10, 'sort_order' => 1]);
        QuoteBandWorker::create(['quote_band_id' => $q1_band2->id, 'name' => 'مصنعية الكهربائي', 'contract_type' => 'lump_sum', 'sell_amount' => 15000, 'supervision_pct' => 10, 'sort_order' => 1]);

        // Items for Q1 Band 3
        QuoteBandItem::create(['quote_band_id' => $q1_band3->id, 'name' => 'رمل وأسمنت', 'qty' => 100, 'unit_price' => 150, 'supervision_pct' => 10, 'sort_order' => 1]);
        QuoteBandWorker::create(['quote_band_id' => $q1_band3->id, 'name' => 'مصنعية المبيض', 'contract_type' => 'per_meter', 'contract_qty' => 500, 'sell_rate' => 60, 'sell_amount' => 30000, 'supervision_pct' => 10, 'sort_order' => 1]);
        
        // Update price based on items
        $q1_band1->update(['price' => $q1_band1->itemsTotal()]);
        $q1_band2->update(['price' => $q1_band2->itemsTotal()]);
        $q1_band3->update(['price' => $q1_band3->itemsTotal()]);
        $q1_band4->update(['price' => 15000]); // Hardcoded for Band 4

        // 3. Add Bands to Quote 2
        $q2_band1 = QuoteBand::create(['quote_id' => $quote2->id, 'name' => 'سيراميك وأرضيات', 'price' => 0, 'sort_order' => 1]);
        $q2_band2 = QuoteBand::create(['quote_id' => $quote2->id, 'name' => 'دهانات', 'price' => 0, 'sort_order' => 2]);

        QuoteBandItem::create(['quote_band_id' => $q2_band1->id, 'name' => 'سيراميك فرز أول', 'qty' => 120, 'unit_price' => 300, 'supervision_pct' => 15, 'sort_order' => 1]);
        QuoteBandWorker::create(['quote_band_id' => $q2_band1->id, 'name' => 'مصنعية المبلط', 'contract_type' => 'per_meter', 'contract_qty' => 120, 'sell_rate' => 100, 'sell_amount' => 12000, 'supervision_pct' => 15, 'sort_order' => 1]);
        
        QuoteBandItem::create(['quote_band_id' => $q2_band2->id, 'name' => 'معجون وبلاستيك', 'qty' => 20, 'unit_price' => 500, 'supervision_pct' => 10, 'sort_order' => 1]);
        QuoteBandWorker::create(['quote_band_id' => $q2_band2->id, 'name' => 'مصنعية النقاش', 'contract_type' => 'lump_sum', 'sell_amount' => 25000, 'supervision_pct' => 10, 'sort_order' => 1]);
        
        $q2_band1->update(['price' => $q2_band1->itemsTotal()]);
        $q2_band2->update(['price' => $q2_band2->itemsTotal()]);

        // 4. Add Material Returns
        // We will find a material and return a part of it
        $material1 = Material::where('item', 'أسمنت')->first();
        if ($material1) {
            MaterialReturn::create([
                'material_id' => $material1->id,
                'qty' => 1,
                'return_price' => $material1->unit_price, // returned with same price (no loss)
                'date' => Carbon::now()->subDays(1),
                'notes' => 'أسمنت زائد عن حاجة الموقع'
            ]);
        }

        $material2 = Material::where('item', 'أسلاك سويدي 2 ملم')->first();
        if ($material2) {
            MaterialReturn::create([
                'material_id' => $material2->id,
                'qty' => 2,
                'return_price' => $material2->unit_price - 100, // returned with a lower price (loss)
                'date' => Carbon::now()->subDays(2),
                'notes' => 'تم استرجاعه بخسارة بسبب فتحه'
            ]);
        }
    }
}
