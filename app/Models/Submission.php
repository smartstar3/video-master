<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Submission extends BaseModel
{
    use SoftDeletes;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['product', 'product.music', 'status'];

    protected $dates = ['submitted_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['meta'];


    public function scopeApproved($query)
    {
        return $this->scopeStatus($query, 'approved');
    }

    public function scopeRejected($query)
    {
        return $this->scopeStatus($query, 'rejected');
    }

    public function scopePending($query)
    {
        return $this->scopeStatus($query, 'pending');
    }

    private function scopeStatus($query, $statusName)
    {
        $status = SubmissionStatus::where('status', '=', $statusName)->first();

        return $query->where('submission_status_id', $status->id);
    }

    /**
     * Append meta attribute
     *
     * @return array
     */
    public function getMetaAttribute()
    {
        $meta = [
            'queue_position' => $this->getQueuePosition(),
            'seller' => [
                'name' => $this->seller['firstname'] . ' ' . $this->seller['lastname']
            ]
        ];

        return $meta;
    }

    /**
     * Get the pending review queue position for this item.
     *
     * @return [type] [description]
     */
    public function getQueuePosition()
    {
        // Check to see if this Submission is pending review.
        if (!$this->hasStatus('pending') || !$this->product) {
            return null;
        }

        // Return the position.
        if ($this->submitted_at) {
            // Return the position.
            $result = DB::select("SELECT * 
                                      FROM (SELECT *, ROW_NUMBER() OVER(ORDER BY `sub0`.`requested` DESC, `sub0`.`submitted_at` ASC, `sub0`.`created_at` ASC) as pos
                                      FROM (SELECT `submissions`.`id`, `submissions`.`submission_status_id`, `submissions`.`submitted_at`, `submissions`.`created_at`, (request_products.id > 0) as requested
                                            FROM `submissions`
                                            INNER JOIN `products` ON `products`.`id`=`submissions`.`product_id`
                                            LEFT JOIN `request_products` ON `request_products`.`product_id`=`products`.`id`
                                            WHERE `products`.`category_id` = {$this->product->category->id}
                                            AND `submissions`.`submission_status_id` = {$this->status->id}
                                            AND `submissions`.`deleted_at` IS NULL
                                            AND `products`.`deleted_at` IS NULL) sub0
                                            ORDER BY `sub0`.`requested` DESC, `sub0`.`submitted_at` ASC, `sub0`.`created_at` ASC) sub1
                                            WHERE `id` = {$this->id}");

            if(COUNT($result)) {
                return $result[0]->pos;
            }
        }


        return null;
    }


    /**
     * Check to see if a Submission has the specified status.
     *
     * @param  String $status The status to be searched for.
     *
     * @return boolean
     */
    public function hasStatus($status)
    {
        if ($this->status()->where('status', $status)->first()) {
            return true;
        }

        return false;
    }


    /**
     * Submission belongs to Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('MotionArray\Models\Product');
    }


    /**
     * Submission belongs to User (seller)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller()
    {
        return $this->belongsTo('MotionArray\Models\User', 'seller_id', 'id');
    }


    /**
     * Submission belongs to SubmissionStatus
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo('MotionArray\Models\SubmissionStatus', 'submission_status_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function submissionReviewer()
    {
        return $this->hasOne('MotionArray\Models\SubmissionReviewer');
    }

    /**
     * Submission has many SubmissionNotes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany('MotionArray\Models\SubmissionNote');
    }

}
