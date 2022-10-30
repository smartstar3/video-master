<?php namespace MotionArray\Presenters;

use League\CommonMark\CommonMarkConverter;
use MotionArray\Helpers\Helpers;
use MotionArray\Models\Product;
use MotionArray\Helpers\Imgix;
use Auth;
use MotionArray\Models\User;
use MotionArray\Repositories\PreviewFileRepository;
use MotionArray\Services\Cdn\PreviewCdnChecker;
use View;
use Config;

class ProductPresenter extends Presenter
{
    protected $newProductTimeframe = 2592000; // 30 Days

    /**
     * @return string
     */
    public function description()
    {
        $converter = new CommonMarkConverter();

        return $converter->convertToHtml($this->entity->description);
    }

    /**
     * @return string
     */
    public function detailsBackground()
    {
        if ($this->entity->preview_type == "video") {
            $placeholder = $this->getPreview('placeholder', 'high');
        } else {
            $placeholder = $this->entity->audio_placeholder;
        }

        $split_url = parse_url($placeholder);

        //work around for default placeholder url
        if (!array_key_exists('host', $split_url) || !array_key_exists('query', $split_url)) {
            return "style=\"background-image: url({$placeholder})\"";
        }

        parse_str($split_url['query'], $query);

        $placeholder = $split_url['scheme'] . '://' . $split_url['host'] . $split_url['path'] . '?w=1920&h=1080' . '&fit=' . $query['fit'] . '&q=' . $query['q'] . '&blur=60';

        return "style=\"background-image: url({$placeholder})\"";
    }

    /**
     * Link on products Listing
     *
     * @return string
     */
    public function name()
    {
        return '<a href="' . $this->entity->previewUrl . '">' . $this->entity->name . '</a>';
    }

    /**
     * @return string
     */
    public function badge()
    {
        if (Auth::check()) {
            if (Auth::user()->hasDownloadedProductBefore($this->entity->id)) {
                return "<div class=\"product__badge  product__badge--downloaded\"><span class=\"icon  icon--check\"></span></div>";
            }
        }

        if ($this->entity->free) {
            return "<div class=\"product__badge product__badge__free\">Free</div>";
        }

        if ($this->entity->published_at > date('Y-m-d H:i:s', time() - $this->newProductTimeframe)) {
            return "<div class=\"product__badge\">New</div>";
        }
    }

    /**
     * @return string
     */
    public function className()
    {
        $className = '';

        // Check to see if this product should have a new badge.
        if ($this->entity->published_at > date('Y-m-d H:i:s', time() - $this->newProductTimeframe)) {
            $className .= ' product--new';
        }

        if ($this->entity->free) {
            $className .= ' product--free';
        }

        return $className;
    }

    /**
     * @return string
     */
    public function credited()
    {
        $seller = $this->entity->seller()->withTrashed()->first();

        if ($this->entity->credit_seller) {
            $credit = '<span class="product-details__seller">by&nbsp; ';

            $text = $seller->company_name ? $seller->company_name : ($seller->firstname . '&nbsp;' . $seller->lastname);

            if ($seller->slug) {
                $credit .= '<a href="/browse/producer/' . $seller->slug . '">' . $text . '</a>';
            } else {
                $credit .= $text;
            }

            $credit .= "</span>";

            return $credit;
        }
    }


    private function downloadAction(bool $isProductPageButton = false)
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Product $product */
        $product = $this->entity;
        $action = 'upgrade';

        if (!$user) {
            $action = 'pricing';
        } else {
            // Logged Users

            if ($user->isAdmin()) {
                // Redirect user to /mabackend/submissions?q={id}
                $action = 'mabackend';
            } else {

                if ($user->isPayingMember()) {
                    if ($user->hasDownloadedProductBefore($product->id)) {
                        $action = 'download';
                    } else {
                        $action = 'product-page';
                    }
                } else {
                    if ($product->free) {
                        if ($user->isConfirmed()) {
                            $action = 'product-page';
                        } else {
                            $action = 'confirm-email';
                        }
                    } else {
                        $action = 'upgrade';
                    }
                }
            }
        }

        // If the product is on the products page,
        // Then start download
        if ($isProductPageButton && $action === 'product-page') {
            $action = 'confirm-download';
        }

        $downloadUrl = '/account/download/' . $product->id;

        switch ($action) {
            case 'confirm-email':
                $url = $downloadUrl;
                $classes = 'js-confirm-email';
                break;
            case 'confirm-download':
                $url = $downloadUrl;
                $classes = '';
                break;
            case 'download':
                $url = $downloadUrl;
                $classes = 'js-unrestricted-download';
                break;
            case 'product-page':
                $url = '/' . $product->category->slug . '/' . $product->slug;
                $classes = '';
                break;
            case 'upgrade':
                $url = '/account/upgrade';
                $classes = 'js-upgrade';
                break;
            case 'pricing':
                $url = '/pricing';
                $classes = 'js-required-auth';
                break;
            case 'confirm-tos':
                $url = $downloadUrl;
                $classes = 'js-confirm-tos';
                break;
            case 'mabackend':
                $url = '/mabackend/products/'.$product->id.'/download';
                $classes = '';
                break;
        }

        return [
            'classes' => $classes,
            'url' => $url
        ];
    }

    public function downloadLinkButton()
    {
        $button = $this->downloadAction(true);

        $package_size = Helpers::bytesToSize($this->entity->size);

        return '<a href="' . $button['url'] . '" class="download-btn btn btn--white btn--icon ' . $button['classes'] . '">' .
            '<span class="icon  icon--download"></span>' .
            '<span class="btn__text">Download<small>(' . $package_size . ')</small></span></a>';
    }

    /**
     * @return string
     */
    public function lastDownloadedAt()
    {
        if (Auth::check()) {
            $record = Auth::user()->downloads()->where("product_id", "=", $this->entity->id)->where('active', '=', 1)->first();

            if ($record) {
                return "Downloaded on <strong>" . $record->updated_at->format('F d, Y') . "</strong>";
            }
        }
    }

    /**
     * @param string $theme
     * @param string $quality
     * @param null $url
     * @param null $force_width
     * @param bool $auto
     * @param bool $useVideoJS
     *
     * @return string
     */
    public function preview($theme = "standard", $quality = "high", $url = null, $force_width = null, $auto = true, $useVideoJS = false)
    {
        $parent = $this->entity->parent;

        if ($theme == "placeholder-only" && isset($url)) {
            $placeholder = $this->getPreview('placeholder', $quality);

            $alt = $this->entity->name;
            if ($parent) {
                $alt .= ": " . $this->entity->parent->name;
            }

            $player = "<a href=\"{$url}\"><img class=\"product__placeholder\" src=\"{$placeholder}\" alt=\"{$alt}\" /></a>";
        } elseif ($this->entity->preview_type == "video") {
            $placeholder = $this->getPreview('placeholder', $quality, $force_width);

            // Get high resolution placeholder
            $highResPlaceholder = '';
            if ($quality == 'low') {
                $highResPlaceholder = $this->getPreview('placeholder', 'high', ($force_width * 2));

                $highResPlaceholder = ' data-high-res="' . $highResPlaceholder . '"';
            }

            $mp4 = $this->getPreview('mp4', $quality);
            $webm = $this->getPreview('webm', $quality);
            $ogg = $this->getPreview('ogg', $quality);

            $alt = '';
            if ($this->entity->isProject()) {
                $alt = $this->entity->plain_name;
            } else {
                $alt = $this->entity->name;
            }

            if ($parent) {
                $alt .= ": " . $this->entity->parent->name;
            }

            $preload = "none";
            $autoplay = null;

            if ($theme == "standard") {
                $preload = "metadata";

                if ($auto) {
                    $autoplay = "js-autoplay";
                }
            }

            $playerClass = '';
            if ($useVideoJS) {
                $playerClass = 'videojs-player video-js videojs-custom-skin';
            }

            $player = "<div class=\"product__video player video-player js-ready  {$autoplay}\" data-theme=\"{$theme}\">
				<video  
				class=\"video-player__media  player__media {$playerClass}\" 
				data-autoplay='{$auto}'
				width=\"100%\" height=\"auto\" preload=\"{$preload}\" autobuffer=\"{$preload}\">
			    	<source src=\"{$mp4}\" type=\"video/mp4\">
			   		<source src=\"{$webm}\" type=\"video/webm\">
			    	<source src=\"{$ogg}\" type=\"video/ogg\">
				</video>
				<img class=\"video-player__poster  player__poster\" src=\"{$placeholder}\" {$highResPlaceholder} alt=\"{$alt}\"/>
			</div>";
        } else {
            if ($this->entity->placeholder_id != 0) {
                $placeholder = $this->getPreview('placeholder', $quality, null, $quality == 'high');
            } else {
                $placeholder = str_replace($this->entity->previewsBucketUrl(), $this->entity->imgixUrl(), $this->entity->audio_placeholder);
            }

            $mp3 = $this->getPreview('mp3');
            $ogg = $this->getPreview('ogg');

            $alt = $this->entity->name . ": " . $this->entity->parent->name;

            $preload = "none";
            $autoplay = null;
            if ($theme == "standard") {
                $preload = "metadata";
                if ($auto) {
                    $autoplay = "js-autoplay";
                }
            }

            $playerClass = '';
            if ($useVideoJS) {
                $playerClass = 'videojs-player video-js videojs-custom-skin';
            }
            $player = "
			<div class=\"product__audio  player  audio-player  js-ready  {$autoplay}\" data-theme=\"{$theme}\" style=\"background:#8cdbac\">
                <audio class=\"audio-player__media  player__media  js-hidden {$playerClass}\" 
                data-autoplay='{$auto}'
                preload=\"{$preload}\" autobuffer=\"{$preload}\">
                    <source src=\"{$mp3}\" type=\"audio/mpeg\" />
                    <source src=\"{$ogg}\" type=\"audio/ogg\" />
                </audio>
                <img class=\"audio-player__placeholder\" src=\"{$placeholder}\" alt=\"{$alt}\" />
			</div>
			";
        }

        return $player;
    }

    /**
     * Preview Download link for audio files
     *
     * @return string
     */
    public function previewDownload()
    {
        $count = 0;

        $preview = $this->entity->activePreview;

        if ($preview) {
            $count = $preview->files()
                ->where(function ($query) {
                    $query->where('format', '=', 'mp3');
                    $query->orWhere('format', '=', 'mpeg audio');
                })
                ->count();
        }

        if ($this->entity->preview_type == "audio" && ($count || $this->entity->preview_filename)) {
            $file = $this->id;

            return "<p>Need to test this track with your project? <a class=\"product__preview-download\" href=\"/browse/download/preview/{$file}\" download rel=\"nofollow\">Download the preview</a></p>";
        }
    }

    public function previewDownload_2()
    {
        $count = 0;

        $preview = $this->entity->activePreview;

        if ($preview) {
            $count = $preview->files()
                ->where(function ($query) {
                    $query->where('format', '=', 'mp3');
                    $query->orWhere('format', '=', 'mpeg audio');
                })
                ->count();
        }

        if ($this->entity->preview_type == "audio" && ($count || $this->entity->preview_filename)) {
            $file = $this->id;

            return "<a class=\"product__preview-download\" href=\"/browse/download/preview/{$file}\" download rel=\"nofollow\"><span><span class=\"icon icon--download\" title=\"Download\" data-toggle=\"tooltip\"></span>&nbsp;&nbsp;&nbsp;Download preview</a>";
        }
    }

    /**
     * @param string $format
     * @param string $quality
     * @param null $forceWidth
     * @param bool $fallback
     * @param string $default
     * @return mixed|null|string|string[]
     */
    public function getPreview($format = 'mp4', $quality = 'high', $forceWidth = null, $fallback = false, $default = '/assets/images/site/thumb_placeholder.png')
    {
        /** @var PreviewFileRepository $previewFileRepository */
        $previewFileRepository = app()->make('MotionArray\Repositories\PreviewFileRepository');

        $previewUpload = $this->entity->activePreview;

        $previewFile = null;
        $url = null;

        if ($format == 'placeholder') {
            if ($this->entity->isAudio()) {
                $url = $this->entity->audio_placeholder;
            } elseif ($previewUpload) {
                $previewFile = $previewFileRepository->findPlaceholder($previewUpload, $quality);
            }

        } elseif ($previewUpload) {
            $previewFiles = $previewFileRepository->getPreviewFiles($previewUpload, $format, $quality);

            $previewFile = $previewFiles->first();
        }

        if ($previewFile) {
            $url = $previewFile->url;
        }

        if ($url) {

            if ($fallback) {
                return $url;
            }

            // If its an image use Imgix
            if (preg_match('/jpg/', $url) || preg_match('/png/', $url) || preg_match('/jpeg/', $url)) {
                $width = $forceWidth ? $forceWidth : 660;

                $url = Imgix::getImgixUrl($url, $width);

            } elseif ($this->shouldUseCdn()) {
                $cdnUrl = Config::get("aws.previews_cdn");

                $replace_re = '#https?:\/\/ma-previews(.*)\.s3\.amazonaws.com\/#i';

                $url = preg_replace($replace_re, $cdnUrl, $url);
            }

            $url = str_replace('http://', 'https://', $url);

            return $url;
        }

        // If couldnt find high, try low
        if ($quality == 'high') {
            return $this->getPreview($format, 'low', $forceWidth, $fallback);
        }

        return $default;
    }

    protected function shouldUseCdn()
    {
        return app(PreviewCdnChecker::class)->shouldUseCDN();
    }

    /**
     * @param string $type
     * @param null $slug
     *
     * @return bool|\Illuminate\Contracts\View\View
     */
    public function collectionWidget($type = "icon", $slug = null)
    {
        /**
         * Restriction checks
         */
        if (!Auth::check() || Auth::user()->disabled) {
            return false;
        }

        if ($type == "icon-remove" && $slug != null) {
            return View::make('site._partials.collection-remove', ['product_id' => $this->id, 'slug' => $slug]);
        }

        $product = Product::withTrashed()->where('id', '=', $this->id)->firstOrFail();

        $currentCollections = $product->collections()->where("user_id", "=", Auth::user()->id)->get();

        return View::make('site._partials.collection-widget', ['product_id' => $this->id, 'type' => $type, 'collections' => Auth::user()->collections->unique(), 'currentCollections' => $currentCollections]);
    }

    /**
     * @return string
     */
    function track_durations()
    {
        $durations = array_filter(explode(',', $this->entity->track_durations));
        $durations = implode(' / ', $durations);

        return "Versions included: {$durations}";
    }
}
