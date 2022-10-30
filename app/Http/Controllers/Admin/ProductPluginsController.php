<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\ProductPlugin;
use Request;
use Response;

class ProductPluginsController extends BaseController
{

    /**
     * ProductPlugin Repository
     *
     * @var ProductPlugin
     */
    protected $plugin;

    public function __construct(ProductPlugin $plugin)
    {
        $this->plugin = $plugin;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $response = [];
        $plugins = $this->plugin->all();

        foreach ($plugins as $key => $plugin) {
            $response[$key] = $plugin->toArray();
            $response[$key]["product_count"] = $plugin->products()->count();
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
        $this->plugin->fill(Request::all());

        if ($this->plugin->save()) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $this->plugin->id,
                "name" => $this->plugin->name,
                "created_at" => $this->plugin->created_at,
                "updated_at" => $this->plugin->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->plugin->errors);

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

        $plugin = $this->plugin->find($id);

        if ($plugin->update($input)) {
            $response['state'] = "success";
            $response['entry'] = [
                "id" => $plugin->id,
                "name" => $plugin->name,
                "created_at" => $plugin->created_at,
                "updated_at" => $plugin->updated_at
            ];

            return Response::json($response);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($plugin->errors);

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
        $this->plugin->find($id)->delete();
    }

}
