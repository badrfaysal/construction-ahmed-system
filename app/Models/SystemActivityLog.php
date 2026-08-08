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
            'ProjectBandWorker', 'BandWorker' => 'عامل ببند',
            'ProjectBandMaterial' => 'خامة ببند',
            'SupplierDebt' => 'دين مورد',
            'WorkerPayment' => 'يومية/دفعة عامل',
            'Worker' => 'فني/عامل',
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

    public function getTelegramFields(): array
    {
        $model = $this->subject;
        $basename = class_basename($this->model_type);
        $fields = [];

        if (!$model) {
            $name = $this->new_values['name'] ?? ($this->old_values['name'] ?? '');
            if ($name) $fields['الاسم'] = $name;
            return $fields;
        }

        if ($basename === 'ProjectBand') {
            $fields['النوع'] = $model->parent_id ? 'بند فرعي' : 'بند رئيسي';
            $fields['البند'] = $model->name;
            $fields['المشروع'] = $model->project->name ?? 'غير محدد';
        } elseif ($basename === 'ProjectBandWorker' || $basename === 'BandWorker') {
            $fields['الفني'] = $model->name ?? 'غير محدد';
            $fields['النظام'] = $model ? $model->contractTypeAr() : 'غير محدد';
            $price = $model->amount ?? ($this->new_values['amount'] ?? 0);
            $fields['الإجمالي'] = number_format((float)$price, 2) . " ج.م";
        } elseif ($basename === 'ProjectBandMaterial') {
            $fields['الخامة'] = $model->material->name ?? 'غير محدد';
            $fields['الكمية'] = $model->quantity ?? 0;
        } elseif ($basename === 'WorkerPayment') {
            $fields['الفني'] = $model->worker->name ?? 'غير محدد';
            $fields['المبلغ'] = number_format((float)($model->amount ?? 0), 2) . " ج.م";
        } elseif ($basename === 'Transaction' || $basename === 'FinancialTransaction') {
            $amount = $model->amount ?? ($this->new_values['amount'] ?? 0);
            $fields['المبلغ'] = number_format((float)$amount, 2) . " ج.م";
            if ($model->type) $fields['النوع'] = $model->type;
            if ($model->party) $fields['الطرف'] = $model->party;
            if ($model->project) $fields['المشروع'] = $model->project->name;
        } elseif ($basename === 'Project') {
            $fields['المشروع'] = $model->name;
            if ($model->client_id && $model->client) $fields['العميل'] = $model->client->name;
        } else {
            $name = $model->name ?? $model->title ?? '';
            if ($name) $fields['الاسم'] = $name;
        }

        // Generic fallback to ensure name exists if possible
        if (!isset($fields['الاسم']) && !isset($fields['البند']) && !isset($fields['المشروع']) && !isset($fields['الخامة'])) {
            $name = $model?->name ?? $model?->title ?? $this->new_values['name'] ?? $this->new_values['title'] ?? $this->old_values['name'] ?? $this->old_values['title'] ?? null;
            if ($name) $fields['الاسم'] = $name;
        }

        // Generic extraction for amount / price / total
        if (!isset($fields['المبلغ']) && !isset($fields['الإجمالي'])) {
            $amountVal = $model?->amount ?? $this->new_values['amount'] ?? $model?->total ?? $this->new_values['total'] ?? $model?->price ?? $this->new_values['price'] ?? null;
            if ($amountVal !== null) {
                $fields['المبلغ/القيمة'] = number_format((float)$amountVal, 2) . " ج.م";
            }
        }

        // Generic extraction for balance
        if (!isset($fields['الرصيد'])) {
            $balanceVal = $model?->balance ?? $this->new_values['balance'] ?? null;
            if ($balanceVal !== null) {
                $fields['الرصيد'] = number_format((float)$balanceVal, 2) . " ج.م";
            }
        }

        return $fields;
    }

    public function getTelegramDescription(): string
    {
        $model = $this->subject;
        $actionName = $this->actionAr();
        $basename = class_basename($this->model_type);

        if (!$model) {
            $name = $this->new_values['name'] ?? ($this->old_values['name'] ?? '');
            return "{$actionName}" . ($name ? " ({$name})" : "");
        }

        if ($basename === 'ProjectBandWorker' || $basename === 'BandWorker') {
            $workerName = $model->name ?? 'فني';
            $calcType = $model ? $model->contractTypeAr() : 'غير محدد';
            $price = $model->amount ?? ($this->new_values['amount'] ?? 0);
            return "تعاقد مع فني ({$workerName}) بنظام ({$calcType}) بقيمة " . number_format((float)$price, 2) . " ج.م";
        }

        if ($basename === 'ProjectBandMaterial') {
            $materialName = $model->material->name ?? 'خامة';
            return "إضافة خامة ({$materialName}) بكمية " . ($model->quantity ?? 0);
        }

        // Fallback: join the fields
        $fields = $this->getTelegramFields();
        $desc = "{$actionName}";
        if (!empty($fields)) {
            $desc .= " (" . implode(' - ', array_values($fields)) . ")";
        }
        return $desc;
    }
}
