<?php

namespace MotionArray\Http\Requests;

class SalesRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->hasRole(1);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'type' => 'required',
            'name' => 'required|max:255',
            'start' => 'required',
            'end' => 'required'
        ];

        return $rules;
    }

}
