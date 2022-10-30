<?php namespace MotionArray\Repositories;

use MotionArray\Events\Encoder\PreviewsStored;
use MotionArray\Jobs\DeleteS3Files;
use MotionArray\Models\Output;
use MotionArray\Models\PreviewFile;
use MotionArray\Models\PreviewUpload;
use MotionArray\Models\Product;
use MotionArray\Models\Project;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
use AWS;
use Exception;

class PreviewUploadRepository
{
    /**
     * Find by Id
     *
     * @param $previewUploadId
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     */
    public function findById($previewUploadId)
    {
        $previewUpload = PreviewUpload::with('videoFiles')->find($previewUploadId);

        return $previewUpload;
    }

    /**
     * Returns active preview for the given project
     *
     * @param Project $project
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|PreviewUploadRepository|null|static|static[]
     */
    public function findActiveByProject(Project $project)
    {
        $previewUploadId = $project->active_preview_id;

        if ($previewUploadId) {
            return $this->findById($previewUploadId);
        }
    }

    /**
     * Find by Version
     *
     * @param $projectId
     * @param $version
     *
     * @return mixed
     */
    public function findByVersion($projectId, $version)
    {
        $project = Project::find($projectId);

        if ($project) {
            $previewUpload = $project->previewUploads()
                ->with('videoFiles')
                ->where('version', '=', $version)
                ->first();

            return $previewUpload;
        }
    }

    /**
     * Find multiple Previews, by project Id
     *
     * @param $projectId
     *
     * @return array
     */
    public function findByProject($projectId)
    {
        $project = Project::find($projectId);

        $previews = [];

        if ($project) {
            $previews = $project->previewUploads;
        }

        return $previews;
    }


    /**
     * Find multiple Previews, by product Id
     *
     * @param $productId
     *
     * @return array
     */
    public function findByProduct($productId)
    {
        $product = Product::find($productId);

        $previews = [];

        if ($product) {
            $previews = $product->previewUploads;
        }

        return $previews;
    }

    /**
     *
     * @param Output $output
     * @param $notify
     */
    public function storeJobPreviews(Output $output, $notify = false)
    {
        $encoder = app()->make('MotionArray\Services\Encoding\EncodingInterface');

        $producerMailer = app()->make('MotionArray\Mailers\ProducerMailer');

        $previewUpload = $output->previewUpload;

        if (!$previewUpload) {
            throw new Exception('Error while saving encoded videos, preview upload is null for Output ' . $output->id);

            $output->delete();

            return;
        }

        $uploadable = $previewUpload->uploadable()->first();

        $jobDetails = $encoder->getJobDetails($output->job_id);

        if ($jobDetails->job->state == "finished") {
            $jobInput = $jobDetails->job->input_media_file;

            if (!$previewUpload->fps) {
                $previewUpload->fps = $jobInput->frame_rate;
                $previewUpload->duration_in_ms = $jobInput->duration_in_ms;
                $previewUpload->save();
            }

            $this->storePreviews($previewUpload, $jobDetails);

            $uploadable->event_code_id = 1;

            $uploadable->save();

            if ($notify) {
                $producer = $uploadable->owner;

                $producerMailer->encodingComplete($producer, $uploadable);
            }

            \syncEvent(PreviewsStored::class, [$previewUpload, false, $jobDetails->job]);
        } else {
            $job = (array)$jobDetails->job;

            \Log::alert('provided job is not yet finished ', $job);
        }
    }

    /**
     * Deletes given PreviewUpload and related files
     *
     * @param PreviewUpload $previewUpload
     * @param bool $includeFiles
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete(PreviewUpload $previewUpload, $includeFiles = true)
    {
        if ($includeFiles) {
            dispatch(new DeleteS3Files($previewUpload));
        }

        return $previewUpload->delete();
    }

    /**
     * Delete all files related to given PreviewUpload
     *
     * @param PreviewUpload $previewUpload
     */
    public function deleteS3Files(PreviewUpload $previewUpload)
    {
        $s3 = AWS::get('s3');

        $uploadable = new $previewUpload->uploadable_type;

        $bucket = $uploadable->previewsBucket();
        $bucketUrl = $uploadable->bucketUrl();

        // 1. Delete any completed encodings
        foreach ($previewUpload->files as $file) {
            $this->s3LogDelete($previewUpload, $file->url);

            $s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => str_replace($bucketUrl, '', $file->url)
            ]);
        }

        $previewUpload->files()->delete();

        // 2. Delete staged file from AWS
        $this->deleteStagedFile($previewUpload);

        // 3. Delete outputs / Encoding Files
        $outputs = $previewUpload->outputs;
        $jobs = $outputs->unique('job_id');
        $encoder = \App::make('MotionArray\Services\Encoding\EncodingInterface');

        foreach ($jobs as $job) {
            $encoder->cancelJob($job);
        }

        foreach ($outputs as $output) {
            $this->s3LogDelete($previewUpload, $output->url);

            $s3->deleteObject([
                'Bucket' => $uploadable->previewsBucket(),
                'Key' => str_replace($uploadable->bucketUrl(), '', $output->url)
            ]);
        }

        $previewUpload->outputs()->delete();
    }

    /**
     * Cleans after successful upload
     *
     * @param null $uploadableId
     */
    public function cleanUp(PreviewUpload $previewUpload, $jobDetails)
    {
        $previewUpload->outputs()->where('job_id', '=', $jobDetails->id)->delete();

        // If there no outputs left, delete staged file
        if (!$previewUpload->outputs()->count()) {
            $this->deleteStagedFile($previewUpload);
        }

        if (!$previewUpload->outputs()->where('label', '=', 'mp4 high')->count()) {
            $previewUpload->encoding_status_id = 8;
            $previewUpload->save();
        }
    }

    /**
     * Store previews on DB using the zencoder response
     *
     * @param $uploadable_id
     * @param $details
     */
    public function storePreviews(PreviewUpload $previewUpload, $jobDetails)
    {
        $this->storeThumbnails($previewUpload, $jobDetails);

        $this->storeOriginalFile($previewUpload, $jobDetails);

        $this->storeMediaFiles($previewUpload, $jobDetails);
    }

    /**
     * @param PreviewUpload $previewUpload
     * @param $jobDetails
     */
    protected function storeThumbnails(PreviewUpload $previewUpload, $jobDetails)
    {
        $thumbnails = $jobDetails->job->thumbnails;
        $uploadable = new $previewUpload->uploadable_type;
        $s3 = AWS::get('s3');

        uasort($thumbnails, function ($a, $b) {
            $resolutionsOrder = [1 => 'low', 2 => 'mid', 3 => 'high', 4 => 'low_custom', 5 => 'high_custom'];

            $aRes = (explode(' ', trim($a->group_label)));
            $bRes = (explode(' ', trim($b->group_label)));

            $aRes = array_pop($aRes);
            $bRes = array_pop($bRes);

            $aKey = array_search($aRes, $resolutionsOrder);
            $bKey = array_search($bRes, $resolutionsOrder);

            if ($aKey && $bKey) {
                if ($aKey == $bKey) {
                    $m1 = preg_match('/_([0-9]+).jpg$/', $a->url, $matchesA);
                    $m2 = preg_match('/_([0-9]+).jpg$/', $b->url, $matchesB);

                    if ($m1 && $m2) {
                        return intval($matchesA[1]) < intval($matchesB[1]) ? -1 : 1;
                    }
                }

                $r = ($aKey < $bKey) ? -1 : 1;

                return $r;
            }
        });

        // Save preview thumbnails
        foreach ($thumbnails as $key => $thumbnail) {
            $exists = false;

            preg_match('/_([0-9]+).jpg$/', $thumbnail->url, $matches);

            $uploadNumber = $matches[1];

            if (isset($thumbnail->height)) {
                $exists = !!$previewUpload->files()
                    ->where([
                        'format' => $thumbnail->format,
                        'height' => $thumbnail->height,
                        'width' => $thumbnail->width,
                    ])
                    ->where('url', '!=', $thumbnail->url)
                    ->where('url', 'LIKE', '%' . $uploadNumber . '.jpg')
                    ->count();
            }

            if ($exists) {
                $this->s3LogDelete($previewUpload, $thumbnail->url);

                $s3->deleteObject([
                    'Bucket' => $uploadable->previewsBucket(),
                    'Key' => str_replace($uploadable->bucketUrl(), '', $thumbnail->url)
                ]);
            } else {
                $previewUpload->files()->create([
                    'label' => $thumbnail->group_label,
                    'format' => $thumbnail->format,
                    'url' => $thumbnail->url,
                    'file_size_bytes' => $thumbnail->file_size_bytes ? $thumbnail->file_size_bytes : 0,
                    'height' => isset($thumbnail->height) ? $thumbnail->height : 0,
                    'width' => isset($thumbnail->width) ? $thumbnail->width : 0
                ]);
            }
        }

        if ($previewUpload) {
            $thumbnails = $previewUpload->thumbnails()->get();

            if ($thumbnails && $thumbnails->count() && !$previewUpload->placeholder_id) {
                $randomThumbnail = $thumbnails->random();

                $previewUpload->update(["placeholder_id" => $randomThumbnail->id]);
            }
        }
    }

    /**
     * Creates the records for the encoded files
     *
     * @param PreviewUpload $previewUpload
     * @param $jobDetails
     */
    protected function storeMediaFiles(PreviewUpload $previewUpload, $jobDetails)
    {
        $s3 = AWS::get('s3');
        $uploadable = new $previewUpload->uploadable_type;

        // Order by resolution
        // Low first
        $outputs = $jobDetails->job->output_media_files;

        uasort($outputs, function ($a, $b) {
            $resolutionsOrder = [1 => 'low', 2 => 'mid', 3 => 'high'];

            $aRes = (explode(' ', trim($a->label)));
            $bRes = (explode(' ', trim($b->label)));

            $aRes = array_pop($aRes);
            $bRes = array_pop($bRes);

            $aKey = array_search($aRes, $resolutionsOrder);
            $bKey = array_search($bRes, $resolutionsOrder);

            if ($aKey && $bKey) {
                $r = ($aKey < $bKey) ? -1 : 1;

                return $r;
            }
        });

        // Save outputs
        //
        foreach ($outputs as $output) {
            // Fix for: Keeping multiple copies of same size, format when size is too small
            // If format and width exists for this preview, then delete
            $exists = false;

            if (isset($output->height)) {
                $exists = !!$previewUpload->files()
                    ->where([
                        'format' => $output->format,
                        'height' => $output->height,
                        'width' => $output->width
                    ])
                    ->where('url', '!=', $output->url)
                    ->where('label', '!=', PreviewFile::ORIGINAL)
                    ->count();
            }

            if ($exists || strpos($output->label, "placeholders") !== false) {
                $this->s3LogDelete($previewUpload, $output->url);
                $s3->deleteObject([
                    'Bucket' => $uploadable->previewsBucket(),
                    'Key' => str_replace($uploadable->bucketUrl(), '', $output->url)
                ]);
            } else {
                $previewUpload->files()->create([
                    'label' => $output->label,
                    'format' => $output->format,
                    'url' => $output->url,
                    'file_size_bytes' => $output->file_size_bytes ? $output->file_size_bytes : 0,
                    'height' => isset($output->height) ? $output->height : 0,
                    'width' => isset($output->width) ? $output->width : 0
                ]);

                $url = $output->url;
                $info = pathinfo($url);
                $fileName = basename($url, '.' . $info['extension']);
                $n = 0;

                if (strpos($output->label, "hls") !== false) {
                    do {
                        $n++;

                        $partFilename = $fileName . '-' . str_pad($n, 5, "0", STR_PAD_LEFT) . '.ts';

                        $tsExists = $s3->doesObjectExist($uploadable->previewsBucket(), $partFilename);

                        if ($tsExists) {
                            $previewUpload->files()->create([
                                'label' => 'ts-section',
                                'format' => 'ts-section',
                                'url' => $uploadable->bucketUrl() . $partFilename,
                                'file_size_bytes' => $output->file_size_bytes ? $output->file_size_bytes : 0,
                                'height' => isset($output->height) ? $output->height : 0,
                                'width' => isset($output->width) ? $output->width : 0
                            ]);
                        }
                    } while ($tsExists);
                }
            }
        }
    }

    /**
     * Stores Original file in S3
     *
     * @param PreviewUpload $previewUpload
     * @param $jobDetails
     */
    protected function storeOriginalFile(PreviewUpload $previewUpload, $jobDetails)
    {
        $s3 = AWS::get('s3');

        $uploadable = new $previewUpload->uploadable_type;

        $input = $jobDetails->job->input_media_file;
        $filenameParts = explode('/staging/', $input->url);
        if (isset($filenameParts[1])) {
            $filename = $filenameParts[1]; // Filename without prefix
        } else {
            $filename = basename($filenameParts[0]);
        }

        // Save original File
        if ($uploadable->isProject()) {
            $originalExists = $previewUpload->files()->where('label', '=', PreviewFile::ORIGINAL)->count();

            // Original File
            if ($previewUpload->preview_file_path && !$originalExists) {
                $fileDestination = str_replace('preview-', 'original-', $filename);

                $source = $uploadable->previewsBucket() . "/staging/" . $filename;

                $previewUpload->files()->create([
                    'label' => PreviewFile::ORIGINAL,
                    'format' => $previewUpload->preview_extension,
                    'url' => $uploadable->bucketUrl() . $fileDestination,
                    'file_size_bytes' => $input->file_size_bytes,
                    'height' => isset($input->height) ? $input->height : 0,
                    'width' => isset($input->width) ? $input->width : 0
                ]);

                $s3->copyObject([
                    'Bucket' => $uploadable->previewsBucket(),
                    'Key' => $fileDestination,
                    'CopySource' => $source,
                ]);
            }
        }
    }

    /**
     * Deletes intial file
     *
     * @param PreviewUpload $previewUpload
     */
    protected function deleteStagedFile(PreviewUpload $previewUpload)
    {
        $s3 = AWS::get('s3');

        $uploadable = new $previewUpload->uploadable_type;

        if ($previewUpload->preview_file_path) {

            $this->s3LogDelete($previewUpload, $previewUpload->preview_file_path);

            // Delete staged file from AWS
            $s3->deleteObject([
                'Bucket' => $uploadable->previewsBucket(),
                'Key' => str_replace($uploadable->bucketUrl(), '', $previewUpload->preview_file_path)
            ]);
        }

        // Update product
        $previewUpload->preview_file_path = '';
        $previewUpload->preview_filename = '';
        $previewUpload->preview_extension = '';
        $previewUpload->save();
    }

    public function s3LogDelete($previewUpload, $url = null)
    {
        $uploadable = $previewUpload->uploadable()->withTrashed()->first();

        if ($uploadable && $uploadable->owner && $uploadable->owner->email == 'aj.rugama@gmail.com') {
            Bugsnag::notifyException(new RuntimeException("S3 Delete Object " . $url));
        }

        if (!$uploadable) {
            $id = '';
            if ($previewUpload && isset($previewUpload->id)) {
                $id = $previewUpload->id;
            }

            Bugsnag::notifyException(new RuntimeException("S3 Delete Object previewUpload invalid " . $id));
        }
    }

    /**
     *  Set Approved flag to true
     * @param $projectId
     * @param $version
     */
    public function approveRevision($projectId, $version)
    {
        $previewUpload = $this->findByVersion($projectId, $version);

        $previewUpload->is_approved = true;
        $previewUpload->save();
    }
}
