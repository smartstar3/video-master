<?php

namespace MotionArray\Http\Requests\AdobePanel;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class UserDownloadsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page' => 'required|integer',
            'perPage' => 'required|integer|max:60'
        ];
    }
}
