<?php namespace MotionArray\Repositories;

use Illuminate\Support\Facades\App;
use MotionArray\Events\SubmissionApproved;
use MotionArray\Jobs\SendPreviews;
use MotionArray\Jobs\SendProductToAlgolia;
use MotionArray\Repositories\EloquentBaseRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Mailers\SubmissionMailer;
use MotionArray\Models\Submission;
use MotionArray\Models\SubmissionStatus;
use MotionArray\Models\SubmissionNote;
use MotionArray\Models\Product;
use MotionArray\Models\ProductStatus;
use MotionArray\Models\SubmissionReviewer;
use MotionArray\Models\User;
use Carbon\Carbon;
use DB;
use MotionArray\Services\Algolia\AlgoliaClient;

class SubmissionRepository extends EloquentBaseRepository
{
    /**
     * @var AlgoliaClient
     */
    private $algolia;
    /**
     * @var Product Repo
     */
    private $productRepo;
    /**
     * @var Submission Mailer
     */
    private $submissionMailer;

    private $paginationLimit = 15;

    public function __construct(
        Submission $submission,
        ProductRepository $productRepository,
        SubmissionMailer $submissionMailer,
        AlgoliaClient $algolia)
    {
        $this->model = $submission;
        $this->productRepo = $productRepository;
        $this->submissionMailer = $submissionMailer;
        $this->algolia = $algolia;
    }

    /**
     * Find a submission status by its status
     *
     * @return SubmissionStatus The matched SubmissionStatus object.
     */
    public function findStatusByStatus($status)
    {
        return SubmissionStatus::where('status', $status)->first();
    }
    /**
     * ============================================
     * Getters
     * ============================================
     */
    /**
     * Get all submissions.
     *
     * @return Collection A colleciton of Submission objects.
     */
    public function getSubmissions($status = null, $category = null, $allowed_categories, $q = null, $free = false, $kickass = false, $ownedbyma = false)
    {
        // Return all submission ordered by created_at ASC.
        $query = Submission::query();

        if ($allowed_categories) {
            $query->where(function ($query) use ($allowed_categories) {
                foreach ($allowed_categories as $key => $allowed_category) {
                    $query->orwhere(function ($query) use ($key, $allowed_category) {
                        $query->where('submissions.submission_status_id', $key);

                        $query->where(function ($query) use ($allowed_category) {
                            foreach ($allowed_category as $value) {
                                $query->orWhere('products.category_id', $value->id);
                            }
                        });
                    });
                }
            });
        }

        if ($free) {
            $query->where('free', 1);
        }

        if ($kickass) {
            $query->where('product_level_id', 1);
        }

        if ($ownedbyma) {
            $query->where('owned_by_ma', 1);
        }

        if ($status) {
            $query->where('submissions.submission_status_id', $status);
        }

        $query->join('products', 'products.id', '=', 'submissions.product_id');
        $query->whereNull('products.deleted_at');

        if ($category) {
            $query->where('products.category_id', $category);
        }

        if ($q) {
            $query->join('users', 'users.id', '=', 'submissions.seller_id');
            $parts = array_filter(explode(" ", trim($q)));
            $query->where(function ($query) use ($parts) {
                foreach ($parts as $part) {
                    $query->orWhere('products.name', 'LIKE', "%{$part}%")
                        ->orWhere('products.slug', 'LIKE', "%{$part}%")
                        ->orwhere("users.firstname", "LIKE", "%$part%")
                        ->orwhere("users.lastname", "LIKE", "%$part%")
                        ->orWhere('users.company_name', 'LIKE', "%{$part}%")
                        ->orWhere('users.email', 'LIKE', "%{$part}%");
                }
            });
        }

        $query->whereNull('submissions.deleted_at');

        $query->leftJoin('request_products', 'request_products.product_id', '=', 'products.id');

        $query->select('submissions.*', DB::raw('(request_products.id > 0) as requested'));

        $query->orderBy('requested', 'desc')
            ->orderBy('submissions.submitted_at', 'asc')
            ->orderBy('submissions.created_at', 'asc');

        return $query->paginate($this->paginationLimit);
    }

    /**
     * Get all submissions for the specified seller.
     *
     * @return Collection A collection of Submission objects.
     */
    public function getSubmissionsBySeller($seller, $status = null, $category = null, $order_by = "created_at", $order_direction = "asc")
    {
        // Return all submission ordered by created_at ASC.
        $query = Submission::where('submissions.seller_id', '=', $seller->id);

        if ($status) {
            $query->where('submissions.submission_status_id', $status);
        }

        if ($category || $order_by === "earnings-month" || $order_by === "earnings-forever") {
            $query->join('products', function($join) use($seller) {
                $join->on('products.id', '=', 'submissions.product_id');
            });

            if ($category) {
                $query->where('products.category_id', $category);
            }
        }

        // Order the results
        if (!$order_direction) {
            $order_direction = "asc";
        }

        if (!$order_by || $order_by === "created_at") {
            $query->orderBy('submissions.id', $order_direction);
        }

        $query->select('submissions.*');

        if ($order_by === "earnings-month" ||  $order_by === "earnings-forever") {

                $query->leftJoin('t_product_earnings_by_month', function($join) use ($order_by) {
                    $join->on('submissions.product_id', '=', 't_product_earnings_by_month.product_id');

                    if ($order_by === "earnings-month") {
                        $start_date = Carbon::now()->startOfMonth();

                        $join->where('period_start', '=', $start_date);
                    }
                })
                ->select(DB::raw('submissions.*, SUM(earnings) AS earnings'))
                ->groupBy('submissions.id')
                ->orderBy('earnings', $order_direction);
        }

        return $query->paginate($this->paginationLimit);
    }

    /**
     * Get all submissions with the specied status.
     *
     * @param  SubmissionStatus $status A SubmissionStatus object.
     *
     * @return Collection                A colleciton of Submission objects.
     */
    public function getSubmissionsByStatus($status)
    {
        // Query for submission with the specified status.
        return Submission::where('submission_status_id', '=', $status->id)
            ->orderBy('submitted_at', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get all submissions assigned to a category.
     *
     * @param  Category $category A Category object to find Submissions associated with.
     * @return Collection         A collection of Submission objects.
     */
    public function getSubmissionsByCategory($category)
    {
        // Query for submissions with the specified category.
        return Submission::whereHas('product', function ($query) use ($category) {
            $query->where('category_id', $category->id);
        })->get();
    }

    /**
     * Get all submission statuses.
     *
     * @return Collection   A collection of SubmissionStatus objects.
     */
    public function getSubmissionStatuses()
    {
        return SubmissionStatus::all();
    }
    /**
     * ============================================
     * Store and update methods
     * ============================================
     */
    /**
     * Create a new Submission record in the database
     *
     * @param  Product $product The product that has been submitted.
     * @param  User $seller The seller that made the submission.
     *
     * @return Submission       The created Submission object.
     */
    public function create($product, $seller)
    {
        // Create a new Submission.
        $submission = new Submission;
        // Set the Submission attributes.
        $submission->product_id = $product->id;
        $submission->seller_id = $seller->id;
        // Assign the new status.
        $status = $this->findStatusByStatus('new');
        $submission->submission_status_id = $status->id;
        // Save the Submission.
        $submission->save();

        // Return the new Submission.
        return $submission;
    }

    /**
     * Create a SubmissionNote record in the database.
     *
     * @param  Submission $submission The Submission object that the note should
     *                                be attached to.
     * @param  User $reviewer The moderator that has caused this note.
     * @param  array $data An array of attributes to set the
     *                                SubmissionNote properties with.
     *
     * @return SubmissionNote         The created SubmissionNote.
     */
    public function createNote($submission, $reviewer, $data = [])
    {
        // Create a new SubmissionNote.
        $note = new SubmissionNote;
        $note->submission_id = $submission->id;
        $note->reviewer_id = $reviewer->id;
        $note->submission_status_id = $submission->submission_status_id;
        $note->body_raw = $this->valueOrDefault($data, 'body_raw');
        $note->body = array_key_exists('body_raw', $data) ? $this->formatHtml($data['body_raw']) : null;
        // Save the SubmissionNote.
        $note->save();
        $this->assignReviewer($submission, $reviewer);

        // Return the new SubmissionNote.
        return $note;
    }

    /**
     * @param $submission
     * @param $reviewer
     *
     * @return mixed
     */
    public function assignReviewer($submission, $reviewer)
    {
        $submission_reviewer = SubmissionReviewer::firstOrNew(['submission_id' => $submission->id]);

        $submission_reviewer->reviewer_id = $reviewer->id;
        $submission_reviewer->submission_id = $submission->id;
        $submission_reviewer->save();

        return $reviewer;
    }

    /**
     * @param $submission
     *
     * @return bool
     */
    public function removeReviewer($submission)
    {
        $submission_reviewer = SubmissionReviewer::where('submission_id', $submission->id)->first();

        if ($submission_reviewer) {
            $submission_reviewer->delete();

            return true;
        }

        return false;
    }

    /**
     * @param $submission
     *
     * @return submission Reviewer
     */
    public function getReviewStatus($submission)
    {
        $submission_reviewer = SubmissionReviewer::where('submission_id', $submission->id)->first();
        $note = SubmissionNote::where('submission_id', $submission->id)
            ->OrderBy('created_at', 'desc')->first();

        if ($submission_reviewer) {
            $submission_reviewer->reviewer = User::find($submission_reviewer->reviewer_id);
        }

        if ($note) {
            if (!$submission_reviewer) {
                $submission_reviewer = (object)['note' => $note];
            } else {
                $submission_reviewer->note = $note;
            }

            $submission_reviewer->note->reviewer = User::withTrashed()->find($submission_reviewer->note->reviewer_id);
        }

        return $submission_reviewer;
    }

    /**
     * Update an existing Submission object.
     *
     * @param  Submission $submission The Submission object to be updated.
     * @param  array $data An array of attributes to update the
     *                                Submission.
     *
     * @return Submission             The updayed Submission object.
     */
    public function update($submission, $data = [])
    {
        // Set the Submission attributes.
        $submission->product_id = $this->valueOrDefault($data, 'product_id');
        $submission->seller_id = $this->valueOrDefault($data, 'seller_id');
        $submission->submission_status_id = $this->valueOrDefault($data, 'submission_status_id');
        // Save the Submission.
        $submission->save();

        // Return the new Submission.
        return $submission;
    }

    /**
     * Send a submission for review.
     *
     * @param  Submission $submission The Submission to be reviewed
     *
     * @return submission             The update Submission.
     */
    public function submitForReview($submission)
    {
        $status = $this->findStatusByStatus('pending');
        if (!$submission->submitted_at) {
            $submission->submitted_at = Carbon::now();
        }
        $submission->submission_status_id = $status->id;
        $submission->save();
        // Reload the Submission to get updated relations.
        $submission = $this->findById($submission->id);
        // Notify the seller that their submission was received.
        $this->submissionMailer->submissionReceived($submission);

        // Return the updated submission.
        return $submission;
    }

    /**
     * ============================================
     * Setters
     * ============================================
     */
    /**
     * Set a Submission as Approved.
     *
     * @param Submission $submission The Submission to be approved.
     * @param User $reviewer The moderator that approved the submission.
     * @param array $data An array of attributes to populate the
     *                               SubmissionNote.
     *
     * @return Submission            The approved Submission object.
     */
    public function setApproved($submission, $reviewer, $data = [])
    {
        $existing_status_id = $submission->submission_status_id;

        // Update the status of the Submission.
        $status = $this->findStatusByStatus('approved');
        $submission->submission_status_id = $status->id;
        $submission->save();

        $product = $submission->product;
        $isKickAssChangedToTrue = (!$product->is_kick_ass && $data['is_kick_ass']);

        $this->productRepo->updateKickAss($product, $data['is_kick_ass']);

        if ($data['weight']) {
            $product->weight = $data['weight'];
        }
        $product->save();

        $product->refresh();
        // Create a SubmissionNote with any feedback provided.
        $note = $this->createNote($submission, $reviewer, $data);
        // Fetch the Published product status.
        $product_status = ProductStatus::whereStatus('Published')->first();
        // Mark the Product as published.
        $this->productRepo->update($submission->product->id, [
            'product_status_id' => $product_status->id,
            'published_at' => Carbon::now(),
        ]);

        dispatch(new SendPreviews($submission->product));

        dispatch((new SendProductToAlgolia($submission->product))->onQueue('high'));

        // Notify the seller that their submission was approved. - Only if we have changed the status
        // and kickass is marked from false to true.
        if ($existing_status_id != $status->id || $isKickAssChangedToTrue) {
            $this->submissionMailer->submissionApproved($submission, $note);

            event(new SubmissionApproved($submission));
        }


        return $submission;
    }

    /**
     * Set a Submission as Rejected.
     *
     * @param Submission $submission The Submission to be rejected.
     * @param User $reviewer The moderator that rejected the submission.
     * @param array $data An array of attributes to populate the
     *                               SubmissionNote.
     *
     * @return boolean
     */
    public function setRejected($submission, $reviewer, $data = [])
    {
        // Notify the seller that their submission was rejected.
        $this->submissionMailer->submissionRejected($submission, $data['body_raw']);
        // Delete the submission.
        $this->delete($submission);

        return true;
    }

    /**
     * Set a Submission as Needing Work.
     *
     * @param Submission $submission The Submission to set as needs work.
     * @param User $reviewer The moderator that updated the submission.
     * @param array $data An array of attributes to populate the
     *                               SubmissionNote.
     *
     * @return Submission            The needs work Submission object.
     */
    public function setNeedsWork($submission, $reviewer, $data = [])
    {
        // Update the status of the Submission.
        $status = $this->findStatusByStatus('needs-work');
        $submission->submission_status_id = $status->id;
        $submission->save();

        //Init product change options
        if($submission->product) {
            $submission->product->productChanges()->detach();
        }

        // Create a SubmissionNote with any feedback provided.
        $note = $this->createNote($submission, $reviewer, $data);
        // Notify the seller that their submission need work.
        $this->submissionMailer->submissionNeedsWork($submission, $note);

        $this->algolia->removeProduct($submission->product->id);

        return $submission;
    }
    /**
     * Set a submission to be deleted later
     *
     * Authors can request their "approved" submission to be
     * deleted after a number of days of being live, and it will be
     * deleted a number of days after request.
     *
     * @param Submission $submission
     * @return boolean
     */
    public function requestDelete(Submission $submission)
    {
        $minimum_days_live = 30;
        // Only allow deletion of approved content after 30 days live,
        // and even then, keep it for another X days (from DeleteSubmissions command).
        $approval_time = strtotime($submission->submitted_at);
        $days_past = now()->timestamp - $approval_time / (24 * 60 * 60);
        $days_remaining = $minimum_days_live - $days_past;
        if ($days_remaining > 0 or $submission->delete_requested_at != null)
            return false;

        $submission->delete_requested_at = now();
        $submission->save();
        return true;
    }
    /**
     * Cancel a delete request for a submission
     *
     * @param Submission $submission
     * @return boolean
     */
    public function cancelDeleteRequest(Submission $submission)
    {
        if (!$submission->delete_requested_at)
            return false;
        $submission->delete_requested_at = null;
        $submission->save();
        return true;
    }
    /**
     * ============================================
     * Destructive methods
     * ============================================
     */
    /**
     * Delete a Submission.
     *
     * @param  Submission $submission The Submission to be deleted.
     *
     * @return boolean
     */
    public function delete(Submission $submission)
    {
        $productRepository = App::make('MotionArray\Repositories\Products\ProductRepository');

        // Don't delete the preview files if approved or has downloads
        $deleteFiles = !($submission->hasStatus('approved') || $submission->product->downloads->count());

        $productRepository->delete($submission->product, $deleteFiles);

        // Delete the Submission.
        return $submission->delete();
    }
}
