<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncNewsJob;
use App\Jobs\SyncExchangeRatesJob;
use App\Jobs\SyncWeatherJob;
use App\Jobs\RecalculateRisksJob;
use App\Jobs\SyncWorldBankJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ═══════════════════════════════════════════════
// SCRI Automated Scheduler
// Run: php artisan schedule:work  (development)
//      php artisan schedule:run   (cron: * * * * *)
// ═══════════════════════════════════════════════

// Sync live news every 15 minutes
Schedule::job(new SyncNewsJob())->everyFifteenMinutes()->name('sync-news')->withoutOverlapping(10);

// Refresh exchange rates every 6 hours
Schedule::job(new SyncExchangeRatesJob())->everySixHours()->name('sync-currencies')->withoutOverlapping(5);

// Refresh weather for priority countries every hour
Schedule::job(new SyncWeatherJob())->hourly()->name('sync-weather')->withoutOverlapping(15);

// Refresh World Bank data daily at 02:00
Schedule::job(new SyncWorldBankJob())->dailyAt('02:00')->name('sync-worldbank')->withoutOverlapping(30);

// Recalculate risk scores every 30 minutes
Schedule::job(new RecalculateRisksJob())->everyThirtyMinutes()->name('recalculate-risks')->withoutOverlapping(20);
