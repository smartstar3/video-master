<?php namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Models\Product;
use MotionArray\Models\ProductImpression;
use MotionArray\Models\User;

class ProductImpressionRepository
{
    /**
     * @param Product $product
     * @param User $user
     * @return bool
     */
    public function create(Product $product, User $user)
    {
        $impression = $product->impressions()->firstOrNew(['user_id' => $user->id]);

        $impression->last_visit = Carbon::now();

        return $impression->save();
    }

    public function getMostRecentImpressionProductIds(int $userId, int $limit = 100): array
    {
        $impressions = ProductImpression::query()
            ->select('product_id')
            ->where('user_id', $userId)
            ->orderBy('last_visit', 'desc')
            ->limit($limit)
            ->get();

        return $impressions->pluck('product_id')->toArray();
    }
}
