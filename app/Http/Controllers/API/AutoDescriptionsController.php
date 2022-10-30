<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Http\Request;
use MotionArray\Models\AutoDescription;
use MotionArray\Models\Product;
use MotionArray\Repositories\AutoDescriptionRepository;

class AutoDescriptionsController extends BaseController
{
    protected $autoDescriptionRepository;

    /**
     * @param AutoDescriptionRepository $autoDescriptionRepository
     */
    public function __construct(AutoDescriptionRepository $autoDescriptionRepository)
    {
        $this->autoDescriptionRepository = $autoDescriptionRepository;
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $autoDescriptions = AutoDescription::all();

        //return $autoDescriptions;
        return \MotionArray\Http\Resources\AutoDescription::collection($autoDescriptions);
    }

    /**
     * @param Request $request
     * @param $category
     * @param $name
     * @return \MotionArray\Http\Resources\AutoDescription
     */
    public function update(Request $request, $category, $name)
    {
        $autoDescription = AutoDescription::whereCategory($category)->whereName($name)->first();
        $autoDescription->data = $request->data;
        $autoDescription->save();

        return new \MotionArray\Http\Resources\AutoDescription($autoDescription);
    }

    /**
     * @param Request $request
     * @param $slug
     * @return mixed
     */
    public function generateStockVideoDescription(Request $request, $slug)
    {
        $overrideName = $request->overrideName ?? null;
        $video = Product::whereSlug($slug)->first();

        return $this->autoDescriptionRepository->generateStockVideoDescription($video, $overrideName);
    }
}
