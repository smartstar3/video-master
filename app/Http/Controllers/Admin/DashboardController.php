<?php namespace MotionArray\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Repositories\BlogRepository;
use MotionArray\Repositories\TutorialRepository;
use \Redirect;

class DashboardController extends BaseController
{
    private $productRepo, $blogRepo, $tutorialRepo;

    public function __construct(
        ProductRepository $productRepository,
        BlogRepository $blogRepository,
        TutorialRepository $tutorialRepository
    )
    {
        $this->productRepo = $productRepository;
        $this->blogRepo = $blogRepository;
        $this->tutorialRepo = $tutorialRepository;
    }

    function index()
    {
        $user_name = Auth::user()->firstname . " " . Auth::user()->lastname;

        return View::make('admin.index', compact("user_name"));
    }

    function encodingErrors()
    {
        $failedProducts = $this->productRepo->getFailedApprovedProducts();

        return View::make('admin.failed-products-encoding', compact('failedProducts'));
    }

    function fixEncodingErrors($productId)
    {
        $product = $this->productRepo->find($productId);

        $this->productRepo->fixEncodingErrors($product);

        $this->productRepo->updateAlgoliaDataForProduct($product->id);

        return Redirect::to('/mabackend/encoding-errors');
    }

    function createWaveform()
    {
        $total = $this->productRepo->countOldAudio();

        $old_stock_products = $this->productRepo->getOldAudio();

        foreach ($old_stock_products as $value) {
            $value['previews'] = $value->previews;
        }

        return View::make('admin.create-waveform', compact('old_stock_products'));
    }

    function changeLog()
    {
        $user_name = Auth::user()->firstname . " " . Auth::user()->lastname;

        return View::make('admin.change-log', compact("user_name"));
    }

    function automateNewsletters()
    {
        $format = 'l jS \\of F';

        list($ds, $de) = $this->productRepo->weeklyProductsDateRange();

        $productsDateRange = $ds->format($format) . ' to ' . $de->format($format);

        list($ds, $de) = $this->blogRepo->weeklyRecapDateRange();

        $weeklyRecapDateRange = $ds->format($format) . ' to ' . $de->format($format);

        return View::make('admin.automate-newsletters.index', compact('productsDateRange', 'weeklyRecapDateRange'));
    }

    function autoDescriptions()
    {
        return View::make('admin.auto-descriptions.index');
    }

    function weeklyRecap()
    {
        $limit = 5;

        $blogs = $this->blogRepo->weeklyRecap($limit);
        $tutorials = $this->tutorialRepo->weeklyRecap($limit);

        $posts = array_merge($blogs, $tutorials);

        usort($posts, function ($a, $b) {
            $bDate = Carbon::instance($b->postDate);
            $aDate = Carbon::instance($a->postDate);

            return $bDate->gt($aDate) ? -1 : 1;
        });

        $posts = array_slice($posts, 0, $limit);

        return View('admin.automate-newsletters.weekly-recap', compact('posts'));
    }
}
