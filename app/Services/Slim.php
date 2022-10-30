<?php namespace MotionArray\Services;

abstract class SlimStatus
{
    const Failure = 'failure';
    const Success = 'success';
}

class Slim
{
    // todo: Move to a AWS service class
    public static function uploadToAmazon($bucket, $filename, $value = null, $folder = '', $respectName = false)
    {
        $image = self::getImage($value);

        if ($image) {
            $s3 = \App::make('aws')->get('s3');

            $ext = pathinfo($image['input']['name'], PATHINFO_EXTENSION);
            $ext = $ext == 'jpeg' ? 'jpg' : $ext;

            if (!$respectName) {
                $filename = str_replace('.', '_', $filename) . '.' . $ext;

                $filename = str_replace([' ', '_'], '-', $filename);

                if (!ends_with($folder, '/')) {
                    $folder .= '/';
                }

                $filename = strtolower(preg_replace("/[^a-zA-Z0-9.-]/", "", $filename));
            }

            $filename = $folder . $filename;

            $response = $s3->putObject([
                'Bucket' => $bucket,
                'Body' => $image['output']['data'],
                'Key' => $filename,
                'ACL' => 'public-read',
                'ContentEncoding' => 'base64',
                'ContentType' => $image['input']['type'],
                'CacheControl' => 'public, max-age=31104000',
                'Expires' => date(DATE_RFC2822, strtotime("+360 days"))
            ]);

            return $response['ObjectURL'];
        }
    }

    public static function getImage($value = null)
    {
        $image = null;

        if ($value) {
            $image = Slim::parseInput($value);
        } else {
            $images = Slim::getImages();

            if (isset($images[0])) {
                $image = $images[0];
            }
        }

        return $image;
    }

    public static function getImages($inputName = 'slim')
    {

        $values = Slim::getPostData($inputName);

        // test for errors
        if ($values === false) {
            return false;
        }

        // determine if contains multiple input values, if is singular, put in array
        $data = [];
        if (!is_array($values)) {
            $values = [$values];
        }

        // handle all posted fields
        foreach ($values as $value) {
            $inputValue = Slim::parseInput($value);
            if ($inputValue) {
                array_push($data, $inputValue);
            }
        }

        // return the data collected from the fields
        return $data;
    }

    // $value should be in JSON format
    public static function parseInput($value)
    {
        // if no json received, exit, don't handle empty input values.
        if (empty($value)) {
            return null;
        }

        // The data is posted as a JSON String so to be used it needs to be deserialized first
        $data = json_decode($value);

        // shortcut
        $input = null;
        $actions = null;
        $output = null;
        $meta = null;

        if (isset ($data->input)) {
            $inputData = isset($data->input->image) ? Slim::getBase64Data($data->input->image) : null;
            $input = [
                'data' => $inputData,
                'name' => $data->input->name,
                'type' => $data->input->type,
                'size' => $data->input->size,
                'width' => $data->input->width,
                'height' => $data->input->height,
            ];
        }

        if (isset($data->output)) {
            $outputData = isset($data->output->image) ? Slim::getBase64Data($data->output->image) : null;
            $output = [
                'data' => $outputData,
                'width' => $data->output->width,
                'height' => $data->output->height
            ];
        }

        if (isset($data->actions)) {
            $actions = [
                'crop' => $data->actions->crop ? [
                    'x' => $data->actions->crop->x,
                    'y' => $data->actions->crop->y,
                    'width' => $data->actions->crop->width,
                    'height' => $data->actions->crop->height,
                    'type' => $data->actions->crop->type
                ] : null,
                'size' => $data->actions->size ? [
                    'width' => $data->actions->size->width,
                    'height' => $data->actions->size->height
                ] : null
            ];
        }

        if (isset($data->meta)) {
            $meta = $data->meta;
        }

        // We've sanitized the base64data and will now return the clean file object
        return [
            'input' => $input,
            'output' => $output,
            'actions' => $actions,
            'meta' => $meta
        ];
    }

    // $path should have trailing slash
    public static function saveFile($data, $name, $path = 'tmp/', $uid = true)
    {

        // Add trailing slash if omitted
        if (substr($path, -1) !== '/') {
            $path .= '/';
        }

        // Test if directory already exists
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // Let's put a unique id in front of the filename so we don't accidentally overwrite older files
        if ($uid) {
            $name = uniqid() . '_' . $name;
        }
        $path = $path . $name;

        // store the file
        Slim::save($data, $path);

        // return the files new name and location
        return [
            'name' => $name,
            'path' => $path
        ];
    }

    public static function outputJSON($data)
    {

        header('Content-Type: application/json');

        echo json_encode($data);
    }

    /**
     * Gets the posted data from the POST or FILES object. If was using Slim to upload it will be in POST (as posted
     * with hidden field) if not enhanced with Slim it'll be in FILES.
     *
     * @param $inputName
     *
     * @return array|bool
     */
    private static function getPostData($inputName)
    {

        $values = [];

        if (isset($_POST[$inputName])) {
            $values = $_POST[$inputName];
        } else if (isset($_FILES[$inputName])) {
            // Slim was not used to upload this file
            return false;
        }

        return $values;
    }

    /**
     * Saves the data to a given location
     *
     * @param $data
     * @param $path
     */
    private static function save($data, $path)
    {
        file_put_contents($path, $data);
    }

    /**
     * Strips the "data:image..." part of the base64 data string so PHP can save the string as a file
     *
     * @param $data
     *
     * @return string
     */
    private static function getBase64Data($data)
    {
        return base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data));
    }

}