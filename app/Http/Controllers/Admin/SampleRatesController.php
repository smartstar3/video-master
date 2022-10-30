<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\SampleRate;
use Request;
use Response;

class SampleRatesController extends BaseController
{

    /**
     * SampleRate Repository
     *
     * @var SampleRate
     */
    protected $sampleRate;

    public function __construct(SampleRate $sampleRate)
    {
        $this->sampleRate = $sampleRate;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $sampleRates = $this->sampleRate->all();

        foreach ($sampleRates as $key => $sampleRate) {
            $response[$key] = $sampleRate->toArray();
            $response[$key]["product_count"] = $sampleRate->products()->count();
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
        $this->sampleRate->fill(Request::all());

        if ($this->sampleRate->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->sampleRate->id,
                "name" => $this->sampleRate->name,
                "created_at" => $this->sampleRate->created_at,
                "updated_at" => $this->sampleRate->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->sampleRate->errors);

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

        $sampleRate = $this->sampleRate->find($id);

        if ($sampleRate->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $sampleRate->id,
                "name" => $sampleRate->name,
                "created_at" => $sampleRate->created_at,
                "updated_at" => $sampleRate->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($sampleRate->errors);

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
        $this->sampleRate->find($id)->delete();
    }

}
