<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use MotionArray\Models\Collection;
use MotionArray\Models\CollectionProduct;
use MotionArray\Models\Product;
use MotionArray\Policies\CollectionPolicy;
use MotionArray\Repositories\CollectionRepository;
use Auth;
use Illuminate\Http\Request;
use Response;


class CollectionsController extends BaseController
{
    use ValidatesRequests;
    use AuthorizesRequests;

    /** @var CollectionRepository */
    protected $collection;

    public function __construct(CollectionRepository $collection)
    {
        $this->collection = $collection;
    }

    public function index()
    {
        if (!Auth::check()) {
            return Response::json(['success' => false, 'error' => 'No user logged']);
        }

        $collections = $this->collection->getCollectionsByUser(Auth::user(), 1, 100);

        return Response::json($collections);
    }


    public function indexWithProductIds()
    {
        if (!Auth::check()) {
            return Response::json(['success' => false, 'error' => 'No user logged']);
        }

        $collections = $this->collection->getCollectionsWithProductIdsByUser(Auth::user());

        return Response::json($collections);
    }

    public function products()
    {
        if (!Auth::check()) {
            return Response::json(['success' => false, 'error' => 'No user logged']);
        }

        $user = Auth::user();

        $query = 'SELECT products.id, products.name, products.slug, GROUP_CONCAT(DISTINCT collection_product.collection_id SEPARATOR ",") AS collection_ids FROM collection_product
            LEFT JOIN collections ON collection_product.collection_id = collections.id 
            LEFT JOIN products ON collection_product.product_id = products.id
            WHERE collections.user_id = ' . Auth::id() . '
            AND products.deleted_at IS NULL
            GROUP BY products.id';

        $products = \DB::select($query);

        $collections = $this->collection->getCollectionsByUser($user);

        $collections->each(function ($collection) {
            $collection->setHidden(['created_at', 'updated_at', 'deleted_at']);
        });

        foreach ($products as &$product) {
            $collectionIds = explode(',', $product->collection_ids);

            $collectionIds = array_map(function ($id) {
                return intval($id);
            }, $collectionIds);

            $filtered = $collections->whereIn('id', $collectionIds)->all();

            if (count($filtered)) {
                $product->collections = $filtered;
            } else {
                $product = null;
            }
        }

        /*
		$products = Product::with(['collections'])->whereHas('collections', function ($query)
		{
			$query->where('user_id', '=', Auth::id());
		})->select(['products.id', 'products.name', 'products.slug'])->get();


        $products->each(function ($product)
        {
            $product->setAppends([]);

            $product->collections->each(function ($collection)
            {
                $collection->setHidden(['pivot', 'created_at', 'updated_at', 'deleted_at']);
            });
        });*/

        return Response::json(array_values(array_filter($products)));
    }

    public function create(Request $request)
    {
        $this->authorize(CollectionPolicy::create, Collection::class);

        $this->validate($request, [
            'product_id' => [
                'nullable',
                'exists:products,id'
            ],
            'title' => 'required|max:70',
        ]);

        $collection = new Collection;
        $collection->title = $request->input('title');
        $collection->user_id = Auth::user()->id;
        $collection->slug = $collection->generateSlug();

        $productId = $request->input('product_id');
        if ($productId) {
            /** @var Product $product */
            $product = Product::find($productId);
            $product->collections()->save($collection);
        }

        return $collection;
    }

    public function update(Request $request, Collection $collection)
    {
        $this->authorize(CollectionPolicy::update, $collection);

        $this->validate($request, [
            'title' => 'required|max:200',
        ]);

        $collection->title = $request->input('title');
        $collection->save();

        return $collection;
    }

    public function delete(Request $request, Collection $collection)
    {
        $this->authorize(CollectionPolicy::delete, $collection);

        $collection->delete();
    }

    public function addProduct(Request $request, $collectionId)
    {
        $collection = Collection::findOrFail($collectionId);
        $this->authorize(CollectionPolicy::update, $collection);

        $this->validate($request, [
            'product_id' => [
                'exists:products,id'
            ],
        ]);

        $productId = $request->input('product_id');

        $collectionProduct = new CollectionProduct();
        $collectionProduct->collection_id = $collectionId;
        $collectionProduct->product_id = $productId;
        $collectionProduct->save();
    }

    public function removeProduct(Request $request, $collectionId)
    {
        $collection = Collection::findOrFail($collectionId);
        $this->authorize(CollectionPolicy::update, $collection);

        $productId = $request->input('product_id');

        CollectionProduct::query()
            ->where('collection_id', $collectionId)
            ->where('product_id', $productId)
            ->delete();
    }

    protected function getCollection($collectionSlug)
    {
        return Collection::query()
            ->where('slug', '=', $collectionSlug)
            ->firstOrFail();
    }
}
