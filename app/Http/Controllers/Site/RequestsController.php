<?php namespace MotionArray\Http\Controllers\Site;

use MotionArray\Repositories\RequestRepository;
use Illuminate\Support\Facades\Request;
use View;

class RequestsController extends BaseController
{
    protected $request;

    public function __construct(RequestRepository $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $selectedStatus = Request::get('status');

        $selectedCategory = Request::get('category');

        return View::make("site.requests.index", compact('requests', 'selectedStatus', 'selectedCategory'));
    }
}
