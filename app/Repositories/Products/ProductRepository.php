<?php namespace MotionArray\Repositories\Products;

use Aws\S3\S3Client;
use MotionArray\Events\ProductUnpublished;
use MotionArray\Helpers\Helpers;
use MotionArray\Jobs\Product\GenerateProductImageMetaJob;
use MotionArray\Services\Aws\CloudFront\UrlSigner;
use MotionArray\Helpers\Imgix;
use MotionArray\Jobs\SendProductToAlgolia;
use MotionArray\Models\DebugLog;
use MotionArray\Models\Download;
use MotionArray\Models\PreviewFile;
use Carbon\Carbon;
use Config;
use AWS;
use Illuminate\Support\Collection;
use MotionArray\Models\Product;
use MotionArray\Models\ProductSearchExclusion;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\ProductChangeOptions;
use MotionArray\Models\StaticData\SubmissionStatuses;
use MotionArray\Models\SubmissionStatus;
use MotionArray\Models\Role;
use MotionArray\Models\Category;
use MotionArray\Models\SubCategory;
use MotionArray\Models\Compression;
use MotionArray\Models\Format;
use MotionArray\Models\ProductPlugin;
use MotionArray\Models\Resolution;
use MotionArray\Models\User;
use MotionArray\Models\Version;
use MotionArray\Models\Bpm;
use MotionArray\Models\Fps;
use MotionArray\Models\SampleRate;
use MotionArray\Models\Tag;
use MotionArray\Models\ProductChangeOption;
use MotionArray\Repositories\AutoDescriptionRepository;
use MotionArray\Repositories\UploadableRepository;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;
use MotionArray\Models\StaticData\EventCodes;

class ProductRepository extends UploadableRepository
{
    use ProductBrowseBuilder;

    public $errors = [];

    public $products_per_page = 24;

    protected $model;
    protected $autoDescriptionRepository;

    protected $categoriesWithRelationsHelper;

    public function __construct(
        Product $product,
        AutoDescriptionRepository $autoDescriptionRepository,
        CategoriesWithRelationsHelper $categoriesWithRelationsHelper
    )
    {
        $this->model = $product;
        $this->autoDescriptionRepository = $autoDescriptionRepository;
        $this->categoriesWithRelationsHelper = $categoriesWithRelationsHelper;
    }

    public function allProducts($order_by = 'created_at', $order = 'desc', $published = null)
    {
        if ($published) {
            return Product::where('product_status_id', '=', 1)
                ->orderBy($order_by, $order)
                ->get();
        }

        return Product::orderBy($order_by, $order)->get();
    }

    public function weeklyProductsDateRange()
    {
        Carbon::setWeekEndsAt(Carbon::SUNDAY);
        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $now = Carbon::now();
        $startDate = $now->copy()->subWeek()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        while ($endDate->diffInDays($now) < 7) {
            $startDate->subWeek();
            $endDate->subWeek();
        }

        return [$startDate, $endDate];
    }

    public function weeklyProducts()
    {
        list($startDate, $endDate) = $this->weeklyProductsDateRange();

        $categoryIds = [5, 8, 6, 1, 4, 3];

        $products = new \Illuminate\Database\Eloquent\Collection;

        foreach ($categoryIds as $category) {

            if (in_array($category, [8, 4, 3])) {
                $limit = 1;
            } else {
                $limit = 3;
            }

            $categoryProducts = Product::where('product_status_id', '=', 1)
                ->where('free', '=', 0)
                ->where('category_id', '=', $category)
                ->whereNull('deleted_at')
                ->whereBetween('published_at', [$startDate, $endDate])
                ->withCount(['downloads' => function ($query) {
                    $query = Download::premiumScope($query);
                    $query->whereRaw('first_downloaded_at between products.published_at AND DATE_ADD(products.published_at,INTERVAL 1 WEEK)');
                }])
                ->orderBy('downloads_count', 'desc')
                ->take($limit)
                ->get();

            // Reverse order in template
            // (Bigger pic is last)
            if (in_array($category, [1, 6])) {
                $categoryProducts = $categoryProducts->reverse();
            }

            $products = $products->merge($categoryProducts);
        }

        return $products;
    }

    public function allSellers()
    {
        $role = Role::find(3); // Seller
        $sellers = $role->users()
            ->orderBy('company_name', 'asc')
            ->orderBy('firstname', 'asc')
            ->get();

        foreach ($sellers as $seller) {
            $seller->name = !is_null($seller->company_name) ? $seller->company_name : $seller->firstname . " " . $seller->lastname;
        }

        return $sellers;
    }

    public function allSubCategories()
    {
        return SubCategory::orderBy('name', 'asc')->get();
    }

    public function allCompressions()
    {
        return Compression::orderBy('name', 'asc')->get();
    }

    public function allFormats()
    {
        return Format::orderBy('name', 'asc')->get();
    }

    public function allPlugins()
    {
        return ProductPlugin::orderBy('name', 'asc')->get();
    }

    public function allResolutions()
    {
        return Resolution::orderBy('order', 'asc')->get();
    }

    public function allVersions()
    {
        return Version::with('categories')->orderBy('name', 'asc')->get();
    }

    public function allBpm()
    {
        return Bpm::orderBy('name', 'asc')->get();
    }

    public function allFps()
    {
        return Fps::orderBy('name', 'asc')->get();
    }

    public function allSampleRates()
    {
        return SampleRate::orderBy('name', 'asc')->get();
    }

    public function allSpecs($user = null)
    {
        return [
            'compressions' => $this->allCompressions(),
            'formats' => $this->allFormats(),
            'plugins' => $this->allPlugins(),
            'resolutions' => $this->allResolutions(),
            'versions' => $this->allVersions(),
            'categories' => $this->categoriesWithRelationsHelper->categoriesWithVersions($user),
            'bpm' => $this->allBpm(),
            'fps' => $this->allFps(),
            'sample_rates' => $this->allSampleRates()
        ];
    }

    public function getOldAudio($limit = 500)
    {
        return $this->oldAudioQuery()->paginate($limit);
    }

    public function countOldAudio()
    {
        return $this->oldAudioQuery()->count();
    }

    private function oldAudioQuery()
    {
        $stock_music = Category::find(4);

        return $stock_music->products()->where('placeholder_id', 0)
            ->where('product_status_id', '=', 1)
            ->where('encoding_status_id', '=', 8)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Retrieves and assoc array of music products.
     *
     * @return Array
     */
    public function getStockMusic()
    {
        $stock_music = Category::find(4);

        $assoc_music[0] = 'No';

        foreach ($stock_music->products()->orderBy('products.name')->get() as $product) {
            $assoc_music[$product->id] = $product->name . ' (' . $product->slug . ')';
        }

        return $assoc_music;
    }

    /**
     * Save product and all relationships
     *
     * @param array $attributes
     *
     * @return boolean
     */
    public function make(array $attributes)
    {
        // Set attributes
        $product = new Product;

        $product->seller_id = (int)$attributes['seller_id'];
        $product->music_id = (int)$attributes['music_id'] === 0 ? null : (int)$attributes['music_id'];
        $product->credit_seller = (int)$attributes['credit_seller'];
        $product->category_id = (int)$attributes['category_id[]'];
        $product->product_status_id = 3; // Processing
        $product->event_code_id = 1; // Ready
        $product->name = ucwords($attributes['name']);
        $product->meta_description = $attributes['meta_description'];
        $product->description = $attributes['description'];

        if (isset($attributes['free'])) {
            $product->free = (int)$attributes['free'];
        }

        if (isset($attributes['owned_by_ma'])) {
            $product->owned_by_ma = (int)$attributes['owned_by_ma'];
        }

        if (isset($attributes['is_editorial_use'])) {
            $product->is_editorial_use = (bool)$attributes['is_editorial_use'];
        }

        if (isset($attributes['track_durations'])) {
            $product->track_durations = $attributes['track_durations'];
        }

        if (isset($attributes['music_url'])) {
            $product->setMusic($attributes['music_url']);
        }

        // Save the product
        if ($product->save()) {
            // Set the slug
            $product->slug = $product->generateSlug($product->name) . "-" . $product->id;

            // Set audio placeholder
            // TODO: refactor to make more readable. Create accessor on model for is_audio to use in this conditional
            if ($product->category_id == 4 || $product->category_id == 7) {
                $product->audio_placeholder = $this->getRandomAudioPlaceholder();
            }
            $product->save();

            $this->storeProductSpecs($attributes, $product);
            $product->fresh();

            // Generate a description for the product if it's empty.
            // We currently only generate descriptions for stock footage.
            if ($product->category_id == Categories::STOCK_VIDEO_ID && empty($product->description)) {
                $product->description = $this->autoDescriptionRepository->generateStockVideoDescription($product);
            }
            $product->save();
            $product->fresh();

            return $product;
        }

        // Set any validation errors
        $this->errors = $product->errors;

        return false;
    }

    /**
     * Update product and any relationships
     *
     * @param integer $id Product ID
     * @param array $attributes
     *
     * @return boolean
     */
    public function update($id, array $attributes)
    {
        $product = Product::find($id);

        // Set event code comparator
        $current_event_code = $product->event_code_id;

        // Set attributes
        if (isset($attributes['seller_id'])) $product->seller_id = (int)$attributes['seller_id'];
        if (isset($attributes['music_id'])) $product->music_id = (int)$attributes['music_id'] === 0 ? null : (int)$attributes['music_id'];
        if (isset($attributes['credit_seller'])) $product->credit_seller = (int)$attributes['credit_seller'];
        if (isset($attributes['category_id[]'])) $product->category_id = (int)$attributes['category_id[]'];
        //if (isset($attributes['placeholder_id'])) $product->placeholder_id = $attributes['placeholder_id'];

        if (isset($attributes['excluded']) && $attributes['excluded']) {
            $product->productSearchExclusion()->create();
        } else {
            $product->productSearchExclusion()->delete();
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            if (isset($attributes['weight'])) $product->weight = (int)$attributes['weight'];
        }

        if (isset($attributes['published_at']) && $attributes['published_at'] && $product->published_at == null) {
            if ($attributes['published_at'] instanceof \DateTimeInterface) {
                $product->published_at = $attributes['published_at'];
            } else {
                $product->published_at = Carbon::createFromFormat('m/d/Y', $attributes['published_at']);
            }
        }

        if (isset($attributes['product_status_id'])) {
            $product->product_status_id = (int)$attributes['product_status_id'];

            if ($product->product_status_id == 1 && $product->published_at == null) {
                $product->published_at = Carbon::now();
            }
        }

        if (isset($attributes['event_code_id'])) $product->event_code_id = (int)$attributes['event_code_id'];

        if (isset($attributes['name'])) {
            if ($product->name != $attributes['name']) {
                // Name has changed so we need to update the slug.
                $product->slug = $product->generateSlug($attributes['name']) . "-" . $product->id;

                $this->setChangeOptions($product, ProductChangeOptions::PRODUCT_NAME_CHANGED_ID);
            }

            $product->name = $attributes['name'];
        }

        if (isset($attributes['description'])) {
            if ($product->description != $attributes['description']) {

                $product->description = $attributes['description'];

                $this->setChangeOptions($product, ProductChangeOptions::DESCRIPTION_CHANGED_ID);
            }
        }

        if (isset($attributes['meta_description'])) {
            if ($product->meta_description != $attributes['meta_description']) {

                $product->meta_description = $attributes['meta_description'];

                $this->setChangeOptions($product, ProductChangeOptions::META_DESCRIPTION_CHANGED_ID);
            }
        }

        $packageHasBeenChanged = false;
        if (isset($attributes['package_file_path'])) {
            if ($product->package_file_path != $attributes['package_file_path']) {

                $product->package_file_path = $attributes['package_file_path'];

                $this->setChangeOptions($product, ProductChangeOptions::PACKAGE_CHANGED_ID);

                $packageHasBeenChanged = true;
            }
        }

        if (isset($attributes['package_filename'])) $product->package_filename = $attributes['package_filename'];
        if (isset($attributes['package_extension'])) $product->package_extension = $attributes['package_extension'];
        if (isset($attributes['size'])) $product->size = $attributes['size'];

        if (isset($attributes['audio_placeholder'])) {
            if ($product->audio_placeholder != $attributes['audio_placeholder']) {

                $product->audio_placeholder = $attributes['audio_placeholder'];

                $this->setChangeOptions($product, ProductChangeOptions::AUDIO_PLACEHOLDER_CHANGED_ID);
            }
        }

        if (isset($attributes['placeholder_url'])) {
            $s3 = AWS::get('s3');

            $new_filename_str = str_random(10);
            $filename = 'preview-' . $product->id . $new_filename_str . '.png';

            $response = $s3->putObject([
                'Bucket' => Config::get('aws.previews_bucket'),
                'Body' => file_get_contents($attributes['placeholder_url']),
                'Key' => $filename,
                'ACL' => 'public-read',
                'ContentEncoding' => 'base64',
                'ContentType' => 'image/png',
                'CacheControl' => 'public, max-age=31104000',
                'Expires' => date(DATE_RFC2822, strtotime("+360 days"))
            ]);

            if (isset($response['ObjectURL'])) {
                $preview = $product->activePreview;

                $placeholder = new PreviewFile();

                $placeholder->url = $response['ObjectURL'];
                $placeholder->label = 'placeholder high';

                $placeholder->previewUpload()->associate($preview);
                $placeholder->save();

                $product->audio_placeholder = $response['ObjectURL'];

                $preview->placeholder_id = $placeholder->id;
                $preview->save();

                // save the status because audio Placeholder is changed here.
                $this->setChangeOptions($product, ProductChangeOptions::AUDIO_PLACEHOLDER_CHANGED_ID);
            }
        }

        if (isset($attributes['free'])) {
            if ($product->free != (int)$attributes['free']) {

                $product->free = (int)$attributes['free'];

                $this->setChangeOptions($product, ProductChangeOptions::FREE_CHANGED_ID);
            }
        }

        if (isset($attributes['is_editorial_use'])) {
            $product->is_editorial_use = (bool)$attributes['is_editorial_use'];

            if ($product->isDirty('is_editorial_use')) {
                $this->setChangeOptions($product, ProductChangeOptions::EDITORIAL_USE_CHANGED_ID);
            }
        }

        if (isset($attributes['owned_by_ma'])) {
            $product->owned_by_ma = (int)$attributes['owned_by_ma'];
        }

        if (isset($attributes['track_durations'])) {
            if ($product->track_durations != $attributes['track_durations']) {

                $product->track_durations = $attributes['track_durations'];

                $this->setChangeOptions($product, ProductChangeOptions::TRACK_DURATIONS_CHANGED_ID);
            }
        }

        if (isset($attributes['music_url'])) {
            if ($product->setMusic($attributes['music_url'])) {
                $this->setChangeOptions($product, ProductChangeOptions::MUSIC_URL_CHANGED_ID);
            }
        }

        if (isset($attributes['placeholder_id'])) {
            $preview = $product->activePreview;

            if ($preview && $attributes['placeholder_id'] != $preview->placeholder_id) {
                $this->setChangeOptions($product, ProductChangeOptions::PLACEHOLDER_URL_CHANGED_ID);
            }
        }

        $this->upsertPreview($product, $attributes);
        if (isset($attributes['model_release_url']) && isset($attributes['model_release_filename'])) {
            $this->saveModelRelease($product, $attributes);
        }

        if ($product->save()) {
            if ($packageHasBeenChanged === true) {
                $isImage = $product->isImage();

                if ($isImage === true) {
                    dispatch((new GenerateProductImageMetaJob($product))->onQueue('high'));
                    //TODO: change queue name and listen queue "->onQueue('image-processing')"
                }
            }

            if ($product->isPublished() && !$product->excluded) {
                dispatch((new SendProductToAlgolia($product))->onQueue('high'));
            }

            if ($current_event_code !== $product->event_code_id) {
                if ($product->event_code_id == EventCodes::DELETE_PREVIEW_ID) {
                    $this->setChangeOptions($product, ProductChangeOptions::PREVIEW_CHANGED_ID);
                } elseif ($product->event_code_id == EventCodes::DELETE_PACKAGE_ID) {
                    $this->setChangeOptions($product, ProductChangeOptions::PACKAGE_CHANGED_ID);
                }

                $this->eventHandler($product);
            }

            $this->storeProductSpecs($attributes, $product);

            $product->fresh();

            return $product;
        }

        // Set any validation errors
        $this->errors = $product->errors;

        return false;
    }

    /**
     * Remove the music association for all products related to the
     * specified product.
     *
     * @param Product $product
     */
    public function destroyMusicAssociation($product)
    {
        $results = Product::where('music_id', '=', $product->id)->get();

        foreach ($results as $result) {
            $result->music_id = null;
            $result->save();
        }
    }

    /**
     * findOrFail wrapper method
     *
     * @param integer $id
     *
     * @return obbject     Product
     */
    public function find($id)
    {
        return Product::withTrashed()->find($id);
    }

    /**
     * @param $productSlug
     * @param bool $category_id
     * @param bool $fallbackToId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed|Product|object|null
     */
    public function findBySlug($productSlug, $category_id = false, $fallbackToId = false)
    {
        $query = Product::withTrashed()->where("slug", "=", $productSlug);

        if ($category_id) {
            $query->where("category_id", "=", $category_id);
        } else {
            $query->where("product_status_id", "=", 1);
        }

        $product = $query->first();

        if (!$product && $fallbackToId) {
            preg_match('/[0-9]+$/i', $productSlug, $matches);

            if (isset($matches[0])) {
                $id = $matches[0];

                $product = $this->findById($id);
            }
        }

        return $product;
    }

    public function filterByProcessing()
    {
        return Product::where('product_status_id', '=', 3)->limit(50)->get();
    }

    public function filterByUnpublished()
    {
        return Product::where('product_status_id', '=', 2)->get();
    }

    public function getProductsCountCreatedInLast($days, $category_id = null, $seller_id = null)
    {
        $period = date('Y-m-d 23:59:59', time() - $days * 86400);

        $query = Product::where("product_status_id", "=", 1)
            ->where("published_at", ">", $period);

        if ($category_id) {
            $query = $query->where("category_id", "=", $category_id);
        }

        if ($seller_id) {
            $query = $query->where("seller_id", "=", $seller_id);
        }

        if ($category_id == "free") {
            $query = $query->where("free", "=", 1);
        }

        return $query->remember(5)->count();
    }

    public function totalProductCount()
    {
        $query = Product::published();

        return $query->remember(5)->count();
    }

    public function relatedProducts(Product $product)
    {
        /**
         * Create search string from product tags
         */
        $q = "";
        foreach ($product->tags()->take(3)->get() as $tag) {
            $q .= $tag->name . " ";
        }
        $q = trim($q);

        $related_products = $this->getSearchResults($q, $product->category->slug, $product->id);

        /**
         * Check for related products
         */
        if ($related_products) {

            /**
             * Select first products
             */
            $products = $related_products;

            /**
             * Check that we have at least 8 products, otherwise
             * tack on the shortfall from the latest products in the category
             */
            if ($products->count() < 8) {
                $category_products = Product::where("product_status_id", "=", 1)
                    ->where("category_id", "=", $product->category_id)
                    ->orderBy("published_at", "desc")
                    ->take(8 - $products->count())
                    ->get();

                $category_products->sort(function () {
                    return rand(0, 2) - 1;
                });

                return $products->merge($category_products);
            }

            return $products->slice(0, 8);
        }

        /**
         * Fallback to retrieving products from the same category
         */
        $category_products = Product::where("product_status_id", "=", 1)
            ->where("category_id", "=", $product->category_id)
            ->orderBy("published_at", "desc")
            ->take(8)
            ->get();

        if ($category_products) {
            return $category_products->sort(function () {
                return rand(0, 2) - 1;
            });
        }
    }

    /**
     * Unpublish product
     *
     * @param Product $product
     */
    public function unpublish(Product $product)
    {
        $product->event_code_id = 1;
        $product->product_status_id = 2; // Unpublish
        $product->save();

        // Put the submission back to pending review if it has been approved.
        if ($product->submission && $product->submission->hasStatus('approved')) {
            $pendingStatus = SubmissionStatus::where('status', 'pending')->first();

            $submission = $product->submission;
            $submission->submission_status_id = $pendingStatus->id;
            $submission->save();
        }

        \event(ProductUnpublished::class, [$product]);
    }

    /**
     * Delete download package from storage
     *
     * @param Product $product
     * @param bool $unpublish
     */
    public function deletePackage(Product $product, $unpublish = true)
    {
        if ($product->package_file_path) {
            $s3 = AWS::get('s3');

            $s3->deleteObject([
                'Bucket' => Config::get('aws.packages_bucket'),
                'Key' => $product->getAWSFileKey()
            ]);
        }

        $product->package_file_path = "";
        $product->package_filename = "";
        $product->package_extension = "";
        $product->size = 0;
        $product->event_code_id = 1;

        if ($unpublish) {
            $this->unpublish($product);
        } else {
            $product->save();
        }
    }

    /**
     * Generate sellers list:
     *
     * [id] => [firstname lastname]
     *
     * @return array
     */
    public function sellersList()
    {
        $sellersList = [];
        $sellersList[0] = "Select a seller";

        foreach ($this->allSellers() as $seller) {
            $sellersList = array_add($sellersList, $seller->id, $seller->name);
        }

        return $sellersList;
    }

    /**
     * Collate categoriesation lists into a single array:
     *
     * [compressions]
     *     [id] => [compression]
     *     etc...
     * [formats]
     *     [id] => [format]
     *     etc...
     * etc...
     *
     * @return array
     */
    public function categorisationLists()
    {
        $categorisationLists = [];
        $categorisationLists['compressions'] = Compression::pluck('compression', 'id');
        $categorisationLists['formats'] = Format::pluck('format', 'id');
        $categorisationLists['plugins'] = ProductPlugin::pluck('plugin', 'id');
        $categorisationLists['resolutions'] = Resolution::pluck('resolution', 'id');
        $categorisationLists['versions'] = Version::pluck('version', 'id');
        $categorisationLists['bpm'] = Bpm::pluck('bpm', 'id');
        $categorisationLists['fps'] = Fps::pluck('fps', 'id');
        $categorisationLists['sampleRates'] = SampleRate::pluck('sample_rate', 'id');

        return $categorisationLists;
    }

    public function setChangeOptions($product, $option_id)
    {
        if ($product->submission && $product->submission->hasStatus('needs-work')) {
            $changeOptions = $product->productChanges()->get();

            $product->productChanges()->detach();

            $option = ProductChangeOption::find($option_id);

            if ($option) {
                $product->productChanges()->save($option);
            }

            foreach ($changeOptions as $changeOption) {
                if ($changeOption->id != $option_id) {
                    $product->productChanges()->save($changeOption);
                }
            }

            return true;
        }

        return false;
    }

    public function getSearchResults($q, $category_slug = null, $exclude_relating_product_id = 0, $page_no = 0)
    {
        if ($q) {
            /**
             * If category_slug is passed restrict results to a category
             */
            $restrict_to_category = false;
            if ($category_slug && $category_slug != "all") {
                $category = Category::where("slug", "=", $category_slug)->first();
                $restrict_to_category = true;
            }

            /**
             * Prepare query strings
             */
            $q = trim(strtolower(preg_replace('/\s([0-9])+$|\s([0-9])+\s/', " ", $q)));
            $q_parts = explode(" ", $q);

            /**
             * Check for exact matches
             */
            $query = Product::where("product_status_id", "=", 1);

            if ($restrict_to_category) {
                if ($category_slug == "free") {
                    $query = $query->where("free", "=", 1);
                } else {
                    $query = $query->where("category_id", "=", $category->id);
                }
            }

            $exact_matches = $query->where("name", "LIKE", "%$q%")
                ->orderBy("published_at", "desc")
                ->get();

            /**
             * Find products with matching tags
             */
            $results = new Collection;
            $paginated_results = new Collection;
            foreach ($q_parts as $part) {
                $tag_match = Tag::where("name", "=", $part)->first();

                if ($tag_match) {
                    if ($restrict_to_category) {
                        if ($category_slug == "free") {
                            $products = $tag_match->products()
                                ->where("products.product_status_id", "=", 1)
                                ->where("free", "=", 1)
                                ->where("products.id", "!=", $exclude_relating_product_id)
                                ->orderBy("products.published_at", "desc")
                                ->get();
                        } else {
                            $products = $tag_match->products()
                                ->where("products.product_status_id", "=", 1)
                                ->where("category_id", "=", $category->id)
                                ->where("products.id", "!=", $exclude_relating_product_id)
                                ->orderBy("products.published_at", "desc")
                                ->get();
                        }
                    } else {
                        $products = $tag_match->products()
                            ->where("products.product_status_id", "=", 1)
                            ->where("products.id", "!=", $exclude_relating_product_id)
                            ->orderBy("products.published_at", "desc")
                            ->get();
                    }

                    if ($products) {
                        $results = $results->merge($products);
                    }
                }
            }
            /**
             * Find products with matching track duration
             */
            $q_items = explode(":", $q);
            if (count($q_items) > 1) {
                $min_duration = (int)$q_items[0] * 60 + (int)$q_items[1] - 2;
                $max_duration = (int)$q_items[0] * 60 + (int)$q_items[1] + 2;
                $product_items = Product::where('track_durations', '>', '0')
                    ->where('product_status_id', '=', 1)
                    ->get();

                if ($product_items) {
                    $duration_results = [];
                    foreach ($product_items as $product_item) {
                        $duration_items = explode(',', $product_item->track_durations);
                        foreach ($duration_items as $duration_item) {
                            if ((int)explode(':', $duration_item)[0] * 60 + (int)explode(':', $duration_item)[1] >= $min_duration && (int)explode(':', $duration_item)[0] * 60 + (int)explode(':', $duration_item)[1] <= $max_duration) {
                                array_push($duration_results, $product_item);
                                break;
                            }
                        }
                    }
                    if ($duration_results) {
                        $results = $results->merge($duration_results);
                    }
                }
            }

            /**
             * Find products by beats per minute
             */
            $q_bpm_parts = explode('bpm', $q);
            if (count($q_bpm_parts) == 2 && $q_bpm_parts[1] === '') {
                $mached_bpm = Bpm::where('name', (int)$q_bpm_parts[0] . ' BPM')->first();
                if ($mached_bpm) {
                    $bpm_results = $mached_bpm->products;
                    if ($bpm_results) {
                        $results = $results->merge($bpm_results);
                    }
                }
            }

            // Products from sellers that match the query.
            $seller_results = Product::whereIn('seller_id', function ($query) use ($q) {
                $query->select('id')
                    ->from('users')
                    ->where('company_name', 'LIKE', "%$q%");
            })
                ->orderBy("published_at", "desc")
                ->where("product_status_id", "=", 1)
                ->where("id", "!=", $exclude_relating_product_id)
                ->get();

            // Merge seller results if there are any
            if ($seller_results) {
                $results = $results->merge($seller_results);
            }

            /**
             * Check for results and merge
             */
            if ($results->count()) {
                if ($exact_matches) {
                    $results = $exact_matches->merge($results);
                }
            }

            /**
             * Check for exact matches
             */
            if ($exact_matches->count()) {
                // Slice array for pagination.
                $results = $results->merge($exact_matches->all());
            }

            /**
             * Check for results and return.
             *
             * --------------------------------------------------------------------
             *
             * NOTE:
             * This approach of slicing the array for pagination is far from idea
             * when it comes to performance. Ideally we would need to build a
             * more complex SQL query that utilises JOINS to return a single
             * set of results.
             *
             * --------------------------------------------------------------------
             */
            if ($results->count()) {
                // Check for pagination.
                if ($page_no > 0) {
                    // Calculate pagination offset.
                    $pagination_offset = $this->products_per_page * ($page_no - 1);

                    // Slide the array.
                    $paginated_results = $paginated_results->merge(
                        array_slice($results->all(), $pagination_offset, $this->products_per_page)
                    );

                    // Return the results.
                    return $paginated_results;
                } else {
                    // Return all results.
                    return $results;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Get the total number of results for a search query.
     *
     * @return int
     */
    public function getSearchResultsCount($q, $category_slug = null, $exclude_relating_product_id = 0)
    {
        // TODO: Refactor to use SQL count instead to improve performace.
        $results = $this->getSearchResults($q, $category_slug, $exclude_relating_product_id, 0);

        // Check to see if there are results and return the count.
        if ($results) {
            return $results->count();
        } else {
            return 0;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFailedApprovedProducts()
    {
        $date = Carbon::now()->subMonth();

        //products.id = preview_uploads.uploadable_id
        $query = 'SELECT products.*
            FROM products
            LEFT JOIN preview_uploads ON products.active_preview_id = preview_uploads.id
            AND preview_uploads.uploadable_type = "MotionArray\\\\Models\\\\Product"
            LEFT JOIN submissions ON submissions.product_id = products.id
            LEFT JOIN users ON users.id = products.seller_id
            
            WHERE submissions.submission_status_id = 3
                AND products.published_at > "' . $date . '"
                
                AND users.disabled = 0
                AND users.deleted_at IS NULL
                AND submissions.deleted_at IS NULL
                AND products.deleted_at IS NULL
            
                AND products.product_status_id = 3
            
            ORDER BY published_at DESC';

        $products = Product::fromQuery($query);

        return $products;
    }

    public function fixEncodingErrors(Product $product)
    {
        if ($product->product_status_id != 1) {
            $product->product_status_id = 1;

            $product->save();
        }

        $preview = $product->activePreview;

        if ($preview->encoding_status_id != 8) {
            $preview->encoding_status_id = 8;

            $preview->save();
        }
    }

    public function updateAlgoliaDataForProduct($productId)
    {
        $algolia = app('MotionArray\Services\Algolia\AlgoliaClient');

        $productsArray = $this->getProductsDataForAlgolia(['id' => $productId]);

        // the product will only be in the results if it exists and is valid for display in the marketplace
        $product = collect($productsArray)->first();

        if (!$product) {
            // make sure the product is removed from algolia if this update has made it invalid
            $algolia->removeProduct($productId);
        } else {
            $algolia->sendProducts($productsArray);
        }

        return $productsArray;
    }

    /**
     * @param $options [page | limit | id]
     *
     * @return array
     */
    public function getProductsDataForAlgolia(Array $options = [])
    {
        extract(array_only($options, ['page', 'limit', 'id', 'sellerId', 'recentHours', 'kickass']));

        if (isset($page) && isset($limit)) {
            $offset = ($page - 1) * $limit;
        } elseif (isset($id)) {
            $offset = 0;
            $limit = 1;
        } else {
            throw new \Exception('getProductsDataForAlgolia error, productId or page/limit params are required');
        }

        $columns = [
            'products.id',
            'products.seller_id',
            'products.music_id',
            'name',
            'description',
            'product_status_id',
            'category_id',
            'audio_placeholder',
            'track_durations',
            'product_level_id',
            'free',
            'owned_by_ma',
            'slug',
            'active_preview_id',
            'published_at',
            'kick_ass_at',
            'is_editorial_use'
        ];

        $hideColumns = [
            'id',
            'category_id',
            'product_status_id',
            'product_level_id',
            'active_preview_id',
            'tags'
        ];

        $productsQuery = Product::query()
            ->where('product_status_id', '=', 1)
            ->whereNotIn('products.id', function ($query) {
                $query->from((new ProductSearchExclusion())->getTable())
                    ->select('product_id');
            })
            ->orderBy('published_at', 'DESC');

        if (!empty($id)) {
            $productsQuery->where('products.id', '=', $id);
        }
        if (!empty($sellerId)) {
            $productsQuery->where('products.seller_id', '=', $sellerId);
        }
        if (!empty($recentHours)) {
            $timestamp = Carbon::now()->subHours($recentHours);
            $productsQuery->where('products.updated_at', '>=', $timestamp);
        }
        if (!empty($kickass)) {
            $productsQuery->where('product_level_id', '=', 1);
        }

        $categories = Category::all();

        $products = $productsQuery
            ->join('submissions', 'submissions.product_id', '=', 'products.id')
            ->where('submissions.submission_status_id', '=', SubmissionStatuses::APPROVED_ID)
            ->with(['category' => function ($q) {
                $q->select(['id', 'name', 'display_name', 'short_name', 'preview_type', 'slug']);
            }])
            ->with(['seller' => function ($q) {
                $q->select(['id', 'company_name', 'firstname', 'lastname']);
            }])
            ->with(['subcategories' => function ($q) {
                $q->select(['name']);
            }])
            ->with(['tags' => function ($q) {
                $q->select(['name']);
            }])
            ->select($columns)
            ->limit($limit)
            ->offset($offset)
            ->get();

        $products->each(function ($product) use ($hideColumns) {
            if ($product->free || $product->unlimited) {
                $product->downloads = $product->downloads()->count();
            } else {
                $product->downloads = $product->downloads()->premium()->count();
            }

            $product->requested = (boolean)$product->requests()->count();

            $product->owned_by_ma = (boolean)$product->owned_by_ma;

            $product->free = (boolean)$product->free;

            $product->is_music = $product->isAudio();

            $product->seller_id = $product->seller_id;

            $product->previews_files = $this->getPreviewFiles($product);

            if ($product->previews_files) {
                $product->previews_files = $product->previews_files->toArray();
            }

            $product->tagsList = $product->tags->pluck('name');

            $product->placeholder_fallback = $product->present()->getPreview('placeholder', 'low', null, true);

            $product->placeholder = Imgix::getImgixUrl($product->placeholder_fallback, 660);

            $product->objectID = $product->id;

            $product->setHidden($hideColumns);

            $product->setAppends(['is_kick_ass']);

            $product['specs'] = $product->getPackageSpec();
        });

        $productsArray = $products->toArray();

        foreach ($productsArray as &$product) {
            $product['tags'] = $product['tagsList']->toArray();
            $product['categories'] = [];

            if ($product['free']) {
                $product['categories'][] = 'Free Items';
            }

            if ($product['category'] && $product['category']['name']) {
                if (!$product['subcategories'] || !count($product['subcategories'])) {
                    $product['subcategories'] = [['name' => 'none']];
                }

                $product['categories'][] = $product['category']['name'];
                $subcategories = array_map(function ($subcategory) use ($product) {
                    return $product['category']['name'] . ' > ' . $subcategory['name'];
                }, $product['subcategories']);

                $product['categories'] = array_merge($product['categories'], $subcategories);
            }

            $specs = $product['specs'];
            $product['specs'] = [];
            $validSpecs = [
                'version',
                'resolution',
                'bpm',
                'fps',
                'compression',
                'plugins',
                'format',
                'sampleRate'
            ];

            $filters = [
                Categories::AFTER_EFFECTS_TEMPLATES_ID => ['resolution', 'version'],
                Categories::STOCK_VIDEO_ID => ['resolution'],
                Categories::STOCK_MOTION_GRAPHICS_ID => ['resolution', 'version'],
                Categories::STOCK_MUSIC_ID => ['bpm', 'duration'],
                Categories::PREMIERE_PRO_TEMPLATES_ID => ['resolution', 'version'],
                Categories::MOTION_GRAPHICS_TEMPLATES_ID => ['resolution', 'version'],
                Categories::SOUND_EFFECTS_ID => [],
                Categories::PREMIERE_PRO_PRESETS_ID => ['resolution', 'version'],
                Categories::AFTER_EFFECTS_PRESETS_ID => ['resolution', 'version'],
                Categories::DAVINCI_RESOLVE_TEMPLATES_ID => ['resolution', 'version'],
                Categories::PREMIERE_RUSH_TEMPLATES_ID => ['resolution'],
                Categories::DAVINCI_RESOLVE_MACROS_ID => ['resolution', 'version'],
                Categories::FINAL_CUT_PRO_TEMPLATES_ID => ['resolution', 'version'],
                Categories::STOCK_PHOTOS_ID => [],
            ];

            foreach ($categories as $category) {
                $product['specs']['cat' . $category->id] = [];

                if ($category->id == $product['category']['id']) {
                    if ($specs) {
                        foreach ($specs as $name => $specArr) {
                            if (in_array($name, $validSpecs)) {
                                foreach ($specArr as &$spec) {
                                    if ($name == 'bpm') {
                                        // Clean BPM specs
                                        $spec['name'] = str_replace(' BPM', '', $spec['name']);
                                    }

                                    $product['specs']['cat' . $category->id][$name] = $spec['name'];
                                }
                            }
                        }

                        if (($category->id == Categories::STOCK_MUSIC_ID || $category->id == Categories::SOUND_EFFECTS_ID)
                            && isset($product['track_durations'])) {
                            $durations = explode(',', str_replace(' ', '', $product['track_durations']));

                            if (count($durations)) {
                                $product['specs']['cat4']['duration'] = $durations;
                            }
                        }
                    }
                } else {
                    foreach ($filters[$category->id] as $filter) {
                        $product['specs']['cat' . $category->id][$filter] = 'none';
                    }
                }
            }

            unset($product['product_level']);
            unset($product['tagsList']);
            unset($product['subcategories']);
            unset($product['track_durations']);
            unset($product['active_preview']);
            unset($product['submission']);

            if (isset($product['seller'])) {
                unset($product['seller']['id']);
                unset($product['seller']['is_plan_free']);
                unset($product['seller']['plan']);
            }

            $publishedAt = Carbon::parse($product['published_at']);

            $product['published_at'] = $publishedAt->getTimestamp();

            // Add 3 days of ranking first for kickass products
            if ($product['is_kick_ass']) {
                if (!is_null($product['kick_ass_at'])) {
                    $kickAssAt = Carbon::parse($product['kick_ass_at']);
                    $product['published_at'] = $kickAssAt->addDays(3)->getTimestamp();
                } else {
                    $product['published_at'] = $publishedAt->addDays(3)->getTimestamp();
                }
            }

            unset($product['kick_ass_at']);
        }

        return $productsArray;
    }

    public function getPreviewFiles(Product $product)
    {
        // Previews
        $previewFilesQuery = $product->activePreview->files();

        if ($product->isAudio()) {
            $previewFilesQuery->whereIn('label', ['ogg high', 'mp3 high']);
        } else {
            $previewFilesQuery->where('label', 'like', '% low')
                ->where('label', '!=', 'placeholder low');
        }

        $previewsFiles = $previewFilesQuery->select(['label', 'format', 'url'])
            ->get();

        $previewsFiles = $previewsFiles->map(function ($previewsFile) {
            $parsed = parse_url($previewsFile->url);
            $previewsFile->url_fallback = $previewsFile->url;
            $previewsFile->url = config('aws.previews_cdn') . ltrim($parsed['path'], "/");

            $previewsFile->url_fallback = Helpers::convertToHttps($previewsFile->url_fallback);
            $previewsFile->url = Helpers::convertToHttps($previewsFile->url);

            return $previewsFile;
        });

        return $previewsFiles;
    }

    /**
     * @param array $attributes
     * @param $product
     */
    protected function storeProductSpecs(array $attributes, $product)
    {
        if (isset($attributes['category_id[]']) && $attributes['category_id[]']) {
            $free_subcategory_ids = [];
            $attr_sub_category_ids = [];
            $sub_category_ids = [];

            //If this is a free product add it to the free subcategory
            if ($product->free) {
                $free_subcategory = $product->category->subCategories()->where('slug', '=', 'free')->first();
                if ($free_subcategory) {
                    $free_subcategory_ids = [$free_subcategory->id];
                }
            }

            // Get Sub Categories which product has.
            $sub_categories = $product->subCategories()->get(['sub_category_id']);

            foreach ($sub_categories as $sub_category) {
                array_push($sub_category_ids, $sub_category->sub_category_id);
            }

            // Get Sub Categories from attributes
            if ($attributes['category_' . $product->category_id . '_sub_category_id[]']) {
                $attr_sub_category_ids = (array)$attributes['category_' . $product->category_id . '_sub_category_id[]'];
            }

            $filtered_ids = array_diff($sub_category_ids, $free_subcategory_ids);
            $filtered_attr_ids = array_diff($attr_sub_category_ids, $free_subcategory_ids);

            // check if sun categories are changed
            if (count(array_diff($filtered_ids, $filtered_attr_ids)) != 0
                || count(array_diff($filtered_attr_ids, $filtered_ids)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::SUB_CATEGORY_CHANGED_ID);
            }

            $product->subCategories()->detach();

            if (count($free_subcategory_ids) > 0) {
                array_push($filtered_attr_ids, $free_subcategory_ids[0]);
            }

            foreach ($filtered_attr_ids as $filtered_attr_id) {
                $product->subCategories()->save(SubCategory::find($filtered_attr_id));
            }
        }

        // Store compressions1
        if (isset($attributes['compression_id[]']) && $attributes['compression_id[]']) {
            $compressionIds = [];

            // Get compressions which product has.
            $compressions = $product->compressions()->get(['compression_id']);

            foreach ($compressions as $compression) {
                array_push($compressionIds, $compression->compression_id);
            }

            $attrCompressionIds = (array)$attributes['compression_id[]'];

            // check if sun compressions are changed
            if (count(array_diff($compressionIds, $attrCompressionIds)) != 0
                || count(array_diff($attrCompressionIds, $compressionIds)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::COMPRESSION_CHANGED_ID);

                $product->compressions()->detach();

                foreach ($attrCompressionIds as $attrCompressionId) {
                    $product->compressions()->save(Compression::find($attrCompressionId));
                }
            }
        }

        // Store formats
        if (isset($attributes['format_id[]']) && $attributes['format_id[]']) {
            $formatIds = [];

            // Get formats which product has.
            $formats = $product->formats()->get(['format_id']);

            foreach ($formats as $format) {
                array_push($formatIds, $format->format_id);
            }

            $attrFormatIds = (array)$attributes['format_id[]'];

            // check if formats are changed
            if (count(array_diff($formatIds, $attrFormatIds)) != 0
                || count(array_diff($attrFormatIds, $formatIds)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::FORMAT_CHANGED_ID);

                $product->formats()->detach();

                foreach ($attrFormatIds as $attrFormatId) {
                    $product->formats()->save(Format::find($attrFormatId));
                }
            }
        }

        // Store resolutions
        if (isset($attributes['resolution_id[]']) && $attributes['resolution_id[]']) {
            $resolutionIds = [];

            // Get Sub resolutions which product has.
            $resolutions = $product->resolutions()->get(['resolution_id']);

            foreach ($resolutions as $resolution) {
                array_push($resolutionIds, $resolution->resolution_id);
            }

            $attrResolutionIds = (array)$attributes['resolution_id[]'];

            // check if sun resolutions are changed
            if (count(array_diff($resolutionIds, $attrResolutionIds)) != 0
                || count(array_diff($attrResolutionIds, $resolutionIds)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::RESOLUTION_CHANGED_ID);

                $product->resolutions()->detach();

                foreach ($attrResolutionIds as $attrResolutionId) {
                    $product->resolutions()->save(Resolution::find($attrResolutionId));
                }
            }
        }

        // Store versions
        if (isset($attributes['version_id[]']) && $attributes['version_id[]']) {
            $versionIds = [];

            // Get Sub versions which product has.
            $versions = $product->versions()->get(['version_id']);

            foreach ($versions as $version) {
                array_push($versionIds, $version->version_id);
            }

            $attrVersionIds = (array)$attributes['version_id[]'];

            // check if sun versions are changed
            if (count(array_diff($versionIds, $attrVersionIds)) != 0
                || count(array_diff($attrVersionIds, $versionIds)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::VERSION_CHANGED_ID);

                $product->versions()->detach();

                foreach ($attrVersionIds as $attrVersionId) {
                    $product->versions()->save(Version::find($attrVersionId));
                }
            }
        }

        // Store bpms
        if (isset($attributes['bpm_id[]']) && $attributes['bpm_id[]']) {
            $bpmIds = [];

            // Get bpms which product has.
            $bpms = $product->bpms()->get(['bpm_id']);

            foreach ($bpms as $bpm) {
                array_push($bpmIds, $bpm->bpm_id);
            }

            $attrBpmIds = (array)$attributes['bpm_id[]'];

            // check if bpms are changed
            if (count(array_diff($bpmIds, $attrBpmIds)) != 0
                || count(array_diff($attrBpmIds, $bpmIds)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::BPM_CHANGED_ID);

                $product->bpms()->detach();

                foreach ($attrBpmIds as $attrBpmId) {
                    $product->bpms()->save(Bpm::find($attrBpmId));
                }
            }
        }

        // Store fpss
        if (isset($attributes['fps_id[]']) && $attributes['fps_id[]']) {
            $fpsIds = [];

            // Get fpss which product has.
            $fpss = $product->fpss()->get(['fps_id']);

            foreach ($fpss as $fps) {
                array_push($fpsIds, $fps->fps_id);
            }

            $attrFpsIds = (array)$attributes['fps_id[]'];

            // check if fpss are changed
            if (count(array_diff($fpsIds, $attrFpsIds)) != 0
                || count(array_diff($attrFpsIds, $fpsIds)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::FPS_CHANGED_ID);

                $product->fpss()->detach();

                foreach ($attrFpsIds as $attrFpsId) {
                    $product->fpss()->save(Fps::find($attrFpsId));
                }
            }
        }

        //Store sample rates
        if (isset($attributes['sample_rate_id[]']) && $attributes['sample_rate_id[]']) {
            $sampleRateIds = [];

            // Get Sample Rates which product has.
            $sampleRates = $product->sampleRates()->get(['sample_rate_id']);

            foreach ($sampleRates as $sampleRate) {
                array_push($sampleRateIds, $sampleRate->sample_rate_id);
            }

            $attrSampleRateIds = (array)$attributes['sample_rate_id[]'];

            // check if Sample Rates are changed
            if (count(array_diff($sampleRateIds, $attrSampleRateIds)) != 0
                || count(array_diff($attrSampleRateIds, $sampleRateIds)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::SAMPLE_RATE_CHANGED_ID);

                $product->sampleRates()->detach();

                foreach ($attrSampleRateIds as $attrSampleRateId) {
                    $product->sampleRates()->save(SampleRate::find($attrSampleRateId));
                }
            }
        }

        // Store plugins
        if (isset($attributes['plugin_id[]']) && $attributes['plugin_id[]']) {
            $pluginIds = [];

            // Get Plugins which product has.
            $plugins = $product->plugins()->get(['product_plugin_id']);

            foreach ($plugins as $plugin) {
                array_push($pluginIds, $plugin->product_plugin_id);
            }

            $attrPluginIds = (array)$attributes['plugin_id[]'];

            // check if Plugins are changed
            if (count(array_diff($pluginIds, $attrPluginIds)) != 0
                || count(array_diff($attrPluginIds, $pluginIds)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::PLUGIN_CHANGED_ID);

                $product->plugins()->detach();

                foreach ($attrPluginIds as $attrPluginId) {
                    $product->plugins()->save(ProductPlugin::find($attrPluginId));
                }
            }
        }

        // Store tags
        if (isset($attributes['tags']) && $attributes['tags']) {
            $tagNames = [];

            // Get tags which product has.
            $tags = $product->tags()->get();

            foreach ($tags as $tag) {
                array_push($tagNames, $tag->name);
            }

            $attrTags = explode(",", strtolower($attributes['tags']));
            $attrTagNames = [];

            foreach ($attrTags as $attrTag) {
                $tag = trim($attrTag);

                if ($tag) {
                    array_push($attrTagNames, $tag);
                }
            }

            // check if tags are changed
            if (count(array_diff($tagNames, $attrTagNames)) != 0
                || count(array_diff($attrTagNames, $tagNames)) != 0) {
                $this->setChangeOptions($product, ProductChangeOptions::TAG_CHANGED_ID);

                $product->tags()->detach();

                $tags = $this->processTags($attributes['tags']);

                foreach ($tags as $tag) {
                    // FIXME: A null array item is creeping in for some reason
                    if (!is_null($tag)) {
                        $product->tags()->save($tag);
                    }
                }
            }
        }
    }

    public function getDownloadUrl(Product $product, User $user = null, $useCdn = true)
    {
        $payingUser = $user && !$user->plan->isFree();

        if ($useCdn && ($payingUser || $user->isAdmin())) {
            $url = $this->getCdnDownloadUrl($product);
        } else {
            $url = $this->getStorageDownloadUrl($product);
        }

        return $url;
    }

    public function getStorageDownloadUrl(Product $product)
    {
        $bucket = Config::get("aws.packages_bucket");

        /** @var S3Client $s3 */
        $s3 = AWS::get('s3');

        $filename = $product->package_filename . "." . $product->package_extension;

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $filenameAlias = ucfirst($product->slug) . '.' . $ext;

        $args = ['ResponseContentDisposition' => 'attachment; filename="' . $filenameAlias . '"'];

        $url = $s3->getObjectUrl($bucket, $filename, config('aws.packages_signed_url_expiration'), $args);

        return $url;
    }

    public function getCdnDownloadUrl(Product $product)
    {
        $filename = $product->package_filename . '.' . $product->package_extension;
        $filenameAlias = ucfirst($product->slug) . '.' . $product->package_extension;

        $fileUrl = Config::get('aws.packages_cdn') . $filename . '?response-content-disposition=attachment%3B%20filename%3D' . $filenameAlias;

        /** @var UrlSigner $urlSigner */
        $urlSigner = app(UrlSigner::class);

        $fileUrl = $urlSigner->getSignedUrl($fileUrl, (new \DateTime(config('aws.packages_signed_url_expiration')))->getTimestamp());

        return $fileUrl;
    }

    public function updateKickAss($product, $kickAss)
    {
        if (!$product->is_kick_ass && $kickAss) {
            $product->kick_ass_at = Carbon::now();
        }

        $product->is_kick_ass = $kickAss;

        $product->save();
    }
}
