<?php

namespace LaraJS\Core\Generators\FrontendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class InterfaceCommonUpdateGenerator extends BaseGenerator
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
        $fileName = $this->serviceGenerator->folderPages($model['name']).'.ts';
        $templateDataReal = $this->serviceGenerator->getFile('model', 'package', $fileName);
        $templateDataReal = $this->_generateFieldsUpdate($fields['updateFields'], $fileName, $model, $templateDataReal);
        $this->serviceFile->createFileReal($this->path.$fileName, $templateDataReal);
    }

    private function _generateFieldsUpdate($fields, $fileName, $model, $templateDataReal): string
    {
        return $this->phpParserService->runParserJS(
            $this->path.$fileName,
            [
                'key' => 'common.import',
                'interface' => $this->serviceGenerator->modelNameNotPlural($model['name']),
                'items' => $this->serviceGenerator->generateModel($fields),
            ],
            $templateDataReal
        );
    }
}
