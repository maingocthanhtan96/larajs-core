<?php

namespace LaraJS\Core\Generators\Frontend;

use LaraJS\Core\Generators\BaseGenerator;

class ViewTableGenerator extends BaseGenerator
{
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.views');

        $this->_generate($model);
    }

    private function _generate($model)
    {
        $templateData = $this->serviceGenerator->get_template('index', 'Views/', 'vue');
        $templateData = str_replace(
            '{{$NAME_MODEL$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$TABLE_MODEL_CLASS$}}',
            $this->serviceGenerator->tableNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = str_replace(
            '{{$NAME_ROUTE$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $folderName = $this->path . $this->serviceGenerator->folderPages($model['name']);
        if (!is_dir($folderName)) {
            mkdir($folderName, 0755, true);
        }
        $fileName = "{$this->serviceGenerator->folderPages($model['name'])}/index.vue";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
