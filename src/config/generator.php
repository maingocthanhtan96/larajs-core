<?php

$API_VERSION = env('API_VERSION_GENERATOR', 'v1') . '/';

return [
    'api_version' => env('API_VERSION_GENERATOR', 'v1'),
    'permission' => [
        'view_menu' => 'VIEW_MENU',
    ],
    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    */
    'template' => [
        'laravel' => public_path('generator/templates/Laravel/'),
        'vue' => public_path('generator/templates/Vue/'),
        'package' => public_path('generator/templates/Package/'),
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
            'observer' => app_path('Observers/'),
            'provider' => app_path('Providers/'),
            'api_routes' => base_path('routes/api-v1.php'),
            'api_controller' => app_path('Http/Controllers/Api/' . $API_VERSION),
            'lang' => base_path('lang/'),
            'request' => app_path('Http/Requests/'),
            'swagger' => app_path('LaraJS/Swagger/'),
            'tests' => [
                'feature' => base_path('tests/Feature/Controllers/Api/' . $API_VERSION),
            ],
        ],
        'delete_files' => [
            'laravel' => [
                'migration' => '/database/migrations/',
                'seeder' => '/database/seeders/',
                'factory' => '/database/factories/',
                'model' => '/app/Models/',
                'repository' => '/app/Repositories/',
                'observer' => '/app/Observers/',
                'provider' => '/app/Providers/',
                'api_routes' => '/routes/api-v1.php',
                'api_controller' => '/app/Http/Controllers/Api/' . $API_VERSION,
                'lang' => '/lang/',
                'request' => '/app/Http/Requests/',
                'swagger' => '/app/LaraJS/Swagger/',
                'tests' => [
                    'feature' => '/tests/Feature/Controllers/Api/' . $API_VERSION,
                ],
            ],
            'vue' => [
                'api' => '/resources/scripts/api/' . $API_VERSION,
                'uses' => '/resources/scripts/uses/',
                'views' => '/resources/scripts/views/',
                'router_modules' => '/resources/scripts/router/modules/',
                'router' => '/resources/scripts/router/',
                'resource_js' => '/resources/scripts/',
            ],
            'package' => [
                'model' => '../../packages/common/models/',
            ],
        ],
        'vue' => [
            'api' => base_path('resources/scripts/api/' . $API_VERSION),
            'views' => base_path('resources/scripts/views/'),
            'router_modules' => base_path('resources/scripts/router/modules/'),
            'router' => base_path('resources/scripts/router/'),
            'resource_js' => base_path('resources/scripts/'),
            'uses' => base_path('resources/scripts/uses/'),
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
            'role_admin' => 'Role Admin',
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
        'api_controller' => 'App\Http\Controllers\Api\\' . env('API_VERSION_GENERATOR', 'v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | NOT DELETE
    |--------------------------------------------------------------------------
    |
    */
    'not_delete' => [
        'laravel' => [
            'controller' => [
                'relationship' => '//{{CONTROLLER_RELATIONSHIP_NOT_DELETE_THIS_LINE}}',
            ],
            'model' => [
                'use_class' => '//{{USE_CLASS_NOT_DELETE_THIS_LINE}}',
                'use' => '//{{USE_NOT_DELETE_THIS_LINE}}',
                'timestamps' => '//{{TIMESTAMPS_NOT_DELETE_THIS_LINE}}',
                'relationship' => '//{{RELATIONS_NOT_DELETE_THIS_LINE}}',
                'cats' => '//{{CATS_NOT_DELETE_THIS_LINE}}',
                'fill_able' => '//{{FILL_ABLE_NOT_DELETE_THIS_LINE}}',
            ],
            'route' => [
                'api' => [
                    'admin' => '//{{ROUTE_ADMIN_NOT_DELETE_THIS_LINE}}',
                    'user' => '//{{ROUTE_USER_NOT_DELETE_THIS_LINE}}',
                ],
            ],
            'lang' => [
                'en' => [
                    'route' => '//{{LANG_ROUTE_NOT_DELETE_THIS_LINE}}',
                    'table' => '//{{LANG_TABLE_NOT_DELETE_THIS_LINE}}',
                ],
                'ja' => [
                    'route' => '//{{LANG_ROUTE_NOT_DELETE_THIS_LINE}}',
                    'table' => '//{{LANG_TABLE_NOT_DELETE_THIS_LINE}}',
                ],
                'vi' => [
                    'route' => '//{{LANG_ROUTE_NOT_DELETE_THIS_LINE}}',
                    'table' => '//{{LANG_TABLE_NOT_DELETE_THIS_LINE}}',
                ],
            ],
            'db' => [
                'seeder' => '//{{SEEDER_NOT_DELETE_THIS_LINE}}',
            ],
            'request' => [
                'rule' => '//{{REQUEST_RULES_NOT_DELETE_THIS_LINE}}',
            ],
            'swagger' => [
                'property' => '//{{SWAGGER_PROPERTY_NOT_DELETE_THIS_LINE}}',
                'soft_deletes' => '//{{SWAGGER_SOFT_DELETES_NOT_DELETE_THIS_LINE}}',
                'timestamps' => '//{{SWAGGER_TIMESTAMPS_NOT_DELETE_THIS_LINE}}',
                'json_content' => ' *                  x="{{SWAGGER_PROPERTY_JSON_CONTENT_NOT_DELETE_THIS_LINE}}"',
            ],
            'repository' => [
                'use_class' => '//{{USE_CLASS_NOT_DELETE_THIS_LINE}}',
                'relationship_mtm' => '//{{REPOSITORY_RELATIONSHIP_MTM_NOT_DELETE_THIS_LINE}}',
                'relationship_mtm_create' => '//{{REPOSITORY_RELATIONSHIP_MTM_CREATE_NOT_DELETE_THIS_LINE}}',
                'relationship_mtm_show' => '//{{REPOSITORY_RELATIONSHIP_MTM_SHOW_NOT_DELETE_THIS_LINE}}',
                'relationship_mtm_update' => '//{{REPOSITORY_RELATIONSHIP_MTM_UPDATE_NOT_DELETE_THIS_LINE}}',
                'relationship_mtm_delete' => '//{{REPOSITORY_RELATIONSHIP_MTM_DELETE_NOT_DELETE_THIS_LINE}}',
                'provider' => [
                    'use_class' => '//{{USE_CLASS_SERVICE_PROVIDER_NOT_DELETE_THIS_LINE}}',
                    'register' => '//{{REGISTER_SERVICE_PROVIDER_NOT_DELETE_THIS_LINE}}',
                ],
            ],
            'observer' => [
                'observer_mtm_saved' => '//{{OBSERVER_RELATIONSHIP_MTM_SAVED_NOT_DELETE_THIS_LINE}}',
                'observer_mtm_deleted' => '//{{OBSERVER_RELATIONSHIP_MTM_DELETED_NOT_DELETE_THIS_LINE}}',
                'provider' => [
                    'use_class' => '//{{USE_CLASS_SERVICE_PROVIDER_EVENT_NOT_DELETE_THIS_LINE}}',
                    'register' => '//{{REGISTER_SERVICE_PROVIDER_EVENT_NOT_DELETE_THIS_LINE}}',
                ],
            ],
            'tests' => [
                'relationship' => '//{{TESTS_RELATIONSHIP_NOT_DELETE_THIS_LINE}}',
            ],
        ],
        'vue' => [
            'route' => [
                'import' => '// {{$IMPORT_ROUTE_NOT_DELETE_THIS_LINE$}}',
                'async' => '// {{$ROUTE_ASYNC_NOT_DELETE_THIS_LINE$}}',
            ],
            'form' => [
                'item' => '<!--{{$FROM_ITEM_NOT_DELETE_THIS_LINE$}}-->',
                'fields' => '// {{$FORM_FIELDS_NOT_DELETE_THIS_LINE$}}',
                'rules' => '// {{$RULES_NOT_DELETE_THIS_LINE$}}',
                'import_component' => '// {{$IMPORT_COMPONENT_NOT_DELETE_THIS_LINE$}}',
                'import_component_name' => '// {{$IMPORT_COMPONENT_NAME_NOT_DELETE_THIS_LINE$}}',
                'create' => '// {{$CREATE_NOT_DELETE_THIS_LINE$}}',
                'edit' => '// {{$EDIT_NOT_DELETE_THIS_LINE$}}',
                'methods' => '// {{$METHODS_NOT_DELETE_THIS_LINE$}}',
                'data' => '// {{$DATA_NOT_DELETE_THIS_LINE$}}',
                'stringify' => '// {{$FILE_JSON_STRINGIFY_NOT_DELETE_THIS_LINE$}}',
                'reset_field' => '// {{$RESET_FIELD_NOT_DELETE_THIS_LINE$}}', // reset file
                'api' => '// {{$API_NOT_DELETE_THIS_LINE$}}',
                'this_check' => '// {{$NOT_DELETE$}}',
                'column' => '// {{$COLUMN_NOT_DELETE_THIS_LINE$}}',
                'state_root' => '// {{$STATE_ROOT_NOT_DELETE_THIS_LINE$}}',
            ],
            'views' => [
                'templates' => '<!--{{$TEMPLATES_NOT_DELETE_THIS_LINE$}}-->',
                'headings' => '// {{$HEADING_FIELDS_NOT_DELETE_THIS_LINE$}}',
                'column_classes' => '// {{$COLUMN_CLASSES_FIELDS_NOT_DELETE_THIS_LINE$}}',
            ],
            'uses' => [
                'use' => '// {{$IMPORT_USE_NOT_DELETE_THIS_LINE$}}',
                'form' => [
                    'item' => '// {{$FORM_ITEM_NOT_DELETE_THIS_LINE$}}',
                    'import' => '// {{$IMPORT_NOT_DELETE_THIS_LINE$}}',
                ],
                'api' => '// {{$IMPORT_API_NOT_DELETE_THIS_LINE$}}',
                'function' => [
                    'import' => '// {{$IMPORT_FUNCTION_NOT_DELETE_THIS_LINE$}}',
                    'export' => '// {{$EXPORT_FUNCTION_NOT_DELETE_THIS_LINE$}}',
                ],
                'query' => [
                    'column_search' => '// {{$COLUMN_SEARCH_NOT_DELETE_THIS_LINE$}}',
                    'relationship' => '// {{$COLUMN_RELATIONSHIP_NOT_DELETE_THIS_LINE$}}',
                ],
            ],
        ],
        'package' => [
            'model' => [
                'index' => '// {{$MODEL_NOT_DELETE_THIS_LINE$}}',
                'import' => '// {{$IMPORT_COMMON_NOT_DELETE_THIS_LINE$}}',
            ],
        ],
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
                    'file' => 'use App\Traits\UserSignature;',
                    'name' => 'use UserSignature;',
                ],
            ],
            'model' => [
                'timestamps' => 'public $timestamps = false;',
            ],
        ],
        'vue' => [
            'tinymce' => [
                'file' => "import Tinymce from '@/components/Tinymce/index.vue';",
                'name' => 'Tinymce,',
            ],
            'json_editor' => [
                'file' => "import JsonEditor from '@/components/JsonEditor/index.vue';",
                'name' => 'JsonEditor,',
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
