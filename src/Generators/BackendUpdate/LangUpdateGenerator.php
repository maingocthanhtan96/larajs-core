<?php

namespace LaraJS\Core\Generators\BackendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class LangUpdateGenerator extends BaseGenerator
{
    public function __construct($model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.lang');
        $this->notDelete = config('generator.not_delete.laravel.model');

        $this->generate($model, $updateFields);
    }

    private function generate($model, $updateFields)
    {
        $tableName = $this->serviceGenerator->tableNameNotPlural($model['name']);

        $nameLang = ['table'];
        $lang = config('generator.not_delete.laravel.lang');
        foreach ($lang as $key => $langComment) {
            foreach ($nameLang as $lang) {
                $templateDataReal = $this->serviceGenerator->getFile('lang', 'laravel', $key.'/table.php');
//                $templateDataReal = $this->_generateFieldsRename(
//                    $tableName,
//                    $updateFields['renameFields'],
//                    $templateDataReal,
//                );
//                $templateDataReal = $this->_generateFieldsDrop(
//                    $tableName,
//                    $updateFields['dropFields'],
//                    $templateDataReal,
//                );
                $templateDataReal = $this->_generateFieldsUpdate(
                    $tableName,
                    $updateFields['updateFields'],
                    $templateDataReal,
                );
                $this->serviceFile->createFileReal("{$this->path}$key/$lang.php", $templateDataReal);
            }
        }
    }

    private function _generateFieldsRename($tableName, $renameFields, $templateDataReal): string
    {
        if (! $renameFields) {
            return $templateDataReal;
        }

        $langTemplate = $this->serviceGenerator->langTemplate($tableName, $templateDataReal);
        $template = $langTemplate['template'];
        $templateReplace = $langTemplate['template_replace'];
        if (! $template || ! $templateReplace) {
            return $templateDataReal;
        }

        $arTemplate = explode(',', trim($template));
        $arRename = \Arr::pluck($renameFields, 'field_name_new.field_name');
        $arRenameOld = \Arr::pluck($renameFields, 'field_name_old.field_name');
        $fieldsGenerate = [];
        $fieldsGenerate[] = " '$tableName' => [";
        foreach ($renameFields as $rename) {
            foreach ($arTemplate as $tpl) {
                if (strlen($tpl) > 0) {
                    [$fieldName, $fieldNameTrans] = explode('=>', $tpl);
                    $fieldName = trim($fieldName);
                    $fieldNameTrans = trim($fieldNameTrans);
                    $fieldName = $this->serviceGenerator->trimQuotes($fieldName);
                    $fieldNameTrans = $this->serviceGenerator->trimQuotes($fieldNameTrans);
                    if ($rename['field_name_old']['field_name'] === $fieldName) {
                        $fieldsGenerate[] =
                            "'".$rename['field_name_new']['field_name']."'".' => '."'".$fieldNameTrans."',";
                    } else {
                        $name = "'$fieldName' => '$fieldNameTrans',";
                        if (
                            ! in_array($name, $fieldsGenerate) &&
                            ! in_array($fieldName, $arRename) &&
                            ! in_array($fieldName, $arRenameOld)
                        ) {
                            $fieldsGenerate[] = $name;
                        }
                    }
                }
            }
        }
        $replace = implode($this->serviceGenerator->infy_nl_tab(1, 2), $fieldsGenerate);
        $replace .= "\n\t],";

        return $this->_replaceTemplate($templateReplace, $replace, $templateDataReal);
    }

    private function _generateFieldsUpdate($tableName, $updateFields, $templateDataReal): string
    {
        if (! $updateFields) {
            return $templateDataReal;
        }
        foreach ($updateFields as $update) {
            $templateDataReal = $this->phpParserService->addItemToArray($templateDataReal, $tableName, $update['field_name'], $update['field_name_trans']);
        }

        return $templateDataReal;
    }

    private function _generateFieldsDrop($tableName, $dropUpdate, $templateDataReal): string
    {
        if (! $dropUpdate) {
            return $templateDataReal;
        }

        $langTemplate = $this->serviceGenerator->langTemplate($tableName, $templateDataReal);
        $template = $langTemplate['template'];
        $templateReplace = $langTemplate['template_replace'];
        if (! $template || ! $templateReplace) {
            return $templateDataReal;
        }
        $arTemplate = explode(',', trim($template));
        $dropUpdate = \Arr::pluck($dropUpdate, 'field_name');
        $fieldsGenerate = [];
        $fieldsGenerate[] = " '$tableName' => [";
        foreach ($arTemplate as $tpl) {
            if (strlen($tpl) > 0) {
                [$fieldName, $fieldNameTrans] = explode('=>', $tpl);
                $fieldName = trim($fieldName);
                $fieldNameTrans = trim($fieldNameTrans);
                $fieldName = $this->serviceGenerator->trimQuotes($fieldName);
                $fieldNameTrans = $this->serviceGenerator->trimQuotes($fieldNameTrans);
                $name = "'$fieldName' => '$fieldNameTrans',";
                if (! in_array($fieldName, $dropUpdate) && ! in_array($name, $fieldsGenerate)) {
                    $fieldsGenerate[] = $name;
                }
            }
        }
        $replace = implode($this->serviceGenerator->infy_nl_tab(1, 2), $fieldsGenerate);
        $replace .= "\n\t],";

        return $this->_replaceTemplate($templateReplace, $replace, $templateDataReal);
    }

    private function _replaceTemplate($template, $replace, $templateDataReal): string
    {
        return str_replace($template, $replace, $templateDataReal);
    }
}
