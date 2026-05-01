<?php

use App\Http\Middleware\CheckForcePasswordChange;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureClassroomActive;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'classroom.active' => EnsureClassroomActive::class,
            'force.password.change' => CheckForcePasswordChange::class,
            'maintenance' => CheckMaintenanceMode::class,
        ]);
        // Apply maintenance check to every web request
        $middleware->web(append: [
            CheckMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
