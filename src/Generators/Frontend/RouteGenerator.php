<?php

namespace LaraJS\Core\Generators\Frontend;

use LaraJS\Core\Generators\BaseGenerator;
use Carbon\Carbon;

class RouteGenerator extends BaseGenerator
{
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.router_modules');
        $this->notDelete = config('generator.not_delete.vue.route');

        $this->_generate($model);
    }

    private function _generate($model)
    {
        $now = Carbon::now();
        $pathTemplate = 'Router/';
        $templateData = $this->serviceGenerator->get_template('route', $pathTemplate, 'vue');

        $templateData = str_replace('{{$DATE$}}', $now->toDateTimeString(), $templateData);
        $templateData = str_replace(
            '{{$NAME_CONST$}}',
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$NAME_ROUTE_MODEL_CLASS$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$MODEL_CLASS$}}',
            $this->serviceGenerator->nameAttribute($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$PATH_ROUTE_MODEL_CLASS$}}',
            $this->serviceGenerator->urlResource($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$TITLE_ROUTE_MODEL_CLASS$}}',
            $this->serviceGenerator->tableNameNotPlural($model['name']),
            $templateData,
        );

        $templateDataReal = $this->serviceGenerator->getFile('router', 'vue', $this->jsType('index'));
        $namePermission = strtoupper(\Str::snake($model['name']));
        $viewMenu = config('generator.permission.view_menu');
        $templateData = str_replace(
            '{{$ADMIN_ROLE$}}',
            "permissions: ['{$viewMenu}_$namePermission'],",
            $templateData,
        );
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['async'],
            "{$this->serviceGenerator->modelNameNotPluralFe($model['name'])},",
            3,
            $templateDataReal,
            2,
        );
        $nameModel = $this->serviceGenerator->modelNameNotPluralFe($model['name']);
        $nameModelImport = $this->serviceGenerator->nameAttribute($model['name']);
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['import'],
            "import $nameModel from '{$this->getImportJsOrTs()}/router/modules/$nameModelImport';",
            0,
            $templateDataReal,
        );
        $fileName = "{$this->serviceGenerator->folderPages($model['name'])}.{$this->jsType('ext')}";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
        $pathReal = config('generator.path.vue.router') . $this->jsType('index');
        $this->serviceFile->createFileReal($pathReal, $templateDataReal);
    }
}
