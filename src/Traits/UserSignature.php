<?php

namespace LaraJS\Core\Traits;

use Illuminate\Support\Facades\Auth;

trait UserSignature
{
    protected static function bootUserSignature(): void
    {
        static::creating(function ($model) {
            if ($userId = Auth::id()) {
                $model->created_by = $userId;
                $model->updated_by = $userId;
            }
        });
        static::updating(function ($model) {
            if ($userId = Auth::id()) {
                $model->updated_by = $userId;
            }
        });
    }
}
