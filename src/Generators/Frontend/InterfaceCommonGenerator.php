<?php

namespace LaraJS\Core\Generators\Frontend;

use LaraJS\Core\Generators\BaseGenerator;

class InterfaceCommonGenerator extends BaseGenerator
{
    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.package.model');
        $this->notDelete = config('generator.not_delete.package.model');
        $this->_generate($fields, $model);
    }

    private function _generate($fields, $model)
    {
        $templateData = $this->serviceGenerator->get_template('model', 'Common/', 'package');
        $templateData = str_replace(
            '{{$MODEL$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['index'],
            implode($this->serviceGenerator->infy_nl_tab(1, 1, 2), $this->serviceGenerator->generateModel($fields)),
            1,
            $templateData,
            2,
        );

        $fileName = $this->serviceGenerator->folderPages($model['name']).".{$this->jsType('ext')}";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
        // import
        $fileName = "/{$this->jsType('index')}";
        $templateDataReal = $this->serviceGenerator->getFile('model', 'package', $fileName);
        $fileImport = "'./{$this->serviceGenerator->folderPages($model['name'])}'";
        if (! stripos($templateDataReal, $fileImport)) {
            $templateDataReal .= "export * from $fileImport;";
            $this->serviceFile->createFileReal($this->path.$fileName, $templateDataReal);
        }
    }
}
