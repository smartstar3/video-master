<?php namespace MotionArray\Http\Controllers\API;

use MotionArray\Repositories\RequestRepository;
use Illuminate\Support\Facades\Auth;
use Request;
use Response;

class RequestsController extends BaseController
{
    protected $request;

    public function __construct(RequestRepository $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $query = Request::get('query');

        $statusSlug = Request::get('status');

        $categorySlug = Request::get('category');

        $userId = Request::get('userid');

        if (Request::get('myrequests') == 'true' && Auth::check()) {
            $userId = Auth::id();
        }

        $requests = $this->request->getRequests($query, $statusSlug, $categorySlug, $userId);

        return Response::json($requests);
    }

    public function show($id)
    {
        $request = $this->request->getRequest($id);

        return Response::json($request);
    }

    public function create()
    {
        $data = Request::all();

        $data['user_id'] = Auth::id();

        $request = $this->request->create($data);

        return Response::json($request);
    }

    public function update($requestId)
    {
        $data = Request::only([
            'category_id',
            'name',
            'description'
        ]);

        $request = $this->request->update($requestId, $data);

        return Response::json($request);
    }

    public function updateStatus($requestId)
    {
        // Find the submission
        $request = \MotionArray\Models\Request::find($requestId);

        $status = Request::input('status');

        $feedback = Request::input('feedback');

        if ($request) {
            // Update the status
            switch ($status) {
                case 'approved':
                    $this->request->setApproved($request, Auth::user(), [
                        'body_raw' => $feedback
                    ]);
                    break;
                case 'rejected':
                    $this->request->setRejected($request, Auth::user(), [
                        'body_raw' => $feedback
                    ]);
                    break;
            }

            // Return a successful response
            return Response::json($request);
        }

        // Submission or Status not found
        return App::abort('404');
    }

    public function upvote($requestId)
    {
        $request = $this->request->upvote($requestId, Auth::user());

        if (!$request) {
            return Response::json(['error' => 'Request not found']);
        }

        return Response::json(['count' => $request->upvotes()->count()]);
    }

    public function toggleUpvote($requestId)
    {
        $request = $this->request->toggleUpvote($requestId, Auth::user());

        if (!$request) {
            return Response::json(['error' => 'Request not found']);
        }

        return Response::json(['count' => $request->upvotes()->count()]);
    }

    public function destroy($requestId)
    {
        $request = \MotionArray\Models\Request::find($requestId);

        $response = $request->delete();

        return Response::json($response);
    }
}
