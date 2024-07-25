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

    private function _generate($model): void
    {
        $pathTemplate = 'Views/';
        $templateData = $this->serviceGenerator->get_template('form', $pathTemplate, 'vue');
        $templateData = str_replace([
            '{{$NAME_MODEL$}}',
            '{{$NAME_API$}}',
            '{{$LANG_MODEL_CLASS$}}',
            '{{$CONST_MODEL_CLASS$}}',
            '{{$API_ONE$}}',
            '{{$NAME_USE$}}',
        ], [
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $this->serviceGenerator->tableNameNotPlural($model['name']),
            $this->serviceGenerator->modelNameNotPluralFe($model['name']),
            $this->serviceGenerator->modelNameNotPlural($model['name']),
        ], $templateData);
        $folderName = $this->path.$this->serviceGenerator->folderPages($model['name']);
        if (!is_dir($folderName) && !mkdir($folderName, 0755, true) && !is_dir($folderName)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $folderName));
        }

        $fileName = $this->serviceGenerator->folderPages($model['name']).'/Form.vue';
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
