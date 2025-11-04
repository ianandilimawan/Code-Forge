<?php

namespace IanAndilimawan\LaravelGenerator\Utils;

use IanAndilimawan\LaravelGenerator\Common\GeneratorField;

class GeneratorFieldsInputUtil
{
    public static function parseFieldsFromCommand(array $fieldsInput): array
    {
        $fields = [];

        foreach ($fieldsInput as $fieldInput) {
            $fields[] = self::parseField($fieldInput);
        }

        return $fields;
    }

    public static function parseField(string $fieldInput): GeneratorField
    {
        // Format: name:dbType:htmlType:options
        // Example: name:string:text:nullable,searchable
        // Example: email:string:email:required,email
        // Example: status:string:select:options=active,inactive

        // Split by colon but handle validation rules properly
        $parts = preg_split('/:(?![^,]*,[^,]*:)/', $fieldInput);

        $name = $parts[0] ?? 'field';
        $dbType = $parts[1] ?? 'string';
        $htmlType = $parts[2] ?? 'text';
        $options = isset($parts[3]) ? explode(',', $parts[3]) : [];

        // Clean up options - remove empty values and trim
        $options = array_filter(array_map('trim', $options));

        return new GeneratorField($name, $dbType, $htmlType, $options);
    }

    public static function parseFieldsFromJson(string $jsonPath): array
    {
        if (!file_exists($jsonPath)) {
            throw new \Exception("Schema file not found: {$jsonPath}");
        }

        $schema = json_decode(file_get_contents($jsonPath), true);

        if (!$schema) {
            throw new \Exception("Invalid JSON schema file: {$jsonPath}");
        }

        $fields = [];

        foreach ($schema['fields'] as $fieldData) {
            $options = [];

            if (isset($fieldData['nullable']) && $fieldData['nullable']) {
                $options[] = 'nullable';
            }

            if (isset($fieldData['searchable']) && $fieldData['searchable']) {
                $options[] = 'searchable';
            }

            if (isset($fieldData['sortable']) && $fieldData['sortable']) {
                $options[] = 'sortable';
            }

            if (isset($fieldData['validation'])) {
                $options[] = 'validation:' . implode(',', $fieldData['validation']);
            }

            if (isset($fieldData['options'])) {
                $options[] = 'options:' . implode(',', $fieldData['options']);
            }

            if (isset($fieldData['default'])) {
                $options[] = 'default:' . $fieldData['default'];
            }

            if (isset($fieldData['description'])) {
                $options[] = 'description:' . $fieldData['description'];
            }

            $fields[] = new GeneratorField(
                $fieldData['name'],
                $fieldData['dbType'] ?? 'string',
                $fieldData['htmlType'] ?? 'text',
                $options
            );
        }

        return $fields;
    }
}
