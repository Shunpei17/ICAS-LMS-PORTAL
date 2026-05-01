<?php

namespace App\Providers;

use App\Services\SystemSettingsService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            $settings = new SystemSettingsService;

            $portalTheme = [
                'admin' => $settings->get('theme_admin_color', '#16a34a'),
                'faculty' => $settings->get('theme_faculty_color', '#f59e0b'),
                'student' => $settings->get('theme_student_color', '#7c3aed'),
            ];
        } catch (\Exception $e) {
            // DB unavailable during build (e.g. Railway config:cache)
            $portalTheme = [
                'admin' => '#16a34a',
                'faculty' => '#f59e0b',
                'student' => '#7c3aed',
            ];
        }

        View::share('portalTheme', $portalTheme);
    }
}
