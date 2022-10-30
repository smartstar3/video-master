<?php

namespace MotionArray\Http\Requests\Seller;

use MotionArray\Http\Requests\Request;
use MotionArray\Models\User;

class StoreSellerRequest extends Request
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
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'company_name' => 'required|unique:users,company_name,{:id}',
            'terms' => 'accepted',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return array_merge(User::$messages, [
            'terms.accepted' => 'Please accept the terms and conditions',
            'company_name.required' => 'You need a producer name',
            'company_name.unique' => 'That producer name has already been used',
        ]);
    }
}
