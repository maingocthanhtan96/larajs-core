<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class LangGenerator extends BaseGenerator
{
    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.lang');

        $this->_generate($fields, $model);
    }

    private function _generate($fields, $model)
    {
        $pathTemplate = 'Lang/';

        $nameLang = ['route', 'table'];
        $lang = config('generator.not_delete.laravel.lang');
        foreach ($lang as $key => $langComment) {
            foreach ($nameLang as $lang) {
                $templateData = $this->serviceGenerator->get_template($key . '/' . $lang, $pathTemplate);
                if ($lang === 'table') {
                    $templateData = str_replace('{{FIELDS}}', $this->_generateTableFields($fields), $templateData);
                }
                $templateData = str_replace(
                    '{{LANG_MODEL_CLASS}}',
                    $this->serviceGenerator->tableNameNotPlural($model['name']),
                    $templateData,
                );
                $templateData = str_replace('{{LANG_MODEL_TRANS_CLASS}}', $model['name_trans'], $templateData);

                $templateDataReal = $this->serviceGenerator->getFile('lang', 'laravel', $key . '/' . $lang . '.php');

                $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                    $langComment[$lang],
                    $templateData,
                    1,
                    $templateDataReal,
                );
                $this->serviceFile->createFileReal("$this->path/$key/$lang.php", $templateDataReal);
            }
        }
    }

    private function _generateTableFields($fields): string
    {
        $fieldsGenerate = [];
        foreach ($fields as $field) {
            $fieldsGenerate[] = "'" . $field['field_name'] . "'" . ' => ' . "'" . $field['field_name_trans'] . "',";
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 2), $fieldsGenerate);
    }
}
