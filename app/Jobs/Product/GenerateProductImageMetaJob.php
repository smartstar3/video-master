<?php

namespace MotionArray\Jobs\Product;

use Aws\S3\S3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intervention\Image\Image;
use League\Glide\Manipulators\Brightness;
use MotionArray\Models\PreviewFile;
use MotionArray\Models\Product;
use MotionArray\Models\ProductImageMeta;
use MotionArray\Models\StaticData\EncodingStatuses;
use MotionArray\Models\StaticData\EventCodes;
use MotionArray\Models\StaticData\ProductImageMetaOrientations;
use MotionArray\Models\StaticData\ProductImageTypes;
use MotionArray\Models\Traits\Uploadable;
use MotionArray\Repositories\Products\ProductRepository;

/**
 * Generates Preview files for uploaded package.
 */
class GenerateProductImageMetaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Uploadable
     */
    private $uploadable;
    /**
     * @var S3Client
     */
    private $s3Client;

    /**
     * @param Product $uploadable
     */
    public function __construct(Product $uploadable)
    {
        $this->uploadable = $uploadable;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $isImage = $this->uploadable->isImage();

        if ($isImage === false) {
            return;
        }

        $packageFileName = $this->uploadable->package_filename.'.'.$this->uploadable->package_extension;
        /* @var S3Client $s3 */
        $this->s3Client = \AWS::get('s3');
        $s3Object = $this->s3Client->getObject([
            'Bucket' => config('aws.packages_bucket'),
            'Key' => $packageFileName,
        ]);
        $originalImage = \Image::make(base64_encode($s3Object->get('Body')));
        $originalWidth = $originalImage->width();
        $originalHeight = $originalImage->height();

        $this->productImageMeta($originalImage);

        $previewUpload = $this->uploadable->previewUploads()->create([
            'encoding_status_id' => EncodingStatuses::IN_PROGRESS_ID,
        ]);

        /** @var ProductRepository $productRepository */
        $productRepository = app(ProductRepository::class);

        $productRepository->update($this->uploadable->id, [
            'encoding_status_id' => EncodingStatuses::IN_PROGRESS_ID, // In progress
            'event_code_id' => EventCodes::READY_ID,  // Ready
        ]);

        $types = (new ProductImageTypes())->data();

        $randomString = str_random(16);
        $uploadFileName = 'preview-'.$this->uploadable->id.'-'.$randomString;
        foreach ($types as $type) {
            $manipulatedImage = clone $originalImage;
            $filename = $uploadFileName.'-'.$type['slug'].'.jpg';

            $this->manipulateImage(
                $manipulatedImage,
                $originalWidth,
                $originalHeight,
                $type['has_watermark'],
                $type['max_width'],
                $type['max_height']
            );

            $uploadResponse = $this->uploadImage($manipulatedImage, $filename);

            $objectUrl = $uploadResponse['url'];
            $fileSize = $uploadResponse['file_size'];

            //Create a preview file
            $previewFile = new PreviewFile();
            $previewFile->preview_upload_id = $previewUpload->id;
            $previewFile->label = $type['preview_file_label'];
            $previewFile->format = 'jpg';
            $previewFile->url = $objectUrl;
            $previewFile->file_size_bytes = $fileSize;
            $previewFile->width = $manipulatedImage->getWidth();
            $previewFile->height = $manipulatedImage->getHeight();
            $previewFile->save();

            if ($type['id'] === ProductImageTypes::SMALL_ID) {
                $previewUpload->placeholder_id = $previewFile->id;
                $previewUpload->save();
            }
        }

        $previewUpload->encoding_status_id = EncodingStatuses::FINISHED_ID;
        $previewUpload->save();

        $productRepository->update($this->uploadable->id, [
            'event_code_id' => EventCodes::READY_ID,  // Ready
        ]);
    }

    private function uploadImage(Image $manipulatedImage, $filename)
    {
        $bucket = config('aws.previews_bucket');
        $data = $manipulatedImage->encode('jpg', 95);

        $response = $this->s3Client->putObject([
            'Bucket' => $bucket,
            'Body' => $data->getEncoded(),
            'Key' => $filename,
            'ACL' => 'public-read',
            'ContentEncoding' => 'base64',
            'ContentType' => $manipulatedImage->mime(),
            'CacheControl' => 'public, max-age=31104000',
            'Expires' => date(DATE_RFC2822, strtotime('+360 days')),
        ]);

        $url = $response->get('ObjectURL');

        $header = $this->s3Client->headObject([
            'Bucket' => config('aws.previews_bucket'),
            'Key' => $filename,
        ]);

        $fileSize = (int) $header->get('ContentLength');

        return [
            'url' => $url,
            'file_size' => $fileSize,
        ];
    }

    private function productImageMeta(Image $originalImage)
    {
        $height = $originalImage->getHeight();
        $width = $originalImage->getWidth();

        if ($height === $width) {
            $orientation = ProductImageMetaOrientations::SQUARE_ID;
        } elseif ($height < $width) {
            $orientation = ProductImageMetaOrientations::VERTICAL_ID;
        } elseif ($height > $width) {
            $orientation = ProductImageMetaOrientations::HORIZONTAL_ID;
        }

        $model = new ProductImageMeta();
        $model->product_id = $this->uploadable->id;
        $model->height = $height;
        $model->width = $width;
        $model->file_size = $this->uploadable->size;
        $model->format = 'jpg';
        $model->product_image_meta_orientation_id = $orientation;
        $model->save();

        return $model;
    }

    private function manipulateImage(
        Image $manipulatedImage,
        int $originalWidth,
        int $originalHeight,
        bool $hasWatermark,
        int $maxWidth,
        int $maxHeight
    ) {
        if ($originalWidth > $originalHeight) {
            $manipulatedImage->widen($maxWidth);
        } else {
            $manipulatedImage->heighten($maxHeight);
        }

        if ($hasWatermark) {
            $watermark = \Image::make(resource_path('assets/preview-watermark.png'));
            $watermark->opacity(12); //todo: change opacity on original image, not at manipulation

            $manipulatedImage->fill($watermark);
        }
    }
}
