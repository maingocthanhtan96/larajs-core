<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class ControllerGenerator extends BaseGenerator
{
    /**
     * ControllerGenerator constructor.
     */
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.api_controller');

        $this->_generate($model);
    }

    private function _generate($model): void
    {
        $templateData = $this->serviceGenerator->get_template('controller', 'Controllers/');
        $templateData = str_replace('{{VERSION}}', config('generator.api_version'), $templateData);
        $templateData = str_replace('{{CONTROLLER_CLASS}}', $model['name'], $templateData);
        $templateData = str_replace('{{MODEL_CLASS}}', $model['name'], $templateData);
        $templateData = str_replace(
            '{{MODEL_CLASS_PARAM}}',
            \Str::camel(\Str::singular($model['name'])),
            $templateData,
        );
        $templateData = str_replace(
            '{{MODEL_CLASS_PARAM_LIST}}',
            \Str::camel(\Str::plural($model['name'])),
            $templateData,
        );

        $fileName = $model['name'].'Controller.php';
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
