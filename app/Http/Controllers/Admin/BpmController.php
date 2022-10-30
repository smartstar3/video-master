<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\Bpm;
use Request;
use Response;

class BpmController extends BaseController
{

    /**
     * Bpm Repository
     *
     * @var Bpm
     */
    protected $bpm;

    public function __construct(Bpm $bpm)
    {
        $this->bpm = $bpm;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $bpms = $this->bpm->all();

        $i = 0;
        foreach ($bpms as $bpm) {

            $response[$i] = [
                "id" => $bpm->id,
                "name" => $bpm->name,
                "product_count" => count($bpm->products),
                "created_at" => $bpm->created_at,
                "updated_at" => $bpm->updated_at
            ];
            $i++;
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
        $this->bpm->fill(Request::all());

        if ($this->bpm->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->bpm->id,
                "name" => $this->bpm->name,
                "created_at" => $this->bpm->created_at,
                "updated_at" => $this->bpm->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->bpm->errors);

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

        $bpm = $this->bpm->find($id);

        if ($bpm->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $bpm->id,
                "name" => $bpm->name,
                "created_at" => $bpm->created_at,
                "updated_at" => $bpm->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($bpm->errors);

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
        $this->bpm->find($id)->delete();
    }

}
