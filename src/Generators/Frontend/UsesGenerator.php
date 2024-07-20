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
        // create api.ts
        $templateData = $this->serviceGenerator->get_template('api', 'Uses/', 'vue');
        $templateData = str_replace(['{{$NAME_MODEL$}}', '{{$NAME_USES$}}', '{{$VERSION$}}', '{{$NAME_ROUTE_API$}}'], [$this->serviceGenerator->modelNameNotPlural($model['name']), $this->serviceGenerator->modelNamePlural($model['name']), strtolower(config('generator.api_version')), $this->serviceGenerator->nameAttribute($model['name'])], $templateData);
        $this->serviceFile->createFile($path, $this->jsType('api'), $templateData);
        // create table.tsx
        $templateData = $this->serviceGenerator->get_template('table', 'Uses/', 'vue');
        $templateData = str_replace(['{{$NAME_USES$}}', '{{$NAME_MODEL$}}', '{{$NAME_TABLE$}}', '{{$CONST_NAME_MODEL$}}'], [$this->serviceGenerator->modelNamePlural($model['name']), $this->serviceGenerator->modelNameNotPlural($model['name']), $this->serviceGenerator->tableNameNotPlural($model['name']), $this->serviceGenerator->modelNameNotPluralFe($model['name'])], $templateData);
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
                'items' => ['updated_at'],
            ], $templateData);
        }
        $this->serviceFile->createFile($path, $this->jsType('table'), $templateData);
        // create form.tsx
        $templateData = $this->serviceGenerator->get_template('form', 'Uses/', 'vue');
        $templateData = str_replace(['{{$NAME_USES$}}', '{{$NAME_MODEL$}}', '{{$NAME_TABLE$}}', '{{$CONST_NAME_MODEL$}}'], [$this->serviceGenerator->modelNamePlural($model['name']), $this->serviceGenerator->modelNameNotPlural($model['name']), $this->serviceGenerator->tableNameNotPlural($model['name']), $this->serviceGenerator->modelNameNotPluralFe($model['name'])], $templateData);
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
        $templateDataReal .= "export * from './$folderName';";
        $this->serviceFile->createFileReal("{$this->path}$fileNameReal", $templateDataReal);
    }
}
