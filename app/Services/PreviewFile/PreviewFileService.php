<?php

namespace MotionArray\Services\PreviewFile;

use MotionArray\Helpers\Helpers;
use MotionArray\Services\Cdn\PreviewCdnChecker;

class PreviewFileService
{
    /**
     * @var PreviewCdnChecker
     */
    protected $previewCdnChecker;

    public function __construct(PreviewCdnChecker $previewCdnChecker)
    {
        $this->previewCdnChecker = $previewCdnChecker;
    }

    public function preparePreviewFiles(array $previewFiles, $isMusic)
    {
        return collect($previewFiles)
            ->map(function ($preview) use ($isMusic) {
                if ($this->previewCdnChecker->shouldUseCDN()) {
                    $url = $preview['url'];
                } else {
                    $url = $preview['url_fallback'];
                }

                $url = Helpers::convertToHttps($url);

                $format = $preview['format'];

                // mpeg4 is an invalid mimetype, it should have been mp4.
                if ($format === 'mpeg4') {
                    $format = 'mp4';
                }

                // mpeg audio is an invalid mimetype, it should have been mpeg.
                if ($format === 'mpeg audio') {
                    $format = 'mpeg';
                }

                if ($isMusic) {
                    $mimeType = 'audio/' . $format;
                } else {
                    $mimeType = 'video/' . $format;
                }

                return [
                    'label' => $preview['label'],
                    'mime_type' => $mimeType,
                    'url' => $url,
                ];
            })
            ->toArray();
    }

}
