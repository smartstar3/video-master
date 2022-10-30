<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Models\Project;
use MotionArray\Repositories\ProjectRepository;
use Carbon\Carbon;

class PermanentDeleteForProjects extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:permanent-delete-for-projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes previous deactivated projects.';

    protected $project;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProjectRepository $project)
    {
        $this->project = $project;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deletedAt = Carbon::now()->subDays(30);

        $projects = Project::onlyTrashed()
            ->where('deleted_at', '<', $deletedAt)
            ->has('previewUploads')
            ->get();

        foreach ($projects as $project) {
            $this->project->delete($project);
        }

        $this->info($projects->count() . ' projects permanently removed');
    }
}
