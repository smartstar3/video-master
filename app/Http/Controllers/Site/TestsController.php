<?php namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\File;

class TestsController extends BaseController
{

    public function index($slug = null)
    {
        $folder = realpath(public_path(). "/..")."/tests/_reports/coverage";
        if (!file_exists($folder))
            return abort(404);

        if ($slug===null)
            return redirect("tests/index.html");

        $file = "{$folder}/{$slug}";
        if (strpos($slug, '..')!==false) // Security issue
            return abort(404);
        if (!file_exists($file))
            return abort(404);

        $content = File::get($file);

        if (substr($file,-4)==".css")
            $mime = "text/css";
        elseif (substr($file,-4)==".svg")
            $mime = "image/svg+xml";
        else
            $mime = mime_content_type($file);

        header('Content-Type: '. $mime);
        die($content);
    }
}
