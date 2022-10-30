<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Models\Product;
use Carbon\Carbon;

class CleanupTrashedProductDownloads extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:cleanup-trashed-product-downloads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete downloads associated with deleted products';

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
        $this->info('Cleaning up trashed product downloads');

        $trashedProducts = Product::onlyTrashed()->get();

        foreach ($trashedProducts as $product) {
            $this->info('Deleteing downloads for: ' . $product->name);
            $product->downloads()->delete();
        }

        $this->info('Finished');
    }
}
