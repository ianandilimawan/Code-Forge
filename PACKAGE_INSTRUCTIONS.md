# Package Instructions

## How to Use This Package

### 1. Install Package

To use this package in a new Laravel project, install via Composer:

```bash
composer require ianandilimawan/code-forge
```

The package will be automatically registered because it uses auto-discovery in Laravel 12.

### 2. Publish Assets

After installation, publish all assets (migrations, seeders, models, services, components, and stubs):

```bash
php artisan vendor:publish --tag=laravel-generator
```

Or publish specific assets:

```bash
# Publish all at once
php artisan vendor:publish --tag=laravel-generator

# Publish migrations only
php artisan vendor:publish --tag=laravel-generator-migrations

# Publish seeders only
php artisan vendor:publish --tag=laravel-generator-seeders

# Publish models only
php artisan vendor:publish --tag=laravel-generator-models

# Publish services only
php artisan vendor:publish --tag=laravel-generator-services

# Publish components only
php artisan vendor:publish --tag=laravel-generator-components

# Publish stubs only (for customization)
php artisan vendor:publish --tag=laravel-generator-stubs
```

### 3. Run Migrations and Seeders

After publishing, run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=MenuSeeder
```

### 4. Publish Stubs (Optional)

If you want to customize stub templates:

```bash
php artisan vendor:publish --tag=laravel-generator-stubs
```

Stub files will be copied to `resources/stubs/laravel-generator/` for customization.

### 5. Use Generator

After all assets are published and migrations are run, you can directly use the command:

```bash
php artisan generate:scaffold Product --fields="name:string:text:required,price:decimal:number:required"
```

## How to Publish Package to Packagist

### 1. Create Git Repository

1. Repository already exists on GitHub: [ianandilimawan/Code-Forge](https://github.com/ianandilimawan/Code-Forge)
2. Push all files from the `laravel-generator/` folder to the repository:

```bash
cd laravel-generator
git init
git remote add origin git@github.com:ianandilimawan/Code-Forge.git
git add .
git commit -m "Initial commit"
git push -u origin main
```

### 2. Setup Packagist

1. Register/login to [Packagist.org](https://packagist.org)
2. Submit package with your GitHub/GitLab repository URL
3. Packagist will automatically update the package whenever there's a push to the repository

### 3. Install Package

After the package is published, install with:

```bash
composer require ianandilimawan/code-forge
```

## Development

### Testing Package Locally

To test the package locally before publishing, add to your project's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../admin-template/laravel-generator"
    }
  ],
  "require": {
    "ianandilimawan/code-forge": "*"
  }
}
```

Then install:

```bash
composer require ianandilimawan/code-forge
```

## File Structure

```
laravel-generator/
├── composer.json
├── README.md
├── PACKAGE_INSTRUCTIONS.md
├── .gitignore
├── database/
│   ├── migrations/
│   │   ├── 2025_10_26_052823_create_menus_table.php
│   │   ├── 2025_10_26_052825_create_permissions_table.php
│   │   ├── 2025_10_27_035141_create_roles_table.php
│   │   ├── 2025_11_03_092857_create_role_permission_table.php
│   │   ├── 2025_11_03_093247_create_user_role_table.php
│   │   ├── 2025_11_03_094032_create_user_permission_table.php
│   │   ├── 2025_11_03_095924_add_permission_id_to_menus_table.php
│   │   ├── 2025_11_04_054202_create_activity_logs_table.php
│   │   └── 2025_11_04_055805_add_section_title_to_menus_table.php
│   └── seeders/
│       ├── MenuSeeder.php
│       └── RolePermissionSeeder.php
├── src/
│   ├── LaravelGeneratorServiceProvider.php
│   ├── Commands/
│   │   ├── GenerateCrudCommand.php
│   │   └── GenerateScaffoldCommand.php
│   ├── Common/
│   │   ├── CommandData.php
│   │   └── GeneratorField.php
│   ├── Generators/
│   │   ├── BaseGenerator.php
│   │   ├── ControllerGenerator.php
│   │   ├── CreateRequestGenerator.php
│   │   ├── FactoryGenerator.php
│   │   ├── MenuGenerator.php
│   │   ├── MigrationGenerator.php
│   │   ├── ModelGenerator.php
│   │   ├── PermissionGenerator.php
│   │   ├── RequestGenerator.php
│   │   ├── SeederGenerator.php
│   │   ├── UnitTestGenerator.php
│   │   ├── UpdateRequestGenerator.php
│   │   └── ViewGenerator.php
│   ├── Models/
│   │   ├── ActivityLog.php
│   │   ├── Menu.php
│   │   ├── Permission.php
│   │   └── Role.php
│   ├── Services/
│   │   ├── ActivityLogService.php
│   │   └── FileUploadService.php
│   └── Utils/
│       ├── FileUtil.php
│       └── GeneratorFieldsInputUtil.php
├── resources/
│   └── views/
│       └── components/
│           └── admin/
│               ├── confirm-delete-modal.blade.php
│               ├── menu-item.blade.php
│               └── user-form-modal.blade.php
└── stubs/
    ├── controller.stub
    ├── factory.stub
    ├── migration.stub
    ├── model.stub
    ├── request.stub
    ├── request/
    │   ├── create.stub
    │   └── update.stub
    ├── seeder.stub
    ├── test.stub
    └── view/
        ├── create.stub
        ├── edit.stub
        ├── import.stub
        ├── index.stub
        └── show.stub
```
