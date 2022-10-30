<?php

namespace MotionArray\Services\Algolia;

use MotionArray\Models\StaticData\Categories;

class AlgoliaConfigBuilder
{
    public function prodSettings(string $primaryIndex)
    {
        $baseSettings = $this->baseSettings();

        // production indexes have higher limits
        $baseSettings['paginationLimitedTo'] = 100000;

        return $this->buildSettings($primaryIndex, $baseSettings);
    }

    public function devSettings(string $primaryIndex)
    {
        $baseSettings = $this->baseSettings();

        // production indexes have higher limits
        $baseSettings['paginationLimitedTo'] = 1000;

        return $this->buildSettings($primaryIndex, $baseSettings);
    }

    protected function buildSettings(string $primaryIndex, array $baseSettings): array
    {
        $kickAssReplicaIndex = $primaryIndex . '_by_kickass';
        $downloadsReplicaIndex = $primaryIndex . '_by_downloads';

        $primaryIndexSettings = array_merge($baseSettings, [
            'replicas' => [
                $kickAssReplicaIndex,
                $downloadsReplicaIndex,
            ]
        ]);

        $kickAssReplicaIndexSettings = array_merge($baseSettings, $this->kickAssSettings());
        $downloadsReplicaIndexSettings = array_merge($baseSettings, $this->byDownloadsSettings());

        return [
            $primaryIndex => $primaryIndexSettings,
            $kickAssReplicaIndex => $kickAssReplicaIndexSettings,
            $downloadsReplicaIndex => $downloadsReplicaIndexSettings,
        ];
    }

    protected function baseSettings(): array
    {
        return [
            'minWordSizefor1Typo' => 4,
            'minWordSizefor2Typos' => 8,
            'hitsPerPage' => 20,
            'maxValuesPerFacet' => 100,
            'version' => 2,
            'typoTolerance' => 'true',
            'attributesToIndex' => [
                'objectID',
                'name',
                'categories',
                'unordered(tags)',
                'unordered(description)',
                'seller.company_name',
                'seller.firstname',
                'seller.lastname',
            ],
            'ignorePlurals' => false,
            'advancedSyntax' => false,
            'removeStopWords' => false,
            'replaceSynonymsInHighlight' => true,
            'distinct' => false,
            'numericAttributesToIndex' => null,
            'attributesToRetrieve' => null,
            'allowTyposOnNumericTokens' => false,
            'unretrievableAttributes' => [
                'downloads',
            ],
            'optionalWords' => null,
            'attributesForFaceting' => [
                'category.id',
                'categories',
                'free',
                'is_kick_ass',
                'last_impressions',
                'owned_by_ma',
                'premium',
                'published_at',
                'requested',
                'seller_id',

                'specs.cat' . Categories::AFTER_EFFECTS_TEMPLATES_ID . '.resolution',
                'specs.cat' . Categories::AFTER_EFFECTS_TEMPLATES_ID . '.version',

                'specs.cat' . Categories::STOCK_VIDEO_ID . '.resolution',

                'specs.cat' . Categories::STOCK_MOTION_GRAPHICS_ID . '.resolution',
                'specs.cat' . Categories::STOCK_MOTION_GRAPHICS_ID . '.version',

                'specs.cat' . Categories::STOCK_MUSIC_ID . '.bpm',
                'specs.cat' . Categories::STOCK_MUSIC_ID . '.duration',

                'specs.cat' . Categories::PREMIERE_PRO_TEMPLATES_ID . '.resolution',
                'specs.cat' . Categories::PREMIERE_PRO_TEMPLATES_ID . '.version',

                'specs.cat' . Categories::MOTION_GRAPHICS_TEMPLATES_ID . '.resolution',
                'specs.cat' . Categories::MOTION_GRAPHICS_TEMPLATES_ID . '.version',

                'specs.cat' . Categories::PREMIERE_PRO_PRESETS_ID . '.resolution',
                'specs.cat' . Categories::PREMIERE_PRO_PRESETS_ID . '.version',

                'specs.cat' . Categories::AFTER_EFFECTS_PRESETS_ID . '.resolution',
                'specs.cat' . Categories::AFTER_EFFECTS_PRESETS_ID . '.version',

                'specs.cat' . Categories::DAVINCI_RESOLVE_TEMPLATES_ID . '.resolution',
                'specs.cat' . Categories::DAVINCI_RESOLVE_TEMPLATES_ID . '.version',

                'specs.cat' . Categories::PREMIERE_RUSH_TEMPLATES_ID . '.resolution',
                'specs.cat' . Categories::PREMIERE_RUSH_TEMPLATES_ID . '.version',

                'specs.cat' . Categories::DAVINCI_RESOLVE_MACROS_ID . '.resolution',
                'specs.cat' . Categories::DAVINCI_RESOLVE_MACROS_ID . '.version',

                'specs.cat' . Categories::FINAL_CUT_PRO_TEMPLATES_ID . '.resolution',
                'specs.cat' . Categories::FINAL_CUT_PRO_TEMPLATES_ID . '.version',

                'unlimited',
            ],
            'attributesToSnippet' => null,
            'attributesToHighlight' => null,
            'attributeForDistinct' => null,
            'exactOnSingleWordQuery' => 'attribute',
            'disableTypoToleranceOnAttributes' => [
                'objectID',
            ],
            'ranking' => [
                'typo',
                'geo',
                'words',
                'filters',
                'proximity',
                'attribute',
                'exact',
                'custom',
            ],
            'customRanking' => [
                'desc(published_at)',
            ],
            'separatorsToIndex' => '',
            'removeWordsIfNoResults' => 'none',
            'queryType' => 'prefixNone',
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>',
            'snippetEllipsisText' => '',
            'alternativesAsExact' => [
                'ignorePlurals',
                'singleWordSynonym',
            ]
        ];
    }

    protected function kickAssSettings()
    {
        return [
            'customRanking' => [
                'desc(is_kick_ass)',
                'desc(published_at)',
            ],
        ];
    }

    protected function byDownloadsSettings()
    {
        return [
            'customRanking' => [
                'asc(free)',
                'desc(downloads)',
                'desc(published_at)',
            ],
        ];
    }

}
