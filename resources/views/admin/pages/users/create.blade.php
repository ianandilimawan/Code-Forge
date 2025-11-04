@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Add New User</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create a new user account</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
            <form action="{{ route('admin.users.store') }}" method="POST" class="p-8 space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 pr-12 transition-colors">
                        <button type="button" id="togglePassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition-colors">
                            <!-- Eye icon (visible) -->
                            <svg id="eyeIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            <!-- Eye off icon (hidden) -->
                            <svg id="eyeOffIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                                </path>
                            </svg>
                        </button>
                    </div>
                    <!-- Password Strength Indicator -->
                    <div id="passwordStrength" class="mt-2 hidden">
                        <div class="flex gap-2 mb-2">
                            <div id="strengthBar1"
                                class="h-2 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-colors"></div>
                            <div id="strengthBar2"
                                class="h-2 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-colors"></div>
                            <div id="strengthBar3"
                                class="h-2 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-colors"></div>
                            <div id="strengthBar4"
                                class="h-2 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-colors"></div>
                        </div>
                        <p id="strengthText" class="text-xs font-medium"></p>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 pr-12 transition-colors">
                        <button type="button" id="togglePasswordConfirmation"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition-colors">
                            <!-- Eye icon (visible) -->
                            <svg id="eyeIconConfirmation" class="w-6 h-6" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            <!-- Eye off icon (hidden) -->
                            <svg id="eyeOffIconConfirmation" class="w-6 h-6 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                                </path>
                            </svg>
                        </button>
                    </div>
                    <!-- Password Match Indicator -->
                    <div id="passwordMatchIndicator" class="mt-2 hidden">
                        <p id="passwordMatchText" class="text-xs font-medium flex items-center gap-1">
                            <svg id="matchIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <svg id="mismatchIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span id="matchText"></span>
                        </p>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Role</label>
                    <div
                        class="space-y-2 max-h-48 overflow-y-auto border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        @if ($roles->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No roles available</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach ($roles as $role)
                                    <label
                                        class="flex items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors role-radio"
                                        data-role-id="{{ $role->id }}"
                                        data-role-permissions="{{ $role->permissions->pluck('id')->toJson() }}">
                                        <input type="radio" name="role" value="{{ $role->id }}"
                                            {{ old('role') == $role->id ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <span
                                            class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Select one role for this user. Permissions
                        from
                        the selected role will be automatically checked.</p>
                </div>

                <!-- Permissions -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Permissions</label>
                        @if (!$permissions->isEmpty())
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="select-all-permissions"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Select All</span>
                            </label>
                        @endif
                    </div>
                    <div
                        class="space-y-4 max-h-96 overflow-y-auto border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        @if ($permissions->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No permissions available</p>
                        @else
                            @php
                                $groupedPermissions = $permissions->groupBy('module');
                            @endphp
                            @foreach ($groupedPermissions as $module => $modulePermissions)
                                <div
                                    class="mb-4 pb-4 border-b-2 border-gray-100 dark:border-gray-700 last:border-b-0 last:mb-0 last:pb-0">
                                    <div class="flex items-center justify-between mb-3">
                                        @if ($module)
                                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase">
                                                {{ $module }}</h3>
                                        @else
                                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Other</h3>
                                        @endif
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                class="select-all-module w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                data-module="{{ $module ?? 'other' }}">
                                            <span class="ml-2 text-xs font-medium text-gray-600 dark:text-gray-400">Select
                                                All</span>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach ($modulePermissions as $permission)
                                            <label
                                                class="flex items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors permission-checkbox"
                                                data-module="{{ $module ?? 'other' }}">
                                                <input type="checkbox" name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <span
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Select specific permissions for this user (in
                        addition to role permissions)</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-8 border-t-2 border-gray-100 dark:border-gray-700 mt-8">
                    <a href="{{ route('admin.users.index') }}"
                        class="px-8 py-3 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors font-semibold shadow-md hover:shadow-lg border-2 border-gray-200 dark:border-gray-600">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold shadow-md hover:shadow-lg hover:scale-105 transform">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password Toggle Functionality
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const eyeIconConfirmation = document.getElementById('eyeIconConfirmation');
            const eyeOffIconConfirmation = document.getElementById('eyeOffIconConfirmation');

            // Toggle password visibility
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    eyeIcon.classList.toggle('hidden');
                    eyeOffIcon.classList.toggle('hidden');
                });
            }

            // Toggle password confirmation visibility
            if (togglePasswordConfirmation) {
                togglePasswordConfirmation.addEventListener('click', function() {
                    const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' :
                        'password';
                    passwordConfirmationInput.setAttribute('type', type);
                    eyeIconConfirmation.classList.toggle('hidden');
                    eyeOffIconConfirmation.classList.toggle('hidden');
                });
            }

            // Password Strength Indicator
            function calculatePasswordStrength(password) {
                let points = 0;
                let feedback = [];

                if (password.length === 0) {
                    return {
                        strength: 0,
                        feedback: []
                    };
                }

                // Length checks (max 2 points)
                if (password.length >= 8) {
                    points++;
                    if (password.length >= 12) points++;
                } else {
                    feedback.push('at least 8 characters');
                }

                // Character variety checks (max 4 points)
                if (/[a-z]/.test(password)) {
                    points++;
                } else {
                    feedback.push('lowercase letter');
                }

                if (/[A-Z]/.test(password)) {
                    points++;
                } else {
                    feedback.push('uppercase letter');
                }

                if (/[0-9]/.test(password)) {
                    points++;
                } else {
                    feedback.push('number');
                }

                if (/[^A-Za-z0-9]/.test(password)) {
                    points++;
                } else {
                    feedback.push('special character');
                }

                // Determine strength level (0-4)
                // 0-1 points = 0 (Very Weak)
                // 2 points = 1 (Weak)
                // 3-4 points = 2 (Fair)
                // 5 points = 3 (Good)
                // 6 points = 4 (Strong)
                let strengthLevel;
                if (points <= 1) strengthLevel = 0;
                else if (points === 2) strengthLevel = 1;
                else if (points <= 4) strengthLevel = 2;
                else if (points === 5) strengthLevel = 3;
                else strengthLevel = 4;

                return {
                    strength: strengthLevel,
                    feedback: feedback
                };
            }

            function updatePasswordStrength() {
                const password = passwordInput.value;
                const strengthDiv = document.getElementById('passwordStrength');
                const strengthBars = [
                    document.getElementById('strengthBar1'),
                    document.getElementById('strengthBar2'),
                    document.getElementById('strengthBar3'),
                    document.getElementById('strengthBar4')
                ];
                const strengthText = document.getElementById('strengthText');

                if (password.length === 0) {
                    strengthDiv.classList.add('hidden');
                    return;
                }

                strengthDiv.classList.remove('hidden');
                const {
                    strength: strengthLevel,
                    feedback
                } = calculatePasswordStrength(password);

                // Reset all bars
                strengthBars.forEach(bar => {
                    bar.classList.remove('bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500');
                    bar.classList.add('bg-gray-200', 'dark:bg-gray-700');
                });

                // Set colors based on strength
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
                const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                const textColors = [
                    'text-red-600 dark:text-red-400',
                    'text-orange-600 dark:text-orange-400',
                    'text-yellow-600 dark:text-yellow-400',
                    'text-green-600 dark:text-green-400',
                    'text-green-600 dark:text-green-400'
                ];

                for (let i = 0; i <= strengthLevel; i++) {
                    if (strengthBars[i]) {
                        strengthBars[i].classList.remove('bg-gray-200', 'dark:bg-gray-700');
                        strengthBars[i].classList.add(colors[strengthLevel] || colors[0]);
                    }
                }

                // Update text
                strengthText.className = `text-xs font-medium ${textColors[strengthLevel] || textColors[0]}`;
                if (strengthLevel >= 4) {
                    strengthText.textContent = `✓ ${texts[strengthLevel]}`;
                } else {
                    const missing = feedback.slice(0, 2).join(' and ');
                    strengthText.textContent = `${texts[strengthLevel]} - Add ${missing}`;
                }
            }

            // Password Match Validation
            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirmation = passwordConfirmationInput.value;
                const matchIndicator = document.getElementById('passwordMatchIndicator');
                const matchText = document.getElementById('matchText');
                const matchIcon = document.getElementById('matchIcon');
                const mismatchIcon = document.getElementById('mismatchIcon');

                if (confirmation.length === 0) {
                    matchIndicator.classList.add('hidden');
                    return;
                }

                matchIndicator.classList.remove('hidden');

                if (password === confirmation) {
                    matchText.textContent = 'Passwords match';
                    matchText.className = 'text-xs font-medium text-green-600 dark:text-green-400';
                    matchIcon.classList.remove('hidden');
                    mismatchIcon.classList.add('hidden');
                    matchIcon.classList.add('text-green-600', 'dark:text-green-400');
                } else {
                    matchText.textContent = 'Passwords do not match';
                    matchText.className = 'text-xs font-medium text-red-600 dark:text-red-400';
                    mismatchIcon.classList.remove('hidden');
                    matchIcon.classList.add('hidden');
                    mismatchIcon.classList.add('text-red-600', 'dark:text-red-400');
                }
            }

            // Event listeners for password fields
            passwordInput.addEventListener('input', function() {
                updatePasswordStrength();
                checkPasswordMatch();
            });

            passwordConfirmationInput.addEventListener('input', function() {
                checkPasswordMatch();
            });

            // Permissions Select All - Define variables first
            const selectAllPermissions = document.getElementById('select-all-permissions');
            const moduleCheckboxes = document.querySelectorAll('.select-all-module');
            const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');
            const roleRadios = document.querySelectorAll('input[name="role"]');

            // Select All Permissions functionality
            function updateSelectAllPermissions() {
                if (selectAllPermissions) {
                    const allChecked = Array.from(permissionCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(permissionCheckboxes).some(cb => cb.checked);
                    selectAllPermissions.checked = allChecked;
                    selectAllPermissions.indeterminate = someChecked && !allChecked;
                }
            }

            // Update module checkboxes
            function updateModuleCheckboxes() {
                moduleCheckboxes.forEach(moduleCheckbox => {
                    const module = moduleCheckbox.getAttribute('data-module');
                    const modulePermissionCheckboxes = document.querySelectorAll(
                        `.permission-checkbox[data-module="${module}"] input[type="checkbox"]`
                    );
                    const allChecked = Array.from(modulePermissionCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(modulePermissionCheckboxes).some(cb => cb.checked);
                    moduleCheckbox.checked = allChecked;
                    moduleCheckbox.indeterminate = someChecked && !allChecked;
                });
            }

            if (selectAllPermissions) {
                selectAllPermissions.addEventListener('change', function() {
                    permissionCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    moduleCheckboxes.forEach(moduleCheckbox => {
                        moduleCheckbox.checked = this.checked;
                    });
                });
            }

            // Module Select All functionality
            moduleCheckboxes.forEach(moduleCheckbox => {
                moduleCheckbox.addEventListener('change', function() {
                    const module = this.getAttribute('data-module');
                    const modulePermissionCheckboxes = document.querySelectorAll(
                        `.permission-checkbox[data-module="${module}"] input[type="checkbox"]`
                    );
                    modulePermissionCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateSelectAllPermissions();
                });
            });

            // Individual checkbox change
            permissionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectAllPermissions();
                    updateModuleCheckboxes();
                });
            });

            // Role radio buttons - auto check permissions when role is selected
            roleRadios.forEach(radio => {
                // Check permissions from selected role immediately on page load
                if (radio.checked) {
                    const roleLabel = radio.closest('label.role-radio');
                    if (roleLabel) {
                        const rolePermissionsJson = roleLabel.getAttribute('data-role-permissions');

                        if (rolePermissionsJson && rolePermissionsJson !== '[]' && rolePermissionsJson
                            .trim() !== '') {
                            try {
                                const rolePermissions = JSON.parse(rolePermissionsJson);
                                permissionCheckboxes.forEach(checkbox => {
                                    if (rolePermissions.includes(parseInt(checkbox.value))) {
                                        checkbox.checked = true;
                                    }
                                });
                            } catch (e) {
                                console.error('Error parsing role permissions:', e);
                            }
                        }
                    }
                }

                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const roleLabel = this.closest('label.role-radio');
                        if (roleLabel) {
                            const rolePermissionsJson = roleLabel.getAttribute(
                                'data-role-permissions');

                            if (rolePermissionsJson && rolePermissionsJson !== '[]' &&
                                rolePermissionsJson.trim() !== '') {
                                try {
                                    const rolePermissions = JSON.parse(rolePermissionsJson);

                                    // Check permissions from the selected role (don't uncheck existing ones)
                                    permissionCheckboxes.forEach(checkbox => {
                                        if (rolePermissions.includes(parseInt(checkbox
                                                .value))) {
                                            checkbox.checked = true;
                                        }
                                    });

                                    // Update module and select all checkboxes
                                    updateSelectAllPermissions();
                                    updateModuleCheckboxes();
                                } catch (e) {
                                    console.error('Error parsing role permissions:', e);
                                }
                            }
                        }
                    }
                });
            });

            // Initialize - use setTimeout to ensure all permissions from role are checked first
            setTimeout(() => {
                updateSelectAllPermissions();
                updateModuleCheckboxes();
            }, 100);
        });
    </script>
@endsection
