<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\SubCategory;

class SubCategories extends StaticDBData
{
    public const AFTER_EFFECTS_TEMPLATES_PHOTO_VIDEO = 'photo-video';
    public const AFTER_EFFECTS_TEMPLATES_PHOTO_VIDEO_ID = 17;

    public const AFTER_EFFECTS_TEMPLATES_TEXT = 'text';
    public const AFTER_EFFECTS_TEMPLATES_TEXT_ID = 18;

    public const AFTER_EFFECTS_TEMPLATES_LOGOS = 'logos';
    public const AFTER_EFFECTS_TEMPLATES_LOGOS_ID = 20;

    public const AFTER_EFFECTS_TEMPLATES_LOWER_THIRDS = 'lower-thirds';
    public const AFTER_EFFECTS_TEMPLATES_LOWER_THIRDS_ID = 21;

    public const AFTER_EFFECTS_TEMPLATES_TRANSITIONS = 'transitions';
    public const AFTER_EFFECTS_TEMPLATES_TRANSITIONS_ID = 22;

    public const AFTER_EFFECTS_TEMPLATES_OTHER = 'other';
    public const AFTER_EFFECTS_TEMPLATES_OTHER_ID = 23;

    public const AFTER_EFFECTS_TEMPLATES_FREE = 'free';
    public const AFTER_EFFECTS_TEMPLATES_FREE_ID = 39;

    public const AFTER_EFFECTS_TEMPLATES_PRESETS = 'presets';
    public const AFTER_EFFECTS_TEMPLATES_PRESETS_ID = 83;

    public const STOCK_VIDEO_INK = 'ink';
    public const STOCK_VIDEO_INK_ID = 4;

    public const STOCK_VIDEO_FIRE = 'fire';
    public const STOCK_VIDEO_FIRE_ID = 5;

    public const STOCK_VIDEO_DUST = 'dust';
    public const STOCK_VIDEO_DUST_ID = 6;

    public const STOCK_VIDEO_PAINT = 'paint';
    public const STOCK_VIDEO_PAINT_ID = 7;

    public const STOCK_VIDEO_SMOKE = 'smoke';
    public const STOCK_VIDEO_SMOKE_ID = 8;

    public const STOCK_VIDEO_SNOW = 'snow';
    public const STOCK_VIDEO_SNOW_ID = 9;

    public const STOCK_VIDEO_CLOUDS = 'clouds';
    public const STOCK_VIDEO_CLOUDS_ID = 10;

    public const STOCK_VIDEO_PAPER = 'paper';
    public const STOCK_VIDEO_PAPER_ID = 11;

    public const STOCK_VIDEO_LIGHT = 'light';
    public const STOCK_VIDEO_LIGHT_ID = 12;

    public const STOCK_VIDEO_SPARKS = 'sparks';
    public const STOCK_VIDEO_SPARKS_ID = 13;

    public const STOCK_VIDEO_WATER = 'water';
    public const STOCK_VIDEO_WATER_ID = 14;

    public const STOCK_VIDEO_OIL = 'oil';
    public const STOCK_VIDEO_OIL_ID = 42;

    public const STOCK_VIDEO_PARTICLES = 'particles';
    public const STOCK_VIDEO_PARTICLES_ID = 45;

    public const STOCK_VIDEO_OVERLAY = 'overlay';
    public const STOCK_VIDEO_OVERLAY_ID = 74;

    public const STOCK_VIDEO_BACKGROUND = 'background';
    public const STOCK_VIDEO_BACKGROUND_ID = 75;

    public const STOCK_VIDEO_PEOPLE = 'people';
    public const STOCK_VIDEO_PEOPLE_ID = 104;

    public const STOCK_VIDEO_TECHNOLOGY = 'technology';
    public const STOCK_VIDEO_TECHNOLOGY_ID = 105;

    public const STOCK_VIDEO_GREEN_SCREEN = 'green-screen';
    public const STOCK_VIDEO_GREEN_SCREEN_ID = 106;

    public const STOCK_VIDEO_BUSINESS = 'business';
    public const STOCK_VIDEO_BUSINESS_ID = 107;

    public const STOCK_VIDEO_NATURE = 'nature';
    public const STOCK_VIDEO_NATURE_ID = 108;

    public const STOCK_VIDEO_BUILDINGS = 'buildings';
    public const STOCK_VIDEO_BUILDINGS_ID = 109;

    public const STOCK_VIDEO_HEALTH = 'health';
    public const STOCK_VIDEO_HEALTH_ID = 110;

    public const STOCK_VIDEO_FASHION = 'fashion';
    public const STOCK_VIDEO_FASHION_ID = 111;

    public const STOCK_VIDEO_ANIMALS = 'animals';
    public const STOCK_VIDEO_ANIMALS_ID = 112;

    public const STOCK_VIDEO_FOOD = 'food';
    public const STOCK_VIDEO_FOOD_ID = 113;

    public const STOCK_VIDEO_TRANSPORTATION = 'transportation';
    public const STOCK_VIDEO_TRANSPORTATION_ID = 114;

    public const STOCK_VIDEO_SPORTS = 'sports';
    public const STOCK_VIDEO_SPORTS_ID = 115;

    public const STOCK_VIDEO_AERIAL = 'aerial';
    public const STOCK_VIDEO_AERIAL_ID = 116;

    public const STOCK_VIDEO_HOLIDAYS = 'holidays';
    public const STOCK_VIDEO_HOLIDAYS_ID = 117;

    public const STOCK_VIDEO_INDUSTRIAL = 'industrial';
    public const STOCK_VIDEO_INDUSTRIAL_ID = 118;

    public const STOCK_VIDEO_HOUSEHOLD = 'household';
    public const STOCK_VIDEO_HOUSEHOLD_ID = 119;

    public const STOCK_VIDEO_SCIENCE = 'science';
    public const STOCK_VIDEO_SCIENCE_ID = 120;

    public const STOCK_VIDEO_EDUCATION = 'education';
    public const STOCK_VIDEO_EDUCATION_ID = 121;

    public const STOCK_VIDEO_TRAVEL = 'travel';
    public const STOCK_VIDEO_TRAVEL_ID = 136;

    public const STOCK_VIDEO_FREE = 'free';
    public const STOCK_VIDEO_FREE_ID = 147;

    public const STOCK_VIDEO_URBAN = 'urban';
    public const STOCK_VIDEO_URBAN_ID = 177;

    public const STOCK_MOTION_GRAPHICS_BACKGROUNDS = 'backgrounds';
    public const STOCK_MOTION_GRAPHICS_BACKGROUNDS_ID = 24;

    public const STOCK_MOTION_GRAPHICS_LOWER_THIRDS = 'lower-thirds';
    public const STOCK_MOTION_GRAPHICS_LOWER_THIRDS_ID = 25;

    public const STOCK_MOTION_GRAPHICS_TRANSITIONS = 'transitions';
    public const STOCK_MOTION_GRAPHICS_TRANSITIONS_ID = 26;

    public const STOCK_MOTION_GRAPHICS_FRAMING_DEVICES = 'framing-devices';
    public const STOCK_MOTION_GRAPHICS_FRAMING_DEVICES_ID = 27;

    public const STOCK_MOTION_GRAPHICS_OVERLAYS = 'overlays';
    public const STOCK_MOTION_GRAPHICS_OVERLAYS_ID = 28;

    public const STOCK_MOTION_GRAPHICS_OTHER = 'other';
    public const STOCK_MOTION_GRAPHICS_OTHER_ID = 103;

    public const STOCK_MOTION_GRAPHICS_FREE = 'free';
    public const STOCK_MOTION_GRAPHICS_FREE_ID = 150;

    public const STOCK_MUSIC_EASY_LISTENING = 'easy-listening';
    public const STOCK_MUSIC_EASY_LISTENING_ID = 30;

    public const STOCK_MUSIC_CORPORATE = 'corporate';
    public const STOCK_MUSIC_CORPORATE_ID = 31;

    public const STOCK_MUSIC_ROCK = 'rock';
    public const STOCK_MUSIC_ROCK_ID = 32;

    public const STOCK_MUSIC_POP = 'pop';
    public const STOCK_MUSIC_POP_ID = 33;

    public const STOCK_MUSIC_INSPIRATIONAL = 'inspirational';
    public const STOCK_MUSIC_INSPIRATIONAL_ID = 34;

    public const STOCK_MUSIC_PIANO = 'piano';
    public const STOCK_MUSIC_PIANO_ID = 36;

    public const STOCK_MUSIC_CLASSICAL = 'classical';
    public const STOCK_MUSIC_CLASSICAL_ID = 37;

    public const STOCK_MUSIC_ELECTRONIC = 'electronic';
    public const STOCK_MUSIC_ELECTRONIC_ID = 47;

    public const STOCK_MUSIC_TRAILERS = 'trailers';
    public const STOCK_MUSIC_TRAILERS_ID = 49;

    public const STOCK_MUSIC_ACTION = 'action';
    public const STOCK_MUSIC_ACTION_ID = 50;

    public const STOCK_MUSIC_HEAVY = 'heavy';
    public const STOCK_MUSIC_HEAVY_ID = 51;

    public const STOCK_MUSIC_METAL = 'metal';
    public const STOCK_MUSIC_METAL_ID = 52;

    public const STOCK_MUSIC_PEACEFUL = 'peaceful';
    public const STOCK_MUSIC_PEACEFUL_ID = 53;

    public const STOCK_MUSIC_FUN = 'fun';
    public const STOCK_MUSIC_FUN_ID = 54;

    public const STOCK_MUSIC_UPBEAT = 'upbeat';
    public const STOCK_MUSIC_UPBEAT_ID = 56;

    public const STOCK_MUSIC_DANCE = 'dance';
    public const STOCK_MUSIC_DANCE_ID = 57;

    public const STOCK_MUSIC_HIP_HOP = 'hip-hop';
    public const STOCK_MUSIC_HIP_HOP_ID = 58;

    public const STOCK_MUSIC_HOLIDAY = 'holiday';
    public const STOCK_MUSIC_HOLIDAY_ID = 60;

    public const STOCK_MUSIC_LOGOS = 'logos';
    public const STOCK_MUSIC_LOGOS_ID = 61;

    public const STOCK_MUSIC_FOLK = 'folk';
    public const STOCK_MUSIC_FOLK_ID = 62;

    public const STOCK_MUSIC_ACOUSTIC = 'acoustic';
    public const STOCK_MUSIC_ACOUSTIC_ID = 63;

    public const STOCK_MUSIC_COUNTRY = 'country';
    public const STOCK_MUSIC_COUNTRY_ID = 64;

    public const STOCK_MUSIC_JAZZ = 'jazz';
    public const STOCK_MUSIC_JAZZ_ID = 65;

    public const STOCK_MUSIC_FILM_SCORE = 'film-score';
    public const STOCK_MUSIC_FILM_SCORE_ID = 66;

    public const STOCK_MUSIC_CHILDREN = 'children';
    public const STOCK_MUSIC_CHILDREN_ID = 67;

    public const STOCK_MUSIC_SOUL = 'soul';
    public const STOCK_MUSIC_SOUL_ID = 68;

    public const STOCK_MUSIC_FUNK = 'funk';
    public const STOCK_MUSIC_FUNK_ID = 70;

    public const STOCK_MUSIC_EXPERIMENTAL = 'experimental';
    public const STOCK_MUSIC_EXPERIMENTAL_ID = 71;

    public const STOCK_MUSIC_WORLD = 'world';
    public const STOCK_MUSIC_WORLD_ID = 72;

    public const STOCK_MUSIC_MUSIC_KITS = 'music-kits';
    public const STOCK_MUSIC_MUSIC_KITS_ID = 73;

    public const STOCK_MUSIC_FREE = 'free';
    public const STOCK_MUSIC_FREE_ID = 85;

    public const STOCK_MUSIC_AMBIENT = 'ambient';
    public const STOCK_MUSIC_AMBIENT_ID = 157;

    public const PREMIERE_PRO_TEMPLATES_EDITS = 'edits';
    public const PREMIERE_PRO_TEMPLATES_EDITS_ID = 76;

    public const PREMIERE_PRO_TEMPLATES_TOOLKITS = 'toolkits';
    public const PREMIERE_PRO_TEMPLATES_TOOLKITS_ID = 77;

    public const PREMIERE_PRO_TEMPLATES_TRANSITIONS = 'transitions';
    public const PREMIERE_PRO_TEMPLATES_TRANSITIONS_ID = 78;

    public const PREMIERE_PRO_TEMPLATES_TITLES = 'titles';
    public const PREMIERE_PRO_TEMPLATES_TITLES_ID = 79;

    public const PREMIERE_PRO_TEMPLATES_PRESETS = 'presets';
    public const PREMIERE_PRO_TEMPLATES_PRESETS_ID = 80;

    public const PREMIERE_PRO_TEMPLATES_LOGO = 'logo';
    public const PREMIERE_PRO_TEMPLATES_LOGO_ID = 81;

    public const PREMIERE_PRO_TEMPLATES_SLIDESHOWS = 'slideshows';
    public const PREMIERE_PRO_TEMPLATES_SLIDESHOWS_ID = 82;

    public const PREMIERE_PRO_TEMPLATES_FREE = 'free';
    public const PREMIERE_PRO_TEMPLATES_FREE_ID = 84;

    public const MOTION_GRAPHICS_TEMPLATES_TITLES = 'titles';
    public const MOTION_GRAPHICS_TEMPLATES_TITLES_ID = 86;

    public const MOTION_GRAPHICS_TEMPLATES_TRANSITIONS = 'transitions';
    public const MOTION_GRAPHICS_TEMPLATES_TRANSITIONS_ID = 87;

    public const MOTION_GRAPHICS_TEMPLATES_FREE = 'free';
    public const MOTION_GRAPHICS_TEMPLATES_FREE_ID = 88;

    public const MOTION_GRAPHICS_TEMPLATES_LOWER_THIRDS = 'lower-thirds';
    public const MOTION_GRAPHICS_TEMPLATES_LOWER_THIRDS_ID = 89;

    public const MOTION_GRAPHICS_TEMPLATES_OVERLAYS = 'overlays';
    public const MOTION_GRAPHICS_TEMPLATES_OVERLAYS_ID = 90;

    public const MOTION_GRAPHICS_TEMPLATES_BACKGROUNDS = 'backgrounds';
    public const MOTION_GRAPHICS_TEMPLATES_BACKGROUNDS_ID = 102;

    public const SOUND_EFFECTS_MOVEMENT_AND_TRANSITIONS = 'movement-and-transitions';
    public const SOUND_EFFECTS_MOVEMENT_AND_TRANSITIONS_ID = 91;

    public const SOUND_EFFECTS_UI_AND_BUTTONS = 'ui-and-buttons';
    public const SOUND_EFFECTS_UI_AND_BUTTONS_ID = 92;

    public const SOUND_EFFECTS_NATURE = 'nature';
    public const SOUND_EFFECTS_NATURE_ID = 93;

    public const SOUND_EFFECTS_CITY = 'city';
    public const SOUND_EFFECTS_CITY_ID = 94;

    public const SOUND_EFFECTS_CARTOON = 'cartoon';
    public const SOUND_EFFECTS_CARTOON_ID = 95;

    public const SOUND_EFFECTS_INDUSTRIAL = 'industrial';
    public const SOUND_EFFECTS_INDUSTRIAL_ID = 96;

    public const SOUND_EFFECTS_HUMAN = 'human';
    public const SOUND_EFFECTS_HUMAN_ID = 97;

    public const SOUND_EFFECTS_HOME_AND_OFFICE = 'home-and-office';
    public const SOUND_EFFECTS_HOME_AND_OFFICE_ID = 98;

    public const SOUND_EFFECTS_FUTURISTIC = 'futuristic';
    public const SOUND_EFFECTS_FUTURISTIC_ID = 99;

    public const SOUND_EFFECTS_GAME = 'game';
    public const SOUND_EFFECTS_GAME_ID = 100;

    public const SOUND_EFFECTS_OTHER = 'other';
    public const SOUND_EFFECTS_OTHER_ID = 101;

    public const SOUND_EFFECTS_FREE = 'free';
    public const SOUND_EFFECTS_FREE_ID = 153;

    public const PREMIERE_PRO_PRESETS_TEXT = 'text';
    public const PREMIERE_PRO_PRESETS_TEXT_ID = 122;

    public const PREMIERE_PRO_PRESETS_TRANSITIONS = 'transitions';
    public const PREMIERE_PRO_PRESETS_TRANSITIONS_ID = 123;

    public const PREMIERE_PRO_PRESETS_COLOR = 'color';
    public const PREMIERE_PRO_PRESETS_COLOR_ID = 125;

    public const PREMIERE_PRO_PRESETS_OVERLAYS = 'overlays';
    public const PREMIERE_PRO_PRESETS_OVERLAYS_ID = 126;

    public const PREMIERE_PRO_PRESETS_FREE = 'free';
    public const PREMIERE_PRO_PRESETS_FREE_ID = 134;

    public const PREMIERE_PRO_PRESETS_PHOTO_VIDEO = 'photo-video';
    public const PREMIERE_PRO_PRESETS_PHOTO_VIDEO_ID = 135;

    public const AFTER_EFFECTS_PRESETS_TEXT = 'text';
    public const AFTER_EFFECTS_PRESETS_TEXT_ID = 127;

    public const AFTER_EFFECTS_PRESETS_TRANSITIONS = 'transitions';
    public const AFTER_EFFECTS_PRESETS_TRANSITIONS_ID = 128;

    public const AFTER_EFFECTS_PRESETS_BACKGROUNDS = 'backgrounds';
    public const AFTER_EFFECTS_PRESETS_BACKGROUNDS_ID = 129;

    public const AFTER_EFFECTS_PRESETS_COLOR = 'color';
    public const AFTER_EFFECTS_PRESETS_COLOR_ID = 130;

    public const AFTER_EFFECTS_PRESETS_OVERLAY = 'overlay';
    public const AFTER_EFFECTS_PRESETS_OVERLAY_ID = 131;

    public const AFTER_EFFECTS_PRESETS_FREE = 'free';
    public const AFTER_EFFECTS_PRESETS_FREE_ID = 132;

    public const AFTER_EFFECTS_PRESETS_PHOTO_VIDEO = 'photo-video';
    public const AFTER_EFFECTS_PRESETS_PHOTO_VIDEO_ID = 133;

    public const DAVINCI_RESOLVE_TEMPLATES_FREE = 'free';
    public const DAVINCI_RESOLVE_TEMPLATES_FREE_ID = 137;

    public const DAVINCI_RESOLVE_TEMPLATES_TITLES = 'titles';
    public const DAVINCI_RESOLVE_TEMPLATES_TITLES_ID = 138;

    public const DAVINCI_RESOLVE_TEMPLATES_LOGOS = 'logos';
    public const DAVINCI_RESOLVE_TEMPLATES_LOGOS_ID = 139;

    public const DAVINCI_RESOLVE_TEMPLATES_PHOTO_VIDEO = 'photo-video';
    public const DAVINCI_RESOLVE_TEMPLATES_PHOTO_VIDEO_ID = 140;

    public const DAVINCI_RESOLVE_TEMPLATES_TRANSITIONS = 'transitions';
    public const DAVINCI_RESOLVE_TEMPLATES_TRANSITIONS_ID = 141;

    public const PREMIERE_RUSH_TEMPLATES_TITLES = 'titles';
    public const PREMIERE_RUSH_TEMPLATES_TITLES_ID = 158;

    public const PREMIERE_RUSH_TEMPLATES_TRANSITIONS = 'transitions';
    public const PREMIERE_RUSH_TEMPLATES_TRANSITIONS_ID = 159;

    public const PREMIERE_RUSH_TEMPLATES_FREE = 'free';
    public const PREMIERE_RUSH_TEMPLATES_FREE_ID = 160;

    public const PREMIERE_RUSH_TEMPLATES_LOWER_THIRDS = 'lower-thirds';
    public const PREMIERE_RUSH_TEMPLATES_LOWER_THIRDS_ID = 161;

    public const PREMIERE_RUSH_TEMPLATES_OVERLAYS = 'overlays';
    public const PREMIERE_RUSH_TEMPLATES_OVERLAYS_ID = 162;

    public const PREMIERE_RUSH_TEMPLATES_BACKGROUNDS = 'backgrounds';
    public const PREMIERE_RUSH_TEMPLATES_BACKGROUNDS_ID = 163;

    public const DAVINCI_RESOLVE_MACROS_TITLES = 'titles';
    public const DAVINCI_RESOLVE_MACROS_TITLES_ID = 164;

    public const DAVINCI_RESOLVE_MACROS_TRANSITIONS = 'transitions';
    public const DAVINCI_RESOLVE_MACROS_TRANSITIONS_ID = 165;

    public const DAVINCI_RESOLVE_MACROS_LOGO = 'logo';
    public const DAVINCI_RESOLVE_MACROS_LOGO_ID = 166;

    public const DAVINCI_RESOLVE_MACROS_BACKGROUNDS = 'backgrounds';
    public const DAVINCI_RESOLVE_MACROS_BACKGROUNDS_ID = 167;

    public const DAVINCI_RESOLVE_MACROS_OVERLAYS = 'overlays';
    public const DAVINCI_RESOLVE_MACROS_OVERLAYS_ID = 168;

    public const DAVINCI_RESOLVE_MACROS_FREE = 'free';
    public const DAVINCI_RESOLVE_MACROS_FREE_ID = 169;

    public const FINAL_CUT_PRO_TEMPLATES_TITLES = 'titles';
    public const FINAL_CUT_PRO_TEMPLATES_TITLES_ID = 170;

    public const FINAL_CUT_PRO_TEMPLATES_PHOTO_VIDEO = 'photo-video';
    public const FINAL_CUT_PRO_TEMPLATES_PHOTO_VIDEO_ID = 171;

    public const FINAL_CUT_PRO_TEMPLATES_LOGO = 'logo';
    public const FINAL_CUT_PRO_TEMPLATES_LOGO_ID = 172;

    public const FINAL_CUT_PRO_TEMPLATES_TRANSITIONS = 'transitions';
    public const FINAL_CUT_PRO_TEMPLATES_TRANSITIONS_ID = 173;

    public const FINAL_CUT_PRO_TEMPLATES_BACKGROUNDS = 'backgrounds';
    public const FINAL_CUT_PRO_TEMPLATES_BACKGROUNDS_ID = 174;

    public const FINAL_CUT_PRO_TEMPLATES_OVERLAYS = 'overlays';
    public const FINAL_CUT_PRO_TEMPLATES_OVERLAYS_ID = 175;

    public const FINAL_CUT_PRO_TEMPLATES_FREE = 'free';
    public const FINAL_CUT_PRO_TEMPLATES_FREE_ID = 176;

    public const STOCK_PHOTOS_ABSTRACT_TEXTURES = 'abstract-texture';
    public const STOCK_PHOTOS_ABSTRACT_TEXTURES_ID = 177;

    public const STOCK_PHOTOS_BACKGROUNDS = 'backgrounds';
    public const STOCK_PHOTOS_BACKGROUNDS_ID = 178;

    public const STOCK_PHOTOS_ANIMALS_WILDLIFE = 'animals-wildlife';
    public const STOCK_PHOTOS_ANIMALS_WILDLIFE_ID = 179;

    public const STOCK_PHOTOS_ARCHITECTURE = 'architecture';
    public const STOCK_PHOTOS_ARCHITECTURE_ID = 180;

    public const STOCK_PHOTOS_BUSINESS_FINANCE = 'business-finance';
    public const STOCK_PHOTOS_BUSINESS_FINANCE_ID = 181;

    public const STOCK_PHOTOS_CITY_URBAN = 'city-urban';
    public const STOCK_PHOTOS_CITY_URBAN_ID = 182;

    public const STOCK_PHOTOS_CREATIVITY_DESIGN = 'creativity-design';
    public const STOCK_PHOTOS_CREATIVITY_DESIGN_ID = 183;

    public const STOCK_PHOTOS_CULTURE = 'culture';
    public const STOCK_PHOTOS_CULTURE_ID = 184;

    public const STOCK_PHOTOS_EDUCATION= 'education';
    public const STOCK_PHOTOS_EDUCATION_ID = 185;

    public const STOCK_PHOTOS_FAMILY = 'family';
    public const STOCK_PHOTOS_FAMILY_ID = 186;

    public const STOCK_PHOTOS_FASHION = 'fashion';
    public const STOCK_PHOTOS_FASHION_ID = 187;

    public const STOCK_PHOTOS_FOOD_DRINK = 'food-drink';
    public const STOCK_PHOTOS_FOOD_DRINK_ID = 188;

    public const STOCK_PHOTOS_HEALTH_FITNESS = 'health-fitness';
    public const STOCK_PHOTOS_HEALTH_FITNESS_ID = 189;

    public const STOCK_PHOTOS_HEALTHCARE = 'healthcare';
    public const STOCK_PHOTOS_HEALTHCARE_ID = 190;

    public const STOCK_PHOTOS_HOLIDAYS_SEASONAL = 'holidays-seasonal';
    public const STOCK_PHOTOS_HOLIDAYS_SEASONAL_ID = 191;

    public const STOCK_PHOTOS_INDUSTRY = 'industry';
    public const STOCK_PHOTOS_INDUSTRY_ID = 192;

    public const STOCK_PHOTOS_MUSIC = 'music';
    public const STOCK_PHOTOS_MUSIC_ID = 193;

    public const STOCK_PHOTOS_NATURE_OUTDOORS = 'nature-outdoors';
    public const STOCK_PHOTOS_NATURE_OUTDOORS_ID = 194;

    public const STOCK_PHOTOS_SCIENCE_TECHNOLOGY = 'science-technology';
    public const STOCK_PHOTOS_SCIENCE_TECHNOLOGY_ID = 195;

    public const STOCK_PHOTOS_SPORTS_RECREATION = 'sports-recreation';
    public const STOCK_PHOTOS_SPORTS_RECREATION_ID = 196;

    public const STOCK_PHOTOS_TRANSPORTATION = 'transportation';
    public const STOCK_PHOTOS_TRANSPORTATION_ID = 197;

    public const STOCK_PHOTOS_TRAVEL = 'travel';
    public const STOCK_PHOTOS_TRAVEL_ID = 198;

    public const STOCK_PHOTOS_WEDDING = 'wedding';
    public const STOCK_PHOTOS_WEDDING_ID = 199;

    public const STOCK_PHOTOS_VINTAGE = 'VINTAGE';
    public const STOCK_PHOTOS_VINTAGE_ID = 200;

    protected $modelClass = SubCategory::class;

    public static function legacySlugs()
    {
        return [];
    }

    public static function normalizeSlug($categorySlug, $slug)
    {
        if (isset(static::legacySlugs()[$categorySlug][$slug])) {
            return static::legacySlugs()[$categorySlug][$slug];
        }
        return $slug;
    }

    protected function prepareData(): array
    {
        return collect(array_merge(
            $this->afterEffectsSubCategories(),
            $this->stockVideoSubCategories(),
            $this->stockMotionGraphicsSubCategories(),
            $this->stockMusicSubCategories(),
            $this->premiereProTemplatesSubCategories(),
            $this->motionGraphicsTemplatesSubCategories(),
            $this->soundEffectsSubCategories(),
            $this->premiereProPresetsSubCategories(),
            $this->afterEffectsPresetsSubCategories(),
            $this->davinciResolveTemplatesSubCategories(),
            $this->premiereRushTemplatesSubCategories(),
            $this->davinciResolveMacrosSubCategories(),
            $this->finalCutProXSubCategories(),
            $this->stockPhotosSubCategories()
        ))
            ->keyBy('id')
            ->toArray();
    }

    protected function stockPhotosSubCategories()
    {
        $data = [
            [
                'id' => self::STOCK_PHOTOS_ABSTRACT_TEXTURES_ID,
                'slug' => self::STOCK_PHOTOS_ABSTRACT_TEXTURES,
                'name' => 'Abstract & Textures',
                'order' => 1,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::STOCK_PHOTOS_BACKGROUNDS_ID,
                'slug' => self::STOCK_PHOTOS_BACKGROUNDS,
                'name' => 'Backgrounds',
                'order' => 3,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::STOCK_PHOTOS_ANIMALS_WILDLIFE_ID,
                'slug' => self::STOCK_PHOTOS_ANIMALS_WILDLIFE,
                'name' => 'Animals & Wildlife',
                'order' => 5,
                'sidebar_order' => 5,
            ],
            [
                'id' => self::STOCK_PHOTOS_ARCHITECTURE_ID,
                'slug' => self::STOCK_PHOTOS_ARCHITECTURE,
                'name' => 'Architecture',
                'order' => 7,
                'sidebar_order' => 7,
            ],
            [
                'id' => self::STOCK_PHOTOS_BUSINESS_FINANCE_ID,
                'slug' => self::STOCK_PHOTOS_BUSINESS_FINANCE,
                'name' => 'Business & finance',
                'order' => 9,
                'sidebar_order' => 9,
            ],
            [
                'id' => self::STOCK_PHOTOS_CITY_URBAN_ID,
                'slug' => self::STOCK_PHOTOS_CITY_URBAN,
                'name' => 'City & Urban',
                'order' => 11,
                'sidebar_order' => 11,
            ],
            [
                'id' => self::STOCK_PHOTOS_CREATIVITY_DESIGN_ID,
                'slug' => self::STOCK_PHOTOS_CREATIVITY_DESIGN,
                'name' => 'Creativity & Design',
                'order' => 13,
                'sidebar_order' => 13,
            ],
            [
                'id' => self::STOCK_PHOTOS_CULTURE_ID,
                'slug' => self::STOCK_PHOTOS_CULTURE,
                'name' => 'Culture',
                'order' => 15,
                'sidebar_order' => 15,
            ],
            [
                'id' => self::STOCK_PHOTOS_EDUCATION_ID,
                'slug' => self::STOCK_PHOTOS_EDUCATION,
                'name' => 'Education',
                'order' => 17,
                'sidebar_order' => 17,
            ],
            [
                'id' => self::STOCK_PHOTOS_FAMILY_ID,
                'slug' => self::STOCK_PHOTOS_FAMILY,
                'name' => 'Family',
                'order' => 19,
                'sidebar_order' => 19,
            ],
            [
                'id' => self::STOCK_PHOTOS_FASHION_ID,
                'slug' => self::STOCK_PHOTOS_FASHION,
                'name' => 'Fashion',
                'order' => 21,
                'sidebar_order' => 21,
            ],
            [
                'id' => self::STOCK_PHOTOS_FOOD_DRINK_ID,
                'slug' => self::STOCK_PHOTOS_FOOD_DRINK,
                'name' => 'Food & Drink',
                'order' => 23,
                'sidebar_order' => 23,
            ],
            [
                'id' => self::STOCK_PHOTOS_HEALTH_FITNESS_ID,
                'slug' => self::STOCK_PHOTOS_HEALTH_FITNESS,
                'name' => 'Health & Fitness',
                'order' => 25,
                'sidebar_order' => 25,
            ],
            [
                'id' => self::STOCK_PHOTOS_HEALTHCARE_ID,
                'slug' => self::STOCK_PHOTOS_HEALTHCARE,
                'name' => 'Healthcare',
                'order' => 27,
                'sidebar_order' => 27,
            ],
            [
                'id' => self::STOCK_PHOTOS_HOLIDAYS_SEASONAL_ID,
                'slug' => self::STOCK_PHOTOS_HOLIDAYS_SEASONAL,
                'name' => 'Holidays & Seasonal',
                'order' => 29,
                'sidebar_order' => 29,
            ],
            [
                'id' => self::STOCK_PHOTOS_INDUSTRY_ID,
                'slug' => self::STOCK_PHOTOS_INDUSTRY,
                'name' => 'Industry',
                'order' => 31,
                'sidebar_order' => 31,
            ],
            [
                'id' => self::STOCK_PHOTOS_MUSIC_ID,
                'slug' => self::STOCK_PHOTOS_MUSIC,
                'name' => 'Music',
                'order' => 33,
                'sidebar_order' => 33,
            ],
            [
                'id' => self::STOCK_PHOTOS_NATURE_OUTDOORS_ID,
                'slug' => self::STOCK_PHOTOS_NATURE_OUTDOORS,
                'name' => 'Nature & Outdoors',
                'order' => 35,
                'sidebar_order' => 35,
            ],
            [
                'id' => self::STOCK_PHOTOS_SCIENCE_TECHNOLOGY_ID,
                'slug' => self::STOCK_PHOTOS_SCIENCE_TECHNOLOGY,
                'name' => 'Science & Technology',
                'order' => 37,
                'sidebar_order' => 37,
            ],
            [
                'id' => self::STOCK_PHOTOS_SPORTS_RECREATION_ID,
                'slug' => self::STOCK_PHOTOS_SPORTS_RECREATION,
                'name' => 'Sports & Recreation',
                'order' => 39,
                'sidebar_order' => 39,
            ],
            [
                'id' => self::STOCK_PHOTOS_TRANSPORTATION_ID,
                'slug' => self::STOCK_PHOTOS_TRANSPORTATION,
                'name' => 'Transportation',
                'order' => 41,
                'sidebar_order' => 41,
            ],
            [
                'id' => self::STOCK_PHOTOS_TRAVEL_ID,
                'slug' => self::STOCK_PHOTOS_TRAVEL,
                'name' => 'Travel',
                'order' => 43,
                'sidebar_order' => 43,
            ],
            [
                'id' => self::STOCK_PHOTOS_WEDDING_ID,
                'slug' => self::STOCK_PHOTOS_WEDDING,
                'name' => 'Wedding',
                'order' => 45,
                'sidebar_order' => 45,
            ],
            [
                'id' => self::STOCK_PHOTOS_VINTAGE_ID,
                'slug' => self::STOCK_PHOTOS_VINTAGE,
                'name' => 'Vintage',
                'order' => 47,
                'sidebar_order' => 47,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::STOCK_PHOTOS_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function afterEffectsSubCategories()
    {
        $data = [

            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_PHOTO_VIDEO_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES_PHOTO_VIDEO,
                'name' => 'Photo / Video',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_TEXT_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES_TEXT,
                'name' => 'Text',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_LOGOS_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES_LOGOS,
                'name' => 'Logo',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_LOWER_THIRDS_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES_LOWER_THIRDS,
                'name' => 'Lower Thirds',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_TRANSITIONS_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 0,
                'sidebar_order' => 5,
            ],
            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_OTHER_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES_OTHER,
                'name' => 'Other',
                'order' => 0,
                'sidebar_order' => 7,
            ],
            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_FREE_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES_FREE,
                'name' => 'Free',
                'seo_title' => 'The Best Free After Effects Templates | Unlimited Downloads',
                'meta_description' => 'Download Free After Effects Templates to Use In Personal and Commercial Projects. Easy To Use & Professionally Designed.',
                'order' => 99,
                'sidebar_order' => 99,
            ],
            [
                'id' => self::AFTER_EFFECTS_TEMPLATES_PRESETS_ID,
                'slug' => self::AFTER_EFFECTS_TEMPLATES_PRESETS,
                'name' => 'Presets',
                'order' => 0,
                'sidebar_order' => 0,
                'deleted_at' => '2018-05-07 21:00:00',
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::AFTER_EFFECTS_TEMPLATES_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function stockVideoSubCategories()
    {
        $data = [
            [
                'id' => self::STOCK_VIDEO_GREEN_SCREEN_ID,
                'slug' => self::STOCK_VIDEO_GREEN_SCREEN,
                'name' => 'Green Screen',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::STOCK_VIDEO_TRANSPORTATION_ID,
                'slug' => self::STOCK_VIDEO_TRANSPORTATION,
                'name' => 'Transportation',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::STOCK_VIDEO_BUILDINGS_ID,
                'slug' => self::STOCK_VIDEO_BUILDINGS,
                'name' => 'Buildings',
                'order' => 0,
                'sidebar_order' => 5,
            ],
            [
                'id' => self::STOCK_VIDEO_TECHNOLOGY_ID,
                'slug' => self::STOCK_VIDEO_TECHNOLOGY,
                'name' => 'Technology',
                'order' => 0,
                'sidebar_order' => 7,
            ],
            [
                'id' => self::STOCK_VIDEO_PEOPLE_ID,
                'slug' => self::STOCK_VIDEO_PEOPLE,
                'name' => 'People',
                'order' => 0,
                'sidebar_order' => 9,
            ],
            [
                'id' => self::STOCK_VIDEO_HEALTH_ID,
                'slug' => self::STOCK_VIDEO_HEALTH,
                'name' => 'Health',
                'order' => 0,
                'sidebar_order' => 11,
            ],
            [
                'id' => self::STOCK_VIDEO_FASHION_ID,
                'slug' => self::STOCK_VIDEO_FASHION,
                'name' => 'Fashion',
                'order' => 0,
                'sidebar_order' => 13,
            ],
            [
                'id' => self::STOCK_VIDEO_ANIMALS_ID,
                'slug' => self::STOCK_VIDEO_ANIMALS,
                'name' => 'Animals',
                'order' => 0,
                'sidebar_order' => 15,
            ],
            [
                'id' => self::STOCK_VIDEO_FOOD_ID,
                'slug' => self::STOCK_VIDEO_FOOD,
                'name' => 'Food',
                'order' => 0,
                'sidebar_order' => 17,
            ],
            [
                'id' => self::STOCK_VIDEO_SPORTS_ID,
                'slug' => self::STOCK_VIDEO_SPORTS,
                'name' => 'Sports',
                'order' => 0,
                'sidebar_order' => 19,
            ],
            [
                'id' => self::STOCK_VIDEO_NATURE_ID,
                'slug' => self::STOCK_VIDEO_NATURE,
                'name' => 'Nature',
                'order' => 0,
                'sidebar_order' => 21,
            ],
            [
                'id' => self::STOCK_VIDEO_AERIAL_ID,
                'slug' => self::STOCK_VIDEO_AERIAL,
                'name' => 'Aerial',
                'order' => 0,
                'sidebar_order' => 23,
            ],
            [
                'id' => self::STOCK_VIDEO_HOLIDAYS_ID,
                'slug' => self::STOCK_VIDEO_HOLIDAYS,
                'name' => 'Holidays',
                'order' => 0,
                'sidebar_order' => 25,
            ],
            [
                'id' => self::STOCK_VIDEO_INDUSTRIAL_ID,
                'slug' => self::STOCK_VIDEO_INDUSTRIAL,
                'name' => 'Industrial',
                'order' => 0,
                'sidebar_order' => 27,
            ],
            [
                'id' => self::STOCK_VIDEO_HOUSEHOLD_ID,
                'slug' => self::STOCK_VIDEO_HOUSEHOLD,
                'name' => 'Household',
                'order' => 0,
                'sidebar_order' => 29,
            ],
            [
                'id' => self::STOCK_VIDEO_SCIENCE_ID,
                'slug' => self::STOCK_VIDEO_SCIENCE,
                'name' => 'Science',
                'order' => 0,
                'sidebar_order' => 31,
            ],
            [
                'id' => self::STOCK_VIDEO_EDUCATION_ID,
                'slug' => self::STOCK_VIDEO_EDUCATION,
                'name' => 'Education',
                'order' => 0,
                'sidebar_order' => 33,
            ],
            [
                'id' => self::STOCK_VIDEO_TRAVEL_ID,
                'slug' => self::STOCK_VIDEO_TRAVEL,
                'name' => 'Travel',
                'order' => 0,
                'sidebar_order' => 35,
            ],
            [
                'id' => self::STOCK_VIDEO_URBAN_ID,
                'slug' => self::STOCK_VIDEO_URBAN,
                'name' => 'Urban',
                'order' => 0,
                'sidebar_order' => 37,
            ],
            [
                'id' => self::STOCK_VIDEO_BUSINESS_ID,
                'slug' => self::STOCK_VIDEO_BUSINESS,
                'name' => 'Business',
                'order' => 0,
                'sidebar_order' => 39,
            ],
            [
                'id' => self::STOCK_VIDEO_INK_ID,
                'slug' => self::STOCK_VIDEO_INK,
                'name' => 'Ink',
                'order' => 0,
                'sidebar_order' => 41,
            ],
            [
                'id' => self::STOCK_VIDEO_FIRE_ID,
                'slug' => self::STOCK_VIDEO_FIRE,
                'name' => 'Fire',
                'order' => 0,
                'sidebar_order' => 43,
            ],
            [
                'id' => self::STOCK_VIDEO_DUST_ID,
                'slug' => self::STOCK_VIDEO_DUST,
                'name' => 'Dust',
                'order' => 0,
                'sidebar_order' => 45,
            ],
            [
                'id' => self::STOCK_VIDEO_PAINT_ID,
                'slug' => self::STOCK_VIDEO_PAINT,
                'name' => 'Paint',
                'order' => 0,
                'sidebar_order' => 47,
            ],
            [
                'id' => self::STOCK_VIDEO_SMOKE_ID,
                'slug' => self::STOCK_VIDEO_SMOKE,
                'name' => 'Smoke',
                'order' => 0,
                'sidebar_order' => 49,
            ],
            [
                'id' => self::STOCK_VIDEO_SNOW_ID,
                'slug' => self::STOCK_VIDEO_SNOW,
                'name' => 'Snow',
                'order' => 0,
                'sidebar_order' => 51,
            ],
            [
                'id' => self::STOCK_VIDEO_CLOUDS_ID,
                'slug' => self::STOCK_VIDEO_CLOUDS,
                'name' => 'Clouds',
                'order' => 0,
                'sidebar_order' => 53,
            ],
            [
                'id' => self::STOCK_VIDEO_PAPER_ID,
                'slug' => self::STOCK_VIDEO_PAPER,
                'name' => 'Paper',
                'order' => 0,
                'sidebar_order' => 55,
            ],
            [
                'id' => self::STOCK_VIDEO_LIGHT_ID,
                'slug' => self::STOCK_VIDEO_LIGHT,
                'name' => 'Light',
                'order' => 0,
                'sidebar_order' => 57,
            ],
            [
                'id' => self::STOCK_VIDEO_SPARKS_ID,
                'slug' => self::STOCK_VIDEO_SPARKS,
                'name' => 'Sparks',
                'order' => 0,
                'sidebar_order' => 59,
            ],
            [
                'id' => self::STOCK_VIDEO_WATER_ID,
                'slug' => self::STOCK_VIDEO_WATER,
                'name' => 'Water',
                'order' => 0,
                'sidebar_order' => 61,
            ],
            [
                'id' => self::STOCK_VIDEO_OIL_ID,
                'slug' => self::STOCK_VIDEO_OIL,
                'name' => 'Oil',
                'order' => 0,
                'sidebar_order' => 63,
            ],
            [
                'id' => self::STOCK_VIDEO_PARTICLES_ID,
                'slug' => self::STOCK_VIDEO_PARTICLES,
                'name' => 'Particles',
                'order' => 0,
                'sidebar_order' => 65,
            ],
            [
                'id' => self::STOCK_VIDEO_OVERLAY_ID,
                'slug' => self::STOCK_VIDEO_OVERLAY,
                'name' => 'Overlay',
                'order' => 0,
                'sidebar_order' => 67,
            ],
            [
                'id' => self::STOCK_VIDEO_BACKGROUND_ID,
                'slug' => self::STOCK_VIDEO_BACKGROUND,
                'name' => 'Background',
                'order' => 0,
                'sidebar_order' => 69,
            ],
            [
                'id' => self::STOCK_VIDEO_FREE_ID,
                'slug' => self::STOCK_VIDEO_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::STOCK_VIDEO_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function stockMotionGraphicsSubCategories()
    {
        $data = [
            [
                'id' => self::STOCK_MOTION_GRAPHICS_BACKGROUNDS_ID,
                'slug' => self::STOCK_MOTION_GRAPHICS_BACKGROUNDS,
                'name' => 'Backgrounds',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::STOCK_MOTION_GRAPHICS_LOWER_THIRDS_ID,
                'slug' => self::STOCK_MOTION_GRAPHICS_LOWER_THIRDS,
                'name' => 'Lower Thirds',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::STOCK_MOTION_GRAPHICS_TRANSITIONS_ID,
                'slug' => self::STOCK_MOTION_GRAPHICS_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::STOCK_MOTION_GRAPHICS_FRAMING_DEVICES_ID,
                'slug' => self::STOCK_MOTION_GRAPHICS_FRAMING_DEVICES,
                'name' => 'Framing Devices',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::STOCK_MOTION_GRAPHICS_OVERLAYS_ID,
                'slug' => self::STOCK_MOTION_GRAPHICS_OVERLAYS,
                'name' => 'Overlays',
                'order' => 0,
                'sidebar_order' => 5,
            ],
            [
                'id' => self::STOCK_MOTION_GRAPHICS_OTHER_ID,
                'slug' => self::STOCK_MOTION_GRAPHICS_OTHER,
                'name' => 'Other',
                'order' => 0,
                'sidebar_order' => 6,
            ],
            [
                'id' => self::STOCK_MOTION_GRAPHICS_FREE_ID,
                'slug' => self::STOCK_MOTION_GRAPHICS_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::STOCK_MOTION_GRAPHICS_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function stockMusicSubCategories()
    {
        $data = [
            [
                'id' => self::STOCK_MUSIC_EASY_LISTENING_ID,
                'slug' => self::STOCK_MUSIC_EASY_LISTENING,
                'name' => 'Easy Listening',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::STOCK_MUSIC_CORPORATE_ID,
                'slug' => self::STOCK_MUSIC_CORPORATE,
                'name' => 'Corporate',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::STOCK_MUSIC_ROCK_ID,
                'slug' => self::STOCK_MUSIC_ROCK,
                'name' => 'Rock',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::STOCK_MUSIC_POP_ID,
                'slug' => self::STOCK_MUSIC_POP,
                'name' => 'Pop',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::STOCK_MUSIC_INSPIRATIONAL_ID,
                'slug' => self::STOCK_MUSIC_INSPIRATIONAL,
                'name' => 'Inspirational',
                'order' => 0,
                'sidebar_order' => 5,
            ],
            [
                'id' => self::STOCK_MUSIC_PIANO_ID,
                'slug' => self::STOCK_MUSIC_PIANO,
                'name' => 'Piano',
                'order' => 0,
                'sidebar_order' => 6,
            ],
            [
                'id' => self::STOCK_MUSIC_CLASSICAL_ID,
                'slug' => self::STOCK_MUSIC_CLASSICAL,
                'name' => 'Classical',
                'order' => 0,
                'sidebar_order' => 7,
            ],
            [
                'id' => self::STOCK_MUSIC_ELECTRONIC_ID,
                'slug' => self::STOCK_MUSIC_ELECTRONIC,
                'name' => 'Electronic',
                'order' => 0,
                'sidebar_order' => 8,
            ],
            [
                'id' => self::STOCK_MUSIC_TRAILERS_ID,
                'slug' => self::STOCK_MUSIC_TRAILERS,
                'name' => 'Trailers',
                'order' => 0,
                'sidebar_order' => 9,
            ],
            [
                'id' => self::STOCK_MUSIC_ACTION_ID,
                'slug' => self::STOCK_MUSIC_ACTION,
                'name' => 'Action',
                'order' => 0,
                'sidebar_order' => 10,
            ],
            [
                'id' => self::STOCK_MUSIC_HEAVY_ID,
                'slug' => self::STOCK_MUSIC_HEAVY,
                'name' => 'Heavy',
                'order' => 0,
                'sidebar_order' => 11,
            ],
            [
                'id' => self::STOCK_MUSIC_METAL_ID,
                'slug' => self::STOCK_MUSIC_METAL,
                'name' => 'Metal',
                'order' => 0,
                'sidebar_order' => 12,
            ],
            [
                'id' => self::STOCK_MUSIC_PEACEFUL_ID,
                'slug' => self::STOCK_MUSIC_PEACEFUL,
                'name' => 'Peaceful',
                'order' => 0,
                'sidebar_order' => 13,
            ],
            [
                'id' => self::STOCK_MUSIC_FUN_ID,
                'slug' => self::STOCK_MUSIC_FUN,
                'name' => 'Fun',
                'order' => 0,
                'sidebar_order' => 14,
            ],
            [
                'id' => self::STOCK_MUSIC_UPBEAT_ID,
                'slug' => self::STOCK_MUSIC_UPBEAT,
                'name' => 'Upbeat',
                'order' => 0,
                'sidebar_order' => 15,
            ],
            [
                'id' => self::STOCK_MUSIC_DANCE_ID,
                'slug' => self::STOCK_MUSIC_DANCE,
                'name' => 'Dance',
                'order' => 0,
                'sidebar_order' => 16,
            ],
            [
                'id' => self::STOCK_MUSIC_HIP_HOP_ID,
                'slug' => self::STOCK_MUSIC_HIP_HOP,
                'name' => 'Hip Hop',
                'order' => 0,
                'sidebar_order' => 17,
            ],
            [
                'id' => self::STOCK_MUSIC_HOLIDAY_ID,
                'slug' => self::STOCK_MUSIC_HOLIDAY,
                'name' => 'Holiday',
                'order' => 0,
                'sidebar_order' => 18,
            ],
            [
                'id' => self::STOCK_MUSIC_LOGOS_ID,
                'slug' => self::STOCK_MUSIC_LOGOS,
                'name' => 'Logos ',
                'order' => 0,
                'sidebar_order' => 19,
            ],
            [
                'id' => self::STOCK_MUSIC_FOLK_ID,
                'slug' => self::STOCK_MUSIC_FOLK,
                'name' => 'Folk',
                'order' => 0,
                'sidebar_order' => 20,
            ],
            [
                'id' => self::STOCK_MUSIC_ACOUSTIC_ID,
                'slug' => self::STOCK_MUSIC_ACOUSTIC,
                'name' => 'Acoustic',
                'order' => 0,
                'sidebar_order' => 21,
            ],
            [
                'id' => self::STOCK_MUSIC_COUNTRY_ID,
                'slug' => self::STOCK_MUSIC_COUNTRY,
                'name' => 'Country',
                'order' => 0,
                'sidebar_order' => 22,
            ],
            [
                'id' => self::STOCK_MUSIC_JAZZ_ID,
                'slug' => self::STOCK_MUSIC_JAZZ,
                'name' => 'Jazz',
                'order' => 0,
                'sidebar_order' => 23,
            ],
            [
                'id' => self::STOCK_MUSIC_FILM_SCORE_ID,
                'slug' => self::STOCK_MUSIC_FILM_SCORE,
                'name' => 'Film Score',
                'order' => 0,
                'sidebar_order' => 24,
            ],
            [
                'id' => self::STOCK_MUSIC_CHILDREN_ID,
                'slug' => self::STOCK_MUSIC_CHILDREN,
                'name' => 'Children',
                'order' => 0,
                'sidebar_order' => 25,
            ],
            [
                'id' => self::STOCK_MUSIC_SOUL_ID,
                'slug' => self::STOCK_MUSIC_SOUL,
                'name' => 'Soul',
                'order' => 0,
                'sidebar_order' => 26,
            ],
            [
                'id' => self::STOCK_MUSIC_FUNK_ID,
                'slug' => self::STOCK_MUSIC_FUNK,
                'name' => 'Funk',
                'order' => 0,
                'sidebar_order' => 27,
            ],
            [
                'id' => self::STOCK_MUSIC_EXPERIMENTAL_ID,
                'slug' => self::STOCK_MUSIC_EXPERIMENTAL,
                'name' => 'Experimental',
                'order' => 0,
                'sidebar_order' => 28,
            ],
            [
                'id' => self::STOCK_MUSIC_WORLD_ID,
                'slug' => self::STOCK_MUSIC_WORLD,
                'name' => 'World',
                'order' => 0,
                'sidebar_order' => 29,
            ],
            [
                'id' => self::STOCK_MUSIC_MUSIC_KITS_ID,
                'slug' => self::STOCK_MUSIC_MUSIC_KITS,
                'name' => 'Music Kits',
                'order' => 0,
                'sidebar_order' => 30,
            ],
            [
                'id' => self::STOCK_MUSIC_FREE_ID,
                'slug' => self::STOCK_MUSIC_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
            [
                'id' => self::STOCK_MUSIC_AMBIENT_ID,
                'slug' => self::STOCK_MUSIC_AMBIENT,
                'name' => 'Ambient',
                'order' => 0,
                'sidebar_order' => 0,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::STOCK_MUSIC_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function premiereProTemplatesSubCategories()
    {
        $data = [
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_EDITS_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES_EDITS,
                'name' => 'Edits',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_TOOLKITS_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES_TOOLKITS,
                'name' => 'Toolkits',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_TRANSITIONS_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_TITLES_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES_TITLES,
                'name' => 'Titles',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_PRESETS_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES_PRESETS,
                'name' => 'Presets',
                'order' => 0,
                'sidebar_order' => 0,
                'deleted_at' => '2018-05-07 21:00:00',
            ],
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_LOGO_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES_LOGO,
                'name' => 'Logo',
                'order' => 0,
                'sidebar_order' => 6,
            ],
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_SLIDESHOWS_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES_SLIDESHOWS,
                'name' => 'Slideshows',
                'order' => 0,
                'sidebar_order' => 7,
            ],
            [
                'id' => self::PREMIERE_PRO_TEMPLATES_FREE_ID,
                'slug' => self::PREMIERE_PRO_TEMPLATES_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::PREMIERE_PRO_TEMPLATES_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function motionGraphicsTemplatesSubCategories()
    {
        $data = [
            [
                'id' => self::MOTION_GRAPHICS_TEMPLATES_TITLES_ID,
                'slug' => self::MOTION_GRAPHICS_TEMPLATES_TITLES,
                'name' => 'Titles',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::MOTION_GRAPHICS_TEMPLATES_TRANSITIONS_ID,
                'slug' => self::MOTION_GRAPHICS_TEMPLATES_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::MOTION_GRAPHICS_TEMPLATES_FREE_ID,
                'slug' => self::MOTION_GRAPHICS_TEMPLATES_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
            [
                'id' => self::MOTION_GRAPHICS_TEMPLATES_LOWER_THIRDS_ID,
                'slug' => self::MOTION_GRAPHICS_TEMPLATES_LOWER_THIRDS,
                'name' => 'Lower Thirds',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::MOTION_GRAPHICS_TEMPLATES_OVERLAYS_ID,
                'slug' => self::MOTION_GRAPHICS_TEMPLATES_OVERLAYS,
                'name' => 'Overlays',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::MOTION_GRAPHICS_TEMPLATES_BACKGROUNDS_ID,
                'slug' => self::MOTION_GRAPHICS_TEMPLATES_BACKGROUNDS,
                'name' => 'Backgrounds',
                'order' => 0,
                'sidebar_order' => 4,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::MOTION_GRAPHICS_TEMPLATES_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function soundEffectsSubCategories()
    {
        $data = [
            [
                'id' => self::SOUND_EFFECTS_MOVEMENT_AND_TRANSITIONS_ID,
                'slug' => self::SOUND_EFFECTS_MOVEMENT_AND_TRANSITIONS,
                'name' => 'Movement & Transitions',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::SOUND_EFFECTS_UI_AND_BUTTONS_ID,
                'slug' => self::SOUND_EFFECTS_UI_AND_BUTTONS,
                'name' => 'UI & Buttons',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::SOUND_EFFECTS_NATURE_ID,
                'slug' => self::SOUND_EFFECTS_NATURE,
                'name' => 'Nature',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::SOUND_EFFECTS_CITY_ID,
                'slug' => self::SOUND_EFFECTS_CITY,
                'name' => 'City',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::SOUND_EFFECTS_CARTOON_ID,
                'slug' => self::SOUND_EFFECTS_CARTOON,
                'name' => 'Cartoon',
                'order' => 0,
                'sidebar_order' => 5,
            ],
            [
                'id' => self::SOUND_EFFECTS_INDUSTRIAL_ID,
                'slug' => self::SOUND_EFFECTS_INDUSTRIAL,
                'name' => 'Industrial',
                'order' => 0,
                'sidebar_order' => 6,
            ],
            [
                'id' => self::SOUND_EFFECTS_HUMAN_ID,
                'slug' => self::SOUND_EFFECTS_HUMAN,
                'name' => 'Human',
                'order' => 0,
                'sidebar_order' => 7,
            ],
            [
                'id' => self::SOUND_EFFECTS_HOME_AND_OFFICE_ID,
                'slug' => self::SOUND_EFFECTS_HOME_AND_OFFICE,
                'name' => 'Home & Office',
                'order' => 0,
                'sidebar_order' => 8,
            ],
            [
                'id' => self::SOUND_EFFECTS_FUTURISTIC_ID,
                'slug' => self::SOUND_EFFECTS_FUTURISTIC,
                'name' => 'Futuristic',
                'order' => 0,
                'sidebar_order' => 9,
            ],
            [
                'id' => self::SOUND_EFFECTS_GAME_ID,
                'slug' => self::SOUND_EFFECTS_GAME,
                'name' => 'Game',
                'order' => 0,
                'sidebar_order' => 10,
            ],
            [
                'id' => self::SOUND_EFFECTS_OTHER_ID,
                'slug' => self::SOUND_EFFECTS_OTHER,
                'name' => 'Other',
                'order' => 0,
                'sidebar_order' => 11,
            ],
            [
                'id' => self::SOUND_EFFECTS_FREE_ID,
                'slug' => self::SOUND_EFFECTS_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::SOUND_EFFECTS_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function premiereProPresetsSubCategories()
    {
        $data = [
            [
                'id' => self::PREMIERE_PRO_PRESETS_TEXT_ID,
                'slug' => self::PREMIERE_PRO_PRESETS_TEXT,
                'name' => 'Text',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::PREMIERE_PRO_PRESETS_TRANSITIONS_ID,
                'slug' => self::PREMIERE_PRO_PRESETS_TRANSITIONS,
                'name' => 'Transitions ',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::PREMIERE_PRO_PRESETS_COLOR_ID,
                'slug' => self::PREMIERE_PRO_PRESETS_COLOR,
                'name' => 'Color',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::PREMIERE_PRO_PRESETS_OVERLAYS_ID,
                'slug' => self::PREMIERE_PRO_PRESETS_OVERLAYS,
                'name' => 'Overlays',
                'order' => 0,
                'sidebar_order' => 5,
            ],
            [
                'id' => self::PREMIERE_PRO_PRESETS_FREE_ID,
                'slug' => self::PREMIERE_PRO_PRESETS_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
            [
                'id' => self::PREMIERE_PRO_PRESETS_PHOTO_VIDEO_ID,
                'slug' => self::PREMIERE_PRO_PRESETS_PHOTO_VIDEO,
                'name' => 'Photo / Video',
                'order' => 0,
                'sidebar_order' => 6,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::PREMIERE_PRO_PRESETS_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function afterEffectsPresetsSubCategories()
    {
        $data = [
            [
                'id' => self::AFTER_EFFECTS_PRESETS_TEXT_ID,
                'slug' => self::AFTER_EFFECTS_PRESETS_TEXT,
                'name' => 'Text',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::AFTER_EFFECTS_PRESETS_TRANSITIONS_ID,
                'slug' => self::AFTER_EFFECTS_PRESETS_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::AFTER_EFFECTS_PRESETS_BACKGROUNDS_ID,
                'slug' => self::AFTER_EFFECTS_PRESETS_BACKGROUNDS,
                'name' => 'Backgrounds',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::AFTER_EFFECTS_PRESETS_COLOR_ID,
                'slug' => self::AFTER_EFFECTS_PRESETS_COLOR,
                'name' => 'Color',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::AFTER_EFFECTS_PRESETS_OVERLAY_ID,
                'slug' => self::AFTER_EFFECTS_PRESETS_OVERLAY,
                'name' => 'Overlay',
                'order' => 0,
                'sidebar_order' => 5,
            ],
            [
                'id' => self::AFTER_EFFECTS_PRESETS_FREE_ID,
                'slug' => self::AFTER_EFFECTS_PRESETS_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
            [
                'id' => self::AFTER_EFFECTS_PRESETS_PHOTO_VIDEO_ID,
                'slug' => self::AFTER_EFFECTS_PRESETS_PHOTO_VIDEO,
                'name' => 'Photo/Video',
                'order' => 0,
                'sidebar_order' => 6,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::AFTER_EFFECTS_PRESETS_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function davinciResolveTemplatesSubCategories()
    {
        $data = [
            [
                'id' => self::DAVINCI_RESOLVE_TEMPLATES_FREE_ID,
                'slug' => self::DAVINCI_RESOLVE_TEMPLATES_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_TEMPLATES_TITLES_ID,
                'slug' => self::DAVINCI_RESOLVE_TEMPLATES_TITLES,
                'name' => 'Titles',
                'order' => 0,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_TEMPLATES_LOGOS_ID,
                'slug' => self::DAVINCI_RESOLVE_TEMPLATES_LOGOS,
                'name' => 'Logos',
                'order' => 0,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_TEMPLATES_PHOTO_VIDEO_ID,
                'slug' => self::DAVINCI_RESOLVE_TEMPLATES_PHOTO_VIDEO,
                'name' => 'Photo / Video',
                'order' => 0,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_TEMPLATES_TRANSITIONS_ID,
                'slug' => self::DAVINCI_RESOLVE_TEMPLATES_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 0,
                'sidebar_order' => 0,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::DAVINCI_RESOLVE_TEMPLATES_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function premiereRushTemplatesSubCategories()
    {
        $data = [
            [
                'id' => self::PREMIERE_RUSH_TEMPLATES_TITLES_ID,
                'slug' => self::PREMIERE_RUSH_TEMPLATES_TITLES,
                'name' => 'Titles',
                'order' => 0,
                'sidebar_order' => 1,
            ],
            [
                'id' => self::PREMIERE_RUSH_TEMPLATES_TRANSITIONS_ID,
                'slug' => self::PREMIERE_RUSH_TEMPLATES_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 0,
                'sidebar_order' => 2,
            ],
            [
                'id' => self::PREMIERE_RUSH_TEMPLATES_FREE_ID,
                'slug' => self::PREMIERE_RUSH_TEMPLATES_FREE,
                'name' => 'Free',
                'order' => 99,
                'sidebar_order' => 99,
            ],
            [
                'id' => self::PREMIERE_RUSH_TEMPLATES_LOWER_THIRDS_ID,
                'slug' => self::PREMIERE_RUSH_TEMPLATES_LOWER_THIRDS,
                'name' => 'Lower Thirds',
                'order' => 0,
                'sidebar_order' => 3,
            ],
            [
                'id' => self::PREMIERE_RUSH_TEMPLATES_OVERLAYS_ID,
                'slug' => self::PREMIERE_RUSH_TEMPLATES_OVERLAYS,
                'name' => 'Overlays',
                'order' => 0,
                'sidebar_order' => 4,
            ],
            [
                'id' => self::PREMIERE_RUSH_TEMPLATES_BACKGROUNDS_ID,
                'slug' => self::PREMIERE_RUSH_TEMPLATES_BACKGROUNDS,
                'name' => 'Backgrounds',
                'order' => 0,
                'sidebar_order' => 4,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::PREMIERE_RUSH_TEMPLATES_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function davinciResolveMacrosSubCategories()
    {
        $data = [
            [
                'id' => self::DAVINCI_RESOLVE_MACROS_TITLES_ID,
                'slug' => self::DAVINCI_RESOLVE_MACROS_TITLES,
                'name' => 'Titles',
                'order' => 10,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_MACROS_TRANSITIONS_ID,
                'slug' => self::DAVINCI_RESOLVE_MACROS_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 20,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_MACROS_LOGO_ID,
                'slug' => self::DAVINCI_RESOLVE_MACROS_LOGO,
                'name' => 'Logo',
                'order' => 30,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_MACROS_BACKGROUNDS_ID,
                'slug' => self::DAVINCI_RESOLVE_MACROS_BACKGROUNDS,
                'name' => 'Backgrounds',
                'order' => 40,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_MACROS_OVERLAYS_ID,
                'slug' => self::DAVINCI_RESOLVE_MACROS_OVERLAYS,
                'name' => 'Overlays',
                'order' => 50,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_MACROS_FREE_ID,
                'slug' => self::DAVINCI_RESOLVE_MACROS_FREE,
                'name' => 'Free',
                'order' => 999,
                'sidebar_order' => 0,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::DAVINCI_RESOLVE_MACROS_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

    protected function finalCutProXSubCategories()
    {
        $data = [
            [
                'id' => self::FINAL_CUT_PRO_TEMPLATES_TITLES_ID,
                'slug' => self::FINAL_CUT_PRO_TEMPLATES_TITLES,
                'name' => 'Titles',
                'order' => 70,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::FINAL_CUT_PRO_TEMPLATES_PHOTO_VIDEO_ID,
                'slug' => self::FINAL_CUT_PRO_TEMPLATES_PHOTO_VIDEO,
                'name' => 'Photo Video',
                'order' => 80,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::FINAL_CUT_PRO_TEMPLATES_LOGO_ID,
                'slug' => self::FINAL_CUT_PRO_TEMPLATES_LOGO,
                'name' => 'Logo',
                'order' => 90,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::FINAL_CUT_PRO_TEMPLATES_TRANSITIONS_ID,
                'slug' => self::FINAL_CUT_PRO_TEMPLATES_TRANSITIONS,
                'name' => 'Transitions',
                'order' => 100,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::FINAL_CUT_PRO_TEMPLATES_BACKGROUNDS_ID,
                'slug' => self::FINAL_CUT_PRO_TEMPLATES_BACKGROUNDS,
                'name' => 'Backgrounds',
                'order' => 110,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::FINAL_CUT_PRO_TEMPLATES_OVERLAYS_ID,
                'slug' => self::FINAL_CUT_PRO_TEMPLATES_OVERLAYS,
                'name' => 'Overlays',
                'order' => 120,
                'sidebar_order' => 0,
            ],
            [
                'id' => self::FINAL_CUT_PRO_TEMPLATES_FREE_ID,
                'slug' => self::FINAL_CUT_PRO_TEMPLATES_FREE,
                'name' => 'Free',
                'order' => 999,
                'sidebar_order' => 0,
            ],
        ];

        return collect($data)
            ->map(function ($row) {
                $defaults = [
                    'deleted_at' => null,
                    'category_id' => Categories::FINAL_CUT_PRO_TEMPLATES_ID,
                ];
                return array_merge($defaults, $row);
            })->toArray();
    }

}
