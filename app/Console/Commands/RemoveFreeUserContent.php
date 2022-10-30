<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use MotionArray\Models\Plan;
use MotionArray\Models\User;
use MotionArray\Repositories\ProjectRepository;

class RemoveFreeUserContent extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:remove-free-user-content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete free users uploads after 30 days';

    protected $projectRepo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepo = $projectRepository;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now();

        User::onFreePlan()->has('projects')->whereNotNull('portfolio_trial_ends_at')->where('portfolio_trial_ends_at', '<', $now)
            ->chunk(100, function ($users) {

                $this->info('Processing ' . count($users) . ' users.');

                foreach ($users as $user) {
                    $this->deleteUserUploads($user);
                }
            });
    }

    public function deleteUserUploads($user)
    {
        //Get the users projects
        $projects = $this->projectRepo->findByUser($user->id);
        foreach ($projects as $project) {
            $this->projectRepo->delete($project, false);
        }
    }
}
