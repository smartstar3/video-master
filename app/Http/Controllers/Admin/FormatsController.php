<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\Format;
use Request;
use Response;

class FormatsController extends BaseController
{

    /**
     * Format Repository
     *
     * @var Format
     */
    protected $format;

    public function __construct(Format $format)
    {
        $this->format = $format;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $formats = $this->format->all();

        foreach ($formats as $key => $format) {
            $response[$key] = $format->toArray();
            $response[$key]["product_count"] = $format->products()->count();
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
        $this->format->fill(Request::all());

        if ($this->format->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->format->id,
                "name" => $this->format->name,
                "created_at" => $this->format->created_at,
                "updated_at" => $this->format->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->format->errors);

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

        $format = $this->format->find($id);

        if ($format->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $format->id,
                "name" => $format->name,
                "created_at" => $format->created_at,
                "updated_at" => $format->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($format->errors);

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
        $this->format->find($id)->delete();
    }

}
