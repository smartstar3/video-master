<?php namespace MotionArray\Helpers\Portfolio;

use Illuminate\Support\Facades\URL;
use MotionArray\Helpers\Portfolio\PortfolioContent\PortfolioContentHelper;
use Request;

class PortfolioHelper
{
    const EDIT_MODE = 'edit';
    const THEME_PREVIEW_MODE = 'theme-preview';
    const PREVIEW_MODE = 'preview';
    const PUBLIC_MODE = 'public';

    /**
     * Returns Portfolio mode according Url
     * Preview | Edit | Public
     *
     * @param null $url
     * @return string
     */
    public static function getPortfolioMode($url = null)
    {
        if (!$url) {
            $url = Request::getPathInfo();
        }

        if (preg_match('#\/theme-preview(\/|$)#i', $url) || str_contains($url, 'portfolio/example')) {
            return self::THEME_PREVIEW_MODE;
        } elseif (preg_match('#\/preview(\/|$)#i', $url) || preg_match('#\/insider-preview(\/|$)#i', $url)) {
            return self::PREVIEW_MODE;
        } elseif (preg_match('#\/edit(\/|$)#i', $url)) {
            return self::EDIT_MODE;
        } else {
            return self::PUBLIC_MODE;
        }
    }

    /**
     * @param null $url
     * @return bool
     */
    public static function isEditMode($url = null)
    {
        return self::getPortfolioMode($url) == self::EDIT_MODE;
    }

    /**
     * @param null $url
     * @return bool
     */
    public static function isPublicMode($url = null)
    {
        return self::getPortfolioMode($url) == self::PUBLIC_MODE;
    }

    /**
     * @param null $url
     * @return bool
     */
    public static function isPreviewMode($url = null)
    {
        return self::getPortfolioMode($url) == self::PREVIEW_MODE;
    }

    /**
     * @param null $url
     * @return bool
     */
    public static function isThemePreviewMode($url = null)
    {
        return self::getPortfolioMode($url) == self::THEME_PREVIEW_MODE;
    }

    /**
     * Creates url for portfolio
     *
     * @param $path
     * @return string
     */
    public static function to($path)
    {
        $currentPath = Request::path();

        $basePath = preg_replace('#/?project/([a-z_\-0-9]+)#i', '', $currentPath);

        $path = $basePath . $path;

        $path = preg_replace('#(https?:\/\/)|(\/)+#', '$1$2', $path);

        return URL::to($path);
    }

    public static function getContentHelper($page, $portfolio, $theme, $useDefaultContent)
    {
        $portfolioContentRepository = \App::make('MotionArray\Repositories\PortfolioContentRepository');

        if (PortfolioHelper::isThemePreviewMode()) {
            $portfolioContent = $portfolioContentRepository->getThemePreviewContent($page, $portfolio, $theme, $useDefaultContent);
        } elseif (PortfolioHelper::isPreviewMode()) {
            $portfolioContent = $portfolioContentRepository->getPreviewContent($page, $portfolio, $theme, $useDefaultContent);
        } elseif (PortfolioHelper::isEditMode()) {
            $portfolioContent = $portfolioContentRepository->getEditingContent($page, $portfolio, $theme, $useDefaultContent);
        } else {
            $portfolioContent = $portfolioContentRepository->getPublicContent($page, $portfolio, $theme, $useDefaultContent);
        }

        return new PortfolioContentHelper($portfolioContent);
    }
}
