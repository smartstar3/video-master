<?php

namespace MotionArray\Repositories;

use MotionArray\Helpers\Imgix;
use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioUpload;

/**
 * Class PortfolioUploadRepository
 *
 * @package MotionArray\Repositories\PortfolioUpload
 */
class PortfolioUploadRepository
{
    /**
     * @var PortfolioUpload
     */
    private $portfolioUpload;

    /**
     * PortfolioUploadRepository constructor.
     *
     * @param PortfolioUpload $portfolioUpload
     */
    public function __construct(PortfolioUpload $portfolioUpload)
    {
        $this->portfolioUpload = $portfolioUpload;
    }

    /**
     * Find Portfolio Upload By ID
     *
     * @param $portfolioUploadId
     * @return mixed
     */
    public function findById($portfolioUploadId)
    {
        return $this->portfolioUpload->where('id', $portfolioUploadId)->first();
    }

    /**
     * Find Portfolio Uploads for Portfolio
     *
     * @param Portfolio $portfolio
     * @return mixed
     */
    public function findByPortfolio(Portfolio $portfolio)
    {
        $portfolio_uploads = $portfolio->uploads()->get();

        foreach ($portfolio_uploads as $portfolio_upload) {
            $portfolio_upload->thumb_url = Imgix::getImgixUrl($portfolio_upload->url, 300);
        }

        return $portfolio_uploads;
    }
}
