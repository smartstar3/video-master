<?php namespace MotionArray\Repositories;

use MotionArray\Models\Project;
use MotionArray\Repositories\EloquentBaseRepository;
use MotionArray\Models\PreviewFile;
use MotionArray\Models\PreviewUpload;
use AWS;

class PreviewFileRepository extends EloquentBaseRepository
{
    public function __construct(PreviewFile $previewFile)
    {
        $this->model = $previewFile;
    }

    public function findMissingFiles(PreviewUpload $previewUpload)
    {
        $s3 = AWS::get('s3');

        $previewFiles = $previewUpload->files;

        return $previewFiles->filter(function ($file, $key) use ($s3) {
            $filename = basename($file->url);

            $exists = $s3->doesObjectExist(Project::previewsBucket(), $filename);

            return !$exists;
        });
    }

    /**
     * Gets the selected placeholder, defaults to any available placeholder
     *
     * @param PreviewUpload $previewUpload
     * @param string $quality
     * @return mixed
     */
    public function findPlaceholder(PreviewUpload $previewUpload, $quality = 'high')
    {
        $previewFile = $previewUpload->placeholder;

        if ($previewFile) {

            // Get the required quality for the selected placeholder
            if ($quality != $previewFile->quality) {
                $otherVersions = $this->getPreviewFileVersions($previewFile, $quality, false);

                if ($otherVersions && $otherVersions->count()) {
                    $previewFile = $otherVersions->first();
                }
            }

        } else {
            $previewFile = $previewUpload->thumbnails()->where('label', 'LIKE', '%' . $quality)->first();

            // Get any placeholder available
            if (!$previewFile) {
                $previewFile = $previewUpload->thumbnails()->first();
            }
        }

        return $previewFile;
    }

    /**
     * Find preview files according to given format and quality
     *
     * @param PreviewUpload $previewUpload
     * @param $format
     * @param string $quality
     * @return mixed
     */
    public function getPreviewFiles(PreviewUpload $previewUpload, $format = null, $quality = 'high')
    {
        $previewFilesQuery = $previewUpload->files();

        $formatSynonyms = [
            ['mp4', 'mpeg4'],
            ['ogg', 'ogv'],
            ['mp3', 'mpeg audio'],
            ['hls', 'mpeg-ts', 'm3u8']
        ];

        if ($format) {
            $formats = [$format];

            foreach ($formatSynonyms as $formatSynonymsGroup) {
                if (in_array($format, $formatSynonymsGroup)) {
                    $formats = $formatSynonymsGroup;
                }
            }

            $previewFilesQuery->whereIn('format', $formats);
        }

        if ($quality) {
            $previewFilesQuery->where('label', 'LIKE', '%' . $quality);
        }

        return $previewFilesQuery->get();
    }

    /**
     * Gets additional versions for given file (with different resolutions)
     *
     * @param PreviewFile $previewFile
     * @param null $quality
     * @param bool $includeCurrentUrl
     * @return mixed
     */
    public function getPreviewFileVersions(PreviewFile $previewFile, $quality = null, $includeCurrentUrl = true)
    {
        $urlParts = parse_url($previewFile->url);

        if (isset($urlParts['path'])) {
            $uri = $urlParts['path'];

            preg_match('#_([0-9]{4,5})\.([A-z]{2,5})$#i', $uri, $matches);

            $urlLike = null;

            if (isset($matches[1])) {
                $previewNumber = $matches[1];

                $format = $matches[2];

                $urlLike = '%' . $previewNumber . '.' . $format;
            } else {
                $urlLike = '%' . str_replace('-low.', '-high.', $uri);
            }

            if ($urlLike) {
                $query = $this->model->where('preview_upload_id', '=', $previewFile->preview_upload_id)
                    ->where('url', 'LIKE', $urlLike);

                if (!$includeCurrentUrl) {
                    $query->where('id', '!=', $previewFile->id);
                }

                if ($quality) {
                    $query->where('label', 'LIKE', '%' . $quality);
                }

                return $query->get();
            }
        }
    }

    /**
     * Returns all the file records for a given version url
     *
     * @param $url
     * @param null $quality
     * @param bool $includeCurrentUrl
     * @return mixed
     */
    public function getPlaceholderVersionsByUrl($url, $quality = null, $includeCurrentUrl = true)
    {
        $urlParts = parse_url($url);

        $uri = $urlParts['path'];

        $previewFile = $this->model->where('url', 'LIKE', '%' . $uri . '%')->first();

        if ($previewFile) {
            return $this->getPreviewFileVersions($previewFile, $quality, $includeCurrentUrl);
        }
    }
}
