<?php

namespace LaraJS\Core;

use Illuminate\Support\Facades\Route;

class LaraJSCore
{
    public static function routeCores()
    {
        Route::controller('\\LaraJS\\Core\Controllers\\LaraJSController')->group(function () {
            Route::get('/language/{language}', 'setLanguage');
        });
    }
}
