<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\Compression;
use Request;
use Response;

class CompressionsController extends BaseController
{

    /**
     * Compression Repository
     *
     * @var Compression
     */
    protected $compression;

    public function __construct(Compression $compression)
    {
        $this->compression = $compression;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $compressions = $this->compression->all();

        foreach ($compressions as $key => $compression) {
            $response[$key] = $compression->toArray();
            $response[$key]["product_count"] = $compression->products()->count();
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
        $this->compression->fill(Request::all());

        if ($this->compression->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->compression->id,
                "name" => $this->compression->name,
                "created_at" => $this->compression->created_at,
                "updated_at" => $this->compression->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->compression->errors);

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

        $compression = $this->compression->find($id);

        if ($compression->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $compression->id,
                "name" => $compression->name,
                "created_at" => $compression->created_at,
                "updated_at" => $compression->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($compression->errors);

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
        $this->compression->find($id)->delete();
    }

}
