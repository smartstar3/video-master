<?php

namespace MotionArray\Http\Requests\Seller;

use Illuminate\Support\Facades\Auth;
use MotionArray\Http\Requests\Request;

class UpdateSellerReviewRequest extends Request
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
            'product_id' => 'required|min:1',
            'stars' => 'required|min:1',
            'review' => 'required',
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
            'product_id.required' => 'Product is required',
            'product_id.min' => 'Product is required',
            'stars.required' => 'Star rating is required',
            'stars.min' => 'Star rating is required',
            'review.required' => 'Review is required'
        ];
    }
}
