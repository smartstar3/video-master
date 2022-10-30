<?php namespace MotionArray\Presenters;

class ProjectPresenter extends ProductPresenter
{
    public function url()
    {
        $portfolio = $this->entity->getPortfolio();

        $url = $portfolio->getUrlTo('/project/' . $this->entity->slug);

        return "<a href=\"{$url}\"></a>";
    }

}
