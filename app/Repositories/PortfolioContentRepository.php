<?php

namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Events\PortfolioSaved;
use MotionArray\Models\PortfolioPage;
use MotionArray\Models\PortfolioTheme;
use MotionArray\Models\Portfolio;
use MotionArray\Models\Project;
use MotionArray\Repositories\PortfolioPageRepository;
use MotionArray\Repositories\PortfolioThemeRepository;
use MotionArray\Services\Slim;
use MotionArray\Repositories\PortfolioContent\MergeContent\EditingStrategy;
use MotionArray\Repositories\PortfolioContent\MergeContent\PublicPortfolioStrategy;
use MotionArray\Repositories\PortfolioContent\MergeContent\ThemePreviewStrategy;
use MotionArray\Repositories\PortfolioContent\MergeContent\PreviewStrategy;
use MotionArray\Repositories\PortfolioContent\PortfolioContentProvider;
use AWS;

class PortfolioContentRepository
{
    protected $portfolioPage;
    protected $portfolioTheme;
    protected $portfolioContentProvider;

    public function __construct(
        PortfolioPageRepository $portfolioPage,
        PortfolioThemeRepository $portfolioTheme,
        PortfolioContentProvider $portfolioContentProvider
    )
    {
        $this->portfolioPage = $portfolioPage;
        $this->portfolioTheme = $portfolioTheme;
        $this->portfolioContentProvider = $portfolioContentProvider;
    }

    /**
     * Update Content
     *
     * @param PortfolioPage $portfolioPage
     * @param Portfolio $portfolio
     * @param $content
     * @param $settings
     * @return bool
     */
    public function updateContent(PortfolioPage $portfolioPage, Portfolio $portfolio, $content, $settings)
    {
        // todo: Remove, should come in json from JS
        $results = $this->mapUpdateRequest($portfolioPage, $portfolio, $content, $settings);

        $portfolioSettings = $portfolio->unpublished_settings;

        if (!$portfolioPage->isHome()) {

            if ($portfolioSettings && isset($portfolioSettings['header']['content']['menu'])) {
                // Dont update menu if page is not home
                $results['header']['content']['menu'] = $portfolioSettings['header']['content']['menu'];
            }

            // If homepage doesnt exists, then add
            $homePage = $this->portfolioPage->getHomePage($portfolio);
            if (!$homePage) {
                $themeSettings = $portfolio->portfolioTheme ? $portfolio->portfolioTheme->settings : null;

                if ($themeSettings && isset($themeSettings['sections'])) {
                    $homePage = new PortfolioPage([
                        'type' => PortfolioPage::HomeType,
                        'portfolio_id' => $portfolio->id
                    ]);
                    $sections = array_map(function ($section) {
                        $section['origin'] = 'theme';

                        return $section;
                    }, $themeSettings['sections']);
                    $homePage->unpublished_settings = ['sections' => $sections];
                    $homePage->save();
                }
            }

        } elseif (isset($portfolioSettings['globals']['styles']['gallery'])) {
            // Dont update project globals if page is home
            $results['globals']['styles']['gallery'] = $portfolioSettings['globals']['styles']['gallery'];
        }

        $portfolioPage->unpublished_settings = array_only($results, ['sections', 'project']);

        $portfolioPage->save();

        $portfolio->last_saved_at = Carbon::now();

        $portfolio->unpublished_settings = array_except($results, ['sections', 'project', 'projects']);

        $portfolio->save();

        if (isset($results['projects']) && isset($results['projects']['styles'])) {
            $this->updateAllProjects($portfolio, $results['projects']['styles']);
        }

        $portfolio = $portfolio->fresh();

        $this->portfolioTheme->publish($portfolio);

        \event(new PortfolioSaved($portfolio));

        return $results;
    }

    /**
     * @param Portfolio $portfolio
     * @param $key
     * @param $value
     * @return mixed
     */
    public function replaceImage(Portfolio $portfolio, $key, $value)
    {
        $this->deleteImage($portfolio, $key);

        $upload = $this->uploadImage($portfolio, $key, $value);

        return $upload->url;
    }

    /**
     * @param Portfolio $portfolio
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function uploadImage(Portfolio $portfolio, $key, $value)
    {
        $folder = 'portfolio-' . $portfolio->id;

        $filename = $key . '-' . time();

        $bucket = Project::previewsBucket();

        $image = Slim::getImage($value);

        $url = Slim::uploadToAmazon($bucket, $filename, $value, $folder);

        return $portfolio->uploads()->create([
            'url' => $url,
            'key' => $key,
            'width' => $image['output']['width'],
            'height' => $image['output']['height'],
        ]);
    }

    public function uploadImageFromUrl(Portfolio $portfolio, $key, $url)
    {
        $folder = 'portfolio-' . $portfolio->id;

        $filename = $key . '-' . time();

        $bucket = Project::previewsBucket();

        $s3 = \App::make('aws')->get('s3');

        $imageData = file_get_contents($url);
        $size = getimagesizefromstring($imageData);

        $file_info = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer($imageData);

        $ext = explode('/', $mime_type)[1];
        $ext = $ext == 'jpeg' ? 'jpg' : $ext;

        $filename = str_replace('.', '_', $filename) . '.' . $ext;

        $filename = str_replace([' ', '_'], '-', $filename);

        if (!ends_with($folder, '/')) {
            $folder .= '/';
        }

        $filename = $folder . strtolower(preg_replace("/[^a-zA-Z0-9.-]/", "", $filename));

        $response = $s3->putObject([
            'Bucket' => $bucket,
            'Body' => $imageData,
            'Key' => $filename,
            'ACL' => 'public-read',
            'ContentEncoding' => 'base64',
            'ContentType' => $mime_type,
            'CacheControl' => 'public, max-age=31104000',
            'Expires' => date(DATE_RFC2822, strtotime("+360 days"))
        ]);

        $amazonUrl = $response['ObjectURL'];

        return $portfolio->uploads()->create([
            'url' => $amazonUrl,
            'key' => $key,
            'width' => $size[0],
            'height' => $size[1]
        ]);
    }

    /**
     * Delete Portfolio Image
     *
     * @param Portfolio $portfolio
     * @param $key
     */
    public function deleteImage(Portfolio $portfolio, $key)
    {
        $file = $portfolio->uploads()->where(['key' => $key])->first();

        if (!$file) {
            return;
        }

        $s3 = AWS::get('s3');

        $bucket = Project::previewsBucket();

        $bucketUrl = Project::bucketUrl();

        if ($file->url) {
            $s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => str_replace($bucketUrl, '', $file->url)
            ]);

            $file->delete();
        }
    }

    /**
     * todo: Remove after fixing Javascript
     *
     * @param $results
     * @param $contents
     * @param $keyType
     * @return mixed
     */
    protected function mapSettings($results, $contents, $keyType)
    {
        // Useful function to merge properties into Array
        $addToSectionsById = function ($results, $id, $addValue) {
            $ids = array_column($results['sections'], 'id');

            $index = array_search($id, $ids);

            if ($index !== false) {
                $addValue = array_merge($results['sections'][$index], $addValue);
                $results['sections'][$index] = $addValue;
            } else {
                $addValue['id'] = $id;
                $results['sections'][] = $addValue;
            }

            return $results;
        };

        $isset = function ($key) use ($contents) {
            return isset($contents['main_' . $key]) || isset($contents['project_' . $key]);
        };

        $get = function ($key) use ($contents) {
            if (isset($contents['main_' . $key])) return $contents['main_' . $key];
            if (isset($contents['project_' . $key])) return $contents['project_' . $key];
        };

        if ($isset('section_ids')) {
            foreach ($get('section_ids') as $index => $id) {
                $results = $addToSectionsById($results, $id, []);
            }
        }

        // Check Contents
        $contents = $this->checkContents($contents);

        // Cycle trough POST data
        // Map everything except properties with main_ or project_ preffix
        foreach ($contents as $key => $content) {

            if (preg_match('#([a-zA-Z-]+)_([0-9]+)$#', $key, $matches)) {
                $id = $matches[2];
                $type = $matches[1];
                // Sections

                $addValue = [
                    'id' => $id,
                    'type' => $type,
                    $keyType => $content
                ];

                $results = $addToSectionsById($results, $id, $addValue);

            } elseif (!preg_match('#main_#', $key) && !preg_match('#project_#', $key)) {
                if (!isset($results[$key])) {
                    $results[$key] = [];
                }

                $results[$key][$keyType] = $content;
            }
        }

        // Add sections properties with main_ or project_ preffix
        if ($isset('section_ids')) {
            foreach ($get('section_ids') as $index => $id) {
                $results = $addToSectionsById($results, $id, [
                    'title' => $get('slider_values')[$index],
                    'type' => $get('section_items')[$index],
                    'active' => in_array($id, $get('sections'))
                ]);
            }
        }

        if ($isset('menu_ids')) {
            $menu = [];

            foreach ($get('menu_ids') as $index => $id) {
                // Add menu items
                if (isset($get('menu_values')[$index])) {
                    $menu[] = [
                        'id' => $id,
                        'title' => $get('menu_values')[$index],
                        'menu_active' => in_array($id, (array)$get('active_menu_ids')),
                        'section_active' => in_array($id, $get('sections'))
                    ];
                }
            }

            $results['header']['content']['menu'] = $menu;
        }

        return $results;
    }

    /**
     * Saves custom colors used in color picker
     *
     * @param Portfolio $portfolio
     * @param array $colors
     * @return Portfolio
     */
    public function updateColorPicker(Portfolio $portfolio, Array $customColors)
    {
        $settings = $portfolio->unpublished_settings;

        $settings['globals']['content']['color_picker'] = $customColors;

        $portfolio->unpublished_settings = $settings;

        $portfolio->save();

        return $portfolio;
    }

    public function getPublicContent(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null, $useDefaultContent)
    {
        return $this->portfolioContentProvider
            ->setMergeStrategy(new PublicPortfolioStrategy())
            ->make($page, $portfolio, $theme, $useDefaultContent);
    }

    public function getThemePreviewContent(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null, $useDefaultContent)
    {
        return $this->portfolioContentProvider
            ->setMergeStrategy(new ThemePreviewStrategy())
            ->make($page, $portfolio, $theme, $useDefaultContent);
    }

    public function getPreviewContent(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null, $useDefaultContent)
    {
        return $this->portfolioContentProvider
            ->setMergeStrategy(new PreviewStrategy())
            ->make($page, $portfolio, $theme, $useDefaultContent);
    }

    public function getEditingContent(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null, $useDefaultContent)
    {
        $content = $this->portfolioContentProvider
            ->setMergeStrategy(new EditingStrategy())
            ->make($page, $portfolio, $theme, $useDefaultContent);

        $menuItems = $this->getNavigationLinks($portfolio, $content);

        $content['header']['content']['menu'] = $menuItems;

        return $content;
    }

    /**
     * @param PortfolioPage $portfolioPage
     * @param Portfolio $portfolio
     * @param $content
     * @param $settings
     * @return array|mixed
     */
    protected function mapUpdateRequest(PortfolioPage $portfolioPage, Portfolio $portfolio, $content, $settings)
    {
        $settingsResult = [];

        $contentResult = [];

        foreach ($content as $i => $field) {
            if (!isset($field['name'])) {
                dd($i);
            }

            $key = $field['name'];
            $value = @$field['value'];

            if (is_string($value) && starts_with($value, 'https://motionarray-')) {
                $value = preg_replace('/\?(.*)/', '', $value);
            }

            $isImage = isset($field['handle']) && $field['handle'] == 'image';
            $skip = isset($field['handle']) && $field['handle'] == 'skip';

            if ($skip) {
                $value = $this->getSettingsValue($portfolio, $portfolioPage, $key);
            } else {
                if ($value && $isImage) {
                    $upload = $this->uploadImage($portfolio, $key, $value);

                    $value = $upload->url;
                }
            }

            array_set($contentResult, $key, $value);
        }

        if ($settings) {
            foreach ($settings as $field) {
                $key = $field['name'];
                $value = @$field['value'];

                array_set($settingsResult, $key, $value);
            }
        }

        $results = ['sections' => []];

        $results = $this->mapSettings($results, $contentResult, 'content');

        $results = $this->mapSettings($results, $settingsResult, 'styles');

        return $results;
    }

    public function updateAllProjects($portfolio, $projectsSettings)
    {
        $projects = $portfolio->projects()->get();

        foreach ($projects as $project) {
            $page = $this->portfolioPage->findOrCreateByProject($project);

            $settings = $page->unpublished_settings;

            $sections = $settings['sections'];

            if ($sections) {
                $changed = 0;
                foreach ($sections as &$section) {
                    if (isset($section['type']) && $section['type'] == 'video') {
                        $section['styles'] = $projectsSettings;

                        $changed = 1;
                    }
                }

                if ($changed) {
                    $settings['sections'] = $sections;

                    $page->unpublished_settings = $settings;

                    $page->save();
                }
            }
        }
    }

    public function getSettingsValue($portfolio, $portfolioPage, $key)
    {
        $pageSettings = $portfolioPage->unpublished_settings;
        $sections = $pageSettings['sections'];

        $pathArr = explode('.', $key);
        $typeAndId = array_shift($pathArr);

        $path = implode('.', $pathArr);

        // On Section
        if (strpos($typeAndId, '_') !== false) {
            list($type, $id) = explode('_', $typeAndId);

            $ids = array_column($sections, 'id');

            $position = array_search($id, $ids);

            $value = array_get($sections[$position]['content'], $path);

        } // On Global Settings
        else {
            if (isset($portfolio->unpublished_settings[$typeAndId])) {
                $psContent = $portfolio->unpublished_settings[$typeAndId];

                if (isset($psContent['content']) && isset($psContent['content'][$path])) {
                    $value = $psContent['content'][$path];
                }
            }
        }

        return $value;
    }

    /**
     * Check save contents and set values if needed
     *
     * @param $contents
     * @return mixed
     */
    private function checkContents($contents)
    {
        foreach ($contents as $key => $content) {

            foreach ($content as $content_key => $value) {
                if ($content_key == 'itemPadding' && $value == '') {
                    $contents[$key][$content_key] = "0";
                }
            }
        }

        return $contents;
    }

    /**
     * @param Portfolio $portfolio
     * @param $content
     * @return array
     */
    protected function getNavigationLinks(Portfolio $portfolio, $content)
    {
        $menuItems = [];
        if (isset($content['header']['content']['menu'])) {
            $menuItems = (array)$content['header']['content']['menu'];
        }

        $menuItemIds = array_column($menuItems, 'id');

        $menuActiveItemIds = null;

        if (is_array($menuItems)) {
            $menuActiveItemIds = array_filter(array_map(function ($menuItem) {
                if (isset($menuItem['menu_active']) && $menuItem['menu_active']) {
                    return $menuItem['id'];
                }
            }, $menuItems));
        }

        $homePage = $portfolio->pages()->home()->first();

        /**
         * Add menu items if not there
         */
        if ($homePage) {
            $settings = $homePage->unpublished_settings;

            foreach ($settings['sections'] as $section) {
                $sectionActive = $section['active'];
                $menuActive = false;

                if (isset($section['id']) && !in_array($section['id'], $menuItemIds)) {
                    $menuActive = is_array($menuActiveItemIds) ? in_array($section['id'], $menuActiveItemIds) : $sectionActive;

                    $title = isset($section['title']) ? $section['title'] : ucwords($section['type']);

                    $menuItems[] = [
                        "id" => $section['id'],
                        "title" => $title,
                        "menu_active" => $menuActive,
                        "section_active" => $sectionActive
                    ];
                }
            }
        }

        // Unique id
        $ids = [];
        if (is_array($menuItems)) {
            foreach ($menuItems as &$menuItem) {
                if (in_array($menuItem['id'], $ids)) {
                    $menuItem = null;
                }
                $ids[] = $menuItem['id'];
            }

            $menuItems = array_filter($menuItems);
        }

        return $menuItems;
    }
}


