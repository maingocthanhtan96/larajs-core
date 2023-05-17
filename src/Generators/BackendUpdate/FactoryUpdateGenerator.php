<?php

namespace LaraJS\Core\Generators\BackendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class FactoryUpdateGenerator extends BaseGenerator
{
    protected array $dbType;

    public function __construct($model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.factory');
        $this->notDelete = config('generator.not_delete.laravel.db');
        $this->dbType = config('generator.db_type');

        $this->_generate($model, $updateFields);
    }

    private function _generate($model, $updateFields)
    {
        $fileName = $model['name'].'Factory.php';
        $templateDataReal = $this->serviceGenerator->getFile('factory', 'laravel', $fileName);
        $templateDataReal = $this->phpParserService->addFakerToFactory($templateDataReal, $updateFields['updateFields']);

        $this->serviceFile->createFileReal($this->path.$fileName, $templateDataReal);
    }
}
