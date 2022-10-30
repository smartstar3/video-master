<?php

namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\Redirect;
use MotionArray\Repositories\CategoryGroupRepository;
use MotionArray\Repositories\PageRepository;
use MotionArray\Repositories\PluginRepository;
use MotionArray\Repositories\PluginCategoryRepository;
use View;
use Request;
use Response;

/**
 * Class PagesController
 *
 * @package MotionArray\Http\Controllers\Site
 */
class PluginsController extends BaseController
{
    protected $page;

    protected $plugin;

    protected $pluginCategory;

    protected $categoryGroup;

    public function __construct(
        PageRepository $pageRepository,
        PluginRepository $pluginRepository,
        PluginCategoryRepository $pluginCategoryRepository,
        CategoryGroupRepository $categoryGroupRepository
    )
    {
        $this->page = $pageRepository;

        $this->plugin = $pluginRepository;

        $this->pluginCategory = $pluginCategoryRepository;

        $this->categoryGroup = $categoryGroupRepository;
    }

    public function index($categoryGroupSlug = null, $pluginCategorySlug = null)
    {
        $entry = $this->page->getPageByURI('plugins');

        $categoryGroup = $this->categoryGroup->findBySlug($categoryGroupSlug);

        $pluginCategory = $this->pluginCategory->findBySlug($pluginCategorySlug);

        $plugins = $this->plugin->search(null, $categoryGroup, $pluginCategory);

        return View::make('site.plugins.index', compact('entry', 'plugins', 'categoryGroup', 'pluginCategory'));
    }

    public function show($categoryGroupSlug, $pluginCategorySlug, $slug)
    {
        $plugin = $this->plugin->findBySlug($slug);

        if (!$plugin) {
            return Response::view('site.errors.404');
        }

        $relatedPlugins = $this->plugin->getRelatedPlugins($plugin);

        return View::make('site.plugins.show', compact('plugin', 'relatedPlugins'));
    }

    public function results($categoryGroupSlug = null, $pluginCategorySlug = null)
    {
        $query = Request::get('q');

        $categoryGroup = $this->categoryGroup->findBySlug($categoryGroupSlug);

        $pluginCategory = $this->pluginCategory->findBySlug($pluginCategorySlug);
        $plugins = $this->plugin->search($query, $categoryGroup, $pluginCategory);

        return View::make('site.plugins.result', compact('query', 'categoryGroup', 'pluginCategory', 'plugins'));
    }

    public function download()
    {
        $url = config('plugins.download_url');

        return Redirect::to($url);
    }
}
