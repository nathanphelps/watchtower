<?php

namespace NathanPhelps\Watchtower;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\Events\JobRetryRequested;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use NathanPhelps\Watchtower\Services\JobMonitor;

class WatchtowerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMigrations();
        $this->registerGate();
        $this->registerEventListeners();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/watchtower.php',
            'watchtower'
        );

        $this->app->singleton(JobMonitor::class);
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/watchtower.php' => config_path('watchtower.php'),
            ], 'watchtower-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'watchtower-migrations');
        }
    }

    /**
     * Register the package's migrations.
     */
    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the Watchtower gate.
     */
    protected function registerGate(): void
    {
        Gate::define(config('watchtower.gate', 'viewWatchtower'), function ($user = null) {
            // By default, allow access in local environment only
            if (app()->environment('local')) {
                return true;
            }

            // In production, require explicit gate definition
            return false;
        });
    }

    /**
     * Register event listeners for queue monitoring.
     */
    protected function registerEventListeners(): void
    {
        $monitor = $this->app->make(JobMonitor::class);

        Event::listen(JobQueued::class, function ($event) use ($monitor) {
            $monitor->recordJobQueued($event);
        });

        Event::listen(JobProcessing::class, function ($event) use ($monitor) {
            $monitor->recordJobStarted($event);
        });

        Event::listen(JobProcessed::class, function ($event) use ($monitor) {
            $monitor->recordJobCompleted($event);
        });

        Event::listen(JobFailed::class, function ($event) use ($monitor) {
            $monitor->recordJobFailed($event);
        });

        Event::listen(JobRetryRequested::class, function ($event) use ($monitor) {
            $monitor->recordJobRetrying($event);
        });
    }
}
