<?php namespace MotionArray\Http\Controllers\API;

use MotionArray\Models\Traits\Uploadable;
use MotionArray\Repositories\PageRepository;
use MotionArray\Repositories\ProjectRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Events\Encoder\EncondingCancelled;
use MotionArray\Models\StaticData\EncodingStatuses;
use Response;
use App;

class OutputsController extends BaseController
{
    protected $encoder;

    protected $product;

    protected $page;

    protected $project;

    public function __construct(ProductRepository $product, PageRepository $page, ProjectRepository $project)
    {
        $this->encoder = App::make('MotionArray\Services\Encoding\EncodingInterface');
        $this->product = $product;
        $this->page = $page;
        $this->project = $project;
    }

    public function getUploadRules()
    {
        $entry = $this->page->getPageByURI('upload-rules');

        return [
            "uploadRules" => $entry->body->getRawContent()
        ];
    }

    /**
     * Wrapper method for EncodingInterface getOutputProgress()
     *
     * @param  integer $output_id
     *
     * @return json
     */
    public function getOutputProgress($output_id)
    {
        return Response::json($this->encoder->getOutputProgress($output_id));
    }


    /**
     * Wrapper method for EncodingInterface getOutputDetails()
     *
     * @param  integer $output_id
     *
     * @return json
     */
    public function getOutputDetails($output_id)
    {
        return Response::json($this->encoder->getOutputDetails($output_id));
    }


    /**
     * Wrapper method for EncodingInterface getJobProgress()
     *
     * @param $job_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJobProgress($job_id)
    {
        return Response::json($this->encoder->getJobProgress($job_id));
    }


    /**
     * Wrapper method for EncodingInterface getJobDetails()
     *
     * @param  integer $output_id
     *
     * @return json
     */
    public function getJobDetails($job_id)
    {
        return Response::json($this->encoder->getJobDetails($job_id));
    }


    /**
     * Get job progress by product id
     *
     * @param integer $product_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJobProgressByProduct($product_id)
    {
        $product = $this->product->findById($product_id);

        if ($product && $previewUpload = $product->activePreview) {
            $output = $previewUpload->outputs()->first();

            if ($output !== null) {
                return $this->getJobProgress($output->job_id);
            } elseif ($previewUpload->encoding_status_id == EncodingStatuses::FINISHED_ID) {
                return Response::json(['progress' => 100, 'state' => 'finished']);
            }
        }

        return Response::json(['progress' => 0, 'state' => '']);
    }


    /**
     * Get job details by product id
     *
     * @param  integer $product_id
     *
     * @return json
     */
    public function getJobDetailsByProduct($product_id)
    {
        $product = $this->product->findById($product_id);

        if ($product) {
            $preview = $product->activePreview;

            $output = $preview->outputs()->first();

            if (!is_null($output)) {
                return $this->getJobDetails($output->job_id);
            }
        }

        return 'No job found.';
    }

    public function getJobProgressByProject($project_id)
    {
        $project = $this->project->findById($project_id);

        if ($project) {
            $previewUpload = $project->activePreview;

            $output = $previewUpload->outputs()->first();

            if (!is_null($output)) {
                return $this->getJobProgress($output->job_id);
            } elseif ($previewUpload->encoding_status_id == 8) {
                return Response::json(['progress' => 100, 'state' => 'finished']);
            }
        }

        return Response::json(['progress' => 0, 'state' => '']);
    }

    public function getJobDetailsByProject($project_id)
    {
        $project = $this->project->findById($project_id);

        if ($project) {
            $preview = $project->activePreview;

            $output = $preview->outputs()->first();

            if (!is_null($output)) {
                return $this->getJobDetails($output->job_id);
            }
        }

        return 'No job found.';
    }

    /**
     * Cancel job by product id
     *
     * @param  integer $product_id
     *
     * @return json
     */
    public function cancelJobByProduct($product_id)
    {
        $product = $this->product->findById($product_id);

        return $this->cancelJob($product);
    }

    public function cancelJobByProject($project_id)
    {
        $project = $this->project->findById($project_id);

        return $this->cancelJob($project);
    }

    private function cancelJob(Uploadable $uploadable)
    {
        $previewUpload = $uploadable->activePreview;
        $outputs = $previewUpload->outputs()->get();
        $allJobCancelled = true;

        foreach ($outputs as $output) {
            $jobId = $output->job_id;

            if (!is_null($output)) {
                \syncEvent(EncondingCancelled::class, [$previewUpload, true, null]);

                $jobCancelled = $this->encoder->cancelJob($jobId);

                if (!$jobCancelled) {
                    $allJobCancelled = false;
                }
            }
        }

        return $allJobCancelled;
    }
}
