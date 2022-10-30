<?php

namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Repositories\EloquentBaseRepository;
use MotionArray\Models\UserSite;
use MotionArray\Models\User;

/**
 * Class UserSiteRepository
 *
 * @package MotionArray\Repositories\UserSite
 */
class UserSiteRepository extends EloquentBaseRepository
{
    public function __construct(UserSite $userSite)
    {
        $this->model = $userSite;
    }

    public function getCustomDomainsList()
    {
        return $this->model->whereNotNull('domain')->pluck('domain');
    }

    /**
     * @param $url
     * @return UserSite|null
     */
    public function findByUrl($url)
    {
        $urlParts = parse_url($url);

        $domain = $urlParts['host'];

        if (isset($urlParts['port']) && $urlParts['port']) {
            $domain .= ':' . $urlParts['port'];
        }

        return $this->findByDomain($domain);
    }

    /**
     * @param $domain
     * @return UserSite|null
     */
    public function findByDomain($domain)
    {
        $portfolioDomain = config('portfolio.domain');

        $slug = null;

        if (ends_with($domain, '.' . $portfolioDomain)) {
            $slug = str_replace('.' . $portfolioDomain, '', $domain);
        }

        if ($slug) {
            $site = $this->findBySlug($slug);
        } else {
            $domain = preg_replace('#https?\:\/\/#', '', $domain);

            $domain = preg_replace('#www\.#', '', $domain);

            $site = UserSite::where('domain', '=', $domain)
                ->where('use_domain', 1)
                ->first();
        }

        return $site;
    }

    /**
     * @param $slug
     *
     * @return UserSite|null
     */
    public function findBySlug($slug)
    {
        $slug = preg_replace('#-?develop-?#i', '', $slug);

        $isReviewPage = preg_match('#review#i', \request()->getPathInfo());

        return UserSite::where('slug', '=', $slug)
            ->where(function ($query) use ($isReviewPage) {
                $query->where('use_domain', 0);

                if ($isReviewPage) {
                    $query->orWhere('reviews_same_url', 0);
                }
            })
            ->first();
    }


    /**
     * @param User $user
     * @return UserSite
     */
    public function findOrCreateByUser(User $user)
    {
        $site = $this->findByUser($user);

        if (!$site) {
            $site = $this->createDefault($user);
        }

        return $site;
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function findByUser(User $user)
    {
        return UserSite::where(['user_id' => $user->id])->first();
    }

    /**
     * @param User|null $user
     * @return UserSite
     */
    public function createDefault(User $user)
    {
        $site = new UserSite;

        $site->user()->associate($user);

        $site->save();

        return $site;
    }

    /**
     * @param UserSite $site
     * @param $data
     * @return bool|UserSite
     */
    public function update(UserSite $site, $data)
    {
        if (
            isset($data['use_domain']) && $data['use_domain']
            && isset($data['domain']) && $data['domain']
            && (!isset($data['reviews_same_url']) || $data['reviews_same_url'] !== 0)
        ) {
            unset($data['slug']);

            $data['slug_expires_at'] = Carbon::now()->addDays(5);
        } else {
            unset($data['url']);
        }

        $site->update($data);

        return $site;
    }
}
