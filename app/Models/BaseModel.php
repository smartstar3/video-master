<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use MotionArray\Models\Traits\Validable;
use JesusRugama\Rememberable\Rememberable;
use Event;

class BaseModel extends Eloquent
{
    use Rememberable, Validable;

    protected $guarded = [];

    /**
     * Validate post on saving
     * @return boolean
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($post) {
            if ($post->validate()) {
                // Model has changed. Fire event.
//                Event::fire($post->getClass($post) . ".change");
            } else {
                return false;
            }
        });
    }

//    public static function booted($callback, $priority = 0)
//    {
//        static::registerModelEvent('booted', $callback, $priority);
//    }

    /**
     * todo: replace with sluggable
     *
     * Generate slug
     */
    public function generateSlug($name)
    {
        return str_replace(" ", "-", trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags(strtolower($name))))))));
    }

    /**
     * Get class (to lower)
     */
    public function getClass($object)
    {
        return strtolower(str_replace(__NAMESPACE__ . '\\', '', get_class($object)));
    }
}