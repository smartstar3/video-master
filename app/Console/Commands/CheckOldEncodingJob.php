<?php namespace MotionArray\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MotionArray\Models\Output;
use MotionArray\Repositories\PreviewUploadRepository;

class CheckOldEncodingJob extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:check-old-encoding-job {--minutes=20} {--limit=20}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks old encoding jobs to save or delete them';

    protected $previewUpload;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PreviewUploadRepository $previewUpload)
    {
        $this->previewUpload = $previewUpload;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $minutes = intval($this->option('minutes'));

        $limit = intval($this->option('limit'));

        $query = Output::where('created_at', '>', Carbon::now()->subMonth());

        if ($minutes) {
            $query = $query->where('created_at', '<', Carbon::now()->subMinutes($minutes));
        }

        $outputs = $query->groupBy('job_id')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();

        if (!$outputs->count()) {
            $this->info('No ouputs');

            return;
        }

        $progressBar = $this->output->createProgressBar($outputs->count());

        foreach ($outputs as $output) {
            try {
                $this->previewUpload->storeJobPreviews($output);
            } catch (\Exception $e) {
                $this->info($e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
