<?php namespace MotionArray\Repositories;

use MotionArray\Models\PortfolioPage;
use MotionArray\Models\PortfolioTheme;
use MotionArray\Models\Project;
use MotionArray\Models\Portfolio;
use MotionArray\Repositories\EloquentBaseRepository;

class PortfolioPageRepository extends EloquentBaseRepository
{
    /**
     * PortfolioPageRepository constructor.
     * @param PortfolioPage $portfolioPage
     */
    public function __construct(PortfolioPage $portfolioPage)
    {
        $this->model = $portfolioPage;
    }

    /**
     * @param Portfolio $portfolio
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function getHomePage(Portfolio $portfolio)
    {
        return $portfolio->pages()->home()->first();
    }

    /**
     * @param Portfolio $portfolio
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function findOrCreateHomePage(Portfolio $portfolio)
    {
        $page = $this->getHomePage($portfolio);

        if (!$page) {
            $page = $this->newHomePage($portfolio);

            $page->save();
        }

        return $page;
    }

    /**
     * @param Portfolio $portfolio
     * @return PortfolioPage
     */
    public function newHomePage(Portfolio $portfolio)
    {
        return new PortfolioPage([
            'portfolio_id' => $portfolio->id,
            'type' => PortfolioPage::HomeType
        ]);
    }

    /**
     * @param Project $project
     * @return PortfolioPage
     */
    public function findByProject(Project $project)
    {
        return $project->page;
    }

    /**
     * @param Project $project
     * @return PortfolioPage
     */
    public function findOrCreateByProject(Project $project)
    {
        $page = $this->findByProject($project);

        if (!$page) {
            $page = $this->createByProject($project);
        }

        return $page;
    }

    /**
     * Creates a Page for the given project
     *
     * @param Project $project
     * @return PortfolioPage
     */
    public function createByProject(Project $project)
    {
        $page = $this->newByProject($project);

        $page->save();

        return $page;
    }

    /**
     * @param Project $project
     * @return PortfolioPage
     */
    public function newByProject(Project $project)
    {
        $portfolio = $project->getPortfolio();

        $data = [
            'type' => PortfolioPage::ProjectType,
            'project_id' => $project->id
        ];

        if (isset($portfolio->id)) {
            $data['portfolio_id'] = $portfolio->id;
        }

        $page = new PortfolioPage($data);

        return $page;
    }

    /**
     * @param Portfolio $portfolio
     * @return mixed
     */
    public function publish(Portfolio $portfolio)
    {
        $pages = $portfolio->pages;

        foreach ($pages as $page) {
            $page->settings = $page->unpublished_settings;

            $page->save();
        }
    }

    public function applyStyles($pageSettings, PortfolioTheme $portfolioTheme)
    {
        if (isset($pageSettings['sections'])) {
            $settingsSections = $pageSettings['sections'];

            $themeSettings = $portfolioTheme->settings;

            $themeSections = $themeSettings['sections'];

            if (is_array($settingsSections) && count($settingsSections)) {
                foreach ($settingsSections as &$section) {

                    foreach ($themeSections as $i => $themeSection) {
                        if ($themeSection['type'] == $section['type']) {
                            $styles = [];
                            if (isset($themeSection['styles'])) {
                                $styles = $themeSection['styles'];
                            }
                            $section['styles'] = $styles;

                            $classes = [];
                            if (isset($themeSection['classes'])) {
                                $classes = $themeSection['classes'];
                            }
                            $section['classes'] = $classes;

                            // Move used section styles to the bottom
                            // To make sure the application of styles cycle 
                            // on sections of the same type
                            unset($themeSections[$i]);
                            $themeSections[] = $themeSection;

                            break;
                        }
                    }
                }
            }

            $pageSettings['sections'] = $settingsSections;
        }

        return $pageSettings;
    }

    public function stripHtmlTagAttributes($pageSettings)
    {
        if (isset($pageSettings['sections'])) {
            foreach ($pageSettings['sections'] as $i => $section) {
                $pageSettings['sections'][$i] = $this->stripHtmlTagAttributesOnSection($section);
            }
        }

        return $pageSettings;
    }

    public function stripHtmlTagAttributesOnSection($section)
    {
        if (!isset($section['content'])) {
            return $section;
        }

        switch ($section['type']) {
            case 'about':
                $texts = array_get($section, 'content.body');
                foreach ($texts as $i => $text) {
                    $htmlPaths = [
                        'content.body.' . $i . '.title',
                        'content.body.' . $i . '.text'
                    ];
                }
                break;
            case 'two-img':
                $htmlPaths = [
                    'content.cols.0.text',
                    'content.cols.1.text'
                ];
                break;
            default:
                $htmlPaths = [
                    'content.title',
                    'content.text'
                ];
                break;
        }

        foreach ($htmlPaths as $htmlPath) {
            $text = array_get($section, $htmlPath);

            if ($text) {
                $text = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $text);

                array_set($section, $htmlPath, $text);
            }
        }

        return $section;
    }

    /**
     * @param $section
     * @param $themeSection
     * @return array
     */
    public function extendAssets($section, $themeSection)
    {
        if ($themeSection && $themeSection['type'] != $section['type']) {
            return $section;
        }

        $assetsPaths = [];

        switch ($section['type']) {
            case'about':
                $images = array_get($section, 'content.body');
                foreach ($images as $i => $image) {
                    $assetsPaths[] = 'content.body.' . $i . '.media.upload';
                }
                break;
            case 'clients':
                $images = array_get($section, 'content.logos');
                foreach ($images as $i => $image) {
                    $assetsPaths[] = 'content.logos.' . $i . '.media.upload';
                }
                break;
            case 'two-img':
                $assetsPaths = [
                    'cols.0.media.upload',
                    'cols.1.media.upload',
                ];
                break;
            default:
                $assetsPaths = ['content.media.upload'];
                break;
        }

        // Extend
        foreach ($assetsPaths as $assetPath) {
            $asset = array_get($section, $assetPath);

            if (!$asset) {
                continue;
            }

            // If current media originates from a theme, then replace
            if ((isset($asset['origin']) && $asset['origin'] == 'theme') || (isset($section['origin']) && $section['origin'] == 'theme')) {
                if ($themeSection) {
                    $themeMedia = array_get($themeSection, $assetPath);

                    array_set($section, $assetPath, $themeMedia);
                }

                array_set($section, ($assetPath . '.origin'), 'theme');
            }
        }

        return $section;
    }

    public function applyTheme($pageSettings, PortfolioTheme $portfolioTheme)
    {
        $pageSettings = $this->applyStyles($pageSettings, $portfolioTheme);

        $pageSections = $pageSettings['sections'];

        $sections = [];

        $themeSettings = $portfolioTheme->settings;

        // Remove temporal sections
        // Extended from theme but not saved
        foreach ($pageSections as $i => $pageSection) {
            if (isset($pageSection['origin']) && $pageSection['origin'] == 'theme') {
                unset($pageSections[$i]);
            }
        }

        // Add sections by theme order
        foreach ($themeSettings['sections'] as $themeSection) {

            if (!$themeSection['active']) {
                continue;
            }

            $found = false;

            foreach ($pageSections as $i => $pageSection) {
                if ($themeSection['type'] == $pageSection['type']) {
                    $pageSection['active'] = $themeSection['active'];

                    $pageSection = $this->extendAssets($pageSection, $themeSection);

                    $sections[] = $pageSection;

                    $found = true;

                    unset($pageSections[$i]);

                    break;
                }
            }

            if (!$found) {
                $themeSection['origin'] = 'theme';

                $sections[] = $themeSection;
            }
        }

        // Add remaining sections
        $pageSections = array_map(function ($pageSection) {
            $pageSection['active'] = false;
            return $pageSection;
        }, $pageSections);
        $sections = array_merge(array_values($sections), array_values($pageSections));

        $pageSettings['sections'] = $sections;

        return $pageSettings;
    }
}


