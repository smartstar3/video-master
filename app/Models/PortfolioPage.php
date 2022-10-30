<?php namespace MotionArray\Models;

class PortfolioPage extends BaseModel
{
    protected $casts = [
        'settings' => 'json',
        'unpublished_settings' => 'json',
    ];

    const HomeType = 'home';

    const ProjectType = 'project';

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function portfolio()
    {
        return $this->belongsTo('\MotionArray\Models\Portfolio');
    }

    public function project()
    {
        return $this->belongsTo('\MotionArray\Models\Project');
    }

    /*
	|--------------------------------------------------------------------------
	| Scopes
	|--------------------------------------------------------------------------
	*/
    public function scopeHome($query)
    {
        return $query->where(['type' => self::HomeType]);
    }

    public function scopeProjects($query)
    {
        return $query->where(['type' => self::ProjectType]);
    }

    /*
	|--------------------------------------------------------------------------
	| Accessors & Mutators
	|--------------------------------------------------------------------------
	*/
    public function getUnpublishedSettingsAttribute($attributes)
    {
        $unpublishedSettings = json_decode($attributes, true);

        $unpublishedSettings = $this->addDefaultSections($unpublishedSettings);

        return $unpublishedSettings;
    }

    public function getSettingsAttribute($attributes)
    {
        return $this->getUnpublishedSettingsAttribute($attributes);
    }

    /*
	|--------------------------------------------------------------------------
	| Repo Functions
	|--------------------------------------------------------------------------
	*/
    public function isHome()
    {
        return $this->type == self::HomeType;
    }

    public function isProject()
    {
        return $this->type == self::ProjectType;
    }

    public function addDefaultSections($settings)
    {
        $settings = $this->addDefaultNavbarSection($settings);

        if ($this->isProject()) {
            $settings = $this->addDefaultVideoSection($settings);
        }

        $settings = $this->addDefaultFooterSection($settings);

        return $settings;
    }

    protected function addDefaultNavbarSection($settings)
    {
        $sections = isset($settings['sections']) ? $settings['sections'] : [];

        $sectionTypes = array_column($sections, 'type');

        if (!in_array('navbar', $sectionTypes)) {
            array_unshift($sections, [
                'type' => 'navbar',
                'active' => true,
                "menu_active" => true,
                'title' => 'menu'
            ]);
        }

        $settings['sections'] = $sections;

        return $settings;
    }

    public function addDefaultFooterSection($settings)
    {
        $sections = isset($settings['sections']) ? $settings['sections'] : [];

        $sectionTypes = array_column($sections, 'type');

        if (!in_array('footer', $sectionTypes)) {
            $sections[] = [
                'type' => 'footer',
                'active' => true,
                "menu_active" => true,
                'title' => 'footer'
            ];
        }

        $settings['sections'] = $sections;

        return $settings;
    }

    /**
     * Adds the default section for project
     *
     * @return bool
     */
    protected function addDefaultVideoSection($settings)
    {
        $sections = isset($settings['sections']) ? $settings['sections'] : [];

        $sectionTypes = array_column($sections, 'type');

        if (!in_array('video', $sectionTypes)) {
            $sections[] = [
                "type" => "video",
                "menu_title" => "video",
                "active" => true,
                "menu_active" => false,
                "title" => "video",
            ];
        }

        $settings['sections'] = $sections;

        return $settings;
    }
}