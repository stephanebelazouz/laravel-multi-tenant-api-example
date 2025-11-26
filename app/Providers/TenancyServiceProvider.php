<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();
    }

    protected function bootEvents()
    {
        // TenantCreated event
        Event::listen(Events\TenantCreated::class, function (Events\TenantCreated $event) {
            Log::info('TenantCreated event fired', [
                'tenant_id' => $event->tenant->id,
            ]);

            try {
                // Create new tenant database job
                Log::info('Creating database...');
                dispatch_sync(new Jobs\CreateDatabase($event->tenant));
                Log::info('Database created');

                // Run migrations for the new tenant database
                Log::info('Running migrations...');
                dispatch_sync(new Jobs\MigrateDatabase($event->tenant));
                Log::info('Migrations completed');
            } catch (\Exception $e) {
                Log::error('Error in TenantCreated', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);

                throw $e;
            }
        });

        // TenantDeleted event
        Event::listen(Events\TenantDeleted::class, function (Events\TenantDeleted $event) {
            Log::info('TenantDeleted event fired', [
                'tenant_id' => $event->tenant->id,
            ]);

            try {
                dispatch_sync(new Jobs\DeleteDatabase($event->tenant));
                Log::info('Database deleted');
            } catch (\Exception $e) {
                Log::error('Error deleting database', [
                    'error' => $e->getMessage(),
                ]);
            }
        });

        // TenancyInitialized event
        Event::listen(Events\TenancyInitialized::class, function (Events\TenancyInitialized $event) {
            app(Listeners\BootstrapTenancy::class)->handle($event);
        });

        // TenancyEnded event
        Event::listen(Events\TenancyEnded::class, function (Events\TenancyEnded $event) {
            app(Listeners\RevertToCentralContext::class)->handle($event);
        });
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::group([], function () {
                    require base_path('routes/tenant.php');
                });
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
