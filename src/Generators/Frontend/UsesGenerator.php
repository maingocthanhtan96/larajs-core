<?php

namespace LaraJS\Core\Generators\Frontend;

use LaraJS\Core\Generators\BaseGenerator;

class UsesGenerator extends BaseGenerator
{
    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.uses');
        $this->notDelete = config('generator.not_delete.vue');
        $this->defaultValue = config('generator.default_value');
        $this->dbType = config('generator.db_type');

        $this->_generate($fields, $model);
    }

    private function _generate($fields, $model)
    {
        $folderName = $this->serviceGenerator->folderPages($model['name']);
        $path = "$this->path{$folderName}/";
        // create index.ts
        $templateData = $this->serviceGenerator->get_template('use', 'Uses/', 'vue');
        $this->serviceFile->createFile($path, $this->jsType('index'), $templateData);
        // create table.tsx
        $templateData = $this->serviceGenerator->get_template('table', 'Uses/', 'vue');
        $templateData = str_replace([
            '{{$NAME_API$}}',
            '{{$NAME_MODEL$}}',
            '{{$NAME_TABLE$}}',
            '{{$CONST_NAME_MODEL$}}',
            '{{$API_ONE$}}',
            '{{$API_ALL$}}',
        ], [
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $this->serviceGenerator->tableNameNotPlural($model['name']),
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $this->serviceGenerator->modelNamePlural($model['name']),
        ], $templateData);
        // Run before runJS because to format code
        if ($this->serviceGenerator->getOptions(config('generator.model.options.timestamps'), $model['options'])) {
            $templateData = str_replace('{{$FILTER_DATE$}}', '', $templateData);
        } else {
            $templateData = str_replace('{{$FILTER_DATE$}}', <<<'FILTER_DATE'
            filters: {
              templates: [
                {
                  template: 'search',
                },
              ],
            },
            FILTER_DATE , $templateData);
        }
        $templateData = $this->phpParserService->runParserJS("$path{$this->jsType('table')}", [
            'key' => 'uses.table:columns',
            'items' => $this->serviceGenerator->generateColumns($fields, $model),
        ], $templateData);
        $templateData = $this->phpParserService->runParserJS("$path{$this->jsType('table')}", [
            'key' => 'uses.table:search:column',
            'items' => $this->serviceGenerator->generateColumnSearch($fields),
        ], $templateData);
        if ($this->serviceGenerator->getOptions(config('generator.model.options.timestamps'), $model['options'])) {
            $templateData = $this->phpParserService->runParserJS("$path{$this->jsType('table')}", [
                'key' => 'uses.table:date:column',
                'items' => [$this->serviceGenerator->tableName($model['name']).'.updated_at'],
            ], $templateData);
        }
        $this->serviceFile->createFile($path, $this->jsType('table'), $templateData);
        // create form.tsx
        $templateData = $this->serviceGenerator->get_template('form', 'Uses/', 'vue');
        $templateData = str_replace([
            '{{$NAME_API$}}',
            '{{$NAME_MODEL$}}',
            '{{$NAME_TABLE$}}',
            '{{$CONST_NAME_MODEL$}}',
            '{{$API_ONE$}}',
        ], [
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $this->serviceGenerator->tableNameNotPlural($model['name']),
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $this->serviceGenerator->modelNameNotPlural($model['name']),
        ], $templateData);
        $templateData = $this->phpParserService->runParserJS("{$path}{$this->jsType('form')}", [
            'key' => 'uses.form:item',
            'variable' => 'form',
            'items' => $this->serviceGenerator->generateFieldForm($fields),
        ], $templateData);
        $templateData = $this->phpParserService->runParserJS("{$path}{$this->jsType('form')}", [
            'key' => 'uses.form:item',
            'variable' => 'state',
            'items' => $this->serviceGenerator->generateEnumItem($fields),
        ], $templateData);
        $templateData = $this->phpParserService->runParserJS("{$path}{$this->jsType('form')}", [
            'key' => 'uses.form:rules',
            'variable' => $this->serviceGenerator->modelNameNotPluralFe($model['name']).'Rules',
            'items' => $this->serviceGenerator->generateRules($fields, $model),
        ], $templateData);
        $templateData = $this->phpParserService->runParserJS("{$path}{$this->jsType('form')}", [
            'key' => 'uses.form:items',
            'items' => $this->generateItems($fields, $model),
        ], $templateData);
        $templateData = $this->serviceGenerator->importComponent($fields, $templateData, "{$path}{$this->jsType('form')}", $model);
        $this->serviceFile->createFile($path, $this->jsType('form'), $templateData);
        // import uses/index.ts
        $fileNameReal = $this->jsType('index');
        $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', $fileNameReal);
        $templateDataReal .= "export * from './$folderName';\n";
        $this->serviceFile->createFileReal("{$this->path}$fileNameReal", $templateDataReal);
    }
}
