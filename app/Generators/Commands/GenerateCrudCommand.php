<?php

namespace App\Generators\Commands;

use Illuminate\Console\Command;
use App\Generators\Common\CommandData;
use App\Generators\Utils\GeneratorFieldsInputUtil;
use App\Generators\Generators\ModelGenerator;
use App\Generators\Generators\ControllerGenerator;
use App\Generators\Generators\RequestGenerator;
use App\Generators\Generators\MigrationGenerator;
use App\Generators\Generators\ViewGenerator;
use App\Generators\Generators\MenuGenerator;
use App\Generators\Generators\PermissionGenerator;
use App\Generators\Generators\SeederGenerator;

class GenerateCrudCommand extends Command
{
    protected $signature = 'generate:crud
                            {model : The name of the model}
                            {--fields= : Fields definition (name:type:htmlType:options)}
                            {--schema= : Path to JSON schema file}
                            {--no-migration : Skip migration generation}
                            {--no-controller : Skip controller generation}
                            {--no-model : Skip model generation}
                            {--no-views : Skip views generation}
                            {--no-request : Skip request generation}
                            {--no-routes : Skip routes generation}
                            {--with-factory : Generate factory}
                            {--with-seeder : Generate seeder}
                            {--with-test : Generate test}
                            {--no-menu : Skip menu generation}
                            {--no-permissions : Skip permissions generation}';

    protected $description = 'Generate CRUD operations for a model';

    public function handle()
    {
        $modelName = $this->argument('model');
        $fieldsInput = $this->option('fields');
        $schemaPath = $this->option('schema');

        // Parse options
        $options = [];
        if ($this->option('no-migration')) $options[] = '--no-migration';
        if ($this->option('no-controller')) $options[] = '--no-controller';
        if ($this->option('no-model')) $options[] = '--no-model';
        if ($this->option('no-views')) $options[] = '--no-views';
        if ($this->option('no-request')) $options[] = '--no-request';
        if ($this->option('no-routes')) $options[] = '--no-routes';
        if ($this->option('with-factory')) $options[] = '--with-factory';
        if ($this->option('with-seeder')) $options[] = '--with-seeder';
        if ($this->option('with-test')) $options[] = '--with-test';
        if ($this->option('no-menu')) $options[] = '--no-menu';
        if ($this->option('no-permissions')) $options[] = '--no-permissions';

        try {
            // Parse fields
            $fields = [];
            if ($schemaPath) {
                $fields = GeneratorFieldsInputUtil::parseFieldsFromJson($schemaPath);
            } elseif ($fieldsInput) {
                $fieldsArray = explode(',', $fieldsInput);
                $fields = GeneratorFieldsInputUtil::parseFieldsFromCommand($fieldsArray);
            } else {
                $this->error('You must provide either --fields or --schema option');
                return 1;
            }

            // Create command data
            $commandData = new CommandData($modelName, $fields, $options);

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
                $generators[] = new RequestGenerator($commandData);
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

            $this->info("CRUD generation completed successfully!");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function addRoutes(CommandData $commandData): void
    {
        $routeName = $commandData->getRouteName();
        $controllerName = $commandData->controllerName;

        $routes = "
// {{MODEL_NAME}} routes
Route::resource('{$routeName}', App\\Http\\Controllers\\{$controllerName}::class);
";

        $webRoutesPath = base_path('routes/web.php');
        $currentContent = file_get_contents($webRoutesPath);

        if (!str_contains($currentContent, "Route::resource('{$routeName}'")) {
            file_put_contents($webRoutesPath, $currentContent . $routes);
            $this->info("✓ Added routes for {$routeName}");
        }
    }
}
