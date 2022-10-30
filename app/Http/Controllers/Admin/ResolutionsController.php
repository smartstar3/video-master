<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\Resolution;
use Request;
use Response;

class ResolutionsController extends BaseController
{

    /**
     * Resolution Repository
     *
     * @var Resolution
     */
    protected $resolution;

    public function __construct(Resolution $resolution)
    {
        $this->resolution = $resolution;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $resolutions = $this->resolution->all();

        foreach ($resolutions as $key => $resolution) {
            $response[$key] = $resolution->toArray();
            $response[$key]["product_count"] = $resolution->products()->count();
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
        $this->resolution->fill(Request::all());

        if ($this->resolution->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->resolution->id,
                "name" => $this->resolution->name,
                "created_at" => $this->resolution->created_at,
                "updated_at" => $this->resolution->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->resolution->errors);

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

        $resolution = $this->resolution->find($id);

        if ($resolution->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $resolution->id,
                "name" => $resolution->name,
                "created_at" => $resolution->created_at,
                "updated_at" => $resolution->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($resolution->errors);

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
        $this->resolution->find($id)->delete();
    }

}
