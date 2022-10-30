<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Support\Facades\Request;
use MotionArray\Helpers\Imgix;
use MotionArray\Models\PreviewUpload;
use MotionArray\Models\Product;
use MotionArray\Repositories\PreviewUploadRepository;
use MotionArray\Repositories\ProjectRepository;
use Intervention\Image\Facades\Image;
use MotionArray\Models\PreviewFile;
use MotionArray\Models\Project;
use MotionArray\Services\Slim;
use Response;

class PreviewUploadsController extends BaseController
{
    protected $project;

    protected $previewUpload;

    public function __construct(
        ProjectRepository $project,
        PreviewUploadRepository $previewUpload
    )
    {
        $this->project = $project;

        $this->previewUpload = $previewUpload;
    }

    public function index($projectId)
    {
        $previewUploads = $this->previewUpload->findByProject($projectId);

        foreach ($previewUploads as &$previewUpload) {
            $previewUpload = $this->formatCustomRatio($previewUpload);
        }

        return $previewUploads;
    }

    public function show($projectId, $version)
    {
        $previewUpload = $this->previewUpload->findByVersion($projectId, $version);

        $previewUpload = $this->formatCustomRatio($previewUpload);

        return $previewUpload;
    }

    /**
     * Returns
     *
     * @param $projectId
     * @return mixed
     */
    public function active($projectId)
    {
        $project = $this->project->findById($projectId);

        if (!$project) {
            return Response::json([
                'success' => false,
                'error' => 'Project not found'
            ]);
        }

        $previewUpload = $this->previewUpload->findActiveByProject($project);

        $previewUpload = $this->formatCustomRatio($previewUpload);

        return Response::json([
            'success' => !!$previewUpload,
            'response' => $previewUpload
        ]);
    }

    protected function formatCustomRatio($previewUpload)
    {
        $customSizes = Request::input('custom-sizes');
        $customSizes = filter_var($customSizes, FILTER_VALIDATE_BOOLEAN);

        if ($previewUpload) {
            $previewUpload->placeholder_low = Imgix::getImgixUrl($previewUpload->getPlaceholder('low', $customSizes));
            $previewUpload->placeholder_high = Imgix::getImgixUrl($previewUpload->getPlaceholder('high', $customSizes), 'auto');

            $previewUpload->setHidden(['uploadable']);

            if ($customSizes) {
                $count = $previewUpload->videoFiles()->where(function ($q) {
                    $q->where('label', 'LIKE', '%_custom');
                })->count();

                $videoFilesQuery = $previewUpload->videoFiles();

                if ($count) {
                    $videoFilesQuery->where(function ($q) use ($count) {
                        $q->where('label', 'LIKE', '%_custom');
                        $q->orWhere('label', '=', 'ORIGINAL');
                    });
                }
                $videoFiles = $videoFilesQuery->get();
            } else {
                $videoFiles = $previewUpload->videoFiles()->where('label', 'NOT LIKE', '%_custom')->get();
            }

            $previewUpload = $previewUpload->toArray();
            $previewUpload['video_files'] = $videoFiles;
        }

        return $previewUpload;
    }

    public function deleteMultiple($projectId)
    {
        $ids = Request::get('ids');

        foreach ($ids as $id) {
            $version = PreviewUpload::find($id);

            $version->delete();
        }

        return Response::json(['success' => 'true']);
    }


    public function uploadCustomPlaceholder($upload_type, $uploadableId)
    {
        if ($upload_type == 'project') {
            $uploadable = Project::find($uploadableId);
        } elseif ($upload_type == 'product') {
            $uploadable = Product::find($uploadableId);
        }

        $customSizes = Request::input('custom-sizes');
        $customSizes = filter_var($customSizes, FILTER_VALIDATE_BOOLEAN);

        $previewUpload = $uploadable->activePreview;

        $slim_data = json_decode(Request::get('slim'));

        $width = $slim_data->output->width;
        $height = $slim_data->output->height;

        $resolutions = [
            'high' => [
                'width' => '1920',
                'height' => '1080'
            ],
            'low' => [
                'width' => '850',
                'height' => '480'
            ],
        ];

        if ($uploadable->isProduct()) {
            $resolutions = [
                'high' => [
                    'width' => '1280',
                    'height' => '720'
                ],
                'low' => [
                    'width' => '640',
                    'height' => '360'
                ],
            ];
        }

//        if ($customSizes) {
//            $resolutions['high_custom'] = [
//                'width' => '1920',
//                'height' => ceil((1920 * $height) / $width)
//            ];
//
//            $resolutions['low_custom'] = [
//                'width' => '850',
//                'height' => ceil((850 * $height) / $width)
//            ];
//        }

        $bucket = Project::previewsBucket();

        $img = Image::make($slim_data->output->image)->encode('jpg', 95);
        $img->backup();

        $firstPlaceholder = $previewUpload->thumbnails()->orderBy('id', 'ASC')->first();

        $pattern = '/preview-' . $uploadableId . '-?([a-zA-Z0-9]+)/';
        preg_match($pattern, basename($firstPlaceholder->url), $matches);

        $randomString = $matches[1];

        $uploadFileName = 'preview-' . $uploadableId . '-' . $randomString;
        $lastUploadWithNumber = $previewUpload
            ->thumbnails()
            ->where(function ($q) use ($uploadFileName, $uploadableId, $randomString) {
                $q->where('url', 'LIKE', '%' . $uploadFileName . '%')
                    ->orWhere('url', 'LIKE', '%' . 'preview-' . $uploadableId . $randomString . '%');
            })
            ->orderBy('url', 'DESC')->first();

        $lastUploadNumber = preg_replace('/([^0-9]+)/', '', preg_replace($pattern, '', basename($lastUploadWithNumber->url)));

        $lastUploadNumber = str_pad(++$lastUploadNumber, 4, "0", STR_PAD_LEFT);

        foreach ($resolutions as $key => $resolution) {
            $filename = $uploadFileName . '-' . $key . '_' . $lastUploadNumber . '.jpg';

            $img->reset();
            $slim_data->output->image = (string)$img->fit($resolution['width'], $resolution['height'])->encode('data-url');

            $url = Slim::uploadToAmazon($bucket, $filename, json_encode($slim_data), '', true);

            //Create a preview file
            $preview_file = new PreviewFile();
            $preview_file->preview_upload_id = $previewUpload->id;
            $preview_file->label = 'placeholder ' . $key;
            $preview_file->format = 'jpg';
            $preview_file->url = $url;
            $preview_file->file_size_bytes = (int)$img->filesize();
            $preview_file->width = $resolution['width'];
            $preview_file->height = $resolution['height'];

            $preview_file->save();

            if ($key == 'low') {
                $previewUpload->placeholder_id = $preview_file->id;
            }

            $previewUpload->save();
        }

        $img->reset();

        return $uploadable;
    }
}
