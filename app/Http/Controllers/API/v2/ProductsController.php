<?php

namespace MotionArray\Http\Controllers\API\v2;

use AWS;
use Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use MotionArray\Http\Requests\Products\StoreProductsRequest;
use MotionArray\Jobs\CheckPreviewEncodingProgress;
use MotionArray\Models\Category;
use MotionArray\Models\Compression;
use MotionArray\Models\Fps;
use MotionArray\Models\Product;
use MotionArray\Models\Resolution;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Repositories\SubmissionRepository;
use Response;

class ProductsController extends Controller
{
    protected $submissionRepo;
    protected $productRepo;
    protected $category;
    protected $encoder;

    public function __construct(
        ProductRepository $productRepository,
        SubmissionRepository $submissionRepository,
        Category $category
    )
    {
        $this->encoder = App::make('MotionArray\Services\Encoding\EncodingInterface');
        $this->submissionRepo = $submissionRepository;
        $this->productRepo = $productRepository;
        $this->category = $category;
    }

    /**
     * Store a newly created product.
     *
     * @param StoreProductsRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductsRequest $request)
    {
        $errors = [];

        /**
         * @var bool When true, locate FPS, Resolution and Compression by encoder slugs instead of MA slugs
         */
        $useEncoderSlugs = (bool)$request->get('useEncoderSlugs');

        if (!Auth::user()->confirmed) {
            $response = [
                'success' => false,
                'state' => 'error',
                'error' => 'Email is not confirmed'
            ];
            return Response::json($response, 400);
        }

        $inputs['seller_id'] = Auth::user()->id;
        $inputs['music_id'] = null;
        $inputs['credit_seller'] = true;
        $inputs['category_id[]'] = 2;
        $inputs['category_2_sub_category_id[]'] = [];
        $inputs['name'] = $request->product['name'];
        $inputs['meta_description'] = '';
        $inputs['description'] = '';
        $inputs['free'] = false;
        $inputs['owned_by_ma'] = false;
        $inputs['unlimited'] = false;
        $inputs['track_durations'] = null;
        $inputs['music_url'] = null;

        if (!empty($request->product['compression'])) {
            if ($useEncoderSlugs) {
                $compression = Compression::whereHas('ffmpegSlugs', function ($query) use ($request) {
                    $query->where('slug', '=', $request->product['compression']);
                })->first();
            } else {
                $compression = Compression::whereSlug($request->product['compression'])->first();
            }

            if($compression) {
                $inputs['compression_id[]'] = $compression->id; //@todo refactor to remove '[]'. ProductRepository currently requires it in this format for unknown reasons.
            } else {
                array_push($errors, 'The compression is not correct');
            }
        }

        if (!empty($request->product['fps'])) {
            if ($useEncoderSlugs) {
                $fps = Fps::whereHas('ffmpegSlugs', function ($query) use ($request) {
                    $query->where('slug', '=', $request->product['fps']);
                })->first();
            } else {
                $fps = Fps::whereSlug($request->product['fps'])->first();
            }

            if($fps) {
                $inputs['fps_id[]'] = $fps->id; //@todo refactor to remove '[]'. ProductRepository currently requires it in this format for unknown reasons.
            } else {
                array_push($errors, 'The fps is not correct');
            }
        }
        if (!empty($request->product['resolution'])) {
            if ($useEncoderSlugs) {
                $resolution = Resolution::whereHas('ffmpegSlug', function ($query) use ($request) {
                    $query->where('slug', '=', $request->product['resolution']);
                })->first();
            } else {
                $resolution = Resolution::whereSlug($request->product['resolution'])->first();
            }

            if($resolution) {
                $inputs['resolution_id[]'] = $resolution->id; //@todo refactor to remove '[]'. ProductRepository currently requires it in this format for unknown reasons.
            } else {
                array_push($errors, 'The resolution is not correct');
            }
        }

        if(!count($errors)) {
            $product = $this->productRepo->make($inputs);

            if ($product) {
                $submission = $this->submissionRepo->create($product, Auth::user());
                $submission = $this->submissionRepo->findById($submission->id);
                $submission->product->aws = $submission->product->getAwsAttribute();

                return new JsonResponse([
                    'message' => "Product created",
                    'product' => new \MotionArray\Http\Resources\Product($product)
                ], 201);
            }
        } else {
            $errors = $this->productRepo->errors;
        }

        return new JsonResponse([
            'message' => "Could not create product",
            'errors' => $errors
        ], 422);
    }

    /**
     * Store a newly created product.
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request)
    {
        if (!empty($request->product['preview_url'])) {
            $previewUrlParts = explode('/', $request->product['preview_url']);
            $previewFileNameParts = explode('.', array_pop($previewUrlParts));
            $previewExtension = array_pop($previewFileNameParts);
            $previewFileName = array_pop($previewFileNameParts);
            $inputs['preview_file_path'] = $request->product['preview_url'];
            $inputs['preview_extension'] = $previewExtension;
            $inputs['preview_filename'] = $previewFileName;
        }
        if (!empty($request->product['package_url'])) {
            $parsedUrl = parse_url($request->product['package_url']);
            $tempBucket = rtrim($parsedUrl['host'], '.s3.amazonaws.com');
            $tempFileKey = ltrim($parsedUrl['path'], '/');
            $inputs['package_file_path'] = $request->product['package_url'];
            $inputs['package_extension'] = 'zip';
            $inputs['package_filename'] = "motion-array-{$id}";
            $inputs['size'] = $this->getRemoteFileSize($request->product['package_url']);
            $s3 = AWS::get('s3');
            $s3->copyObject([
                'Bucket' => Config::get('aws.packages_bucket'),
                'Key' => "{$inputs['package_filename']}.{$inputs['package_extension']}",
                'CopySource' => "{$tempBucket}/{$tempFileKey}",
            ]);
        }
        $inputs['event_code_id'] = 2;
        $inputs['owned_by_ma'] = false;

        $product = $this->productRepo->update($id, $inputs);

        if ($product) {
            $response = $product->toArray();
            $response['downloads'] = $product->downloads()->count();
            $response['seller'] = $product->seller()->first()->toArray();
            $response['category'] = $product->category()->first()->toArray();
            $response['sub_categories'] = $product->subCategories()->get()->toArray();
            $response['compressions'] = $product->compressions()->get()->toArray();
            $response['formats'] = $product->formats()->get()->toArray();
            $response['resolutions'] = $product->resolutions()->get()->toArray();
            $response['versions'] = $product->versions()->get()->toArray();
            $response['bpms'] = $product->bpms()->get()->toArray();
            $response['fpss'] = $product->fpss()->get()->toArray();
            $response['sample_rates'] = $product->sampleRates()->get()->toArray();
            $response['plugins'] = $product->plugins()->get()->toArray();
            $response['previews'] = $product->activePreviewFiles();
            $response['music'] = $product->music()->get()->toArray();
            $response['tags'] = $product->tags()->get()->toArray();

            $preview_type = $response['category']['preview_type'];
            $previewPolicy = $product->generateAWSPolicy($preview_type);
            $response['preview'] = [
                'bucket' => $product->getBucket($preview_type),
                'awsKey' => $product->getAWSKey(),
                'awsPolicy' => $previewPolicy,
                'awsSignature' => $product->generateAWSSignature($previewPolicy),
                'bucketKey' => $product->getBucketKey($preview_type),
                'newFilename' => $product->generateFilename($product->previewPrefix . $product->id),
            ];

            $packagePolicy = $product->generateAWSPolicy('package');
            $response['package'] = [
                'bucket' => $product->getBucket('package'),
                'awsKey' => $product->getAWSKey(),
                'awsPolicy' => $packagePolicy,
                'awsSignature' => $product->generateAWSSignature($packagePolicy),
                'bucketKey' => $product->getBucketKey('package'),
                'newFilename' => $product->generateFilename($product->packagePrefix . $product->id),
            ];
            $response['entry']['preview_type'] = $product->preview_type;

            CheckPreviewEncodingProgress::dispatch($product);

            return new JsonResponse([
                'message' => "Products updated",
                'product' => $product
            ], 200);
        }

        return new JsonResponse([
            'message' => "Products update failed",
            'errors' => $this->productRepo->errors,
            'product' => \MotionArray\Http\Resources\Product::collection($product)
        ], 400);
    }

    function getRemoteFileSize($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        return $size;
    }

    public function getEncodingProgress($id)
    {
        $product = Product::whereId($id)->first();

        if ($product) {
            $preview = $product->activePreview;

            $output = $preview->outputs()->first();

            if (!is_null($output)) {
                $response = $this->encoder->getJobProgress($output->job_id);
                return Response::json($response);
            } elseif ($preview->encoding_status_id == 8) {
                return Response::json(['progress' => 100, 'state' => 'finished']);
            }
        }

        return Response::json(['progress' => 0, 'state' => '']);
    }
}
