<?php

namespace LaraJS\Core\Generators\FrontendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class UsesUpdateGenerator extends BaseGenerator
{
    public function __construct($model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.uses');
        $this->dbType = config('generator.db_type');
        $this->notDelete = config('generator.not_delete.vue');
        $this->defaultValue = config('generator.default_value');

        $this->_generate($model, $updateFields);
    }

    private function _generate($model, $updateFields)
    {
        $folderName = $this->serviceGenerator->folderPages($model['name']);
        $path = "$this->path{$folderName}/";
        // create table.tsx
        $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', "/$folderName/table.tsx");
        $templateDataReal = $this->phpParserService->runParserJS("$path{$this->jsType('table')}", [
            'key' => 'uses.table:columns',
            'items' => $this->serviceGenerator->generateColumns($updateFields['updateFields'], $model, true),
        ], $templateDataReal);
        $templateDataReal = $this->phpParserService->runParserJS("$path/table.tsx", [
            'key' => 'uses.table:search:column',
            'items' => $this->serviceGenerator->generateColumnSearch($updateFields['updateFields']),
        ], $templateDataReal);
        $this->serviceFile->createFileReal("$path/table.tsx", $templateDataReal);
        // create form.tsx
        $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', "/$folderName/form.tsx");
        $templateDataReal = $this->phpParserService->runParserJS("{$path}{$this->jsType('form')}", [
            'key' => 'uses.form:item',
            'variable' => 'form',
            'items' => $this->serviceGenerator->generateFieldForm($updateFields['updateFields']),
        ], $templateDataReal);
        $templateDataReal = $this->phpParserService->runParserJS("{$path}{$this->jsType('form')}", [
            'key' => 'uses.form:rules',
            'items' => $this->serviceGenerator->generateRules($updateFields['updateFields'], $model),
        ], $templateDataReal);
        $templateDataReal = $this->phpParserService->runParserJS("{$path}{$this->jsType('form')}", [
            'key' => 'uses.form:items',
            'items' => $this->generateItems($updateFields['updateFields'], $model),
        ], $templateDataReal);
        $templateDataReal = $this->serviceGenerator->importComponent($updateFields['updateFields'], $templateDataReal, "{$path}{$this->jsType('form')}", $model);

        $this->serviceFile->createFileReal("{$path}{$this->jsType('form')}", $templateDataReal);
    }
}
