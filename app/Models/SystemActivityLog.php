<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemActivityLog extends Model
{
    protected $table = 'system_activity_logs';

    protected $fillable = [
        'batch_id',
        'user_id',
        'ip_address',
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
        
        $source = $this->action === 'deleted' ? $this->old_values : $this->new_values;
        if (!is_array($source)) $source = [];
        if (!is_array($this->old_values)) $this->old_values = [];
        if (!is_array($this->new_values)) $this->new_values = [];

        if (!$model && $this->action !== 'deleted') {
            $name = $this->new_values['name'] ?? ($this->old_values['name'] ?? '');
            if ($name) $fields['الاسم'] = $name;
            return $fields;
        }

        if ($basename === 'ProjectBand') {
            $fields['النوع'] = ($model->parent_id ?? $source['parent_id'] ?? null) ? 'بند فرعي' : 'بند رئيسي';
            $fields['البند'] = $model->name ?? $source['name'] ?? '';
            $fields['المشروع'] = $model->project->name ?? 'غير محدد';
        } elseif ($basename === 'ProjectBandWorker' || $basename === 'BandWorker') {
            $fields['الفني'] = $model->name ?? $source['name'] ?? 'غير محدد';
            $price = $model->amount ?? $source['amount'] ?? 0;
            $fields['الإجمالي'] = $price > 0 ? number_format((float)$price, 2) . " ج.م" : "أجل";
        } elseif ($basename === 'ProjectBandMaterial') {
            $fields['الخامة'] = $model->material->name ?? 'غير محدد';
            $fields['الكمية'] = $model->quantity ?? $source['quantity'] ?? 0;
        } elseif ($basename === 'WorkerPayment') {
            $fields['الفني'] = $model->worker->name ?? 'غير محدد';
            $amount = $model->amount ?? $source['amount'] ?? 0;
            $fields['المبلغ'] = $amount > 0 ? number_format((float)$amount, 2) . " ج.م" : "أجل";
        } elseif ($basename === 'Transaction' || $basename === 'FinancialTransaction') {
            $amount = $model->amount ?? $source['amount'] ?? 0;
            $fields['المبلغ'] = $amount > 0 ? number_format((float)$amount, 2) . " ج.م" : "أجل";
            if ($type = ($model->type ?? $source['type'] ?? null)) $fields['النوع'] = $type;
            if ($party = ($model->party ?? $source['party'] ?? null)) $fields['الطرف'] = $party;
            if ($model && $model->project) $fields['المشروع'] = $model->project->name;
        } elseif ($basename === 'Project') {
            $fields['المشروع'] = $model->name ?? $source['name'] ?? '';
            if ($model && $model->client_id && $model->client) $fields['العميل'] = $model->client->name;
        } else {
            $name = $model->name ?? $model->title ?? $source['name'] ?? $source['title'] ?? '';
            if ($name) $fields['الاسم'] = $name;
        }

        // Generic fallback to ensure name exists if possible
        if (!isset($fields['الاسم']) && !isset($fields['البند']) && !isset($fields['المشروع']) && !isset($fields['الخامة'])) {
            $name = $model?->name ?? $model?->title ?? $source['name'] ?? $source['title'] ?? null;
            if ($name) $fields['الاسم'] = $name;
        }

        // Generic extraction for amount / price / total (Always show if available)
        if (!isset($fields['المبلغ']) && !isset($fields['الإجمالي']) && !isset($fields['المبلغ/القيمة'])) {
            $amountVal = $model?->amount ?? $source['amount'] ?? $model?->total ?? $source['total'] ?? $model?->price ?? $source['price'] ?? null;
            if ($amountVal !== null) {
                $fields['المبلغ'] = $amountVal > 0 ? number_format((float)$amountVal, 2) . " ج.م" : "أجل";
            }
        }

        // Generic extraction for Project (Always show if available)
        if (!isset($fields['المشروع'])) {
            $projectId = $model?->project_id ?? $source['project_id'] ?? null;
            if ($projectId) {
                $project = \App\Models\Project::find($projectId);
                if ($project) {
                    $fields['المشروع'] = $project->name;
                }
            }
        }

        // Generic extraction for balance
        if (!isset($fields['الرصيد'])) {
            $balanceVal = $model?->balance ?? $source['balance'] ?? null;
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
        
        $source = $this->action === 'deleted' ? $this->old_values : $this->new_values;
        if (!is_array($source)) $source = [];

        if (!$model && $this->action !== 'deleted') {
            $name = $this->new_values['name'] ?? ($this->old_values['name'] ?? '');
            return "{$actionName}" . ($name ? " ({$name})" : "");
        }

        if ($basename === 'ProjectBandWorker' || $basename === 'BandWorker') {
            $workerName = $model->name ?? $source['name'] ?? 'فني';
            $calcType = $model ? $model->contractTypeAr() : 'غير محدد';
            $price = $model->amount ?? $source['amount'] ?? 0;
            $priceStr = $price > 0 ? number_format((float)$price, 2) . " ج.م" : "أجل";
            
            $bandName = $model->band->name ?? null;
            $projectName = $model->band->project->name ?? null;
            
            $details = "تعاقد مع فني ({$workerName}) بنظام ({$calcType}) بقيمة " . $priceStr;
            if ($bandName) $details .= " - لبند ({$bandName})";
            if ($projectName) $details .= " - مشروع ({$projectName})";
            
            return $details;
        }

        if ($basename === 'ProjectBandMaterial') {
            $materialName = $model->material->name ?? 'خامة';
            return "إضافة خامة ({$materialName}) بكمية " . ($model->quantity ?? $source['quantity'] ?? 0);
        }
        
        if ($this->action === 'login') {
            return "تسجيل الدخول إلى النظام";
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
