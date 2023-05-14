<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class TestsGenerator extends BaseGenerator
{
    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.tests.feature');

        $this->_generate($fields, $model);
    }

    public function generateFields($fields): string
    {
        $fieldsGenerate = [];
        foreach ($fields as $index => $field) {
            if ($index > 0) {
                $fieldsGenerate[] = $this->serviceGenerator->seederField($field);
            }
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 4), $fieldsGenerate);
    }

    private function _generate($fields, $model)
    {
        //template Repository
        $templateData = $this->serviceGenerator->get_template('Feature', 'Tests/');
        $templateData = str_replace('{{MODEL}}', $model['name'], $templateData);
        $templateData = str_replace('{{FIELDS}}', $this->generateFields($fields), $templateData);
        $templateData = str_replace('{{MODEL_TEST}}', $this->serviceGenerator->modelNameTitle($model['name']), $templateData);
        $templateData = str_replace('{{ROUTE_RESOURCE}}', $this->serviceGenerator->urlResource($model['name']), $templateData);
        $templateData = str_replace('{{MODEL_VARIABLE}}', $this->serviceGenerator->modelNameSingular($model['name']), $templateData);
        $templateData = str_replace('{{MODEL_SINGULAR}}', $this->serviceGenerator->modelNameRouteParamSingular($model['name']), $templateData);
        $this->serviceFile->createFile($this->path, $model['name'].'Test.php', $templateData);
    }
}
