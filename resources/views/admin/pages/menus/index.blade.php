@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Menu Management</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Drag and drop to reorder, click to edit menu items</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                <p class="text-sm text-red-600 dark:text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Menu Editor -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
            <div class="p-6 space-y-6" id="menuEditorContainer">
                @forelse($groupedMenus as $sectionTitle => $sectionMenus)
                    <div class="menu-section bg-gray-100 dark:bg-gray-700/30 rounded-lg p-4 border-2 border-transparent hover:border-blue-300 dark:hover:border-blue-600 transition-colors"
                        data-section="{{ $sectionTitle }}">
                        <!-- Section Title -->
                        <div
                            class="flex items-center justify-between mb-4 pb-3 border-b-2 border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-3">
                                <!-- Section Drag Handle -->
                                <div class="cursor-move section-drag-handle">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 8h16M4 16h16"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                                    {{ $sectionTitle }}
                                </h3>
                            </div>
                            <button type="button" onclick="editSectionTitle('{{ $sectionTitle }}')"
                                class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                Edit Title
                            </button>
                        </div>

                        <!-- Menu Items -->
                        <div class="menu-items-list space-y-2" data-section="{{ $sectionTitle }}">
                            @foreach ($sectionMenus as $menu)
                                <div class="menu-item bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border-2 border-transparent hover:border-blue-300 dark:hover:border-blue-600 transition-colors"
                                    data-menu-id="{{ $menu->id }}" data-section="{{ $sectionTitle }}">
                                    <div class="flex items-center gap-4">
                                        <!-- Drag Handle -->
                                        <div class="cursor-move">
                                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 8h16M4 16h16"></path>
                                            </svg>
                                        </div>

                                        <!-- Icon -->
                                        <div class="shrink-0">
                                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center icon-preview"
                                                data-menu-id="{{ $menu->id }}">
                                                {!! App\Helpers\MenuHelper::renderIcon($menu->icon ?? '') !!}
                                            </div>
                                        </div>

                                        <!-- Menu Info -->
                                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <!-- Name -->
                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                                    Name
                                                </label>
                                                <input type="text" name="name" value="{{ $menu->name }}"
                                                    data-menu-id="{{ $menu->id }}"
                                                    class="menu-edit-input w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <!-- Icon -->
                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                                    Icon
                                                </label>
                                                <div class="flex gap-2">
                                                    <input type="text" name="icon" value="{{ $menu->icon ?? '' }}"
                                                        placeholder="e.g., home, fa fa-home, fas fa-users"
                                                        data-menu-id="{{ $menu->id }}"
                                                        class="menu-edit-input flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                                    <button type="button"
                                                        onclick="openIconPicker({{ $menu->id }}, '{{ $menu->icon ?? '' }}')"
                                                        class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        Pick
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Route -->
                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                                    Route
                                                </label>
                                                <input type="text" name="route" value="{{ $menu->route ?? '' }}"
                                                    placeholder="admin.products.index" data-menu-id="{{ $menu->id }}"
                                                    readonly
                                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                                            </div>
                                        </div>

                                        <!-- Section Title Dropdown -->
                                        <div class="shrink-0 w-48">
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                                Section
                                            </label>
                                            <select name="section_title" data-menu-id="{{ $menu->id }}"
                                                class="menu-section-select w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                                <option value="">-- No Section --</option>
                                                @foreach ($existingSections as $section)
                                                    <option value="{{ $section }}"
                                                        {{ $menu->section_title == $section ? 'selected' : '' }}>
                                                        {{ $section }}
                                                    </option>
                                                @endforeach
                                                <option value="__new__">-- Create New --</option>
                                            </select>
                                            <input type="text" name="section_title_new" placeholder="New section title"
                                                data-menu-id="{{ $menu->id }}"
                                                class="menu-section-new hidden w-full px-3 py-2 mt-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400">No menus found. Menus will appear here after
                            scaffolding.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Edit Section Title Modal -->
    <div id="editSectionTitleModal" class="hidden fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="editSectionTitleModal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 backdrop-blur-sm transition-opacity" style="background-color: #0000003d;"
            onclick="closeEditSectionTitleModal()">
        </div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white"
                                id="editSectionTitleModal-title">
                                Edit Section Title
                            </h3>
                            <div class="mt-4">
                                <label for="sectionTitleInput"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Section Title
                                </label>
                                <input type="text" id="sectionTitleInput" name="section_title"
                                    class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-3 transition-colors"
                                    placeholder="Enter section title (e.g., CONTENT MANAGEMENT)"
                                    onkeydown="if(event.key === 'Enter') { saveSectionTitle(); }">
                                <input type="hidden" id="currentSectionTitle" value="">
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    This will update the section title for all menus in this section.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" onclick="saveSectionTitle()"
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">
                        Save
                    </button>
                    <button type="button" onclick="closeEditSectionTitleModal()"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <div id="iconPickerModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="iconPickerModal-title"
        role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 backdrop-blur-sm transition-opacity" style="background-color: #0000003d;"
            onclick="closeIconPickerModal()">
        </div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white"
                                id="iconPickerModal-title">
                                Pick Icon
                            </h3>
                            <div class="mt-4">
                                <div class="mb-4">
                                    <label for="iconSearchInput"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Search or Enter Icon Code
                                    </label>
                                    <input type="text" id="iconSearchInput"
                                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-3 transition-colors"
                                        placeholder="e.g., home, fa fa-home, fas fa-users, bi bi-house"
                                        onkeydown="if(event.key === 'Enter') { selectCustomIcon(); }">
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Supports: Heroicons (e.g., home), FontAwesome (e.g., fa fa-home, fas fa-users),
                                        Bootstrap Icons (e.g., bi bi-house), or custom class
                                    </p>
                                </div>

                                <!-- Heroicons Section -->
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Heroicons
                                        (Default)</h4>
                                    <div
                                        class="grid grid-cols-8 gap-2 max-h-48 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        @php
                                            $heroIcons = [
                                                'home',
                                                'users',
                                                'user',
                                                'shopping-cart',
                                                'folder',
                                                'file',
                                                'file-text',
                                                'settings',
                                                'shield',
                                                'key',
                                                'chart-bar',
                                                'calendar',
                                                'mail',
                                                'bell',
                                                'search',
                                                'heart',
                                                'star',
                                                'trash',
                                                'edit',
                                                'plus',
                                                'minus',
                                                'x',
                                                'check',
                                                'arrow-right',
                                                'arrow-left',
                                                'arrow-up',
                                                'arrow-down',
                                                'menu',
                                                'dots-vertical',
                                                'cog',
                                                'photograph',
                                                'collection',
                                                'tag',
                                                'archive',
                                                'database',
                                                'server',
                                                'document',
                                                'folder-open',
                                                'globe',
                                                'link',
                                                'external-link',
                                                'download',
                                                'upload',
                                                'save',
                                                'refresh',
                                                'lock',
                                                'unlock',
                                                'eye',
                                                'eye-off',
                                                'information-circle',
                                                'exclamation-circle',
                                                'question-mark-circle',
                                            ];
                                        @endphp
                                        @foreach ($heroIcons as $icon)
                                            <button type="button" onclick="selectIcon('{{ $icon }}')"
                                                class="icon-option p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 transition-colors flex items-center justify-center"
                                                data-icon="{{ $icon }}" title="{{ $icon }}">
                                                <div class="w-6 h-6 text-gray-600 dark:text-gray-400">
                                                    {!! App\Helpers\MenuHelper::renderIcon($icon) !!}
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- FontAwesome Examples -->
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">FontAwesome
                                        Examples</h4>
                                    <div
                                        class="grid grid-cols-8 gap-2 max-h-32 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        @php
                                            $faIcons = [
                                                'fa fa-home',
                                                'fa fa-users',
                                                'fa fa-user',
                                                'fa fa-shopping-cart',
                                                'fa fa-folder',
                                                'fa fa-file',
                                                'fa fa-cog',
                                                'fa fa-shield',
                                                'fa fa-key',
                                                'fa fa-chart-bar',
                                                'fa fa-calendar',
                                                'fa fa-envelope',
                                                'fa fa-bell',
                                                'fa fa-search',
                                                'fa fa-heart',
                                                'fa fa-star',
                                                'fa fa-trash',
                                                'fa fa-edit',
                                                'fa fa-plus',
                                                'fa fa-minus',
                                                'fa fa-check',
                                                'fa fa-times',
                                                'fa fa-arrow-right',
                                                'fa fa-arrow-left',
                                                'fa fa-arrow-up',
                                                'fa fa-arrow-down',
                                                'fa fa-bars',
                                                'fa fa-ellipsis-v',
                                                'fa fa-image',
                                                'fa fa-tags',
                                                'fa fa-archive',
                                                'fa fa-database',
                                                'fa fa-server',
                                                'fa fa-document',
                                                'fa fa-globe',
                                                'fa fa-link',
                                                'fa fa-external-link',
                                                'fa fa-download',
                                                'fa fa-upload',
                                                'fa fa-save',
                                                'fa fa-refresh',
                                                'fa fa-lock',
                                                'fa fa-unlock',
                                                'fa fa-eye',
                                                'fa fa-eye-slash',
                                                'fa fa-info-circle',
                                                'fa fa-exclamation-circle',
                                                'fa fa-question-circle',
                                            ];
                                        @endphp
                                        @foreach ($faIcons as $icon)
                                            <button type="button" onclick="selectIcon('{{ $icon }}')"
                                                class="icon-option p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 transition-colors flex items-center justify-center"
                                                data-icon="{{ $icon }}" title="{{ $icon }}">
                                                <i class="{{ $icon }} text-gray-600 dark:text-gray-400"></i>
                                            </button>
                                        @endforeach
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Note: FontAwesome requires CDN link in your layout. You can use any FontAwesome icon
                                        class (e.g., fa fa-home, fas fa-home, far fa-home, fab fa-facebook).
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" onclick="selectCustomIcon()"
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">
                        Use Custom Icon
                    </button>
                    <button type="button" onclick="closeIconPickerModal()"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Icon Picker Functions - must be in global scope for onclick
            let currentMenuIdForIcon = null;

            function openIconPicker(menuId, currentIcon) {
                currentMenuIdForIcon = menuId;
                const modal = document.getElementById('iconPickerModal');
                const input = document.getElementById('iconSearchInput');

                if (modal && input) {
                    input.value = currentIcon || '';
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                }
            }

            function closeIconPickerModal() {
                const modal = document.getElementById('iconPickerModal');
                const input = document.getElementById('iconSearchInput');

                if (modal) {
                    modal.classList.add('hidden');
                }
                if (input) {
                    input.value = '';
                }
                currentMenuIdForIcon = null;
            }

            function selectIcon(iconCode) {
                if (!currentMenuIdForIcon) return;

                // Update the input field
                const input = document.querySelector(`input[name="icon"][data-menu-id="${currentMenuIdForIcon}"]`);
                if (input) {
                    input.value = iconCode;
                    updateMenuField(currentMenuIdForIcon, 'icon', iconCode);
                    updateIconPreview(currentMenuIdForIcon, iconCode);
                }

                closeIconPickerModal();
            }

            function selectCustomIcon() {
                if (!currentMenuIdForIcon) return;

                const input = document.getElementById('iconSearchInput');
                if (!input) return;

                const iconCode = input.value.trim();
                if (!iconCode) {
                    alert('Please enter an icon code');
                    return;
                }

                selectIcon(iconCode);
            }

            function updateIconPreview(menuId, iconCode) {
                const preview = document.querySelector(`.icon-preview[data-menu-id="${menuId}"]`);
                if (!preview) return;

                // Fetch icon preview via AJAX
                fetch(`{{ route('admin.menus.icon-preview') }}?icon=${encodeURIComponent(iconCode)}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        preview.innerHTML = html;
                        // Also update sidebar icon if exists
                        updateSidebarIcon(menuId, iconCode);
                    })
                    .catch(error => {
                        console.error('Error updating icon preview:', error);
                    });
            }

            function updateSidebarIcon(menuId, iconCode) {
                // Find sidebar menu item by menu ID (if sidebar has data attribute)
                const sidebarItems = document.querySelectorAll('[data-sidebar-menu-id]');
                sidebarItems.forEach(item => {
                    if (item.getAttribute('data-sidebar-menu-id') == menuId) {
                        const iconContainer = item.querySelector('.menu-icon-container');
                        if (iconContainer) {
                            fetch(`{{ route('admin.menus.icon-preview') }}?icon=${encodeURIComponent(iconCode)}`, {
                                    method: 'GET',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                })
                                .then(response => response.text())
                                .then(html => {
                                    iconContainer.innerHTML = html;
                                })
                                .catch(error => {
                                    console.error('Error updating sidebar icon:', error);
                                });
                        }
                    }
                });
            }

            function updateMenuField(menuId, fieldName, fieldValue) {
                fetch(`/admin/menus/${menuId}/quick-update`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            [fieldName]: fieldValue
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // If icon changed, update preview and sidebar
                            if (fieldName === 'icon') {
                                updateIconPreview(menuId, fieldValue);
                            }
                            // If section_title changed, reload page
                            if (fieldName === 'section_title') {
                                window.location.reload();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error updating menu:', error);
                    });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Close modal on ESC key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        const editModal = document.getElementById('editSectionTitleModal');
                        if (editModal && !editModal.classList.contains('hidden')) {
                            closeEditSectionTitleModal();
                        }

                        const iconModal = document.getElementById('iconPickerModal');
                        if (iconModal && !iconModal.classList.contains('hidden')) {
                            closeIconPickerModal();
                        }
                    }
                });

                const menuEditorContainer = document.getElementById('menuEditorContainer');

                // Initialize SortableJS for sections (can drag entire sections)
                new Sortable(menuEditorContainer, {
                    handle: '.section-drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    draggable: '.menu-section',
                    onEnd: function(evt) {
                        // Get all menu items in the new order (across all sections)
                        const allMenuItems = Array.from(document.querySelectorAll('.menu-item'));
                        const menuIds = allMenuItems.map(item => item.getAttribute('data-menu-id'));

                        // Update sort order
                        fetch('{{ route('admin.menus.update-sort-order') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    menu_ids: menuIds
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Reload page to refresh section grouping
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 300);
                                }
                            });
                    }
                });

                // Initialize SortableJS for each section list, but allow dragging between sections
                document.querySelectorAll('.menu-items-list').forEach(function(list) {
                    new Sortable(list, {
                        handle: '.cursor-move',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        group: 'menus', // Same group name allows dragging between lists
                        draggable: '.menu-item',
                        onEnd: function(evt) {
                            const menuItem = evt.item;
                            const menuId = menuItem.getAttribute('data-menu-id');
                            const oldSection = menuItem.getAttribute('data-section');

                            // Get new section from parent
                            const newSectionElement = menuItem.closest('.menu-section');
                            const newSection = newSectionElement ? newSectionElement.getAttribute(
                                'data-section') : null;

                            // If section changed, update section_title
                            if (newSection && newSection !== oldSection) {
                                // Update data-section attribute
                                menuItem.setAttribute('data-section', newSection);

                                // Update section dropdown
                                const sectionSelect = menuItem.querySelector(
                                    '.menu-section-select');
                                if (sectionSelect) {
                                    const sectionTitle = newSection === 'Main Menu' ? '' :
                                        newSection;
                                    sectionSelect.value = sectionTitle;
                                }

                                // Update section_title in database
                                const sectionTitle = newSection === 'Main Menu' ? '' : newSection;
                                fetch(`/admin/menus/${menuId}/quick-update`, {
                                        method: 'PUT',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            section_title: sectionTitle
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            console.log('Section updated for menu:', menuId);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error updating section:', error);
                                    });
                            }

                            // Get all menu items in the new order (across all sections)
                            const allMenuItems = Array.from(document.querySelectorAll(
                                '.menu-item'));
                            const menuIds = allMenuItems.map(item => item.getAttribute(
                                'data-menu-id'));

                            // Update sort order
                            fetch('{{ route('admin.menus.update-sort-order') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        menu_ids: menuIds
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Reload page to refresh section grouping
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 500);
                                    }
                                });
                        }
                    });
                });

                // Handle section title select change
                document.querySelectorAll('.menu-section-select').forEach(function(select) {
                    select.addEventListener('change', function() {
                        const menuId = this.getAttribute('data-menu-id');
                        const newInput = this.parentElement.querySelector('.menu-section-new');

                        if (this.value === '__new__') {
                            newInput.classList.remove('hidden');
                            newInput.setAttribute('name', 'section_title');
                            this.setAttribute('name', 'section_title_old');
                        } else {
                            newInput.classList.add('hidden');
                            newInput.removeAttribute('name');
                            this.setAttribute('name', 'section_title');
                            updateMenuField(menuId, 'section_title', this.value);
                        }
                    });
                });

                // Handle input changes with debounce
                let updateTimeouts = {};
                document.querySelectorAll('.menu-edit-input').forEach(function(input) {
                    input.addEventListener('change', function() {
                        const menuId = this.getAttribute('data-menu-id');
                        const fieldName = this.getAttribute('name');
                        const fieldValue = this.value;

                        // Clear existing timeout
                        if (updateTimeouts[menuId + fieldName]) {
                            clearTimeout(updateTimeouts[menuId + fieldName]);
                        }

                        // Set new timeout for debounce
                        updateTimeouts[menuId + fieldName] = setTimeout(function() {
                            updateMenuField(menuId, fieldName, fieldValue);
                        }, 1000);
                    });

                    // Also handle icon input on blur for immediate update
                    if (input.getAttribute('name') === 'icon') {
                        input.addEventListener('blur', function() {
                            const menuId = this.getAttribute('data-menu-id');
                            const fieldValue = this.value;
                            updateIconPreview(menuId, fieldValue);
                        });
                    }
                });


                // Handle new section title input
                document.querySelectorAll('.menu-section-new').forEach(function(input) {
                    input.addEventListener('change', function() {
                        const menuId = this.getAttribute('data-menu-id');
                        const sectionTitle = this.value;
                        if (sectionTitle) {
                            updateMenuField(menuId, 'section_title', sectionTitle);
                            // Reload page to refresh section grouping
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }
                    });
                });
            });

            function editSectionTitle(currentTitle) {
                // Set current title in modal input
                document.getElementById('sectionTitleInput').value = currentTitle;
                document.getElementById('currentSectionTitle').value = currentTitle;

                // Show modal
                document.getElementById('editSectionTitleModal').classList.remove('hidden');

                // Focus on input
                setTimeout(() => {
                    document.getElementById('sectionTitleInput').focus();
                    document.getElementById('sectionTitleInput').select();
                }, 100);
            }

            function closeEditSectionTitleModal() {
                document.getElementById('editSectionTitleModal').classList.add('hidden');
                document.getElementById('sectionTitleInput').value = '';
                document.getElementById('currentSectionTitle').value = '';
            }

            function saveSectionTitle() {
                const currentTitle = document.getElementById('currentSectionTitle').value;
                const newTitle = document.getElementById('sectionTitleInput').value.trim();

                if (!newTitle) {
                    alert('Section title cannot be empty');
                    return;
                }

                if (newTitle === currentTitle) {
                    closeEditSectionTitleModal();
                    return;
                }

                // Update all menus in this section
                const sectionMenus = document.querySelectorAll(`[data-section="${currentTitle}"][data-menu-id]`);
                let updateCount = 0;
                const totalMenus = sectionMenus.length;

                if (totalMenus === 0) {
                    closeEditSectionTitleModal();
                    return;
                }

                sectionMenus.forEach(function(menuItem) {
                    const menuId = menuItem.getAttribute('data-menu-id');
                    updateMenuField(menuId, 'section_title', newTitle);
                    updateCount++;

                    // After all updates, reload page
                    if (updateCount === totalMenus) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                });

                closeEditSectionTitleModal();
            }
        </script>
    @endpush
@endsection
