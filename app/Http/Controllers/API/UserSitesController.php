<?php

namespace MotionArray\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use MotionArray\Http\Requests\UserSiteRequest;
use MotionArray\Jobs\UserSiteStatusCheck;
use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\ReviewRepository;
use MotionArray\Repositories\UserSiteRepository;

/**
 * Class UserSitesController
 *
 * @package MotionArray\Http\Controllers\API
 */
class UserSitesController extends BaseController
{
    protected $userSiteRepository;

    protected $portfolio;

    protected $review;

    /**
     * UserSitesController constructor.
     *
     * @param \MotionArray\Repositories\UserSiteRepository $userSiteRepository
     * @param PortfolioRepository $portfolio
     * @param \MotionArray\Repositories\ReviewRepository $review
     */
    public function __construct(
        UserSiteRepository $userSiteRepository,
        PortfolioRepository $portfolio,
        ReviewRepository $review
    )
    {
        $this->userSiteRepository = $userSiteRepository;
        $this->portfolio = $portfolio;
        $this->review = $review;
    }

    public function index()
    {
        return $this->userSiteRepository->getCustomDomainsList();
    }

    /**
     * Show User Site Data
     *
     * @return mixed
     */
    public function show()
    {
        if (!Auth::check()) {
            return Response::json(['success' => true, 'error' => 'No user logged']);
        }

        $site = $this->userSiteRepository->findByUser(Auth::user());

        if ($site) {
            $response = $site->toArray();

            $response['portfolio'] = $site->portfolio;

            $response['review'] = $site->review;

            if ($response['portfolio']) {
                $response['portfolio']->setHidden(['owner', 'site']);
            }

            if ($response['review']) {
                $response['review']->setHidden(['owner', 'site']);
            }
        }

        return Response::json(['success' => !!$site, 'response' => $site]);
    }

    /**
     * Update User Site Data
     *
     * @param UserSiteRequest $request
     * @return mixed
     */
    public function update(UserSiteRequest $request)
    {
        $user = auth()->user();

        $input = array_except($request->all(), ['_token', '_method']);

        $siteData = array_except($input, ['portfolio', 'review', 'success', 'response']);

        $userSite = $this->userSiteRepository->findOrCreateByUser($user);
        $this->userSiteRepository->update($userSite, $siteData);
        dispatch(new UserSiteStatusCheck($userSite));

        if (isset($input['portfolio'])) {
            $portfolio = $this->portfolio->findOrCreateBySite($userSite);
            $portfolio = $this->portfolio->update($portfolio, array_except($input['portfolio'], ['url']));
        }

        if (isset($input['review']) && $input['review']) {
            $review = $this->review->findOrCreateBySite($userSite);
            $review = $this->review->update($review, array_except($input['review'], ['url']));

            $this->review->updateProjectsNotification($review, $input['review']['settings']['email_notification']);
        }

        return Response::json(['success' => true, 'response' => $userSite]);
    }
}
