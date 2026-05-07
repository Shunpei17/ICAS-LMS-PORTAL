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

        // View composer for all portals
        View::composer(['layouts.admin', 'layouts.faculty', 'layouts.student'], function ($view): void {
            $settings = new \App\Services\SystemSettingsService();
            $activeAY = $settings->get('academic_year', '2024–2025');
            $activeSem = $settings->get('current_semester', 'Second Semester');
            $activeTerm = "A.Y. $activeAY | $activeSem";

            // Enrollment Dates for Student Portal logic
            $enrollmentStart = $settings->get('enrollment_start');
            $enrollmentEnd = $settings->get('enrollment_end');
            $isEnrollmentPeriod = false;
            if ($enrollmentStart && $enrollmentEnd) {
                $now = now();
                $isEnrollmentPeriod = $now->between(
                    \Carbon\Carbon::parse($enrollmentStart)->startOfDay(),
                    \Carbon\Carbon::parse($enrollmentEnd)->endOfDay()
                );
            }

            $newAnnouncementsCount = 0;

            if (Auth::check() && Schema::hasTable('announcements')) {
                $userRole = strtolower((string) Auth::user()->role);

                $query = Announcement::query()->where('created_at', '>=', now()->subDay());

                if (in_array($userRole, ['faculty', 'student'], true)) {
                    $query->visibleToAudience($userRole);
                }

                $newAnnouncementsCount = $query->count();
            }

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
