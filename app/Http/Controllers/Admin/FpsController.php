<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\Fps;
use Request;
use Response;

class FpsController extends BaseController
{

    /**
     * Fps Repository
     *
     * @var Fps
     */
    protected $fps;

    public function __construct(Fps $fps)
    {
        $this->fps = $fps;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $fpss = $this->fps->all();

        foreach ($fpss as $key => $fps) {
            $response[$key] = $fps->toArray();
            $response[$key]["product_count"] = $fps->products()->count();
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
        $this->fps->fill(Request::all());

        if ($this->fps->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->fps->id,
                "name" => $this->fps->name,
                "created_at" => $this->fps->created_at,
                "updated_at" => $this->fps->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->fps->errors);

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

        $fps = $this->fps->find($id);

        if ($fps->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $fps->id,
                "name" => $fps->name,
                "created_at" => $fps->created_at,
                "updated_at" => $fps->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($fps->errors);

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
        $this->fps->find($id)->delete();
    }

}
