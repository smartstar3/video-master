<?php

namespace MotionArray\Http\Requests\Products;

use Illuminate\Support\Facades\Auth;
use MotionArray\Http\Requests\Request;

class StoreProductsRequest extends Request
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
            'product.name' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'product.name.required' => 'Product must have a name'
        ];
    }
}
