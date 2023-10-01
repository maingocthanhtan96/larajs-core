<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class RequestGenerator extends BaseGenerator
{
    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.request');
        $this->notDelete = config('generator.not_delete.laravel.request');

        $this->_generate($fields, $model);
    }

    private function _generate($fields, $model)
    {
        $pathTemplate = 'Requests/';
        $templateData = $this->serviceGenerator->get_template('store', $pathTemplate);
        $templateData = str_replace('{{VERSION}}', config('generator.api_version'), $templateData);
        $templateData = str_replace('{{MODEL_CLASS}}', $model['name'], $templateData);
        $templateData = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['rule'],
            $this->_generateFields($fields),
            3,
            $templateData,
        );
        //create sort delete
        $fileName = "Store{$model['name']}Request.php";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }

    private function _generateFields($fields): string
    {
        $fieldsGenerate = [];
        foreach ($fields as $index => $field) {
            if ($index > 0) {
                $fieldsGenerate[] = $this->serviceGenerator->requestField($field);
            }
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 3), $fieldsGenerate);
    }
}
