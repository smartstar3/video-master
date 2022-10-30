<?php namespace MotionArray\Http\Controllers\Site;

use MotionArray\Repositories\PageRepository;
use MotionArray\Repositories\TutorialRepository;
use MotionArray\Repositories\TutorialCategoryRepository;
use View;
use Request;
use Redirect;

class TutorialsController extends BaseController
{
    protected $tutorial;
    protected $tutorialCategory;
    protected $page;

    protected $perPage = 8;

    public function __construct(
        TutorialRepository $tutorial,
        TutorialCategoryRepository $tutorialCategory,
        PageRepository $page
    )
    {
        $this->tutorial = $tutorial;
        $this->tutorialCategory = $tutorialCategory;
        $this->page = $page;
    }

    private function getPagination($page, $total)
    {
        $pagination = [
            'products_per_page' => $this->perPage,
            'current_page_no' => $page,
            'product_count' => $total,
            'pagination_range' => 10,
            'param_str' => '/p',
            'remove_browse' => true
        ];

        return $pagination;
    }

    /**
     * Multiple point of entry for listing and details
     *
     * @param $path
     * @param $lastSegment
     *
     * @return mixed
     */
    public function index($path = null, $lastSegment = null)
    {
        $page = 1;

        if ($path && !$lastSegment) {
            $lastSegment = $path;

            $path = null;
        }

        if ($lastSegment) {
            if (preg_match('#p([0-9]+)#i', $lastSegment, $matches)) {
                $page = intval($matches[1]);
            } elseif ($this->tutorial->getPageBySlug($lastSegment)) {
                return $this->show($path, $lastSegment);
            } else {
                $path .= ($path ? '/' : '') . $lastSegment;
            }
        }

        return $this->listing($path, $page);
    }

    public function listing($path, $page = 1)
    {
        $entry = $this->page->getPageByURI('tutorials');

        $page = intval(preg_replace('#^p#i', '', $page));

        $categories = $this->tutorialCategory->getCategoriesByLevel();

        $selectedCategories = $this->tutorialCategory->findCategoriesInPath($path);

        $criteria = [];

        $selectedCategory = null;

        if ($selectedCategories) {
            $selectedCategory = end($selectedCategories);

            reset($selectedCategories);

            $criteria = ['relatedTo' => $selectedCategory];
        }

        $total = $this->tutorial->countEntries(null, $criteria);

        $offset = $this->perPage * ($page-1);
        if ($offset > $total) {
            // Redirect to last page
        }

        $entries = $this->tutorial->getEntries($page, $this->perPage, 1, $criteria);

        $latestPost = $this->tutorial->getLastEntry($criteria);

        $pagination = $this->getPagination($page, $total);

        $title = $selectedCategories ? ('Amazing ' . implode(' | ', $selectedCategories)) : 'Tutorials';

        return View::make('site.tutorials.index', compact('title', 'entries', 'categories', 'selectedCategories', 'path', 'latestPost', 'page', 'entry', 'pagination'));
    }

    public function show($path, $slug = null)
    {
        $entry = $this->tutorial->getPageBySlug($slug);

        $categories = $this->tutorialCategory->findCategoriesInPath($path);

        if (!$entry) {
            return Redirect::to('tutorials');
        } elseif (Request::path() != $entry->uri) {
            return Redirect::to($entry->uri);
        }

        return View::make('site.tutorials._entry', compact('entry', 'categories'));
    }

    public function results($path = null, $page = 1)
    {
        // Fix for path
        if (preg_match('#p([0-9])+#i', $path, $matches)) {
            $page = $path;

            $path = null;
        }

        $categories = $this->tutorialCategory->getCategoriesByLevel();

        $selectedCategories = $this->tutorialCategory->findCategoriesInPath($path);

        $criteria = [];

        $selectedCategory = null;

        if ($selectedCategories) {
            $selectedCategory = end($selectedCategories);

            reset($selectedCategories);

            $criteria = ['relatedTo' => $selectedCategory];
        }

        $page = intval(preg_replace('#^p#i', '', $page));

        $query = Request::input('q');

        $entries = $this->tutorial->search($query, $page, $this->perPage, $criteria);

        $total = $this->tutorial->countEntries($query);

        $pagination = $this->getPagination($page, $total);

        $pagination['filters'] = 'q=' . $query;

        $title = $selectedCategories ? implode(' | ', $selectedCategories) : 'Tutorials';

        return View::make('site.tutorials.results', compact('title', 'path', 'query', 'entries', 'categories', 'selectedCategories', 'pagination'));
    }
}
