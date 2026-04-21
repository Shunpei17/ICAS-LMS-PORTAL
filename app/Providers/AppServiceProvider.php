<?php

namespace App\Providers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
