<?php

namespace IanAndilimawan\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use IanAndilimawan\LaravelGenerator\Common\CommandData;
use IanAndilimawan\LaravelGenerator\Utils\GeneratorFieldsInputUtil;
use IanAndilimawan\LaravelGenerator\Generators\ModelGenerator;
use IanAndilimawan\LaravelGenerator\Generators\ControllerGenerator;
use IanAndilimawan\LaravelGenerator\Generators\RequestGenerator;
use IanAndilimawan\LaravelGenerator\Generators\CreateRequestGenerator;
use IanAndilimawan\LaravelGenerator\Generators\UpdateRequestGenerator;
use IanAndilimawan\LaravelGenerator\Generators\MigrationGenerator;
use IanAndilimawan\LaravelGenerator\Generators\ViewGenerator;
use IanAndilimawan\LaravelGenerator\Generators\MenuGenerator;
use IanAndilimawan\LaravelGenerator\Generators\PermissionGenerator;
use IanAndilimawan\LaravelGenerator\Generators\SeederGenerator;
use IanAndilimawan\LaravelGenerator\Generators\UnitTestGenerator;
use IanAndilimawan\LaravelGenerator\Generators\FactoryGenerator;
use IanAndilimawan\LaravelGenerator\Common\GeneratorField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GenerateScaffoldCommand extends Command
{
    protected $signature = 'generate:scaffold
                            {model? : The name of the model}
                            {--fromTable : Generate from existing table structure}
                            {--tableName= : The name of the existing database table}
                            {--fields= : Fields definition (name:type:htmlType:options)}
                            {--schema= : Path to JSON schema file}
                            {--with-factory : Generate factory}
                            {--with-seeder : Generate seeder}
                            {--migration : Generate migration}
                            {--no-controller : Skip controller generation}
                            {--no-model : Skip model generation}
                            {--no-views : Skip views generation}
                            {--no-request : Skip request generation}
                            {--no-routes : Skip routes generation}
                            {--no-menu : Skip menu generation}
                            {--no-permissions : Skip permissions generation}
                            {--no-test : Skip test generation}
                            {--section-title= : Section title for the menu}';

    protected $description = 'Generate CRUD operations - from table structure or field definition';

    public function handle()
    {
        $modelName = $this->argument('model');
        $tableName = $this->option('tableName');
        $fieldsInput = $this->option('fields');
        $schemaPath = $this->option('schema');

        // Parse options
        $options = [];
        if ($this->option('with-factory')) $options[] = '--with-factory';
        if ($this->option('with-seeder')) $options[] = '--with-seeder';
        if ($this->option('migration')) $options[] = '--migration';
        if ($this->option('no-controller')) $options[] = '--no-controller';
        if ($this->option('no-model')) $options[] = '--no-model';
        if ($this->option('no-views')) $options[] = '--no-views';
        if ($this->option('no-request')) $options[] = '--no-request';
        if ($this->option('no-routes')) $options[] = '--no-routes';
        if ($this->option('no-menu')) $options[] = '--no-menu';
        if ($this->option('no-permissions')) $options[] = '--no-permissions';
        if ($this->option('no-test')) $options[] = '--no-test';

        try {
            // If fromTable, generate from existing table
            if ($this->option('fromTable')) {
                // Use tableName option if provided, otherwise use model name
                $actualTableName = $tableName ?? $modelName;
                if (!$actualTableName) {
                    $this->error('Please provide model name or use --tableName option');
                    return 1;
                }
                return $this->generateFromExistingTable($actualTableName, $modelName, $options);
            }

            // Otherwise, parse fields
            $fields = [];
            if ($schemaPath) {
                $fields = GeneratorFieldsInputUtil::parseFieldsFromJson($schemaPath);
            } elseif ($fieldsInput) {
                $fieldsArray = explode(',', $fieldsInput);
                $fields = GeneratorFieldsInputUtil::parseFieldsFromCommand($fieldsArray);
            } else {
                $this->error('You must provide either --fields, --schema, or --fromTable option');
                return 1;
            }

            // Create command data
            $commandData = new CommandData($modelName, $fields, $options);

            // Handle section title
            if ($commandData->withMenu) {
                $sectionTitle = $this->getSectionTitle();
                if ($sectionTitle !== null) {
                    $commandData->sectionTitle = $sectionTitle;
                }
            }

            $this->info("Generating CRUD for {$modelName}...");

            // Generate files
            $generators = [];

            if ($commandData->withModel) {
                $generators[] = new ModelGenerator($commandData);
            }

            if ($commandData->withController) {
                $generators[] = new ControllerGenerator($commandData);
            }

            if ($commandData->withRequest) {
                $generators[] = new CreateRequestGenerator($commandData);
                $generators[] = new UpdateRequestGenerator($commandData);
            }

            if ($commandData->withMigration) {
                $generators[] = new MigrationGenerator($commandData);
            }

            if ($commandData->withViews) {
                $generators[] = new ViewGenerator($commandData);
            }

            if ($commandData->withSeeder) {
                $generators[] = new SeederGenerator($commandData);
            }

            if ($commandData->withTest) {
                // Generate factory when test is enabled (factory is needed for tests)
                $generators[] = new FactoryGenerator($commandData);
                $generators[] = new UnitTestGenerator($commandData);
            } elseif ($commandData->withFactory) {
                // Generate factory even if test is disabled (if explicitly requested)
                $generators[] = new FactoryGenerator($commandData);
            }

            if ($commandData->withMenu) {
                $generators[] = new MenuGenerator($commandData);
            }

            if ($commandData->withPermissions) {
                $generators[] = new PermissionGenerator($commandData);
            }

            // Execute generators
            foreach ($generators as $generator) {
                if ($generator->generate()) {
                    $this->info("✓ Generated: " . get_class($generator));
                } else {
                    $this->error("✗ Failed to generate: " . get_class($generator));
                }
            }

            // Add routes
            if ($commandData->withRoutes) {
                $this->addRoutes($commandData);
            }

            // Regenerate autoloader to make new classes available
            $this->regenerateAutoloader();

            $this->info("CRUD generation completed successfully!");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function generateFromExistingTable(string $tableName, ?string $modelName = null, array $options = []): int
    {
        try {
            $this->info("Reading table structure: {$tableName}");

            // Check if table exists
            if (!Schema::hasTable($tableName)) {
                $this->error("Table {$tableName} does not exist!");
                return 1;
            }

            // Get model name from argument or derive from table name
            $modelName = $modelName ?? Str::studly(Str::singular($tableName));

            // Get columns from table
            $columns = $this->getTableColumns($tableName);

            // Convert columns to GeneratorField objects
            $fields = $this->convertColumnsToFields($columns);

            // Check if migration already exists
            $migrationExists = $this->migrationExists($tableName);

            // Only generate migration if --migration flag is set
            if (in_array('--migration', $options)) {
                if ($migrationExists) {
                    $this->warn("Migration file already exists, but will generate anyway due to --migration flag.");
                } else {
                    $this->info("Will generate migration based on table structure (--migration flag set).");
                }
            } else {
                // Migration not requested, skip generation
                if ($migrationExists) {
                    $this->info("Migration file already exists, skipping migration generation.");
                } else {
                    $this->info("Skipping migration generation (use --migration flag to generate).");
                }
                $options[] = '--no-migration';
            }

            // Create command data with options from command
            $commandData = new CommandData($modelName, $fields, $options);

            // Handle section title
            if ($commandData->withMenu && Schema::hasTable('menus')) {
                $sectionTitle = $this->getSectionTitle();
                if ($sectionTitle !== null) {
                    $commandData->sectionTitle = $sectionTitle;
                }
            }

            $this->info("Generating CRUD for {$modelName}...");

            // Generate files
            $generators = [];

            if ($commandData->withModel) {
                $generators[] = new ModelGenerator($commandData);
            }

            if ($commandData->withController) {
                $generators[] = new ControllerGenerator($commandData);
            }

            if ($commandData->withRequest) {
                $generators[] = new CreateRequestGenerator($commandData);
                $generators[] = new UpdateRequestGenerator($commandData);
            }

            // Add migration generator if migration should be generated
            if ($commandData->withMigration) {
                $generators[] = new MigrationGenerator($commandData);
            }

            if ($commandData->withViews) {
                $generators[] = new ViewGenerator($commandData);
            }

            if ($commandData->withSeeder) {
                $generators[] = new SeederGenerator($commandData);
            }

            if ($commandData->withTest) {
                // Generate factory when test is enabled (factory is needed for tests)
                $generators[] = new FactoryGenerator($commandData);
                $generators[] = new UnitTestGenerator($commandData);
            } elseif ($commandData->withFactory) {
                // Generate factory even if test is disabled (if explicitly requested)
                $generators[] = new FactoryGenerator($commandData);
            }

            // Only generate menu/permissions if options allow it and tables exist
            if ($commandData->withMenu) {
                if (Schema::hasTable('menus')) {
                    $generators[] = new MenuGenerator($commandData);
                } else {
                    $this->info("Skipping menu generation: menus table does not exist.");
                }
            }

            if ($commandData->withPermissions) {
                if (Schema::hasTable('permissions')) {
                    $generators[] = new PermissionGenerator($commandData);
                } else {
                    $this->info("Skipping permissions generation: permissions table does not exist.");
                }
            }

            // Execute generators
            foreach ($generators as $generator) {
                if ($generator->generate()) {
                    $this->info("✓ Generated: " . get_class($generator));
                } else {
                    $this->error("✗ Failed to generate: " . get_class($generator));
                }
            }

            // Add routes
            if ($commandData->withRoutes) {
                $this->addRoutes($commandData);
            }

            // Regenerate autoloader to make new classes available
            $this->regenerateAutoloader();

            $this->info("CRUD generation completed successfully!");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function regenerateAutoloader(): void
    {
        $this->info("Regenerating autoloader...");

        try {
            // Clear Laravel's cached config and routes
            $this->call('config:clear');
            $this->call('route:clear');

            // Run composer dump-autoload
            $command = 'composer dump-autoload --quiet';
            $exitCode = 0;
            $output = [];

            // Use exec to capture both output and exit code
            exec($command . ' 2>&1', $output, $exitCode);

            if ($exitCode === 0) {
                $this->info("✓ Autoloader regenerated successfully");
            } else {
                $this->warn("Composer dump-autoload returned exit code: {$exitCode}");
                $this->warn("You may need to run 'composer dump-autoload' manually.");
            }
        } catch (\Exception $e) {
            // Don't fail the whole generation if autoloader regeneration fails
            $this->warn("Could not regenerate autoloader automatically: " . $e->getMessage());
            $this->warn("Please run 'composer dump-autoload' manually.");
        }
    }

    private function getTableColumns(string $tableName): array
    {
        $columns = [];

        try {
            $driver = config('database.default');
            $connection = DB::connection($driver);

            if ($connection->getDriverName() === 'mysql') {
                $columns = $this->getMySQLColumns($connection, $tableName);
            } elseif ($connection->getDriverName() === 'pgsql') {
                $columns = $this->getPostgreSQLColumns($connection, $tableName);
            } elseif ($connection->getDriverName() === 'sqlite') {
                $columns = $this->getSQLiteColumns($connection, $tableName);
            }
        } catch (\Exception $e) {
            $this->warn("Could not get columns via introspection: " . $e->getMessage());
            $this->info("Using schema inspector instead...");

            // Fallback to Laravel's schema
            $columns = Schema::getColumnListing($tableName);
            $columns = array_map(function ($col) use ($tableName) {
                return [
                    'name' => $col,
                    'type' => $this->getColumnType($tableName, $col),
                    'nullable' => false,
                    'default' => null,
                ];
            }, $columns);
        }

        return $columns;
    }

    private function getMySQLColumns($connection, string $tableName): array
    {
        $query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_TYPE
                  FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";

        $databaseName = $connection->getDatabaseName();
        $columns = DB::select($query, [$databaseName, $tableName]);

        $result = [];
        foreach ($columns as $column) {
            if (in_array($column->COLUMN_NAME, ['id', 'created_at', 'updated_at'])) {
                continue; // Skip Laravel default columns
            }

            $result[] = [
                'name' => $column->COLUMN_NAME,
                'type' => $this->convertMySQLType($column->DATA_TYPE, $column->COLUMN_TYPE),
                'nullable' => $column->IS_NULLABLE === 'YES',
                'default' => $column->COLUMN_DEFAULT,
                'column_type' => $column->COLUMN_TYPE, // Keep original for ENUM parsing
            ];
        }

        return $result;
    }

    private function getPostgreSQLColumns($connection, string $tableName): array
    {
        $query = "SELECT column_name, data_type, is_nullable, column_default
                  FROM information_schema.columns
                  WHERE table_name = ?";

        $columns = DB::select($query, [$tableName]);

        $result = [];
        foreach ($columns as $column) {
            if (in_array($column->column_name, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $result[] = [
                'name' => $column->column_name,
                'type' => $this->convertPostgreSQLType($column->data_type),
                'nullable' => $column->is_nullable === 'YES',
                'default' => $column->column_default,
            ];
        }

        return $result;
    }

    private function getSQLiteColumns($connection, string $tableName): array
    {
        $query = "PRAGMA table_info({$tableName})";
        $columns = DB::select($query);

        $result = [];
        foreach ($columns as $column) {
            if (in_array($column->name, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $result[] = [
                'name' => $column->name,
                'type' => $this->convertSQLiteType($column->type),
                'nullable' => !$column->notnull,
                'default' => $column->dflt_value,
            ];
        }

        return $result;
    }

    private function convertMySQLType(string $dataType, string $fullType = ''): string
    {
        $typeMap = [
            'varchar' => 'string',
            'char' => 'string',
            'text' => 'text',
            'longtext' => 'text',
            'mediumtext' => 'text',
            'tinytext' => 'text',
            'int' => 'integer',
            'bigint' => 'integer',
            'smallint' => 'integer',
            'tinyint' => 'boolean',
            'decimal' => 'decimal',
            'float' => 'float',
            'double' => 'double',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'time' => 'time',
            'json' => 'json',
            'boolean' => 'boolean',
        ];

        // Check for enum
        if (strpos($fullType, 'enum') !== false) {
            return 'select';
        }

        return $typeMap[strtolower($dataType)] ?? 'string';
    }

    private function convertPostgreSQLType(string $dataType): string
    {
        $typeMap = [
            'character varying' => 'string',
            'varchar' => 'string',
            'text' => 'text',
            'integer' => 'integer',
            'bigint' => 'integer',
            'smallint' => 'integer',
            'decimal' => 'decimal',
            'numeric' => 'decimal',
            'real' => 'float',
            'double precision' => 'double',
            'date' => 'date',
            'timestamp' => 'timestamp',
            'timestamp with time zone' => 'timestamp',
            'boolean' => 'boolean',
            'json' => 'json',
            'jsonb' => 'json',
        ];

        return $typeMap[strtolower($dataType)] ?? 'string';
    }

    private function convertSQLiteType(string $type): string
    {
        $typeMap = [
            'text' => 'text',
            'integer' => 'integer',
            'real' => 'float',
            'blob' => 'binary',
            'numeric' => 'decimal',
        ];

        return $typeMap[strtolower($type)] ?? 'string';
    }

    private function getColumnType(string $tableName, string $columnName): string
    {
        try {
            $column = Schema::getColumnType($tableName, $columnName);
            return $column;
        } catch (\Exception $e) {
            return 'string';
        }
    }

    private function convertColumnsToFields(array $columns): array
    {
        $fields = [];

        foreach ($columns as $column) {
            $htmlType = $this->determineHtmlType($column['type'], $column['name']);
            $options = [];

            if ($column['nullable']) {
                $options[] = 'nullable';
            }

            // Handle ENUM fields
            if (isset($column['column_type']) && strpos($column['column_type'], 'enum') !== false) {
                $htmlType = 'select';
                $enumValues = $this->parseEnumValues($column['column_type']);
                if (!empty($enumValues)) {
                    $options[] = 'options:' . implode(',', $enumValues);
                }
            }

            $fields[] = new GeneratorField(
                $column['name'],
                $column['type'],
                $htmlType,
                $options
            );
        }

        return $fields;
    }

    private function determineHtmlType(string $dbType, string $fieldName = ''): string
    {
        $typeMap = [
            'text' => 'textarea',
            'boolean' => 'checkbox',
            'date' => 'date',
            'datetime' => 'date',
            'timestamp' => 'date',
            'integer' => 'number',
            'decimal' => 'number',
            'float' => 'number',
            'double' => 'number',
            'select' => 'select',
            'json' => 'tags', // JSON fields become tags
        ];

        // Check if field name suggests file/image upload
        if (
            str_contains(strtolower($fieldName), 'image') ||
            str_contains(strtolower($fieldName), 'file') ||
            str_contains(strtolower($fieldName), 'photo') ||
            str_contains(strtolower($fieldName), 'banner')
        ) {
            return 'file';
        }

        return $typeMap[$dbType] ?? 'text';
    }

    private function parseEnumValues(string $columnType): array
    {
        // Parse ENUM('value1','value2','value3') format
        if (preg_match("/enum\s*\(\s*'([^']+)'(?:\s*,\s*'([^']+)')*\)/i", $columnType, $matches)) {
            $values = [];
            // Extract all quoted values
            preg_match_all("/'([^']+)'/", $columnType, $allMatches);
            return $allMatches[1];
        }

        return [];
    }

    private function getSectionTitle(): ?string
    {
        // Check if section-title option is provided
        $sectionTitleOption = $this->option('section-title');

        if ($sectionTitleOption !== null && $sectionTitleOption !== '') {
            return $sectionTitleOption;
        }

        // If not provided via option, prompt interactively
        if (Schema::hasTable('menus')) {
            // Get existing section titles
            $existingSections = \App\Models\Menu::whereNotNull('section_title')
                ->distinct()
                ->pluck('section_title')
                ->toArray();

            if (!empty($existingSections)) {
                $this->info('Available section titles:');
                foreach ($existingSections as $index => $section) {
                    $this->line('  ' . ($index + 1) . '. ' . $section);
                }
                $this->line('  ' . (count($existingSections) + 1) . '. Create new section title');
                $this->line('  ' . (count($existingSections) + 2) . '. No section title (auto-detect)');

                $choice = $this->ask('Select section title (or press Enter to auto-detect):');

                if ($choice === '' || $choice === null) {
                    return null; // Auto-detect
                }

                $choiceIndex = (int) $choice;

                if ($choiceIndex >= 1 && $choiceIndex <= count($existingSections)) {
                    return $existingSections[$choiceIndex - 1];
                } elseif ($choiceIndex === count($existingSections) + 1) {
                    // Create new section title
                    $newTitle = $this->ask('Enter new section title:');
                    return $newTitle ?: null;
                } elseif ($choiceIndex === count($existingSections) + 2) {
                    return null; // No section title
                }
            } else {
                // No existing sections, ask if user wants to create one
                $createNew = $this->confirm('No existing section titles found. Create a new section title?', false);

                if ($createNew) {
                    $newTitle = $this->ask('Enter section title:');
                    return $newTitle ?: null;
                }
            }
        }

        return null; // Default: auto-detect
    }

    private function migrationExists(string $tableName): bool
    {
        $migrationFiles = glob(database_path("migrations/*_create_{$tableName}_table.php"));
        return !empty($migrationFiles);
    }

    private function addRoutes(CommandData $commandData): void
    {
        // Route path doesn't need admin prefix (it's already in Route::prefix('admin'))
        // Route name also doesn't need admin prefix because it's already in Route::name('admin.') group
        // So we use relative route name (without admin prefix) and Laravel will add it automatically
        $routeName = $commandData->getRouteName(); // e.g., 'admin.activity_logs'
        $routePath = str_replace('admin.', '', $routeName); // Remove admin prefix for route path: 'activity_logs'
        $routeNameWithoutPrefix = str_replace('admin.', '', $routeName); // Remove admin prefix for route name: 'activity_logs'
        $controllerName = $commandData->controllerName;
        $modelNameTitle = $commandData->modelNameTitle; // e.g., 'Product' or 'Activity Log'

        // Import routes must be defined BEFORE resource route to avoid conflicts
        $routes = "
        // {$modelNameTitle} routes
        Route::get('{$routePath}/import', [{$controllerName}::class, 'importForm'])->name('{$routeNameWithoutPrefix}.importForm');
        Route::post('{$routePath}/import', [{$controllerName}::class, 'import'])->name('{$routeNameWithoutPrefix}.import');
        Route::get('{$routePath}/sample/{format?}', [{$controllerName}::class, 'downloadSample'])->name('{$routeNameWithoutPrefix}.downloadSample');
        Route::resource('{$routePath}', {$controllerName}::class);
";

        $webRoutesPath = base_path('routes/web.php');
        $currentContent = file_get_contents($webRoutesPath);

        // Check if route already exists (check for route path without admin prefix)
        if (str_contains($currentContent, "Route::resource('{$routePath}'")) {
            // Even if route exists, ensure controller import exists
            $controllerImport = "use App\\Http\\Controllers\\{$controllerName};";
            if (!str_contains($currentContent, $controllerImport)) {
                $this->addControllerImport($webRoutesPath, $currentContent, $controllerImport);
                $currentContent = file_get_contents($webRoutesPath); // Refresh content after adding import
            }

            // Check if import routes already exist - check for route name pattern (with both single and double quotes)
            // Check both with and without admin prefix (for existing routes that might have been added incorrectly)
            $routeNameWithoutPrefix = str_replace('admin.', '', $routeName);
            $importFormRouteName = "->name('{$routeNameWithoutPrefix}.importForm')";
            $importFormRouteNameAlt = "->name(\"{$routeNameWithoutPrefix}.importForm\")";
            $importFormRouteNameWithPrefix = "->name('{$routeName}.importForm')";
            $importFormRouteNameWithPrefixAlt = "->name(\"{$routeName}.importForm\")";
            $importRouteName = "->name('{$routeNameWithoutPrefix}.import')";
            $importRouteNameAlt = "->name(\"{$routeNameWithoutPrefix}.import\")";
            $importRouteNameWithPrefix = "->name('{$routeName}.import')";
            $importRouteNameWithPrefixAlt = "->name(\"{$routeName}.import\")";
            $downloadSampleRouteName = "->name('{$routeNameWithoutPrefix}.downloadSample')";
            $downloadSampleRouteNameAlt = "->name(\"{$routeNameWithoutPrefix}.downloadSample\")";
            $downloadSampleRouteNameWithPrefix = "->name('{$routeName}.downloadSample')";
            $downloadSampleRouteNameWithPrefixAlt = "->name(\"{$routeName}.downloadSample\")";

            $needsImportFormRoute = !str_contains($currentContent, $importFormRouteName) && !str_contains($currentContent, $importFormRouteNameAlt)
                && !str_contains($currentContent, $importFormRouteNameWithPrefix) && !str_contains($currentContent, $importFormRouteNameWithPrefixAlt);
            $needsImportRoute = !str_contains($currentContent, $importRouteName) && !str_contains($currentContent, $importRouteNameAlt)
                && !str_contains($currentContent, $importRouteNameWithPrefix) && !str_contains($currentContent, $importRouteNameWithPrefixAlt);
            $needsSampleRoute = !str_contains($currentContent, $downloadSampleRouteName) && !str_contains($currentContent, $downloadSampleRouteNameAlt)
                && !str_contains($currentContent, $downloadSampleRouteNameWithPrefix) && !str_contains($currentContent, $downloadSampleRouteNameWithPrefixAlt);

            if ($needsImportFormRoute || $needsImportRoute || $needsSampleRoute) {
                // Add import routes BEFORE the resource route to avoid conflicts
                $lines = explode("\n", $currentContent);
                $modelNameTitle = $commandData->modelNameTitle; // e.g., 'Product' or 'Activity Log'

                for ($i = 0; $i < count($lines); $i++) {
                    if (str_contains($lines[$i], "Route::resource('{$routePath}'")) {
                        $importRoutes = "";

                        if ($needsImportFormRoute || $needsImportRoute) {
                            $importRoutes .= "
        // {$modelNameTitle} routes
        Route::get('{$routePath}/import', [{$controllerName}::class, 'importForm'])->name('{$routeNameWithoutPrefix}.importForm');
        Route::post('{$routePath}/import', [{$controllerName}::class, 'import'])->name('{$routeNameWithoutPrefix}.import');";
                        }

                        if ($needsSampleRoute) {
                            // If we already added import routes, add sample route without comment
                            if ($needsImportFormRoute || $needsImportRoute) {
                                $importRoutes .= "
        Route::get('{$routePath}/sample/{format?}', [{$controllerName}::class, 'downloadSample'])->name('{$routeNameWithoutPrefix}.downloadSample');";
                            } else {
                                // If only sample route needed, add comment
                                $importRoutes .= "
        // {$modelNameTitle} routes
        Route::get('{$routePath}/sample/{format?}', [{$controllerName}::class, 'downloadSample'])->name('{$routeNameWithoutPrefix}.downloadSample');";
                            }
                        }

                        if ($importRoutes) {
                            // Insert import routes BEFORE resource route
                            $lines[$i] = $importRoutes . "\n" . $lines[$i];
                            $newContent = implode("\n", $lines);
                            file_put_contents($webRoutesPath, $newContent);
                            $this->info("✓ Added import routes for {$routeName}");
                            return;
                        }
                    }
                }
            }
            return;
        }

        // Ensure controller import exists before adding route
        $controllerImport = "use App\\Http\\Controllers\\{$controllerName};";
        if (!str_contains($currentContent, $controllerImport)) {
            $currentContent = $this->addControllerImport($webRoutesPath, $currentContent, $controllerImport);
        }

        $lines = explode("\n", $currentContent);
        $insertLine = -1;

        // Look for "// Resource routes" comment within auth middleware group
        for ($i = 0; $i < count($lines); $i++) {
            // Check if this line has "// Resource routes"
            if (str_contains($lines[$i], "// Resource routes")) {
                // Find the last Route::resource in this section
                for ($j = $i + 1; $j < count($lines); $j++) {
                    if (str_contains($lines[$j], "Route::resource")) {
                        $insertLine = $j;
                    } elseif (preg_match('/^\s*\}\);?\s*$/', $lines[$j])) {
                        // Found closing bracket, stop
                        break;
                    }
                }

                if ($insertLine !== -1) {
                    // Insert after the last resource route
                    $lines[$insertLine] = $lines[$insertLine] . $routes;
                    $newContent = implode("\n", $lines);
                    file_put_contents($webRoutesPath, $newContent);
                    $this->info("✓ Added routes for {$routeName} inside admin group with auth middleware");
                    return;
                }

                // If no Route::resource found after comment, insert right after comment
                $insertLine = $i + 1;
                $lines[$insertLine] = $routes . "\n" . $lines[$insertLine];
                $newContent = implode("\n", $lines);
                file_put_contents($webRoutesPath, $newContent);
                $this->info("✓ Added routes for {$routeName} inside admin group with auth middleware");
                return;
            }
        }

        // If no "// Resource routes" found, look for Route::resource in auth middleware
        for ($i = 0; $i < count($lines); $i++) {
            if (str_contains($lines[$i], "Route::middleware('auth')") || str_contains($lines[$i], "Route::middleware(\"auth\"")) {
                // Find Route::resource inside this group
                $bracketCount = 1;
                for ($j = $i + 1; $j < count($lines); $j++) {
                    $bracketCount += substr_count($lines[$j], '{') - substr_count($lines[$j], '}');

                    if (str_contains($lines[$j], "Route::resource")) {
                        $insertLine = $j;
                    }

                    if ($bracketCount === 0) {
                        // End of auth middleware group
                        if ($insertLine !== -1) {
                            $lines[$insertLine] = $lines[$insertLine] . $routes;
                        } else {
                            // Insert before closing bracket
                            $lines[$j] = $routes . "\n" . $lines[$j];
                        }
                        $newContent = implode("\n", $lines);
                        file_put_contents($webRoutesPath, $newContent);
                        $this->info("✓ Added routes for {$routePath} inside admin group with auth middleware");
                        return;
                    }
                }
                break;
            }
        }

        // If no suitable location found, create admin group with auth middleware
        $adminGroup = "
Route::prefix('admin')->name('admin.')->group(function () {
    // Protected admin routes
    Route::middleware('auth')->group(function () {
        // Resource routes
{$routes}
    });
});";

        file_put_contents($webRoutesPath, $currentContent . $adminGroup);
        $this->info("✓ Created admin group and added routes for {$routePath}");
    }

    private function addControllerImport(string $webRoutesPath, string $currentContent, string $controllerImport): string
    {
        // Find last use statement and add import
        $usePattern = "/^use\s+.*?;$/m";
        if (preg_match_all($usePattern, $currentContent, $matches, PREG_OFFSET_CAPTURE)) {
            $lastMatch = end($matches[0]);
            $insertPos = $lastMatch[1] + strlen($lastMatch[0]);
            $currentContent = substr_replace($currentContent, "\n" . $controllerImport, $insertPos, 0);
        } else {
            // No use statements found, add after opening tag
            $currentContent = str_replace('<?php', "<?php\n\n" . $controllerImport, $currentContent);
        }

        file_put_contents($webRoutesPath, $currentContent);
        return $currentContent;
    }
}
