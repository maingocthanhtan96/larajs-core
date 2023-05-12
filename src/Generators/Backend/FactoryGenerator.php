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

    public function generateFields($fields): string
    {
        $fieldsGenerate = [];
        foreach ($fields as $index => $field) {
            if ($index > 0) {
                $fieldsGenerate[] = $this->serviceGenerator->seederField($field);
            }
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 3), $fieldsGenerate);
    }

    private function _generate($fields, $model): void
    {
        $now = Carbon::now();
        $templateData = $this->serviceGenerator->get_template('factory', 'Databases/Factories/');
        $templateData = str_replace('{{DATE_TIME}}', $now->toDateTimeString(), $templateData);
        $templateData = str_replace('{{TABLE_NAME_TITLE}}', $model['name'], $templateData);
        $templateData = str_replace('{{FIELDS}}', $this->generateFields($fields), $templateData);
        if ($this->serviceGenerator->getOptions(config('generator.model.options.user_signature'), $model['options'])) {
            $templateData = $this->_generateUserSignature($templateData);
        }
        $fileName = "{$model['name']}Factory.php";

        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }

    private function _generateUserSignature($templateData): string
    {
        $userSignature = ['created_by', 'updated_by'];
        $notDelete = config('generator.not_delete.laravel.db');
        $importTrait = config('generator.import.laravel.use.trait_user_signature');

        foreach ($userSignature as $signature) {
            $templateData = $this->serviceGenerator->replaceNotDelete(
                $notDelete['seeder'],
                "'$signature'".' => ' . $importTrait['model'],
                3,
                $templateData,
            );
        }

        return $templateData;
    }
}
