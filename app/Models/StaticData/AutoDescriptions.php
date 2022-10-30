<?php

namespace MotionArray\Models\StaticData;

class AutoDescriptions extends StaticDBData
{
    public const SENTENCE_1 = 'sentence-1';
    public const SENTENCE_1_ID = 1;

    public const SENTENCE_2 = 'sentence-2';
    public const SENTENCE_2_ID = 2;

    public const SENTENCE_3 = 'sentence-3';
    public const SENTENCE_3_ID = 3;

    public const SENTENCE_4 = 'sentence-4';
    public const SENTENCE_4_ID = 4;

    protected $modelClass = \MotionArray\Models\AutoDescription::class;

    protected $data = [
        [
            'id' => self::SENTENCE_1_ID,
            'name' => self::SENTENCE_1,
            'category' => 'stock-video-description',
            'data' => [
                'variables' => [
                    'verb_1' => [
                        'features',
                        'contains',
                        'consists of',
                        'shows',
                        'displays',
                        'exhibits'
                    ],
                    'phrase_1' => [
                        'piece of footage',
                        'piece of video',
                        'video clip',
                        'footage clip',
                        'bit of video',
                        'bit of footage'
                    ],
                    'adjective_1' => [
                        'stunning',
                        'beautiful',
                        'amazing',
                        'awesome',
                        'fine',
                        'gorgeous',
                        'incredible',
                        'wonderful',
                        'excellent',
                        'great'
                    ]
                ],
                'sentence_structures' => [
                    'The {name} stock video is a {adjective_1} {phrase_1} that {verb_1} ___',
                    '{name} is a {adjective_1} stock video that {verb_1} footage of ___',
                    '{name} is a stock video that {verb_1} {adjective_1} footage of ___'
                ]
            ]


        ],
        [
            'id' => self::SENTENCE_2_ID,
            'name' => self::SENTENCE_2,
            'category' => 'stock-video-description',
            'data' => [
                'variables' => [
                    'phrase_1' => [
                        'footage',
                        'video',
                        'video clip',
                        'footage',
                        'bit of video',
                        'bit of footage'
                    ],
                    'phrase_2' => [
                        'relates to',
                        'has to do with',
                        'depicts'
                    ],
                    'adjective_1' => [
                        'perfect',
                        'ideal',
                        'excellent',
                        'fitting',
                        'applicable',
                        'apt',
                        'suitable'
                    ],
                    'adjective_2' => [
                        'stunning',
                        'beautiful',
                        'amazing',
                        'awesome',
                        'fine',
                        'gorgeous',
                        'incredible',
                        'wonderful',
                        'excellent',
                        'great'
                    ]
                ],
                'sentence_structures' => [
                    'This {resolution} {phrase_1} is {adjective_1} to use in any project that {phrase_2} ___',
                    'You can use this {resolution} {phrase_1} in any project that {phrase_2} ___',
                    'This {resolution} {phrase_1} will look {adjective_2} in any video project that {phrase_2} ___'
                ]
            ]
        ],
        [
            'id' => self::SENTENCE_3_ID,
            'name' => self::SENTENCE_3,
            'category' => 'stock-video-description',
            'data' => [
                'variables' => [
                    'verb_1' => [
                        'add',
                        'include',
                        'incorporate',
                        'use'
                    ],
                    'verb_2' => [
                        'download',
                        'grab',
                        'get',
                        'get your hands on',
                        'snap up'
                    ],
                    'phrase_1' => [
                        'film',
                        'edit',
                        'project',
                        'video',
                        'intro',
                        'movie',
                        'Youtube video',
                        'social media campaign',
                        'documentary'
                    ],
                    'phrase_2' => [
                        'video',
                        'clip',
                        'footage'
                    ]
                ],
                'sentence_structures' => [
                    '{verb_1} this footage in your next {phrase_1}, {phrase_1}, {phrase_1}, etc',
                    'This {phrase_2} will look great in your next {phrase_1}, {phrase_1}, or {phrase_1}.',
                    '{verb_2} this {phrase_2} today, and {verb_1} it to your next {phrase_1}, {phrase_1}, or {phrase_1}'
                ]
            ],
        ],
        [
            'id' => self::SENTENCE_4_ID,
            'name' => self::SENTENCE_4,
            'category' => 'stock-video-description',
            'data' => [
                'variables' => [
                    'noun_1' => [
                        'viewers',
                        'audience'
                    ],
                    'noun_2' => [
                        'project\'s',
                        'video\'s',
                        'film\'s'
                    ],
                    'verb_1' => [
                        'blow away',
                        'amaze',
                        'astonish',
                        'impress',
                        'thrill'
                    ],
                    'phrase_1' => [
                        'blown away',
                        'amazed',
                        'astonished',
                        'super impressed',
                        'thrilled'
                    ],
                    'phrase_2' => [
                        'to another level',
                        'to a whole new level',
                        'up a notch',
                        'up a level'
                    ]
                ],
                'sentence_structures' => [
                    'Take your {noun_2} production value {phrase_2}, and {verb_1} your {noun_1}',
                    '{verb_1} your {noun_1}, and take your {noun_2} production value {phrase_2}',
                    'Your {noun_1} will be {phrase_1}',
                    'All of your viewers will be absolutely {phrase_1}',
                    'Download now.'
                ]
            ]
        ]
    ];
}
