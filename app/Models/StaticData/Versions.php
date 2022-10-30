<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\Version;

class Versions extends StaticDBData
{
    public const AFTER_EFFECTS_CS4 = 'after-effects-cs4';
    public const AFTER_EFFECTS_CS4_ID = 1;

    public const AFTER_EFFECTS_CS5 = 'after-effects-cs5';
    public const AFTER_EFFECTS_CS5_ID = 2;

    public const AFTER_EFFECTS_CS55 = 'after-effects-cs55';
    public const AFTER_EFFECTS_CS55_ID = 3;

    public const AFTER_EFFECTS_CS6 = 'after-effects-cs6';
    public const AFTER_EFFECTS_CS6_ID = 4;

    public const AFTER_EFFECTS_CC = 'after-effects-cc';
    public const AFTER_EFFECTS_CC_ID = 5;

    public const AFTER_EFFECTS_CC_2015 = 'after-effects-cc-2015';
    public const AFTER_EFFECTS_CC_2015_ID = 14;

    public const AFTER_EFFECTS_CC_2017 = 'after-effects-cc-2017';
    public const AFTER_EFFECTS_CC_2017_ID = 13;

    public const AFTER_EFFECTS_CC_2018 = 'after-effects-cc-2018';
    public const AFTER_EFFECTS_CC_2018_ID = 12;

    public const AFTER_EFFECTS_CC_2019 = 'after-effects-cc-2019';
    public const AFTER_EFFECTS_CC_2019_ID = 18;

    public const AFTER_EFFECTS_CC_2020 = 'after-effects-cc-2020';
    public const AFTER_EFFECTS_CC_2020_ID = 31;

    public const PREMIERE_CC_20153 = 'premiere-cc-20153';
    public const PREMIERE_CC_20153_ID = 9;

    public const PREMIERE_CC_2017 = 'premiere-cc-2017';
    public const PREMIERE_CC_2017_ID = 6;

    public const PREMIERE_CC_20171 = 'premiere-cc-20171';
    public const PREMIERE_CC_20171_ID = 10;

    public const PREMIERE_CC_2018 = 'premiere-cc-2018';
    public const PREMIERE_CC_2018_ID = 11;

    public const PREMIERE_CC_2019 = 'premiere-cc-2019';
    public const PREMIERE_CC_2019_ID = 17;

    public const PREMIERE_CC_2020 = 'premiere-cc-2020';
    public const PREMIERE_CC_2020_ID = 32;

    public const DAVINCI_RESOLVE_15 = 'davinci-resolve-15';
    public const DAVINCI_RESOLVE_15_ID = 15;

    public const DAVINCI_RESOLVE_16 = 'davinci-resolve-16';
    public const DAVINCI_RESOLVE_16_ID = 20;

    public const RESOLVE_STUDIO_15 = 'resolve-studio-15';
    public const RESOLVE_STUDIO_15_ID = 16;

    public const RESOLVE_STUDIO_16 = 'resolve-studio-16';
    public const RESOLVE_STUDIO_16_ID = 21;

    public const PREMIERE_RUSH_1 = 'premiere-rush-1';
    public const PREMIERE_RUSH_1_ID = 19;

    public const PREMIERE_RUSH_1_1 = 'premiere-rush-1-1';
    public const PREMIERE_RUSH_1_1_ID = 22;

    public const FINAL_CUT_PRO_X_10_4 = 'FCPx 10.4';
    public const FINAL_CUT_PRO_X_10_4_ID = 23;

    public const FINAL_CUT_PRO_X_10_4_1 = 'FCPx 10.4.1';
    public const FINAL_CUT_PRO_X_10_4_1_ID = 24;

    public const FINAL_CUT_PRO_X_10_4_2 = 'FCPx 10.4.2';
    public const FINAL_CUT_PRO_X_10_4_2_ID = 25;

    public const FINAL_CUT_PRO_X_10_4_3 = 'FCPx 10.4.3';
    public const FINAL_CUT_PRO_X_10_4_3_ID = 26;

    public const FINAL_CUT_PRO_X_10_4_4 = 'FCPx 10.4.4';
    public const FINAL_CUT_PRO_X_10_4_4_ID = 27;

    public const FINAL_CUT_PRO_X_10_4_5 = 'FCPx 10.4.5';
    public const FINAL_CUT_PRO_X_10_4_5_ID = 28;

    public const FINAL_CUT_PRO_X_10_4_6 = 'FCPx 10.4.6';
    public const FINAL_CUT_PRO_X_10_4_6_ID = 29;

    public const FINAL_CUT_PRO_X_10_4_7 = 'FCPx 10.4.7';
    public const FINAL_CUT_PRO_X_10_4_7_ID = 30;

    protected $modelClass = Version::class;

    protected function prepareData(): array
    {
        $data = array_merge(
            $this->afterEffectsData(),
            $this->premiereProData(),
            $this->premiereRushData(),
            $this->davinciResolveData(),
            $this->davinciResolveStudioData(),
            $this->finalCutProData()
        );

        return collect($data)
            ->keyBy('id')
            ->toArray();
    }

    public function afterEffectsData()
    {
        $data = [
            [
                'id' => self::AFTER_EFFECTS_CS4_ID,
                'slug' => self::AFTER_EFFECTS_CS4,
                'name' => 'After Effects CS4+',
                'order' => 1,
                'is_retired' => true,
            ],
            [
                'id' => self::AFTER_EFFECTS_CS5_ID,
                'slug' => self::AFTER_EFFECTS_CS5,
                'name' => 'After Effects CS5+',
                'order' => 2,
                'is_retired' => true,
            ],
            [
                'id' => self::AFTER_EFFECTS_CS55_ID,
                'slug' => self::AFTER_EFFECTS_CS55,
                'name' => 'After Effects CS5.5+',
                'order' => 3,
                'is_retired' => true,
            ],
            [
                'id' => self::AFTER_EFFECTS_CS6_ID,
                'slug' => self::AFTER_EFFECTS_CS6,
                'name' => 'After Effects CS6+',
                'order' => 4,
                'is_retired' => false,
            ],
            [
                'id' => self::AFTER_EFFECTS_CC_ID,
                'slug' => self::AFTER_EFFECTS_CC,
                'name' => 'After Effects CC',
                'order' => 5,
                'is_retired' => true,
            ],
            [
                'id' => self::AFTER_EFFECTS_CC_2015_ID,
                'slug' => self::AFTER_EFFECTS_CC_2015,
                'name' => 'After Effects CC 2015',
                'order' => 6,
                'is_retired' => false,
            ],
            [
                'id' => self::AFTER_EFFECTS_CC_2017_ID,
                'slug' => self::AFTER_EFFECTS_CC_2017,
                'name' => 'After Effects CC 2017',
                'order' => 7,
                'is_retired' => false,
            ],
            [
                'id' => self::AFTER_EFFECTS_CC_2018_ID,
                'slug' => self::AFTER_EFFECTS_CC_2018,
                'name' => 'After Effects CC 2018',
                'order' => 8,
                'is_retired' => false,
            ],
            [
                'id' => self::AFTER_EFFECTS_CC_2019_ID,
                'slug' => self::AFTER_EFFECTS_CC_2019,
                'name' => 'After Effects CC 2019',
                'order' => 9,
                'is_retired' => false,
            ],
            [
                'id' => self::AFTER_EFFECTS_CC_2020_ID,
                'slug' => self::AFTER_EFFECTS_CC_2020,
                'name' => 'After Effects CC 2020',
                'order' => 10,
                'is_retired' => false,
            ],
        ];

        return collect($data)
            ->map(function ($item) {
                return array_merge($item, [
                    'application_id' => Applications::AFTER_EFFECTS_ID,
                ]);
            })
            ->toArray();
    }

    public function premiereProData()
    {
        $data = [
            [
                'id' => self::PREMIERE_CC_20153_ID,
                'slug' => self::PREMIERE_CC_20153,
                'name' => 'Premiere CC 2015.3',
                'order' => 10,
                'is_retired' => false,
            ],
            [
                'id' => self::PREMIERE_CC_2017_ID,
                'slug' => self::PREMIERE_CC_2017,
                'name' => 'Premiere CC 2017',
                'order' => 11,
                'is_retired' => false,
            ],
            [
                'id' => self::PREMIERE_CC_20171_ID,
                'slug' => self::PREMIERE_CC_20171,
                'name' => 'Premiere CC 2017.1',
                'order' => 12,
                'is_retired' => false,
            ],
            [
                'id' => self::PREMIERE_CC_2018_ID,
                'slug' => self::PREMIERE_CC_2018,
                'name' => 'Premiere CC 2018',
                'order' => 13,
                'is_retired' => false,
            ],
            [
                'id' => self::PREMIERE_CC_2019_ID,
                'slug' => self::PREMIERE_CC_2019,
                'name' => 'Premiere CC 2019',
                'order' => 14,
                'is_retired' => false,
            ],
            [
                'id' => self::PREMIERE_CC_2020_ID,
                'slug' => self::PREMIERE_CC_2020,
                'name' => 'Premiere CC 2020',
                'order' => 15,
                'is_retired' => false,
            ],
        ];

        return collect($data)
            ->map(function ($item) {
                return array_merge($item, [
                    'application_id' => Applications::PREMIERE_PRO_ID,
                ]);
            })
            ->toArray();
    }

    public function premiereRushData()
    {
        $data = [
            [
                'id' => self::PREMIERE_RUSH_1_ID,
                'slug' => self::PREMIERE_RUSH_1,
                'name' => 'Premiere Rush 1.0',
                'order' => 19,
                'is_retired' => false,
            ],
            [
                'id' => self::PREMIERE_RUSH_1_1_ID,
                'slug' => self::PREMIERE_RUSH_1_1,
                'name' => 'Premiere Rush 1.1',
                'order' => 20,
                'is_retired' => false,
            ],
        ];

        return collect($data)
            ->map(function ($item) {
                return array_merge($item, [
                    'application_id' => Applications::PREMIERE_RUSH_ID,
                ]);
            })
            ->toArray();
    }

    public function davinciResolveData()
    {
        $data = [
            [
                'id' => self::DAVINCI_RESOLVE_15_ID,
                'slug' => self::DAVINCI_RESOLVE_15,
                'name' => 'Resolve 15',
                'order' => 16,
                'is_retired' => false,
            ],
            [
                'id' => self::DAVINCI_RESOLVE_16_ID,
                'slug' => self::DAVINCI_RESOLVE_16,
                'name' => 'Resolve 16',
                'order' => 18,
                'is_retired' => false,
            ],

        ];

        return collect($data)
            ->map(function ($item) {
                return array_merge($item, [
                    'application_id' => Applications::DAVINCI_RESOLVE_ID,
                ]);
            })
            ->toArray();
    }

    public function davinciResolveStudioData()
    {
        $data = [
            [
                'id' => self::RESOLVE_STUDIO_15_ID,
                'slug' => self::RESOLVE_STUDIO_15,
                'name' => 'Resolve Studio 15',
                'order' => 15,
                'is_retired' => false,
            ],
            [
                'id' => self::RESOLVE_STUDIO_16_ID,
                'slug' => self::RESOLVE_STUDIO_16,
                'name' => 'Resolve Studio 16',
                'order' => 17,
                'is_retired' => false,
            ],
        ];

        return collect($data)
            ->map(function ($item) {
                return array_merge($item, [
                    'application_id' => Applications::DAVINCI_RESOLVE_STUDIO_ID,
                ]);
            })
            ->toArray();
    }

    public function finalCutProData()
    {
        $data = [
            [
                'id' => self::FINAL_CUT_PRO_X_10_4_ID,
                'slug' => self::FINAL_CUT_PRO_X_10_4,
                'name' => 'Final Cut Pro X 10.4',
                'order' => 1,
                'is_retired' => false,
            ],
            [
                'id' => self::FINAL_CUT_PRO_X_10_4_1_ID,
                'slug' => self::FINAL_CUT_PRO_X_10_4_1,
                'name' => 'Final Cut Pro X 10.4.1',
                'order' => 2,
                'is_retired' => false,
            ],
            [
                'id' => self::FINAL_CUT_PRO_X_10_4_2_ID,
                'slug' => self::FINAL_CUT_PRO_X_10_4_2,
                'name' => 'Final Cut Pro X 10.4.2',
                'order' => 3,
                'is_retired' => false,
            ],
            [
                'id' => self::FINAL_CUT_PRO_X_10_4_3_ID,
                'slug' => self::FINAL_CUT_PRO_X_10_4_3,
                'name' => 'Final Cut Pro X 10.4.3',
                'order' => 4,
                'is_retired' => false,
            ],
            [
                'id' => self::FINAL_CUT_PRO_X_10_4_4_ID,
                'slug' => self::FINAL_CUT_PRO_X_10_4_4,
                'name' => 'Final Cut Pro X 10.4.4',
                'order' => 5,
                'is_retired' => false,
            ],
            [
                'id' => self::FINAL_CUT_PRO_X_10_4_5_ID,
                'slug' => self::FINAL_CUT_PRO_X_10_4_5,
                'name' => 'Final Cut Pro X 10.4.5',
                'order' => 6,
                'is_retired' => false,
            ],
            [
                'id' => self::FINAL_CUT_PRO_X_10_4_6_ID,
                'slug' => self::FINAL_CUT_PRO_X_10_4_6,
                'name' => 'Final Cut Pro X 10.4.6',
                'order' => 7,
                'is_retired' => false,
            ],
            [
                'id' => self::FINAL_CUT_PRO_X_10_4_7_ID,
                'slug' => self::FINAL_CUT_PRO_X_10_4_7,
                'name' => 'Final Cut Pro X 10.4.7',
                'order' => 8,
            ],
        ];

        return collect($data)
            ->map(function ($item) {
                return array_merge($item, [
                    'application_id' => Applications::FINAL_CUT_PRO_ID,
                ]);
            })
            ->toArray();
    }

    public function getBackwardCompatibleVersionIds($versionId): array
    {
        $versions = (new static)->dataCollection();
        $version = $versions->get($versionId);
        $applicationId = $version['application_id'];

        return $versions->where('application_id', '=', $applicationId)
            ->where('order', '<', $version['order'])
            ->pluck('id')
            ->toArray();
    }
}
