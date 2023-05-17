<?php

namespace LaraJS\Core\Generators\BackendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class ModelUpdateGenerator extends BaseGenerator
{
    public function __construct($model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.model');
        $this->notDelete = config('generator.not_delete.laravel.model');

        $this->_generate($updateFields, $model);
    }

    private function _generate($updateFields, $model)
    {
        $templateDataReal = $this->serviceGenerator->getFile('model', 'laravel', $model['name'].'.php');
        $templateDataReal = $this->_generateUpdateFields($updateFields['updateFields'], $templateDataReal);

        $fileName = $this->path.$model['name'].'.php';
        $this->serviceFile->createFileReal($fileName, $templateDataReal);
    }

    private function _generateUpdateFields($updateFields, $templateDataReal): string
    {
        if (!$updateFields) {
            return $templateDataReal;
        }

        foreach ($updateFields as $field) {
            $templateDataReal = $this->phpParserService->addStringToArray($templateDataReal, $field['field_name'], 'fillable');
        }

        return $templateDataReal;
    }
}
