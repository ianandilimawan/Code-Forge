<?php

namespace App\Generators\Generators;

use App\Generators\Utils\FileUtil;

class FactoryGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $template = FileUtil::getStubContents('factory');
        $outputPath = FileUtil::getFactoryPath($this->commandData->factoryName);

        $replacements = array_merge($this->getReplacements(), [
            '{{FACTORY_FIELDS}}' => $this->getFactoryFields(),
        ]);

        return $this->generateFile($template, $outputPath, $replacements);
    }

    public function rollback(): bool
    {
        $outputPath = FileUtil::getFactoryPath($this->commandData->factoryName);
        return FileUtil::delete($outputPath);
    }

    private function getFactoryFields(): string
    {
        $fields = [];
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Skip timestamp fields - Laravel handles them automatically
            if (in_array($field->name, $timestampFields)) {
                continue;
            }

            $fieldDefinition = $this->getFactoryFieldDefinition($field);
            if ($fieldDefinition) {
                $fields[] = "            " . $fieldDefinition;
            }
        }

        if (empty($fields)) {
            return "            // No fields defined";
        }

        return implode(",\n", $fields);
    }

    private function getFactoryFieldDefinition($field): ?string
    {
        // Get field name
        $fieldName = $field->name;

        // Determine if field is nullable
        $isNullable = !in_array('required', $field->validations ?? []);
        $isRequired = in_array('required', $field->validations ?? []);

        // Generate factory value based on field type
        switch ($field->dbType) {
            case 'string':
                if ($fieldName === 'email') {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->unique()->safeEmail(),"
                        : "'{$fieldName}' => fake()->unique()->safeEmail(),";
                } elseif ($fieldName === 'slug') {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->unique()->slug(),"
                        : "'{$fieldName}' => fake()->unique()->slug(),";
                } elseif ($fieldName === 'sku') {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->unique()->bothify('SKU-####-????'),"
                        : "'{$fieldName}' => fake()->unique()->bothify('SKU-####-????'),";
                } elseif (str_contains($fieldName, 'uuid') || str_contains($fieldName, 'certification_number')) {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->uuid(),"
                        : "'{$fieldName}' => fake()->uuid(),";
                } elseif (str_contains($fieldName, 'name') || str_contains($fieldName, 'title')) {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->words(3, true),"
                        : "'{$fieldName}' => fake()->words(3, true),";
                } else {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->sentence(),"
                        : "'{$fieldName}' => fake()->sentence(),";
                }

            case 'text':
                return $isNullable
                    ? "'{$fieldName}' => fake()->optional()->paragraph(),"
                    : "'{$fieldName}' => fake()->paragraph(),";

            case 'integer':
            case 'bigInteger':
            case 'smallInteger':
            case 'tinyInteger':
            case 'mediumInteger':
                if ($fieldName === 'stock' || str_contains($fieldName, 'quantity') || str_contains($fieldName, 'qty')) {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->numberBetween(0, 1000),"
                        : "'{$fieldName}' => fake()->numberBetween(0, 1000),";
                } elseif (str_contains($fieldName, 'id') && $fieldName !== 'id') {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->numberBetween(1, 10),"
                        : "'{$fieldName}' => fake()->numberBetween(1, 10),";
                } else {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->numberBetween(1, 100),"
                        : "'{$fieldName}' => fake()->numberBetween(1, 100),";
                }

            case 'decimal':
            case 'float':
            case 'double':
                if (str_contains($fieldName, 'price') || str_contains($fieldName, 'cost') || str_contains($fieldName, 'amount')) {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->randomFloat(2, 1000, 100000),"
                        : "'{$fieldName}' => fake()->randomFloat(2, 1000, 100000),";
                } elseif (str_contains($fieldName, 'weight') || str_contains($fieldName, 'length') || str_contains($fieldName, 'width') || str_contains($fieldName, 'height')) {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->randomFloat(2, 0.1, 100),"
                        : "'{$fieldName}' => fake()->randomFloat(2, 0.1, 100),";
                } else {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->randomFloat(2, 0, 1000),"
                        : "'{$fieldName}' => fake()->randomFloat(2, 0, 1000),";
                }

            case 'boolean':
                return $isNullable
                    ? "'{$fieldName}' => fake()->optional()->boolean(),"
                    : "'{$fieldName}' => fake()->boolean(),";

            case 'date':
                return $isNullable
                    ? "'{$fieldName}' => fake()->optional()->date(),"
                    : "'{$fieldName}' => fake()->date(),";

            case 'datetime':
            case 'timestamp':
                if (str_contains($fieldName, 'preorder')) {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->dateTimeBetween('now', '+3 months'),"
                        : "'{$fieldName}' => fake()->dateTimeBetween('now', '+3 months'),";
                } else {
                    return $isNullable
                        ? "'{$fieldName}' => fake()->optional()->dateTime(),"
                        : "'{$fieldName}' => fake()->dateTime(),";
                }

            case 'json':
                return $isNullable
                    ? "'{$fieldName}' => json_encode(fake()->optional()->words(5)),"
                    : "'{$fieldName}' => json_encode(fake()->words(5)),";

            default:
                // For unknown types, try to generate based on field name
                if ($isNullable) {
                    return "'{$fieldName}' => fake()->optional()->word(),";
                } else {
                    return "'{$fieldName}' => fake()->word(),";
                }
        }
    }
}
