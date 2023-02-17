<?php

namespace LaraJS\Core\Generators;

use LaraJS\Core\Services\FileService;
use LaraJS\Core\Services\GeneratorService;

class BaseGenerator
{
    /** @var GeneratorService */
    protected GeneratorService $serviceGenerator;

    /** @var FileService */
    protected FileService $serviceFile;

    /** @var string */
    protected string $path;

    /** @var array */
    protected array $notDelete;

    /** @var array */
    protected array $defaultValue;

    /** @var array */
    protected array $dbType;

    public function __construct()
    {
        $this->serviceGenerator = new GeneratorService();
        $this->serviceFile = new FileService();
        $this->dbType = config('generator.db_type');
    }

    public function jsType(string $type = null)
    {
        $isJS = config('generator.js_language') === 'js';
        switch ($type) {
            case 'form':
                if ($isJS)  return 'form.jsx';
                else return 'form.tsx';
            case 'table':
                if ($isJS)  return 'table.jsx';
                else return 'table.tsx';
            case 'index':
                if ($isJS)  return 'index.js';
                else return 'index.ts';
            case 'ext':
                return config('generator.js_language');
            default:
                return $isJS;
        }
    }

    public function getImportJsOrTs($isMono = false): string
    {
        $isJS = config('generator.js_language') === 'js';
        if ($isMono) {
            return $isJS ? '@' : '@larajs';
        }
        return $isJS ? '@' : '@larajs/cms';
    }

    public function rollbackFile($path, $fileName): bool
    {
        if (file_exists($path . $fileName)) {
            return FileService::deleteFile($path, $fileName);
        }

        return false;
    }

    public function generateItems($fields, $model): string
    {
        $fieldsGenerate = [];
        $formFeGenerateField = $this->serviceGenerator->formFeGenerateField();
        $templateFormItem = $this->serviceGenerator->get_template('item', 'Forms/', 'vue');
        foreach ($fields as $index => $field) {
            $tableName = $this->serviceGenerator->tableNameNotPlural($model['name']);
            $templateFormItemClone = $templateFormItem;
            $templateFormItemClone = str_replace('{{$PROP_NAME$}}', $field['field_name'], $templateFormItemClone);
            $templateFormItemClone = str_replace('{{$COLUMNS$}}', $field['position_column'], $templateFormItemClone);
            $fieldsGenerate[] = match ($field['db_type']) {
                $this->dbType['integer'],
                $this->dbType['bigInteger'],
                $this->dbType['float'],
                $this->dbType['double']
                    => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateInput('inputNumber', $tableName, $field, $index),
                    $templateFormItemClone,
                ),
                $this->dbType['boolean'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateBoolean($tableName, $field),
                    $templateFormItemClone,
                ),
                $this->dbType['date'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateDateTime('date', $tableName, $field),
                    $templateFormItemClone,
                ),
                $this->dbType['dateTime'], $this->dbType['timestamp'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateDateTime('dateTime', $tableName, $field),
                    $templateFormItemClone,
                ),
                $this->dbType['time'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateDateTime('time', $tableName, $field),
                    $templateFormItemClone,
                ),
                $this->dbType['year'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateDateTime('year', $tableName, $field),
                    $templateFormItemClone,
                ),
                $this->dbType['string'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateInput('input', $tableName, $field, $index, $this->dbType['string']),
                    $templateFormItemClone,
                ),
                $this->dbType['text'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateInput('textarea', $tableName, $field, $index),
                    $templateFormItemClone,
                ),
                $this->dbType['longtext'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateTinymce($tableName, $field),
                    $templateFormItemClone,
                ),
                $this->dbType['enum'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateEnum($tableName, $field),
                    $templateFormItemClone,
                ),
                $this->dbType['json'] => str_replace(
                    '{{$COMPONENT$}}',
                    $formFeGenerateField->generateJson($tableName, $field),
                    $templateFormItemClone,
                ),
                default => '',
            };
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 5, 2), $fieldsGenerate);
    }
}
