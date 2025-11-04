<?php

namespace IanAndilimawan\LaravelGenerator\Generators;

use IanAndilimawan\LaravelGenerator\Utils\FileUtil;

class CreateRequestGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $templateData = FileUtil::getStubContents('request/create');
        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile(FileUtil::getRequestPath($this->commandData->createRequestName), $templateData);

        return true;
    }

    public function rollback(): bool
    {
        return FileUtil::delete(FileUtil::getRequestPath($this->commandData->createRequestName));
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

            $rule = $field->getValidationRules();
            $rules[] = "            '{$field->name}' => '{$rule}',";
        }

        return implode("\n", $rules);
    }
}
