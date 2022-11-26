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
        $fileName = $this->serviceGenerator->folderPages($model['name']) . '.ts';
        $templateDataReal = $this->serviceGenerator->getFile('model', 'package', $fileName);
        $templateDataReal = $this->_generateFieldsUpdate($fields['updateFields'], $templateDataReal);
        $this->serviceFile->createFileReal($this->path . $fileName, $templateDataReal);
    }

    private function _generateFieldsUpdate($fields, $templateDataReal): string
    {
        return $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['index'],
            implode($this->serviceGenerator->infy_nl_tab(1, 1, 2), $this->serviceGenerator->generateModel($fields)),
            1,
            $templateDataReal,
            2,
        );
    }
}
