<?php namespace MotionArray\Presenters;

use Carbon\Carbon;

class DownloadPresenter extends Presenter
{
    public function first_downloaded_at()
    {
        return $this->entity->first_downloaded_at->format($this->defaultDateFormat);
    }

    public function placeholder()
    {
        $product = $this->entity->product()->withTrashed()->first();

        if ($product->placeholder_id) {
            if ($preview = $product->activePreview) {
                $placeholder = $preview->files()->where("id", "=", $product->placeholder_id)->first();

                return $placeholder ? $placeholder->url : null;
            }
        }

        return $product->audio_placeholder;
    }
}