<?php

namespace MotionArray\Repositories;

use MotionArray\Repositories\CraftArchiveRepository;
use Carbon\Carbon;

class TutorialRepository extends CraftArchiveRepository
{
    protected $section = 'tutorials';

    public function weeklyRecap($limit)
    {
        $criteriaParams = [];

        list($startDate, $endDate) = $this->weeklyRecapDateRange();

        $criteriaParams['postDate'] = 'and, >= ' . $startDate . ' , <' . $endDate;

        return $this->getEntries(1, $limit, 0, $criteriaParams);
    }
}
