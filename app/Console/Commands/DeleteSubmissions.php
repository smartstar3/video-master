<?php

namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Models\Submission;
use App;
class DeleteSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motionarray:delete-requested-submissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete submissions requested to be deleted by author, after a certain time.';

    /**
     * Delete the submission requested for deletion after this number of days
     *
     * Is confirmed by the user in terms of service, do not modify lightly.
     *
     * @var integer
     */
    public $delete_after_days = 30;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $submissionRepo = App::make("MotionArray\Repositories\SubmissionRepository");

        $delete_after = now()->subDays($this->delete_after_days);
        $submissions = Submission::where('delete_requested_at', '<=', $delete_after)->get();

        $count = 0;
        foreach ($submissions as $submission) {
            $submissionRepo->delete($submission);
            $count++;
        }

        $this->info("{$count} submissions deleted.");
    }
}
