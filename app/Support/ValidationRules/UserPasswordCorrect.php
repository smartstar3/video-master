<?php

namespace MotionArray\Support\ValidationRules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use MotionArray\Models\User;

class UserPasswordCorrect implements Rule
{
    /**
     * @var User $user
     */
    private $user;

    /**
     * Create a new rule instance.
     * @param User $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     * Check that the password passed in argument matches the one currently saved in the database for the user.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Hash::check($value, $this->user->password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute was not entered correctly.';
    }
}
