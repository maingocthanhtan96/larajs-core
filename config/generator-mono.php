<?php

$API_VERSION = strtoupper(env('GENERATOR_API_VERSION', 'V1')) . '/';

return [
    'js_language' => 'ts',
    'api_version' => strtoupper(env('GENERATOR_API_VERSION', 'V1')),
    'node_path' => env('GENERATOR_NODE_PATH', 'node'),
    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    */
    'template' => [
        'laravel' => public_path('vendor/generator/templates/Laravel/'),
        'vue' => public_path('vendor/generator/templates/Vue/'),
        'package' => public_path('vendor/generator/templates/Package/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    |
    */
    'path' => [
        'laravel' => [
            'migration' => base_path('database/migrations/'),
            'seeder' => base_path('database/seeders/'),
            'factory' => base_path('database/factories/'),
            'model' => app_path('Models/'),
            'repository' => app_path('Repositories/'),
            'service' => app_path('Services/' . $API_VERSION),
            'observer' => app_path('Observers/'),
            'provider' => app_path('Providers/'),
            'api_routes' => base_path('routes/api-' . strtolower(env('GENERATOR_API_VERSION', 'V1')) . '.php'),
            'api_controller' => app_path('Http/Controllers/Api/' . $API_VERSION),
            'resources' => app_path('Http/Resources/' . $API_VERSION),
            'lang' => base_path('lang/'),
            'request' => app_path('Http/Requests/' . $API_VERSION),
            'tests' => [
                'feature' => base_path('tests/Feature/'),
            ],
        ],
        'delete_files' => [
            'laravel' => [
                'migration' => '/database/migrations/',
                'seeder' => '/database/seeders/',
                'factory' => '/database/factories/',
                'model' => '/app/Models/',
                'repository' => '/app/Repositories/',
                'service' => '/app/Services/' . $API_VERSION,
                'observer' => '/app/Observers/',
                'provider' => '/app/Providers/',
                'api_routes' => '/routes/api-v1.php',
                'api_controller' => '/app/Http/Controllers/Api/' . $API_VERSION,
                'resources' => '/app/Http/Resources/' . $API_VERSION,
                'lang' => '/lang/',
                'request' => '/app/Http/Requests/' . $API_VERSION,
                'tests' => [
                    'feature' => '/tests/Feature/',
                ],
            ],
            'vue' => [
                'api' => '../cms/src/api/' . $API_VERSION,
                'uses' => '../cms/src/uses/',
                'views' => '../cms/src/views/',
                'router_modules' => '../cms/src/router/modules/',
                'router' => '../cms/src/router/',
                'resource_js' => '../cms/src/',
            ],
            'package' => [
                'model' => '../../packages/common/src/models/',
            ],
        ],
        'vue' => [
            'api' => cms_path('api/' . $API_VERSION),
            'views' => cms_path('views/'),
            'router_modules' => cms_path('router/modules/'),
            'router' => cms_path('router/'),
            'resource_js' => cms_path(),
            'uses' => cms_path('uses/'),
        ],
        'package' => [
            'model' => package_path('common', 'models/'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    */

    'model' => [
        'options' => [
            'soft_deletes' => 'Soft Deletes',
            'timestamps' => 'Timestamps',
            'user_signature' => 'User Signature',
            'datatables' => 'Datatables',
            'ignore_migrate' => 'Ignore Migrate',
            'only_migrate' => 'Only Migrate',
            'test_cases' => 'Test Cases',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DB type
    |--------------------------------------------------------------------------
    |
    */
    'db_type' => [
        'increments' => 'Increments',
        'integer' => 'INT',
        'bigInteger' => 'BIGINT',
        'float' => 'FLOAT',
        'double' => 'DOUBLE',
        'boolean' => 'BOOLEAN',
        'date' => 'DATE',
        'dateTime' => 'DATETIME',
        'timestamp' => 'TIMESTAMP',
        'time' => 'TIME',
        'year' => 'YEAR',
        'string' => 'VARCHAR',
        'text' => 'TEXT',
        'longtext' => 'LONGTEXT',
        'enum' => 'ENUM',
        'json' => 'JSON',
        // relationship
        'hasOne' => 'hasOne',
        'hasMany' => 'hasMany',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default value
    |--------------------------------------------------------------------------
    |
    */

    'default_value' => [
        'none' => 'None',
        'null' => 'NULL',
        'as_define' => 'As define',
        'current_timestamps' => 'CURRENT_TIMESTAMPS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    */

    'namespace' => [
        'model' => 'App\Models',
        'repository' => 'App\Repositories',
        'api_controller' => 'App\Http\Controllers\Api\\' . env('GENERATOR_API_VERSION', 'V1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | NOT DELETE
    |--------------------------------------------------------------------------
    |
    */
    'not_delete' => [
        'laravel' => [
            'model' => [],
            'route' => [
                'api' => [
                    'user' => '//{{ROUTE_USER_NOT_DELETE_THIS_LINE}}',
                ],
            ],
            'lang' => [
                'en' => [
                    'route' => '',
                    'table' => '',
                ],
                'ja' => [
                    'route' => '',
                    'table' => '',
                ],
                'vi' => [
                    'route' => '',
                    'table' => '',
                ],
            ],
            'db' => [
                'seeder' => '//{{SEEDER_NOT_DELETE_THIS_LINE}}',
            ],
            'request' => [
                'rule' => '//{{REQUEST_RULES_NOT_DELETE_THIS_LINE}}',
            ],
            'repository' => [],
            'observer' => [],
            'tests' => [],
        ],
        'vue' => [
            'route' => [],
            'form' => [
                'create' => '// {{$CREATE_NOT_DELETE_THIS_LINE$}}',
                'edit' => '// {{$EDIT_NOT_DELETE_THIS_LINE$}}',
            ],
            'uses' => [
                'use' => '// {{$IMPORT_USE_NOT_DELETE_THIS_LINE$}}',
                'form' => [],
            ],
            'api' => [],
        ],
        'package' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | IMPORT
    |--------------------------------------------------------------------------
    |
    */
    'import' => [
        'laravel' => [
            'use' => [
                'sort_delete' => [
                    'file' => 'use Illuminate\Database\Eloquent\SoftDeletes;',
                    'name' => 'use SoftDeletes;',
                ],
                'trait_user_signature' => [
                    'file' => 'use LaraJS\Core\Traits\UserSignature;',
                    'name' => 'use UserSignature;',
                    'model' => '\App\Models\User::factory(),',
                ],
            ],
            'model' => [
                'timestamps' => 'public $timestamps = false;',
            ],
        ],
        'vue' => [
            'tinymce' => [
                'name' => 'Tinymce',
                'path' => '@larajs/components',
            ],
            'json_editor' => [
                'name' => 'JsonEditor',
                'path' => '@larajs/components',
            ],
            'parse_time' => [
                'name' => 'parseTime',
                'path' => '@larajs/utils',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    |
    */
    'relationship' => [
        'ignore_model' => ['Generator', 'Permission', 'Role', 'BaseModel', 'PasswordReset', 'PersonalAccessToken'],
        'relationship' => [
            'has_one' => 'hasOne',
            'has_many' => 'hasMany',
            'belongs_to_many' => 'belongsToMany',
            'belongs_to' => 'belongsTo',
        ],
        'options' => [
            'search' => 'Search',
            'sort' => 'Sort',
            'show' => 'Show',
        ],
    ],
];
