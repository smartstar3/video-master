<?php

namespace MotionArray\Http\Requests;

use MotionArray\Models\UserSite;

/**
 * Class UserSiteRequest
 *
 * @package MotionArray\Http\Requests
 */
class UserSiteRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'portfolio.email' => 'email|max:255',
            'review.email' => 'email|max:255'
        ];

        $rules = array_merge(UserSite::$rules, $rules);

        if ($this->isUpdateRequest()) {
            $id = auth()->user()->id;
            $rules ['slug'] = "nullable|unique:user_sites,slug,{$id},user_id|required_if:use_domain,0|max:100";
            $rules ['domain'] = "nullable|unique:user_sites,domain,{$id},user_id|required_if:use_domain,1|max:255";
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'portfolio.email.email' => 'Your contact email must be a valid email address',
            'review.email.email' => 'Your contact email must be a valid email address'
        ];

        $messages = array_merge(UserSite::$messages, $messages);

        return $messages;
    }
}
