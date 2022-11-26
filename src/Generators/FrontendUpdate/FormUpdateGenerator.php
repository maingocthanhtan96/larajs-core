<?php

namespace LaraJS\Core\Generators\FrontendUpdate;

use LaraJS\Core\Generators\BaseGenerator;

class FormUpdateGenerator extends BaseGenerator
{
    public const TEMPLATE_END = '</el-form-item>';

    public const DATA_GENERATOR = 'data-generator=';

    public function __construct($generator, $model, $updateFields)
    {
        parent::__construct();
        $this->path = config('generator.path.vue.views');
        $this->dbType = config('generator.db_type');
        $this->notDelete = config('generator.not_delete.vue.form');
        $this->defaultValue = config('generator.default_value');

        $this->_generate($generator, $model, $updateFields);
    }

    private function _generate($generator, $model, $updateFields)
    {
        $fileName = $this->serviceGenerator->folderPages($model['name']) . '/Form.vue';
        $templateDataReal = $this->serviceGenerator->getFile('views', 'vue', $fileName);
        $templateDataReal = $this->_generateFieldsRename($updateFields['renameFields'], $templateDataReal);
        $templateDataReal = $this->_generateFieldsDrop($updateFields['dropFields'], $templateDataReal);
        $templateDataReal = $this->_generateFieldsChange(
            $generator,
            $updateFields['changeFields'],
            $model,
            $templateDataReal,
        );
        $templateDataReal = $this->_generateFieldsUpdate($updateFields['updateFields'], $model, $templateDataReal);
        $templateDataReal = $this->_importComponent($updateFields, $templateDataReal);
        $fileName = $this->path . $fileName;
        $this->serviceFile->createFileReal($fileName, $templateDataReal);
    }

    private function _generateFieldsRename($renameFields, $templateDataReal): string
    {
        if (!$renameFields) {
            return $templateDataReal;
        }

        $selfTemplateEnd = self::TEMPLATE_END;
        foreach ($renameFields as $rename) {
            //replace template form item
            $selfTemplateStart = self::DATA_GENERATOR;
            $selfTemplateStart .= '"' . $rename['field_name_old']['field_name'] . '"';
            $templateFormItem = $this->serviceGenerator->searchTemplateX(
                $selfTemplateStart,
                1,
                $selfTemplateEnd,
                -strlen($selfTemplateStart) * 3 + strlen(self::DATA_GENERATOR),
                strlen($selfTemplateStart) * 3,
                $templateDataReal,
            );
            if ($templateFormItem) {
                $formItem = explode(' ', $templateFormItem);
                $fieldsGenerate = $this->_templateForm($formItem, $rename);
                $templateDataReal = str_replace($templateFormItem, implode(' ', $fieldsGenerate), $templateDataReal);
            }
        }

        return $templateDataReal;
    }

    private function _generateFieldsChange($generator, $changeFields, $model, $templateDataReal)
    {
        if (!$changeFields) {
            return $templateDataReal;
        }

        $selfTemplateEnd = self::TEMPLATE_END;
        $formFields = json_decode($generator->field, true);
        foreach ($changeFields as $change) {
            foreach ($formFields as $index => $oldField) {
                if ($index > 0 && $change['id'] === $oldField['id']) {
                    // replace form item
                    $selfTemplateStart = self::DATA_GENERATOR;
                    $selfTemplateStart .= '"' . $change['field_name'] . '"';
                    $templateFormItem = $this->serviceGenerator->searchTemplateX(
                        $selfTemplateStart,
                        1,
                        $selfTemplateEnd,
                        -strlen($selfTemplateStart) * 3 + strlen(self::DATA_GENERATOR),
                        strlen($selfTemplateStart) * 3,
                        $templateDataReal,
                    );
                    if ($change['db_type'] !== $oldField['db_type']) {
                        //replace template form item
                        $templateFormItem = $this->_checkTemplateStartsWith($templateFormItem);
                        $templateDataReal = str_replace(
                            $templateFormItem,
                            $this->_generateItem($change, $model),
                            $templateDataReal,
                        );
                    } else {
                        preg_match('/maxlength=(\'|")[0-9]{0,3}(\'|")/im', $templateFormItem, $matches);
                        if (isset($matches[0])) {
                            $templateFormItemNew = str_replace(
                                $matches[0],
                                'maxlength=' . "'{$change['length_varchar']}'",
                                $templateFormItem,
                            );
                            $templateFormItem = $this->_checkTemplateStartsWith($templateFormItem);
                            $templateDataReal = str_replace($templateFormItem, $templateFormItemNew, $templateDataReal);
                        }
                    }
                }
            }
        }

        return $templateDataReal;
    }

    private function _generateFieldsDrop($dropFields, $templateDataReal)
    {
        if (!$dropFields) {
            return $templateDataReal;
        }

        $selfTemplateEnd = self::TEMPLATE_END;
        foreach ($dropFields as $drop) {
            //replace template form item
            $selfTemplateStart = self::DATA_GENERATOR;
            $selfTemplateStart .= '"' . $drop['field_name'] . '"';
            $templateFormItem = $this->serviceGenerator->searchTemplateX(
                $selfTemplateStart,
                1,
                $selfTemplateEnd,
                -strlen($selfTemplateStart) * 3 + strlen(self::DATA_GENERATOR),
                strlen($selfTemplateStart) * 3,
                $templateDataReal,
            );
            if ($templateFormItem) {
                $templateFormItem = $this->_checkTemplateStartsWith($templateFormItem);
                $templateDataReal = str_replace($templateFormItem, '', $templateDataReal);
            }
        }

        return $templateDataReal;
    }

    private function _generateFieldsUpdate($updateFields, $model, $templateDataReal)
    {
        if (!$updateFields) {
            return $templateDataReal;
        }

        //create form item
        return $this->serviceGenerator->replaceNotDelete(
            $this->notDelete['item'],
            $this->generateItems($updateFields, $model),
            5,
            $templateDataReal,
            2,
        );
    }

    private function _importComponent($updateFields, $templateDataReal)
    {
        $mergeUpdate = array_merge($updateFields['changeFields'], $updateFields['updateFields']);
        $flags = [
            'import' => [
                'long_text' => true,
                'json' => true,
            ],
            'component' => [
                'long_text' => true,
                'json' => true,
            ],
        ];
        $importVueJS = config('generator.import.vue');
        foreach ($mergeUpdate as $field) {
            if ($field['db_type'] === $this->dbType['longtext'] && $flags['import']['long_text']) {
                if (!strpos($templateDataReal, $importVueJS['tinymce']['file'])) {
                    $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                        $this->notDelete['import_component'],
                        $importVueJS['tinymce']['file'],
                        0,
                        $templateDataReal,
                        2,
                    );
                    $flags['import']['long_text'] = false;
                }
                if (!strpos($templateDataReal, $importVueJS['tinymce']['name']) && $flags['component']['long_text']) {
                    $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                        $this->notDelete['import_component_name'],
                        $importVueJS['tinymce']['name'],
                        2,
                        $templateDataReal,
                        2,
                    );
                    $flags['component']['long_text'] = false;
                }
            } elseif ($field['db_type'] === $this->dbType['json']) {
                if (!strpos($templateDataReal, $importVueJS['json_editor']['file']) && $flags['import']['json']) {
                    $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                        $this->notDelete['import_component'],
                        $importVueJS['json_editor']['file'],
                        0,
                        $templateDataReal,
                        2,
                    );
                    $flags['import']['json'] = false;
                }
                if (!strpos($templateDataReal, $importVueJS['json_editor']['name']) && $flags['component']['json']) {
                    $templateDataReal = $this->serviceGenerator->replaceNotDelete(
                        $this->notDelete['import_component_name'],
                        $importVueJS['json_editor']['name'],
                        2,
                        $templateDataReal,
                        2,
                    );
                    $flags['component']['json'] = false;
                }
            }
        }

        return $templateDataReal;
    }

    /**
     * @param $templates
     * @param $fields
     * @return array
     */
    private function _templateForm($templates, $fields): array
    {
        $fieldsGenerate = [];
        foreach ($templates as $template) {
            if (\Str::contains($template, $fields['field_name_old']['field_name'])) {
                if (!\Str::contains($template, '<json-editor')) {
                    $fieldsGenerate[] = str_replace(
                        $fields['field_name_old']['field_name'],
                        $fields['field_name_new']['field_name'],
                        $template,
                    );
                } else {
                    $fieldsGenerate[] = $template;
                }
            } else {
                $fieldsGenerate[] = $template;
            }
        }

        return $fieldsGenerate;
    }

    private function _generateItem($field, $model): string
    {
        $formFeGenerateField = $this->serviceGenerator->formFeGenerateField();
        $fieldsGenerate = [];
        $tableName = $this->serviceGenerator->tableNameNotPlural($model['name']);
        $fieldsGenerate[] = match ($field['db_type']) {
            $this->dbType['integer'],
            $this->dbType['bigInteger'],
            $this->dbType['float'],
            $this->dbType['double']
                => $formFeGenerateField->generateInput('inputNumber', $tableName, $field),
            $this->dbType['boolean'] => $formFeGenerateField->generateBoolean($tableName, $field),
            $this->dbType['date'] => $formFeGenerateField->generateDateTime('date', $tableName, $field),
            $this->dbType['dateTime'], $this->dbType['timestamp'] => $formFeGenerateField->generateDateTime(
                'dateTime',
                $tableName,
                $field,
            ),
            $this->dbType['time'] => $formFeGenerateField->generateDateTime('time', $tableName, $field),
            $this->dbType['year'] => $formFeGenerateField->generateDateTime('year', $tableName, $field),
            $this->dbType['string'] => $formFeGenerateField->generateInput(
                'input',
                $tableName,
                $field,
                dbType: $this->dbType['string'],
            ),
            $this->dbType['text'] => $formFeGenerateField->generateInput('textarea', $tableName, $field),
            $this->dbType['longtext'] => $formFeGenerateField->generateTinymce($tableName, $field),
            $this->dbType['enum'] => $formFeGenerateField->generateEnum($tableName, $field),
            $this->dbType['json'] => $formFeGenerateField->generateJson($tableName, $field),
            default => '',
        };

        return implode($this->serviceGenerator->infy_nl_tab(1, 3, 2), $fieldsGenerate);
    }

    private function _checkTemplateStartsWith($templateFormItem)
    {
        if (!strpos($templateFormItem, '<el-form-item')) {
            $templateFormItem = "<$templateFormItem";
        }

        return $templateFormItem;
    }
}
