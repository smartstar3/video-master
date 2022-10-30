<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Embed\Embed;
use MotionArray\Jobs\UpdateUserOnIntercom;

/**
 * The request model for Motion Array design requests
 * Not to be confused with HTTP requests.
 */
class Request extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [];

    public static $rules = [
        'name' => 'required'
    ];

    protected $appends = ['queue_position', 'thumbnail'];

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/

    public function category()
    {
        return $this->belongsTo('MotionArray\Models\Category');
    }

    public function notes()
    {
        return $this->hasMany('MotionArray\Models\RequestNote');
    }

    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }

    public function upvotes()
    {
        return $this->hasMany('MotionArray\Models\RequestUpvote');
    }

    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'request_products');
    }

    public function status()
    {
        return $this->belongsTo('MotionArray\Models\RequestStatus', 'request_status_id');
    }

    /*
	|--------------------------------------------------------------------------
	| Accessors, Mutators & Scopes
	|--------------------------------------------------------------------------
	*/

    public function scopeApproved($query)
    {
        return $query->whereHas('status', function ($query) {
            $query->where(function ($q) {
                $q->where('slug', '=', 'active');
                $q->orWhere('slug', '=', 'complete');
            });
        });
    }

    public function getThumbnailAttribute()
    {
        return $this->attributes['thumbnail'] ? $this->attributes['thumbnail'] : '/assets/images/site/request-default@2x.jpg';
    }

    public function getThumbnail()
    {
        $videos = $this->getVideosInDescription();

        if (isset($videos[0])) {
            $video = $videos[0];

            $type = $video->providerName;

            if (strtolower($type) == 'youtube') {
                return str_replace('hqdefault.jpg', 'mqdefault.jpg', $video->image);
            } elseif (strtolower($type) == 'vimeo') {
                return $video->image;
            }
        }
    }

    public function updateThumbnail()
    {
        $thumbnail = $this->getThumbnail();

        if ($thumbnail) {
            $s3 = \App::make('aws')->get('s3');

            $ext = pathinfo($thumbnail, PATHINFO_EXTENSION);

            $filename = 'ma-request-' . $this->id . '.' . $ext;

            $response = $s3->putObject([
                'Bucket' => Project::previewsBucket(),
                'Body' => file_get_contents($thumbnail),
                'Key' => $filename,
                'ACL' => 'public-read',
                //'ContentType' => 'image/',
                'CacheControl' => 'public, max-age=31104000',
                'Expires' => date(DATE_RFC2822, strtotime("+360 days"))
            ]);

            if (isset($response['ObjectURL'])) {
                $this->thumbnail = $response['ObjectURL'];

                $this->save();
            }
        }
    }

    /**
     * Repository Methods
     */

    public function hasStatus($status)
    {
        if ($this->status()->where('slug', $status)->first()) {
            return true;
        }

        return false;
    }

    /**
     * Get the pending review queue position for this item.
     *
     * @return [type] [description]
     */
    public function getQueuePosition()
    {
        // Check to see if this Submission is pending review.
        if (!$this->hasStatus('new') || !$this->id) {
            return null;
        }

        // Return the position.
        $result = DB::select("SELECT (SELECT COUNT(*)
                                        FROM `requests`
                                        WHERE `requests`.`id` <= {$this->id}
                                        AND `requests`.`category_id` = {$this->category->id}
                                        AND `requests`.`request_status_id` = {$this->status->id}
                                        AND `requests`.`deleted_at` IS NULL)
                                    AS `position`
                                    FROM `requests`
                                    WHERE `id` = {$this->id};");

        if (COUNT($result)) {
            return $result[0]->position;
        }

        return null;
    }

    public function getQueuePositionAttribute($value)
    {
        return $this->getQueuePosition();
    }

    public function getVideosInDescription()
    {
        $urls = $this->getUrlsInDescription();

        $urls = array_map(function ($url) {
            try {
                $url = Embed::create($url);
            } catch (\Exception $e) {
                $url = null;
            }

            return $url;
        }, $urls);

        return array_filter($urls);
    }

    private function getUrlsInDescription()
    {
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $this->description, $match);

        return $match[0];
    }

    public function changeStatus($statusSlug)
    {
        $status = RequestStatus::where('slug', $statusSlug)->first();

        $this->request_status_id = $status->id;

        $this->save();
    }
}

// Update Intercom data
Request::updated(function ($request) {
    if ($request->user) {
        Bus::dispatch(new UpdateUserOnIntercom($request->user));
    }
});
