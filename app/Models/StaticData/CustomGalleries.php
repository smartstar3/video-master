<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\CustomGallery;

class CustomGalleries extends StaticDBData
{
    public const ADOBE_PREMIERE_TEMPLATES = 'adobe-premiere-templates';
    public const ADOBE_PREMIERE_TEMPLATES_ID = 1;

    public const PREMIERE_PRO_TEMPLATES = 'premiere-pro-templates';
    public const PREMIERE_PRO_TEMPLATES_ID = 2;

    public const PREMIERE_TEMPLATES = 'premiere-templates';
    public const PREMIERE_TEMPLATES_ID = 3;

    public const ADOBE_PREMIERE_PRO_TEMPLATES = 'adobe-premiere-pro-templates';
    public const ADOBE_PREMIERE_PRO_TEMPLATES_ID = 4;

    public const PREMIERE_TRANSITIONS = 'premiere-transitions';
    public const PREMIERE_TRANSITIONS_ID = 5;

    public const ADOBE_PREMIERE_TRANSITIONS = 'adobe-premiere-transitions';
    public const ADOBE_PREMIERE_TRANSITIONS_ID = 6;

    public const PREMIERE_PRO_TRANSITIONS = 'premiere-pro-transitions';
    public const PREMIERE_PRO_TRANSITIONS_ID = 7;

    public const PREMIERE_PRO_TRANSITIONS_PACK = 'premiere-pro-transitions-pack';
    public const PREMIERE_PRO_TRANSITIONS_PACK_ID = 8;

    public const ADOBE_PREMIERE_PRO_TRANSITIONS = 'adobe-premiere-pro-transitions';
    public const ADOBE_PREMIERE_PRO_TRANSITIONS_ID = 9;

    public const VIDEO_TRANSITIONS_PREMIERE_PRO = 'video-transitions-premiere-pro';
    public const VIDEO_TRANSITIONS_PREMIERE_PRO_ID = 10;

    public const ADOBE_PREMIERE_VIDEO_TRANSITIONS = 'adobe-premiere-video-transitions';
    public const ADOBE_PREMIERE_VIDEO_TRANSITIONS_ID = 11;

    public const PREMIERE_PRO_TRANSITION_PRESETS = 'premiere-pro-transition-presets';
    public const PREMIERE_PRO_TRANSITION_PRESETS_ID = 12;

    public const PREMIERE_PRO_CC_TRANSITIONS = 'premiere-pro-cc-transitions';
    public const PREMIERE_PRO_CC_TRANSITIONS_ID = 13;

    public const ADOBE_PREMIERE_TRANSITIONS_PACK = 'adobe-premiere-transitions-pack';
    public const ADOBE_PREMIERE_TRANSITIONS_PACK_ID = 14;

    public const AFTER_EFFECTS_TEMPLATES = 'after-effects-templates';
    public const AFTER_EFFECTS_TEMPLATES_ID = 15;

    public const AE_TEMPLATES = 'ae-templates';
    public const AE_TEMPLATES_ID = 16;

    public const ADOBE_AFTER_EFFECTS_TEMPLATES = 'adobe-after-effects-templates';
    public const ADOBE_AFTER_EFFECTS_TEMPLATES_ID = 17;

    public const STOCK_FOOTAGE = 'stock-footage';
    public const STOCK_FOOTAGE_ID = 18;

    public const STOCK_VIDEO = 'stock-video';
    public const STOCK_VIDEO_ID = 19;

    public const STOCK_VIDEO_FOOTAGE = 'stock-video-footage';
    public const STOCK_VIDEO_FOOTAGE_ID = 20;

    public const ROYALTY_FREE_VIDEO = 'royalty-free-video';
    public const ROYALTY_FREE_VIDEO_ID = 21;

    public const ROYALTY_FREE_FOOTAGE = 'royalty-free-footage';
    public const ROYALTY_FREE_FOOTAGE_ID = 22;

    public const ROYALTY_FREE_STOCK_VIDEO = 'royalty-free-stock-video';
    public const ROYALTY_FREE_STOCK_VIDEO_ID = 23;

    public const ROYALTY_FREE_STOCK_FOOTAGE = 'royalty-free-stock-footage';
    public const ROYALTY_FREE_STOCK_FOOTAGE_ID = 24;

    public const ROYALTY_FREE_STOCK_VIDEO_FOOTAGE = 'royalty-free-stock-video-footage';
    public const ROYALTY_FREE_STOCK_VIDEO_FOOTAGE_ID = 25;

    public const AFTER_EFFECTS_INTROS = 'after-effects-intros';
    public const AFTER_EFFECTS_INTROS_ID = 26;

    public const AFTER_EFFECTS_INTRO_TEMPLATES = 'after-effects-intro-templates';
    public const AFTER_EFFECTS_INTRO_TEMPLATES_ID = 27;

    public const AFTER_EFFECTS_TRANSITIONS = 'after-effects-transitions';
    public const AFTER_EFFECTS_TRANSITIONS_ID = 28;

    public const AFTER_EFFECTS_LOGOS = 'after-effects-logos';
    public const AFTER_EFFECTS_LOGOS_ID = 29;

    public const LOGO_ANIMATIONS_FOR_AFTER_EFFECTS = 'logo-animations-for-after-effects';
    public const LOGO_ANIMATIONS_FOR_AFTER_EFFECTS_ID = 30;

    public const AFTER_EFFECTS_LOGO_TEMPLATES = 'after-effects-logo-templates';
    public const AFTER_EFFECTS_LOGO_TEMPLATES_ID = 31;

    public const LOGO_REVEALS_FOR_AFTER_EFFECTS = 'logo-reveals-for-after-effects';
    public const LOGO_REVEALS_FOR_AFTER_EFFECTS_ID = 32;

    public const AFTER_EFFECTS_TITLES = 'after-effects-titles';
    public const AFTER_EFFECTS_TITLES_ID = 33;

    public const AFTER_EFFECTS_TITLE_TEMPLATES = 'after-effects-title-templates';
    public const AFTER_EFFECTS_TITLE_TEMPLATES_ID = 34;

    public const AFTER_EFFECTS_SLIDESHOWS = 'after-effects-slideshows';
    public const AFTER_EFFECTS_SLIDESHOWS_ID = 35;

    public const AFTER_EFFECTS_SLIDESHOW_TEMPLATES = 'after-effects-slideshow-templates';
    public const AFTER_EFFECTS_SLIDESHOW_TEMPLATES_ID = 36;

    public const AFTER_EFFECTS_PHOTO_TEMPLATES = 'after-effects-photo-templates';
    public const AFTER_EFFECTS_PHOTO_TEMPLATES_ID = 37;

    public const LOWER_THIRDS_FOR_AFTER_EFFECTS = 'lower-thirds-for-after-effects';
    public const LOWER_THIRDS_FOR_AFTER_EFFECTS_ID = 38;

    protected $modelClass = CustomGallery::class;

    protected $data = [];

    protected function prepareData(): array
    {
        $data = array_merge(
            $this->data,
            $this->afterEffectsTemplates(),
            $this->premierTransitions(),
            $this->premierTemplates(),
            $this->stockFootage(),
            $this->aeIntro(),
            $this->aeTransitions(),
            $this->aeLogo(),
            $this->aeTitles(),
            $this->aeSlideshow(),
            $this->aeLowerThird()
        );
        return collect($data)
            ->keyBy('id')
            ->toArray();
    }

    protected function afterEffectsTemplates()
    {
        $data = [
            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES,
                'title_text' => 'After Effects Templates',
                'see_more_href' => '/browse/premiere-pro-presets/transitions?sort_by=production_products_by_downloads&date_added=last-6-months&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_TEMPLATES,
            ],
            [
                'id' => self::AE_TEMPLATES_ID,
                'slug' => self::AE_TEMPLATES,
                'title_text' => 'AE Templates',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=after-effects-templates,after-effects-presets&pass_through_custom_gallery_slug=' . self::AE_TEMPLATES,
            ],
            [
                'id' => self::ADOBE_AFTER_EFFECTS_TEMPLATES_ID,
                'slug' => self::ADOBE_AFTER_EFFECTS_TEMPLATES,
                'title_text' => 'Adobe After Effects Templates',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=after-effects-templates,after-effects-presets&pass_through_custom_gallery_slug=' . self::ADOBE_AFTER_EFFECTS_TEMPLATES,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::ADOBE_AFTER_EFFECTS_TEMPLATES,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited After Effects Templates Today',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/custom+gallery+hero+bg.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function premierTransitions()
    {
        $data = [
            [
                'id' => self::PREMIERE_TRANSITIONS_ID,
                'slug' => self::PREMIERE_TRANSITIONS,
                'title_text' => 'Premiere Transitions',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::PREMIERE_TRANSITIONS,
            ],
            [
                'id' => self::ADOBE_PREMIERE_TRANSITIONS_ID,
                'slug' => self::ADOBE_PREMIERE_TRANSITIONS,
                'title_text' => 'Adobe Premiere Transitions',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::ADOBE_PREMIERE_TRANSITIONS,
            ],
            [
                'id' => self::PREMIERE_PRO_TRANSITIONS_ID,
                'slug' => self::PREMIERE_PRO_TRANSITIONS,
                'title_text' => 'Premiere Pro Transitions',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::PREMIERE_PRO_TRANSITIONS,
            ],
            [
                'id' => self::PREMIERE_PRO_TRANSITIONS_PACK_ID,
                'slug' => self::PREMIERE_PRO_TRANSITIONS_PACK,
                'title_text' => 'Premiere Pro Transitions Pack',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::PREMIERE_PRO_TRANSITIONS_PACK,
            ],
            [
                'id' => self::ADOBE_PREMIERE_PRO_TRANSITIONS_ID,
                'slug' => self::ADOBE_PREMIERE_PRO_TRANSITIONS,
                'title_text' => 'Adobe Premiere Pro Transitions',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::ADOBE_PREMIERE_PRO_TRANSITIONS,
            ],
            [
                'id' => self::VIDEO_TRANSITIONS_PREMIERE_PRO_ID,
                'slug' => self::VIDEO_TRANSITIONS_PREMIERE_PRO,
                'title_text' => 'Video Transitions Premiere Pro',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::VIDEO_TRANSITIONS_PREMIERE_PRO,
            ],
            [
                'id' => self::ADOBE_PREMIERE_VIDEO_TRANSITIONS_ID,
                'slug' => self::ADOBE_PREMIERE_VIDEO_TRANSITIONS,
                'title_text' => 'Adobe Premiere Video Transitions',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::ADOBE_PREMIERE_VIDEO_TRANSITIONS,
            ],
            [
                'id' => self::PREMIERE_PRO_TRANSITION_PRESETS_ID,
                'slug' => self::PREMIERE_PRO_TRANSITION_PRESETS,
                'title_text' => 'Premiere Pro Transition Presets',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::PREMIERE_PRO_TRANSITION_PRESETS,
            ],
            [
                'id' => self::PREMIERE_PRO_CC_TRANSITIONS_ID,
                'slug' => self::PREMIERE_PRO_CC_TRANSITIONS,
                'title_text' => 'Premiere Pro CC Transitions',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::PREMIERE_PRO_CC_TRANSITIONS,
            ],
            [
                'id' => self::ADOBE_PREMIERE_TRANSITIONS_PACK_ID,
                'slug' => self::ADOBE_PREMIERE_TRANSITIONS_PACK,
                'title_text' => 'Adobe Premiere Transitions Pack',
                'see_more_href' => '/browse/premiere-pro-templates?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates:transitions&pass_through_custom_gallery_slug=' . self::ADOBE_PREMIERE_TRANSITIONS_PACK,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::ADOBE_PREMIERE_PRO_TRANSITIONS,
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited Premiere Pro Transitions Today',
                    'heading_text' => 'Download Unlimited',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/custom+gallery+hero+bg+2.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function premierTemplates()
    {
        $data = [
            [
                'id' => self::ADOBE_PREMIERE_TEMPLATES_ID,
                'slug' => self::ADOBE_PREMIERE_TEMPLATES,
                'title_text' => 'Adobe Premiere Templates',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates&pass_through_custom_gallery_slug=' . self::ADOBE_PREMIERE_TEMPLATES,
            ],
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES,
                'title_text' => 'Premiere Pro Templates',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates&pass_through_custom_gallery_slug=' . self::PREMIERE_PRO_TEMPLATES,
            ],
            [
                'id' => self::PREMIERE_TEMPLATES_ID,
                'slug' => self::PREMIERE_TEMPLATES,
                'title_text' => 'Premiere Templates',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates&pass_through_custom_gallery_slug=' . self::PREMIERE_TEMPLATES,
            ],
            [
                'id' => self::ADOBE_PREMIERE_PRO_TEMPLATES_ID,
                'slug' => self::ADOBE_PREMIERE_PRO_TEMPLATES,
                'title_text' => 'Adobe Premiere Pro Templates',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=premiere-pro-templates&pass_through_custom_gallery_slug=' . self::ADOBE_PREMIERE_PRO_TEMPLATES,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::ADOBE_PREMIERE_PRO_TEMPLATES,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited After Effects Templates Today',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/custom+gallery+hero+bg.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function stockFootage()
    {
        $stockData = [
            [
                'id' => self::STOCK_FOOTAGE_ID,
                'slug' => self::STOCK_FOOTAGE,
                'title_text' => 'Stock Footage',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=stock-video&pass_through_custom_gallery_slug=' . self::STOCK_FOOTAGE,
            ],
            [
                'id' => self::STOCK_VIDEO_ID,
                'slug' => self::STOCK_VIDEO,
                'title_text' => 'Stock Video',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=stock-video&pass_through_custom_gallery_slug=' . self::STOCK_VIDEO,
            ],
            [
                'id' => self::STOCK_VIDEO_FOOTAGE_ID,
                'slug' => self::STOCK_VIDEO_FOOTAGE,
                'title_text' => 'Stock Video Footage',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=stock-video&pass_through_custom_gallery_slug=' . self::STOCK_VIDEO_FOOTAGE,
            ],
            [
                'id' => self::ROYALTY_FREE_VIDEO_ID,
                'slug' => self::ROYALTY_FREE_VIDEO,
                'title_text' => 'Royalty Free Video',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=stock-video&pass_through_custom_gallery_slug=' . self::ROYALTY_FREE_VIDEO,
            ],
            [
                'id' => self::ROYALTY_FREE_FOOTAGE_ID,
                'slug' => self::ROYALTY_FREE_FOOTAGE,
                'title_text' => 'Royalty Free Footage',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=stock-video&pass_through_custom_gallery_slug=' . self::ROYALTY_FREE_FOOTAGE,
            ],
            [
                'id' => self::ROYALTY_FREE_STOCK_VIDEO_ID,
                'slug' => self::ROYALTY_FREE_STOCK_VIDEO,
                'title_text' => 'Royalty Free Stock Video',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=stock-video&pass_through_custom_gallery_slug=' . self::ROYALTY_FREE_STOCK_VIDEO,
            ],
            [
                'id' => self::ROYALTY_FREE_STOCK_FOOTAGE_ID,
                'slug' => self::ROYALTY_FREE_STOCK_FOOTAGE,
                'title_text' => 'Royalty Free Stock Footage',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=stock-video&pass_through_custom_gallery_slug=' . self::ROYALTY_FREE_STOCK_FOOTAGE,
            ],
            [
                'id' => self::ROYALTY_FREE_STOCK_VIDEO_FOOTAGE_ID,
                'slug' => self::ROYALTY_FREE_STOCK_VIDEO_FOOTAGE,
                'title_text' => 'Royalty Free Stock Video Footage',
                'see_more_href' => '/browse?sort_by=production_products_by_downloads&date_added=last-6-months&categories=stock-video&pass_through_custom_gallery_slug=' . self::ROYALTY_FREE_STOCK_VIDEO_FOOTAGE,
            ],
        ];

        return collect($stockData)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::STOCK_FOOTAGE,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited Stock Footage Today',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/custom+gallery+hero+bg+3.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function aeIntro()
    {
        $aeIntroData = [
            [
                'id' => self::AFTER_EFFECTS_INTROS_ID,
                'slug' => self::AFTER_EFFECTS_INTROS,
                'title_text' => 'After Effects Intros',
                'see_more_href' => 'https://motionarray.com/browse?sort_by=production_products_by_downloads&q=intro&date_added=last-6-months&categories=after-effects-templates,after-effects-presets&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_INTROS,
            ],
            [
                'id' => self::AFTER_EFFECTS_INTRO_TEMPLATES_ID,
                'slug' => self::AFTER_EFFECTS_INTRO_TEMPLATES,
                'title_text' => 'After Effects Intro Templates',
                'see_more_href' => 'https://motionarray.com/browse?sort_by=production_products_by_downloads&q=intro&date_added=last-6-months&categories=after-effects-templates,after-effects-presets&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_INTRO_TEMPLATES,
            ],
        ];

        return collect($aeIntroData)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::AE_INTRO,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited After Effects Intro Templates Today',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/Additional+website+header+-+AE+Intro.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function aeTransitions()
    {
        $aeTransitionsData = [
            [
                'id' => self::AFTER_EFFECTS_TRANSITIONS_ID,
                'slug' => self::AFTER_EFFECTS_TRANSITIONS,
                'title_text' => 'After Effects Transitions',
                'see_more_href' => 'https://motionarray.com/browse?sort_by=production_products_by_downloads&q=intro&date_added=last-6-months&categories=after-effects-templates,after-effects-presets&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_TRANSITIONS,
            ],
        ];

        return collect($aeTransitionsData)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::AE_TRANSITIONS,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited After Effects Transitions Today',
                    'see_more_href' => 'https://motionarray.com/browse?sort_by=production_products_by_downloads&date_added=last-year&categories=after-effects-templates:transitions,after-effects-presets:transitions&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_INTROS,
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/Additional+website+header+-+Transitions.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function aeLogo()
    {
        $aeLogoData = [
            [
                'id' => self::AFTER_EFFECTS_LOGOS_ID,
                'slug' => self::AFTER_EFFECTS_LOGOS,
                'title_text' => 'After Effects Logos',
                'footer_text' => 'Start Downloading Unlimited After Effects Logos Today',
                'see_more_href' => 'https://motionarray.com/browse/after-effects-templates?sort_by=production_products_by_downloads&q=logo&date_added=last-6-months&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_LOGOS,
            ],
            [
                'id' => self::LOGO_ANIMATIONS_FOR_AFTER_EFFECTS_ID,
                'slug' => self::LOGO_ANIMATIONS_FOR_AFTER_EFFECTS,
                'title_text' => 'Logo Animations For After Effects',
                'footer_text' => 'Start Downloading Unlimited Logo Animations For After Effects Today',
                'see_more_href' => 'https://motionarray.com/browse/after-effects-templates?sort_by=production_products_by_downloads&q=logo&date_added=last-6-months&pass_through_custom_gallery_slug=' . self::LOGO_ANIMATIONS_FOR_AFTER_EFFECTS,
            ],
            [
                'id' => self::AFTER_EFFECTS_LOGO_TEMPLATES_ID,
                'slug' => self::AFTER_EFFECTS_LOGO_TEMPLATES,
                'title_text' => 'After Effects Logo Templates',
                'footer_text' => 'Start Downloading Unlimited After Effects Logo Templates Today',
                'see_more_href' => 'https://motionarray.com/browse/after-effects-templates?sort_by=production_products_by_downloads&q=logo&date_added=last-6-months&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_LOGO_TEMPLATES,
            ],
            [
                'id' => self::LOGO_REVEALS_FOR_AFTER_EFFECTS_ID,
                'slug' => self::LOGO_REVEALS_FOR_AFTER_EFFECTS,
                'title_text' => 'Logo Reveals for After Effects',
                'footer_text' => 'Start Downloading Unlimited Logo Reveals for After Effects Today',
                'see_more_href' => 'https://motionarray.com/browse/after-effects-templates?sort_by=production_products_by_downloads&q=logo&date_added=last-6-months&pass_through_custom_gallery_slug=' . self::LOGO_REVEALS_FOR_AFTER_EFFECTS,
            ],
        ];

        return collect($aeLogoData)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::AE_LOGO,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/Additional+website+header+-+Logo.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function aeTitles()
    {
        $aeTitlesData = [
            [
                'id' => self::AFTER_EFFECTS_TITLES_ID,
                'slug' => self::AFTER_EFFECTS_TITLES,
                'title_text' => 'After Effects Titles',
                'see_more_href' => 'https://motionarray.com/browse?sort_by=production_products_by_downloads&q=intro&date_added=last-6-months&categories=after-effects-templates,after-effects-presets&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_TITLES,
            ],
            [
                'id' => self::AFTER_EFFECTS_TITLE_TEMPLATES_ID,
                'slug' => self::AFTER_EFFECTS_TITLE_TEMPLATES,
                'title_text' => 'After Effects Title Templates',
                'see_more_href' => 'https://motionarray.com/browse?sort_by=production_products_by_downloads&q=title&date_added=last-6-months&categories=after-effects-templates,after-effects-presets&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_TITLE_TEMPLATES,
            ],
        ];

        return collect($aeTitlesData)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::AE_TITLES,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited After Effects Titles Today',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/Additional+website+header+-+Title.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function aeSlideshow()
    {
        $aeSlideShowData = [

            [
                'id' => self::AFTER_EFFECTS_SLIDESHOWS_ID,
                'slug' => self::AFTER_EFFECTS_SLIDESHOWS,
                'title_text' => 'After Effects Slideshows',
                'footer_text' => 'Start Downloading Unlimited After Effects Slideshows Today',
                'see_more_href' => 'https://motionarray.com/browse/after-effects-templates?sort_by=production_products_by_downloads&q=slideshow&date_added=last-6-months&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_SLIDESHOWS,
            ],
            [
                'id' => self::AFTER_EFFECTS_SLIDESHOW_TEMPLATES_ID,
                'slug' => self::AFTER_EFFECTS_SLIDESHOW_TEMPLATES,
                'title_text' => 'After Effects Slideshow Templates',
                'footer_text' => 'Start Downloading Unlimited After Effects Slideshow Templates Today',
                'see_more_href' => 'https://motionarray.com/browse/after-effects-templates?sort_by=production_products_by_downloads&q=slideshow&date_added=last-6-months&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_SLIDESHOW_TEMPLATES,
            ],
            [
                'id' => self::AFTER_EFFECTS_PHOTO_TEMPLATES_ID,
                'slug' => self::AFTER_EFFECTS_PHOTO_TEMPLATES,
                'title_text' => 'After Effects Photo Templates',
                'footer_text' => 'Start Downloading Unlimited After Effects Photo Templates Today',
                'see_more_href' => 'https://motionarray.com/browse/after-effects-templates?sort_by=production_products_by_downloads&q=slideshow&date_added=last-6-months&pass_through_custom_gallery_slug=' . self::AFTER_EFFECTS_PHOTO_TEMPLATES,
            ],
        ];

        return collect($aeSlideShowData)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::AE_SLIDESHOW,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited After Effects Intro Templates Today',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/Additional+website+header+-+Slideshow.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }

    protected function aeLowerThird()
    {
        $aeIntroData = [
            [
                'id' => self:: LOWER_THIRDS_FOR_AFTER_EFFECTS_ID,
                'slug' => self:: LOWER_THIRDS_FOR_AFTER_EFFECTS,
                'title_text' => 'Lower Thirds for After Effects',
                'footer_text' => 'Start Downloading Unlimited Lower Thirds for After Effects Today',
                'see_more_href' => 'https://motionarray.com/browse?sort_by=production_products_by_downloads&q=lower,third&categories=after-effects-templates,after-effects-presets&pass_through_custom_gallery_slug=' . self::LOWER_THIRDS_FOR_AFTER_EFFECTS,
            ],
        ];

        return collect($aeIntroData)
            ->map(function ($row) {
                return array_merge($row, [
                    'collection_slug' => Collections::AE_LOWER_THIRD,
                    'heading_text' => 'Download Unlimited',
                    'sub_title_text' => 'Over 100,000 Video Assets Included',
                    'footer_text' => 'Start Downloading Unlimited After Effects Intro Templates Today',
                    'call_to_action_button_text' => 'Join Free',
                    'call_to_action_button_href' => '/pricing',
                    'css' => "#custom-gallery {--hero-background-img: url('https://motionarray-content.imgix.net/custom-galleries/Additional+website+header+-+Lower+thirds.png?h=380');}",
                    'active' => 1,
                ]);
            })
            ->toArray();
    }
}
