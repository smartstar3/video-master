<?php namespace MotionArray\Console\Commands\OneTime;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MotionArray\Models\PreviewFile;
use MotionArray\Models\Project;
use MotionArray\Repositories\PreviewFileRepository;
use MotionArray\Services\Encoding\EncodingInterface;

class FixProjectsWithMissingFiles extends Command
{
    protected $signature = 'motionarray-onetime:fix-projects-with-missing-files';

    protected $previewFile;

    public function __construct(EncodingInterface $encoder, PreviewFileRepository $previewFileRepository)
    {
        $this->encoder = $encoder;

        $this->previewFile = $previewFileRepository;

        parent::__construct();
    }

    public function handle()
    {
        $addWatermark = false;

        $resolutions = [
            'low' => '852x480',
            'high' => '1920x1080',
        ];

        $hourAgo = Carbon::now()->subHour();

        $projects = Project::where('created_at', '<', $hourAgo)->orderBy('created_at', 'DESC')->get();

        foreach ($projects as $project) {
            foreach ($project->previewUploads as $previewUpload) {

                $original = $previewUpload->files()->where('label', '=', PreviewFile::ORIGINAL)->first();

                $missingFiles = $this->previewFile->findMissingFiles($previewUpload);

                if ($missingFiles->count()) {

                    $missingFilesLabels = $missingFiles->pluck('label')->all();

                    $missingFormats = implode(' ', $missingFilesLabels);

                    $originalMissing = collect($missingFilesLabels)->contains(PreviewFile::ORIGINAL);

                    if ($originalMissing) {
                        $this->info('Original is missing for project ' . $project->id);
                    } else {
                        $this->info('Files missing, original size is ' . $original->size . ' project: ' . $project->id);

                        $outputSettings = [];

                        $uploadable = $project;

                        $pathParts = pathinfo($original->url);

                        $filename = str_replace('original', 'preview', basename($pathParts['filename']));

                        if (str_contains($missingFormats, 'mp4')) {
                            $hls = str_contains($missingFormats, 'hls');

                            $mp4Encoding = $this->encoder->encodeVideoPreviews($uploadable, $resolutions, $filename, ['mp4'], $addWatermark, $hls);
                            $outputSettings = array_merge($outputSettings, $mp4Encoding);
                        }

                        if (str_contains($missingFormats, 'webm')) {
                            $webmEncoding = $this->encoder->encodeVideoPreviews($uploadable, $resolutions, $filename, ['webm'], $addWatermark);
                            $outputSettings = array_merge($outputSettings, $webmEncoding);
                        }

                        $encodingSettings = [
                            'input' => $original->url,
                            'outputs' => $outputSettings
                        ];

                        $this->encoder->createEncodeJobFromSettings($uploadable, $encodingSettings);

                        $this->info('Encoding task created for project ' . $project->id);
                    }
                } else {
                    $this->info('No missing file for project ' . $project->id);
                }
            }
        }
    }
}
