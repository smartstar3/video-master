<?php

namespace MotionArray\Http\Requests\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use MotionArray\Facades\Flash;
use MotionArray\Http\Requests\Request;
use MotionArray\Models\User;
use MotionArray\Support\ValidationRules\UserPasswordCorrect;

class UpdateSelfRequest extends Request
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
        $user = Auth::user();

        return [
            // Email is required and must be unique.
            'email' => "required|email|unique:users,email,{$user->id}",
            // If password is not empty, also require the confirm email input, and minimum 6 characters.
            'password' => 'confirmed|min:6',
            'current_password' => [
                // If a password isn't empty, current_password is required.
                "required_if:password,!=,''",
                // If the current password is entered, make sure it matches the password in the database
                new UserPasswordCorrect($user)
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return User::$messages;
    }

    /**
     * We need to add a flash message when validation fails
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        if ($validator->fails()) {
            Flash::danger("There was a problem updating your details.");
        }
    }
}
