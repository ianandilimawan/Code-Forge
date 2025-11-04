<?php

namespace IanAndilimawan\LaravelGenerator\Generators;

use IanAndilimawan\LaravelGenerator\Utils\FileUtil;

class ControllerGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $template = FileUtil::getStubContents('controller');
        $outputPath = FileUtil::getControllerPath($this->commandData->controllerName);

        $replacements = array_merge($this->getReplacements(), [
            '{{CREATE_REQUEST_CLASS}}' => $this->commandData->createRequestName,
            '{{UPDATE_REQUEST_CLASS}}' => $this->commandData->updateRequestName,
            '{{MODEL_VARIABLE}}' => $this->commandData->modelNameCamel,
            '{{MODEL_VARIABLE_PLURAL}}' => $this->commandData->modelNameCamel . 's',
            '{{MODEL_NAME_SNAKE}}' => $this->commandData->modelNameSnake,
        ]);

        return $this->generateFile($template, $outputPath, $replacements);
    }

    public function rollback(): bool
    {
        $outputPath = FileUtil::getControllerPath($this->commandData->controllerName);
        return FileUtil::delete($outputPath);
    }
}
