<?php

namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use MotionArray\Models\Category;
use MotionArray\Models\SubCategory;
use Carbon\Carbon;
use MotionArray\Repositories\CategoryGroupRepository;
use MotionArray\Repositories\PluginRepository;
use MotionArray\Repositories\Products\ProductRepository;

class SitemapsController extends BaseController
{
    protected $categoryGroup;

    protected $product;

    protected $plugin;

    protected $maxPerPage = 50000;

    public function __construct(
        CategoryGroupRepository $categoryGroupRepository,
        ProductRepository $productRepository,
        PluginRepository $pluginRepository
    )
    {
        $this->categoryGroup = $categoryGroupRepository;
        $this->product = $productRepository;
        $this->plugin = $pluginRepository;
    }

    public function index()
    {
        $sitemap = app()->make("sitemap");

        $sitemap->addSitemap(Url::to('sitemap/pages.xml'), Carbon::now()->toDateTimeString());
        $sitemap->addSitemap(Url::to('hub/sitemap/index.xml'), Carbon::now()->toDateTimeString());
        $sitemap->addSitemap(Url::to('sitemap/marketplace.xml'), Carbon::now()->toDateTimeString());

        foreach (Category::all() as $category) {
            $count = $this->product->getProductSiteMapDataByCategoryQuery($category->id)->count();
            $pages = ceil($count / $this->maxPerPage);

            if ($pages > 1) {
                foreach (range(1, $pages) as $page) {
                    $sitemap->addSitemap(Url::to('sitemap/marketplace/' . $category->slug . '/products-' . $page . '.xml'), Carbon::now()->toDateTimeString());
                }
            } else {
                $sitemap->addSitemap(Url::to('sitemap/marketplace/' . $category->slug . '/products.xml'), Carbon::now()->toDateTimeString());

            }
        }

        $sitemap->addSitemap(Url::to('sitemap/plugins.xml'), Carbon::now()->toDateTimeString());

        return $sitemap->render('sitemapindex');
    }

    public function redirect()
    {
        return Redirect::to('sitemap/index.xml');
    }

    public function pages()
    {
        $sitemap = app()->make("sitemap");

        $urls = [
            '/',
            'marketplace',
            'reviews',
            'portfolio',
            'requests',
            'tutorials',
            'blog'
        ];

        foreach ($urls as $url) {
            $sitemap->add(URL::to($url), Carbon::now()->toDateTimeString(), '1.0', 'daily');
        }

        return $sitemap->render('xml');
    }

    public function plugins()
    {
        $sitemap = app()->make("sitemap");

        $sitemap->add(URL::to('plugins'), Carbon::now()->toDateTimeString(), '1.0', 'weekly');

        $categoryGroups = $this->categoryGroup->all();

        foreach ($categoryGroups as $categoryGroup) {
            if ($categoryGroup->pluginCategories->count()) {
                $url = 'plugins/' . $categoryGroup->slug;

                $sitemap->add(URL::to($url), Carbon::now()->toDateTimeString(), '1.0', 'weekly');

                foreach ($categoryGroup->pluginCategories as $pluginCategory) {
                    $sitemap->add(URL::to($url . '/' . $pluginCategory->slug), Carbon::now()->toDateTimeString(), '1.0', 'weekly');
                }
            }
        }

        foreach ($this->plugin->all() as $plugin) {
            $pluginCategory = $plugin->category;

            $url = 'plugins/' . $pluginCategory->categoryGroup->slug . '/' . $pluginCategory->slug . '/' . $plugin->slug;
            $url = URL::to($url);
            $sitemap->add($url, $plugin->published_at, '1.0', 'weekly');
        }

        return $sitemap->render('xml');
    }

    public function marketplace()
    {
        $sitemap = app()->make("sitemap");

        $sitemap->add(URL::to('browse'), Carbon::now()->toDateTimeString(), '1.0', 'daily');
        $sitemap->add(URL::to('browse/free'), Carbon::now()->toDateTimeString(), '1.0', 'daily');

        $categories = Category::orderBy('name', 'asc')->get();
        $sub_categories = SubCategory::orderBy('name', 'asc')->get();

        foreach ($categories as $category) {
            $url = 'browse/' . $category->slug;
            $url = URL::to($url);
            $sitemap->add($url, $category->updated_at, '1.0', 'daily');
        }

        foreach ($sub_categories as $sub_category) {
            $url = 'browse/' . $sub_category->category()->first()->slug . '/' . $sub_category->slug;
            $url = URL::to($url);
            $sitemap->add($url, $sub_category->updated_at, '1.0', 'daily');
        }

        return $sitemap->render('xml');
    }

    public function products($categorySlug, $page = 1)
    {
        $sitemap = app()->make("sitemap");

        $category = Category::where(['slug' => $categorySlug])->first();

        if (!$category) {
            return Redirect::to('sitemap/marketplace.xml');
        }
        $products = $this->product->getProductSiteMapDataByCategoryQuery($category->id)
            ->skip(($page - 1) * $this->maxPerPage)
            ->take($this->maxPerPage + 1)
            ->get();

        if (!$products->count()) {
            return Redirect::to('sitemap/marketplace.xml');
        }

        foreach ($products as $product) {
            $url = $category->slug . '/' . $product->slug;
            $url = URL::to($url);
            $sitemap->add($url, $product->published_at, '1.0', 'weekly');
        }

        return $sitemap->render('xml');
    }
}
