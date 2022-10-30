<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\Version;
use Request;
use Response;

class VersionsController extends BaseController
{

    /**
     * Version Repository
     *
     * @var Version
     */
    protected $version;

    public function __construct(Version $version)
    {
        $this->version = $version;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $versions = $this->version->all();

        foreach ($versions as $key => $version) {
            $response[$key] = $version->toArray();
            $response[$key]["product_count"] = $version->products()->count();
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
        $this->version->fill(Request::all());

        if ($this->version->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->version->id,
                "name" => $this->version->name,
                "created_at" => $this->version->created_at,
                "updated_at" => $this->version->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->version->errors);

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

        $version = $this->version->find($id);

        if ($version->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $version->id,
                "name" => $version->name,
                "created_at" => $version->created_at,
                "updated_at" => $version->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($version->errors);

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
        $this->version->find($id)->delete();
    }

}
