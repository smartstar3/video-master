<?php

namespace MotionArray\Services\Submission;

use MotionArray\Models\StaticData\AccessServices;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;
use MotionArray\Models\StaticData\SubmissionStatuses;
use Auth;
use AWS;
use Config;
use MotionArray\Models\Traits\Uploadable;

class SubmissionService
{
    /**
     * @var CategoriesWithRelationsHelper
     */
    protected $categoriesWithRelationsHelper;

    public function __construct(CategoriesWithRelationsHelper $categoriesWithRelationsHelper)
    {
        $this->categoriesWithRelationsHelper = $categoriesWithRelationsHelper;
    }

    /**
     * @param $accessServices
     * @param $submissionStatusId (if it is null, it will mean all status)
     * @return \Illuminate\Support\Collection
     */
    public function categoriesByAccessServiceAndStatus($accessServices, $submissionStatusId = null)
    {
        $categories = $this->categoriesWithRelationsHelper->categoriesWithSubCategories(Auth::user());
        $accessServiceIds = $accessServices->map(function ($accessService) {
            return $accessService->id;
        })->all();

        $categoryIds = [];
        $validAccessServiceIds = [];

        if (in_array($submissionStatusId,
            [
                SubmissionStatuses::NEW_ID,
                SubmissionStatuses::PENDING_ID,
                SubmissionStatuses::NEEDS_WORK_ID
            ])
        ) {
            $validAccessServiceIds = [
                AccessServices::REVIEW_AFTER_EFFECTS_ID,
                AccessServices::REVIEW_PREMIERE_PRO_ID,
                AccessServices::REVIEW_AUDIO_ID,
                AccessServices::REVIEW_VIDEO_ID
            ];
        } elseif ($submissionStatusId == SubmissionStatuses::APPROVED_ID) {
            $validAccessServiceIds = [
                AccessServices::APPROVED_AFTER_EFFECTS_ID,
                AccessServices::APPROVED_PREMIERE_PRO_ID,
                AccessServices::APPROVED_AUDIO_ID,
                AccessServices::APPROVED_VIDEO_ID
            ];
        } elseif ($submissionStatusId == null) {
            $validAccessServiceIds = [
                AccessServices::REVIEW_AFTER_EFFECTS_ID,
                AccessServices::REVIEW_PREMIERE_PRO_ID,
                AccessServices::REVIEW_AUDIO_ID,
                AccessServices::REVIEW_VIDEO_ID,
                AccessServices::APPROVED_AFTER_EFFECTS_ID,
                AccessServices::APPROVED_PREMIERE_PRO_ID,
                AccessServices::APPROVED_AUDIO_ID,
                AccessServices::APPROVED_VIDEO_ID
            ];
        }

        $filteredAccessServiceIds = array_intersect($validAccessServiceIds, $accessServiceIds);

        foreach ($filteredAccessServiceIds as $accessServiceId) {
            $categoryIds = array_merge($categoryIds, $this->categoryIdsByAccessService($accessServiceId));
        }

        $categoryIds = array_unique($categoryIds);
        $filteredCategories = $categories->filter(function ($category) use ($categoryIds) {
            return in_array($category->id, $categoryIds);
        })->values();

        return $filteredCategories;
    }
    
    public function prepareProductJson(Uploadable $uploadable)
    {
        if($uploadable->isProject()) {
            $uploadable->load('reviewSettings');
            // Add Video files for uploads section
            if ($uploadable->activePreview) {
                $uploadable->activePreview->load('videoFiles');
            }
        }

        $response = $uploadable->toArray();
        $response['previews'] = $uploadable->activePreviewFiles();
        $response['tags'] = $uploadable->tags()->get()->toArray();

        $previewType = $uploadable->preview_type;
        $previewPolicy = $uploadable->generateAWSPolicy($previewType);
        $response['preview'] = [
            'bucket' => $uploadable->getBucket($previewType),
            'awsKey' => $uploadable->getAWSKey(),
            'awsPolicy' => $previewPolicy,
            'awsSignature' => $uploadable->generateAWSSignature($previewPolicy),
            'bucketKey' => $uploadable->getBucketKey($previewType),
            'newFilename' => $uploadable->generateFilename($uploadable->previewPrefix . $uploadable->id)
        ];
        
        if($uploadable->isProject()) {
            $response['aws'] = $uploadable->getAwsAttribute();
        } else {
            $response['downloads'] = $uploadable->downloads()->count();
            $response['seller'] = $uploadable->seller()->first()->toArray();
            $response['category'] = $uploadable->category()->first()->toArray();
            $response['sub_categories'] = $uploadable->subCategories()->get()->toArray();
            $response['compressions'] = $uploadable->compressions()->get()->toArray();
            $response['formats'] = $uploadable->formats()->get()->toArray();
            $response['resolutions'] = $uploadable->resolutions()->get()->toArray();
            $response['versions'] = $uploadable->versions()->get()->toArray();
            $response['bpms'] = $uploadable->bpms()->get()->toArray();
            $response['fpss'] = $uploadable->fpss()->get()->toArray();
            $response['sample_rates'] = $uploadable->sampleRates()->get()->toArray();
            $response['plugins'] = $uploadable->plugins()->get()->toArray();
            $response['music'] = $uploadable->music()->get()->toArray();
            $response['model_releases'] = $uploadable->modelReleases()->get()->toArray();

            $response['encoding_status_id'] = $uploadable->previewUploads()->count() > 0 ? $uploadable->previewUploads->last()->encoding_status_id : 1;//TODO: This should be checked by Jesus as i'm not sure the logic is correct

            $packagePolicy = $uploadable->generateAWSPolicy('package');
            $response['package'] = ['bucket' => $uploadable->getBucket('package'),
                'awsKey' => $uploadable->getAWSKey(),
                'awsPolicy' => $packagePolicy,
                'awsSignature' => $uploadable->generateAWSSignature($packagePolicy),
                'bucketKey' => $uploadable->getBucketKey('package'),
                'newFilename' => $uploadable->generateFilename($uploadable->packagePrefix . $uploadable->id),];
            $response['entry']['preview_type'] = $uploadable->preview_type;

            /**
             * Setup signed package url
             */
            if ($response['package_filename']) {
                $s3 = AWS::get('s3');
                $bucket = Config::get("aws.packages_bucket");

                $filename = $response['package_filename'] . "." . $response['package_extension'];
                $response['package_file_path'] = $s3->getObjectUrl($bucket, $filename, '+86400 minutes');
            }
        }

        return $response;
    }

    private function categoryIdsByAccessService($accessServiceId)
    {
        $categoryIds = [];

        switch ($accessServiceId) {
            case AccessServices::REVIEW_AFTER_EFFECTS_ID:
            case AccessServices::APPROVED_AFTER_EFFECTS_ID:
                $categoryIds = [
                    Categories::AFTER_EFFECTS_TEMPLATES_ID,
                    Categories::AFTER_EFFECTS_PRESETS_ID
                ];
                break;
            case AccessServices::REVIEW_PREMIERE_PRO_ID:
            case AccessServices::APPROVED_PREMIERE_PRO_ID:
                $categoryIds = [
                    Categories::PREMIERE_PRO_TEMPLATES_ID,
                    Categories::MOTION_GRAPHICS_TEMPLATES_ID,
                    Categories::PREMIERE_PRO_PRESETS_ID
                ];
                break;
            case AccessServices::REVIEW_AUDIO_ID:
            case AccessServices::APPROVED_AUDIO_ID:
                $categoryIds = [
                    Categories::STOCK_MUSIC_ID,
                    Categories::SOUND_EFFECTS_ID
                ];
                break;
            case AccessServices::REVIEW_VIDEO_ID:
            case AccessServices::APPROVED_VIDEO_ID:
                $categoryIds = [
                    Categories::STOCK_VIDEO_ID,
                    Categories::STOCK_MOTION_GRAPHICS_ID
                ];
                break;
            default:
                break;
        }

        return $categoryIds;
    }
}
