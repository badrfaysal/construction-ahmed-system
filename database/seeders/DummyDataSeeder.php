<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Marketer;
use App\Models\Project;
use App\Models\ProjectBand;
use App\Models\BandWorker;
use App\Models\MaterialInvoice;
use App\Models\Material;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DummyDataSeeder extends Seeder
{
    private const TABLES_TO_TRUNCATE = [
        'sy2_audit_logs',
        'sy2_transactions',
        'sy2_installment_payments',
        'sy2_installment_contracts',
        'sy2_worker_payments',
        'sy2_band_workers',
        'sy2_material_returns',
        'sy2_materials',
        'sy2_material_invoices',
        'sy2_installments',
        'sy2_supplier_debts',
        'sy2_warranty_complaints',
        'sy2_warranties',
        'sy2_quote_band_workers',
        'sy2_quote_band_items',
        'sy2_quote_bands',
        'sy2_quotes',
        'sy2_project_discounts',
        'sy2_project_bands',
        'sy2_projects',
        'sy2_marketers',
        'sy2_clients',
        'sy2_suppliers',
    ];

    public function run(): void
    {
        // 1. Truncate all data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (self::TABLES_TO_TRUNCATE as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Temporarily give wallet a huge balance to bypass validation during seeding
        DB::table((new Account)->getTable())
            ->where('id', Account::WALLET_ID)
            ->update(['balance' => 1000000]);

        $now = Carbon::now();

        // 2. Seed Clients
        $clients = [
            Client::create(['name' => 'م. أحمد المصري', 'phone' => '01012345678', 'address' => 'التجمع الخامس - البنفسج']),
            Client::create(['name' => 'د. محمود سليم', 'phone' => '01112345678', 'address' => 'المعادي - شارع 9']),
            Client::create(['name' => 'أ. طارق حسن', 'phone' => '01212345678', 'address' => 'الشيخ زايد - بيفرلي هيلز']),
            Client::create(['name' => 'مؤسسة الأفق للتطوير', 'phone' => '01512345678', 'address' => 'أكتوبر - الحي المتميز']),
            Client::create(['name' => 'أ. هدى كمال', 'phone' => '01000000001', 'address' => 'مدينتي - B3']),
        ];

        // 3. Seed Suppliers
        $suppliers = [
            Supplier::create(['name' => 'شركة النور للأسمنت والحديد', 'phone' => '01099998888', 'activity' => 'مواد بناء']),
            Supplier::create(['name' => 'مغلق الصفا للأخشاب', 'phone' => '01199998888', 'activity' => 'أخشاب']),
            Supplier::create(['name' => 'المحبة للدهانات', 'phone' => '01299998888', 'activity' => 'دهانات']),
            Supplier::create(['name' => 'سنتر السباكة الحديثة', 'phone' => '01599998888', 'activity' => 'أدوات صحية']),
            Supplier::create(['name' => 'الإيمان للكهرباء', 'phone' => '01000000002', 'activity' => 'أدوات كهربائية']),
        ];

        // 4. Seed Marketers
        $marketers = [
            Marketer::create(['name' => 'كريم عبد العزيز', 'phone' => '01011112222']),
            Marketer::create(['name' => 'عمر الشريف', 'phone' => '01111112222']),
            Marketer::create(['name' => 'مصطفى كامل', 'phone' => '01211112222']),
        ];

        // 5. Seed Projects
        $projects = [
            // Project 1: Active
            Project::create([
                'client_id' => $clients[0]->id,
                'name' => 'تشطيب فيلا التجمع',
                'address' => 'فيلا 15 البنفسج',
                'area' => 350,
                'start_date' => $now->copy()->subMonths(2)->format('Y-m-d'),
                'status' => 'active',
                'default_supervision_pct' => 15,
                'discount' => 5000,
            ]),
            // Project 2: Done
            Project::create([
                'client_id' => $clients[1]->id,
                'name' => 'تجديد شقة المعادي',
                'address' => 'شقة 12 عمارة 5 شارع 9',
                'area' => 180,
                'start_date' => $now->copy()->subMonths(5)->format('Y-m-d'),
                'status' => 'done',
                'default_supervision_pct' => 20,
            ]),
            // Project 3: Active (New)
            Project::create([
                'client_id' => $clients[2]->id,
                'name' => 'تشطيب دوبلكس الشيخ زايد',
                'address' => 'دوبلكس 3 بيفرلي هيلز',
                'area' => 220,
                'start_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'status' => 'active',
                'default_supervision_pct' => 15,
            ]),
            // Project 4: Active (Installments)
            Project::create([
                'client_id' => $clients[3]->id,
                'name' => 'عمارة أكتوبر السكنية',
                'address' => 'الحي المتميز',
                'area' => 800,
                'start_date' => $now->copy()->subMonth()->format('Y-m-d'),
                'status' => 'active',
                'default_supervision_pct' => 10,
            ]),
        ];

        // 6. Seed Project Bands & Workers & Materials
        // Project 1 (فيلا التجمع)
        $p1_b1 = ProjectBand::create([
            'project_id' => $projects[0]->id,
            'name' => 'تأسيس سباكة',
            'client_price' => 35000,
            'status' => 'done',
            'contract_type' => 'lump_sum',
            'labor_amount' => 8000,
            'labor_date' => $now->copy()->subMonths(2)->format('Y-m-d'),
        ]);
        $p1_w1 = BandWorker::create([
            'project_band_id' => $p1_b1->id,
            'name' => 'الأسطى رجب (سباك)',
            'amount' => 8000,
        ]);
        $p1_w1->payments()->create([
            'project_id' => $projects[0]->id,
            'project_band_id' => $p1_b1->id,
            'amount' => 7500,
            'date' => $now->copy()->subMonths(2)->addDays(5)->format('Y-m-d'),
            'discount' => 500,
            'discount_reason' => 'تأخير في تسليم الشغل',
        ]);
        // Observers will automatically create Transactions for WorkerPayments!

        // Invoice for Project 1 - Plumbing
        $inv1 = MaterialInvoice::create([
            'supplier_id' => $suppliers[3]->id,
            'project_id' => $projects[0]->id,
            'account_id' => Account::WALLET_ID,
            'name' => 'INV-001',
            'total_amount' => 0,
            'paid_amount' => 0,
            'date' => $now->copy()->subMonths(2)->format('Y-m-d'),
            'notes' => 'مواسير ولوازم سباكة',
        ]);
        Material::create([
            'project_id' => $projects[0]->id,
            'band_id' => $p1_b1->id,
            'supplier_id' => $suppliers[3]->id,
            'invoice_id' => $inv1->id,
            'item' => 'مواسير 3/4 بوصة',
            'unit' => 'متر',
            'qty' => 100,
            'unit_price' => 50,
            'sell_price' => 60,
            'date' => $now->copy()->subMonths(2)->format('Y-m-d'),
            'category' => 'material',
        ]);
        Material::create([
            'project_id' => $projects[0]->id,
            'band_id' => $p1_b1->id,
            'supplier_id' => $suppliers[3]->id,
            'invoice_id' => $inv1->id,
            'item' => 'بانيو أيديال',
            'unit' => 'قطعة',
            'qty' => 3,
            'unit_price' => 2500,
            'sell_price' => 3000,
            'date' => $now->copy()->subMonths(2)->format('Y-m-d'),
            'category' => 'material',
        ]);

        // Observer handles invoice debt. We just handle the payment.
        // Pay supplier 10,000 for this invoice
        $inv1->update(['paid_amount' => 10000]);


        // Returns for Project 1
        $matToReturn = Material::where('item', 'مواسير 3/4 بوصة')->first();
        $matToReturn->returns()->create([
            'qty' => 10,
            'date' => $now->copy()->subMonths(1)->format('Y-m-d'),
            'return_price' => 45, // Loss of 5 per unit
            'notes' => 'زيادة عن الحاجة',
        ]);

        // Since we are mocking returns manually here, we will directly update the invoice total_amount
        // in a real scenario MaterialReturnObserver does this to Material and Material updates Invoice.
        $inv1->update(['total_amount' => $inv1->total_amount - 450]);


        // Project 1 - Electrical Band
        $p1_b2 = ProjectBand::create([
            'project_id' => $projects[0]->id,
            'name' => 'تأسيس كهرباء',
            'client_price' => 45000,
            'status' => 'active',
            'contract_type' => 'lump_sum',
            'labor_amount' => 12000,
            'labor_date' => $now->copy()->subMonths(1)->format('Y-m-d'),
        ]);
        $p1_w2 = BandWorker::create([
            'project_band_id' => $p1_b2->id,
            'name' => 'الأسطى محمود (كهربائي)',
            'amount' => 12000,
        ]);
        // Invoice for electrical
        $inv2 = MaterialInvoice::create([
            'supplier_id' => $suppliers[4]->id,
            'project_id' => $projects[0]->id,
            'account_id' => Account::WALLET_ID,
            'name' => 'ELEC-099',
            'total_amount' => 0,
            'paid_amount' => 0,
            'date' => $now->copy()->subMonths(1)->format('Y-m-d'),
            'notes' => 'أسلاك ولوحات',
        ]);
        Material::create([
            'project_id' => $projects[0]->id,
            'band_id' => $p1_b2->id,
            'supplier_id' => $suppliers[4]->id,
            'invoice_id' => $inv2->id,
            'item' => 'لفة سلك 2 ملي',
            'unit' => 'لفة',
            'qty' => 20,
            'unit_price' => 800,
            'sell_price' => 950,
            'date' => $now->copy()->subMonths(1)->format('Y-m-d'),
            'category' => 'material',
        ]);
        // Observer handles invoice debt.


        // Project 1 Financials (Marketer & Client Payments)
        Transaction::create([
            'account_id' => Account::WALLET_ID,
            'direction' => 'out',
            'type' => 'marketer_commission',
            'party' => $marketers[0]->name,
            'amount' => 2000,
            'description' => 'عمولة تسويق: ' . $marketers[0]->name,
            'ref_type' => 'marketer_commission',
            'ref_id' => $marketers[0]->id,
            'project_id' => $projects[0]->id,
            'date' => $now->copy()->subMonths(2)->format('Y-m-d'),
        ]);
        
        $p1_pay1 = Transaction::create([
            'account_id' => Account::WALLET_ID,
            'direction' => 'in',
            'type' => 'client_payment',
            'party' => $projects[0]->client->name,
            'amount' => 50000,
            'description' => 'دفعة مقدمة من العميل',
            'ref_type' => 'client_payment',
            'project_id' => $projects[0]->id,
            'date' => $now->copy()->subMonths(2)->format('Y-m-d'),
        ]);


        // Project 2 (شقة المعادي - Done)
        $p2_b1 = ProjectBand::create([
            'project_id' => $projects[1]->id,
            'name' => 'تشطيب متكامل',
            'client_price' => 150000,
            'status' => 'done',
            'contract_type' => 'lump_sum',
            'labor_amount' => 40000,
            'labor_date' => $now->copy()->subMonths(5)->format('Y-m-d'),
        ]);
        $p2_w1 = BandWorker::create([
            'project_band_id' => $p2_b1->id,
            'name' => 'مقاول التشطيبات (علي)',
            'amount' => 40000,
        ]);
        $p2_w1->payments()->create([
            'project_id' => $projects[1]->id,
            'project_band_id' => $p2_b1->id,
            'amount' => 40000,
            'date' => $now->copy()->subMonths(3)->format('Y-m-d'),
        ]);
        // Observers will automatically create Transactions for WorkerPayments

        $inv3 = MaterialInvoice::create([
            'supplier_id' => $suppliers[0]->id,
            'project_id' => $projects[1]->id,
            'account_id' => Account::WALLET_ID,
            'name' => 'C-555',
            'total_amount' => 0,
            'paid_amount' => 0,
            'date' => $now->copy()->subMonths(5)->format('Y-m-d'),
        ]);
        Material::create([
            'project_id' => $projects[1]->id,
            'band_id' => $p2_b1->id,
            'supplier_id' => $suppliers[0]->id,
            'invoice_id' => $inv3->id,
            'item' => 'أسمنت',
            'unit' => 'طن',
            'qty' => 10,
            'unit_price' => 2000,
            'sell_price' => 2400,
            'date' => $now->copy()->subMonths(5)->format('Y-m-d'),
            'category' => 'material',
        ]);
        // Observer handles invoice debt.
        
        $p2_pay1 = Transaction::create([
            'account_id' => Account::WALLET_ID,
            'direction' => 'in',
            'type' => 'client_payment',
            'party' => $projects[1]->client->name,
            'amount' => 150000, // Fully paid
            'description' => 'سداد كامل',
            'ref_type' => 'client_payment',
            'project_id' => $projects[1]->id,
            'date' => $now->copy()->subMonths(3)->format('Y-m-d'),
        ]);


        // Project 4 (Installments)
        Installment::create([
            'project_id' => $projects[3]->id,
            'label' => 'الدفعة المقدمة (30%)',
            'due_date' => $now->copy()->subMonth()->format('Y-m-d'),
            'amount' => 150000,
            'status' => 'paid',
            'payment_method' => 'تحويل بنكي',
            'paid_date' => $now->copy()->subMonth()->addDays(2)->format('Y-m-d'),
            'sort_order' => 1,
        ]);


        Installment::create([
            'project_id' => $projects[3]->id,
            'label' => 'قسط شهر 1',
            'due_date' => $now->copy()->format('Y-m-d'),
            'amount' => 50000,
            'status' => 'due',
            'sort_order' => 2,
        ]);
        
        Installment::create([
            'project_id' => $projects[3]->id,
            'label' => 'قسط شهر 2',
            'due_date' => $now->copy()->addMonth()->format('Y-m-d'),
            'amount' => 50000,
            'status' => 'upcoming',
            'sort_order' => 3,
        ]);
        
        // Cache updates for projects manually
        foreach ($projects as $project) {
            $project->update([
                'cached_spent' => $project->totalSpent(),
                'cached_collected' => $project->computeTotalCollected(),
            ]);
        }
        
        // Update wallet balance correctly based on transactions
        $balance = Transaction::where('account_id', Account::WALLET_ID)->sum(DB::raw('CASE WHEN type = "in" THEN amount ELSE -amount END'));
        DB::table((new Account)->getTable())
            ->where('id', Account::WALLET_ID)
            ->update(['balance' => $balance]);
    }
}
