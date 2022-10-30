<?php

namespace MotionArray\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class Request
 *
 * @package App\Http\Requests
 */
abstract class Request extends FormRequest
{
    /**
     * Array of Request Inputs mapped to DB fields
     *
     * @var array
     */
    protected $input_mapping = [];

    /**
     *  Convert Inputs using Mapping
     */
    public function getConvertedInput()
    {
        $input = $this->all();

        foreach ($this->input_mapping as $old_name => $new_name) {
            $input = $this->changeInputName($input, $old_name, $new_name);
        }

        return $input;
    }

    /**
     * Change Input Name
     *
     * @param $input
     * @param $old_name
     * @param $new_name
     * @return array
     */
    protected function changeInputName($input, $old_name, $new_name)
    {
        if (isset($input[$old_name])) {
            $input[$new_name] = $input[$old_name];
            unset($input[$old_name]);
        }

        return $input;
    }

    /**
     * Check if update request
     *
     * @return bool
     */
    protected function isUpdateRequest()
    {
        return $this->getMethod() == 'PUT';
    }
}
