<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class CustomGallery extends Model
{
    protected $table = 'custom_galleries';

    public function collection()
    {
        return $this->belongsTo('MotionArray\Models\Collection', 'collection_id')->withTrashed();
    }
}
