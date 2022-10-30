<?php

namespace MotionArray\Http\Requests\Seller;

use Illuminate\Validation\Validator;
use MotionArray\Http\Requests\Request;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;

class UpgradeUserToSellerRequest extends Request
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
            'terms' => 'accepted',
            'company_name' => 'required|unique:users,company_name,{:id}'
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
            'terms.accepted' => 'Please accept the terms and conditions',
            'company_name.required' => 'You need a producer name',
            'company_name.unique' => 'That producer name has already been used',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->container->make(UserRepository::class);

            if ($userRepository->slugExists($this->get('company_name'))) {
                $validator->errors()->add('company_name', 'That producer name has already been used');
            }
        });
    }
}
