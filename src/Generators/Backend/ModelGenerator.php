<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class ModelGenerator extends BaseGenerator
{
    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.model');

        $this->_generate($fields, $model);
    }

    private function _generate($fields, $model): void
    {
        $pathTemplate = 'Models/';
        $templateData = $this->serviceGenerator->get_template('model', $pathTemplate);
        $templateData = str_replace('{{MODEL_CLASS}}', $model['name'], $templateData);
        $templateData = str_replace('{{FIELDS}}', $this->_generateFields($fields), $templateData);
        $templateData = str_replace(
            '{{TABLE_NAME}}',
            $this->serviceGenerator->tableName($model['name']),
            $templateData,
        );
        //create sort delete
        $importLaravel = config('generator.import.laravel.use');
        $importLaravelModel = config('generator.import.laravel.model');
        $useClass = '//{{USE_CLASS}}';
        $use = '//{{USE}}';
        if ($this->serviceGenerator->getOptions(config('generator.model.options.user_signature'), $model['options'])) {
            $templateData = $this->serviceGenerator->replaceNotDelete(
                $useClass,
                $importLaravel['trait_user_signature']['file'],
                0,
                $templateData,
            );
            $templateData = $this->serviceGenerator->replaceNotDelete(
                $use,
                $importLaravel['trait_user_signature']['name'],
                1,
                $templateData,
            );
        }
        if ($this->serviceGenerator->getOptions(config('generator.model.options.soft_deletes'), $model['options'])) {
            $templateData = str_replace($useClass, $importLaravel['sort_delete']['file'], $templateData);
            $templateData = str_replace($use, $importLaravel['sort_delete']['name'], $templateData);
        } else {
            $templateData = str_replace($useClass, '', $templateData);
            $templateData = str_replace($use, '', $templateData);
        }
        $templateData = str_replace('//{{TIMESTAMPS}}', $this->serviceGenerator->getOptions(config('generator.model.options.timestamps'), $model['options']) ? '' : $importLaravelModel['timestamps'], $templateData);
        $fileName = $model['name'].'.php';
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }

    private function _generateFields($fields): string
    {
        $fieldsGenerate = [];
        foreach ($fields as $index => $field) {
            if ($index > 0) {
                $fieldsGenerate[] = "'".$field['field_name']."',";
            }
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 2), $fieldsGenerate);
    }

    private function _generateYear($fields): string
    {
        $fieldsGenerate = [];
        $dbType = config('generator.db_type');
        $pathTemplate = 'Models/';
        $templateCats = $this->serviceGenerator->get_template('cats', $pathTemplate);
        foreach ($fields as $index => $field) {
            if ($index > 0) {
                if ($field['db_type'] === $dbType['year']) {
                    $name = $field['field_name'];
                    $fieldsGenerate[] = str_replace('{{FIELD}}', "'$name' => 'string',", $templateCats);
                }
            }
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 2), $fieldsGenerate);
    }
}
