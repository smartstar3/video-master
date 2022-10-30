<?php namespace MotionArray\Helpers\Portfolio\PortfolioContent;

use MotionArray\Helpers\Portfolio\PortfolioHelper;

class PortfolioContentHelper extends PortfolioContentGetter
{
    protected $jsonSettings;

    protected $settings;

    public function getSection($id)
    {
        $sections = $this->getSections();

        $section = array_values(array_where($sections, function ($key, $value) use ($id) {
            return ($value->id == $id);
        }));

        if (isset($section[0])) {
            return $section[0];
        }
    }

    public function getNavigationLinks()
    {
        return (array)$this->header('menu');
    }

    public function getSections()
    {
        $sections = $this->settings['sections'];

        return array_map(function ($section) {
            return new PortfolioContentSection($section);
        }, $sections);
    }

    public function header($path, $default = null)
    {
        $value = $this->get($path, $default, 'header');

        return $value;
    }

    public function footer($path, $default = null)
    {
        return $this->get($path, $default, 'footer');
    }

    public function globals($path, $default = null)
    {
        $val = $this->get($path, $default, 'globals');

        if (!is_null($val)) {
            return $val;
        }

        return $this->get($path, $default, 'global_variable');
    }

    public function getGalleryClasses()
    {
        $globalClasses = $this->globals('gallery.classes');
        $isPlayerStyleSet = false;
        $globalClasses = explode(' ', $globalClasses);
        foreach ($globalClasses as $globalClasse) {
            if (strpos($globalClasse, "player-control-style") === 0) {
                $isPlayerStyleSet = true;
            }
        }
        if (!$isPlayerStyleSet) {
            $globalClasses[] = 'player-control-style-1';
        }
        $globalClasses = implode(' ', $globalClasses);

        return $globalClasses;
    }

    public function projects($path, $default = null)
    {
        return $this->get($path, $default, 'projects');
    }
}
