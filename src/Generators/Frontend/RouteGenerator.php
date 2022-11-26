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
        if ($this->serviceGenerator->getOptions(config('generator.model.options.role_admin'), $model['options'])) {
            $templateData = $this->serviceGenerator->get_template('routeAdmin', $pathTemplate, 'vue');
        } else {
            $templateData = $this->serviceGenerator->get_template('route', $pathTemplate, 'vue');
        }

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

        $templateDataReal = $this->serviceGenerator->getFile('router', 'vue', 'index.ts');
        if ($this->serviceGenerator->getOptions(config('generator.model.options.role_admin'), $model['options'])) {
            $templateData = str_replace('{{$ADMIN_ROLE$}}', 'roles: [superAdmin],', $templateData);
        } else {
            $namePermission = strtoupper(\Str::snake($model['name']));
            $viewMenu = config('generator.permission.view_menu');
            $templateData = str_replace(
                '{{$ADMIN_ROLE$}}',
                "permissions: ['{$viewMenu}_$namePermission'],",
                $templateData,
            );
        }
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
            "import $nameModel from './modules/$nameModelImport';",
            0,
            $templateDataReal,
        );
        $fileName = "{$this->serviceGenerator->folderPages($model['name'])}.ts";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
        $pathReal = config('generator.path.vue.router') . 'index.ts';
        $this->serviceFile->createFileReal($pathReal, $templateDataReal);
    }
}
