<?php

namespace MotionArray\Http\Requests\Seller;

use Illuminate\Support\Facades\Auth;
use MotionArray\Http\Requests\Request;

class MessageSellerRequest extends Request
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
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
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
            'email.email' => 'Please enter a valid email address',
            'name.requred' => 'Name is required',
            'email.requred' => 'Email is required',
            'subject.requred' => 'Subject is required',
            'message.requred' => 'Message is required'
        ];
    }
}
