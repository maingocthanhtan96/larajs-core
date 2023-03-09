<?php

namespace LaraJS\Core\Generators\BackendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class FactoryUpdateGenerator extends BaseGenerator
{
    protected array $dbType;

    public function __construct($generator, $model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.factory');
        $this->notDelete = config('generator.not_delete.laravel.db');
        $this->dbType = config('generator.db_type');

        $this->_generate($generator, $model, $updateFields);
    }

    private function _generate($generator, $model, $updateFields)
    {
        $fileName = $model['name'].'Factory.php';
        $templateDataReal = $this->serviceGenerator->getFile('factory', 'laravel', $fileName);
        $templateDataReal = $this->_generateRenameFields($updateFields['renameFields'], $templateDataReal);
        $templateDataReal = $this->_generateChangeFields($updateFields['changeFields'], $generator, $templateDataReal);
        $templateDataReal = $this->_generateFieldsDrop($updateFields['dropFields'], $templateDataReal);
        if ($updateFields['updateFields']) {
            $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                $this->notDelete['seeder'],
                $this->_generateFieldsUpdate($updateFields['updateFields']),
                3,
                $templateDataReal,
            );
        }
        $this->serviceFile->createFileReal($this->path.$fileName, $templateDataReal);
    }

    private function _generateRenameFields($renameFields, $templateDataReal)
    {
        foreach ($renameFields as $rename) {
            $templateDataReal = str_replace(
                "'{$rename['field_name_old']['field_name']}'",
                "'{$rename['field_name_new']['field_name']}'",
                $templateDataReal,
            );
        }

        return $templateDataReal;
    }

    private function _generateChangeFields($changeFields, $generator, $templateDataReal)
    {
        $formFields = json_decode($generator->field, true);
        foreach ($changeFields as $change) {
            foreach ($formFields as $index => $oldField) {
                if ($index > 0 && $oldField['id'] === $change['id']) {
                    $templateDataReal = str_replace(
                        $this->switchDbType($oldField),
                        $this->switchDbType($change),
                        $templateDataReal,
                    );
                }
            }
        }

        return $templateDataReal;
    }

    private function _generateFieldsDrop($dropFields, $templateDataReal)
    {
        foreach ($dropFields as $drop) {
            $templateDataReal = str_replace($this->switchDbType($drop), '', $templateDataReal);
        }

        return $templateDataReal;
    }

    private function _generateFieldsUpdate($updateFields): string
    {
        $fieldsGenerate = [];
        foreach ($updateFields as $update) {
            $fieldsGenerate[] = $this->switchDbType($update);
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 3), $fieldsGenerate);
    }

    private function switchDbType($change): string
    {
        return $this->serviceGenerator->seederField($change);
    }
}
