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
        $templateData = str_replace(['{{MODEL}}', '{{FIELDS}}', '{{MODEL_TEST}}', '{{ROUTE_RESOURCE}}', '{{MODEL_VARIABLE}}', '{{MODEL_SINGULAR}}'], [$model['name'], $this->generateFields($fields), $this->serviceGenerator->modelNameTitle($model['name']), $this->serviceGenerator->urlResource($model['name']), $this->serviceGenerator->modelNameSingular($model['name']), $this->serviceGenerator->modelNameRouteParamSingular($model['name'])], $templateData);
        $this->serviceFile->createFile($this->path, $model['name'].'Test.php', $templateData);
    }
}
