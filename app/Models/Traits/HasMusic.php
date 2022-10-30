<?php namespace MotionArray\Models\Traits;

use MotionArray\Models\Product;

Trait HasMusic
{
    public function getMusicUrlAttribute()
    {
        if ($music = $this->music) {
            return $this->music->previewUrl;
        }
    }

    public function setMusic($url)
    {
        $result = false;

        $url = trim($url);

        if ($url) {
            $urlParts = parse_url($url);

            $pathParts = array_filter(explode('/', $urlParts['path']));

            $slug = array_pop($pathParts);

            if ($slug) {
                $music = Product::where('slug', $slug)->first();

                if ($music) {
                    if($this->music_id != $music->id) {
                        $result = true;
                    }

                    $this->music_id = $music->id;
                }
            }
        } else {
            if($this->music_id != null) {
                $result = true;
            }

            $this->music_id = null;
        }

        return $result;
    }
}