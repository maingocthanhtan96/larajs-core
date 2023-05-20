<?php

namespace LaraJS\Core\Generators\Backend;

use Carbon\Carbon;
use Illuminate\Support\Str;
use LaraJS\Core\Generators\BaseGenerator;

class RelationshipGenerator extends BaseGenerator
{
    public const REF_UPPER = 'Ref';

    public const _REF_LOWER = 'ref_';

    public const SORT_COLUMN = 'sortable="custom"';

    public const _ID = '_id';

    public const _IDS = '_ids';

    public const NUMBER_COLUMN = 12;

    protected array $relationship;

    protected array $tableDiff;

    public function __construct($relationship, $model, $modelCurrent, $column, $column2, $options)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.migration');
        $this->notDelete = config('generator.not_delete.laravel.model');
        $this->relationship = config('generator.relationship.relationship');
        $this->_generate($relationship, $model, $modelCurrent, $column, $column2, $options);
    }

    private function _generate($relationship, $model, $modelCurrent, $column, $column2, $options)
    {
        $pathTemplate = 'Models/';
        $fileRelationship =
            $relationship === $this->relationship['belongs_to_many'] ? 'relationshipMTM' : 'relationship';
        $template = $this->serviceGenerator->get_template($fileRelationship, $pathTemplate);
        // Model Relationship
        if ($relationship === $this->relationship['has_one']) {
            $templateModel = str_replace('{{FUNCTION_NAME}}', Str::camel($model), $template);
            $templateInverse = str_replace('{{FUNCTION_NAME}}', Str::camel($modelCurrent), $template);
        } elseif ($relationship === $this->relationship['has_many']) {
            $templateModel = str_replace(
                '{{FUNCTION_NAME}}',
                $this->serviceGenerator->modelNamePluralFe($model),
                $template,
            );
            $templateInverse = str_replace('{{FUNCTION_NAME}}', Str::camel($modelCurrent), $template);
        } else {
            $templateModel = str_replace(
                '{{FUNCTION_NAME}}',
                $this->serviceGenerator->modelNamePluralFe($model),
                $template,
            );
            $templateInverse = str_replace(
                '{{FUNCTION_NAME}}',
                $this->serviceGenerator->modelNamePluralFe($modelCurrent),
                $template,
            );
        }
        $templateModel = str_replace('{{RELATION}}', $relationship, $templateModel);
        $templateModel = str_replace('{{RELATION_MODEL_CLASS}}', $model, $templateModel);
        //ModelCurrent Relationship

        $templateInverse = str_replace('{{RELATION_MODEL_CLASS}}', $modelCurrent, $templateInverse);
        if ($relationship === $this->relationship['belongs_to_many']) {
            $templateInverse = str_replace('{{RELATION}}', 'belongsToMany', $templateInverse);
            $templateModel = str_replace(
                '{{FIELD_RELATIONSHIP}}',
                "'".
                    self::_REF_LOWER.
                    Str::snake($modelCurrent).
                    '_'.
                    Str::snake($model).
                    "', '".
                    Str::snake($modelCurrent).
                    "_id', '".
                    Str::snake($model).
                    "_id'",
                $templateModel,
            );
            $templateModel = str_replace(", 'id'", '', $templateModel);
            $templateInverse = str_replace(
                '{{FIELD_RELATIONSHIP}}',
                "'".
                    self::_REF_LOWER.
                    Str::snake($modelCurrent).
                    '_'.
                    Str::snake($model).
                    "', '".
                    Str::snake($model).
                    "_id', '".
                    Str::snake($modelCurrent).
                    "_id'",
                $templateInverse,
            );
            $templateInverse = str_replace(", 'id'", '', $templateInverse);
        } else {
            $templateModel = str_replace(
                '{{FIELD_RELATIONSHIP}}',
                "'".Str::snake($modelCurrent).self::_ID."'",
                $templateModel,
            );
            $templateInverse = str_replace(
                '{{FIELD_RELATIONSHIP}}',
                "'".Str::snake($modelCurrent).self::_ID."'",
                $templateInverse,
            );
            $templateInverse = str_replace('{{RELATION}}', 'belongsTo', $templateInverse);
        }
        $this->_migrateRelationship($relationship, $model, $modelCurrent, $column, $column2, $options);
        //replace file model real
        $templateModelReal = $this->serviceGenerator->getFile('model', 'laravel', $model.'.php');
        $this->_replaceFile($model, $templateInverse, $templateModelReal);
        //replace file model current real
        $templateModelCurrentReal = $this->serviceGenerator->getFile('model', 'laravel', $modelCurrent.'.php');
        $this->_replaceFile($modelCurrent, $templateModel, $templateModelCurrentReal);
    }

    private function _migrateRelationship($relationship, $model, $modelCurrent, $column, $column2, $options)
    {
        $now = Carbon::now();
        $pathTemplate = 'Databases/Migrations/';
        $templateData = $this->serviceGenerator->get_template('migrationRelationship', $pathTemplate);
        $templateData = str_replace('{{DATE_TIME}}', $now->toDateTimeString(), $templateData);
        if ($relationship === $this->relationship['belongs_to_many']) {
            //belongsToMany
            $templateData = $this->serviceGenerator->get_template('migrationRelationshipMTM', $pathTemplate);
            //if belongsToMany replace table to create
            $templateData = $this->_replaceTemplateRelationshipMTM($model, $modelCurrent, $templateData);
            $fileName =
                date('Y_m_d_His').
                '_relationship_'.
                self::_REF_LOWER.
                Str::snake($modelCurrent).
                '_'.
                Str::snake($model).
                '_table.php';
            $this->_generateModelMTM($model, $modelCurrent);
            $this->_generateSeeder($modelCurrent, $model, $relationship);
            $this->_generateRoute($modelCurrent);
            $this->_generateRoute($model);
            $this->_generateRequest($modelCurrent, $model, $relationship);
            $this->_generateRequest($model, $modelCurrent, $relationship);
            $this->_generateController($modelCurrent, $model, $options, $column, $relationship);
            $this->_generateController($model, $modelCurrent, $options, $column2, $relationship);
            $this->_generateTests($model);
            $this->_generateTests($modelCurrent);
            //            $this->_generateRepository($modelCurrent, $model);
            //            $this->_generateRepository($model, $modelCurrent);
            $this->_generateObserver($modelCurrent, $model);
            $this->_generateObserver($model, $modelCurrent);
            //generate frontend
            $this->_generateFormFe($modelCurrent, $model, $column, $options, $relationship);
            $this->_generateFormFe($model, $modelCurrent, $column2, $options, $relationship);
            if (!$this->jsType()) {
                $this->_generateInterfaceCommon($modelCurrent, $model, $relationship);
                $this->_generateInterfaceCommon($model, $modelCurrent, $relationship);
            }
        } else {
            //hasOne or hasMany
            $templateData = $this->_replaceTemplateRelationship($model, $modelCurrent, $templateData);
            $fileName =
                date('Y_m_d_His').
                '_relationship_'.
                $this->serviceGenerator->tableName($modelCurrent).
                '_'.
                $this->serviceGenerator->tableName($model).
                '_table.php';
            $this->_generateModel($modelCurrent, $model);
            $this->_generateSeeder($modelCurrent, $model, $relationship);
            $this->_generateRoute($modelCurrent);
            $this->_generateRequest($modelCurrent, $model, $relationship);
            $this->_generateController($modelCurrent, $model, $options, $column, $relationship);
            $this->_generateTests($modelCurrent);
            //generate frontend
            $this->_generateFormFe($modelCurrent, $model, $column, $options, $relationship);
            if (!$this->jsType()) {
                $this->_generateInterfaceCommon($modelCurrent, $model, $relationship);
            }
        }

        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }

    private function _generateFormFe($model, $modelRelationship, $columnRelationship, $options, $relationship)
    {
        $notDelete = config('generator.not_delete.vue.form');
        $notDeleteUses = config('generator.not_delete.vue.uses');
        $isMTM = $relationship === $this->relationship['belongs_to_many'];
        $path = config('generator.path.vue.uses');
        $folderName = $this->serviceGenerator->folderPages($modelRelationship);
        $path = "$path{$folderName}";
        //create form: form.tsx
        $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', "/$folderName/{$this->jsType('form')}");
        if (!$templateDataReal) {
            return;
        }
        if (!$isMTM) {
            $templateRules = $this->_getHandlerTemplate();
            $templateRules = str_replace('{{$FIELD$}}', Str::snake($model).self::_ID, $templateRules);
            $templateRules = str_replace('{{$ATTRIBUTE_FIELD$}}', 't(\'route.'.Str::snake($model).'\')', $templateRules);
            $templateRules = str_replace('{{$TRIGGER$}}', 'change', $templateRules);
            $templateDataReal = $this->serviceGenerator->replaceNotDelete($notDelete['rules'], $templateRules, 1, $templateDataReal, 2);
        }
        $field = $isMTM ? Str::snake($model).self::_IDS : Str::snake($model).self::_ID;
        $tableFunctionRelationship = $isMTM
            ? $this->serviceGenerator->tableName($model)
            : $this->serviceGenerator->tableNameNotPlural($model);
        $templateDataReal = $this->phpParserService->runParserJS("{$path}{$this->jsType('form')}", [
            'key' => 'uses.form:item',
            'items' => [
                $field => [
                    'type' => $isMTM ? 'array' : ': null',
                ],
            ],
        ], $templateDataReal);
        $templateDataReal = $this->_generateAddApi(
            $model,
            $modelRelationship,
            $templateDataReal,
            $notDelete,
            $relationship,
        );
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $notDeleteUses['form']['item'],
            $this->_generateSelect(
                Str::snake($model),
                Str::snake($model).($isMTM ? self::_IDS : self::_ID),
                $columnRelationship,
                $relationship,
            ),
            3,
            $templateDataReal,
            2,
        );
        $this->serviceFile->createFileReal("$path/{$this->jsType('form')}", $templateDataReal);
        // create column: table.tsx
        $configOptions = config('generator.relationship.options');
        if (in_array($configOptions['show'], $options)) {
            $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', "/$folderName/{$this->jsType('table')}");
            $templateColumn = $this->serviceGenerator->get_template('column', 'Forms/', 'vue');
            $templateColumn = str_replace(
                '{{$FIELD_NAME$}}',
                Str::camel($tableFunctionRelationship).".$columnRelationship",
                $templateColumn,
            );
            $templateColumn = str_replace(
                '{{$FORM_SORTABLE$}}',
                in_array($configOptions['sort'], $options) ? "'custom'" : 'false',
                $templateColumn,
            );
            $templateColumn = str_replace('{{$FORM_ALIGN$}}', 'left', $templateColumn);
            $templateColumn = str_replace(
                '{{$FORM_LABEL$}}',
                "label: t('route.{$this->serviceGenerator->tableNameNotPlural($model)}'),",
                $templateColumn,
            );
            if ($isMTM) {
                $templateRow = <<<TEMPLATE
                template: ({ row }) => row.$tableFunctionRelationship.map(item => <el-tag>{item.$columnRelationship}</el-tag>),
                TEMPLATE;
            } else {
                $templateRow = <<<TEMPLATE
                template: ({ row }) => row.$tableFunctionRelationship?.$columnRelationship,
                TEMPLATE;
            }
            $templateColumn = str_replace('{{$FORM_TEMPLATE$}}', $templateRow, $templateColumn);
            $templateDataReal = $this->phpParserService->runParserJS("$path{$this->jsType('table')}", [
                'key' => 'uses.table:columns',
                'items' => [$templateColumn],
            ], $templateDataReal);
            $templateDataReal = $this->_generateQuery(
                $model,
                $modelRelationship,
                $relationship,
                $options,
                $columnRelationship,
                $templateDataReal,
            );
            $this->serviceFile->createFileReal("$path/{$this->jsType('table')}", $templateDataReal);
        }
        //generate api
        $this->_generateApi($model);
        $this->_generateFunctionAll($model);
        //generate form item
        $this->_generateFormItem($model, $modelRelationship, $notDelete, $isMTM);
    }

    private function _generateQuery($model, $modelRelationship, $relationship, $options, $columnRelationship, $templateDataReal)
    {
        $configOptions = config('generator.relationship.options');
        $path = config('generator.path.vue.uses');
        $folderName = $this->serviceGenerator->folderPages($modelRelationship);
        $path = "$path{$folderName}";
        if (in_array($configOptions['show'], $options)) {
            $withRelationship =
                $relationship === $this->relationship['belongs_to_many']
                    ? $this->serviceGenerator->modelNamePluralFe($model)
                    : $this->serviceGenerator->modelNameNotPluralFe($model);
            $templateDataReal = $this->phpParserService->runParserJS("$path/{$this->jsType('table')}", [
                'key' => 'uses.table:relationship',
                'items' => [$withRelationship],
            ], $templateDataReal);
        }
        if (in_array($configOptions['search'], $options)) {
            $columnDidGenerate = Str::camel($model).".$columnRelationship";
            $templateDataReal = $this->phpParserService->runParserJS("$path/{$this->jsType('table')}", [
                'key' => 'uses.table:column_search',
                'items' => [$columnDidGenerate],
            ], $templateDataReal);
        }

        return $templateDataReal;
    }

    //create form item
    private function _generateFormItem($model, $modelRelationship, $notDelete, $isMTM)
    {
        $fileName = "{$this->serviceGenerator->folderPages($modelRelationship)}/Form.vue";
        $templateDataReal = $this->serviceGenerator->getFile('views', 'vue', $fileName);
        // edit
        if ($isMTM) {
            $stubGetData =
                'form.{{$FIELD_NAME$}} = {{$MODEL_RELATIONSHIP$}}.{{$FIELD_RELATIONSHIP$}}.map(item => item.id);';
            $stubGetData = str_replace(
                '{{$FIELD_NAME$}}',
                $this->serviceGenerator->tableNameNotPlural($model).self::_IDS,
                $stubGetData,
            );
            $stubGetData = str_replace(
                '{{$MODEL_RELATIONSHIP$}}',
                $this->serviceGenerator->modelNameNotPluralFe($modelRelationship),
                $stubGetData,
            );
            $stubGetData = str_replace(
                '{{$FIELD_RELATIONSHIP$}}',
                Str::snake($this->serviceGenerator->modelNamePluralFe($model)),
                $stubGetData,
            );
            $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                $notDelete['edit'],
                $stubGetData,
                4,
                $templateDataReal,
            );
        }

        $fileName = config('generator.path.vue.views').$fileName;
        $this->serviceFile->createFileReal($fileName, $templateDataReal);
    }

    // crate function "all" in file use
    private function _generateFunctionAll($model)
    {
        $fileName = "{$this->serviceGenerator->folderPages($model)}/{$this->jsType('index')}";
        $templateDataRealRelationship = $this->serviceGenerator->getFile('uses', 'vue', $fileName);
        $stubAddData = $this->serviceGenerator->get_template('addDataRelationship', 'Handler/', 'vue');
        $nameFunctionAll = "all{$model}";
        $stubAddData = str_replace(
            '{{$USE_MODEL_RELATIONSHIP$}}',
            $this->serviceGenerator->modelNamePluralFe($nameFunctionAll),
            $stubAddData,
        );
        $stubAddData = str_replace(
            '{{$MODEL_RELATIONSHIP$}}',
            $this->serviceGenerator->modelNameNotPlural($model),
            $stubAddData,
        );
        $fileName = config('generator.path.vue.uses').$fileName;
        $templateDataRealRelationship = $this->phpParserService->runParserJS($fileName, [
            'name' => "use{$this->serviceGenerator->modelNamePlural($model)}",
            'key' => 'uses.index',
            'value' => $stubAddData,
            'property' => $this->serviceGenerator->modelNamePluralFe($nameFunctionAll),
        ], $templateDataRealRelationship);
        $this->serviceFile->createFileReal($fileName, $templateDataRealRelationship);
    }

    private function _generateAddApi($model, $modelRelationship, $templateDataReal, $notDelete, $relationship): string
    {
        $path = config('generator.path.vue.uses');
        $notDeleteUses = config('generator.not_delete.vue.uses');
        $folderName = $this->serviceGenerator->folderPages($modelRelationship);
        $fileName = "{$this->serviceGenerator->folderPages($modelRelationship)}/Form.vue";
        $nameModelRelationship =
            $relationship === $this->relationship['has_one']
                ? $this->serviceGenerator->modelNameNotPluralFe($model)
                : $this->serviceGenerator->modelNamePluralFe($model);
        $templateDataReal = $this->phpParserService->runParserJS("$path{$folderName}/{$this->jsType('form')}", [
            'key' => 'uses.form:item',
            'variable' => 'state',
            'items' => [
                $nameModelRelationship => [
                    'value' => [],
                    'type' => 'array',
                ],
            ],
        ], $templateDataReal);
        // State Root
        if (!$this->jsType()) {
            $templateDataReal = $this->phpParserService->runParserJS("$path{$folderName}/{$this->jsType('form')}", [
                'key' => 'uses.form',
                'name' => $model,
                'path' => '@larajs/common',
                'interface' => "{$modelRelationship}Root",
                'items' => [
                    $nameModelRelationship => "{$model}[];",
                ],
            ], $templateDataReal);
        }
        // form
        $templateDataRealForm = $this->serviceGenerator->getFile('views', 'vue', $fileName);
        $useModel = "use{$this->serviceGenerator->modelNamePlural($model)}";
        $importStub = "import { $useModel } from '{$this->getImportJsOrTs()}/uses';";
        if (!stripos($templateDataRealForm, $useModel)) {
            $templateDataRealForm = $this->serviceGenerator->replaceNotDelete(
                $notDelete['import_component'],
                $importStub,
                0,
                $templateDataRealForm,
                2,
            );
        }
        $useStub = "const { {$this->serviceGenerator->modelNamePluralFe("all$model")} } = $useModel();";
        if (!stripos($templateDataRealForm, $useStub)) {
            $templateDataRealForm = $this->serviceGenerator->replaceNotDelete(
                $notDeleteUses['use'],
                $useStub,
                1,
                $templateDataRealForm,
                2,
            );
        }
        $stubGetData = $this->serviceGenerator->get_template('getDataRelationship', 'Handler/', 'vue');
        $stubGetData = str_replace(
            '{{$USE_MODEL_RELATIONSHIP$}}',
            $this->serviceGenerator->modelNamePluralFe("all{$model}"),
            $stubGetData,
        );
        $stubGetData = str_replace('{{$MODEL_RELATIONSHIP$}}', $nameModelRelationship, $stubGetData);
        $templateDataRealForm = $this->serviceGenerator->replaceNotDelete(
            $notDelete['create'],
            $stubGetData,
            3,
            $templateDataRealForm,
            2,
        );
        $fileName = config('generator.path.vue.views').$fileName;
        $this->serviceFile->createFileReal($fileName, $templateDataRealForm);

        return $templateDataReal;
    }

    private function _generateApi($model): void
    {
        $path = config('generator.path.vue.api');
        $fileName = $this->serviceGenerator->folderPages($model).".{$this->jsType('ext')}";
        $templateDataReal = $this->phpParserService->runParserJS($path.$fileName, [
            'key' => 'api.import',
            'name' => 'request',
            'path' => "'{$this->getImportJsOrTs()}/services'",
            'class_name' => "{$model}Resource",
        ]);

        $this->serviceFile->createFile(config('generator.path.vue.api'), $fileName, $templateDataReal);
    }

    private function _generateIndexTableFe($modelRelationship, $columnRelationship, $options, $funcName, $relationship)
    {
        $configOptions = config('generator.relationship.options');
        $notDelete = config('generator.not_delete.vue.views');
        $fileName = $this->serviceGenerator->folderPages($modelRelationship).'/index.vue';
        $templateDataReal = $this->serviceGenerator->getFile('views', 'vue', $fileName);
        $pathTemplate = 'Handler/';

        if (in_array($configOptions['show'], $options)) {
            if ($relationship === $this->relationship['belongs_to_many']) {
                $templateTableColumn = $this->serviceGenerator->get_template(
                    'tableTagRelationshipMTM',
                    $pathTemplate,
                    'vue',
                );
                $fileNameTag = $funcName.'.'.$columnRelationship;
                $templateTableColumn = str_replace('{{$FIELD_NAME$}}', $fileNameTag, $templateTableColumn);
                $templateTableColumn = str_replace('{{$TABLE_MODEL_CLASS$}}', $funcName, $templateTableColumn);
                $templateTableColumn = str_replace('{{$ALIGN$}}', 'left', $templateTableColumn);
                $templateTableColumn = str_replace(
                    '{{$MODEL_RELATIONSHIP$}}',
                    $this->serviceGenerator->tableName($funcName),
                    $templateTableColumn,
                );
                $templateTableColumn = str_replace('{{$COLUMN_DISPLAY$}}', $columnRelationship, $templateTableColumn);
            } else {
                $templateTableColumn = $this->serviceGenerator->get_template(
                    'tableColumnRelationship',
                    $pathTemplate,
                    'vue',
                );
                $templateTableColumn = str_replace(
                    '{{$FIELD_NAME_RELATIONSHIP$}}',
                    $funcName.self::_ID,
                    $templateTableColumn,
                );
                $templateTableColumn = str_replace(
                    '{{$MODEL_RELATIONSHIP$}}',
                    $this->serviceGenerator->tableNameNotPlural($funcName),
                    $templateTableColumn,
                );
                $templateTableColumn = str_replace('{{$FIELD_NAME$}}', $columnRelationship, $templateTableColumn);
                $templateTableColumn = str_replace('{{$TABLE_MODEL_CLASS$}}', $funcName, $templateTableColumn);
                $templateTableColumn = str_replace('{{$ALIGN$}}', 'left', $templateTableColumn);
            }

            if (in_array($configOptions['sort'], $options)) {
                $templateTableColumn = str_replace('{{$SORT$}}', self::SORT_COLUMN, $templateTableColumn);
            } else {
                $templateTableColumn = str_replace('{{$SORT$}}', '', $templateTableColumn);
            }
            $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                $notDelete['templates'],
                $templateTableColumn,
                6,
                $templateDataReal,
                2,
            );
            // replace file
            $fileName = config('generator.path.vue.views').$fileName;
            $this->serviceFile->createFileReal($fileName, $templateDataReal);
        }
    }

    private function _generateOptions($model, $modelRelationship, $templateDataReal, $relationship)
    {
        if ($relationship === $this->relationship['belongs_to_many']) {
            $modelName = $this->serviceGenerator->modelNameNotPluralFe($model);
            $functionShow = "this->{$modelName}Repository->show($$modelName, [";
            $templateFunctionShow = $this->serviceGenerator->searchTemplate(
                $functionShow,
                ']);',
                0,
                0,
                $templateDataReal,
            );
            if (!$templateFunctionShow) {
                return $templateDataReal;
            }
            $commaFunctionShow = ',';
            if (Str::endsWith($templateFunctionShow, ',') || strlen($templateFunctionShow) === strlen($functionShow)) {
                $commaFunctionShow = '';
            }
            $withRelationship = "'{$this->serviceGenerator->modelNamePluralFe($modelRelationship)}'";
            $templateDataReal = str_replace(
                "$templateFunctionShow]);",
                "{$templateFunctionShow}{$commaFunctionShow}{$withRelationship}]);",
                $templateDataReal,
            );
        }

        return $templateDataReal;
    }

    private function _generateRequest($modelRelationship, $model, $relationship)
    {
        $notDelete = config('generator.not_delete.laravel.request');
        $fileNameFunc = "Store{$model}Request.php";
        $templateDataRealFunc = $this->serviceGenerator->getFile('request', 'laravel', $fileNameFunc);
        if (!$templateDataRealFunc) {
            return;
        }
        $isMTM = $relationship === $this->relationship['belongs_to_many'];
        $rule = $isMTM ? 'array' : 'integer|required';
        $templateDataRealFunc = $this->serviceGenerator->replaceNotDelete(
            $notDelete['rule'],
            "'".Str::snake($modelRelationship).($isMTM ? self::_IDS : self::_ID)."'".' => '."'$rule',",
            3,
            $templateDataRealFunc,
        );
        $fileNameFunc = config('generator.path.laravel.request').$fileNameFunc;
        $this->serviceFile->createFileReal($fileNameFunc, $templateDataRealFunc);
    }

    private function _generateController($modelRelationship, $model, $options, $column, $relationship)
    {
        $notDelete = config('generator.not_delete.laravel.controller');
        $pathTemplate = 'Controllers/';
        $fileName = $model.'Controller.php';
        $templateDataReal = $this->serviceGenerator->getFile('api_controller', 'laravel', $fileName);
        if (!$templateDataReal) {
            return;
        }
        //generate options
        $templateDataReal = $this->_generateOptions($model, $modelRelationship, $templateDataReal, $relationship);
        $path = config('generator.path.laravel.api_controller');
        $fileName = $path.$fileName;
        $this->serviceFile->createFileReal($fileName, $templateDataReal);
        //generate controller
        $fileNameFunc = $modelRelationship.'Controller.php';
        $templateDataRealFunc = $this->serviceGenerator->getFile('api_controller', 'laravel', $fileNameFunc);
        if (!stripos($templateDataRealFunc, 'function all()')) {
            $templateDataFunc = $this->serviceGenerator->get_template('relationship', $pathTemplate);
            $templateDataFunc = str_replace('{{MODEL_RELATIONSHIP}}', $modelRelationship, $templateDataFunc);
            $templateDataFunc = str_replace(
                '{{PARAM_MODEL_RELATIONSHIP_LIST}}',
                $this->serviceGenerator->modelNamePluralFe($modelRelationship),
                $templateDataFunc,
            );
            $templateDataFunc = str_replace(
                '{{PARAM_MODEL_RELATIONSHIP}}',
                $this->serviceGenerator->modelNameNotPluralFe($modelRelationship),
                $templateDataFunc,
            );
            $templateDataRealFunc = $this->serviceGenerator->replaceEndFile($templateDataRealFunc, $templateDataFunc, 1);
            $fileNameFunc = $path.$fileNameFunc;
            $this->serviceFile->createFileReal($fileNameFunc, $templateDataRealFunc);
        }
    }

    private function _generateTests($modelRelationship)
    {
        $fileName = "{$modelRelationship}Test.php";
        $templateDataReal = $this->serviceGenerator->getFile('tests.feature', 'laravel', $fileName);
        if (!$templateDataReal) {
            return;
        }
        $templateDataFunc = $this->serviceGenerator->get_template('Relationship', 'Tests/');
        $templateDataFunc = str_replace('{{MODEL_TEST}}', $this->serviceGenerator->modelNameTitle($modelRelationship), $templateDataFunc);
        $templateDataFunc = str_replace('{{ROUTE_RESOURCE}}', $this->serviceGenerator->urlResource($modelRelationship), $templateDataFunc);
        $templateDataReal = $this->serviceGenerator->replaceEndFile($templateDataReal, $templateDataFunc, 0);
        $fileNameFunc = config('generator.path.laravel.tests.feature').$fileName;
        $this->serviceFile->createFileReal($fileNameFunc, $templateDataReal);
    }

    private function _generateRepository($modelRelationship, $model)
    {
        $notDelete = config('generator.not_delete.laravel.repository');
        $pathTemplate = 'Repositories/';
        $fileName = "$model/{$model}Repository.php";
        $templateDataReal = $this->serviceGenerator->getFile('repository', 'laravel', $fileName);
        if (!$templateDataReal) {
            return;
        }
        if (stripos($templateDataReal, $notDelete['relationship_mtm'])) {
            $templateFunction = $this->serviceGenerator->get_template('functionRelationship', $pathTemplate);
            $templateDataReal = str_replace($notDelete['relationship_mtm'], $templateFunction, $templateDataReal);
            $templateDataReal = str_replace(
                '{{MODEL}}',
                $this->serviceGenerator->modelNameNotPluralFe($model),
                $templateDataReal,
            );
            $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                $notDelete['use_class'],
                'use Illuminate\Database\Eloquent\Model;',
                0,
                $templateDataReal,
            );
            $templateDataReal = str_replace($notDelete['use_class'], 'use Illuminate\Http\Request;', $templateDataReal);
        }
        $templateCreateUpdate = $this->serviceGenerator->get_template('createUpdateRelationship', $pathTemplate);
        //replace create or update
        //        $paramCreateUpdateStub = "{$this->serviceGenerator->modelNameNotPluralFe($modelRelationship)}Id";
        //        $templateCreateUpdate = str_replace('{{FIELD_MODEL_ID}}', $paramCreateUpdateStub, $templateCreateUpdate);
        $templateCreateUpdate = str_replace(
            '{{SNAKE_FIELD_MODEL_ID}}',
            $this->serviceGenerator->tableNameNotPlural($modelRelationship).self::_IDS,
            $templateCreateUpdate,
        );
        $templateCreateUpdate = str_replace(
            '{{SNAKE_FIELD_MODEL_ID}}',
            $this->serviceGenerator->tableNameNotPlural($modelRelationship).self::_IDS,
            $templateCreateUpdate,
        );
        $templateCreateUpdate = str_replace(
            '{{MODEL}}',
            $this->serviceGenerator->modelNameNotPluralFe($model),
            $templateCreateUpdate,
        );
        $templateCreateUpdate = str_replace(
            '{{MODEL_RELATIONSHIP}}',
            $this->serviceGenerator->modelNamePluralFe($modelRelationship),
            $templateCreateUpdate,
        );
        $templateCreate = str_replace('{{ATTACH_ASYNC}}', 'attach', $templateCreateUpdate);
        $templateUpdate = str_replace('{{ATTACH_ASYNC}}', 'sync', $templateCreateUpdate);
        //replace create
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $notDelete['relationship_mtm_create'],
            $templateCreate,
            2,
            $templateDataReal,
        );
        //replace update
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $notDelete['relationship_mtm_update'],
            $templateUpdate,
            2,
            $templateDataReal,
        );
        //replace show
        $templateShow = $this->serviceGenerator->get_template('showRelationship', $pathTemplate);
        $templateShow = str_replace('{{MODEL}}', $this->serviceGenerator->modelNameNotPluralFe($model), $templateShow);
        $templateShow = str_replace(
            '{{SNAKE_MODEL_RELATIONSHIP_ID}}',
            $this->serviceGenerator->tableNameNotPlural($modelRelationship).self::_IDS,
            $templateShow,
        );
        $templateShow = str_replace(
            '{{MODEL_RELATIONSHIP}}',
            $this->serviceGenerator->modelNamePluralFe($modelRelationship),
            $templateShow,
        );
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $notDelete['relationship_mtm_show'],
            $templateShow,
            2,
            $templateDataReal,
        );
        //replace delete
        $templateDelete = $this->serviceGenerator->get_template('deleteRelationship', $pathTemplate);
        $templateDelete = str_replace(
            '{{MODEL}}',
            $this->serviceGenerator->modelNameNotPluralFe($model),
            $templateDelete,
        );
        $templateDelete = str_replace(
            '{{MODEL_RELATIONSHIP}}',
            $this->serviceGenerator->modelNamePluralFe($modelRelationship),
            $templateDelete,
        );
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $notDelete['relationship_mtm_delete'],
            $templateDelete,
            2,
            $templateDataReal,
        );
        $path = config('generator.path.laravel.repository');
        $this->serviceFile->createFileReal("$path/$fileName", $templateDataReal);
    }

    private function _generateObserver($modelRelationship, $model): void
    {
        $pathObserver = config('generator.path.laravel.observer');
        $pathTemplate = 'Observers/';
        $fileName = "{$model}Observer.php";
        $templateDataReal = file_exists("$pathObserver{$model}Observer.php")
            ? $this->serviceGenerator->getFile('observer', 'laravel', $fileName)
            : $this->serviceGenerator->get_template('observer', $pathTemplate);
        if (!$templateDataReal) {
            return;
        }
        $templateDataReal = str_replace('{{MODEL_CLASS}}', $model, $templateDataReal);
        $templateDataReal = str_replace(
            '{{MODAL_CLASS_PARAM}}',
            $this->serviceGenerator->modelNameNotPluralFe($model),
            $templateDataReal,
        );
        // saved
        $templateSaved = $this->serviceGenerator->get_template('saved', $pathTemplate);
        $templateSaved = str_replace(
            '{{FIELD_NAME}}',
            $this->serviceGenerator->tableNameNotPlural($modelRelationship).self::_IDS,
            $templateSaved,
        );
        $templateSaved = str_replace(
            '{{MODAL_CLASS_PARAM}}',
            $this->serviceGenerator->modelNameNotPluralFe($model),
            $templateSaved,
        );
        $templateSaved = str_replace(
            '{{FUNCTION_NAME}}',
            $this->serviceGenerator->modelNamePluralFe($modelRelationship),
            $templateSaved,
        );
        // deleted
        $templateDeleted = $this->serviceGenerator->get_template('deleted', $pathTemplate);
        $templateDeleted = str_replace(
            '{{MODAL_CLASS_PARAM}}',
            $this->serviceGenerator->modelNameNotPluralFe($model),
            $templateDeleted,
        );
        $templateDeleted = str_replace(
            '{{FUNCTION_NAME}}',
            $this->serviceGenerator->modelNamePluralFe($modelRelationship),
            $templateDeleted,
        );
        //replace delete
        $templateDataReal = $this->phpParserService->addCodeToFunction($templateDataReal, $templateSaved, 'saved');
        $templateDataReal = $this->phpParserService->addCodeToFunction($templateDataReal, $templateDeleted, 'deleted');
        $this->serviceFile->createFile($pathObserver, $fileName, $templateDataReal);
        // provider event
        $fileName = 'EventServiceProvider.php';
        $templateDataRegisterEvent = $this->serviceGenerator->getFile('provider', 'laravel', $fileName);
        $templateDataRegisterEvent = $this->phpParserService->usePackage($templateDataRegisterEvent, "App\Models\\$model");
        $templateDataRegisterEvent = $this->phpParserService->usePackage($templateDataRegisterEvent, "App\Observers\\{$model}Observer");
        $templateDataRegisterEvent = $this->phpParserService->addCodeToFunction($templateDataRegisterEvent, "$model::observe({$model}Observer::class);\n", 'boot');
        $pathProvider = config('generator.path.laravel.provider');
        $this->serviceFile->createFileReal("$pathProvider/$fileName", $templateDataRegisterEvent);
    }

    private function _generateRoute($modelRelationship)
    {
        $templateDataReal = $this->serviceGenerator->getFile('api_routes', 'laravel');
        if (!$templateDataReal) {
            return;
        }
        if (!stripos($templateDataReal, "{$this->serviceGenerator->urlResource($modelRelationship)}/all")) {
            $stubResource = "Route::apiResource('{{RESOURCE}}', '{{MODEL_CLASS}}Controller');";
            $stubRoute = "Route::get('/{{MODEL}}/all', '{{CONTROLLER}}Controller@all')->name('{{MODEL}}.all');";
            $templateResource = str_replace(
                '{{RESOURCE}}',
                $this->serviceGenerator->urlResource($modelRelationship),
                $stubResource,
            );
            $templateResource = str_replace('{{MODEL_CLASS}}', $modelRelationship, $templateResource);
            $templateRoute = str_replace(
                '{{MODEL}}',
                $this->serviceGenerator->urlResource($modelRelationship),
                $stubRoute,
            );
            $templateRoute = str_replace('{{CONTROLLER}}', $modelRelationship, $templateRoute);
            $templateDataReal = str_replace(
                $templateResource,
                $templateRoute.$this->serviceGenerator->infy_nl_tab(1, 3).$templateResource,
                $templateDataReal,
            );
            $path = config('generator.path.laravel.api_routes');
            $this->serviceFile->createFileReal($path, $templateDataReal);
        }
    }

    private function _replaceFile($model, $templateModel, $templateReal)
    {
        $templateReal = $this->serviceGenerator->replaceEndFile($templateReal, $templateModel, 1);
        $this->serviceFile->createFileReal(config('generator.path.laravel.model')."$model.php", $templateReal);
    }

    private function _replaceTemplateRelationship($model, $modelDif, $templateData): string
    {
        $templateData = str_replace('{{TABLE_NAME}}', $this->serviceGenerator->tableName($model), $templateData);
        $templateData = str_replace('{{FOREIGN_KEY}}', Str::snake($modelDif).self::_ID, $templateData);

        return str_replace('{{TABLE_FOREIGN_KEY}}', $this->serviceGenerator->tableName($modelDif), $templateData);
    }

    private function _replaceTemplateRelationshipMTM($model, $modelCurrent, $templateData): string
    {
        $now = Carbon::now();
        $templateData = str_replace('{{DATE_TIME}}', $now->toDateTimeString(), $templateData);
        $templateData = str_replace(
            '{{TABLE_NAME}}',
            self::_REF_LOWER.Str::snake($modelCurrent).'_'.Str::snake($model),
            $templateData,
        );
        $templateData = str_replace('{{FOREIGN_KEY_1}}', Str::snake($model).self::_ID, $templateData);
        $templateData = str_replace('{{FOREIGN_KEY_2}}', Str::snake($modelCurrent).self::_ID, $templateData);
        $templateData = str_replace(
            '{{TABLE_FOREIGN_KEY_1}}',
            $this->serviceGenerator->tableName($model),
            $templateData,
        );

        return str_replace('{{TABLE_FOREIGN_KEY_2}}', $this->serviceGenerator->tableName($modelCurrent), $templateData);
    }

    private function _generateModel($model, $modelRelationship): void
    {
        $templateDataReal = $this->serviceGenerator->getFile('model', 'laravel', $modelRelationship.'.php');
        if (!$templateDataReal) {
            return;
        }
        $field = Str::snake($model).self::_ID;
        $templateDataReal = $this->phpParserService->addStringToArray($templateDataReal, $field, 'fillable');
        $this->_createFileAll('model', $modelRelationship, $templateDataReal);
    }

    private function _generateModelMTM($model, $modelCurrent): void
    {
        $fieldModel = Str::snake($model).self::_ID;
        $fieldModelCurrent = Str::snake($modelCurrent).self::_ID;
        $now = Carbon::now();
        $pathTemplate = 'Models/';
        $templateData = $this->serviceGenerator->get_template('model', $pathTemplate);
        $templateData = str_replace('{{DATE}}', $now->toDateTimeString(), $templateData);
        $templateData = str_replace('{{MODEL_CLASS}}', self::REF_UPPER.$modelCurrent.$model, $templateData);
        $arFields = ["'".$fieldModel."',", "'".$fieldModelCurrent."',"];
        $implodeFields = implode($this->serviceGenerator->infy_nl_tab(1, 2), $arFields);
        $templateData = str_replace('{{FIELDS}}', $implodeFields, $templateData);
        $templateData = str_replace(
            '{{TABLE_NAME}}',
            self::_REF_LOWER.Str::snake($modelCurrent).'_'.Str::snake($model),
            $templateData,
        );
        $templateData = str_replace('{{CATS}}', '', $templateData);
        $fileName = self::REF_UPPER.$modelCurrent.$model.'.php';
        $path = config('generator.path.laravel.model');
        $this->serviceFile->createFile($path, $fileName, $templateData);
    }

    private function _generateSeeder($model, $modelRelationship, $relationship): void
    {
        // model hasOne/hasMany
        $fileName = "{$model}Seeder";
        $templateDataReal = $this->serviceGenerator->getFile('seeder', 'laravel', "$fileName.php");
        if ($templateDataReal) {
            $nameModelRelationship =
                $relationship === $this->relationship['has_one']
                    ? $this->serviceGenerator->modelNameNotPlural($modelRelationship)
                    : $this->serviceGenerator->modelNamePlural($modelRelationship);
            $hasMethod = "has$nameModelRelationship";
            $templateDataReal = $this->phpParserService->addNewMethod($templateDataReal, $hasMethod, 5);
            $this->_createFileAll('seeder', $fileName, $templateDataReal);
        }

        // model belongsTo/belongsToMany
        $fileName = "{$modelRelationship}Seeder";
        $templateDataReal = $this->serviceGenerator->getFile('seeder', 'laravel', "$fileName.php");
        if ($templateDataReal) {
            $forMethod = $relationship === $this->relationship['belongs_to_many']
                ? "has{$this->serviceGenerator->modelNamePlural($model)}"
                : "for{$this->serviceGenerator->modelNameNotPlural($model)}";
            $templateDataReal = $this->phpParserService->addNewMethod($templateDataReal, $forMethod, $relationship === $this->relationship['belongs_to_many'] ? 5 : 0);
            $this->_createFileAll('seeder', $fileName, $templateDataReal);
        }
    }

    private function _createFileAll($namePath, $model, $templateDataReal)
    {
        $path = config('generator.path.laravel.'.$namePath);
        $fileName = $path.$model.'.php';
        $this->serviceFile->createFileReal($fileName, $templateDataReal);
    }

    private function _replaceTemplate($fieldsGenerate, $space = 2): string
    {
        return $this->serviceGenerator->infy_nl_tab(1, 2).
            implode($this->serviceGenerator->infy_nl_tab(1, 2), $fieldsGenerate).
            $this->serviceGenerator->infy_nl_tab(1, 3, $space);
    }

    private function _generateSelect($funcName, $field, $column, $relationship): string
    {
        $pathTemplate = 'Forms/';
        if ($relationship === $this->relationship['belongs_to_many']) {
            $formTemplate = $this->serviceGenerator->get_template('selectMTM', $pathTemplate, 'vue');
        } else {
            $formTemplate = $this->serviceGenerator->get_template('select', $pathTemplate, 'vue');
        }
        if ($relationship === $this->relationship['has_one']) {
            $nameModelRelationship = $this->serviceGenerator->modelNameNotPluralFe($funcName);
        } else {
            $nameModelRelationship = $this->serviceGenerator->modelNamePluralFe($funcName);
        }
        $templateFormItem = $this->serviceGenerator->get_template('itemRelationship', 'Forms/', 'vue');
        $templateFormItem = str_replace('{{$LABEL$}}', 't(\'route.'.$funcName.'\')', $templateFormItem);
        $templateFormItem = str_replace('{{$PROP_NAME$}}', $field, $templateFormItem);
        $templateFormItem = str_replace('{{$COLUMNS$}}', self::NUMBER_COLUMN, $templateFormItem);
        $formTemplate = str_replace('{{$FIELD_NAME$}}', $field, $formTemplate);
        $formTemplate = str_replace('{{$LIST_SELECT$}}', $nameModelRelationship, $formTemplate);
        $formTemplate = str_replace('{{$LABEL_OPTION$}}', 'item.'.$column, $formTemplate);
        $formTemplate = str_replace('{{$VALUE_OPTION$}}', 'item.id', $formTemplate);

        return str_replace('{{$COMPONENT$}}', $formTemplate, $templateFormItem);
    }

    private function _getHandlerTemplate(): string
    {
        return $this->serviceGenerator->get_template('rules', 'Handler/', 'vue');
    }

    private function _generateInterfaceCommon($modelCurrent, $model, $relationship)
    {
        $path = config('generator.path.package.model');
        if ($relationship === $this->relationship['belongs_to_many']) {
            $fileName = "/{$this->serviceGenerator->folderPages($modelCurrent)}.{$this->jsType('ext')}";
            $nameColumnRelationship = Str::snake(Str::plural($model));
            $templateDataReal = $this->serviceGenerator->getFile('model', 'package', $fileName);
            $modelIds = Str::snake($model).self::_IDS;
            $templateDataReal = $this->phpParserService->runParserJS(
                $path.$fileName,
                [
                    'key' => 'common.import',
                    'name' => $model,
                    'path' => './index',
                    'interface' => $this->serviceGenerator->modelNameNotPlural($modelCurrent),
                    'items' => [
                        "$modelIds?" => 'number[];',
                        "$nameColumnRelationship?" => "{$model}[];",
                    ],
                ],
                $templateDataReal
            );
            $this->serviceFile->createFileReal($path.$fileName, $templateDataReal);
        } else {
            // hasOne| hasMany
            $fileName = $this->serviceGenerator->folderPages($modelCurrent).".{$this->jsType('ext')}";
            $templateDataReal = $this->serviceGenerator->getFile('model', 'package', $fileName);
            if ($relationship === $this->relationship['has_one']) {
                $nameColumnRelationship = Str::snake($model);
                $items = [
                    "$nameColumnRelationship?" => "$model;",
                ];
            } else {
                $nameColumnRelationship = Str::snake(Str::plural($model));
                $items = [
                    "$nameColumnRelationship?" => "{$model}[];",
                ];
            }
            $templateDataReal = $this->phpParserService->runParserJS(
                $path.$fileName,
                [
                    'key' => 'common.import',
                    'name' => $model,
                    'path' => './index',
                    'interface' => $this->serviceGenerator->modelNameNotPlural($modelCurrent),
                    'items' => $items,
                ],
                $templateDataReal
            );
            $this->serviceFile->createFileReal($path.$fileName, $templateDataReal);
            // belongsTo
            $fileName = $this->serviceGenerator->folderPages($model).".{$this->jsType('ext')}";
            $templateDataReal = $this->serviceGenerator->getFile('model', 'package', $fileName);
            $fieldModelCurrent = Str::snake($modelCurrent).self::_ID;
            $nameColumnRelationship = Str::snake($modelCurrent);
            $templateDataReal = $this->phpParserService->runParserJS(
                $path.$fileName,
                [
                    'key' => 'common.import',
                    'name' => $modelCurrent,
                    'path' => './index',
                    'interface' => $this->serviceGenerator->modelNameNotPlural($model),
                    'items' => [
                        "$fieldModelCurrent?" => 'number | null;',
                        "$nameColumnRelationship?" => "$modelCurrent;",
                    ],
                ],
                $templateDataReal
            );
            $this->serviceFile->createFileReal($path.$fileName, $templateDataReal);
        }
    }

    /**
     * @param  null  $notDelete
     */
    private function _checkImportInterfaceCommon(
        $model,
        $templateDataReal,
        string $from = './index',
        $notDelete = null,
    ): string {
        $notDelete ??= config('generator.not_delete.package.model')['import'];
        if (!preg_match("~\b$model\b~", $templateDataReal)) {
            return $this->serviceGenerator->replaceNotDelete(
                $notDelete,
                "import { $model } from '$from';",
                0,
                $templateDataReal,
                0,
            );
        }

        return $templateDataReal;
    }
}
