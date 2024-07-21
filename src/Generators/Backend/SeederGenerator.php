<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;

class SeederGenerator extends BaseGenerator
{
    public function __construct($model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.seeder');

        $this->_generate($model);
    }

    private function _generate($model): void
    {
        $templateData = $this->serviceGenerator->get_template('seeder', 'Databases/Seeders/');
        $templateData = str_replace(['{{TABLE_NAME_TITLE}}', '{{MODEL_CLASS}}'], [$model['name'], $model['name']], $templateData);
        $fileName = "{$model['name']}Seeder.php";

        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
