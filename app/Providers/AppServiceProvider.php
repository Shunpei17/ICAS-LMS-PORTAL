<?php

namespace App\Providers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Global Timezone Setting — wrapped in try-catch for Railway cold starts
        try {
            if (Schema::hasTable('system_settings')) {
                $settings = new \App\Services\SystemSettingsService();
                $tz = $settings->get('timezone');
                if ($tz) {
                    config(['app.timezone' => $tz]);
                    date_default_timezone_set($tz);
                }
            }
        } catch (\Exception $e) {
            // Database not yet available (e.g., during config caching) — use defaults
        }

        // View composer for all portals
        View::composer(['layouts.*', 'admin.*', 'faculty.*', 'student.*'], function ($view): void {
            $activeAY = '2024–2025';
            $activeSem = 'Second Semester';
            $isEnrollmentPeriod = false;
            $newAnnouncementsCount = 0;

            try {
                $settings = new \App\Services\SystemSettingsService();
                $activeAY = $settings->get('academic_year', '2024–2025');
                $activeSem = $settings->get('current_semester', 'Second Semester');

                // Enrollment Dates for Student Portal logic
                $enrollmentStart = $settings->get('enrollment_start');
                $enrollmentEnd = $settings->get('enrollment_end');
                if ($enrollmentStart && $enrollmentEnd) {
                    $now = now();
                    $isEnrollmentPeriod = $now->between(
                        \Carbon\Carbon::parse($enrollmentStart)->startOfDay(),
                        \Carbon\Carbon::parse($enrollmentEnd)->endOfDay()
                    );
                }

                if (Auth::check() && Schema::hasTable('announcements')) {
                    $userRole = strtolower((string) Auth::user()->role);

                    $query = Announcement::query()->where('created_at', '>=', now()->subDay());

                    if (in_array($userRole, ['faculty', 'student'], true)) {
                        $query->visibleToAudience($userRole);
                    }

                    $newAnnouncementsCount = $query->count();
                }
            } catch (\Exception $e) {
                // Database not available — use defaults
            }

            $activeTerm = "A.Y. $activeAY | $activeSem";

            $view->with([
                'activeAY' => $activeAY,
                'activeSem' => $activeSem,
                'activeTerm' => $activeTerm,
                'isEnrollmentPeriod' => $isEnrollmentPeriod,
                'newAnnouncementsCount' => $newAnnouncementsCount
            ]);
        });
    }
}
