<?php

namespace App\Generators\Generators;

use App\Generators\Utils\FileUtil;

class UpdateRequestGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $templateData = FileUtil::getStubContents('request/update');
        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile(FileUtil::getRequestPath($this->commandData->updateRequestName), $templateData);

        return true;
    }

    public function rollback(): bool
    {
        return FileUtil::delete(FileUtil::getRequestPath($this->commandData->updateRequestName));
    }

    private function fillTemplate(string $template): string
    {
        $template = str_replace('{{MODEL_NAME}}', $this->commandData->modelName, $template);
        $template = str_replace('{{MODEL_NAME_PLURAL}}', $this->commandData->modelNamePlural, $template);
        $template = str_replace('{{MODEL_NAME_SNAKE}}', $this->commandData->modelNameSnake, $template);
        $template = str_replace('{{MODEL_NAME_LOWER}}', $this->commandData->modelNameLower, $template);
        $template = str_replace('{{MODEL_NAME_LOWER_PLURAL}}', $this->commandData->modelNameLowerPlural, $template);
        $template = str_replace('{{VALIDATION_RULES}}', $this->getValidationRules(), $template);

        return $template;
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

            // For update requests, make rules more flexible (nullable unless required)
            $rule = $field->getValidationRules();
            if (!$field->nullable && !str_contains($rule, 'required')) {
                $rule = 'nullable|' . $rule;
            }
            $rules[] = "            '{$field->name}' => '{$rule}',";
        }

        return implode("\n", $rules);
    }
}
