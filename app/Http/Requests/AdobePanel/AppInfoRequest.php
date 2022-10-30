<?php

namespace MotionArray\Http\Requests\AdobePanel;

use Illuminate\Foundation\Http\FormRequest;

class AppInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'app_version' => 'regex:/^\d+.\d+.\d+/m',
        ];

        return $rules;
    }
}
