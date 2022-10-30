<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MotionArray\Traits\PresentableTrait;
use Config;

class PortfolioTheme extends BaseModel
{
    use PresentableTrait, SoftDeletes;

    protected $casts = [
        'settings' => 'json'
    ];

    protected $presenter = 'MotionArray\Presenters\PortfolioThemePresenter';

    protected $fillable = ['user_id', 'name', 'settings', 'content', 'saved'];

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function user()
    {
        return $this->belongsTo('\MotionArray\Models\User');
    }

    public function parentTheme()
    {
        return $this->belongsTo('\MotionArray\Models\PortfolioTheme', 'parent_theme_id');
    }

    /*
	|--------------------------------------------------------------------------
	|
	|--------------------------------------------------------------------------
	*/
    public function isSiteTheme()
    {
        return !$this->user_id;
    }
}