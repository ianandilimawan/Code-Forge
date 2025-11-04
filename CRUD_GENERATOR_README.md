# Laravel CRUD Generator

A modern Laravel CRUD generator similar to webcore but updated for Laravel 12 with Tailwind CSS support.

**Unified Command**: All functionality is accessible via `generate:scaffold` command!

## Features

- Generate complete CRUD operations with a single command
- Support for various field types (text, textarea, select, checkbox, date, email, password, number, file)
- Modern Tailwind CSS templates
- JSON schema support for complex field definitions
- Validation rules generation
- Optional migration generation (use `--migration` flag)
- Model, Controller, Request, and View generation
- **Automatic unit test generation** (enabled by default)
- Route registration

## Installation

The generator is already integrated into your Laravel project. No additional installation required.

## Usage

### Quick Start

**From Existing Database Table:**

```bash
php artisan generate:scaffold {ModelName} --fromTable --tableName={table_name}
```

**From Field Definitions:**

```bash
php artisan generate:scaffold {ModelName} --fields="field1:type:htmlType:options"
```

**From JSON Schema:**

```bash
php artisan generate:scaffold {ModelName} --schema=path/to/schema.json
```

Replace `{ModelName}` and placeholders with your actual values.

---

### Generate from Existing Database Table

Generate CRUD from an existing database table:

```bash
php artisan generate:scaffold Blog --fromTable --tableName=blog
```

**Format:**

```bash
php artisan generate:scaffold {ModelName} --fromTable --tableName={table_name}
```

**Example with MySQL:**

```bash
# 1. Update .env with your database credentials
DB_CONNECTION=mysql
DB_HOST=your_database_host
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

# 2. Connect to your database and generate from existing tables
php artisan generate:scaffold Blog --fromTable --tableName=blog
php artisan generate:scaffold Post --fromTable --tableName=posts
php artisan generate:scaffold Category --fromTable --tableName=categories

# Replace {ModelName} and {table_name} with your actual model and table names
```

**Example with SQLite:**

```bash
# SQLite uses .env or database/database.sqlite
DB_CONNECTION=sqlite
# DB_DATABASE is optional for SQLite

# Generate from existing SQLite tables
php artisan generate:scaffold User --fromTable --tableName=users
```

**Note:**

- Replace placeholders with your actual database credentials
- The `{ModelName}` will be used for generated class names (Blog, Post, etc.)
- The `{table_name}` should match your actual database table name
- Works with MySQL, PostgreSQL, and SQLite

This will:

- Read the table structure from the database
- Automatically detect column types (int, varchar, text, json, date, etc.)
- **Detect ENUM fields and generate select dropdowns with proper options**
- Generate Model with proper fillable and casts
- Generate Controller, Request, Views
- **Skip migration generation by default** (use `--migration` flag to generate migration)
- Skip Menu/Permissions if tables don't exist

**ENUM Support:**
When the generator detects ENUM columns (like `visible ENUM('show','hide')`), it automatically:

- Creates a select dropdown in forms
- Includes all ENUM values as options
- Handles proper selection in edit forms

**Rich Text Editor:**
For `text` and `textarea` fields, the generator automatically includes:

- TinyMCE rich text editor
- Full toolbar with formatting options
- Image upload and media support
- Clean, modern interface

**Switch Components:**
For `boolean` fields, the generator creates:

- Modern toggle switch instead of checkbox
- Smooth animations and transitions
- Proper form handling (hidden input for false values)
- Accessible design with proper labels

### Generate from Fields

Generate CRUD for a model with field definitions:

```bash
php artisan generate:scaffold {ModelName} --fields="field1:type:htmlType:options,field2:type:htmlType:options"
```

**Example:**

```bash
php artisan generate:scaffold Product --fields="name:string:text:required,description:text:textarea:nullable,price:decimal:number:required"
```

### Generate from JSON Schema

Create a schema file in `resources/schemas/` with your field definitions:

```json
{
  "model": "Product",
  "fields": [
    {
      "name": "name",
      "dbType": "string",
      "htmlType": "text",
      "validation": ["required", "string", "max:255"],
      "searchable": true,
      "sortable": true
    },
    {
      "name": "description",
      "dbType": "text",
      "htmlType": "textarea",
      "validation": ["nullable", "string"]
    },
    {
      "name": "price",
      "dbType": "decimal",
      "htmlType": "number",
      "validation": ["required", "numeric", "min:0"]
    },
    {
      "name": "category",
      "dbType": "string",
      "htmlType": "select",
      "validation": ["required", "string"],
      "options": ["Electronics", "Clothing", "Books", "Home", "Sports"]
    },
    {
      "name": "is_active",
      "dbType": "boolean",
      "htmlType": "checkbox",
      "validation": ["boolean"],
      "default": true
    }
  ]
}
```

Then generate using the schema:

```bash
php artisan generate:scaffold {ModelName} --schema=resources/schemas/your_schema.json
```

**Example:**

```bash
php artisan generate:scaffold Product --schema=resources/schemas/product.json
```

### Field Types

#### Database Types

- `string` - VARCHAR
- `text` - TEXT
- `integer` - INTEGER
- `decimal` - DECIMAL
- `boolean` - BOOLEAN
- `date` - DATE
- `datetime` - DATETIME
- `timestamp` - TIMESTAMP
- `json` - JSON

#### HTML Types

- `text` - Text input
- `textarea` - Textarea
- `select` - Select dropdown
- `checkbox` - Checkbox
- `date` - Date input
- `email` - Email input
- `password` - Password input
- `number` - Number input
- `file` - File input

### Field Options

- `nullable` - Make field nullable
- `searchable` - Enable search functionality
- `sortable` - Enable sorting
- `required` - Make field required
- `validation:rule1,rule2` - Custom validation rules
- `options:option1,option2` - Options for select fields
- `default:value` - Default value

### Command Options

- `--migration` - Generate migration file (migration is **not generated by default**)
- `--no-migration` - Skip migration generation (default behavior)
- `--no-controller` - Skip controller generation
- `--no-model` - Skip model generation
- `--no-views` - Skip views generation
- `--no-request` - Skip request generation
- `--no-routes` - Skip routes generation
- `--no-menu` - Skip menu generation
- `--no-permissions` - Skip permissions generation
- `--no-test` - Skip test generation (tests are **generated by default**)
- `--with-factory` - Generate factory
- `--with-seeder` - Generate seeder
- `--with-test` - Generate test (default behavior)
- `--section-title=` - Section title for the menu

### Examples

**Generate a simple blog post (without migration):**

```bash
php artisan generate:scaffold Post --fields="title:string:text:required,max:255,content:text:textarea:required,status:string:select:required,options:published,draft,featured_at:datetime:date:nullable"
```

**Generate a blog post with migration:**

```bash
php artisan generate:scaffold Post --fields="title:string:text:required,max:255,content:text:textarea:required,status:string:select:required,options:published,draft,featured_at:datetime:date:nullable" --migration
```

**Generate a user model with factory and seeder (without migration):**

```bash
php artisan generate:scaffold User --fields="name:string:text:required,email:string:email:required,unique:users,password:string:password:required,min:8,is_admin:boolean:checkbox:default:false" --with-factory --with-seeder
```

**Generate a user model with migration, factory and seeder:**

```bash
php artisan generate:scaffold User --fields="name:string:text:required,email:string:email:required,unique:users,password:string:password:required,min:8,is_admin:boolean:checkbox:default:false" --migration --with-factory --with-seeder
```

**Generate from existing database table (without migration):**

```bash
# For MySQL
php artisan generate:scaffold Blog --fromTable --tableName=blog

# For PostgreSQL
php artisan generate:scaffold Article --fromTable --tableName=articles

# For SQLite
php artisan generate:scaffold Product --fromTable --tableName=products
```

**Generate from existing database table with migration:**

```bash
# Generate migration based on existing table structure
php artisan generate:scaffold Blog --fromTable --tableName=blog --migration

# For PostgreSQL
php artisan generate:scaffold Article --fromTable --tableName=articles --migration

# For SQLite
php artisan generate:scaffold Product --fromTable --tableName=products --migration
```

## Generated Files

The generator creates the following files:

- **Model**: `app/Models/{ModelName}.php`
- **Controller**: `app/Http/Controllers/{ModelName}Controller.php`
- **Request**: `app/Http/Requests/{ModelName}Request.php`
- **Migration**: `database/migrations/{timestamp}_create_{table_name}_table.php` (only if `--migration` flag is used)
- **Views**: `resources/views/{model_name}s/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`
- **Test**: `tests/Feature/{ModelName}Test.php` (generated by default, use `--no-test` to skip)
- **Routes**: Added to `routes/web.php`

## Unit Testing

The generator automatically creates comprehensive unit tests for all CRUD operations. Tests are generated by default to help minimize errors and ensure code quality.

### Running Tests

**Run all tests:**

```bash
php artisan test
```

**Run tests for a specific model:**

```bash
php artisan test --filter {ModelName}Test
```

**Example:**

```bash
# Run all Product tests
php artisan test --filter ProductTest

# Run all Blog tests
php artisan test --filter BlogTest
```

**Run tests with coverage:**

```bash
php artisan test --coverage
```

**Run a specific test method:**

```bash
php artisan test --filter test_index_page_is_accessible
```

### Generated Test Coverage

The generated tests include:

- ✅ **Index page** - Tests that the index page is accessible
- ✅ **Create page** - Tests that the create page is accessible
- ✅ **Store method** - Tests creating new records with validation
- ✅ **Show page** - Tests displaying record details
- ✅ **Edit page** - Tests that the edit page is accessible
- ✅ **Update method** - Tests updating records with validation
- ✅ **Destroy method** - Tests deleting records
- ✅ **Validation** - Tests required field validation
- ✅ **Authorization** - Tests unauthorized access is denied

### Customizing Tests

After generation, you can customize the test file at `tests/Feature/{ModelName}Test.php`. You'll need to:

1. **Fill in test data** - Update `getValidCreateData()` and `getValidUpdateData()` methods with actual field values
2. **Add custom assertions** - Add additional test cases specific to your model
3. **Update database assertions** - Modify `getDatabaseAssertionData()` if needed

**Example test data:**

```php
protected function getValidCreateData(): array
{
    return [
        'name' => 'Test Product',
        'price' => 100000,
        'description' => 'Test description',
        'status' => 'active',
    ];
}
```

### Skipping Test Generation

To skip test generation, use the `--no-test` flag:

```bash
php artisan generate:scaffold Product --fields="name:string:text:required" --no-test
```

## Customization

### Templates

You can customize the generated templates by modifying the stub files in `resources/stubs/`:

- `model.stub` - Model template
- `controller.stub` - Controller template
- `request.stub` - Request template
- `migration.stub` - Migration template
- `test.stub` - Unit test template
- `view/index.stub` - Index view template
- `view/create.stub` - Create view template
- `view/edit.stub` - Edit view template
- `view/show.stub` - Show view template

### Adding New Field Types

To add new field types, modify the `GeneratorField` class in `app/Generators/Common/GeneratorField.php` and add the appropriate HTML generation logic in the `getFormInput()` method.

## Requirements

- Laravel 12+
- PHP 8.2+
- Tailwind CSS (for styling)

## License

MIT License
