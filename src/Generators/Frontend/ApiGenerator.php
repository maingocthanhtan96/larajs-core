<?php

namespace LaraJS\Core\Generators\Frontend;

use Carbon\Carbon;
use LaraJS\Core\Generators\BaseGenerator;

class ApiGenerator extends BaseGenerator
{
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.api');
        $this->notDelete = config('generator.not_delete.vue');

        $this->_generate($model);
    }

    private function _generate($model)
    {
        $now = Carbon::now();
        $pathTemplate = 'Api/';
        $templateData = $this->serviceGenerator->get_template('api', $pathTemplate, 'vue');
        $templateData = str_replace(['{{$DATE$}}', '{{$MODEL_CLASS$}}', '{{$VERSION$}}', '{{$MODEL_CLASS_URI$}}'], [$now->toDateTimeString(), $model['name'], strtolower(config('generator.api_version')), $this->serviceGenerator->urlResource($model['name'])], $templateData);

        $fileName = $this->serviceGenerator->folderPages($model['name']).".{$this->jsType('ext')}";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
        $fileNameReal = $this->jsType('index');
        $pathApi = "{$this->path}$fileNameReal";
        if (!file_exists($pathApi)) {
            file_put_contents($pathApi, '');
        }
        $templateDataReal = $this->serviceGenerator->getFile('api', 'vue', $fileNameReal);
        $templateDataReal .= "export { default as {$model['name']}Resource } from './{$this->serviceGenerator->folderPages($model['name'])}';";
        $this->serviceFile->createFileReal($pathApi, $templateDataReal);
    }
}
