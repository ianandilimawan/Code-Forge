@props(['menu'])

@php
    $url = '#';

    if ($menu->route) {
        // Check if route exists
        if (Route::has($menu->route)) {
            $url = route($menu->route);
        } else {
            // Route doesn't exist yet
        $url = 'javascript:void(0)';
        }
    } elseif ($menu->url) {
        $url = $menu->url;
    }

    $isActive = $menu->route && Route::has($menu->route) && request()->routeIs($menu->route);
@endphp

<a href="{{ $url }}"
    class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-150 cursor-pointer {{ $isActive ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    data-sidebar-menu-id="{{ $menu->id }}">
    <div class="w-5 h-5 mr-3 flex items-center justify-center menu-icon-container">
        {!! App\Helpers\MenuHelper::renderIcon($menu->icon ?? '') !!}
    </div>
    {{ $menu->name }}
</a>
