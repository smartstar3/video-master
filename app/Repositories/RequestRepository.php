<?php namespace MotionArray\Repositories;

use Illuminate\Support\Facades\DB;
use MotionArray\Mailers\RequestMailer;
use MotionArray\Models\Request;
use MotionArray\Models\RequestNote;
use MotionArray\Models\RequestStatus;
use MotionArray\Models\User;
use MotionArray\Repositories\EloquentBaseRepository;
use Auth;

class RequestRepository extends EloquentBaseRepository
{
    protected $requestMailer;

    public function __construct(RequestMailer $requestMailer)
    {
        $this->requestMailer = $requestMailer;
    }

    public function create($data)
    {
        $request = Request::create($data);

        $request = $request->fresh();

        $request->updateThumbnail();

        $this->requestMailer->requestReceived($request);

        return $request;
    }

    public function update($id, $data)
    {
        $request = Request::find($id);

        $request->update($data);

        $request->updateThumbnail();

        return $request;
    }

    public function getRequests($search = null, $statusSlug = null, $categorySlug = null, $userId = null)
    {
        $query = Request::leftJoin('request_upvotes', 'requests.id', '=', 'request_upvotes.request_id')
            ->select(DB::raw('requests.*,
        DATEDIFF(CURRENT_DATE(), requests.created_at) AS passed,
        COUNT(request_upvotes.id) AS upvotesCount,
        (COUNT(request_upvotes.id) - DATEDIFF(CURRENT_DATE(), requests.created_at)/2) AS score'))
            ->groupBy('requests.id')
            ->orderBy('score', 'DESC')
            ->orderBy('created_at', 'DESC');

        if ($query) {
            $query->where(function ($q) use ($search) {
                $search = '%' . $search . '%';

                $q->where('requests.name', 'LIKE', $search);

                $q->orWhere('requests.description', 'LIKE', $search);
            });
        }

        if ($statusSlug) {
            $statuses = explode(',', $statusSlug);

            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $status) {
                    $q->orWhereHas('status', function ($q) use ($status) {
                        $q->where('slug', '=', $status);
                    });
                }
            });
        }

        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', '=', $categorySlug);
            });
        }

        if ($userId) {
            $query->whereHas('user', function ($q) use ($userId) {
                $q->where('id', '=', $userId);
            });
        }

        $query = $this->includeFields($query);

        return $query->paginate();
    }

    public function getRequest($id)
    {
        $query = Request::whereNotNull('id');

        $query = $this->includeFields($query);

        return $query->find($id);
    }

    public function includeFields($query)
    {
        $with = ['status', 'category',
            'products' => function ($query) {
                $query->where('product_status_id', '=', 1);
            },

            'products.activePreview.files' => function ($query) {
                $query->where(function ($q) {
                    $q->whereIn('label', ['mp4 low', 'webm low', 'hls low']);

                    $q->orWhereIn('format', ['mp3', 'ogg']);
                });
            },

            'user' => function ($query) {
                $query->select(['id', 'firstname', 'lastname', 'email', 'plan_id']);
            }
        ];

        if (Auth::check()) {
            $with['products.collections'] = function ($q) {
                $q->where('user_id', '=', Auth::id());
            };
        }

        $query->with($with);

        return $query;
    }

    public function attachProduct($requestId, $productId)
    {
        $request = Request::find($requestId);

        if (!$request) {
            return;
        }

        $request->products()->attach($productId);

        return $request;
    }

    public function upvote($requestId, User $user)
    {
        $request = Request::find($requestId);

        if (!$request) {
            return;
        }

        $request->upvotes()->create([
            'user_id' => $user->id
        ]);

        return $request->fresh();
    }

    public function toggleUpvote($requestId, User $user)
    {
        $request = Request::find($requestId);

        if (!$request) {
            return;
        }

        $upvote = $request->upvotes()->where('user_id', '=', $user->id)->first();

        if ($upvote) {
            $upvote->delete();
        } else {
            $request->upvotes()->create([
                'user_id' => $user->id
            ]);
        }

        return $request->fresh();
    }

    public function setApproved($request, $reviewer, $data = [])
    {
        // Update the status of the Request.
        $request->changeStatus('active');

        // Create a RequestNote with any feedback provided.
        $note = $this->createNote($request, $reviewer, $data);

        // Notify the seller that their request was approved.
        $this->requestMailer->requestApproved($request, $note);

        return $request;
    }


    /**
     * Set a Request as Rejected.
     *
     * @param Request $request The Request to be rejected.
     * @param User $reviewer The moderator that rejected the request.
     * @param array $data An array of attributes to populate the
     *                               RequestNote.
     *
     * @return boolean
     */
    public function setRejected($request, $reviewer, $data = [])
    {
        // Notify the seller that their request was rejected.
        $this->requestMailer->requestRejected($request, $data['body_raw']);

        $this->createNote($request, $reviewer, $data);

        $request->changeStatus('rejected');

        // Delete the request.
        $this->destroy($request);

        return true;
    }

    public function createNote($request, $reviewer, $data = [])
    {
        // Create a new SubmissionNote.
        $note = new RequestNote;

        $note->request_id = $request->id;
        $note->reviewer_id = $reviewer->id;
        $note->request_status_id = $request->request_status_id;

        $note->body_raw = $this->valueOrDefault($data, 'body_raw');
        $note->body = array_key_exists('body_raw', $data) ? $this->formatHtml($data['body_raw']) : null;

        // Save the SubmissionNote.
        $note->save();

        // Return the new SubmissionNote.
        return $note;
    }

    public function destroy($request)
    {
        // Delete the Submission.
        $request->delete();
    }
}
