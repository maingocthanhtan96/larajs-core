<?php

namespace LaraJS\Core\Generators\FrontendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class UsesUpdateGenerator extends BaseGenerator
{
    public function __construct($model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.uses');
        $this->dbType = config('generator.db_type');
        $this->notDelete = config('generator.not_delete.vue');
        $this->defaultValue = config('generator.default_value');

        $this->_generate($model, $updateFields);
    }

    private function _generate($model, $updateFields)
    {
        $folderName = $this->serviceGenerator->folderPages($model['name']);
        $path = "$this->path{$folderName}/";
        // create table.tsx
        $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', "/$folderName/table.tsx");
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['form']['column'],
            implode(
                $this->serviceGenerator->infy_nl_tab(1, 2, 2),
                $this->serviceGenerator->generateColumns($updateFields['updateFields'], $model, true),
            ),
            3,
            $templateDataReal,
            2,
        );
        $templateDataReal = $this->phpParserService->runParserJS("$path/table.tsx", [
            'key' => 'query.column_search',
            'items' => $this->serviceGenerator->generateColumnSearch($updateFields['updateFields'])
        ], $templateDataReal);
        $this->serviceFile->createFileReal("$path/table.tsx", $templateDataReal);
        // create form.tsx
        $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', "/$folderName/form.tsx");
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['form']['fields'],
            implode(
                $this->serviceGenerator->infy_nl_tab(1, 2, 2),
                $this->serviceGenerator->generateFieldForm($updateFields['updateFields']),
            ),
            2,
            $templateDataReal,
            2,
        );
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['uses']['form']['item'],
            $this->generateItems($updateFields['updateFields'], $model),
            2,
            $templateDataReal,
            2,
        );
        $templateDataReal = $this->serviceGenerator->importComponent($updateFields['updateFields'], $templateDataReal);
        $templateDataReal = $this->serviceGenerator->generateRules(
            $updateFields['updateFields'],
            $model,
            $templateDataReal,
        );
        $this->serviceFile->createFileReal("$path/form.tsx", $templateDataReal);
    }
}
