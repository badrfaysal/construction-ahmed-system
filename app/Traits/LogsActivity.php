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
            self::$batchId = (string) \Illuminate\Support\Str::uuid();
            
            app()->terminating(function () {
                $batchId = self::$batchId;
                if (!$batchId) return;
                
                $logs = SystemActivityLog::where('batch_id', $batchId)->orderBy('id')->get();
                if ($logs->isEmpty()) return;

                $mainLog = $logs->first();
                $subLogs = $logs->slice(1);

                $text = "<b>⚙️ سجل العمليات - تحديث جديد ⚙️</b>\n\n";
                $text .= "<b>الحركة الرئيسية:</b> " . $mainLog->actionAr() . " (" . $mainLog->modelTypeAr() . ")\n";
                
                // Attempt to get name if the model has one
                $model = $mainLog->subject;
                if ($model && !empty($model->name)) {
                    $text .= "<b>الاسم:</b> " . $model->name . "\n";
                } elseif ($model && !empty($model->title)) {
                    $text .= "<b>الاسم:</b> " . $model->title . "\n";
                }

                $text .= "<b>بواسطة:</b> " . ($mainLog->user->name ?? 'النظام') . "\n";

                if ($subLogs->count() > 0) {
                    $text .= "\n<b>📋 تأثيرات إضافية تم تنفيذها:</b>\n";
                    foreach ($subLogs as $sub) {
                        $text .= "▪️ " . $sub->actionAr() . " (" . $sub->modelTypeAr() . ")\n";
                    }
                }

                \App\Jobs\SendTelegramNotification::dispatchSync($text);
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
