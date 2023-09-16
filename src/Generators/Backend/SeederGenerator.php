<?php

namespace LaraJS\Core\Generators\Backend;

use Carbon\Carbon;
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
        $now = Carbon::now();
        $templateData = $this->serviceGenerator->get_template('seeder', 'Databases/Seeders/');
        $templateData = str_replace('{{DATE_TIME}}', $now->toDateTimeString(), $templateData);
        $templateData = str_replace('{{TABLE_NAME_TITLE}}', $model['name'], $templateData);
        $templateData = str_replace('{{MODEL_CLASS}}', $model['name'], $templateData);
        $fileName = "{$model['name']}Seeder.php";

        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }
}
