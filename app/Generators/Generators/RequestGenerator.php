<?php

namespace App\Generators\Generators;

use App\Generators\Utils\FileUtil;

class RequestGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $template = FileUtil::getStubContents('request');
        $outputPath = FileUtil::getRequestPath($this->commandData->requestName);

        $replacements = array_merge($this->getReplacements(), [
            '{{VALIDATION_RULES}}' => $this->getValidationRules(),
        ]);

        return $this->generateFile($template, $outputPath, $replacements);
    }

    public function rollback(): bool
    {
        $outputPath = FileUtil::getRequestPath($this->commandData->requestName);
        return FileUtil::delete($outputPath);
    }

    private function getValidationRules(): string
    {
        $rules = [];
        $timestampFields = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Skip timestamp fields - Laravel handles them automatically
            if (in_array($field->name, $timestampFields)) {
                continue;
            }

            $rule = $field->getValidationRules();
            $rules[] = "            '{$field->name}' => '{$rule}',";
        }

        return implode("\n", $rules);
    }
}
