<?php namespace MotionArray\Repositories\PortfolioContent\MergeContent;

use Illuminate\Support\Facades\Config;
use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioPage;
use MotionArray\Models\PortfolioTheme;

class BaseMergeStrategy
{
    // For sections
    protected $useDefaultContent = true;

    public function extendSettings($base, $object)
    {
        $base = (array)$base;
        $object = (array)$object;

        // Extend except sections
        $response = array_merge_recursive_distinct(array_except($base, ['sections']), array_except($object, ['sections']));

        $response['sections'] = $this->extendSections($base, $object);

        return $response;
    }

    public function setUseDefaultContent($useDefaultContent)
    {
        $this->useDefaultContent = $useDefaultContent;

        return $this;
    }

    public function extendSections($base, $object)
    {
        if (!isset($base['sections']) && !isset($object['sections'])) return [];
        if (!isset($base['sections'])) return $object['sections'];

        if (!isset($object['sections']) || !count($object['sections'])) {
            if ($this->useDefaultContent) {
                return $base['sections'];
            } else {
                return [];
            }
        }

        // Extend if Exists
        $sections = array_map(function ($objectSection) use ($base) {
            foreach ($base['sections'] as $baseSection) {
                if ($objectSection['type'] == $baseSection['type']) {

                    // Special fix for clients section
                    if (isset($objectSection['type']) && $objectSection['type'] == 'clients') {
                        if (count($objectSection['content']['logos'])) {
                            $baseSection['content']['logos'] = [];
                        }
                    }

                    $objectSection = array_merge_recursive_distinct($baseSection, $objectSection);
                    break;
                }
            }

            return $objectSection;

        }, $object['sections']);

        return $sections;
    }

    /**
     * Overrides $settings styles using $themeSettings styles
     *
     * @param $settings
     * @param $themeSettings
     */
    public function applyStyles($settings, PortfolioTheme $theme)
    {
        $pageRepository = app()->make('MotionArray\Repositories\PortfolioPageRepository');
        $portfolioRepository = app()->make('MotionArray\Repositories\PortfolioRepository');

        $pageSettings = array_only($settings, ['sections']);
        $portfolioSettings = array_except($settings, ['sections']);

        $pageSettings = $pageRepository->applyStyles($pageSettings, $theme);
        $portfolioSettings = $portfolioRepository->applyStyles($portfolioSettings, $theme);

        $settings = $portfolioSettings;
        $settings['sections'] = $pageSettings['sections'];

        return $settings;
    }

    public function addSectionIds(Array $settings)
    {
        $ids = array_column($settings['sections'], 'id');

        $remainingIds = [];
        for ($x = 1; $x <= count($settings['sections']); $x++) {
            $remainingIds[] = $x;
        }
        $remainingIds = array_diff($remainingIds, $ids);

        foreach ($settings['sections'] as &$section) {
            if (!isset($section['id'])) {
                $section['id'] = array_shift($remainingIds);
            }
        }

        return $settings;
    }

    public function prepareThemeContent(PortfolioTheme $theme, $settings, Portfolio $portfolio = null, PortfolioPage $page = null)
    {
        $themeSettings = $theme->settings;

        if ($settings) {
            if (!$portfolio || !$portfolio->unpublished_settings) {
                $sections = array_get($settings, 'sections', []);
                $settings = array_merge($themeSettings, [
                    'sections' => $sections
                ]);
            }
            $themeSettingsSections = array_get($themeSettings, 'sections');
            if ($themeSettingsSections && (!$page || ($page->isHome() && !$page->exists))) {
                $settings['sections'] = $themeSettingsSections;
            }
        }
        $settings = $this->setSectionFallbackEmails($settings, $portfolio);

        return $settings;
    }

    protected function setSectionFallbackEmails($settings, Portfolio $portfolio)
    {
        $sections = array_get($settings, 'sections');

        if ($sections){
            foreach ($sections as $key => $section) {
                if ($section['type'] === 'contact') {
                    if ($portfolio) {
                        $email = $portfolio->email;

                        if (!$email) {
                            $email = $portfolio->owner->email; 
                        }
                    }
                    $settings['sections'][$key]['content']['receive_email'] = $email;
                    if (empty($settings['footer']['content']['social']['email'])) {
                        $settings['footer']['content']['social']['email'] = $email;
                    }
                }
            }
        }
        return $settings;
    }
}

function array_merge_recursive_distinct(array $array1, array $array2)
{
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
        if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
            $merged [$key] = array_merge_recursive_distinct($merged [$key], $value);
        } else {
            $merged [$key] = $value;
        }
    }

    return $merged;
}
