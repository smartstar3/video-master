<?php

namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use MotionArray\Models\ProductSearchExclusion;
use MotionArray\Repositories\Products\ProductRepository;

class UndoSearchExclusions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motionarray-onetime:undo-search-exclusions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Include all products from search exclusions again';

    /**
     * @var ProductRepository
     */
    protected $productRepo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $productIds = ProductSearchExclusion::pluck('product_id');

        $attributes = ['excluded' => false];

        foreach ($productIds as $productId) {
            $this->productRepo->update($productId, $attributes);
        }
    }
}
