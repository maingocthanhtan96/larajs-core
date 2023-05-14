<?php

namespace LaraJS\Core\Generators\BackendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class TestsUpdateGenerator extends BaseGenerator
{
    public function __construct($model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.tests.feature');
        $this->notDelete = config('generator.not_delete.laravel.db');

        $this->_generate($model, $updateFields);
    }

    private function _generate($model, $updateFields)
    {
        $fileName = $model['name'].'Test.php';
        $templateDataReal = $this->serviceGenerator->getFile('tests.feature', 'laravel', $fileName);
        if ($updateFields['updateFields']) {
            $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                $this->notDelete['seeder'],
                $this->_generateFieldsUpdate($updateFields['updateFields']),
                4,
                $templateDataReal,
            );
        }
        $this->serviceFile->createFileReal($this->path.$fileName, $templateDataReal);
    }

    private function _generateFieldsUpdate($updateFields): string
    {
        $fieldsGenerate = [];
        foreach ($updateFields as $update) {
            $fieldsGenerate[] = $this->switchDbType($update);
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 4), $fieldsGenerate);
    }

    private function switchDbType($change): string
    {
        return $this->serviceGenerator->seederField($change);
    }
}
