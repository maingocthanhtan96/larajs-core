<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => 'api/v1',
        'middleware' => ['api'],
    ],
    function () {
        Route::get('/language/{language}', [\LaraJS\Core\Controllers\LaraJSController::class, 'setLanguage']);
    },
);
