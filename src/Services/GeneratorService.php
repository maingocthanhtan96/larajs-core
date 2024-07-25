<?php

namespace LaraJS\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Exception\UnexpectedValueException;

class GeneratorService
{
    /**
     * Generates tab with spaces.
     *
     * @param  int  $spaces
     * @return string
     */
    public function infy_tab($spaces = 4)
    {
        return str_repeat(' ', $spaces);
    }

    /**
     * Generates tab with spaces.
     *
     * @param  int  $tabs
     * @param  int  $spaces
     * @return string
     */
    public function infy_tabs($tabs, $spaces = 4)
    {
        return str_repeat($this->infy_tab($spaces), $tabs);
    }

    /**
     * Generates new line char.
     *
     * @param  int  $count
     * @return string
     */
    public function infy_nl($count = 1)
    {
        return str_repeat(PHP_EOL, $count);
    }

    /**
     * Generates new line char.
     *
     * @param  int  $count
     * @param  int  $nls
     * @return string
     */
    public function infy_nls($count, $nls = 1)
    {
        return str_repeat($this->infy_nl($nls), $count);
    }

    /**
     * Generates new line char.
     */
    public function infy_nl_tab(int $lns = 1, int $tabs = 1, int $spaces = 4): string
    {
        return $this->infy_nls($lns).$this->infy_tabs($tabs, $spaces);
    }

    /**
     * get path for template file.
     */
    public function get_template_file_path(
        string $templateName,
        string $templatePath,
        string $typeTemplate = 'laravel',
    ): bool|string {
        if ($typeTemplate === 'laravel') {
            $templatesPath = config('generator.template.laravel');
        } elseif ($typeTemplate === 'package') {
            $templatesPath = config('generator.template.package');
        } else {
            $templatesPath = config('generator.template.vue').config('generator.js_language').'/';
        }
        $path = $templatesPath.$templatePath.$templateName.'.stub';
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }

    /**
     * get path for file.
     *
     * @param  string  $nameConfig
     * @param  string  $type
     * @param  string  $fileName
     * @return string
     */
    public function getFilePath($nameConfig, $type = 'laravel', $fileName = '')
    {
        if ($type === 'laravel') {
            $path = config('generator.path.laravel.'.$nameConfig);
        } elseif ($type === 'package') {
            $path = config('generator.path.package.'.$nameConfig);
        } else {
            $path = config('generator.path.vue.'.$nameConfig);
        }
        if ($fileName) {
            $path .= $fileName;
        }
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }

    /**
     * get file.
     *
     * @param  string  $type  laravel|vue|package
     * @return string
     */
    public function getFile(string $nameConfig, string $type = 'laravel', string $fileName = '')
    {
        $path = $this->getFilePath($nameConfig, $type, $fileName);

        return file_exists($path) ? file_get_contents($path) : '';
    }

    /**
     * get path for file.
     */
    public function getFilePathReal(string $fileName = '', string $type = 'laravel'): bool|string
    {
        if ($type === 'laravel') {
            $path = config('generator.path.laravel');
        } else {
            $path = config('generator.path.vue.resource_js');
        }
        if ($fileName) {
            $path .= $fileName;
        }
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }

    /**
     * get template contents.
     */
    public function get_template(string $templateName, string $templatePath, string $typeTemplate = 'laravel'): string
    {
        $path = $this->get_template_file_path($templateName, $templatePath, $typeTemplate);

        return file_get_contents($path);
    }

    /**
     * fill template with variable values.
     *
     * @param  array  $variables
     * @param  string  $template
     * @return string
     */
    public function fill_template($variables, $template)
    {
        foreach ($variables as $variable => $value) {
            $template = str_replace($variable, $value, $template);
        }

        return $template;
    }

    /**
     * fill field template with variable values.
     *
     * @param  array  $variables
     * @param  string  $template
     * @param  \InfyOm\Generator\Common\GeneratorField  $field
     * @return string
     */
    public function fill_field_template($variables, $template, $field)
    {
        foreach ($variables as $variable => $key) {
            $template = str_replace($variable, $field->$key, $template);
        }

        return $template;
    }

    /**
     * generates model name from table name.
     *
     * @param  string  $tableName
     * @return string
     */
    public function modelNamePlural($tableName)
    {
        return ucfirst(\Str::camel(\Str::plural($tableName)));
    }

    /**
     * generates model name from table name.
     *
     * @param  string  $tableName
     * @return string
     */
    public function modelNameNotPlural($tableName)
    {
        return ucfirst(\Str::camel($tableName));
    }

    /**
     * generates model name from table name frontend.
     */
    public function modelNameNotPluralFe(string $tableName): string
    {
        return \Str::camel($tableName);
    }

    public function modelNameSingular(string $tableName): string
    {
        return \Str::camel(\Str::singular($tableName));
    }

    public function modelNameRouteParamSingular(string $tableName): string
    {
        return \Str::snake(\Str::singular($tableName));
    }

    public function modelNameTitle(string $tableName): string
    {
        return \Str::of($tableName)->snake(' ')->title()->lower();
    }

    /**
     * generates model name from table name frontend.
     *
     * @param  string  $tableName
     * @return string
     */
    public function modelNamePluralFe($tableName)
    {
        return \Str::camel(\Str::plural($tableName));
    }

    /**
     * generates model name from table name.
     *
     * @param  string  $tableName
     * @return string
     */
    public function urlResource($tableName)
    {
        return lcfirst(\Str::kebab(\Str::plural($tableName)));
    }

    /**
     * generates folder name from model name.
     *
     * @param  string  $tableName
     * @return string
     */
    public function folderPages($tableName)
    {
        return lcfirst(\Str::kebab($tableName));
    }

    /**
     * generates folder name from model name.
     *
     * @param  string  $tableName
     * @return string
     */
    public function nameAttribute($tableName)
    {
        return lcfirst(\Str::kebab($tableName));
    }

    /**
     * generates model name from table name.
     */
    public function tableName(string $name): string
    {
        $name = $this->tableNameHandle($name);

        return \Str::snake(\Str::plural($name));
    }

    /**
     * generates model name from table name.
     */
    public function tableNameNotPlural(string $name): string
    {
        $name = $this->tableNameHandle($name);

        return \Str::snake($name);
    }

    /**
     * @return array|string|array<string>|null
     */
    public function tableNameHandle($name)
    {
        return preg_replace_callback(
            '/(?:[A-Z]+)(?![a-z])/',
            function ($matches) {
                foreach ($matches as $match) {
                    return ucfirst(strtolower($match));
                }
            },
            $name,
        );
    }

    /**
     * check options.
     *
     * @return string
     */
    public function getOptions(string $name, array $options)
    {
        return in_array($name, $options);
    }

    /**
     * Replace comment not delete.
     *
     * @param  string  $noteDelete
     * @param  string  $replace
     * @param  number  $tab
     * @param  string  $templateDataReal
     * @param  int  $spaces
     * @return string
     */
    public function replaceNotDelete($noteDelete, $replace, $tab, $templateDataReal, $spaces = 4)
    {
        return str_replace(
            $noteDelete,
            $replace.$this->infy_nl_tab(1, $tab, $spaces).$noteDelete,
            $templateDataReal,
        );
    }

    /**
     * search string template.
     *
     * @param  number  $plusStart
     * @param  number  $plusEnd
     */
    public function searchTemplate(
        string $search,
        string $char,
        $plusStart,
        $plusEnd,
        string $templateDataReal,
        string $searchOther = '',
    ): bool|string {
        if (!$searchOther) {
            $searchOther = $search;
        }
        if (strpos($templateDataReal, $searchOther)) {
            $template = substr($templateDataReal, stripos($templateDataReal, $search));
            $length = stripos($template, $char);

            return substr($templateDataReal, stripos($templateDataReal, $search) + $plusStart, $length + $plusEnd);
        }

        return false;
    }

    /**
     * Get relationship on model
     */
    public function getDiagram(?string $model): array
    {
        $modelData = [];
        if ($model) {
            if (!in_array($model, config('generator.relationship.ignore_model'))) {
                $modelData[] = [
                    'model' => $model,
                    'relationships' => $this->getRelationships(app("\\App\\Models\\$model")),
                ];
            }
        } else {
            $modelNames = $this->getModelsNames(app_path('Models'));
            foreach ($modelNames as $modelName) {
                $model = class_basename($modelName);
                if (!in_array($model, config('generator.relationship.ignore_model'))) {
                    $modelData[] = [
                        'model' => $model,
                        'relationships' => $this->getRelationships(app($modelName)),
                    ];
                }
            }
        }

        return $modelData;
    }

    public function getModelsNames(string $modelsPath): Collection
    {
        return collect(\File::allFiles($modelsPath))
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                $namespace = $this->extractNamespace($item->getRealPath()).'\\';

                return sprintf('\%s%s', $namespace, strtr(substr($path, 0, strrpos($path, '.')), '/', '\\'));
            })
            ->filter(function ($class) {
                $valid = false;

                if (class_exists($class)) {
                    $reflection = new ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) && !$reflection->isAbstract();
                }

                return $valid;
            });
    }

    /**
     * Trim quotes
     *
     * @return string
     */
    public function trimQuotes($string)
    {
        $string = trim($string, "'");

        return trim($string, '"');
    }

    private function extractNamespace($file)
    {
        $ns = null;
        $handle = fopen($file, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (str_starts_with($line, 'namespace')) {
                    $parts = explode(' ', $line);
                    $ns = rtrim(trim($parts[1]), ';');
                    break;
                }
            }
            fclose($handle);
        }

        return $ns;
    }

    /**
     * Relationships
     *
     * @return array of relationships
     */
    private function getRelationships(Model $model): array
    {
        $relationships = [];
        $model = new $model();

        foreach ((new ReflectionClass($model))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (
                $method->class != get_class($model) ||
                !empty($method->getParameters()) ||
                $method->getName() == __FUNCTION__
            ) {
                continue;
            }

            try {
                $return = $method->invoke($model);
                // check if not instance of Relation
                if (!($return instanceof Relation)) {
                    continue;
                }
                $relationType = (new ReflectionClass($return))->getShortName();
                $modelName = (new ReflectionClass($return->getRelated()))->getName();

                $foreignKey = $return->getQualifiedForeignKeyName();
                $parentKey = $return->getQualifiedParentKeyName();
                $relationships[$method->getName()] = [
                    'type' => $relationType,
                    'model' => $modelName,
                    'foreign_key' => $foreignKey,
                    'parent_key' => $parentKey,
                ];
            } catch (QueryException|\TypeError|\Throwable $e) {
                // ignore
            }
        }

        return $relationships;
    }

    // START - MIGRATION
    public function migrationFields($field, $configDBType, $typeDB, $typeLaravel): string
    {
        return match ($field['db_type']) {
            $configDBType['enum'] => "\$table->enum('{$field['field_name']}', " . json_encode($field['enum']) . ')',
            $configDBType['hasOne'], $configDBType['hasMany'] => "\$table->foreignId('{$field['field_name']}')",
            default => $field['db_type'] === $typeDB ? "\$table->{$typeLaravel}('" . trim($field['field_name']) . "')" : '',
        };
    }

    public function migrationDefaultValue($field, $configDefaultValue): string
    {
        return match ($field['default_value']) {
            $configDefaultValue['null'] => '->nullable()',
            $configDefaultValue['as_define'] => '->default(' . (is_numeric($field['as_define']) ? $field['as_define'] : '"' . $field['as_define'] . '"') . ')',
            $configDefaultValue['current_timestamps'] => '->useCurrent()',
            default => '',
        };
    }

    public function migrationOption($field): string
    {
        $table = '';
        if ($field['options']['comment']) {
            $table .= '->comment("'.$field['options']['comment'].'")';
        }
        if ($field['options']['unique']) {
            $table .= '->unique()';
        }
        if ($field['options']['index']) {
            $table .= '->index()';
        }

        return $table;
    }
    // END - MIGRATION

    public function formFeGenerateField(): object
    {
        return new class extends GeneratorService
        {
            public function generateBoolean($tableName, $field): string
            {
                $formTemplate = $this->_getFormTemplate('switch');
                $formTemplate = $this->_replaceLabelForm($tableName, $field, $formTemplate);
                $formTemplate = $this->_checkRequired($field, $formTemplate);

                return $this->_replaceFormField($field, $formTemplate);
            }

            public function generateDateTime($fileName, $tableName, $field): string
            {
                $formTemplate = $this->_getFormTemplate($fileName);
                $formTemplate = $this->_replaceLabelForm($tableName, $field, $formTemplate);
                $formTemplate = $this->_checkRequired($field, $formTemplate);

                return $this->_replaceFormField($field, $formTemplate);
            }

            public function generateInput($fileName, $tableName, $field, $index = 0, $dbType = ''): string
            {
                $dbTypeConfig = config('generator.db_type');
                $formTemplate = $this->_getFormTemplate($fileName);
                $formTemplate = $this->_replaceLabelForm($tableName, $field, $formTemplate);
                $formTemplate = $this->_checkRequired($field, $formTemplate);
                $formTemplate = $this->_replaceAutoFocus($index, $formTemplate);
                $formTemplate = $this->_replaceFormField($field, $formTemplate);
                if (in_array($dbType, [$dbTypeConfig['string'], $dbTypeConfig['char']], false)) {
                    $formTemplate = str_replace('{{MAX_LENGTH}}', $field['length_varchar'], $formTemplate);
                }

                return $formTemplate;
            }

            public function generateTinymce($tableName, $field): string
            {
                $formTemplate = $this->_getFormTemplate('tinymce');
                $formTemplate = $this->_replaceLabelForm($tableName, $field, $formTemplate);
                $formTemplate = $this->_checkRequired($field, $formTemplate);

                return $this->_replaceFormField($field, $formTemplate);
            }

            public function generateEnum($tableName, $field): string
            {
                $formTemplate = $this->_getFormTemplate('select');
                $formTemplate = $this->_replaceLabelForm($tableName, $field, $formTemplate);
                $formTemplate = $this->_checkRequired($field, $formTemplate);
                $formTemplate = $this->_replaceFormField($field, $formTemplate);
                $formTemplate = str_replace(
                    '{{$LIST_SELECT$}}',
                    $this->modelNameNotPluralFe($field['field_name']).'Options',
                    $formTemplate,
                );
                $formTemplate = str_replace('{{$LABEL_OPTION$}}', 'item', $formTemplate);

                return str_replace('{{$VALUE_OPTION$}}', 'item', $formTemplate);
            }

            public function generateJson($tableName, $field): string
            {
                $formTemplate = $this->_getFormTemplate('json');
                $formTemplate = $this->_replaceLabelForm($tableName, $field, $formTemplate);
                $formTemplate = $this->_checkRequired($field, $formTemplate);
                $formTemplate = $this->_replaceFormField($field, $formTemplate);

                return str_replace('{{$REF_JSON$}}', $this->modelNameNotPluralFe($field['field_name']), $formTemplate);
            }

            private function _getFormTemplate($nameForm): string
            {
                return $this->get_template($nameForm, 'Forms/', 'vue');
            }

            private function _checkRequired($field, $formTemplate): string
            {
                return str_replace('{{$PROP_NAME$}}', 'prop="'.$field['field_name'].'"', $formTemplate);
            }

            private function _replaceLabelForm($tableName, $field, $formTemplate): string
            {
                return str_replace(
                    '{{$LABEL_NAME_INPUT$}}',
                    '$t(\'table.'."$tableName.{$field['field_name']}')",
                    $formTemplate,
                );
            }

            private function _replaceAutoFocus($index, $formTemplate): string
            {
                return $index === 1
                    ? str_replace('{{$AUTO_FOCUS_INPUT$}}', 'autofocus', $formTemplate)
                    : str_replace('{{$AUTO_FOCUS_INPUT$}}', '', $formTemplate);
            }

            private function _replaceFormField($field, $formTemplate): string
            {
                return str_replace('{{$FIELD_NAME$}}', $field['field_name'], $formTemplate);
            }
        };
    }
    // END - FORM

    // START - REQUEST
    public function requestField(array $field): string
    {
        // Retrieve configuration values
        $dbType = config('generator.db_type');
        $configDefaultValue = config('generator.default_value');
        // Determine if the field is required or nullable
        $required = $field['default_value'] === $configDefaultValue['none'] ? 'required' : 'nullable';
        // Determine the rules based on the database type
        $rules = match ($field['db_type']) {
            $dbType['integer'],
            $dbType['smallInteger'],
            $dbType['tinyInteger'],
            $dbType['mediumInteger'],
            $dbType['bigInteger'],
            $dbType['float'],
            $dbType['double'] => "['$required', 'numeric']",
            $dbType['decimal'] => "['$required', 'numeric', 'between:-999999.99,999999.99']",
            $dbType['unsignedInteger'],
            $dbType['unsignedTinyInteger'],
            $dbType['unsignedSmallInteger'],
            $dbType['unsignedMediumInteger'],
            $dbType['unsignedBigInteger'] => "['$required', 'min:0', 'numeric']",
            $dbType['boolean'] => "['$required', 'boolean']",
            $dbType['date'] => "['$required', 'date_format:Y-m-d']",
            $dbType['dateTime'],
            $dbType['timestamp'] => "['$required', 'date_format:Y-m-d H:i:s']",
            $dbType['time'] => "['$required', 'date_format:H:i:s']",
            $dbType['year'] => "['$required', 'date_format:Y']",
            $dbType['char'],
            $dbType['string'],
            $dbType['tinyText'] => "['$required', 'string', 'max:{$field['length_varchar']}']",
            $dbType['mediumText'],
            $dbType['text'],
            $dbType['longText'] => "['$required', 'string']",
            $dbType['enum'] => "['$required','in:".implode(',', $field['enum'])."']",
            $dbType['json'],
            $dbType['jsonb'] => "['$required', 'json']",
            default => throw new UnexpectedValueException('Unknown database type'),
        };

        // Return the formatted string
        return "'{$field['field_name']}' => $rules,";
    }
    // END - REQUEST

    // START - SEEDER
    public function seederField(array $field): string
    {
        $dbType = config('generator.db_type');
        $fieldName = "'" . $field['field_name'] . "'";

        return match ($field['db_type']) {
            $dbType['integer'],
            $dbType['unsignedInteger'],
            $dbType['tinyInteger'],
            $dbType['unsignedTinyInteger'],
            $dbType['smallInteger'],
            $dbType['unsignedSmallInteger'],
            $dbType['mediumInteger'],
            $dbType['unsignedMediumInteger'],
            $dbType['bigInteger'],
            $dbType['unsignedBigInteger'] => "$fieldName => \$faker->numberBetween(0, 100),",
            $dbType['float'],
            $dbType['double'],
            $dbType['decimal'] => "$fieldName => \$faker->randomFloat(2, 1, 1000),",
            $dbType['boolean'] => "$fieldName => \$faker->boolean(),",
            $dbType['date'] => "$fieldName => \$faker->date(),",
            $dbType['dateTime'], $dbType['timestamp'] => "$fieldName => \$faker->dateTime->format('Y-m-d H:i:s'),",
            $dbType['time'] => "$fieldName => \$faker->time('H:i:s'),",
            $dbType['year'] => "$fieldName => \$faker->year(),",
            $dbType['char'],
            $dbType['string'],
            $dbType['tinyText'] => "$fieldName => \$faker->randomLetter(),",
            $dbType['text'],
            $dbType['mediumText'],
            $dbType['longText'] => "$fieldName => \$faker->paragraph(),",
            $dbType['enum'] => "$fieldName => \$faker->randomElement(" . json_encode($field['enum']) . '),',
            $dbType['json'],
            $dbType['jsonb'] => "$fieldName => '{}',",
            default => '',
        };
    }
    // END - SEEDER

    // START - LANG
    public function langTemplate($tableName, $templateDataReal): array|string
    {
        $quoteTable = "'".$tableName."' => [";
        $template = $this->searchTemplate(
            $quoteTable,
            '],',
            2 + strlen($quoteTable),
            -2 - strlen($quoteTable),
            $templateDataReal,
        );
        $templateReplace = $this->searchTemplate($quoteTable, '],', 0, 4, $templateDataReal);

        return [
            'template' => $template,
            'template_replace' => $templateReplace,
        ];
    }
    // END - LANG

    // START - VIEW TABLE
    public function viewTableClassColumn($field): string
    {
        $dbType = config('generator.db_type');

        return match ($field['db_type']) {
            $dbType['increments'],
            $dbType['integer'],
            $dbType['unsignedInteger'],
            $dbType['tinyInteger'],
            $dbType['unsignedTinyInteger'],
            $dbType['smallInteger'],
            $dbType['unsignedSmallInteger'],
            $dbType['mediumInteger'],
            $dbType['unsignedMediumInteger'],
            $dbType['bigInteger'],
            $dbType['unsignedBigInteger'],
            $dbType['float'],
            $dbType['double'],
            $dbType['decimal'],
            $dbType['boolean'],
            $dbType['date'],
            $dbType['dateTime'],
            $dbType['timestamp'],
            $dbType['time'],
            $dbType['year'],
            $dbType['char'],
            $dbType['enum'] => 'center',
            default => 'left',
        };
    }
    // END - VIEW TABLE

    public function generateRepositoryProvider($case, $model): string
    {
        return match ($case) {
            'use_class' => "use App\Repositories\\{$model['name']}\\{$model['name']}{$model['class']};",
            'ast_use_class' => "App\Repositories\\{$model['name']}\\{$model['name']}{$model['class']}",
            'register' => '$this->app->bind('.
                $model['name'].
                'Interface::class, '.
                $model['name'].
                'Repository::class);',
        };
    }

    public function generateObserverProvider($case, $model): string
    {
        return match ($case) {
            'use_class_model' => "use App\Models\\{$model['name']};",
            'use_class_observer' => "use App\Observers\\{$model['name']}Observer;",
            'register' => "{$model['name']}::observe({$model['name']}Observer::class);",
        };
    }

    public function generateFieldForm($fields): array
    {
        $defaultValue = config('generator.default_value');
        $dbType = config('generator.db_type');
        $items = [];
        foreach ($fields as $field) {
            $fieldName = $field['field_name'];
            $items[$fieldName] = [
                'value' => '',
                'type' => '',
            ];
            switch ($field['default_value']) {
                case $defaultValue['none']:
                case $defaultValue['null']:
                    if (in_array($field['db_type'], [$dbType['json'], $dbType['jsonb']], false)) {
                        $items[$fieldName] = [
                            'value' => [],
                            'type' => 'json',
                        ];
                    } elseif (in_array($field['db_type'], [
                        $dbType['increments'],
                        $dbType['integer'],
                        $dbType['unsignedInteger'],
                        $dbType['tinyInteger'],
                        $dbType['unsignedTinyInteger'],
                        $dbType['smallInteger'],
                        $dbType['unsignedSmallInteger'],
                        $dbType['mediumInteger'],
                        $dbType['unsignedMediumInteger'],
                        $dbType['bigInteger'],
                        $dbType['unsignedBigInteger'],
                        $dbType['float'],
                        $dbType['double'],
                        $dbType['decimal'],
                    ], false)) {
                        $items[$fieldName] = [
                            'value' => 0,
                            'type' => 'number',
                        ];
                    } elseif ($field['db_type'] === $dbType['boolean']) {
                        $items[$fieldName] = [
                            'value' => false,
                            'type' => 'boolean',
                        ];
                    } else {
                        $items[$fieldName] = [
                            'value' => '',
                            'type' => 'string',
                        ];
                    }
                    break;
                case $defaultValue['as_define']:
                    $value = $field['as_define'];
                    if ($field['db_type'] === $dbType['boolean']) {
                        $items[$fieldName] = [
                            'value' => (bool) $value,
                            'type' => 'boolean',
                        ];
                    } else {
                        $items[$fieldName] = [
                            'value' => is_numeric($value) ? +$value : $value,
                            'type' => is_numeric($value) ? 'number' : 'string',
                        ];
                    }

                    break;
                case $defaultValue['current_timestamps']:
                    $items[$fieldName]['type'] = $defaultValue['current_timestamps'];
                    break;
            }
        }

        return $items;
    }

    public function generateEnumItem($fields): array
    {
        $dbType = config('generator.db_type');
        $items = [];
        foreach ($fields as $field) {
            if ($field['db_type'] === $dbType['enum']) {
                $items["{$field['field_name']}Options"] = [
                    'value' => $field['enum'],
                    'type' => 'array',
                ];
            }
        }

        return $items;
    }

    public function replaceField($field, $model, $formTemplate): string
    {
        $dbType = config('generator.db_type');
        $attribute = "t('table.{$this->tableNameNotPlural($model['name'])}.{$field['field_name']}')";

        return str_replace(['{{$ATTRIBUTE_FIELD$}}', '{{$TRIGGER$}}'], [$attribute, 'change'], $formTemplate);
    }

    public function generateColumns($fields, $model, $ignoreOptions = false): array
    {
        $columns = [];
        $template = $this->get_template('column', 'Forms/', 'vue');
        foreach ($fields as $field) {
            if ($field['field_name'] === 'id' || !$field['show']) {
                continue;
            }
            $templateClone = $template;
            $templateClone = str_replace(['{{$FIELD_NAME$}}', '{{$FORM_SORTABLE$}}', '{{$FORM_ALIGN$}}', '{{$FORM_LABEL$}}'], [$field['field_name'], $field['sort'] ? "'custom'" : 'false', $this->viewTableClassColumn($field), ''], $templateClone);
            $templateColumn = $this->templateColumn($field);
            $templateClone = str_replace('{{$FORM_TEMPLATE$}}', $templateColumn, $templateClone);
            $columns[] = $templateClone;
        }
        if (!$ignoreOptions && $this->getOptions(config('generator.model.options.timestamps'), $model['options'])) {
            $template = str_replace(['{{$FIELD_NAME$}}', '{{$FORM_SORTABLE$}}', '{{$FORM_ALIGN$}}', '{{$FORM_LABEL$}}', '{{$FORM_TEMPLATE$}}'], ['updated_at', "'custom'", 'center', "label: t('date.updated_at'),", "template: 'date',"], $template);
            $columns[] = $template;
        }

        return $columns;
    }

    public function templateColumn($field): string
    {
        $dbType = config('generator.db_type');
        switch ($field['db_type']) {
            case $dbType['boolean']:
                $template = $this->get_template('tableColumnBoolean', 'Handler/', 'vue');
                $template = str_replace('{{$FIELD_NAME$}}', $field['field_name'], $template);
                break;
            case $dbType['longText']:
                $template = $this->get_template('tableColumnLongText', 'Handler/', 'vue');
                $template = str_replace('{{$FIELD_NAME$}}', $field['field_name'], $template);
                break;
            case $dbType['dateTime']:
            case $dbType['timestamp']:
                return "template: 'date'";
            default:
                $template = '';
        }
        if (!$template) {
            return '';
        }

        return <<<TEMPLATE
        template: ({ row }) => (
          $template
        ),
        TEMPLATE;
    }

    public function generateColumnSearch($fields): array
    {
        $column = [];
        foreach ($fields as $field) {
            if ($field['search']) {
                $column[] = $field['field_name'];
            }
        }

        return $column;
    }

    public function importComponent($fields, $templateDataReal, $path, $model): string
    {
        $phpParserService = new PhpParserService();
        $dbType = config('generator.db_type');
        $defaultValue = config('generator.default_value');
        $flags = [
            'long_text' => true,
            'json' => true,
            'parse_time' => true,
        ];
        $importVueJS = config('generator.import.vue');
        foreach ($fields as $field) {
            if (
                $field['db_type'] === $dbType['longText'] &&
                $flags['long_text']
            ) {
                $templateDataReal = $phpParserService->runParserJS($path, [
                    'key' => 'uses.form',
                    'name' => $importVueJS['tinymce']['name'],
                    'path' => $importVueJS['tinymce']['path'],
                ], $templateDataReal);
                $flags['long_text'] = false;
            } elseif (
                $field['db_type'] === $dbType['json'] &&
                $flags['json']
            ) {
                $templateDataReal = $phpParserService->runParserJS($path, [
                    'key' => 'uses.form',
                    'name' => $importVueJS['json_editor']['name'],
                    'path' => $importVueJS['json_editor']['path'],
                ], $templateDataReal);
                $flags['json'] = false;
            } elseif (
                in_array($field['db_type'], [$dbType['dateTime'], $dbType['timestamp']], false) &&
                $flags['parse_time'] &&
                $field['default_value'] === $defaultValue['current_timestamps']
            ) {
                $templateDataReal = $phpParserService->runParserJS($path, [
                    'key' => 'uses.form',
                    'name' => $importVueJS['parse_time']['name'],
                    'path' => $importVueJS['parse_time']['path'],
                ], $templateDataReal);
                $flags['parse_time'] = false;
            } elseif ($field['db_type'] === $dbType['enum'] && config('generator.js_language') === 'ts') {
                $templateDataReal = $phpParserService->runParserJS($path, [
                    'key' => 'uses.form',
                    'interface' => "{$model['name']}StateRoot",
                    'items' => [
                        "{$field['field_name']}Options" => 'unknown[];',
                    ],
                ], $templateDataReal);
            }
        }

        return $templateDataReal;
    }

    /**
     * @param  $fields
     * @param  $model
     * @return array
     */
    public function generateRules($fields, $model): array
    {
        $defaultValue = config('generator.default_value');
        $items = [];
        foreach ($fields as $field) {
            if ($field['field_name'] === 'id') {
                continue;
            }
            if ($field['default_value'] === $defaultValue['none']) {
                $templateRules = $this->get_template('rules', 'Handler/', 'vue');
                $templateRules = $this->replaceField($field, $model, $templateRules);
                $items[$field['field_name']] = $templateRules;
            }
        }

        return $items;
    }

    public function generateModel($fields): array
    {
        $data = [];
        $dbType = config('generator.db_type');
        $defaultValue = config('generator.default_value');
        // Mapping of database types to TypeScript types
        foreach ($fields as $field) {
            $type = match ($field['db_type']) {
                $dbType['increments'],
                $dbType['integer'],
                $dbType['unsignedInteger'],
                $dbType['tinyInteger'],
                $dbType['unsignedTinyInteger'],
                $dbType['smallInteger'],
                $dbType['unsignedSmallInteger'],
                $dbType['mediumInteger'],
                $dbType['unsignedMediumInteger'],
                $dbType['bigInteger'],
                $dbType['unsignedBigInteger'],
                $dbType['float'],
                $dbType['double'],
                $dbType['decimal'] => 'number',
                $dbType['boolean'] => 'boolean',
                $dbType['date'],
                $dbType['dateTime'],
                $dbType['timestamp'],
                $dbType['time'],
                $dbType['year'],
                $dbType['char'],
                $dbType['string'],
                $dbType['tinyText'],
                $dbType['mediumText'],
                $dbType['text'],
                $dbType['longText'] => 'string',
                $dbType['json'],
                $dbType['jsonb'] => 'Record<string, unknown>',
                default => '',
            };
            $isNull = $defaultValue['null'] === $field['default_value'];
            // Determine the TypeScript type based on the database type
            if ($field['db_type'] === $dbType['enum']) {
                $tsType = $this->processEnumType($field);
            } else {
                $tsType = $type ?? 'unknown'; // Default to 'unknown' if not mapped
            }
            $tsType .= $isNull ? ' | null' : ''; // Append '| null' for nullable fields

            $data[$field['field_name']] = $tsType . ';';
        }

        return $data;
    }

    /**
     * Process the TypeScript enum type.
     */
    private function processEnumType(array $field): string
    {
        // Assuming $field['enum'] contains the possible enum values
        return implode('|', array_map(fn($value) => "'$value'", $field['enum']));
    }

    public function replaceEndFile($templateDataReal, $content, $tab, $spaces = 4): array|string|null
    {
        return preg_replace('/\}\s*$/', "\n{$this->infy_nl_tab(1, $tab, $spaces)}$content\n}", $templateDataReal);
    }

    public function replaceArray($templateDataReal, $key, $content, $tab, $spaces = 4): array|string|null
    {
        $regex = '/'.$key.'\s*\[([^\]]*)\]/m';

        return preg_replace($regex, "\n{$this->infy_nl_tab(1, $tab, $spaces)}$content\n}", $templateDataReal);
    }
}
