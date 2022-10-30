<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\Category;
use View;
use Request;
use Response;

class CategoriesController extends BaseController
{


    /**
     * Category Repository
     *
     * @var Category
     */
    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $categories = $this->category->all();

        foreach ($categories as $key => $category) {
            $response[$key] = $category->toArray();
            $response[$key]["product_count"] = $category->products()->count();
        }

        return Response::json($response);
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $category = $this->category->find($id);

        if (is_null($category)) {
            $response['state'] = "error";
            $response['errors'] = [
                "message" => "No category found",
            ];

            return Response::json($response);
        }

        $response['state'] = "success";
        $response['entry'] = $category->toArray();

        return Response::json($response);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return View::make('admin.forms.category');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $category = $this->category->find($id);
        $edit = true;

        if (is_null($category)) {
            $response['state'] = "error";
            $response['errors'] = [
                "message" => "No category found",
            ];

            return Response::json($response);
        }

        return View::make('admin.forms.category', compact('category', 'edit'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $response = [];
        $this->category->fill(Request::all());

        if ($this->category->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->category->id,
                "name" => $this->category->name,
                "created_at" => $this->category->created_at,
                "updated_at" => $this->category->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->category->errors);

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
        $input = array_except(Request::all(), '_method', 'crud_select_name');

        $category = $this->category->find($id);

        if ($category->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $category->id,
                "name" => $category->name,
                "created_at" => $category->created_at,
                "updated_at" => $category->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($category->errors);

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
        $this->category->find($id)->delete();
    }

}
