<?php namespace MotionArray\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MotionArray\Models\UserSite;

class ExpirePortfolioSubdomain extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:expire-portfolio-subdomain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire subdomain some time after changed the settings to domain';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        UserSite::where('slug_expires_at', '<', Carbon::now())
            ->update(['slug' => null, 'slug_expires_at' => null]);
    }
}
