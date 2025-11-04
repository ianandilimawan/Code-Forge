<?php

namespace App\Generators\Common;

class CommandData
{
    public string $modelName;
    public string $modelNamePlural;
    public string $modelNameCamel;
    public string $modelNameSnake;
    public string $modelNameKebab;
    public string $modelNameLower;
    public string $modelNameLowerPlural;
    public string $modelNameUpper;
    public string $modelNameTitle;

    public array $fields = [];
    public array $relations = [];
    public array $options = [];

    public bool $withMigration = false;
    public bool $withController = true;
    public bool $withModel = true;
    public bool $withViews = true;
    public bool $withRequest = true;
    public bool $withRoutes = true;
    public bool $withFactory = false;
    public bool $withSeeder = false;
    public bool $withTest = true;
    public bool $withMenu = true;
    public bool $withPermissions = true;
    public ?string $sectionTitle = null;

    public string $controllerName;
    public string $requestName;
    public string $createRequestName;
    public string $updateRequestName;
    public string $factoryName;
    public string $seederName;
    public string $testName;

    public function __construct(string $modelName, array $fields = [], array $options = [])
    {
        $this->modelName = $modelName;
        $this->fields = $fields;
        $this->options = $options;

        $this->generateNames();
        $this->parseOptions();
    }

    private function generateNames(): void
    {
        $pluralName = str($this->modelName)->plural();
        // Add space before capital letters for readable title (e.g., ProductImages -> Product Images)
        $this->modelNamePlural = preg_replace('/(?<!^)([A-Z])/', ' $1', $pluralName);
        $this->modelNameCamel = str($this->modelName)->camel();
        $this->modelNameSnake = str($this->modelName)->snake();
        $this->modelNameKebab = str($this->modelName)->kebab();
        $this->modelNameLower = str($this->modelName)->lower();
        $this->modelNameLowerPlural = str($this->modelName)->lower()->plural();
        $this->modelNameUpper = str($this->modelName)->upper();
        // Add space before capital letters for readable title (e.g., ActivityLog -> Activity Log)
        $this->modelNameTitle = preg_replace('/(?<!^)([A-Z])/', ' $1', $this->modelName);

        $this->controllerName = $this->modelName . 'Controller';
        $this->requestName = $this->modelName . 'Request';
        $this->createRequestName = 'Create' . $this->modelName . 'Request';
        $this->updateRequestName = 'Update' . $this->modelName . 'Request';
        $this->factoryName = $this->modelName . 'Factory';
        $this->seederName = $this->modelName . 'Seeder';
        $this->testName = $this->modelName . 'Test';
    }

    private function parseOptions(): void
    {
        foreach ($this->options as $option) {
            switch ($option) {
                case '--migration':
                    $this->withMigration = true;
                    break;
                case '--no-migration':
                    $this->withMigration = false;
                    break;
                case '--no-controller':
                    $this->withController = false;
                    break;
                case '--no-model':
                    $this->withModel = false;
                    break;
                case '--no-views':
                    $this->withViews = false;
                    break;
                case '--no-request':
                    $this->withRequest = false;
                    break;
                case '--no-routes':
                    $this->withRoutes = false;
                    break;
                case '--with-factory':
                    $this->withFactory = true;
                    break;
                case '--with-seeder':
                    $this->withSeeder = true;
                    break;
                case '--with-test':
                    $this->withTest = true;
                    break;
                case '--no-test':
                    $this->withTest = false;
                    break;
                case '--no-menu':
                    $this->withMenu = false;
                    break;
                case '--no-permissions':
                    $this->withPermissions = false;
                    break;
            }
        }
    }

    public function getTableName(): string
    {
        return $this->modelNameSnake . 's';
    }

    public function getRouteName(): string
    {
        // Route dibuat di dalam Route::prefix('admin')->name('admin.')->group()
        // jadi route path tidak perlu prefix admin. karena sudah ada di prefix()
        // route name akan otomatis menjadi admin.{route_name} karena name('admin.')
        // Jadi kita perlu return dengan prefix admin. untuk digunakan di views/controller
        return 'admin.' . $this->modelNameSnake . 's';
    }

    public function getViewPath(): string
    {
        return $this->modelNameSnake . 's';
    }

    public function getNamespace(): string
    {
        return 'App\\Models';
    }

    public function getControllerNamespace(): string
    {
        return 'App\\Http\\Controllers';
    }

    public function getRequestNamespace(): string
    {
        return 'App\\Http\\Requests';
    }

    public function getFactoryNamespace(): string
    {
        return 'Database\\Factories';
    }

    public function getSeederNamespace(): string
    {
        return 'Database\\Seeders';
    }

    public function getTestNamespace(): string
    {
        return 'Tests\\Feature';
    }
}
