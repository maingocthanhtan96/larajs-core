<?php

namespace LaraJS\Core\Generators\Frontend;

use LaraJS\Core\Generators\BaseGenerator;

class InterfaceCommonGenerator extends BaseGenerator
{
    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.package.model');
        $this->_generate($fields, $model);
    }

    private function _generate($fields, $model)
    {
        $fileName = $this->serviceGenerator->folderPages($model['name']).".{$this->jsType('ext')}";
        $templateData = $this->serviceGenerator->get_template('model', 'Common/', 'package');
        $templateData = str_replace(
            '{{$MODEL$}}',
            $this->serviceGenerator->modelNameNotPlural($model['name']),
            $templateData,
        );
        $templateData = $this->phpParserService->runParserJS(
            $this->path.$fileName,
            [
                'key' => 'common.import',
                'interface' => $this->serviceGenerator->modelNameNotPlural($model['name']),
                'items' => $this->serviceGenerator->generateModel($fields),
            ],
            $templateData
        );
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
        // import
        $fileName = "/{$this->jsType('index')}";
        $templateDataReal = $this->serviceGenerator->getFile('model', 'package', $fileName);
        $fileImport = "'./{$this->serviceGenerator->folderPages($model['name'])}'";
        if (!stripos($templateDataReal, $fileImport)) {
            $templateDataReal .= "export * from $fileImport;";
            $this->serviceFile->createFileReal($this->path.$fileName, $templateDataReal);
        }
    }
}
