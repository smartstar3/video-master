<?php namespace MotionArray\Repositories;

use JesusRugama\Craft\CraftHelper;
use MotionArray\Helpers\Imgix;
use MotionArray\Helpers\ReplaceContent;
use Cache;

class PageRepository
{
    protected $craftHelper;

    public function __construct(CraftHelper $craftHelper)
    {
        $this->craftHelper = $craftHelper;
    }
    public function getHomeEntry()
    {
        // Cannot materialize CraftHelper::getHomeEntry() because of the random query,
        // instead we cache it for 10 minutes.
        $obj = $this;
        $originalFunction = function () use ($obj) {
            return $this->_getHomeEntry();
        };
        return config('cache.craft') ? Cache::remember("Craft-home_entry", 10, $originalFunction) : $originalFunction();
    }
    /**
     * Returns homepage elements from Craft
     * Cached version
     *
     * @return void
     */
    protected function _getHomeEntry()
    {

        $entry = $this->craftHelper->getHomeEntry();

        $welcomeHeaderBackground = $entry->welcomeHeaderBackgrounds->limit(1)->orderBy("RAND()")->one();

        $producerTestimonials = $entry->producerTestimonials;
        $producerTestimonials = $this->craftHelper->materialize($producerTestimonials);

        return (object)[
            'welcomeHeaderTitle' => $entry->welcomeHeaderTitle,
            'welcomeHeaderContent' => $entry->welcomeHeaderContent,
            'welcomeHeaderFooter' => ReplaceContent::productsCount($entry->welcomeHeaderFooter),
            'welcomeHeaderVideoUrl' => $entry->welcomeHeaderVideoUrl,
            'welcomeHeaderBackground' => $welcomeHeaderBackground->getUrl("welcomeHeaderBackground"),
            'featuresSlider' => $entry->featuresSlider,
            'producerTestimonials' => $producerTestimonials,
            'seoTitle' => $entry->seoTitle,
            'seoDescription' => $entry->seoDescription,
            'title' => $entry->title,
            'slug' => $entry->slug,
        ];
    }

    public function getPageByURI($uri)
    {
        return $this->craftHelper->getPageByURI($uri);
    }

    public function getPageBySlug($slug)
    {
        return $this->craftHelper->getPageBySlug($slug);
    }

    public function getEntriesBySection($section)
    {
        return $this->craftHelper->getEntriesBySection($section);
    }
}
