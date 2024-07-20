<?php

namespace LaraJS\Core\Generators\Backend;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Support\Str;
use LaraJS\Core\Generators\BaseGenerator;

class RelationshipGenerator extends BaseGenerator
{
    public const _ID = '_id';

    public const _IDS = '_ids';

    public const NUMBER_COLUMN = 12;

    protected array $relationship;

    public function __construct($relationship, $model, $modelCurrent, $column, $column2, $options, $modelName, $columnChildren = '', $ignoreMigrate = false)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.migration');
        $this->notDelete = config('generator.not_delete.laravel.model');
        $this->relationship = config('generator.relationship.relationship');

        if (!$columnChildren) {
            $columnChildren = Str::snake($modelCurrent).self::_ID;
        }

        return $this->_generate($relationship, $model, $modelCurrent, $column, $column2, $options, $modelName, $columnChildren, $ignoreMigrate);
    }

    private function _generate($relationship, $model, $modelCurrent, $column, $column2, $options, $modelName, $columnChildren, $ignoreMigrate): string
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
        $templateModel = str_replace(['{{RELATION}}', '{{RELATION_FUNCTION}}', '{{RELATION_MODEL_CLASS}}'], [ucfirst($relationship), $relationship, $model], $templateModel);
        //ModelCurrent Relationship

        $templateInverse = str_replace('{{RELATION_MODEL_CLASS}}', $modelCurrent, $templateInverse);
        if ($relationship === $this->relationship['belongs_to_many']) {
            $relationshipInverse = 'BelongsToMany';
            $templateInverse = str_replace(['{{RELATION}}', '{{RELATION_FUNCTION}}'], [$relationshipInverse, lcfirst($relationshipInverse)], $templateInverse);
            $templateModel = str_replace(
                '{{FIELD_RELATIONSHIP}}',
                "'".
                $this->serviceGenerator->tableName($modelName).
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
                $this->serviceGenerator->tableName($modelName).
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
                "'".$columnChildren."'",
                $templateModel,
            );
            $templateInverse = str_replace(
                '{{FIELD_RELATIONSHIP}}',
                "'".$columnChildren."'",
                $templateInverse,
            );
            $relationshipInverse = 'BelongsTo';
            $templateInverse = str_replace(['{{RELATION}}', '{{RELATION_FUNCTION}}'], [$relationshipInverse, lcfirst($relationshipInverse)], $templateInverse);
        }
        $migrateFile = $this->_migrateRelationship($relationship, $model, $modelCurrent, $column, $column2, $options, $modelName, $columnChildren, $ignoreMigrate);
        //replace file model real
        $templateModelReal = $this->serviceGenerator->getFile('model', 'laravel', $model.'.php');
        $templateModelReal = $this->phpParserService->usePackage($templateModelReal, "Illuminate\Database\Eloquent\Relations\\$relationshipInverse");
        $this->_replaceFile($model, $templateInverse, $templateModelReal);
        //replace file model current real
        $templateModelCurrentReal = $this->serviceGenerator->getFile('model', 'laravel', $modelCurrent.'.php');
        $templateModelCurrentReal = $this->phpParserService->usePackage($templateModelCurrentReal, "Illuminate\Database\Eloquent\Relations\\" . ucfirst($relationship));
        $this->_replaceFile($modelCurrent, $templateModel, $templateModelCurrentReal);

        return $migrateFile;
    }

    private function _migrateRelationship($relationship, $model, $modelCurrent, $column, $column2, $options, $modelName, $columnChildren, $ignoreMigrate): string
    {
        $pathTemplate = 'Databases/Migrations/';
        $templateData = $this->serviceGenerator->get_template('migrationRelationship', $pathTemplate);
        if ($relationship === $this->relationship['belongs_to_many']) {
            //belongsToMany
            $templateData = $this->serviceGenerator->get_template('migrationRelationshipMTM', $pathTemplate);
            //if belongsToMany replace table to create
            $templateData = $this->_replaceTemplateRelationshipMTM($model, $modelCurrent, $templateData, $modelName);
            $fileName = date('Y_m_d_His')."_relationship_{$this->serviceGenerator->tableName($modelName)}_table.php";
            $this->_generateDocs($model, $modelCurrent, $relationship);
            $this->_generateDocs($modelCurrent, $model, $relationship);
            $this->_generateModelMTM($model, $modelCurrent, $modelName);
            $this->_generateSeeder($modelCurrent, $model, $relationship);
            $this->_generateRequest($model, $relationship, Str::snake($modelCurrent) . self::_IDS);
            $this->_generateRequest($modelCurrent, $relationship, Str::snake($model) . self::_IDS);
            $this->_generateObserver($modelCurrent, $model);
            $this->_generateObserver($model, $modelCurrent);
            //generate frontend
            $this->_generateFormFe($modelCurrent, $model, $column, $options, $relationship, Str::snake($modelCurrent).self::_ID);
            $this->_generateFormFe($model, $modelCurrent, $column2, $options, $relationship, Str::snake($model).self::_ID);
            if (!$this->jsType()) {
                $this->_generateInterfaceCommon($modelCurrent, $model, $relationship, Str::snake($model).self::_IDS);
                $this->_generateInterfaceCommon($model, $modelCurrent, $relationship, Str::snake($modelCurrent).self::_IDS);
            }
        } else {
            //hasOne or hasMany
            $templateData = $this->_replaceTemplateRelationship($model, $modelCurrent, $templateData, $columnChildren);
            $fileName =
                date('Y_m_d_His').
                '_relationship_'.
                $this->serviceGenerator->tableName($modelCurrent).
                '_'.
                $this->serviceGenerator->tableName($model).
                '_table.php';
            $this->_generateModel($model, $columnChildren);
            $this->_generateDocs($modelCurrent, $model, $relationship);
            $this->_generateDocs($model, $modelCurrent, $relationship === $this->relationship['has_one'] ? $this->relationship['has_many'] : $this->relationship['has_one']); // reserve
            $this->_generateFactory($modelCurrent, $model, $columnChildren);
            $this->_generateSeeder($modelCurrent, $model, $relationship);
            $this->_generateRequest($model, $relationship, $columnChildren);
            //generate frontend
            $this->_generateFormFe($modelCurrent, $model, $column, $options, $relationship, $columnChildren);
            if (!$this->jsType()) {
                $this->_generateInterfaceCommon($modelCurrent, $model, $relationship, $columnChildren);
            }
        }
        if (!$ignoreMigrate) {
            $this->serviceFile->createFile($this->path, $fileName, $templateData);
        }

        return $fileName;
    }

    private function _generateFactory(string $model, string $modelRelationship, string $columnChildren): void
    {
        $templateDataReal = $this->serviceGenerator->getFile('factory', 'laravel', "{$modelRelationship}Factory.php");
        $templateDataReal = $this->phpParserService->addFakerToFactory($templateDataReal, [
            [
                'field_name' => $columnChildren,
                'db_type' => 'FOREIGN_KEY',
                'model' => $model,
            ],
        ]);
        $this->_createFileAll('factory', "{$modelRelationship}Factory", $templateDataReal);
    }

    private function _generateDocs(string $model, string $modelRelationship, string $relationship): void
    {
        $templateDataReal = $this->serviceGenerator->getFile('api_controller', 'laravel', "{$model}Controller.php");
        if (!$templateDataReal) {
            return;
        }
        $relationship =
            $relationship === $this->relationship['has_one']
                ? $this->serviceGenerator->modelNameNotPluralFe($modelRelationship)
                : $this->serviceGenerator->modelNamePluralFe($modelRelationship);
        $templateDataReal = $this->phpParserService->addItemForAttribute($templateDataReal, $relationship, 'with');
        $this->_createFileAll('api_controller', "{$model}Controller", $templateDataReal);
    }

    private function _generateFormFe($model, $modelRelationship, $columnRelationship, $options, $relationship, $columnChildren)
    {
        $notDelete = config('generator.not_delete.vue.form');
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
            $templateRules = str_replace(['{{$FIELD$}}', '{{$ATTRIBUTE_FIELD$}}', '{{$TRIGGER$}}'], [$columnChildren, 't(\'route.' . Str::snake($model) . '\')', 'change'], $templateRules);
            $templateDataReal = $this->phpParserService->runParserJS("$path/{$this->jsType('form')}", [
                'key' => 'uses.form:rules',
                'variable' => $this->serviceGenerator->modelNameNotPluralFe($modelRelationship).'Rules',
                'items' => [
                    $columnChildren => $templateRules,
                ],
            ]);
        }
        $field = $isMTM ? "{$columnChildren}s" : $columnChildren;
        $tableFunctionRelationship = $isMTM
            ? $this->serviceGenerator->tableName($model)
            : $this->serviceGenerator->tableNameNotPlural($model);
        $templateDataReal = $this->phpParserService->runParserJS("$path/{$this->jsType('form')}", [
            'key' => 'uses.form:item',
            'variable' => 'form',
            'items' => [
                $field => [
                    'type' => $isMTM ? 'array' : 'null',
                    'value' => [],
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
        $templateDataReal = $this->phpParserService->runParserJS("$path/{$this->jsType('form')}", [
            'key' => 'uses.form:items',
            'items' => [
                $this->_generateSelect(
                    Str::snake($model),
                    $field,
                    $columnRelationship,
                    $relationship,
                ),
            ],
        ], $templateDataReal);
        $this->serviceFile->createFileReal("$path/{$this->jsType('form')}", $templateDataReal);
        // create column: table.tsx
        $configOptions = config('generator.relationship.options');
        if (in_array($configOptions['show'], $options)) {
            $templateDataReal = $this->serviceGenerator->getFile('uses', 'vue', "/$folderName/{$this->jsType('table')}");
            $templateColumn = $this->serviceGenerator->get_template('column', 'Forms/', 'vue');
            $templateColumn = str_replace(['{{$FIELD_NAME$}}', '{{$FORM_SORTABLE$}}', '{{$FORM_ALIGN$}}', '{{$FORM_LABEL$}}'], [Str::camel($tableFunctionRelationship) . ".$columnRelationship", in_array($configOptions['sort'], $options) ? "'custom'" : 'false', 'left', "label: t('route.{$this->serviceGenerator->tableNameNotPlural($model)}'),"], $templateColumn);
            if ($isMTM) {
                $templateRow = <<<TEMPLATE
                template: ({ row }) => row.$tableFunctionRelationship.map(item => <el-tag key={item.id}>{item.$columnRelationship}</el-tag>),
                TEMPLATE;
            } else {
                $templateRow = <<<TEMPLATE
                template: ({ row }) => row.$tableFunctionRelationship?.$columnRelationship,
                TEMPLATE;
            }
            $templateColumn = str_replace('{{$FORM_TEMPLATE$}}', $templateRow, $templateColumn);
            $templateDataReal = $this->phpParserService->runParserJS("$path/{$this->jsType('table')}", [
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
        //generate form item
        $this->_generateFormItem($model, $modelRelationship, $isMTM);
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
                'key' => 'uses.table:include',
                'items' => [$withRelationship],
            ], $templateDataReal);
        }
        if (in_array($configOptions['search'], $options)) {
            $columnDidGenerate = Str::camel($model).".$columnRelationship";
            $templateDataReal = $this->phpParserService->runParserJS("$path/{$this->jsType('table')}", [
                'key' => 'uses.table:search:column',
                'items' => [$columnDidGenerate],
            ], $templateDataReal);
        }

        return $templateDataReal;
    }

    //create form item
    private function _generateFormItem($model, $modelRelationship, $isMTM): void
    {
        // edit
        if ($isMTM) {
            $fileName = "{$this->serviceGenerator->folderPages($modelRelationship)}/Form.vue";
            $pathFile = config('generator.path.vue.views').$fileName;
            $stubGetData =
                'form.{{$FIELD_NAME$}} = {{$MODEL_RELATIONSHIP$}}.{{$FIELD_RELATIONSHIP$}}.map(item => item.id);';
            $stubGetData = str_replace(
                ['{{$FIELD_NAME$}}', '{{$MODEL_RELATIONSHIP$}}', '{{$FIELD_RELATIONSHIP$}}'],
                [
                    $this->serviceGenerator->tableNameNotPlural($model) . self::_IDS,
                    $this->serviceGenerator->modelNameNotPluralFe($modelRelationship),
                    Str::snake($this->serviceGenerator->modelNamePluralFe($model)),
                ],
                $stubGetData
            );
            $templateDataReal = $this->phpParserService->runParserJS($pathFile, [
                'key' => 'views.form:edit',
                'content' => $stubGetData,
                'relationFunction' => $this->serviceGenerator->modelNamePluralFe($model),
            ]);

            $this->serviceFile->createFileReal($pathFile, $templateDataReal);
        }
    }

    private function _generateAddApi($model, $modelRelationship, $templateDataReal, $notDelete, $relationship): string
    {
        $path = config('generator.path.vue.uses');
        $folderName = $this->serviceGenerator->folderPages($modelRelationship);
        $fileName = "{$this->serviceGenerator->folderPages($modelRelationship)}/Form.vue";
        $pathFile = config('generator.path.vue.views').$fileName;
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
                'interface' => "{$modelRelationship}StateRoot",
                'items' => [
                    $nameModelRelationship => "{$model}[];",
                ],
            ], $templateDataReal);
        }
        // form
        $templateDataRealForm = $this->serviceGenerator->getFile('views', 'vue', $fileName);
        $useModel = "use{$this->serviceGenerator->modelNamePlural($model)}";
        $templateDataRealForm = $this->phpParserService->runParserJS($pathFile, [
            'key' => 'views.form:import',
            'name' => $useModel,
            'path' => "{$this->getImportJsOrTs()}/uses",
            'useName' => $useModel,
            'useKey' => "list: {$this->serviceGenerator->modelNameNotPluralFe($model)}List",
        ], $templateDataRealForm);
        $stubGetData = $this->serviceGenerator->get_template('getDataRelationship', 'Handler/', 'vue');
        $stubGetData = str_replace(
            '{{$USE_MODEL_RELATIONSHIP$}}',
            "{$this->serviceGenerator->modelNameNotPluralFe($model)}List",
            $stubGetData,
        );
        $stubGetData = str_replace('{{$MODEL_RELATIONSHIP$}}', $nameModelRelationship, $stubGetData);
        $templateDataRealForm = $this->phpParserService->runParserJS($pathFile, [
            'key' => 'views.form:create',
            'content' => $stubGetData,
        ], $templateDataRealForm);

        $this->serviceFile->createFileReal($pathFile, $templateDataRealForm);

        return $templateDataReal;
    }

    private function _generateRequest($model, $relationship, $field)
    {
        $notDelete = config('generator.not_delete.laravel.request');
        $fileNameFunc = "Store{$model}Request.php";
        $templateDataRealFunc = $this->serviceGenerator->getFile('request', 'laravel', $fileNameFunc);
        if (!$templateDataRealFunc) {
            return;
        }
        $isMTM = $relationship === $this->relationship['belongs_to_many'];
        $rule = $isMTM ? "['array']" : "['integer', 'required']";
        $templateDataRealFunc = $this->serviceGenerator->replaceNotDelete(
            $notDelete['rule'],
            "'".$field."'".' => '."$rule,",
            3,
            $templateDataRealFunc,
        );
        $fileNameFunc = config('generator.path.laravel.request').$fileNameFunc;
        $this->serviceFile->createFileReal($fileNameFunc, $templateDataRealFunc);
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
            '{{MODEL_CLASS_PARAM}}',
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
            '{{MODEL_CLASS_PARAM}}',
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
            '{{MODEL_CLASS_PARAM}}',
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
        $fileName = "$model.php";
        $templateDataRegisterEvent = $this->serviceGenerator->getFile('model', 'laravel', $fileName);
        if (!$templateDataRegisterEvent) {
            return;
        }
        $templateDataRegisterEvent = $this->phpParserService->usePackage($templateDataRegisterEvent, ObservedBy::class);
        $templateDataRegisterEvent = $this->phpParserService->usePackage($templateDataRegisterEvent, "App\Observers\\{$model}Observer");
        $templateDataRegisterEvent = $this->phpParserService->addAttribute($templateDataRegisterEvent, 'ObservedBy', "{$model}Observer");
        $pathProvider = config('generator.path.laravel.model');
        $this->serviceFile->createFileReal("$pathProvider/$fileName", $templateDataRegisterEvent);
    }

    private function _replaceFile($model, $templateModel, $templateReal)
    {
        $templateReal = $this->serviceGenerator->replaceEndFile($templateReal, $templateModel, 1);
        $this->serviceFile->createFileReal(config('generator.path.laravel.model')."$model.php", $templateReal);
    }

    private function _replaceTemplateRelationship($model, $modelDif, $templateData, $columnChildren): string
    {
        $templateData = str_replace('{{TABLE_NAME}}', $this->serviceGenerator->tableName($model), $templateData);
        $templateData = str_replace('{{FOREIGN_KEY}}', $columnChildren, $templateData);

        return str_replace('{{TABLE_FOREIGN_KEY}}', $this->serviceGenerator->tableName($modelDif), $templateData);
    }

    private function _replaceTemplateRelationshipMTM($model, $modelCurrent, $templateData, $modelName): string
    {
        $templateData = str_replace(
            '{{TABLE_NAME}}',
            $this->serviceGenerator->tableName($modelName),
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

    private function _generateModel($modelRelationship, $columnChildren): void
    {
        $templateDataReal = $this->serviceGenerator->getFile('model', 'laravel', $modelRelationship.'.php');
        if (!$templateDataReal) {
            return;
        }
        $templateDataReal = $this->phpParserService->addStringToArray($templateDataReal, $columnChildren, 'fillable');
        $this->_createFileAll('model', $modelRelationship, $templateDataReal);
    }

    private function _generateModelMTM($model, $modelCurrent, $modelName): void
    {
        $fieldModel = Str::snake($model).self::_ID;
        $fieldModelCurrent = Str::snake($modelCurrent).self::_ID;
        $pathTemplate = 'Models/';
        $templateData = $this->serviceGenerator->get_template('model', $pathTemplate);
        $templateData = str_replace('{{MODEL_CLASS}}', $modelName, $templateData);
        $templateData = str_replace(['//{{USE_CLASS}}', '//{{USE}}', '//{{TIMESTAMPS}}'], '', $templateData);
        $arFields = ["'".$fieldModel."',", "'".$fieldModelCurrent."',"];
        $implodeFields = implode($this->serviceGenerator->infy_nl_tab(1, 2), $arFields);
        $templateData = str_replace('{{FIELDS}}', $implodeFields, $templateData);
        $templateData = str_replace(
            '{{TABLE_NAME}}',
            $this->serviceGenerator->tableName($modelName),
            $templateData,
        );
        $templateData = str_replace('{{CATS}}', '', $templateData);
        $path = config('generator.path.laravel.model');
        $this->serviceFile->createFile($path, "$modelName.php", $templateData);
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
            $templateDataReal = $this->phpParserService->addNewMethod($templateDataReal, $hasMethod, 1);
            $this->_createFileAll('seeder', $fileName, $templateDataReal);
        }

        // model belongsTo/belongsToMany
        $fileName = "{$modelRelationship}Seeder";
        $templateDataReal = $this->serviceGenerator->getFile('seeder', 'laravel', "$fileName.php");
        if ($templateDataReal) {
            $forMethod = $relationship === $this->relationship['belongs_to_many']
                ? "has{$this->serviceGenerator->modelNamePlural($model)}"
                : "for{$this->serviceGenerator->modelNameNotPlural($model)}";
            $templateDataReal = $this->phpParserService->addNewMethod($templateDataReal, $forMethod, $relationship === $this->relationship['belongs_to_many'] ? 1 : 0);
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

    private function _generateInterfaceCommon($modelCurrent, $model, $relationship, $field)
    {
        $path = config('generator.path.package.model');
        if ($relationship === $this->relationship['belongs_to_many']) {
            $fileName = "/{$this->serviceGenerator->folderPages($modelCurrent)}.{$this->jsType('ext')}";
            $nameColumnRelationship = Str::snake(Str::plural($model));
            $templateDataReal = $this->serviceGenerator->getFile('model', 'package', $fileName);
            $modelIds = $field;
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
            $fieldModelCurrent = $field;
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
}
