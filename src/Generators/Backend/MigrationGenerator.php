<?php

namespace LaraJS\Core\Generators\Backend;

use Carbon\Carbon;
use LaraJS\Core\Generators\BaseGenerator;

class MigrationGenerator extends BaseGenerator
{
    public string $file;

    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.migration');

        $this->file = $this->_generate($fields, $model);
    }

    private function _generate($fields, $model): string
    {
        $now = Carbon::now();
        $pathTemplate = 'Databases/Migrations/';
        $templateData = $this->serviceGenerator->get_template('migration', $pathTemplate);
        $templateData = str_replace('{{FIELDS}}', $this->_generateFields($fields, $model), $templateData);
        $templateData = str_replace('{{DATE_TIME}}', $now->toDateTimeString(), $templateData);
        $templateData = str_replace(
            '{{TABLE_NAME}}',
            $this->serviceGenerator->tableName($model['name']),
            $templateData,
        );
        $fileName = date('Y_m_d_His')."_create_{$this->serviceGenerator->tableName($model['name'])}_table.php";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);

        return $fileName;
    }

    private function _generateFields($fields, $model): string
    {
        $fieldsGenerate = [];

        $configDBType = config('generator.db_type');
        $configDefaultValue = config('generator.default_value');
        $fieldsGenerate[] = '$table->bigIncrements("id");';
        foreach ($fields as $index => $field) {
            $table = '';
            foreach ($configDBType as $typeLaravel => $typeDB) {
                if ($field['db_type'] === $configDBType['string']) {
                    $table = '$table->string("'.trim($field['field_name']).'", '.$field['length_varchar'].')';
                    break;
                }
                $migrationField = $this->serviceGenerator->migrationFields(
                    $field,
                    $configDBType,
                    $typeDB,
                    $typeLaravel,
                    $model,
                );
                if ($migrationField) {
                    $table = $migrationField;
                    break;
                }
            }
            $table .= $this->serviceGenerator->migrationDefaultValue($field, $configDefaultValue);
            $table .= $this->serviceGenerator->migrationOption($field);
            if ($index > 0) {
                $table .= ';';
                $fieldsGenerate[] = $table;
            }
        }
        if ($this->serviceGenerator->getOptions(config('generator.model.options.user_signature'), $model['options'])) {
            $fieldsGenerate[] = '$table->unsignedBigInteger(\'created_by\')->nullable();';
            $fieldsGenerate[] = '$table->unsignedBigInteger(\'updated_by\')->nullable();';
        }
        if ($this->serviceGenerator->getOptions(config('generator.model.options.timestamps'), $model['options'])) {
            $fieldsGenerate[] = '$table->timestamps();';
        }
        if ($this->serviceGenerator->getOptions(config('generator.model.options.soft_deletes'), $model['options'])) {
            $fieldsGenerate[] = '$table->softDeletes();';
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 3), $fieldsGenerate);
    }
}
