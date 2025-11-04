<?php

namespace IanAndilimawan\LaravelGenerator\Utils;

use Illuminate\Support\Facades\File;

class FileUtil
{
    public static function createFile(string $path, string $contents): bool
    {
        $directory = dirname($path);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return File::put($path, $contents) !== false;
    }

    public static function createFileIfNotExists(string $path, string $contents): bool
    {
        if (File::exists($path)) {
            return false;
        }

        return self::createFile($path, $contents);
    }

    public static function getFileContents(string $path): string
    {
        if (!File::exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        return File::get($path);
    }

    public static function getStubContents(string $stubName): string
    {
        // Handle stub paths with subdirectories (e.g., "request/create", "view/index")
        $stubPath = __DIR__ . "/../stubs/{$stubName}.stub";

        if (!File::exists($stubPath)) {
            // Try fallback to user's stubs directory if package stub doesn't exist
            $fallbackPath = resource_path("stubs/laravel-generator/{$stubName}.stub");
            if (File::exists($fallbackPath)) {
                $stubPath = $fallbackPath;
            } else {
                throw new \Exception("Stub not found: {$stubPath}");
            }
        }

        return File::get($stubPath);
    }

    public static function replaceTemplate(string $template, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $template = str_replace($search, $replace, $template);
        }

        return $template;
    }

    public static function getModelPath(string $modelName): string
    {
        return app_path("Models/{$modelName}.php");
    }

    public static function getControllerPath(string $controllerName): string
    {
        return app_path("Http/Controllers/{$controllerName}.php");
    }

    public static function getRequestPath(string $requestName): string
    {
        return app_path("Http/Requests/{$requestName}.php");
    }

    public static function getMigrationPath(string $tableName): string
    {
        $timestamp = date('Y_m_d_His');
        return database_path("migrations/{$timestamp}_create_{$tableName}_table.php");
    }

    public static function getViewPath(string $viewName): string
    {
        return resource_path("views/{$viewName}.blade.php");
    }

    public static function getFactoryPath(string $factoryName): string
    {
        return database_path("factories/{$factoryName}.php");
    }

    public static function getSeederPath(string $seederName): string
    {
        return database_path("seeders/{$seederName}.php");
    }

    public static function getTestPath(string $testName): string
    {
        return base_path("tests/Feature/{$testName}.php");
    }

    public static function delete(string $path): bool
    {
        return File::delete($path);
    }

    public static function deleteDirectory(string $path): bool
    {
        return File::deleteDirectory($path);
    }
}
