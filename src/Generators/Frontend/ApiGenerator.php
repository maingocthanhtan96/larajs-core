<?php

namespace LaraJS\Core\Generators\Frontend;

use LaraJS\Core\Generators\BaseGenerator;
use Carbon\Carbon;

class ApiGenerator extends BaseGenerator
{
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.api');

        $this->_generate($model);
    }

    private function _generate($model)
    {
        $now = Carbon::now();
        $pathTemplate = 'Api/';
        $templateData = $this->serviceGenerator->get_template('api', $pathTemplate, 'vue');
        $templateData = str_replace('{{$DATE$}}', $now->toDateTimeString(), $templateData);
        $templateData = str_replace('{{$MODEL_CLASS$}}', $model['name'], $templateData);
        $templateData = str_replace('{{$API_VERSION$}}', config('generator.api_version'), $templateData);
        $templateData = str_replace(
            '{{$MODEL_CLASS_URI$}}',
            $this->serviceGenerator->urlResource($model['name']),
            $templateData,
        );

        $fileName = $this->serviceGenerator->folderPages($model['name']) . '.ts';
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
