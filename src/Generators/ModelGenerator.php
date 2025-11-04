<?php

namespace IanAndilimawan\LaravelGenerator\Generators;

use IanAndilimawan\LaravelGenerator\Utils\FileUtil;

class ModelGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $template = FileUtil::getStubContents('model');
        $outputPath = FileUtil::getModelPath($this->commandData->modelName);

        $replacements = array_merge($this->getReplacements(), [
            '{{FILLABLE_FIELDS}}' => $this->getFillableFields(),
            '{{CASTS}}' => $this->getCasts(),
            '{{RELATIONS}}' => $this->getRelations(),
        ]);

        return $this->generateFile($template, $outputPath, $replacements);
    }

    public function rollback(): bool
    {
        $outputPath = FileUtil::getModelPath($this->commandData->modelName);
        return FileUtil::delete($outputPath);
    }

    private function getFillableFields(): string
    {
        $fillableFields = [];
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Skip timestamp fields from fillable - Laravel handles them automatically
            if (in_array($field->name, $timestampFields)) {
                continue;
            }

            if ($field->fillable) {
                $fillableFields[] = "'{$field->name}'";
            }
        }

        return implode(', ', $fillableFields);
    }

    private function getCasts(): string
    {
        $casts = [];

        foreach ($this->commandData->fields as $field) {
            switch ($field->dbType) {
                case 'boolean':
                    $casts[] = "'{$field->name}' => 'boolean'";
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $casts[] = "'{$field->name}' => 'datetime'";
                    break;
                case 'json':
                    $casts[] = "'{$field->name}' => 'array'";
                    break;
            }
        }

        if (empty($casts)) {
            return '';
        }

        return "protected \$casts = [\n        " . implode(",\n        ", $casts) . "\n    ];";
    }

    private function getRelations(): string
    {
        // This can be extended to handle relationships
        return '';
    }
}
