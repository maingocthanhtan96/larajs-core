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
        //        $templateDataReal = $this->_generateFieldsRename($updateFields['renameFields'], $templateDataReal);
        //        $templateDataReal = $this->_generateFieldsDrop($updateFields['dropFields'], $templateDataReal);

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

    private function _generateFieldsRename($renameFields, $templateDataReal)
    {
        foreach ($renameFields as $rename) {
            $templateDataReal = str_replace(
                "'{$rename['field_name_old']['field_name']}',",
                "'{$rename['field_name_new']['field_name']}',",
                $templateDataReal,
            );
        }

        return $templateDataReal;
    }

    private function _generateFieldsDrop($dropFields, $templateDataReal)
    {
        foreach ($dropFields as $drop) {
            $templateDataReal = str_replace("'{$drop['field_name']}',", '', $templateDataReal);
            $templateDataReal = str_replace("'{$drop['field_name']}' => 'string',", '', $templateDataReal);
        }

        return $templateDataReal;
    }

    private function _generateYear($updateFields): string
    {
        $fieldsGenerate = [];
        $dbType = config('generator.db_type');
        foreach ($updateFields['updateFields'] as $field) {
            if ($field['db_type'] === $dbType['year']) {
                $fieldsGenerate[] = "'{$field['field_name']}' => 'string',";
            }
        }
        foreach ($updateFields['changeFields'] as $change) {
            if ($change['db_type'] === $dbType['year']) {
                $fieldsGenerate[] = "'{$change['field_name']}' => 'string',";
            }
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 2), $fieldsGenerate);
    }
}
