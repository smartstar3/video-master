<?php

namespace MotionArray\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AlgoliaPushSearchData::class,
        Commands\AlgoliaRemoveTrashedProducts::class,
        Commands\CheckOldEncodingJob::class,
        Commands\CleanupTrashedProductDownloads::class,
        Commands\DeleteSubmissions::class,
        Commands\DowngradeUsersAtEndOfFreeloaderExpirationPeriodCommand::class,
        Commands\DowngradeUsersOutsideGracePeriodCommand::class,
        Commands\DowngradeUsersToPlan::class,
        Commands\EmailMissingPackages::class,
        Commands\ExpirePortfolioSubdomain::class,
        Commands\ExportUsersToIntercom::class,
        Commands\FreeloadersDueToExpireCommand::class,
        Commands\MissingStripeUsersOnDB::class,
        Commands\PermanentDeleteForProjects::class,
        Commands\PushYouTubeVideos::class,
        Commands\RefreshYouTubeData::class,
        Commands\RemoveDeletedUsersData::class,
        Commands\RemoveFreeUserContent::class,
        Commands\RemoveInvalidIntercomUsers::class,
        Commands\SendContentRemovalWarning::class,
        Commands\UpdateDownloadsCount::class,
        Commands\UpdatePayoneerPayeeStatus::class,
        Commands\UpdatePayoutTotals::class,
        Commands\UpdateSellerPayoutTotals::class,
        Commands\UpdateSellerStatsCache::class,
        Commands\UpdateStripeCustomers::class,
        Commands\OneTime\FixFreeProductSubCategories::class,
        Commands\OneTime\FixS3XMLErrors::class,
        Commands\OneTime\UpdateCountryIdFromStripe::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $path = storage_path('logs/scheduled_tasks' . date('-m-y') . '.log');

        // Run every hour
        $schedule->command('motionarray:downgrade-users-to-plan')
            ->cron('0 * * * *')->appendOutputTo($path);

        $schedule->command('motionarray:export-users-to-intercom --last-day')
            ->twiceDaily(0, 12)->appendOutputTo($path);

        $schedule->command('motionarray:downgrade-users-at-end-of-freeloader-expiration-period')
            ->cron('1 0 * * *')->appendOutputTo($path);

        $schedule->command('motionarray:downgrade-users-outside-grace-period')
            ->cron('1 0 * * *')->appendOutputTo($path);

        $schedule->command('motionarray:email-admin-about-freeloaders-due-to-expire')
            ->cron('1 0 * * *')->appendOutputTo($path);

        $schedule->command('motionarray:update-payout-totals')
            ->hourly()->appendOutputTo($path);

        $schedule->command('motionarray:update-seller-payout-totals')
            ->cron('1 0 1 * *')->appendOutputTo($path);

        $schedule->command('motionarray:email-missing-packages')
            ->dailyAt('18:00')->appendOutputTo($path);

        $schedule->command('motionarray:email-missing-packages --minutes=7 --email=false')
            ->everyFiveMinutes()->appendOutputTo($path);

        $schedule->command('motionarray:expire-portfolio-subdomain')
            ->cron('1 0 * * *')->appendOutputTo($path);

        $schedule->command('motionarray:update-downloads-count')
            ->twiceDaily(2, 14)->appendOutputTo($path);

        $schedule->command('motionarray:update-seller-stats-cache')
            ->hourly()->appendOutputTo($path);

        $schedule->command('motionarray:permanent-delete-for-projects')
            ->cron('1 0 * * *')->appendOutputTo($path);

        $schedule->command('motionarray:update-payoneer-payee-status')
            ->hourly()->appendOutputTo($path);

        $schedule->command('motionarray:remove-deleted-users-data')
            ->dailyAt('02:00')->appendOutputTo($path);

        $schedule->command('motionarray:remove-free-user-content')
            ->dailyAt('03:00')->appendOutputTo($path);

        $schedule->command('motionarray:delete-requested-submissions')
            ->dailyAt('04:00')->appendOutputTo($path);

        $schedule->command('motionarray:send-content-removal-warning')
            ->hourly()->appendOutputTo($path);

        $schedule->command('motionarray:remove-invalid-intercom-users')
            ->sundays()->appendOutputTo($path);

        $schedule->command('motionarray:refresh-youtube-data --older-than-days=20 --limit=10000 --force')
            ->daily()->at('6:00')->appendOutputTo($path);
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require app_path('Http/Routes/console.php');
    }
}
