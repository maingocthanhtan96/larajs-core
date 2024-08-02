<?php

namespace LaraJS\Core\Generators\Backend;

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
        $pathTemplate = 'Databases/Migrations/';
        $templateData = $this->serviceGenerator->get_template('migration', $pathTemplate);
        $templateData = str_replace(['{{FIELDS}}', '{{TABLE_NAME}}'], [$this->_generateFields($fields, $model), $this->serviceGenerator->tableName($model['name'])], $templateData);
        $fileName = date('Y_m_d_His')."_create_{$this->serviceGenerator->tableName($model['name'])}_table.php";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);

        return $fileName;
    }

    private function _generateFields($fields, $model): string
    {
        $fieldsGenerate = [];

        $configDBType = config('generator.db_type');
        $configDefaultValue = config('generator.default_value');

        foreach ($fields as $index => $field) {
            if ($index === 0) {
                $fieldsGenerate[] = '$table->bigIncrements("id");';

                continue;
            }
            $fieldType = $field['db_type'];
            $fieldName = trim($field['field_name']);
            $fieldLength = $field['length_varchar'] ?? 191;

            $table = match (true) {
                in_array($fieldType, [$configDBType['string'], $configDBType['char']], false) => "\$table->string('$fieldName'" . ($fieldLength ? ", $fieldLength" : '') . ')',
                default => $this->serviceGenerator->migrationFields($field, $configDBType, $fieldType, array_search($fieldType, $configDBType, false)),
            };
            $table .= $this->serviceGenerator->migrationDefaultValue($field, $configDefaultValue);
            $table .= $this->serviceGenerator->migrationOption($field);
            $table .= ';';
            $fieldsGenerate[] = $table;
        }

        $modelOptions = $model['options'] ?? [];
        if ($this->serviceGenerator->getOptions(config('generator.model.options.user_signature'), $modelOptions)) {
            $fieldsGenerate[] = "\$table->foreignId('created_by')->index();";
            $fieldsGenerate[] = "\$table->foreignId('updated_by')->index();";
        }
        if ($this->serviceGenerator->getOptions(config('generator.model.options.timestamps'), $modelOptions)) {
            $fieldsGenerate[] = '$table->timestamps();';
        }
        if ($this->serviceGenerator->getOptions(config('generator.model.options.soft_deletes'), $modelOptions)) {
            $fieldsGenerate[] = '$table->softDeletes();';
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 3), $fieldsGenerate);
    }
}
