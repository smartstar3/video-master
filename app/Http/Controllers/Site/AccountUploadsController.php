<?php namespace MotionArray\Http\Controllers\Site;

use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\ProjectRepository;
use MotionArray\Repositories\UserSiteRepository;
use View;
use Redirect;
use Auth;

class AccountUploadsController extends BaseController
{
    protected $portfolio;

    protected $project;

    protected $userMailer;

    public function __construct(
        PortfolioRepository $portfolio,
        UserSiteRepository $userSite,
        ProjectRepository $project
    )
    {
        $this->userSite = $userSite;

        $this->portfolio = $portfolio;

        $this->project = $project;
    }

    public function uploads()
    {
        $title = 'My Uploads';

        $tabs = [
            'uploads' => 'All uploads',
            'reviews' => 'My Review Videos',
            'portfolio' => 'My Gallery Videos',
            'images' => 'My Gallery Images'
        ];

        $user = Auth::user();

        $portfolio = $this->portfolio->findByUser($user);

        $uploadType = '';

        return view('site.account.portfolio.uploads', compact('title', 'tabs', 'portfolio', 'uploadType'));
    }

    public function portfolio()
    {
        $title = 'Portfolio';

        $tabs = [
            'portfolio-theme' => 'My Portfolio',
            'portfolio' => 'My Portfolio Gallery',
            'portfolio-settings' => 'My Portfolio settings'
        ];

        $user = Auth::user();

        $portfolio = $this->portfolio->findByUser($user);

        $uploadType = 'public';

        return view('site.account.portfolio.portfolio', compact('title', 'tabs', 'portfolio', 'uploadType'));
    }

    public function review()
    {
        $title = 'Review';

        $tabs = [
            'reviews' => 'My Review Projects',
            'review-settings' => 'My Review settings'
        ];

        $user = Auth::user();

        $portfolio = $this->portfolio->findByUser($user);

        $uploadType = 'review';

        return view('site.account.portfolio.reviews', compact('title', 'tabs', 'portfolio', 'uploadType'));
    }

    public function portfolioRedirect()
    {
        $path = request()->get('path');

        $user = auth()->user();

        if (!$user) {
            return redirect()->back();
        }

        $userSite = $this->userSite->findOrCreateByUser($user);

        $url = $userSite->getPortfolioUrl($path);

        return Redirect::to($url);
    }

    public function reviewRedirect()
    {
        $path = request()->get('path');

        $user = auth()->user();

        if (!$user) {
            return redirect()->back();
        }

        $userSite = $this->userSite->findOrCreateByUser($user);

        $url = $userSite->getReviewUrl($path);

        return redirect()->to($url);
    }
}
