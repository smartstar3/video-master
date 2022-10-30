<?php namespace MotionArray\Services\MediaSender;

use Google_Service_YouTube;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;
use Guzzle\Http\EntityBody;
use GuzzleHttp\Client;
use MotionArray\Helpers\Helpers;
use MotionArray\Models\PreviewFile;
use MotionArray\Models\PreviewUpload;
use MotionArray\Models\Product;
use Vimeo\Vimeo;

class HttpMediaSender
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @var string
     */
    public $tmpFile;

    /**
     * @var Google_Service_YouTube
     */
    protected $youtube;

    /**
     * @var Vimeo
     */
    protected $vimeo;

    /**
     * Extra tags.
     *
     * @var array
     */
    public $defaultTags = [
        'after-effects-templates' => [
            'after',
            'effects',
            'free',
            'intro',
            'mograph',
            'motion design',
            'motion graphics',
            'royalty',
            'templates',
        ],
        'premiere-pro-templates' => [
            'premiere',
            'free',
            'intro',
            'royalty',
            'templates',
        ],
        'motion-graphics-templates' => [
            'titles',
            'free',
            'motion graphics',
            'transitions',
            'templates',
        ],
        'stock-motion-graphics' => [
            'animation',
            'backgrounds',
            'free',
            'lower thirds',
            'motion graphics',
            'royalty',
            'stock',
            'transitions',
        ],
        'stock-music' => [
            'audio',
            'background music',
            'background',
            'free',
            'music',
            'podcast',
            'radio',
            'royalty',
            'stock',
        ],
        'sound-effects' => [
            'audio',
            'free',
            'music',
            'sound',
            'effects',
            'royalty',
            'stock',
        ],
        'stock-video' => [
            'compositing',
            'editing',
            'footage',
            'free',
            'royalty',
            'stock',
            'video',
        ],
    ];

    /**
     * HttpMediaSender constructor.
     *
     * @param Vimeo $vimeo
     * @param Google_Service_YouTube $youtube
     */
    public function __construct(Vimeo $vimeo, Google_Service_YouTube $youtube)
    {
        $this->vimeo = $vimeo;
        $this->youtube = $youtube;
    }

    /**
     * @param Product $product
     *
     * @return Product
     */
    public function send(Product $product)
    {
        $this->product = $product;
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'ma-');

        $successfulUploadCount = 0;

        if (!config('services.send-to-youtube') && !config('services.send-to-vimeo')) {
            return $successfulUploadCount;
        }

        $this->downloadFile();

        if (config('services.send-to-youtube')) {
            $successfulUploadCount += $this->sendToYoutube($this->product, $this->youtube); // true cast to 1, false to 0
        }

        if (config('services.send-to-vimeo')) {
            $successfulUploadCount += $this->sendToVimeo($this->product, $this->vimeo);
        }

        $this->deleteTmpFile();

        return $successfulUploadCount;
    }

    /**
     * @param Product $product
     * @param Vimeo $vimeo
     */
    protected function sendToVimeo(Product $product, Vimeo $vimeo)
    {
        $preview = $product->activePreview;

        if ($preview->vimeo_id) {
            return false;
        }

        $params = [
            'description' => $this->getProductDescription(),
            'name' => $this->getProductName(),
        ];

        $uri = $vimeo->upload($this->tmpFile);
        $videoId = str_replace('/videos/', '', $uri);

        $vimeo->request($uri, $params, 'PATCH');
        $vimeo->request($uri . '/tags', $this->getProductTags()->take(20), 'PUT');

        $preview->update(['vimeo_id' => $videoId]);

        if ($product->isVideo()) {
            $this->sendToGroups($videoId);
        }
        return true;
    }

    public function deleteOnVimeo(Product $product)
    {
        $preview = $product->activePreview;
        if (!$preview->vimeo_id)
            return false;

        $uri = '/videos/' . $preview->vimeo_id;
        $this->vimeo->request($uri, [], 'DELETE');
        $preview->update(['vimeo_id' => null]);
        return true;
    }

    /**
     * @param int $videoId
     */
    protected function sendToGroups($videoId)
    {
        $groups = $this->vimeo->request('/me/groups', ['per_page' => 50]);

        collect(array_get($groups, 'body.data'))
            ->pluck('uri')
            ->each(function ($uri) use ($videoId) {
                $this->vimeo->request("{$uri}/videos/{$videoId}", [], 'PUT');
            });
    }

    /**
     * @param Product $product
     * @param Google_Service_YouTube $youtube
     */
    protected function sendToYoutube(Product $product, Google_Service_YouTube $youtube)
    {
        $preview = $product->activePreview;

        if ($preview->youtube_id) {
            return false;
        }

        $snippet = new Google_Service_YouTube_VideoSnippet([
            'description' => $this->getProductDescription(),
            'tags' => $this->getProductTags(),
            'title' => $this->getProductName(),
        ]);

        $video = new Google_Service_YouTube_Video();

        $video->setSnippet($snippet);

        $response = $youtube->videos->insert('snippet', $video, [
            'data' => file_get_contents($this->tmpFile),
            'mimeType' => 'video/*',
            'uploadType' => 'media',
        ]);

        $preview->update(['youtube_id' => $response->getId()]);
        return true;
    }

    /**
     * Delete a product preview on Youtube
     * @FIXME(abiusx): does not work immediately after creating, so automated tests fail with it.
     * @param Product $product
     * @return void
     */
    public function deleteOnYoutube(Product $product)
    {
        $preview = $product->activePreview;
        if (!$preview->youtube_id)
            return false;

        $response = $this->youtube->videos->delete($preview->youtube_id);
        $preview->update(['youtube_id' => null]);
        return true;
    }
    /**
     * Get description for the current product.
     *
     * @return string
     */
    protected function getProductDescription()
    {
        $product = $this->product;

        $url = config('app.url') . '/' . $this->product->category->slug . '/' . $this->product->slug;

        return "Get this here: {$url}\n...included with our Unlimited memberships. Or download hundreds of other assets with a FREE account. https://motionarray.com/free\n\n{$product->description}";
    }

    /**
     * Get name for the current product.
     *
     * @return string
     */
    protected function getProductName()
    {
        return $this->product->name . ' ' . $this->product->category->name;
    }

    /**
     * Get tags for the current product.
     *
     * @return mixed
     */
    protected function getProductTags()
    {
        $product = $this->product;
        $tags = $product->tags->pluck('name');

        if (isset($this->defaultTags[$product->category->slug])) {
            $defaultTags = $this->defaultTags[$product->category->slug];

            if (!empty($defaultTags)) {
                return $tags->merge($defaultTags)->unique()->values();
            }
        }

        return $tags;
    }

    /**
     * Download preview from S3.
     */
    protected function downloadFile()
    {
        $previewFile = $this->product->activePreview->files->first(function ($preview, $key) {
            return in_array($preview->label, [PreviewFile::MP3_HIGH, PreviewFile::MP4_HIGH]);
        });

        if (PreviewFile::MP3_HIGH == $previewFile->label) {
            $this->createVideoFromMP3($previewFile);
        } else {
            $res = app('aws')->get('s3')->getObject([
                'Bucket' => config('aws.previews_bucket'),
                'Key' => str_replace(config('aws.previews_s3'), '', Helpers::convertToHttps($previewFile->url)),
            ]);
            file_put_contents($this->tmpFile, (string) $res['Body']);
        }
    }

    /**
     * @param PreviewUpload $preview
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function createVideoFromMP3(PreviewFile $previewFile)
    {
        $client = new Client([
            'base_uri' => 'http://encoder.motionarray.com/api/v1/',
        ]);

        $client->post('jobs', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer LSXDpVF6cGleWdbhqHT7Tzt5mSUrJbhw9V4mHGgt37PWXtxZC1qFEyls7oam',
            ],
            'json' => [
                'input' => $previewFile->url,
            ],
            'sink' => $this->tmpFile,
        ]);
    }

    /**
     * Remove tmp file.
     */
    protected function deleteTmpFile()
    {
        // We want this to throw an error on failure, because this is the end of the job
        // and we have a lot of dangling files on /tmp! So lets see why it is.
        unlink($this->tmpFile);
    }
}
