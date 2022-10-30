<?php

namespace MotionArray\Listeners;

use Carbon\Carbon;
use MotionArray\Events\PortfolioSaved;
use MotionArray\Models\Project;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Manipulations;

class UploadScreenshotToS3 implements ShouldQueue
{

    protected $screenshotDirectory = '';
    protected $fileFormat = 'png';

    public function __construct()
    {
        $this->screenshotDirectory = storage_path('app/screenshots');

    }

    /**
     * Handle the event.
     *
     * @param PortfolioSaved $event
     *
     * @return void
     */
    public function handle(PortfolioSaved $event)
    {
        $portfolio = $event->portfolio;

        $theme = $portfolio->portfolioTheme()->first();

        if (!$theme) {
            return;
        }

        $uri = '/portfolio/insider-preview?site-id=' . $portfolio->user_site_id;

        $url = URL::to($uri);

        $filename = $this->getScreenshotFilename($url);

        Browsershot::url($url)
            ->windowSize(1400, 1400)
            ->fit(Manipulations::FIT_CONTAIN, 800, 800)
            ->setDelay(2000)
            ->setChromePath(config('services.chrome.path'))
            ->setNodeBinary(config('node.node_binary'))
            ->setNpmBinary(config('node.npm_binary'))
            ->save($this->screenshotDirectory . '/' . $filename);

        $destination = 'portfolio-' . $portfolio->id . '/theme-' . $portfolio->portfolioTheme->id . '-screenshot.' . $this->fileFormat;

        $s3Url = $this->uploadToS3($filename, $destination);

        if (!$s3Url) {
            return;
        }

        $theme->thumbnail_url = $s3Url;

        $theme->save();
    }

    public function getScreenshotFilename($url)
    {
        $now = Carbon::now()->toDateString();

        return md5($url) . '-' . $now . '.' . $this->fileFormat;

    }

    public function uploadToS3($filename, $destination)
    {
        $bucket = Project::previewsBucket();
        $path = $this->screenshotDirectory . '/' . $filename;
        $image = file_get_contents($path);
        $s3 = \App::make('aws')->get('s3');

        $response = $s3->putObject([
            'Bucket' => $bucket,
            'Body' => $image,
            'Key' => $destination,
            'ACL' => 'public-read',
            'ContentEncoding' => 'base64',
            'ContentType' => 'image/' . pathinfo($path, PATHINFO_EXTENSION),
            'CacheControl' => 'public, max-age=31104000',
            'Expires' => date(DATE_RFC2822, strtotime("+360 days"))
        ]);

        return isset($response['ObjectURL']) ? $response['ObjectURL'] : null;
    }
}
