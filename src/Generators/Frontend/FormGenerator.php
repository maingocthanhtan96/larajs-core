<?php

namespace LaraJS\Core\Generators\Frontend;

use LaraJS\Core\Generators\BaseGenerator;

class FormGenerator extends BaseGenerator
{
    protected array $dbType;

    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.views');
        $this->dbType = config('generator.db_type');
        $this->notDelete = config('generator.not_delete.vue.form');

        $this->_generate($model);
    }

    private function _generate($model)
    {
        $pathTemplate = 'Views/';
        $templateData = $this->serviceGenerator->get_template('form', $pathTemplate, 'vue');
        $templateData = str_replace(
            '{{$NAME_MODEL$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$NAME_USES$}}',
            $this->serviceGenerator->modelNamePlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$LANG_MODEL_CLASS$}}',
            $this->serviceGenerator->tableNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$CONST_MODEL_CLASS$}}',
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $templateData,
        );
        $folderName = $this->path.$this->serviceGenerator->folderPages($model['name']);
        if (! is_dir($folderName)) {
            mkdir($folderName, 0755, true);
        }

        $fileName = $this->serviceGenerator->folderPages($model['name']).'/Form.vue';
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
