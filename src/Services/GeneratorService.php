<?php

namespace LaraJS\Core\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use ReflectionClass;

class GeneratorService
{
    /**
     * Find the position of the Xth occurrence of a substring in a string
     *
     * @param $haystack
     * @param $needle
     * @param $number integer > 0
     * @return int
     */
    public function strpos_x($haystack, $needle, int $number): int
    {
        if ($number === 1) {
            return strpos($haystack, $needle);
        }
        if ($number > 1) {
            return strpos($haystack, $needle, strpos_x($haystack, $needle, $number - 1) + strlen($needle));
        }

        return error_log('Error: Value for parameter $number is out of range');
    }

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
     *
     * @param  int  $lns
     * @param  int  $tabs
     * @param  int  $spaces
     * @return string
     */
    public function infy_nl_tab(int $lns = 1, int $tabs = 1, int $spaces = 4): string
    {
        return $this->infy_nls($lns) . $this->infy_tabs($tabs, $spaces);
    }

    /**
     * get path for template file.
     *
     * @param  string  $templateName
     * @param  string  $templatePath
     * @param  string  $typeTemplate
     * @return bool|string
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
            $templatesPath = config('generator.template.vue');
        }
        $path = $templatesPath . $templatePath . $templateName . '.stub';
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
            $path = config('generator.path.laravel.' . $nameConfig);
        } elseif ($type === 'package') {
            $path = config('generator.path.package.' . $nameConfig);
        } else {
            $path = config('generator.path.vue.' . $nameConfig);
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
     * @param  string  $nameConfig
     * @param  string  $type
     * @param  string  $fileName
     * @return string
     */
    public function getFile(string $nameConfig, string $type = 'laravel', string $fileName = '')
    {
        $path = $this->getFilePath($nameConfig, $type, $fileName);

        return file_exists($path) ? file_get_contents($path) : '';
    }

    /**
     * get path for file.
     *
     * @param  string  $fileName
     * @param  string  $type
     * @return bool|string
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
     * get file.
     *
     * @param  string  $fileName
     * @param  string  $type
     * @return string
     */
    public function getFileReal(string $fileName = '', string $type = 'laravel'): string
    {
        $path = $this->getFilePathReal($fileName, $type);

        return file_get_contents($path);
    }

    /**
     * get template contents.
     *
     * @param  string  $templateName
     * @param  string  $templatePath
     * @param  string  $typeTemplate
     * @return string
     */
    public function get_template(string $templateName, string $templatePath, string $typeTemplate = 'laravel'): string
    {
        $path = $this->get_template_file_path($templateName, $templatePath, $typeTemplate);

        return file_get_contents($path);
    }

    /**
     * get template contents.
     *
     * @param  string  $templateName
     * @param  string  $templatePath
     * @param  string  $typeTemplate
     * @return string
     */
    public function getFileExist($templateName, $templatePath, $typeTemplate = 'laravel')
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
     * fill template with field data.
     *
     * @param  array  $variables
     * @param  array  $fieldVariables
     * @param  string  $template
     * @param  \InfyOm\Generator\Common\GeneratorField  $field
     * @return string
     */
    public function fill_template_with_field_data($variables, $fieldVariables, $template, $field)
    {
        $template = $this->fill_template($variables, $template);

        return $this->fill_field_template($fieldVariables, $template, $field);
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
     *
     * @param  string  $tableName
     * @return string
     */
    public function modelNameNotPluralFe(string $tableName): string
    {
        return \Str::camel($tableName);
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
     *
     * @param  string  $name
     * @return string
     */
    public function tableName(string $name): string
    {
        $name = $this->tableNameHandle($name);

        return \Str::snake(\Str::plural($name));
    }

    /**
     * generates model name from table name.
     *
     * @param  string  $name
     * @return string
     */
    public function tableNameNotPlural(string $name): string
    {
        $name = $this->tableNameHandle($name);

        return \Str::snake($name);
    }

    /**
     * @param $name
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
     * generates model name from table name.
     *
     * @param  string  $tableName
     * @return string
     */
    public function urlFilterColumn($key, $type, $value = '', $singleSorting = true)
    {
        $params = \Request::all();
        if (isset($params['filter_column']) && $singleSorting) {
            foreach ($params['filter_column'] as $k => $filter) {
                foreach ($filter as $t => $val) {
                    if ($t === 'sorting') {
                        unset($params['filter_column'][$k]['sorting']);
                    }
                }
            }
        }
        $params['filter_column'][$key][$type] = $value;

        return \Request::url() . '?' . http_build_query($params);
    }

    /**
     * check options.
     *
     * @param  array  $options
     * @param  string  $name
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
            $replace . $this->infy_nl_tab(1, $tab, $spaces) . $noteDelete,
            $templateDataReal,
        );
    }

    /**
     * search string template.
     *
     * @param  string  $search
     * @param  string  $char
     * @param  number  $plusStart
     * @param  number  $plusEnd
     * @param  string  $templateDataReal
     * @param  string  $searchOther
     * @return bool|string
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
     * search string with position X template.
     *
     * @param  string  $search
     * @param  number  $number
     * @param  string  $char
     * @param  number  $plusStart
     * @param  number  $plusEnd
     * @param  string  $templateDataReal
     * @return string
     */
    public function searchTemplateX($search, $number, $char, $plusStart, $plusEnd, $templateDataReal)
    {
        $position = $this->strpos_x($templateDataReal, $search, $number);
        if ($position) {
            $template = substr($templateDataReal, $position);
            $length = stripos($template, $char);

            return substr(
                $templateDataReal,
                $position + strlen($search) + $plusStart,
                $length + $plusEnd - strlen($search),
            );
        }

        return false;
    }

    /**
     * Get relationship on model
     *
     * @param  string|null  $model
     * @return array
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

    /**
     * search relationship
     *
     * @param  array  $data
     * @return array
     */
    public function extractRelations(array $data): array
    {
        $relationshipIdentifiers = config('generator.relationship.relationship');
        $relationshipData = [];
        foreach ($data as $line) {
            foreach ($relationshipIdentifiers as $relationship) {
                $nameRelationship = $relationship . '(';
                $searchRelationship = $this->searchTemplateX(
                    $nameRelationship,
                    1,
                    ')',
                    -strlen($nameRelationship),
                    strlen($nameRelationship),
                    $line,
                );
                if ($searchRelationship) {
                    $modelData = explode(',', $searchRelationship);
                    $modelName = $this->stripString($modelData[0], $relationship);
                    if ($relationship === 'belongsToMany') {
                        $tableName = $this->modelNameNotPlural($this->stripString($modelData[1], $relationship));
                        $subModel = substr($tableName, strlen($modelName));
                        $relationshipData[] = [
                            'type' => $relationship,
                            'model' => $modelName,
                            'table' => $tableName,
                            'foreign_key' => $this->stripString(
                                $modelData[2] ?? $this->tableNameNotPlural($subModel) . '_id',
                            ),
                            'local_key' => $this->stripString(
                                $modelData[3] ?? $this->tableNameNotPlural($modelName) . '_id',
                            ),
                        ];
                    } else {
                        $relationshipData[] = [
                            'type' => $relationship,
                            'model' => $modelName,
                            'foreign_key' => $this->stripString(
                                $modelData[1] ?? $this->tableNameNotPlural($modelName) . '_id',
                            ),
                            'local_key' => $this->stripString($modelData[2] ?? 'id'),
                        ];
                    }
                }
            }
        }

        return $relationshipData;
    }

    /**
     * @param  string  $modelsPath
     * @return Collection
     */
    public function getModelsNames(string $modelsPath): Collection
    {
        return collect(\File::allFiles($modelsPath))
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                $namespace = $this->extractNamespace($item->getRealPath()) . '\\';

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
     * trip strings from slashes, App, class and ::
     *
     * @param  string  $string
     * @param  string  $relationship
     * @return string
     */
    public function stripString($string, $relationship = '')
    {
        $string = str_replace('App', '', $string);
        $string = str_replace("'", '', $string);
        $string = str_replace('\\', '', $string);
        $string = str_replace('Models', '', $string);
        $string = str_replace('::', '', $string);
        $string = str_replace('class', '', $string);
        $string = str_replace($relationship, '', $string);
        $string = str_replace('(', '', $string);
        $string = str_replace(')', '', $string);

        return str_replace(' ', '', $string);
    }

    /**
     * Trim quotes
     *
     * @param $string
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
     * @param  Model  $model
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
            } catch (QueryException | \TypeError | \Throwable $e) {
                // ignore
            }
        }

        return $relationships;
    }

    // START - MIGRATION
    public function migrationFields($field, $configDBType, $typeDB, $typeLaravel): string
    {
        $table = '';
        if ($field['db_type'] === $configDBType['enum']) {
            $enum = '';
            foreach ($field['enum'] as $keyEnum => $value) {
                if ($keyEnum === count($field['enum']) - 1) {
                    $enum .= "'$value'";
                } else {
                    $enum .= "'$value',";
                }
            }
            $table = '$table->enum("' . trim($field['field_name']) . '", [' . $enum . '])';
        }
        if ($field['db_type'] === $typeDB) {
            $table = '$table->' . $typeLaravel . '("' . trim($field['field_name']) . '")';
        }

        return $table;
    }

    public function migrationDefaultValue($field, $configDefaultValue): string
    {
        $table = '';
        if ($field['default_value'] === $configDefaultValue['null']) {
            $table = '->nullable()';
        } elseif ($field['default_value'] === $configDefaultValue['as_define']) {
            $table = '->nullable()->default("' . $field['as_define'] . '")';
        } elseif ($field['default_value'] === $configDefaultValue['current_timestamps']) {
            $table = '->nullable()->useCurrent()';
        }

        return $table;
    }

    public function migrationOption($field): string
    {
        $table = '';
        if ($field['options']['comment']) {
            $table .= '->comment("' . $field['options']['comment'] . '")';
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

    // START - FORM
    public function formField($templateDataForm): array
    {
        $dataForms = explode(',', trim($templateDataForm));
        $fieldsGenerateDataForm = [];
        foreach ($dataForms as $form) {
            if (strlen($form) > 0) {
                $form = trim($form);
                [$keyForm, $valForm] = array_pad(explode(':', $form, 2), 2, '');
                $name = $keyForm . ":$valForm,";
                $fieldsGenerateDataForm[] = $name;
            }
        }

        return $fieldsGenerateDataForm;
    }

    public function formFeGenerateField(): object
    {
        return new class extends GeneratorService {
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
                if ($dbType === $dbTypeConfig['string']) {
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
                    $this->modelNameNotPluralFe($field['field_name']) . 'Options',
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
                return str_replace('{{$PROP_NAME$}}', 'prop="' . $field['field_name'] . '"', $formTemplate);
            }

            private function _replaceLabelForm($tableName, $field, $formTemplate): string
            {
                return str_replace(
                    '{{$LABEL_NAME_INPUT$}}',
                    '$t(\'table.' . "$tableName.{$field['field_name']}')",
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
    public function requestField($field): string
    {
        $dbType = config('generator.db_type');
        $configDefaultValue = config('generator.default_value');
        if ($field['default_value'] === $configDefaultValue['none']) {
            $required = 'required';
        } else {
            $required = 'nullable';
        }
        $enumFunc = function ($field, $required) {
            $enum = 'in:';
            foreach ($field['enum'] as $keyEnum => $value) {
                if ($keyEnum === count($field['enum']) - 1) {
                    $enum .= "$value";
                } else {
                    $enum .= "$value,";
                }
            }

            return "'" . $field['field_name'] . "'" . ' => ' . "'$required|$enum',";
        };

        return match ($field['db_type']) {
            $dbType['integer'], $dbType['bigInteger'], $dbType['float'], $dbType['double'] => "'" .
                $field['field_name'] .
                "'" .
                ' => ' .
                "'$required|numeric',",
            $dbType['boolean'] => "'" . $field['field_name'] . "'" . ' => ' . "'$required|boolean',",
            $dbType['date'] => "'" . $field['field_name'] . "'" . ' => ' . "'$required|date_format:Y-m-d',",
            $dbType['dateTime'], $dbType['timestamp'] => "'" .
                $field['field_name'] .
                "'" .
                ' => ' .
                "'$required|date_format:Y-m-d H:i:s',",
            $dbType['time'] => "'" . $field['field_name'] . "'" . ' => ' . "'$required|date_format:H:i:s',",
            $dbType['year'] => "'" . $field['field_name'] . "'" . ' => ' . "'$required|date_format:Y',",
            $dbType['string'] => "'" .
                $field['field_name'] .
                "'" .
                ' => ' .
                "'$required|string|max:{$field['length_varchar']}',",
            $dbType['text'], $dbType['longtext'] => "'" . $field['field_name'] . "'" . ' => ' . "'$required|string',",
            $dbType['enum'] => $enumFunc($field, $required),
            $dbType['json'] => "'" . $field['field_name'] . "'" . ' => ' . "'$required|json',",
            default => '',
        };
    }
    // END - REQUEST

    // START - SEEDER
    public function seederField($field): string
    {
        $dbType = config('generator.db_type');

        return match ($field['db_type']) {
            $dbType['integer'], $dbType['bigInteger'] => "'" .
                $field['field_name'] .
                "'" .
                ' => $faker->numberBetween(1000, 9000),',
            $dbType['float'], $dbType['double'] => "'" .
                $field['field_name'] .
                "'" .
                ' => $faker->randomFloat(2, 1000, 9000),',
            $dbType['boolean'] => "'" . $field['field_name'] . "'" . ' => $faker->numberBetween(0, 1),',
            $dbType['date'] => "'" . $field['field_name'] . "'" . ' => $faker->date,',
            $dbType['dateTime'], $dbType['timestamp'] => "'" .
                $field['field_name'] .
                "'" .
                ' => $faker->dateTime->format(\'Y-m-d H:i:s\'),',
            $dbType['time'] => "'" . $field['field_name'] . "'" . ' => $faker->date(\'H:i:s\'),',
            $dbType['year'] => "'" . $field['field_name'] . "'" . ' => $faker->year,',
            $dbType['string'] => "'" . $field['field_name'] . "'" . ' => $faker->name,',
            $dbType['text'], $dbType['longtext'] => "'" . $field['field_name'] . "'" . ' => $faker->paragraph,',
            $dbType['enum'] => "'" .
                $field['field_name'] .
                "'" .
                ' => $faker->randomElement(' .
                json_encode($field['enum']) .
                '),',
            $dbType['json'] => "'" . $field['field_name'] . "'" . " => '[{}]',",
            $dbType['file'] => "'" .
                $field['field_name'] .
                "'" .
                " => json_encode(['https://via.placeholder.com/350']),",
            default => '',
        };
    }
    // END - SEEDER

    // START - LANG
    public function langTemplate($tableName, $templateDataReal): array|string
    {
        $quoteTable = "'" . $tableName . "' => [";
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
            'Increments',
            $dbType['integer'],
            $dbType['bigInteger'],
            $dbType['float'],
            $dbType['double'],
            $dbType['boolean'],
            $dbType['date'],
            $dbType['dateTime'],
            $dbType['timestamp'],
            $dbType['time'],
            $dbType['year'],
            $dbType['enum']
                => 'center',
            default => 'left',
        };
    }

    public function viewTableHandler($field, $model): string
    {
        $dbType = config('generator.db_type');
        $pathTemplate = 'Handler/';
        $templateTableColumnLongText = $this->get_template('tableColumnLongText', $pathTemplate, 'vue');
        $templateTableColumnBoolean = $this->get_template('tableColumnBoolean', $pathTemplate, 'vue');
        $templateTableColumn = $this->get_template('tableColumn', $pathTemplate, 'vue');
        if ($field['db_type'] === $dbType['longtext']) {
            $template = str_replace('{{$FIELD_NAME$}}', $field['field_name'], $templateTableColumnLongText);
            $template = str_replace('{{$TABLE_MODEL_CLASS$}}', $this->tableNameNotPlural($model['name']), $template);
        } elseif ($field['db_type'] === $dbType['boolean']) {
            $template = str_replace('{{$FIELD_NAME$}}', $field['field_name'], $templateTableColumnBoolean);
            $template = str_replace('{{$TABLE_MODEL_CLASS$}}', $this->tableNameNotPlural($model['name']), $template);
        } else {
            $template = str_replace('{{$FIELD_NAME$}}', $field['field_name'], $templateTableColumn);
            $template = str_replace('{{$TABLE_MODEL_CLASS$}}', $this->tableNameNotPlural($model['name']), $template);
            $template = str_replace('{{$ALIGN$}}', $this->viewTableClassColumn($field), $template);
        }
        if ($field['sort']) {
            $template = str_replace('{{$SORT$}}', 'sortable="custom"', $template);
        } else {
            $template = str_replace('{{$SORT$}}', '', $template);
        }

        return $template;
    }
    // END - VIEW TABLE

    public function generateRepositoryProvider($case, $model): string
    {
        return match ($case) {
            'use_class' => "use App\Repositories\\{$model['name']}\\{$model['name']}{$model['class']};",
            'register' => '$this->app->bind(' .
                $model['name'] .
                'Interface::class, ' .
                $model['name'] .
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
        $fieldsGenerate = [];
        $defaultValue = config('generator.default_value');
        $dbType = config('generator.db_type');
        foreach ($fields as $field) {
            $fieldName = $field['field_name'];
            $fieldForm = '';
            if (
                $field['default_value'] === $defaultValue['none'] ||
                $field['default_value'] === $defaultValue['null']
            ) {
                if ($field['db_type'] === $dbType['json']) {
                    $fieldForm = "$fieldName: '[]'";
                } elseif (
                    in_array($field['db_type'], [
                        $dbType['integer'],
                        $dbType['bigInteger'],
                        $dbType['float'],
                        $dbType['double'],
                        $dbType['boolean'],
                        $dbType['increments'],
                    ])
                ) {
                    $fieldForm = "$fieldName: 0";
                } else {
                    $fieldForm = "$fieldName: ''";
                }
            } elseif ($field['default_value'] === $defaultValue['as_define']) {
                $asDefine = $field['as_define'];
                if (is_numeric($asDefine)) {
                    $fieldForm = "$fieldName: $asDefine";
                } else {
                    $fieldForm = "$fieldName: '$asDefine'";
                }
            } elseif ($field['default_value'] === $defaultValue['current_timestamps']) {
                $fieldForm = "$fieldName: undefined";
            }
            $fieldForm .= ',';
            $fieldsGenerate[] = $fieldForm;
        }

        return $fieldsGenerate;
    }

    public function getHandlerTemplate(): string
    {
        return $this->get_template('rules', 'Handler/', 'vue');
    }

    public function replaceField($field, $model, $formTemplate): string
    {
        $attribute = "t('table.{$this->tableNameNotPlural($model['name'])}.{$field['field_name']}')";
        $formTemplate = str_replace('{{$ATTRIBUTE_FIELD$}}', $attribute, $formTemplate);

        return str_replace('{{$FIELD$}}', $field['field_name'], $formTemplate);
    }

    public function replaceTemplate($fieldsGenerate, int $space = 2, int $start = 1): string
    {
        return $this->infy_nl_tab($start, 2) .
            implode($this->infy_nl_tab(1, 2), $fieldsGenerate) .
            $this->infy_nl_tab(1, 3, $space);
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
            $templateClone = str_replace('{{$FIELD_NAME$}}', $field['field_name'], $templateClone);
            $templateClone = str_replace('{{$FORM_SORTABLE$}}', $field['sort'] ? "'custom'" : 'false', $templateClone);
            $templateClone = str_replace('{{$FORM_ALIGN$}}', $this->viewTableClassColumn($field), $templateClone);
            $templateClone = str_replace('{{$FORM_LABEL$}}', '', $templateClone);
            $templateColumn = $this->templateColumn($field);
            $templateClone = str_replace('{{$FORM_TEMPLATE$}}', $templateColumn, $templateClone);
            $columns[] = $templateClone;
        }
        if (!$ignoreOptions && $this->getOptions(config('generator.model.options.timestamps'), $model['options'])) {
            $template = str_replace('{{$FIELD_NAME$}}', 'updated_at', $template);
            $template = str_replace('{{$FORM_SORTABLE$}}', "'custom'", $template);
            $template = str_replace('{{$FORM_ALIGN$}}', 'center', $template);
            $template = str_replace('{{$FORM_LABEL$}}', "label: t('date.updated_at'),", $template);
            $template = str_replace('{{$FORM_TEMPLATE$}}', "template: 'date',", $template);
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
            case $dbType['longtext']:
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

    /**
     * @param $fields
     * @return string
     */
    public function generateColumnSearch($fields): string
    {
        $column = [];
        foreach ($fields as $field) {
            if ($field['search']) {
                $column[] = "'" . $field['field_name'] . "'";
            }
        }

        return implode($this->infy_nl_tab(0, 0) . ', ', $column);
    }

    /**
     * @param $fields
     * @param $templateDataReal
     * @return mixed|string
     */
    public function importComponent($fields, $templateDataReal): mixed
    {
        $dbType = config('generator.db_type');
        $notDelete = config('generator.not_delete.vue.form');
        $flags = [
            'long_text' => true,
            'json' => true,
        ];
        $importVueJS = config('generator.import.vue');
        foreach ($fields as $field) {
            if (!$flags['long_text'] && !$flags['json']) {
                return $templateDataReal;
            }
            if (
                $field['db_type'] === $dbType['longtext'] &&
                $flags['long_text'] &&
                !strpos($templateDataReal, $importVueJS['tinymce']['file'])
            ) {
                $templateDataReal = $this->replaceNotDelete(
                    $notDelete['import_component'],
                    $importVueJS['tinymce']['file'],
                    0,
                    $templateDataReal,
                    2,
                );
                $flags['long_text'] = false;
            } elseif (
                $field['db_type'] === $dbType['json'] &&
                $flags['json'] &&
                !strpos($templateDataReal, $importVueJS['json_editor']['file'])
            ) {
                $templateDataReal = $this->replaceNotDelete(
                    $notDelete['import_component'],
                    $importVueJS['json_editor']['file'],
                    0,
                    $templateDataReal,
                    2,
                );
                $flags['json'] = false;
            }
        }

        return $templateDataReal;
    }

    /**
     * @param $fields
     * @param $model
     * @param $templateData
     * @return mixed|string
     */
    public function generateRules($fields, $model, $templateData): mixed
    {
        $notDelete = config('generator.not_delete.vue');
        $defaultValue = config('generator.default_value');
        $dbType = config('generator.db_type');
        foreach ($fields as $field) {
            if ($field['field_name'] === 'id') {
                continue;
            }
            if ($field['default_value'] === $defaultValue['none']) {
                $templateRules = $this->getHandlerTemplate();
                $templateData = $this->replaceNotDelete(
                    $notDelete['form']['rules'],
                    $templateRules,
                    2,
                    $templateData,
                    2,
                );
                $templateData = $this->replaceField($field, $model, $templateData);
            }
            if ($field['db_type'] === $dbType['enum']) {
                $enum = '';
                foreach ($field['enum'] as $keyEnum => $value) {
                    $value = is_numeric($value) ? $value : "'$value'";
                    if ($keyEnum === count($field['enum']) - 1) {
                        $enum .= $value;
                    } else {
                        $enum .= "$value,";
                    }
                }
                $name = "{$field['field_name']}Options: [$enum],";
                $templateData = $this->replaceNotDelete($notDelete['form']['data'], $name, 2, $templateData, 2);
            }
        }

        return $templateData;
    }

    /**
     * @param $fields
     * @return array
     */
    public function generateModel($fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            $dbType = config('generator.db_type');
            $data[] = match ($field['db_type']) {
                $dbType['increments'],
                $dbType['integer'],
                $dbType['bigInteger'],
                $dbType['float'],
                $dbType['double']
                    => "{$field['field_name']}: number;",
                $dbType['boolean'] => "{$field['field_name']}: boolean;",
                $dbType['date'], $dbType['dateTime'], $dbType['timestamp'] => "{$field['field_name']}: Date;",
                $dbType['time'],
                $dbType['year'],
                $dbType['string'],
                $dbType['text'],
                $dbType['enum']
                    => "{$field['field_name']}: string;",
                $dbType['json'] => "{$field['field_name']}: any;",
                default => '',
            };
        }

        return $data;
    }
}
