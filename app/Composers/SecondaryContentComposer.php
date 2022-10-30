<?php namespace MotionArray\Composers;

use MotionArray\Repositories\PageRepository;

class SecondaryContentComposer
{
    protected $page;

    public function __construct(PageRepository $page)
    {
        $this->page = $page;
    }

    public function compose($view)
    {
        if (!isset($view->columns) && isset($view->page)) {
            $slug = $view->page ? $view->page : 'browse';

            $page = $this->page->getPageBySlug($slug);

            if (isset($page->secondaryContent)) {
                $view->with('columns', $page->secondaryContent)->with('slug', $slug);
            }
        }
    }

}
