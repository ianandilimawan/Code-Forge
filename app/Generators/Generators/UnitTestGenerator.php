<?php

namespace App\Generators\Generators;

use App\Generators\Utils\FileUtil;

class UnitTestGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $template = FileUtil::getStubContents('test');
        $outputPath = FileUtil::getTestPath($this->commandData->testName);

        $replacements = array_merge($this->getReplacements(), [
            '{{MODEL_VARIABLE}}' => $this->commandData->modelNameCamel,
            '{{MODEL_VARIABLE_PLURAL}}' => $this->commandData->modelNameCamel . 's',
            '{{VALID_CREATE_DATA}}' => $this->getValidCreateData(),
            '{{VALID_UPDATE_DATA}}' => $this->getValidUpdateData(),
        ]);

        return $this->generateFile($template, $outputPath, $replacements);
    }

    public function rollback(): bool
    {
        $outputPath = FileUtil::getTestPath($this->commandData->testName);
        return FileUtil::delete($outputPath);
    }

    private function getValidCreateData(): string
    {
        $fields = [];
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Skip timestamp fields - Laravel handles them automatically
            if (in_array($field->name, $timestampFields)) {
                continue;
            }

            // Only include required fields for create data
            $isRequired = in_array('required', $field->validations ?? []) || (!$field->nullable && empty($field->validations));

            if ($isRequired && $field->fillable) {
                $value = $this->getTestDataValue($field);
                $fields[] = "            '{$field->name}' => {$value},";
            }
        }

        if (empty($fields)) {
            return "            // Add your required fields here based on the model";
        }

        return implode("\n", $fields);
    }

    private function getValidUpdateData(): string
    {
        $fields = [];
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Skip timestamp fields - Laravel handles them automatically
            if (in_array($field->name, $timestampFields)) {
                continue;
            }

            // Include required fields for update data
            $isRequired = in_array('required', $field->validations ?? []) || (!$field->nullable && empty($field->validations));

            if ($isRequired && $field->fillable) {
                $value = $this->getTestDataValue($field, true);
                $fields[] = "            '{$field->name}' => {$value},";
            }
        }

        if (empty($fields)) {
            return "            // Add your required fields here based on the model";
        }

        return implode("\n", $fields);
    }

    private function getTestDataValue($field, bool $isUpdate = false): string
    {
        $fieldName = $field->name;
        $dbType = $field->dbType;
        $prefix = $isUpdate ? 'Updated ' : '';

        switch ($dbType) {
            case 'string':
                if ($fieldName === 'email') {
                    return "'{$prefix}test@example.com'";
                } elseif ($fieldName === 'slug') {
                    return "'" . strtolower($prefix . $this->commandData->modelName) . '-' . ($isUpdate ? 'updated' : 'test') . "'";
                } elseif (str_contains($fieldName, 'name') || str_contains($fieldName, 'title')) {
                    return "'{$prefix}Test {$this->commandData->modelNameTitle}'";
                } else {
                    return "'{$prefix}Test Value'";
                }

            case 'text':
                return "'{$prefix}Test description'";

            case 'integer':
            case 'bigInteger':
            case 'smallInteger':
            case 'tinyInteger':
            case 'mediumInteger':
                if ($fieldName === 'stock' || str_contains($fieldName, 'quantity') || str_contains($fieldName, 'qty')) {
                    return $isUpdate ? '200' : '100';
                } elseif (str_contains($fieldName, 'id') && $fieldName !== 'id') {
                    return '1';
                } else {
                    return $isUpdate ? '2' : '1';
                }

            case 'decimal':
            case 'float':
            case 'double':
                if (str_contains($fieldName, 'price') || str_contains($fieldName, 'cost') || str_contains($fieldName, 'amount')) {
                    return $isUpdate ? '150000' : '100000';
                } else {
                    return $isUpdate ? '2.5' : '1.5';
                }

            case 'boolean':
                return $isUpdate ? 'true' : 'false';

            case 'date':
                return "'" . date('Y-m-d') . "'";

            case 'datetime':
            case 'timestamp':
                return "'" . date('Y-m-d H:i:s') . "'";

            case 'json':
                return "['test', 'data']";

            default:
                return "'{$prefix}Test Value'";
        }
    }
}
