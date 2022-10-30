<?php

namespace MotionArray\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Google_Service_YouTube;
use MotionArray\Models\PreviewUpload;

class RefreshYouTubeData extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:refresh-youtube-data 
        {--older-than-days=20 : Only refresh products that have not been refreshed in the last number of days}
        {--limit=10000 : Limit the number of records to refresh}
        {--force : Skip the confirmation prompt}
        ';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Update YouTube related data that we store with the most recent data from YouTube';

    /**
     * @var Google_Service_YouTube
     */
    protected $youtubeService;

    /**
     * @param Google_Service_YouTube $youtubeService
     */
    public function handle(Google_Service_YouTube $youtubeService)
    {
        $this->youtubeService = $youtubeService;

        /** @var int $chunkSize */
        $chunkSize = 100;

        $olderThanDays = (int)$this->option('older-than-days');
        $limit = (int)$this->option('limit');
        $now = Carbon::now();
        $startDate = $now->subDays($olderThanDays);

        $previewUploads = PreviewUpload::query()
            ->withTrashed()
            ->whereNotNull('youtube_id')
            ->where(function ($q) use ($startDate) {
                $q->where('youtube_refresh_timestamp', '<=', $startDate)->orWhereNull('youtube_refresh_timestamp');
            })
            ->orderBy('youtube_refresh_timestamp', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->take($limit)
            ->get();

        $count = $previewUploads->count();
        $count = min($count, $limit);

        $force = (bool)$this->option('force');
        if (!$force) {
            $confirmed = $this->confirm("Run for $count records?");
            if (!$confirmed) {
                return;
            }
        }

        $bar = $this->output->createProgressBar($count);

        foreach ($previewUploads as $previewUpload) {

            // The only YouTube related info we're currently storing is the video ID which we store in
            // preview_uploads.youtube_id. So for each ID we have stored, check if it still exists on YouTube.
            // If it doesn't, remove the ID from our database. We need to do this at least once every 30 days.
            // This is to comply with YouTube's policies. See point 4.E.a-g here:
            // https://developers.google.com/youtube/terms/developer-policies#e.-handling-youtube-data-and-content
            $videosResponse = $this->youtubeService->videos->listVideos('id', ['id' => $previewUpload->youtube_id]);
            if (sizeof($videosResponse['modelData']['items']) < 1) {
                $previewUpload->youtube_id = null;
            }

            // Save the timestamp so we can later determine which records have not been updated in the required
            // timespan.
            $previewUpload->youtube_refresh_timestamp = Carbon::now();

            $previewUpload->save();
            $bar->advance();

        }

        $bar->finish();
        $this->output->writeln('');
    }
}
