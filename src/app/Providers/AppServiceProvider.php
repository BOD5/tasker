<?php

namespace App\Providers;

use App\Models\TimeEntry;
use App\Policies\TimeEntryPolicy;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    protected $policies = [
        TimeEntry::class => TimeEntryPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
