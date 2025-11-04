<?php

namespace IanAndilimawan\LaravelGenerator\Generators;

use IanAndilimawan\LaravelGenerator\Utils\FileUtil;

class SeederGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $template = FileUtil::getStubContents('seeder');
        $outputPath = FileUtil::getSeederPath($this->commandData->seederName);

        $replacements = array_merge($this->getReplacements(), [
            '{{SAMPLE_DATA}}' => $this->getSampleData(),
        ]);

        return $this->generateFile($template, $outputPath, $replacements);
    }

    public function rollback(): bool
    {
        $outputPath = FileUtil::getSeederPath($this->commandData->seederName);
        return FileUtil::delete($outputPath);
    }

    private function getSampleData(): string
    {
        $data = [];

        foreach ($this->commandData->fields as $field) {
            if ($field->name === 'id' || $field->name === 'created_at' || $field->name === 'updated_at') {
                continue;
            }

            $value = $this->getSampleValue($field);
            $data[] = "            '{$field->name}' => {$value},";
        }

        return implode("\n", $data);
    }

    private function getSampleValue($field): string
    {
        switch ($field->htmlType) {
            case 'email':
                return "'sample@example.com'";
            case 'password':
                return "'password'";
            case 'number':
                return '100';
            case 'checkbox':
                return 'true';
            case 'date':
                return "'2024-01-01'";
            case 'select':
                if (!empty($field->htmlInputs)) {
                    return "'{$field->htmlInputs[0]}'";
                }
                return "'option1'";
            case 'textarea':
                return "'Sample description text'";
            default:
                return "'Sample {$field->name}'";
        }
    }
}
