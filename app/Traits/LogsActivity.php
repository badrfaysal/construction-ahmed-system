<?php

namespace App\Traits;

use App\Models\SystemActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static $batchId = null;

    public static function getBatchId()
    {
        if (self::$batchId === null) {
            $batchId = (string) \Illuminate\Support\Str::uuid();
            self::$batchId = $batchId;
            
            register_shutdown_function(function () use ($batchId) {
                try {
                    $logs = SystemActivityLog::where('batch_id', $batchId)->orderBy('id')->get();
                    
                    if ($logs->isEmpty()) return;

                    $mainLog = $logs->first();
                    $subLogs = $logs->slice(1);

                    $actionEmoji = match($mainLog->action) {
                        'created' => '✨ إضافة جديدة',
                        'updated' => '📝 تعديل',
                        'deleted' => '❌ حذف',
                        default => $mainLog->actionAr()
                    };

                    $text = "<b>🏢 مـقـاولات 🏢</b>\n\n";

                    // Custom override for Manual Invoices
                    if ($mainLog->model_type === 'App\Models\ManualInvoice') {
                        if ($mainLog->action === 'created') {
                            $clientName = $mainLog->new_values['client_name'] ?? 'بدون اسم';
                            $text .= "<b>العملية:</b> ✨ إنشاء فاتورة يدوية جديدة للعميل ({$clientName})\n";
                        } else {
                            $text .= "<b>العملية:</b> {$actionEmoji} فاتورة يدوية للعميل (" . ($mainLog->new_values['client_name'] ?? $mainLog->old_values['client_name'] ?? 'بدون اسم') . ")\n";
                        }
                        
                        $fields = $mainLog->getTelegramFields();
                        foreach ($fields as $key => $value) {
                            if (!empty($value)) {
                                $text .= "<b>{$key}:</b> {$value}\n";
                            }
                        }
                        
                        // Clear sub-logs for manual invoices to avoid cluttering with items (delete/create)
                        $subLogs = collect();
                    } else {
                        $text .= "<b>العملية:</b> {$actionEmoji} ({$mainLog->modelTypeAr()})\n";
                        
                        $fields = $mainLog->getTelegramFields();
                        foreach ($fields as $key => $value) {
                            if (!empty($value)) {
                                $text .= "<b>{$key}:</b> {$value}\n";
                            }
                        }
                    }

                    $text .= "<b>بواسطة:</b> " . ($mainLog->user->name ?? 'النظام') . "\n";

                    if ($subLogs->count() > 0) {
                        $text .= "\n<b>حركات مرتبطة بالبند:</b>\n";
                        foreach ($subLogs as $sub) {
                            $text .= "▪️ " . $sub->getTelegramDescription() . "\n";
                        }
                    }

                    \App\Jobs\SendTelegramNotification::dispatchSync($text);
                } catch (\Throwable $e) {
                    \Log::error("Telegram Batch Error: " . $e->getMessage());
                }
            });
        }
        return self::$batchId;
    }

    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            self::logAction($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getAttributes();
            
            $changedKeys = array_keys($model->getChanges());
            $oldDiff = [];
            $newDiff = [];
            foreach ($changedKeys as $key) {
                if ($key === 'updated_at') continue;
                $oldDiff[$key] = $oldValues[$key] ?? null;
                $newDiff[$key] = $newValues[$key] ?? null;
            }

            if (!empty($newDiff)) {
                self::logAction($model, 'updated', $oldDiff, $newDiff);
            }
        });

        static::deleted(function ($model) {
            self::logAction($model, 'deleted', $model->getAttributes(), null);
        });
    }

    protected static function logAction($model, string $action, ?array $oldValues = null, ?array $newValues = null)
    {
        try {
            SystemActivityLog::create([
                'batch_id'   => self::getBatchId(),
                'user_id'    => Auth::id(),
                'action'     => $action,
                'model_type' => get_class($model),
                'model_id'   => $model->id ?? 0,
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}
