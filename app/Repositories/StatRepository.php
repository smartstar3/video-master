<?php namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Models\Portfolio;
use MotionArray\Models\Project;
use MotionArray\Models\User;

class StatRepository
{
    /**
     * @param null $startDate
     * @param null $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveReviewsByDate($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subMonth(1);
        }

        if (!$endDate) {
            $endDate = Carbon::now();
        }

        $sql = 'SELECT projects.*, COUNT(*) AS comments, users.email AS owner, DATE(project_comments.created_at) AS comment_date 
            FROM project_comments 
            LEFT JOIN preview_uploads ON preview_uploads.id = project_comments.preview_upload_id
            LEFT JOIN projects ON projects.id = preview_uploads.uploadable_id
            LEFT JOIN users ON projects.user_id = users.id
            WHERE project_comments.deleted_at IS NULL
            AND project_comments.created_at > "' . $startDate . '"
            AND project_comments.created_at < "' . $endDate . '"
            GROUP BY DATE(project_comments.created_at), projects.id
            ORDER BY comment_date DESC';

        return Project::fromQuery($sql);
    }

    public function getPortfoliosCreatedByDate($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subMonth(3);
        }

        if (!$endDate) {
            $endDate = Carbon::now();
        }

        $createdPortfoliosByDate = Portfolio::published()->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('count(*) AS portfolios_count, DATE(created_at) AS created_date')
            ->groupBy('created_date')
            ->orderBy('created_date', 'ASC')
            ->get();

        return $createdPortfoliosByDate;
    }

    public function getPublishedPortfolios()
    {
        $portfolios = Portfolio::published()->orderBy('created_at', 'DESC')->get();

        return $portfolios;
    }

    public function getReviewsCountByUser()
    {
        $users = User::withCount('activeReviews')->with('activeReviews')->having('active_reviews_count', '>', 0)->get();

        return $users;
    }

    public function getReviewsCreatedByDate($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subMonth(3);
        }

        if (!$endDate) {
            $endDate = Carbon::now();
        }

        $reviewsCreatedByDate = Project::withReview()->active()->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('count(*) AS reviews_count, DATE(created_at) AS created_date')
            ->groupBy('created_date')
            ->orderBy('created_date', 'ASC')
            ->get();

        return $reviewsCreatedByDate;
    }
}
