<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('logs', 'account');

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('purchase_date')->orderBy('id');
        } elseif ($sort === 'value_desc') {
            $query->orderByDesc('current_value');
        } elseif ($sort === 'value_asc') {
            $query->orderBy('current_value');
        } else {
            $query->orderByDesc('purchase_date')->orderByDesc('id');
        }

        $assets = $query->get();
        $activeAssets = $assets->where('status', 'active');
        $soldAssets = $assets->where('status', 'sold');
        $depreciatedAssets = $assets->where('status', 'fully_depreciated');
        
        $accounts = Account::selectable();

        return view('assets.index', compact('activeAssets', 'soldAssets', 'depreciatedAssets', 'accounts', 'sort'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'purchase_cost' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
            'account_id' => 'nullable|exists:sy2_accounts,id',
        ]);

        $data['current_value'] = $data['purchase_cost'];
        $data['status'] = 'active';

        DB::transaction(function () use ($data) {
            $asset = Asset::create($data);

            if ($asset->account_id) {
                // تسجيل حركة خصم من الحساب
                Transaction::create([
                    'project_id'  => null,
                    'band_id'     => null,
                    'account_id'  => $asset->account_id,
                    'direction'   => 'out',
                    'type'        => 'شراء أصل',
                    'party'       => 'الأصول الثابتة',
                    'amount'      => $asset->purchase_cost,
                    'date'        => $asset->purchase_date,
                    'description' => "شراء أصل: " . $asset->name,
                    'ref_type'    => 'asset',
                    'ref_id'      => $asset->id,
                ]);
            }

            $asset->logs()->create([
                'action' => 'purchase',
                'amount' => $asset->purchase_cost,
                'notes' => 'تم شراء الأصل بقيمة ' . number_format($asset->purchase_cost, 2) . ' ج.م' . ($asset->account_id ? ' (تم السحب من حساب)' : ' (بدون سحب من حساب)'),
            ]);

            if (!$asset->account_id) {
                AuditLog::create([
                    'action'       => 'created',
                    'direction'    => 'in',
                    'type'         => 'شراء أصل (دفتري)',
                    'party'        => 'الأصول الثابتة',
                    'amount'       => $asset->purchase_cost,
                    'ref_type'     => 'asset',
                    'ref_id'       => $asset->id,
                    'description'  => "شراء أصل (دفتري): " . $asset->name,
                    'date'         => $asset->purchase_date,
                    'performed_by' => auth()->id(),
                    'happened_at'  => now(),
                ]);
            }
        });

        return back()->with('success', 'تم تسجيل الأصل بنجاح.');
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'current_value' => 'required|numeric|min:0',
            'purchase_cost' => 'required|numeric|min:0',
        ]);

        $oldValue = $asset->current_value;
        $oldCost = $asset->purchase_cost;
        $asset->update($data);

        $asset->logs()->create([
            'action' => 'edit',
            'amount' => $asset->current_value,
            'notes' => "تم تعديل الأصل. السعر القديم: {$oldCost}، السعر الجديد: {$asset->purchase_cost}. القيمة القديمة: {$oldValue}، القيمة الجديدة: {$asset->current_value}.",
        ]);

        AuditLog::create([
            'action'       => 'updated',
            'direction'    => '',
            'type'         => 'تعديل أصل',
            'party'        => 'الأصول الثابتة',
            'amount'       => $asset->current_value,
            'ref_type'     => 'asset',
            'ref_id'       => $asset->id,
            'description'  => "تعديل أصل: " . $asset->name . " (الشراء من $oldCost إلى {$asset->purchase_cost}، القيمة من $oldValue إلى {$asset->current_value})",
            'date'         => now()->toDateString(),
            'performed_by' => auth()->id(),
            'happened_at'  => now(),
        ]);

        return back()->with('success', 'تم تعديل الأصل بنجاح.');
    }

    public function depreciate(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $asset->current_value,
        ]);

        DB::transaction(function () use ($data, $asset) {
            $asset->current_value -= $data['amount'];
            
            if ($asset->current_value <= 0) {
                $asset->current_value = 0;
                $asset->status = 'fully_depreciated';
            }
            $asset->save();

            $asset->logs()->create([
                'action' => 'depreciation',
                'amount' => $data['amount'],
                'notes' => 'تم عمل إهلاك بقيمة ' . number_format($data['amount'], 2) . ' ج.م',
            ]);

            AuditLog::create([
                'action'       => 'updated',
                'direction'    => 'out',
                'type'         => 'إهلاك أصل',
                'party'        => 'الأصول الثابتة',
                'amount'       => $data['amount'],
                'ref_type'     => 'asset',
                'ref_id'       => $asset->id,
                'description'  => "إهلاك أصل: " . $asset->name . " بقيمة " . $data['amount'],
                'date'         => now()->toDateString(),
                'performed_by' => auth()->id(),
                'happened_at'  => now(),
            ]);
        });

        return back()->with('success', 'تم إهلاك الأصل بنجاح.');
    }

    public function sell(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'sell_amount' => 'required|numeric|min:0',
            'account_id'  => 'required|exists:sy2_accounts,id',
            'date'        => 'required|date',
        ]);

        DB::transaction(function () use ($data, $asset) {
            $asset->update(['status' => 'sold']);

            Transaction::create([
                'project_id'  => null,
                'band_id'     => null,
                'account_id'  => $data['account_id'],
                'direction'   => 'in',
                'type'        => 'بيع أصل',
                'party'       => 'الأصول الثابتة',
                'amount'      => $data['sell_amount'],
                'date'        => $data['date'],
                'description' => "بيع أصل: " . $asset->name,
                'ref_type'    => 'asset',
                'ref_id'      => $asset->id,
            ]);

            $asset->logs()->create([
                'action' => 'sale',
                'amount' => $data['sell_amount'],
                'notes'  => 'تم بيع/تسييل الأصل بقيمة ' . number_format($data['sell_amount'], 2) . ' ج.م (تم الإيداع بحساب)',
            ]);
        });

        return back()->with('success', 'تم بيع/تسييل الأصل بنجاح.');
    }

    public function destroy(Request $request, Asset $asset)
    {
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors(['admin_password' => 'كلمة المرور غير صحيحة، لا يمكن إتمام الحذف.'])->withInput();
        }

        // حذف الحركات المرتبطة بالأصل من Transaction (إذا كان تم شراؤه أو بيعه بحساب)
        Transaction::where('ref_type', 'asset')->where('ref_id', $asset->id)->delete();
        
        // تسجيل الإلغاء في الرادار
        AuditLog::create([
            'action'       => 'deleted',
            'direction'    => '',
            'type'         => 'حذف أصل نهائي',
            'party'        => 'الأصول الثابتة',
            'amount'       => $asset->current_value,
            'ref_type'     => 'asset',
            'ref_id'       => $asset->id,
            'description'  => "حذف أصل: " . $asset->name,
            'date'         => now()->toDateString(),
            'performed_by' => auth()->id(),
            'happened_at'  => now(),
        ]);

        $asset->delete(); // this will cascade delete logs

        return back()->with('success', 'تم حذف الأصل نهائياً.');
    }
}
