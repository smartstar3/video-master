<?php

namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use MotionArray\Repositories\PageRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Models\Category;

/**
 * Class PagesController
 *
 * @package MotionArray\Http\Controllers\Site
 */
class PagesController extends BaseController
{
    protected $category;
    protected $product;
    protected $page;

    protected $redirectTo = "/";
    protected $paginationRange = 10;

    /**
     * PagesController constructor.
     *
     * @param ProductRepository $product
     * @param Category $category
     * @param PageRepository $page
     */
    public function __construct(
        ProductRepository $product,
        Category $category,
        PageRepository $page
    )
    {
        $this->product = $product;
        $this->category = $category;
        $this->page = $page;
    }

    /**
     * Display pages by slug
     *
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function slug($slug)
    {
        $entry = $this->page->getPageByURI($slug);

        if (!$entry) {
            return app()->abort(404);
        }

        return view('site.pages.' . str_replace('.', '', $slug), compact('entry'));
    }

    /**
     * FAQ redirection
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function faq()
    {
        return redirect()->to(config('app.help_url'));
    }

    /**
     * Display Pricing Page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function pricing()
    {
        $entry = $this->page->getPageByURI('pricing');

        if (!$entry) {
            return app()->abort(404);
        }

        if(Request::get('v') == 2) {
            $totalProductNumber = $this->product->totalProductCount();

            return view('site.pages.pricing-b', compact('entry', 'totalProductNumber'));
        } else {
            return view('site.pages.pricing', compact('entry'));
        }
    }

    /**
     * Display RSS feed
     *
     * @return mixed
     */
    public function feed()
    {
        $entries = $this->page->getEntriesBySection(['blog', 'tutorials']);

        return response()->view('site.pages.feed', compact('entries'), 200, ['Content-type' => 'application/rss+xml; charset=utf-8']);
    }

    /**
     * Display Terms Of Service
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function termsOfService()
    {
        return $this->slug('terms-of-service');
    }

    /**
     * Display RSS XML
     *
     * @return mixed
     */
    public function feedXML()
    {
        return Redirect::to('feed.rss');
    }

    /**
     * Returns error 500 if the system isn't healthy.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function health()
    {
        $entry = $this->page->getHomeEntry();
        // Simple query to check DB connection
        $test = \Illuminate\Support\Facades\DB::select("SELECT count(*) as test from migrations");

        if (!$entry || $test[0]->test <= 0) {
            return app()->abort(500);
        }

        return 'OK';
    }
    /**
     * Display Adobe Panel pages
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adobePanel()
    {
        $entry = new \stdClass();
        $entry->title = 'Adobe Extension';
        $entry->slug = '/integrations/adobe';
        $entry->seoTitle = 'Adobe Extension';
        $entry->seoDescription = 'Download and import every asset you need, right inside your Adobe applications.';
        $entry->joinFooterTitle = 'Sign up for free, and start using our Marketplace Extension For Adobe today';
        $entry->joinFooterDescription = 'No hidden extras. Join now, and you\'ll get access to everything within the Motion Array video platform.';

        $entry->toolsHeader = [
            (object)[
                'video' => 'https://ma-content.s3.amazonaws.com/explainer-reels/Integrations/wip-ma-panel-promo-20269-PREVIEW',
                'header' => 'my-header'
            ]
        ];

        $entry->producerTestimonials = [
            (object)[
                'testimonialBody' => 'The Motion Array panel is like an all-you-can-eat buffet. I can grab any kind of stock asset without ever leaving Premiere Pro.',
                'producerName' => 'Sean S.',
                'producerDuration' => 'boardshortfilms.com',
                'producerAvatar' => [
                    (object)[
                        'url' => asset('assets/images/content/panel/testimonial-avatar1.png')
                    ]
                ]
            ],
            (object)[
                'testimonialBody' => 'The Adobe extension is a wonderful idea! I love it! It came in handy on a tight project timeline.',
                'producerName' => 'Lucius W.J',
                'producerDuration' => 'llstudio2069.com',
                'producerAvatar' => [
                    (object)[
                        'url' => asset('assets/images/content/panel/testimonial-avatar2.png')
                    ]
                ]
            ]
        ];

        $headerImage = asset('assets/images/content/panel/entry_image.png');

        return view('site.pages.panel', compact('entry', 'headerImage'));
    }
}
