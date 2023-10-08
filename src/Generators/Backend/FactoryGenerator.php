<?php

namespace LaraJS\Core\Generators\Backend;

use Carbon\Carbon;
use LaraJS\Core\Generators\BaseGenerator;

class FactoryGenerator extends BaseGenerator
{
    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.factory');

        $this->_generate($fields, $model);
    }

    private function _generate($fields, $model): void
    {
        $templateData = $this->serviceGenerator->get_template('factory', 'Databases/Factories/');
        $templateData = str_replace('{{TABLE_NAME_TITLE}}', $model['name'], $templateData);
        $templateData = $this->phpParserService->addFakerToFactory($templateData, $fields, $this->serviceGenerator->getOptions(config('generator.model.options.user_signature'), $model['options']));

        $fileName = "{$model['name']}Factory.php";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
