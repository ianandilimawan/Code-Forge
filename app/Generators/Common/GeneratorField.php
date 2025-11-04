<?php

namespace App\Generators\Common;

class GeneratorField
{
    public string $name;
    public string $dbType;
    public string $htmlType;
    public array $htmlInputs = [];
    public array $validations = [];
    public array $options = [];
    public bool $nullable = false;
    public bool $fillable = true;
    public bool $searchable = false;
    public bool $sortable = false;
    public ?string $defaultValue = null;
    public ?string $description = null;

    public function __construct(
        string $name,
        string $dbType = 'string',
        string $htmlType = 'text',
        array $options = []
    ) {
        $this->name = $name;
        $this->dbType = $dbType;
        $this->htmlType = $htmlType;
        $this->options = $options;

        $this->parseOptions();
    }

    private function parseOptions(): void
    {
        foreach ($this->options as $option) {
            $this->parseOption($option);
        }
    }

    private function parseOption(string $option): void
    {
        if (str_contains($option, ':')) {
            [$key, $value] = explode(':', $option, 2);

            switch ($key) {
                case 'nullable':
                    $this->nullable = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'fillable':
                    $this->fillable = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'searchable':
                    $this->searchable = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'sortable':
                    $this->sortable = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'default':
                    $this->defaultValue = $value;
                    break;
                case 'description':
                    $this->description = $value;
                    break;
                case 'validation':
                    $this->validations = explode(',', $value);
                    break;
                case 'options':
                    $this->htmlInputs = explode(',', $value);
                    break;
            }
        } else {
            // Handle boolean flags
            switch ($option) {
                case 'nullable':
                    $this->nullable = true;
                    break;
                case 'searchable':
                    $this->searchable = true;
                    break;
                case 'sortable':
                    $this->sortable = true;
                    break;
            }
        }
    }

    public function getMigrationDefinition(): string
    {
        $definition = "\$table->{$this->dbType}('{$this->name}')";

        if ($this->nullable) {
            $definition .= '->nullable()';
        }

        if ($this->defaultValue !== null) {
            $definition .= "->default('{$this->defaultValue}')";
        }

        return $definition . ';';
    }

    public function getValidationRules(): string
    {
        if (empty($this->validations)) {
            return $this->nullable ? 'nullable' : 'required';
        }

        return implode('|', $this->validations);
    }

    public function getFormInput(): string
    {
        switch ($this->htmlType) {
            case 'textarea':
                return "<textarea name=\"{$this->name}\" class=\"block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" rows=\"3\">{{\$" . strtolower($this->name) . " ?? ''}}</textarea>";

            case 'select':
                $options = '';
                foreach ($this->htmlInputs as $option) {
                    $options .= "<option value=\"{$option}\">{$option}</option>\n";
                }
                return "<select name=\"{$this->name}\" class=\"block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\">\n{$options}</select>";

            case 'checkbox':
                return "<input type=\"checkbox\" name=\"{$this->name}\" value=\"1\" class=\"h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700\" {{\$" . strtolower($this->name) . " ? 'checked' : ''}}>";

            case 'date':
                return "<input type=\"date\" name=\"{$this->name}\" class=\"block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . strtolower($this->name) . " ?? ''}}\">";

            case 'email':
                return "<input type=\"email\" name=\"{$this->name}\" class=\"block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . strtolower($this->name) . " ?? ''}}\">";

            case 'password':
                return "<input type=\"password\" name=\"{$this->name}\" class=\"block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\">";

            case 'number':
                return "<input type=\"number\" name=\"{$this->name}\" class=\"block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . strtolower($this->name) . " ?? ''}}\">";

            case 'file':
                return "<input type=\"file\" name=\"{$this->name}\" class=\"block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\">";

            default: // text
                return "<input type=\"text\" name=\"{$this->name}\" class=\"block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . strtolower($this->name) . " ?? ''}}\">";
        }
    }

    public function getTableHeader(): string
    {
        return "<th class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">" .
            ucfirst(str_replace('_', ' ', $this->name)) .
            "</th>";
    }

    public function getTableCell(): string
    {
        return "<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\">{{\$item->{$this->name}}}</td>";
    }
}
