<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share menus to all admin views - filter by user permissions
        View::composer('admin.*', function ($view) {
            $user = Auth::user();
            $menus = Menu::active()
                ->root()
                ->accessibleBy($user)
                ->orderBy('section_title')
                ->orderBy('sort_order')
                ->get();

            // Group menus by section_title, then sort by sort_order within each section
            $groupedMenus = $menus->groupBy(function ($menu) {
                return $menu->section_title ?? 'Main Menu';
            })->map(function ($sectionMenus) {
                // Sort menus within section by sort_order
                return $sectionMenus->sortBy('sort_order')->values();
            });

            // Sort sections by the sort_order of the first menu in each section
            $groupedMenus = $groupedMenus->sortBy(function ($sectionMenus) {
                return $sectionMenus->first()->sort_order ?? 0;
            });

            $view->with('groupedMenus', $groupedMenus);
            $view->with('menus', $menus); // Keep for backward compatibility
        });
    }
}
