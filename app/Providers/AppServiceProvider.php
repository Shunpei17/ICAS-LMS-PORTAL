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

        // View composer for announcements
        View::composer(['layouts.admin', 'layouts.faculty', 'layouts.student'], function ($view): void {
            $newAnnouncementsCount = 0;

            if (Auth::check() && Schema::hasTable('announcements')) {
                $userRole = strtolower((string) Auth::user()->role);

                $query = Announcement::query()->where('created_at', '>=', now()->subDay());

                if (in_array($userRole, ['faculty', 'student'], true)) {
                    $query->visibleToAudience($userRole);
                }

                $newAnnouncementsCount = $query->count();
            }

            $view->with('newAnnouncementsCount', $newAnnouncementsCount);
        });
    }
}