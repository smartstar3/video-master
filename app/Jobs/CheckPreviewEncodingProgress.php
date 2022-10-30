<?php

namespace MotionArray\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\App;
use MotionArray\Models\Product;
use MotionArray\Repositories\Products\ProductRepository;

class CheckPreviewEncodingProgress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const SECONDS_DELAY = 10;

    /**
     * @var \MotionArray\Services\Encoding\EncodingInterface
     */
    protected $encoder;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ProductRepository
     */
    protected $productRepo;

    /**
     * Create a new job instance.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->encoder = App::make('MotionArray\Services\Encoding\EncodingInterface');
        $this->productRepo = App::make('MotionArray\Repositories\Products\ProductRepository');
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $preview = $this->product->activePreview;

        $output = $preview->outputs()->first();

        if (!is_null($output)) {
            $response = $this->encoder->getJobProgress($output->job_id);
            if ($response->state == "finished") {
                $this->storeVideoPreviewFiles();
            } elseif ($response->state == "processing") {
                Self::dispatch($this->product)->delay(now()->addSeconds(Self::SECONDS_DELAY));
            }
        }
    }

    protected function storeVideoPreviewFiles()
    {
        $inputs['encoding_status_id'] = 3;
        $inputs['event_code_id'] = 3;
        $inputs['owned_by_ma'] = false;

        $product = $this->productRepo->update($this->product->id, $inputs);

        if ($product) {
            return true;
        }

        return false;
    }
}
