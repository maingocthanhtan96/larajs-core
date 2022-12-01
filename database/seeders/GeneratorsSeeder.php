<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GeneratorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $model = [
            'name' => 'User',
            'name_trans' => 'User',
            'limit' => 25,
            'options' => [
                'Timestamps',
                'Test Cases',
                'Soft Deletes',
            ],
        ];

        $fields = [
            [
                'id' => 1,
                'field_name' => 'id',
                'field_name_trans' => 'ID',
                'db_type' => 'Increments',
                'enum' => [],
                'length_varchar' => 191,
                'default_value' => 'None',
                'position_column' => 24,
                'as_define' => null,
                'after_column' => null,
                'search' => false,
                'sort' => true,
                'show' => true,
                'options' => [
                    'comment' => null,
                    'unique' => false,
                    'index' => false,
                ],
            ],
            [
                'id' => 2,
                'field_name' => 'name',
                'field_name_trans' => 'Name',
                'db_type' => 'VARCHAR',
                'enum' => [],
                'length_varchar' => 191,
                'default_value' => 'None',
                'position_column' => 12,
                'as_define' => null,
                'after_column' => null,
                'search' => true,
                'sort' => true,
                'show' => true,
                'options' => [
                    'comment' => null,
                    'unique' => false,
                    'index' => false,
                ],
            ],
            [
                'id' => 3,
                'field_name' => 'email',
                'field_name_trans' => 'Email',
                'db_type' => 'VARCHAR',
                'enum' => [],
                'length_varchar' => 191,
                'default_value' => 'None',
                'position_column' => 12,
                'as_define' => null,
                'after_column' => null,
                'search' => true,
                'sort' => true,
                'show' => true,
                'options' => [
                    'comment' => null,
                    'unique' => true,
                    'index' => false,
                ],
            ],
            [
                'id' => 4,
                'field_name' => 'avatar',
                'field_name_trans' => 'Avatar',
                'db_type' => 'VARCHAR',
                'enum' => [],
                'length_varchar' => 191,
                'default_value' => 'NULL',
                'position_column' => 12,
                'as_define' => null,
                'after_column' => null,
                'search' => false,
                'sort' => false,
                'show' => true,
                'options' => [
                    'comment' => null,
                    'unique' => false,
                    'index' => false,
                ],
            ],
            [
                'id' => 5,
                'field_name' => 'password',
                'field_name_trans' => 'Password',
                'db_type' => 'VARCHAR',
                'enum' => [],
                'length_varchar' => 191,
                'default_value' => 'None',
                'position_column' => 12,
                'as_define' => null,
                'after_column' => null,
                'search' => false,
                'sort' => false,
                'show' => false,
                'options' => [
                    'comment' => null,
                    'unique' => false,
                    'index' => false,
                ],
            ],
        ];

        \LaraJS\Core\Models\Generator::create([
            'field' => json_encode($fields),
            'model' => json_encode($model),
            'table' => Str::snake(Str::plural('user')),
            'files' => '{}',
        ]);
    }
}
