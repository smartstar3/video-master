<?php namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use MotionArray\Models\User;
use MotionArray\Repositories\SubmissionRepository;
use Exception;

class DeleteProductsForSeller extends Command
{
    protected $signature = 'motionarray-onetime:delete-products-for-seller 
                            {seller-id : The ID of the selle whom products will be removed}';

    protected $description = 'Delete all approved products skipping the 30 days waiting time';

    protected $submission;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SubmissionRepository $submission)
    {
        $this->submission = $submission;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sellerId = intval($this->argument('seller-id'));

        $seller = User::find($sellerId);

        if (!$seller) {
            throw new Exception('Seller not found on DB');
        }

        $submissions = $this->submission->getSubmissionsBySeller($seller);

        $count = 0;

        foreach ($submissions as $submission) {
            if (!$submission->product->owned_by_ma) {
                $this->submission->delete($submission);
                $count++;
            }
        }

        $this->info("{$count} submissions deleted.");
    }
}
