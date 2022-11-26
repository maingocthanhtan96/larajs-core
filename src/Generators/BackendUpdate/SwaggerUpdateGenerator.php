<?php

namespace LaraJS\Core\Generators\BackendUpdate;

use LaraJS\Core\Generators\BaseGenerator;
use Carbon\Carbon;

class SwaggerUpdateGenerator extends BaseGenerator
{
    public const FIELD_ID = 'id';

    public const OA_SCHEME = '@OA\Schema(';

    public const REQUIRED = 'required={';

    public const CHECK_CHAR_SCHEMA = '<###> @OA\Property(';

    /** @var array */
    protected array $dbType;

    /** @var array */
    protected array $configDefaultValue;

    public function __construct($generator, $model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.laravel.swagger');
        $this->notDelete = config('generator.not_delete.laravel.swagger');
        $this->dbType = config('generator.db_type');
        $this->configDefaultValue = config('generator.default_value');

        $this->_generate($generator, $model, $updateFields);
    }

    public function _generateFieldsUpdate($updateFields, $templateDataReal): string
    {
        if (!$updateFields) {
            return $templateDataReal;
        }
        // Required
        $templateScheme = $this->serviceGenerator->searchTemplate(
            self::OA_SCHEME,
            ')',
            -strlen(self::OA_SCHEME) + 4,
            strlen(self::OA_SCHEME) + 2,
            $templateDataReal,
        );
        $templateRequired = $this->serviceGenerator->searchTemplate(
            self::REQUIRED,
            '}',
            strlen(self::REQUIRED),
            -strlen(self::REQUIRED),
            $templateScheme,
        );

        if (!$templateScheme || !$templateRequired) {
            return $templateDataReal;
        }
        $fieldRequired = \Arr::pluck($updateFields, 'default_value', 'field_name');
        $fieldRequires = '';
        foreach ($fieldRequired as $field => $default) {
            if ($field === self::FIELD_ID) {
                continue;
            }
            if ($default === $this->configDefaultValue['none']) {
                $fieldRequires .= '"' . $field . '",';
            }
        }
        $templateRequiredNew = $templateRequired . ', ' . $fieldRequires;
        $templateDataReal = str_replace($templateRequired, rtrim($templateRequiredNew, ', '), $templateDataReal);
        // end required
        $templateDataReal = $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['property'],
            $this->_generateFields($updateFields),
            1,
            $templateDataReal,
        );

        return $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['json_content'],
            $this->_generateFields($updateFields, true),
            0,
            $templateDataReal,
        );
    }

    public function changeDefault($field): string
    {
        if ($field['default_value'] === $this->configDefaultValue['none']) {
            $defaultValue = 'NONE';
        } elseif ($field['default_value'] === $this->configDefaultValue['null']) {
            $defaultValue = 'NULL';
        } else {
            $defaultValue = $field['as_define'];
        }

        return 'default="' . $defaultValue . '"';
    }

    public function changeExample($field): string
    {
        $faker = \Faker\Factory::create();
        $example = match ($field['db_type']) {
            $this->dbType['integer'], $this->dbType['bigInteger'] => $faker->numberBetween(1000, 9000),
            $this->dbType['float'], $this->dbType['double'] => $faker->randomFloat(2, 1000, 9000),
            $this->dbType['boolean'] => $faker->numberBetween(0, 1),
            $this->dbType['date'] => Carbon::now()->toDateString(),
            $this->dbType['dateTime'], $this->dbType['timestamp'] => Carbon::now()->toDateTimeString(),
            $this->dbType['time'] => Carbon::now()->toTimeString(),
            $this->dbType['year'] => Carbon::now()->year,
            $this->dbType['string'] => $faker->name,
            $this->dbType['text'], $this->dbType['longtext'] => $faker->paragraph,
            $this->dbType['enum'] => \Arr::random($field['enum']),
            $this->dbType['json'] => '[{}]',
        };

        return 'example="' . $example . '"';
    }

    private function _generate($generator, $model, $updateFields)
    {
        $fileName = $model['name'] . '.php';
        $templateDataReal = $this->serviceGenerator->getFile('swagger', 'laravel', $fileName);
        $templateDataReal = $this->_generateFieldsRename($updateFields['renameFields'], $templateDataReal);
        $templateDataReal = $this->_generateFieldsChange($generator, $updateFields['changeFields'], $templateDataReal);
        $templateDataReal = $this->_generateFieldsUpdate($updateFields['updateFields'], $templateDataReal);
        $templateDataReal = $this->_generateFieldsDrop($updateFields['dropFields'], $templateDataReal);
        $fileName = $this->path . $fileName;
        $this->serviceFile->createFileReal($fileName, $templateDataReal);
    }

    private function _generateFieldsRename($renameFields, $templateDataReal)
    {
        if (!$renameFields) {
            return $templateDataReal;
        }
        // required
        $templateScheme = $this->serviceGenerator->searchTemplate(
            self::OA_SCHEME,
            ')',
            -strlen(self::OA_SCHEME) + 4,
            strlen(self::OA_SCHEME) + 2,
            $templateDataReal,
        );
        $templateRequired = $this->serviceGenerator->searchTemplate(
            self::REQUIRED,
            '}',
            strlen(self::REQUIRED),
            -strlen(self::REQUIRED),
            $templateScheme,
        );
        if (!$templateScheme || !$templateRequired) {
            return $templateDataReal;
        }
        //end required

        foreach ($renameFields as $rename) {
            $templateDataReal = str_replace(
                'property="' . $rename['field_name_old']['field_name'] . '"',
                'property="' . $rename['field_name_new']['field_name'] . '"',
                $templateDataReal,
            );
            $templateRequiredNew = str_replace(
                $rename['field_name_old']['field_name'],
                $rename['field_name_new']['field_name'],
                $templateRequired,
            );
            $templateDataReal = str_replace($templateRequired, $templateRequiredNew, $templateDataReal);
        }

        return $templateDataReal;
    }

    private function _generateFieldsChange($generator, $changeFields, $templateDataReal)
    {
        if (!$changeFields) {
            return $templateDataReal;
        }
        $formFields = json_decode($generator->field, true);
        // Required
        $templateScheme = $this->_searchField($templateDataReal);
        $templateRequired = $this->serviceGenerator->searchTemplate(
            self::REQUIRED,
            '}',
            strlen(self::REQUIRED),
            -strlen(self::REQUIRED),
            $templateScheme,
        );
        if (!$templateScheme || !$templateRequired) {
            return $templateDataReal;
        }
        $arrayFields = explode(',', $templateRequired);
        $newFields = [];
        foreach ($arrayFields as $field) {
            $field = trim($field);
            $field = $this->serviceGenerator->trimQuotes($field);
            $newFields[] = $field; // auto None
        }
        // end required
        $dataOld = [];
        foreach ($formFields as $index => $field) {
            if ($index > 0) {
                $dataOld[$field['id']]['id'] = $field['id'];
                $dataOld[$field['id']]['db_type'] = $field['db_type'];
                $dataOld[$field['id']]['default_value'] = $field['default_value'];
                $dataOld[$field['id']]['enum'] = $field['enum'];
                $dataOld[$field['id']]['field_name'] = $field['field_name'];
                $dataOld[$field['id']]['as_define'] = $field['as_define'];
            }
        }
        foreach ($changeFields as $change) {
            if ($dataOld[$change['id']]['id'] === $change['id']) {
                // replace json content
                $searchPropertyJson = 'property="' . $change['field_name'] . '"';
                $templatePropertyJson = $this->serviceGenerator->searchTemplate(
                    $searchPropertyJson,
                    '),',
                    -strlen($searchPropertyJson) + 1,
                    strlen($searchPropertyJson) + 1,
                    $templateDataReal,
                );
                if (!$templatePropertyJson) {
                    return false;
                }
                preg_match('/example=".*"/miU', $templatePropertyJson, $example);
                $example = reset($example);
                $templateJsonContentOld = $templatePropertyJson;
                $templatePropertyJson = str_replace(
                    $this->changeDefault($dataOld[$change['id']]),
                    $this->changeDefault($change),
                    $templatePropertyJson,
                );

                $templatePropertyJson = str_replace($example, $this->changeExample($change), $templatePropertyJson);
                $templatePropertyJson = str_replace(
                    $this->_changeDBType($dataOld[$change['id']]['db_type']),
                    $this->_changeDBType($change['db_type']),
                    $templatePropertyJson,
                );
                $templateDataReal = str_replace($templateJsonContentOld, $templatePropertyJson, $templateDataReal);

                // replace schema
                $templatePropertySchema = $this->serviceGenerator->searchTemplate(
                    self::CHECK_CHAR_SCHEMA . $searchPropertyJson,
                    '*/',
                    -strlen(self::CHECK_CHAR_SCHEMA) + 6,
                    strlen(self::CHECK_CHAR_SCHEMA) - 4,
                    $templateDataReal,
                );
                if (!$templatePropertySchema) {
                    return false;
                }

                $templateSchemaOld = $templatePropertySchema;
                $templatePropertySchema = str_replace(
                    $this->changeDefault($dataOld[$change['id']]),
                    $this->changeDefault($change),
                    $templatePropertySchema,
                );
                $templatePropertySchema = str_replace(
                    $this->_changeDBType($dataOld[$change['id']]['db_type']),
                    $this->_changeDBType($change['db_type']),
                    $templatePropertySchema,
                );
                $templateDataReal = str_replace($templateSchemaOld, $templatePropertySchema, $templateDataReal);
                // required
                if ($change['default_value'] !== $this->configDefaultValue['none']) {
                    $key = array_search($change['field_name'], $newFields);
                    if ($key !== false) {
                        unset($newFields[$key]);
                    }
                } else {
                    $newFields[] = $change['field_name'];
                }
                // end required
            }
        }
        $fieldRequires = '';
        foreach (array_values($newFields) as $field) {
            $fieldRequires .= '"' . $field . '",';
        }
        if ($fieldRequires) {
            $templateDataReal = str_replace($templateRequired, rtrim($fieldRequires, ', '), $templateDataReal);
        }

        return $templateDataReal;
    }

    private function _generateFieldsDrop($dropFields, $templateDataReal): string|bool
    {
        if (!$dropFields) {
            return $templateDataReal;
        }

        // required
        $templateScheme = $this->_searchField($templateDataReal);
        $templateRequired = $this->serviceGenerator->searchTemplate(
            self::REQUIRED,
            '}',
            strlen(self::REQUIRED),
            -strlen(self::REQUIRED),
            $templateScheme,
        );
        if (!$templateScheme || !$templateRequired) {
            return $templateDataReal;
        }
        $arrayFields = explode(',', $templateRequired);
        $fieldRequiredDrop = \Arr::pluck($dropFields, 'field_name');
        $fieldRequires = '';
        foreach ($arrayFields as $field) {
            $field = trim($field);
            $field = $this->serviceGenerator->trimQuotes($field);
            if (!in_array($field, $fieldRequiredDrop)) {
                $fieldRequires .= '"' . $field . '",';
            }
        }
        $templateDataReal = str_replace($templateRequired, rtrim($fieldRequires, ', '), $templateDataReal);
        //end required
        foreach ($dropFields as $drop) {
            // drop json content
            $searchPropertyJson = 'property="' . $drop['field_name'] . '"';
            $templatePropertyJson = $this->serviceGenerator->searchTemplate(
                $searchPropertyJson,
                '),',
                -strlen($searchPropertyJson) + 1,
                strlen($searchPropertyJson) + 1,
                $templateDataReal,
            );
            if (!$templatePropertyJson) {
                return false;
            }
            $templateDataReal = str_replace($templatePropertyJson, '', $templateDataReal);

            // drop schema
            $templatePropertySchema = $this->serviceGenerator->searchTemplate(
                self::CHECK_CHAR_SCHEMA . $searchPropertyJson,
                '*/',
                -strlen(self::CHECK_CHAR_SCHEMA) + 6,
                strlen(self::CHECK_CHAR_SCHEMA) - 4,
                $templateDataReal,
            );
            if (!$templatePropertySchema) {
                return false;
            }
            $templateDataReal = str_replace($templatePropertySchema, '', $templateDataReal);
        }

        return $templateDataReal;
    }

    private function _changeDBType($dbType): string
    {
        return 'type="' . $dbType . '"';
    }

    private function _generateFields($fields, $propertyJson = false): string
    {
        $fieldsGenerate = [];
        foreach ($fields as $field) {
            $fieldsGenerate[] = $this->serviceGenerator->swaggerField($field, $propertyJson);
        }

        return implode($this->serviceGenerator->infy_nl_tab(1, 1), $fieldsGenerate);
    }

    private function _searchField($templateDataReal)
    {
        return $this->serviceGenerator->searchTemplate(
            self::OA_SCHEME,
            ')',
            -strlen(self::OA_SCHEME) + 4,
            strlen(self::OA_SCHEME) + 2,
            $templateDataReal,
        );
    }
}
