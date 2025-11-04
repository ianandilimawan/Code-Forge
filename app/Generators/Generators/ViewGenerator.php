<?php

namespace App\Generators\Generators;

use App\Generators\Utils\FileUtil;

class ViewGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $views = ['index', 'create', 'edit', 'show', 'import'];
        $success = true;

        // Generate fields.blade.php partial first
        $fieldsTemplate = $this->getFormFields();
        $fieldsOutputPath = FileUtil::getViewPath("{$this->commandData->getViewPath()}/fields");
        if (!$this->generateFile($fieldsTemplate, $fieldsOutputPath, [])) {
            $success = false;
        }

        foreach ($views as $view) {
            $template = FileUtil::getStubContents("view/{$view}");
            $outputPath = FileUtil::getViewPath("{$this->commandData->getViewPath()}/{$view}");

            $viewPath = $this->commandData->getViewPath();
            $replacements = array_merge($this->getReplacements(), [
                '{{FORM_FIELDS}}' => "@include('{$viewPath}.fields')",
                '{{TABLE_HEADERS}}' => $this->getTableHeaders(),
                '{{TABLE_CELLS}}' => $this->getTableCells(),
                '{{FORM_INPUTS}}' => $this->getFormInputs(),
                '{{FIELD_COUNT}}' => count($this->commandData->fields) + 1, // +1 for Actions column
                '{{CSV_SAMPLE}}' => $this->getCsvSample(),
            ]);

            if (!$this->generateFile($template, $outputPath, $replacements)) {
                $success = false;
            }
        }

        return $success;
    }

    public function rollback(): bool
    {
        $viewPath = resource_path("views/{$this->commandData->getViewPath()}");
        return FileUtil::deleteDirectory($viewPath);
    }

    private function getFormFields(): string
    {
        $fields = [];
        $scriptsAndStyles = [];
        $hiddenFields = [];

        // Separate timestamp fields from regular fields
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Check if this is a timestamp field
            if (in_array($field->name, $timestampFields)) {
                // Add as hidden field instead of regular input
                $modelVar = $this->commandData->modelNameCamel;
                $hiddenFields[] = "                    <input type=\"hidden\" name=\"{$field->name}\" value=\"{{\$" . $modelVar . "->{$field->name} ?? ''}}\">";
            } else {
                // Regular field - add as visible form input
                $fields[] = "                <div>";
                $fields[] = "                    <label for=\"{$field->name}\" class=\"block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2\">" .
                    ucfirst(str_replace('_', ' ', $field->name)) . "</label>";
                $fields[] = "                    <div>" . $this->getFormInputForField($field, $scriptsAndStyles) . "</div>";
                $fields[] = "                    @error('{$field->name}')";
                $fields[] = "                        <p class=\"mt-1 text-sm text-red-600 dark:text-red-400\">{{ \$message }}</p>";
                $fields[] = "                    @enderror";
                $fields[] = "                </div>";
            }
        }

        // Add hidden fields at the beginning
        if (!empty($hiddenFields)) {
            array_unshift($fields, "                <!-- Hidden timestamp fields -->");
            foreach ($hiddenFields as $hiddenField) {
                array_unshift($fields, $hiddenField);
            }
        }

        // Check if title and slug fields exist for auto-generate slug feature
        $hasTitleField = false;
        $hasSlugField = false;
        foreach ($this->commandData->fields as $field) {
            if ($field->name === 'title') {
                $hasTitleField = true;
            }
            if ($field->name === 'slug') {
                $hasSlugField = true;
            }
        }

        // Check if there are currency fields
        $hasCurrencyFields = false;
        foreach ($scriptsAndStyles as $item) {
            if (isset($item['type']) && $item['type'] === 'currency') {
                $hasCurrencyFields = true;
                break;
            }
        }

        // Add scripts and styles at the bottom
        // Always add scripts if there are fields that need them OR if auto-generate slug is needed OR if there are currency fields
        if (!empty($scriptsAndStyles) || ($hasTitleField && $hasSlugField) || $hasCurrencyFields) {
            $fields[] = "";
            $fields[] = "@push('scripts')";
            $fields[] = "<style>";
            $fields[] = $this->getGlobalStyles();
            $fields[] = "</style>";
            $fields[] = "";
            $fields[] = "<script>";
            $fields[] = $this->getGlobalScripts($scriptsAndStyles);
            $fields[] = "</script>";
            $fields[] = "@endpush";
        }

        return implode("\n", $fields);
    }

    private function getGlobalStyles(): string
    {
        return <<<'STYLE'
    /* Placeholder color for dark mode */
    .dark input::placeholder,
    .dark textarea::placeholder {
        color: #9ca3af !important;
    }

    /* Tagify Styles - Match other fields exactly */
    tagify {
        border: 2px solid rgb(229, 231, 235) !important;
        border-color: rgb(229, 231, 235) !important;
        border-radius: 0.5rem !important;
        padding: 0 !important;
        min-height: 56px !important;
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        --tw-shadow: 0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1)) !important;
        box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow) !important;
    }

    .dark tagify {
        border: 2px solid oklch(37.3% 0.034 259.733) !important;
        border-color: var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
        background-color: #111827 !important;
    }

    tagify:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        outline: none !important;
    }

    .dark tagify:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }

    /* Tagify Input Styling */
    .tagify__input {
        padding: 14px 16px !important;
        min-height: 56px !important;
        color: #111827 !important;
    }

    .dark .tagify__input {
        color: #fff !important;
    }

    /* Tagify Placeholder - Improve readability */
    tagify[data-placeholder]:empty::before {
        color: #6b7280 !important;
        opacity: 1 !important;
        font-size: 14px !important;
        padding: 14px 16px !important;
        line-height: 1.5 !important;
    }

    .dark tagify[data-placeholder]:empty::before {
        color: #9ca3af !important;
        opacity: 1 !important;
    }

    /* Tagify Empty Placeholder - Match text color */
    .tagify--empty .tagify__input::before {
        color: #111827 !important;
        opacity: 1 !important;
    }

    .dark .tagify--empty .tagify__input::before {
        color: #fff !important;
        opacity: 1 !important;
    }

    .tagify__input::placeholder {
        color: #6b7280 !important;
        opacity: 1 !important;
    }

    .dark .tagify__input::placeholder {
        color: #9ca3af !important;
        opacity: 1 !important;
    }

    /* Select2 Border and Styling - Match other fields */
    .select2-container--default .select2-selection--single {
        border: 2px solid rgb(229, 231, 235) !important;
        border-color: rgb(229, 231, 235) !important;
        border-radius: 0.5rem !important;
        height: auto !important;
        min-height: 56px !important;
        padding: 0 !important;
        --tw-shadow: 0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1)) !important;
        box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow) !important;
    }

    .dark .select2-container--default .select2-selection--single {
        background-color: #111827 !important;
        border: 2px solid var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
        border-color: var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
        color: #fff !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        outline: none !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding: 14px 16px !important;
        line-height: 1.5 !important;
        color: #111827 !important;
    }

    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff !important;
    }

    /* Select2 Clear Button (X) Alignment */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute !important;
        right: 45px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        cursor: pointer !important;
        font-size: 18px !important;
        line-height: 1 !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 20px !important;
        height: 20px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #6b7280 !important;
    }

    .dark .select2-container--default .select2-selection--single .select2-selection__clear {
        color: #9ca3af !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear:hover {
        color: #ef4444 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        right: 16px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        position: absolute !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #6b7280 transparent transparent transparent !important;
        border-width: 6px 5px 0 5px !important;
        margin-top: -3px !important;
    }

    .dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #fff transparent transparent transparent !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #6b7280 transparent !important;
        border-width: 0 5px 6px 5px !important;
    }

    .dark .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #fff transparent !important;
    }

    /* Select2 Dropdown */
    .select2-dropdown {
        border: 2px solid rgb(229, 231, 235) !important;
        border-color: rgb(229, 231, 235) !important;
        border-radius: 0.5rem !important;
        --tw-shadow: 0 10px 15px -3px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 4px 6px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.05)) !important;
        box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow) !important;
    }

    .dark .select2-dropdown {
        background-color: #1f2937 !important;
        border: 2px solid var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
        border-color: var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
    }

    .select2-container--default .select2-results__option {
        padding: 12px 16px !important;
        border-radius: 0.375rem !important;
    }

    .dark .select2-container--default .select2-results__option {
        background-color: #1f2937 !important;
        color: #fff !important;
    }

    .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #374151 !important;
        color: #fff !important;
    }

    .dark .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #2563eb !important;
        color: #fff !important;
    }

    .dark .select2-search--dropdown .select2-search__field {
        background-color: #1f2937 !important;
        border: 2px solid var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
        border-color: var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
        color: #fff !important;
        border-radius: 0.375rem !important;
    }

    /* TinyMCE Border and Placeholder Styles - Match Tagify exactly */
    .tox-tinymce {
        border: 2px solid rgb(229, 231, 235) !important;
        border-color: rgb(229, 231, 235) !important;
        border-radius: 0.5rem !important;
        --tw-shadow: 0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1)) !important;
        box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow) !important;
    }

    .dark .tox-tinymce {
        border: 2px solid var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
        border-color: var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
    }

    .tox-tinymce.tox-tinymce--focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }

    .tox .tox-edit-area__iframe {
        background-color: #fff !important;
    }

    .dark .tox .tox-edit-area__iframe {
        background-color: #1f2937 !important;
    }

    /* Placeholder color for TinyMCE */
    .tox .tox-edit-area p[data-mce-placeholder] {
        color: #9ca3af !important;
    }

    .dark .tox .tox-edit-area p[data-mce-placeholder] {
        color: #9ca3af !important;
    }

    /* Remove border for regular inputs in light mode (keep for TinyMCE, Tagify, Select2) */
    input[type="text"]:not([id*="keywords"]),
    input[type="email"],
    input[type="number"],
    input[type="date"],
    input[type="password"],
    textarea:not([id*="description"]):not([id*="meta_description"]) {
        border: none !important;
        border-width: 0 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
    }

    /* Force shadow for regular inputs in light mode - match Tagify */
    input[type="text"]:not([id*="keywords"]),
    input[type="email"],
    input[type="number"],
    input[type="date"],
    input[type="password"] {
        --tw-shadow: 0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1)) !important;
        box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow) !important;
    }

    /* Ensure shadow even when focused (but add focus ring) */
    input[type="text"]:not([id*="keywords"]):focus,
    input[type="email"]:focus,
    input[type="number"]:focus,
    input[type="date"]:focus,
    input[type="password"]:focus {
        --tw-shadow: 0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1)) !important;
        --tw-ring-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow) !important;
    }

    .dark input[type="text"]:not([id*="keywords"]),
    .dark input[type="email"],
    .dark input[type="number"],
    .dark input[type="date"],
    .dark input[type="password"],
    .dark textarea:not([id*="description"]):not([id*="meta_description"]) {
        border: 2px solid var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
        border-color: var(--color-gray-700, oklch(37.3% 0.034 259.733)) !important;
    }
STYLE;
    }

    private function getGlobalScripts($scriptsAndStyles): string
    {
        $fieldIds = [];
        $textareaFields = [];
        $selectFields = [];
        $tagifyFields = [];
        $currencyFields = [];

        foreach ($scriptsAndStyles as $item) {
            if ($item['type'] === 'textarea') {
                $textareaFields[] = $item['id'];
            } elseif ($item['type'] === 'select') {
                $selectFields[] = $item['id'];
            } elseif ($item['type'] === 'tags') {
                $tagifyFields[] = $item['id'];
            } elseif ($item['type'] === 'currency') {
                $currencyFields[] = $item['id'];
            }
        }

        // Check if title and slug fields exist for auto-generate slug feature
        $hasTitleField = false;
        $hasSlugField = false;
        foreach ($this->commandData->fields as $field) {
            if ($field->name === 'title') {
                $hasTitleField = true;
            }
            if ($field->name === 'slug') {
                $hasSlugField = true;
            }
        }

        $script = "    $(document).ready(function() {\n";

        // Auto-generate slug from title if both fields exist
        if ($hasTitleField && $hasSlugField) {
            $script .= "        // Auto-generate slug from title\n";
            $script .= "        var titleInput = document.getElementById('title');\n";
            $script .= "        var slugInput = document.getElementById('slug');\n";
            $script .= "        var slugManuallyEdited = false;\n\n";
            $script .= "        // Function to generate slug from text\n";
            $script .= "        function generateSlug(text) {\n";
            $script .= "            return text\n";
            $script .= "                .toLowerCase()\n";
            $script .= "                .trim()\n";
            $script .= "                .replace(/[^\\w\\s-]/g, '') // Remove special characters\n";
            $script .= "                .replace(/[\\s_-]+/g, '-') // Replace spaces and underscores with hyphens\n";
            $script .= "                .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens\n";
            $script .= "        }\n\n";
            $script .= "        // Auto-generate slug when title changes\n";
            $script .= "        if (titleInput && slugInput) {\n";
            $script .= "            // Track if user manually edits the slug field\n";
            $script .= "            slugInput.addEventListener('keydown', function(e) {\n";
            $script .= "                // Detect if user is typing (not just navigation keys)\n";
            $script .= "                if (!e.ctrlKey && !e.metaKey && !e.altKey) {\n";
            $script .= "                    // Allow: Backspace, Delete, Arrow keys, Tab, Enter\n";
            $script .= "                    if (!['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter'].includes(e.key)) {\n";
            $script .= "                        slugManuallyEdited = true;\n";
            $script .= "                    }\n";
            $script .= "                }\n";
            $script .= "            });\n\n";
            $script .= "            // Also track paste events\n";
            $script .= "            slugInput.addEventListener('paste', function() {\n";
            $script .= "                slugManuallyEdited = true;\n";
            $script .= "            });\n\n";
            $script .= "            // Auto-generate slug when title changes\n";
            $script .= "            titleInput.addEventListener('input', function() {\n";
            $script .= "                // Only auto-generate if slug hasn't been manually edited\n";
            $script .= "                if (!slugManuallyEdited) {\n";
            $script .= "                    var title = titleInput.value;\n";
            $script .= "                    slugInput.value = generateSlug(title);\n";
            $script .= "                }\n";
            $script .= "            });\n";
            $script .= "        }\n\n";
        }

        // Tagify initialization
        if (!empty($tagifyFields)) {
            foreach ($tagifyFields as $fieldId) {
                $script .= "        var {$fieldId}Input = document.querySelector('#{$fieldId}');\n";
                $script .= "        if ({$fieldId}Input) {\n";
                $script .= "            var {$fieldId}Tagify = new Tagify({$fieldId}Input, {\n";
                $script .= "                duplicates: false,\n";
                $script .= "                trim: true,\n";
                $script .= "                placeholder: 'Add tags...'\n";
                $script .= "            });\n";
                $script .= "            \n";
                $script .= "            // Set border-color inline after Tagify initialization\n";
                $script .= "            setTimeout(function() {\n";
                $script .= "                var {$fieldId}TagsElement = {$fieldId}Input.closest('tags') || document.querySelector('tags[id=\"{$fieldId}\"]') || document.querySelector('tags');\n";
                $script .= "                if ({$fieldId}TagsElement) {\n";
                $script .= "                    var isDark = document.documentElement.classList.contains('dark');\n";
                $script .= "                    if (isDark) {\n";
                $script .= "                        {$fieldId}TagsElement.style.borderColor = 'var(--color-gray-700, oklch(37.3% 0.034 259.733))';\n";
                $script .= "                    } else {\n";
                $script .= "                        {$fieldId}TagsElement.style.borderColor = 'rgb(229, 231, 235)';\n";
                $script .= "                    }\n";
                $script .= "                }\n";
                $script .= "            }, 100);\n";
                $script .= "            \n";
                $script .= "            // Update border-color on theme change\n";
                $script .= "            var {$fieldId}Observer = new MutationObserver(function() {\n";
                $script .= "                var {$fieldId}TagsElement = {$fieldId}Input.closest('tags') || document.querySelector('tags[id=\"{$fieldId}\"]') || document.querySelector('tags');\n";
                $script .= "                if ({$fieldId}TagsElement) {\n";
                $script .= "                    var isDark = document.documentElement.classList.contains('dark');\n";
                $script .= "                    if (isDark) {\n";
                $script .= "                        {$fieldId}TagsElement.style.borderColor = 'var(--color-gray-700, oklch(37.3% 0.034 259.733))';\n";
                $script .= "                    } else {\n";
                $script .= "                        {$fieldId}TagsElement.style.borderColor = 'rgb(229, 231, 235)';\n";
                $script .= "                    }\n";
                $script .= "                }\n";
                $script .= "            });\n";
                $script .= "            {$fieldId}Observer.observe(document.documentElement, {\n";
                $script .= "                attributes: true,\n";
                $script .= "                attributeFilter: ['class']\n";
                $script .= "            });\n";
                $script .= "        }\n";
            }
        }

        // TinyMCE initialization function
        if (!empty($textareaFields)) {
            $script .= "\n        // TinyMCE initialization function\n";
            $script .= "        function initTinyMCE(selector) {\n";
            $script .= "            var isDark = document.documentElement.classList.contains('dark');\n";
            $script .= "\n            // Remove existing TinyMCE instance if any\n";
            $script .= "            if (tinymce.get(selector.replace('#', ''))) {\n";
            $script .= "                tinymce.remove(selector.replace('#', ''));\n";
            $script .= "            }\n";
            $script .= "\n            tinymce.init({\n";
            $script .= "                selector: selector,\n";
            $script .= "                plugins: [\n";
            $script .= "                    // Core editing features\n";
            $script .= "                    'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists',\n";
            $script .= "                    'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',\n";
            $script .= "                    // Premium features\n";
            $script .= "                    'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed',\n";
            $script .= "                    'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable',\n";
            $script .= "                    'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions', 'tinycomments',\n";
            $script .= "                    'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography',\n";
            $script .= "                    'inlinecss', 'markdown', 'importword', 'exportword', 'exportpdf'\n";
            $script .= "                ],\n";
            $script .= "                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',\n";
            $script .= "                tinycomments_mode: 'embedded',\n";
            $script .= "                tinycomments_author: 'Author name',\n";
            $script .= "                mergetags_list: [{\n";
            $script .= "                        value: 'First.Name',\n";
            $script .= "                        title: 'First Name'\n";
            $script .= "                    },\n";
            $script .= "                    {\n";
            $script .= "                        value: 'Email',\n";
            $script .= "                        title: 'Email'\n";
            $script .= "                    },\n";
            $script .= "                ],\n";
            $script .= "                ai_request: (request, respondWith) => respondWith.string(() => Promise.reject(\n";
            $script .= "                    'See docs to implement AI Assistant')),\n";
            $script .= "                uploadcare_public_key: 'bf93e1e5c6e80ed486ed',\n";
            $script .= "                height: 400,\n";
            $script .= "                menubar: false,\n";
            $script .= "                branding: false,\n";
            $script .= "                promotion: false,\n";
            $script .= "                skin: isDark ? 'oxide-dark' : 'oxide',\n";
            $script .= "                content_css: isDark ? 'dark' : 'default',\n";
            $script .= "                content_style: 'body { font-family: \"Instrument Sans\", -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; font-size: 14px; ' +\n";
            $script .= "                    (isDark ? 'background-color: #1f2937; color: #fff;' : '') + ' }',\n";
            $script .= "                placeholder: 'Start typing...',\n";
            $script .= "                setup: function(editor) {\n";
            $script .= "                    editor.on('init', function() {\n";
            $script .= "                        // Content is already set from textarea value\n";
            $script .= "                        // Force set border after TinyMCE is fully initialized\n";
            $script .= "                        function setTinyMCEBorder() {\n";
            $script .= "                            var tinymceElement = editor.getContainer();\n";
            $script .= "                            if (tinymceElement) {\n";
            $script .= "                                var isDark = document.documentElement.classList.contains('dark');\n";
            $script .= "                                var borderColor = isDark ? 'var(--color-gray-700, oklch(37.3% 0.034 259.733))' : 'rgb(229, 231, 235)';\n";
            $script .= "                                var borderValue = isDark ? 'oklch(37.3% 0.034 259.733)' : 'rgb(229, 231, 235)';\n";
            $script .= "                                tinymceElement.style.border = '2px solid ' + borderValue;\n";
            $script .= "                                tinymceElement.style.borderColor = borderColor;\n";
            $script .= "                                tinymceElement.style.borderRadius = '0.5rem';\n";
            $script .= "                                tinymceElement.style.setProperty('border', '2px solid ' + borderValue, 'important');\n";
            $script .= "                                tinymceElement.style.setProperty('border-color', borderColor, 'important');\n";
            $script .= "                                tinymceElement.style.setProperty('border-radius', '0.5rem', 'important');\n";
            $script .= "                            }\n";
            $script .= "                        }\n";
            $script .= "                        \n";
            $script .= "                        // Try multiple times to ensure border is set\n";
            $script .= "                        setTinyMCEBorder();\n";
            $script .= "                        setTimeout(setTinyMCEBorder, 100);\n";
            $script .= "                        setTimeout(setTinyMCEBorder, 300);\n";
            $script .= "                        setTimeout(setTinyMCEBorder, 500);\n";
            $script .= "                    });\n";
            $script .= "                    \n";
            $script .= "                    // Also set border when editor is fully loaded\n";
            $script .= "                    editor.on('loadedmetadata', function() {\n";
            $script .= "                        setTimeout(function() {\n";
            $script .= "                            var tinymceElement = editor.getContainer();\n";
            $script .= "                            if (tinymceElement) {\n";
            $script .= "                                var isDark = document.documentElement.classList.contains('dark');\n";
            $script .= "                                var borderColor = isDark ? 'var(--color-gray-700, oklch(37.3% 0.034 259.733))' : 'rgb(229, 231, 235)';\n";
            $script .= "                                var borderValue = isDark ? 'oklch(37.3% 0.034 259.733)' : 'rgb(229, 231, 235)';\n";
            $script .= "                                tinymceElement.style.setProperty('border', '2px solid ' + borderValue, 'important');\n";
            $script .= "                                tinymceElement.style.setProperty('border-color', borderColor, 'important');\n";
            $script .= "                                tinymceElement.style.setProperty('border-radius', '0.5rem', 'important');\n";
            $script .= "                            }\n";
            $script .= "                        }, 100);\n";
            $script .= "                    });\n";
            $script .= "                }\n";
            $script .= "            });\n";
            $script .= "        }\n\n";

            foreach ($textareaFields as $fieldId) {
                $script .= "        // Initialize TinyMCE for {$fieldId}\n";
                $script .= "        if ($('#{$fieldId}').length) {\n";
                $script .= "            initTinyMCE('#{$fieldId}');\n";
                $script .= "        }\n";
            }
        }

        // Select2 initialization
        if (!empty($selectFields)) {
            $script .= "\n        // Select2 initialization\n";
            $script .= "        function initSelect2() {\n";
            foreach ($selectFields as $fieldId) {
                $fieldLabel = ucfirst(str_replace('_', ' ', $fieldId));
                $script .= "            if ($('#{$fieldId}').length && !$('#{$fieldId}').hasClass('select2-hidden-accessible')) {\n";
                $script .= "                $('#{$fieldId}').select2({\n";
                $script .= "                    placeholder: 'Select {$fieldLabel}',\n";
                $script .= "                    allowClear: true,\n";
                $script .= "                    width: '100%'\n";
                $script .= "                });\n";
                $script .= "            }\n";
            }
            $script .= "        }\n\n";
            $script .= "        initSelect2();\n";
        }

        // Currency formatting initialization
        if (!empty($currencyFields)) {
            $script .= "\n        // Initialize currency formatting for inputs with data-currency attribute\n";
            $script .= "        function initCurrencyFormatting() {\n";
            $script .= "            document.querySelectorAll('input[data-currency]').forEach(function(input) {\n";
            $script .= "                // Check if AutoNumeric is already initialized on this element\n";
            $script .= "                var existingInstance = AutoNumeric.getAutoNumericElement(input);\n";
            $script .= "                if (!existingInstance) {\n";
            $script .= "                    // Get current value and clean it\n";
            $script .= "                    var currentValue = input.value || '';\n";
            $script .= "                    // Remove currency formatting characters (commas, dots, etc.) to get raw number\n";
            $script .= "                    var rawValue = currentValue.toString().replace(/[^\\d.-]/g, '');\n";
            $script .= "                    \n";
            $script .= "                    // Initialize AutoNumeric with Indonesian Rupiah format\n";
            $script .= "                    var autoNumericInstance = new AutoNumeric(input, {\n";
            $script .= "                        digitGroupSeparator: '.',\n";
            $script .= "                        decimalCharacter: ',',\n";
            $script .= "                        decimalPlaces: 0,\n";
            $script .= "                        currencySymbol: 'Rp ',\n";
            $script .= "                        currencySymbolPlacement: 'p',\n";
            $script .= "                        allowDecimalPadding: false,\n";
            $script .= "                        minimumValue: '0',\n";
            $script .= "                        maximumValue: '999999999999',\n";
            $script .= "                        formatOnPageLoad: true,\n";
            $script .= "                        unformatOnSubmit: true,\n";
            $script .= "                        modifyValueOnWheel: false\n";
            $script .= "                    });\n";
            $script .= "\n";
            $script .= "                    // Set the value if it exists (this will format it automatically)\n";
            $script .= "                    if (rawValue && rawValue !== '' && rawValue !== '0') {\n";
            $script .= "                        autoNumericInstance.set(parseFloat(rawValue) || 0);\n";
            $script .= "                    } else if (currentValue === '' || currentValue === '0') {\n";
            $script .= "                        // Clear the field if empty or zero\n";
            $script .= "                        autoNumericInstance.clear();\n";
            $script .= "                    }\n";
            $script .= "                }\n";
            $script .= "            });\n";
            $script .= "        }\n\n";
            $script .= "        // Initialize currency formatting on page load\n";
            $script .= "        // Wait a bit to ensure AutoNumeric library is loaded\n";
            $script .= "        if (typeof AutoNumeric !== 'undefined') {\n";
            $script .= "            initCurrencyFormatting();\n";
            $script .= "        } else {\n";
            $script .= "            // Wait for AutoNumeric to load\n";
            $script .= "            var checkAutoNumeric = setInterval(function() {\n";
            $script .= "                if (typeof AutoNumeric !== 'undefined') {\n";
            $script .= "                    clearInterval(checkAutoNumeric);\n";
            $script .= "                    initCurrencyFormatting();\n";
            $script .= "                }\n";
            $script .= "            }, 100);\n";
            $script .= "            \n";
            $script .= "            // Timeout after 5 seconds\n";
            $script .= "            setTimeout(function() {\n";
            $script .= "                clearInterval(checkAutoNumeric);\n";
            $script .= "            }, 5000);\n";
            $script .= "        }\n\n";
            $script .= "        // Ensure currency values are unformatted before form submission\n";
            $script .= "        $('form').on('submit', function(e) {\n";
            $script .= "            document.querySelectorAll('input[data-currency]').forEach(function(input) {\n";
            $script .= "                var autoNumericInstance = AutoNumeric.getAutoNumericElement(input);\n";
            $script .= "                if (autoNumericInstance) {\n";
            $script .= "                    // Get unformatted value and set it back to the input\n";
            $script .= "                    var unformattedValue = autoNumericInstance.getNumber();\n";
            $script .= "                    input.value = unformattedValue || '';\n";
            $script .= "                }\n";
            $script .= "            });\n";
            $script .= "        });\n";
        }

        // Watch for theme changes
        if (!empty($textareaFields) || !empty($selectFields)) {
            $script .= "\n        // Watch for theme changes and reinitialize components\n";
            $script .= "        var observer = new MutationObserver(function() {\n";
            $script .= "            var isDark = document.documentElement.classList.contains('dark');\n";
            $script .= "\n";

            if (!empty($textareaFields)) {
                $script .= "            // Force update TinyMCE border\n";
                $script .= "            function updateTinyMCEBorder(editorId) {\n";
                $script .= "                var editor = tinymce.get(editorId);\n";
                $script .= "                if (editor) {\n";
                $script .= "                    var container = editor.getContainer();\n";
                $script .= "                    if (container) {\n";
                $script .= "                        var borderColor = isDark ? 'oklch(37.3% 0.034 259.733)' : 'rgb(229, 231, 235)';\n";
                $script .= "                        container.style.setProperty('border', '2px solid ' + borderColor, 'important');\n";
                $script .= "                        container.style.setProperty('border-color', isDark ? 'var(--color-gray-700, oklch(37.3% 0.034 259.733))' : 'rgb(229, 231, 235)', 'important');\n";
                $script .= "                        container.style.setProperty('border-radius', '0.5rem', 'important');\n";
                $script .= "                    }\n";
                $script .= "                }\n";
                $script .= "            }\n";
                $script .= "            \n";
                foreach ($textareaFields as $fieldId) {
                    $script .= "            updateTinyMCEBorder('{$fieldId}');\n";
                }
                $script .= "\n            // Reinitialize TinyMCE\n";
                foreach ($textareaFields as $fieldId) {
                    $script .= "            if ($('#{$fieldId}').length) {\n";
                    $script .= "                initTinyMCE('#{$fieldId}');\n";
                    $script .= "            }\n";
                }
            }

            if (!empty($selectFields)) {
                $script .= "\n            // Reinitialize Select2\n";
                foreach ($selectFields as $fieldId) {
                    $script .= "            if ($('#{$fieldId}').length && $('#{$fieldId}').hasClass('select2-hidden-accessible')) {\n";
                    $script .= "                $('#{$fieldId}').select2('destroy');\n";
                    $script .= "            }\n";
                }
                $script .= "            initSelect2();\n";
            }

            $script .= "        });\n\n";
            $script .= "        observer.observe(document.documentElement, {\n";
            $script .= "            attributes: true,\n";
            $script .= "            attributeFilter: ['class']\n";
            $script .= "        });\n";
        }

        $script .= "    });";

        return $script;
    }

    private function getTableHeaders(): string
    {
        $headers = [];
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Skip timestamp fields from table headers
            if (in_array($field->name, $timestampFields)) {
                continue;
            }

            if ($field->sortable) {
                $headers[] = "                            <th class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600\">" .
                    ucfirst(str_replace('_', ' ', $field->name)) . "</th>";
            } else {
                $tableHeader = $field->getTableHeader();
                // Update to support dark mode
                $tableHeader = str_replace(
                    'class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"',
                    'class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"',
                    $tableHeader
                );
                $headers[] = "                            " . $tableHeader;
            }
        }

        return implode("\n", $headers);
    }

    private function getTableCells(): string
    {
        $cells = [];
        $modelVar = $this->commandData->modelNameCamel;
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Skip timestamp fields from table cells
            if (in_array($field->name, $timestampFields)) {
                continue;
            }

            // Handle Tagify fields (tags) - parse JSON and display comma-separated values
            if ($field->htmlType === 'tags') {
                $tableCell = "<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white\">";
                $tableCell .= "@if(\$" . $modelVar . "->{$field->name})\n";
                $tableCell .= "                                        @php\n";
                $tableCell .= "                                            \$keywords = is_array(\$" . $modelVar . "->{$field->name}) ? \$" . $modelVar . "->{$field->name} : json_decode(\$" . $modelVar . "->{$field->name}, true);\n";
                $tableCell .= "                                            \$keywordValues = [];\n";
                $tableCell .= "                                            if (is_array(\$keywords)) {\n";
                $tableCell .= "                                                foreach (\$keywords as \$keyword) {\n";
                $tableCell .= "                                                    if (is_array(\$keyword) && isset(\$keyword['value'])) {\n";
                $tableCell .= "                                                        \$keywordValues[] = \$keyword['value'];\n";
                $tableCell .= "                                                    } elseif (is_string(\$keyword)) {\n";
                $tableCell .= "                                                        \$keywordValues[] = \$keyword;\n";
                $tableCell .= "                                                    }\n";
                $tableCell .= "                                                }\n";
                $tableCell .= "                                            }\n";
                $tableCell .= "                                            echo implode(', ', \$keywordValues);\n";
                $tableCell .= "                                        @endphp\n";
                $tableCell .= "                                    @endif";
                $tableCell .= "</td>";
            }
            // Handle TinyMCE fields (textarea) - strip HTML and limit to 100 chars
            elseif ($field->htmlType === 'textarea') {
                $tableCell = "<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white\">";
                $tableCell .= "@if(\$" . $modelVar . "->{$field->name})\n";
                $tableCell .= "                                        @php\n";
                $tableCell .= "                                            \$text = strip_tags(\$" . $modelVar . "->{$field->name});\n";
                $tableCell .= "                                            \$text = mb_strlen(\$text) > 100 ? mb_substr(\$text, 0, 100) . '...' : \$text;\n";
                $tableCell .= "                                            echo \$text;\n";
                $tableCell .= "                                        @endphp\n";
                $tableCell .= "                                    @endif";
                $tableCell .= "</td>";
            }
            // Default behavior for other fields
            else {
                $tableCell = $field->getTableCell();
                // Replace $item with the correct model variable name
                $tableCell = str_replace('$item', '$' . $modelVar, $tableCell);
                // Update to support dark mode
                $tableCell = str_replace(
                    'class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"',
                    'class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"',
                    $tableCell
                );
            }

            $cells[] = "                                " . $tableCell;
        }

        return implode("\n", $cells);
    }

    private function getFormInputs(): string
    {
        // This is just an alias for getFormFields, which already handles scripts/styles
        return $this->getFormFields();
    }

    private function getFormInputForField($field, &$scriptsAndStyles = []): string
    {
        switch ($field->htmlType) {
            case 'textarea':
                $scriptsAndStyles[] = ['type' => 'textarea', 'id' => $field->name];
                return $this->getRichTextEditor($field);

            case 'select':
                $scriptsAndStyles[] = ['type' => 'select', 'id' => $field->name];
                return $this->getSelect2Component($field);

            case 'checkbox':
                return $this->getSwitchComponent($field);

            case 'date':
                return "<input type=\"date\" name=\"{$field->name}\" id=\"{$field->name}\" class=\"block w-full rounded-lg border-0 dark:border-2 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-md dark:shadow-md focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . $this->commandData->modelNameCamel . "->{$field->name} ?? ''}}\">";

            case 'email':
                return "<input type=\"email\" name=\"{$field->name}\" id=\"{$field->name}\" class=\"block w-full rounded-lg border-0 dark:border-2 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-md dark:shadow-md focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . $this->commandData->modelNameCamel . "->{$field->name} ?? ''}}\">";

            case 'password':
                return "<input type=\"password\" name=\"{$field->name}\" id=\"{$field->name}\" class=\"block w-full rounded-lg border-0 dark:border-2 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-md dark:shadow-md focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\">";

            case 'number':
                // Check if this is a currency field
                if ($this->isCurrencyField($field)) {
                    $scriptsAndStyles[] = ['type' => 'currency', 'id' => $field->name];
                    return "<input type=\"text\" name=\"{$field->name}\" id=\"{$field->name}\" class=\"block w-full rounded-lg border-0 dark:border-2 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-md dark:shadow-md focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . $this->commandData->modelNameCamel . "->{$field->name} ?? ''}}\" data-currency>";
                }
                return "<input type=\"number\" name=\"{$field->name}\" id=\"{$field->name}\" class=\"block w-full rounded-lg border-0 dark:border-2 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-md dark:shadow-md focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . $this->commandData->modelNameCamel . "->{$field->name} ?? ''}}\">";

            case 'currency':
                $scriptsAndStyles[] = ['type' => 'currency', 'id' => $field->name];
                return "<input type=\"text\" name=\"{$field->name}\" id=\"{$field->name}\" class=\"block w-full rounded-lg border-0 dark:border-2 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-md dark:shadow-md focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . $this->commandData->modelNameCamel . "->{$field->name} ?? ''}}\" data-currency>";

            case 'file':
                return $this->getDropifyComponent($field);

            case 'tags':
                $scriptsAndStyles[] = ['type' => 'tags', 'id' => $field->name];
                return $this->getTagifyComponent($field);

            default: // text
                // Check if this is a currency field (for decimal/float/double types)
                if ($this->isCurrencyField($field)) {
                    $scriptsAndStyles[] = ['type' => 'currency', 'id' => $field->name];
                    return "<input type=\"text\" name=\"{$field->name}\" id=\"{$field->name}\" class=\"block w-full rounded-lg border-0 dark:border-2 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-md dark:shadow-md focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . $this->commandData->modelNameCamel . "->{$field->name} ?? ''}}\" data-currency>";
                }
                return "<input type=\"text\" name=\"{$field->name}\" id=\"{$field->name}\" class=\"block w-full rounded-lg border-0 dark:border-2 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-md dark:shadow-md focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors\" value=\"{{\$" . $this->commandData->modelNameCamel . "->{$field->name} ?? ''}}\">";
        }
    }

    /**
     * Check if a field is a currency field based on name or database type
     */
    private function isCurrencyField($field): bool
    {
        // Check if field name contains currency-related keywords
        $currencyKeywords = ['price', 'cost', 'amount', 'currency', 'fee', 'charge', 'total', 'sum', 'balance', 'payment', 'sale_price', 'discount', 'tax'];
        $fieldNameLower = strtolower($field->name);

        foreach ($currencyKeywords as $keyword) {
            if (str_contains($fieldNameLower, $keyword)) {
                return true;
            }
        }

        // Check if database type is decimal, float, or double (common for currency)
        $currencyDbTypes = ['decimal', 'float', 'double'];
        if (in_array($field->dbType, $currencyDbTypes)) {
            return true;
        }

        // Check if htmlType is explicitly currency
        if ($field->htmlType === 'currency') {
            return true;
        }

        return false;
    }

    private function getRichTextEditor($field): string
    {
        $fieldName = $field->name;
        $modelVar = $this->commandData->modelNameCamel;

        return <<<HTML
                    <textarea name="{$fieldName}" id="{$fieldName}" class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">{{\$$modelVar->{$fieldName} ?? ''}}</textarea>
HTML;
    }

    private function getSelect2Component($field): string
    {
        $fieldName = $field->name;
        $modelVar = $this->commandData->modelNameCamel;
        $options = '';

        // Check if field has options from ENUM or manual definition
        if (!empty($field->htmlInputs)) {
            // Use predefined options
            foreach ($field->htmlInputs as $option) {
                $selected = "{{(\$" . $modelVar . "->{$fieldName} ?? '') == '{$option}' ? 'selected' : ''}}";
                $options .= "                        <option value=\"{$option}\" {$selected}>" . ucfirst($option) . "</option>\n";
            }
        } else {
            // Fallback for select without options
            $options = "                        <option value=\"\">Select " . ucfirst(str_replace('_', ' ', $fieldName)) . "</option>\n";
        }

        return <<<HTML
                    <select name="{$fieldName}" id="{$fieldName}" class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
{$options}                    </select>
HTML;
    }

    private function getDropifyComponent($field): string
    {
        $fieldName = $field->name;
        $modelVar = $this->commandData->modelNameCamel;

        return <<<HTML
                    @php
                        use App\Services\FileUploadService;
                        \$filePath = isset(\$$modelVar) && \$$modelVar ? (\$$modelVar->{$fieldName} ?? '') : '';
                        \$fileUrl = \$filePath ? FileUploadService::getFileUrl(\$filePath) : '';
                    @endphp
                    <input type="file" name="{$fieldName}" id="{$fieldName}" class="dropify"@if(\$fileUrl) data-default-file="{\$fileUrl}"@endif data-height="200" accept="image/*,.webp,.gif,.svg,image/svg+xml">
                    <!-- Progress Bar Container -->
                    <div id="{$fieldName}-progress-container" class="mt-2 hidden">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                            <div id="{$fieldName}-progress-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                        </div>
                        <p id="{$fieldName}-progress-text" class="text-xs text-gray-600 dark:text-gray-400 mt-1 text-center">0%</p>
                    </div>
                    <script>
                        \$(document).ready(function() {
                            var \$dropifyInput = $('#{$fieldName}');
                            var \$progressContainer = $('#{$fieldName}-progress-container');
                            var \$progressBar = $('#{$fieldName}-progress-bar');
                            var \$progressText = $('#{$fieldName}-progress-text');

                            // Initialize dropify
                            \$dropifyInput.dropify({
                                messages: {
                                    'default': 'Drag and drop a file here or click',
                                    'replace': 'Drag and drop or click to replace',
                                    'remove': 'Remove',
                                    'error': 'Ooops, something wrong happened.'
                                },
                                acceptedFiles: 'image/*,.webp,.gif,.svg,image/svg+xml',
                                showLoader: true
                            });

                            // Handle form submission with file upload progress
                            var form = \$dropifyInput.closest('form');
                            if (form.length) {
                                form.on('submit', function(e) {
                                    var fileInput = \$dropifyInput[0];
                                    if (fileInput && fileInput.files && fileInput.files.length > 0) {
                                        // Show progress bar
                                        \$progressContainer.removeClass('hidden');
                                        \$progressBar.css('width', '0%');
                                        \$progressText.text('0%');

                                        // Simulate progress for file upload
                                        var progress = 0;
                                        var progressInterval = setInterval(function() {
                                            progress += Math.random() * 15;
                                            if (progress > 90) {
                                                progress = 90; // Don't go to 100% until actual upload completes
                                            }
                                            \$progressBar.css('width', progress + '%');
                                            \$progressText.text(Math.round(progress) + '%');
                                        }, 200);

                                        // Store interval ID to clear it later
                                        form.data('progressInterval', progressInterval);
                                    }
                                });
                            }

                            // Function to force image render
                            function forceImageRender() {
                                var wrapper = \$dropifyInput.closest('.dropify-wrapper');
                                if (!wrapper.length) return;

                                var render = wrapper.find('.dropify-render');
                                if (!render.length) return;

                                var fileInput = \$dropifyInput[0];
                                var hasImage = render.find('img').length > 0;
                                var hasFileIcon = render.find('.file-icon').length > 0;
                                var hasFileName = render.find('.dropify-filename-inner').length > 0;

                                // Handle uploaded file
                                if (fileInput && fileInput.files && fileInput.files.length > 0) {
                                    var file = fileInput.files[0];
                                    // Check if it's an image file (including GIF, SVG, WebP)
                                    if (file && (
                                        file.type.startsWith('image/') ||
                                        file.type === 'image/svg+xml' ||
                                        file.name.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)
                                    )) {
                                        // Force replace if has file icon or filename but no image
                                        if (!hasImage || hasFileIcon || (hasFileName && !hasImage)) {
                                            var reader = new FileReader();
                                            reader.onload = function(e) {
                                                // For SVG, ensure proper rendering
                                                if (file.type === 'image/svg+xml' || file.name.match(/\.svg$/i)) {
                                                    render.html('<img src="' + e.target.result + '" class="dropify-preview-image" style="max-width: 100%; max-height: 100%; object-fit: contain;">');
                                                } else {
                                                    render.html('<img src="' + e.target.result + '" class="dropify-preview-image">');
                                                }
                                            };
                                            reader.readAsDataURL(file);
                                            return;
                                        }
                                    }
                                }

                                // Handle default file (existing file)
                                var defaultFile = \$dropifyInput.attr('data-default-file');
                                if (defaultFile && defaultFile.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)) {
                                    // Force replace if has file icon or filename but no image
                                    if (!hasImage || hasFileIcon || (hasFileName && !hasImage)) {
                                        // For SVG, ensure proper rendering
                                        if (defaultFile.match(/\.svg$/i)) {
                                            render.html('<img src="' + defaultFile + '" class="dropify-preview-image" style="max-width: 100%; max-height: 100%; object-fit: contain;">');
                                        } else {
                                            render.html('<img src="' + defaultFile + '" class="dropify-preview-image">');
                                        }
                                    }
                                }
                            }

                            // Watch for file changes
                            \$dropifyInput.on('change', function() {
                                setTimeout(function() {
                                    forceImageRender();
                                }, 200);
                            });

                            // Use MutationObserver to watch for DOM changes
                            var observer = new MutationObserver(function(mutations) {
                                mutations.forEach(function(mutation) {
                                    if (mutation.addedNodes.length > 0) {
                                        setTimeout(function() {
                                            forceImageRender();
                                        }, 100);
                                    }
                                });
                            });

                            // Observe the dropify wrapper
                            setTimeout(function() {
                                var wrapper = \$dropifyInput.closest('.dropify-wrapper');
                                if (wrapper.length) {
                                    observer.observe(wrapper[0], {
                                        childList: true,
                                        subtree: true
                                    });
                                    forceImageRender(); // Initial render
                                }
                            }, 300);

                            // Fallback: periodic check
                            var checkInterval = setInterval(function() {
                                forceImageRender();
                            }, 500);

                            setTimeout(function() {
                                clearInterval(checkInterval);
                                observer.disconnect();
                            }, 5000);

                            // Reset progress bar when file is removed
                            \$dropifyInput.on('dropify.afterClear', function() {
                                \$progressContainer.addClass('hidden');
                                \$progressBar.css('width', '0%');
                                \$progressText.text('0%');
                            });
                        });
                    </script>
                    <style>
                        .dropify-wrapper .dropify-preview .dropify-render {
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            width: 100% !important;
                            height: 100% !important;
                            text-align: center !important;
                        }
                        .dropify-wrapper .dropify-preview .dropify-render img {
                            max-width: 100% !important;
                            max-height: 100% !important;
                            object-fit: contain !important;
                            margin: 0 auto !important;
                            display: block !important;
                            width: auto !important;
                            height: auto !important;
                        }
                        .dropify-preview-image {
                            max-width: 100% !important;
                            max-height: 100% !important;
                            object-fit: contain !important;
                            margin: 0 auto !important;
                            display: block !important;
                        }
                        /* SVG and GIF specific styling for better preview */
                        .dropify-wrapper .dropify-preview .dropify-render img[src*=".svg"],
                        .dropify-wrapper .dropify-preview .dropify-render img[src*=".gif"] {
                            max-width: 100% !important;
                            max-height: 100% !important;
                            object-fit: contain !important;
                            background: transparent !important;
                        }
                        /* Hide file icon when image is present */
                        .dropify-wrapper .dropify-preview .dropify-render:has(img) .file-icon {
                            display: none !important;
                        }
                        /* Progress bar styles */
                        #{$fieldName}-progress-container {
                            margin-top: 0.5rem;
                        }
                        #{$fieldName}-progress-bar {
                            transition: width 0.3s ease-out;
                        }
                    </style>
HTML;
    }

    private function getTagifyComponent($field): string
    {
        $fieldName = $field->name;
        $modelVar = $this->commandData->modelNameCamel;

        return <<<HTML
                    <input name="{$fieldName}" id="{$fieldName}" value="{{\$$modelVar->{$fieldName} ?? ''}}" placeholder="Add tags..." class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
HTML;
    }

    private function getSwitchComponent($field): string
    {
        $fieldName = $field->name;
        $modelVar = $this->commandData->modelNameCamel;
        $label = ucfirst(str_replace('_', ' ', $fieldName));

        return <<<HTML
                    <div class="flex items-center">
                        <input type="hidden" name="{$fieldName}" value="0">
                        <label for="{$fieldName}" class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="{$fieldName}" value="1" id="{$fieldName}" class="sr-only peer" {{(\$$modelVar->{$fieldName} ?? false) ? 'checked' : ''}}>
                            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
HTML;
    }

    private function getCsvSample(): string
    {
        $headers = [];
        $sampleRow = [];
        $timestampFields = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->commandData->fields as $field) {
            // Skip timestamp fields from CSV sample
            if (in_array($field->name, $timestampFields)) {
                continue;
            }

            if ($field->fillable) {
                $headers[] = $field->name;
                // Generate sample value based on field type
                switch ($field->dbType) {
                    case 'integer':
                    case 'bigint':
                        $sampleRow[] = '1';
                        break;
                    case 'boolean':
                        $sampleRow[] = '1';
                        break;
                    case 'date':
                        $sampleRow[] = '2024-01-01';
                        break;
                    case 'datetime':
                    case 'timestamp':
                        $sampleRow[] = '2024-01-01 12:00:00';
                        break;
                    case 'decimal':
                    case 'float':
                    case 'double':
                        $sampleRow[] = '100.00';
                        break;
                    default:
                        $sampleRow[] = 'Sample Value';
                        break;
                }
            }
        }

        $csv = implode(',', $headers) . "\n";
        $csv .= implode(',', $sampleRow);

        return $csv;
    }
}
