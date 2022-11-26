<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;
use Carbon\Carbon;

class ControllerGenerator extends BaseGenerator
{
    /**
     * ControllerGenerator constructor.
     *
     * @param $model
     */
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.api_controller');

        $this->_generate($model);
    }

    /**
     * @param $model
     */
    private function _generate($model): void
    {
        $now = Carbon::now();
        $templateData = $this->serviceGenerator->get_template('controller', 'Controllers/');
        $templateData = str_replace('{{DATE}}', $now->toDateTimeString(), $templateData);
        $templateData = str_replace('{{CONTROLLER_CLASS}}', $model['name'], $templateData);
        $templateData = str_replace('{{MODAL_CLASS}}', $model['name'], $templateData);
        $templateData = str_replace(
            '{{MODAL_CLASS_PARAM}}',
            \Str::camel(\Str::singular($model['name'])),
            $templateData,
        );
        $templateData = str_replace(
            '{{MODAL_CLASS_PARAM_LIST}}',
            \Str::camel(\Str::plural($model['name'])),
            $templateData,
        );

        $fileName = $model['name'] . 'Controller.php';
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
