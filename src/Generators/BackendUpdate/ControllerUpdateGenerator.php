<?php

namespace LaraJS\Core\Generators\BackendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class ControllerUpdateGenerator extends BaseGenerator
{
    public const QS_COLUMNS_SEARCH = "'columnSearch' =>";

    public function __construct($model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.api_controller');

        $this->_generate($model, $updateFields);
    }

    private function _generate($model, $updateFields)
    {
        $fileName = $model['name'] . 'Controller.php';
        $templateDataReal = $this->serviceGenerator->getFile('api_controller', 'laravel', $fileName);
        $templateDataReal = $this->_generateFieldsRename($updateFields['renameFields'], $templateDataReal);
        $templateDataReal = $this->_generateFieldsChange($updateFields['changeFields'], $templateDataReal);
        $templateDataReal = $this->_generateFieldsDrop($updateFields['dropFields'], $templateDataReal);
        $templateDataReal = $this->_generateFieldsUpdate($updateFields['updateFields'], $templateDataReal);
        $fileName = $this->path . $fileName;
        $this->serviceFile->createFileReal($fileName, $templateDataReal);
    }

    private function _generateFieldsRename($renameFields, $templateDataReal)
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

    private function _generateFieldsDrop($dropFields, $templateDataReal)
    {
        foreach ($dropFields as $drop) {
            $templateDataReal = $this->_checkComma("'{$drop['field_name']}',", $drop['field_name'], $templateDataReal);
        }

        return $templateDataReal;
    }

    private function _generateFieldsChange($changeFields, $templateDataReal): string
    {
        return $this->_changeUpdateFields($changeFields, $templateDataReal);
    }

    private function _checkComma($name, $drop, $templateDataReal): string
    {
        if (\Str::contains($templateDataReal, "$name")) {
            if (\Str::contains($templateDataReal, "$name ")) {
                $templateDataReal = str_replace("'$drop', ", '', $templateDataReal);
            } else {
                $templateDataReal = str_replace("'$drop',", '', $templateDataReal);
            }
        } else {
            $templateDataReal = str_replace("'$drop'", '', $templateDataReal);
        }

        return $templateDataReal;
    }

    private function _generateFieldsUpdate($updateFields, $templateDataReal): string
    {
        if (!$updateFields) {
            return $templateDataReal;
        }

        $templateColumnsSearch = $this->serviceGenerator->searchTemplate(
            self::QS_COLUMNS_SEARCH,
            '],',
            strlen(self::QS_COLUMNS_SEARCH) + 2,
            -strlen(self::QS_COLUMNS_SEARCH) - 2,
            $templateDataReal,
            self::QS_COLUMNS_SEARCH,
        );
        if (!$templateColumnsSearch) {
            return $templateDataReal;
        }

        $commaSearch = ', ';
        $columnsSearch = '';
        if (\Str::endsWith($templateColumnsSearch, ',') || \Str::endsWith($templateColumnsSearch, ', ')) {
            $commaSearch = '';
        }
        foreach ($updateFields as $update) {
            if ($update['search']) {
                $columnsSearch .= $commaSearch . "'{$update['field_name']}'";
            }
        }

        $selfColumnsSearch = self::QS_COLUMNS_SEARCH;

        return str_replace(
            "$selfColumnsSearch [" . $templateColumnsSearch . ']',
            "$selfColumnsSearch [" . $templateColumnsSearch . $columnsSearch . ']',
            $templateDataReal,
        );
    }

    private function _changeUpdateFields($changeFields, $templateDataReal): string
    {
        if (!$changeFields) {
            return $templateDataReal;
        }

        $fieldsGeneratorColumnSearch = [];
        $templateColumnsSearch = $this->serviceGenerator->searchTemplate(
            self::QS_COLUMNS_SEARCH,
            '],',
            strlen(self::QS_COLUMNS_SEARCH) + 2,
            -strlen(self::QS_COLUMNS_SEARCH) - 2,
            $templateDataReal,
            self::QS_COLUMNS_SEARCH,
        );
        if (!$templateColumnsSearch) {
            return $templateDataReal;
        }
        $arrayColumnsSearch = explode(',', $templateColumnsSearch);
        $arrayChange = \Arr::pluck($changeFields, 'field_name');
        foreach ($changeFields as $change) {
            foreach ($arrayColumnsSearch as $search) {
                $search = trim($search);
                $trimSort = $this->serviceGenerator->trimQuotes($search);
                if ($change['field_name'] === $trimSort) {
                    if ($change['search']) {
                        $fieldsGeneratorColumnSearch[] = "'{$change['field_name']}'";
                    }
                } else {
                    $nameTrimSort = "'$trimSort'";
                    if (
                        !in_array($nameTrimSort, $fieldsGeneratorColumnSearch) &&
                        !in_array($nameTrimSort, $arrayChange)
                    ) {
                        $fieldsGeneratorColumnSearch[] = $nameTrimSort;
                    }
                }
            }
        }

        $selfColumnsSearch = self::QS_COLUMNS_SEARCH;

        return str_replace(
            "$selfColumnsSearch [" . $templateColumnsSearch . ']',
            "$selfColumnsSearch [" .
                implode($this->serviceGenerator->infy_nl_tab(0, 0) . ', ', $fieldsGeneratorColumnSearch) .
                ']',
            $templateDataReal,
        );
    }
}
