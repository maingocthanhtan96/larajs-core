<?php

namespace LaraJS\Core\Generators\Frontend;

use Carbon\Carbon;
use LaraJS\Core\Generators\BaseGenerator;

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

        $templateData = str_replace(['{{$DATE$}}', '{{$NAME_CONST$}}', '{{$NAME_ROUTE_MODEL_CLASS$}}', '{{$MODEL_CLASS$}}', '{{$PATH_ROUTE_MODEL_CLASS$}}', '{{$TITLE_ROUTE_MODEL_CLASS$}}'], [$now->toDateTimeString(), $this->serviceGenerator->modelNameNotPluralFe($model['name']), $this->serviceGenerator->modelNameNotPlural($model['name']), $this->serviceGenerator->nameAttribute($model['name']), $this->serviceGenerator->urlResource($model['name']), $this->serviceGenerator->tableNameNotPlural($model['name'])], $templateData);
        $fileName = "{$this->serviceGenerator->folderPages($model['name'])}.{$this->getType('ext')}";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);

        $pathReal = config('generator.path.vue.router').$this->getType('index');
        $templateDataReal = $this->phpParserService->runParserJS($pathReal, [
            'key' => 'router.import',
            'name' => $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            'path' => "{$this->getImport()}/router/modules/{$this->serviceGenerator->nameAttribute($model['name'])}",
        ]);
        $this->serviceFile->createFileReal($pathReal, $templateDataReal);
    }
}
