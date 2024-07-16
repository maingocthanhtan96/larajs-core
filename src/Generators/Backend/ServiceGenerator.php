<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class ServiceGenerator extends BaseGenerator
{
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.service');

        $this->_generate($model);
    }

    private function _generate($model)
    {
        $createFolderModel = '/'.$model['name'].'/';
        $pathTemplate = 'Services/';
        //template Repository
        $templateDataService = $this->serviceGenerator->get_template('Service', $pathTemplate);
        $templateDataService = str_replace(['{{VERSION}}', '{{MODEL_CLASS}}', '{{MODEL_CLASS_PARAM}}'], [config('generator.api_version'), $model['name'], $this->serviceGenerator->modelNameNotPluralFe($model['name'])], $templateDataService);
        $fileNameRepository = $model['name'].'Service.php';
        $this->serviceFile->createFile($this->path.$createFolderModel, $fileNameRepository, $templateDataService);
    }
}
