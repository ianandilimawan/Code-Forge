<?php

namespace IanAndilimawan\LaravelGenerator\Generators;

use IanAndilimawan\LaravelGenerator\Utils\FileUtil;

class MigrationGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $template = FileUtil::getStubContents('migration');
        $outputPath = FileUtil::getMigrationPath($this->commandData->getTableName());

        $replacements = array_merge($this->getReplacements(), [
            '{{MIGRATION_FIELDS}}' => $this->getMigrationFields(),
        ]);

        return $this->generateFile($template, $outputPath, $replacements);
    }

    public function rollback(): bool
    {
        // Find and delete the migration file
        $migrationFiles = glob(database_path("migrations/*_create_{$this->commandData->getTableName()}_table.php"));

        if (!empty($migrationFiles)) {
            return FileUtil::delete($migrationFiles[0]);
        }

        return false;
    }

    private function getMigrationFields(): string
    {
        $fields = [];

        // Add ID field
        $fields[] = '            $table->id();';

        // Add timestamps
        $fields[] = '            $table->timestamps();';

        // Add soft deletes
        $fields[] = '            $table->softDeletes();';

        // Add custom fields
        foreach ($this->commandData->fields as $field) {
            $fields[] = '            ' . $field->getMigrationDefinition();
        }

        return implode("\n", $fields);
    }
}
