<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemActivityLog extends Model
{
    protected $table = 'system_activity_logs';

    protected $fillable = [
        'batch_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function subject()
    {
        return $this->morphTo('model');
    }

    public function modelTypeAr(): string
    {
        $basename = class_basename($this->model_type);
        return match ($basename) {
            'Project' => 'مشروع',
            'ProjectBand' => 'بند مشروع',
            'ProjectDiscount' => 'خصم مشروع',
            'Quote' => 'عرض سعر',
            'QuoteBand' => 'بند عرض سعر',
            'QuoteBandItem' => 'بند فرعي',
            'QuoteBandWorker' => 'أجر عامل (مقايسة)',
            'Material' => 'خامة',
            'MaterialInvoice' => 'فاتورة خامات',
            'MaterialReturn' => 'مرتجع خامات',
            'Transaction', 'FinancialTransaction' => 'حركة مالية',
            'Expense' => 'مصروف',
            'ManualDebt' => 'دين يدوي',
            'Installment' => 'قسط',
            'InstallmentContract' => 'عقد تقسيط',
            'InstallmentPayment' => 'دفع قسط',
            'User' => 'مستخدم',
            'Client' => 'عميل',
            'Supplier' => 'مورد',
            'Marketer' => 'مسوق',
            'BandWorker' => 'يومية عامل',
            'SupplierDebt' => 'دين مورد',
            'WorkerPayment' => 'دفعة صنايعي',
            'Settings' => 'إعدادات',
            'Account' => 'حساب مالي',
            'Warranty' => 'ضمان',
            'WarrantyComplaint' => 'شكوى ضمان',
            default => 'عنصر (' . $basename . ')',
        };
    }

    public function actionAr(): string
    {
        $type = $this->modelTypeAr();
        if ($this->action === 'created') {
            return "إضافة " . $type;
        } elseif ($this->action === 'updated') {
            return "تعديل " . $type;
        } elseif ($this->action === 'deleted') {
            return "حذف " . $type;
        }
        return $this->action;
    }
}
