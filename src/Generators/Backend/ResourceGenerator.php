<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class ResourceGenerator extends BaseGenerator
{
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.resources');

        $this->_generate($model);
    }

    private function _generate($model): void
    {
        $templateData = $this->serviceGenerator->get_template('resource', 'Resources/');
        $templateData = str_replace('{{VERSION}}', config('generator.api_version'), $templateData);
        $templateData = str_replace('{{MODEL_CLASS}}', $model['name'], $templateData);
        $fileName = "{$model['name']}Resource.php";

        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
