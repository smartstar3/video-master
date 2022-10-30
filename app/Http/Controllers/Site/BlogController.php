<?php namespace MotionArray\Http\Controllers\Site;

use MotionArray\Repositories\BlogRepository;
use MotionArray\Repositories\PageRepository;
use View;
use Request;
use Redirect;

class BlogController extends BaseController
{
    protected $blog;
    protected $page;

    protected $perPage = 8;

    public function __construct(BlogRepository $blog, PageRepository $page)
    {
        $this->blog = $blog;
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

    public function index($page = 1)
    {
        $entry = $this->page->getPageByURI('blog');

        $page = intval(preg_replace('#^p#i', '', $page));

        $entries = $this->blog->getEntries($page, $this->perPage, 1);

        $total = $this->blog->countEntries();

        $latestPost = $this->blog->getLastEntry();

        $pagination = $this->getPagination($page, $total);

        return View::make('site.blog.index', compact('entries', 'latestPost', 'page', 'entry', 'pagination'));
    }

    public function show($slug)
    {
        $entry = $this->blog->getPageBySlug($slug);

        if (!$entry) {
            return Redirect::to('blog');
        }

        return View::make('site.blog._entry', compact('entry'));
    }

    public function results($page = 1)
    {
        $page = intval(preg_replace('#^p#i', '', $page));

        $query = Request::input('q');

        $entries = $this->blog->search($query, $page, $this->perPage);

        $total = $this->blog->countEntries($query);

        $pagination = $this->getPagination($page, $total);

        $pagination['filters'] = 'q=' . $query;

        return View::make('site.blog.results', compact('query', 'entries', 'pagination'));
    }
}
