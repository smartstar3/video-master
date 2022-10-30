<?php namespace MotionArray\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Collection;
use MotionArray\Repositories\RequestRepository;
use View;
use Request;

class RequestsController extends BaseController
{
    protected $request;

    public function __construct(RequestRepository $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $q = Request::get("q");

        $status = Request::get("status");

        $category = Request::get("category");

        $id = Request::get("id");

        $requests = new Collection();

        $params = [
            "category" => "all",
            "status" => "all",
            "q" => ""
        ];

        if (!$id) {
            $params["status"] = "new";
        }

        if ($q) {
            $params["q"] = $q;
        }

        if ($status) {
            $params["status"] = $status;
        }

        if ($category) {
            $params["category"] = $category;
        }

        return View::make("admin.requests.index", compact('requests', 'params', 'id'));
    }
}
