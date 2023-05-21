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
        $templateData = str_replace(
            '{{$CONST_NAME_MODEL$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$NAME_USES$}}',
            $this->serviceGenerator->modelNamePlural($model['name']),
            $templateData,
        );
        $templateData = str_replace('{{$API_VERSION$}}', config('generator.api_version'), $templateData);
        $templateData = str_replace(
            '{{$NAME_ROUTE_API$}}',
            $this->serviceGenerator->nameAttribute($model['name']),
            $templateData,
        );
        $this->serviceFile->createFile($path, $this->jsType('index'), $templateData);
        // create table.tsx
        $templateData = $this->serviceGenerator->get_template('table', 'Uses/', 'vue');
        $templateData = str_replace(
            '{{$NAME_USES$}}',
            $this->serviceGenerator->modelNamePlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$NAME_MODEL$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$NAME_TABLE$}}',
            $this->serviceGenerator->tableNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$CONST_NAME_MODEL$}}',
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $templateData,
        );
        $templateData = $this->phpParserService->runParserJS("$path{$this->jsType('table')}", [
            'key' => 'uses.table:columns',
            'items' => $this->serviceGenerator->generateColumns($fields, $model),
        ], $templateData);
        $templateData = $this->phpParserService->runParserJS("$path{$this->jsType('table')}", [
            'key' => 'uses.table:column_search',
            'items' => $this->serviceGenerator->generateColumnSearch($fields),
        ], $templateData);
        $this->serviceFile->createFile($path, $this->jsType('table'), $templateData);
        // create form.tsx
        $templateData = $this->serviceGenerator->get_template('form', 'Uses/', 'vue');
        $templateData = str_replace(
            '{{$NAME_USES$}}',
            $this->serviceGenerator->modelNamePlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$NAME_MODEL$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$NAME_TABLE$}}',
            $this->serviceGenerator->tableNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$CONST_NAME_MODEL$}}',
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $templateData,
        );
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
            'items' => $this->serviceGenerator->generateRules($fields, $model),
        ], $templateData);
        $templateData = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['uses']['form']['item'],
            $this->generateItems($fields, $model),
            2,
            $templateData,
            2,
        );
        $templateData = $this->serviceGenerator->importComponent($fields, $templateData, "{$path}{$this->jsType('form')}", $model);
        $this->serviceFile->createFile($path, $this->jsType('form'), $templateData);
        // import uses/index.ts
        $fileNameReal = $this->jsType('index');
        $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', $fileNameReal);
        $templateDataReal .= "export * from './$folderName';";
        $this->serviceFile->createFileReal("{$this->path}$fileNameReal", $templateDataReal);
    }
}
