<?php

namespace LaraJS\Core\Generators\Backend;

use LaraJS\Core\Generators\BaseGenerator;
use Carbon\Carbon;

class SwaggerGenerator extends BaseGenerator
{
    public const FIELD_ID = 'id';

    /** @var array */
    protected array $dbType;

    /** @var array */
    protected array $configDefaultValue;

    public function __construct($fields, $model)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.swagger');
        $this->notDelete = config('generator.not_delete.laravel.swagger');
        $this->dbType = config('generator.db_type');
        $this->configDefaultValue = config('generator.default_value');

        $this->_generate($fields, $model);
    }

    private function _generate($fields, $model): void
    {
        $now = Carbon::now();
        $pathTemplate = 'Swagger/';
        $templateData = $this->serviceGenerator->get_template('swagger', $pathTemplate);
        $templateData = str_replace('{{DATE}}', $now->toDateTimeString(), $templateData);
        // Generate JsonContent
        $templateData = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['json_content'],
            $this->_generateFields($fields, true),
            0,
            $templateData,
            0,
        );

        $templateData = str_replace('{{MODEL_CLASS}}', $model['name'], $templateData);
        $templateData = str_replace(
            '{{RESOURCE}}',
            $this->serviceGenerator->urlResource($model['name']),
            $templateData,
        );
        $templateTimestamps = '';
        if ($this->serviceGenerator->getOptions(config('generator.model.options.timestamps'), $model['options'])) {
            $templateTimestamps = $this->serviceGenerator->get_template('timestamps', $pathTemplate);
        }
        // SoftDeletes
        $templateData = str_replace($this->notDelete['timestamps'], $templateTimestamps, $templateData);
        $templateSoftDeletes = '';
        if ($this->serviceGenerator->getOptions(config('generator.model.options.soft_deletes'), $model['options'])) {
            $templateSoftDeletes = $this->serviceGenerator->get_template('SoftDeletes', $pathTemplate);
        }
        // SoftDeletes
        $templateData = str_replace($this->notDelete['soft_deletes'], $templateSoftDeletes, $templateData);
        // Required
        $fieldRequired = \Arr::pluck($fields, 'default_value', 'field_name');

        $fieldRequires = '';
        foreach ($fieldRequired as $field => $default) {
            if ($field === self::FIELD_ID) {
                continue;
            }
            if ($default === $this->configDefaultValue['none']) {
                $fieldRequires .= '"' . $field . '",';
            }
        }
        if ($fieldRequires) {
            $templateData = str_replace(
                '{{REQUIRED_FIELDS}}',
                '{"' . self::FIELD_ID . '", ' . rtrim($fieldRequires, ', ') . '}',
                $templateData,
            );
        } else {
            $templateData = str_replace('{{REQUIRED_FIELDS}}', '{"' . self::FIELD_ID . '"}', $templateData);
        }
        // end required

        $templateData = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['property'],
            $this->_generateFields($fields),
            1,
            $templateData,
        );
        //create sort delete
        $fileName = "{$model['name']}.php";
        $this->serviceFile->createFile($this->path, $fileName, $templateData);
    }

    private function _generateFields($fields, $propertyJson = false): string
    {
        $fieldsGenerate = [];

        foreach ($fields as $index => $field) {
            if ($index > 0) {
                $fieldsGenerate[] = $this->serviceGenerator->swaggerField($field, $propertyJson);
            }
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 1), $fieldsGenerate);
    }
}
