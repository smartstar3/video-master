<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\SubCategory;
use Request;
use Response;

class SubCategoriesController extends BaseController
{

    /**
     * SubCategory Repository
     *
     * @var SubCategory
     */
    protected $subCategory;

    public function __construct(SubCategory $subCategory)
    {
        $this->subCategory = $subCategory;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $subCategories = $this->subCategory->all();

        foreach ($subCategories as $key => $subCategory) {
            $response[$key] = $subCategory->toArray();
            $response[$key]["product_count"] = $subCategory->products()->count();
        }

        return Response::json($response);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $response = [];
        $this->subCategory->fill(Request::all());
        $this->subCategory->slug = $this->subCategory->generateSlug($this->subCategory->name);

        if ($this->subCategory->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->subCategory->id,
                "name" => $this->subCategory->name,
                "slug" => $this->subCategory->slug,
                "created_at" => $this->subCategory->created_at,
                "updated_at" => $this->subCategory->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->subCategory->errors);

        return Response::json($response);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update($id)
    {
        $response = [];
        $input = array_except(Request::all(), '_method');

        $subCategory = $this->subCategory->find($id);

        if ($subCategory->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $subCategory->id,
                "name" => $subCategory->name,
                "slug" => $subCategory->slug,
                "created_at" => $subCategory->created_at,
                "updated_at" => $subCategory->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($subCategory->errors);

        return Response::json($response);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $this->subCategory->find($id)->delete();
    }

}
