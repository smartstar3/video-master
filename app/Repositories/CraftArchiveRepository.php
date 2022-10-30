<?php

namespace MotionArray\Repositories;

use Carbon\Carbon;
use JesusRugama\Craft\CraftHelper;

class CraftArchiveRepository
{
    protected $craftHelper;
    protected $section = 'blog';

    public function __construct(CraftHelper $craftHelper)
    {
        $this->craftHelper = $craftHelper;
    }

    public function getPageBySlug($slug)
    {
        return $this->craftHelper->getPageBySlug($slug, $this->section);
    }

    public function getEntries($page, $limit, $offset, Array $criteriaParams = [])
    {
        return $this->craftHelper
            ->setPagination($page, $limit, $offset)
            ->getEntriesBySection($this->section, $criteriaParams);
    }

    public function weeklyRecapDateRange()
    {
        Carbon::setWeekEndsAt(Carbon::THURSDAY);
        Carbon::setWeekStartsAt(Carbon::FRIDAY);

        $now = Carbon::now();
        $startDate = $now->startOfWeek();
        $endDate = $now->copy()->endOfWeek();

        if ($now < $endDate) {
            $startDate->subWeek();
            $endDate->subWeek();
        }

        return [$startDate, $endDate];
    }

    public function countEntries($search = null, Array $criteriaParams = [])
    {
        return $this->craftHelper->countInSearch($search, $this->section, $criteriaParams);
    }

    public function getLastEntry(Array $criteriaParams = [])
    {
        $entry = $this->craftHelper
            ->setPagination(1, 1, 0)
            ->getEntriesBySection($this->section, $criteriaParams);

        return array_pop($entry);
    }

    public function search($query, $page, $limit, Array $criteriaParams = [])
    {
        return $this->craftHelper
            ->setPagination($page, $limit, 0)
            ->search($query, $this->section, $criteriaParams);
    }
}