<?php

namespace LaraJS\Core;

use Illuminate\Support\Facades\Route;

class LaraJSCore
{
    public static function routeGenerators()
    {
        Route::group(
            ['prefix' => 'generators', 'controller' => '\\LaraJS\\Core\Controllers\\GeneratorController'],
            function () {
                Route::get('check-model', 'checkModel');
                Route::get('check-column', 'checkColumn');
                Route::get('get-models', 'getModels');
                Route::get('get-all-models', 'getAllModels');
                Route::get('get-columns', 'getColumns');
                Route::post('relationship', 'generateRelationship');
                Route::get('diagram', 'generateDiagram');
            },
        );
        Route::apiResource('generators', '\\LaraJS\\Core\Controllers\\GeneratorController');
    }

    public static function routeCores () {
        Route::controller('\\LaraJS\\Core\Controllers\\LaraJSController')->group(function () {
            Route::get('/language/{language}', 'setLanguage');
            Route::post('/logging', 'logging');
        });
    }
}
