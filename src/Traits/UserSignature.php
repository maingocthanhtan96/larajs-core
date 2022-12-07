<?php

namespace LaraJS\Core\Traits;

use Illuminate\Support\Facades\Auth;

trait UserSignature
{
    protected static function bootUserSignature()
    {
        static::creating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->created_by = $user->id;
                $model->updated_by = $user->id;
            }
        });
        static::updating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->id;
            }
        });
    }
}
